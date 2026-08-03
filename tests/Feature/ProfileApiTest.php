<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a user with a real Sanctum token and a known password.
     *
     * @return array{0: User, 1: string}
     */
    private function makeAuthenticatedUser(string $password = 'OldPass123'): array
    {
        $user = User::factory()->create(['password' => Hash::make($password)]);
        $token = $user->createToken('test-token')->plainTextToken;

        return [$user, $token];
    }

    // ------------------------------------------------------------------ //
    // Update profile
    // ------------------------------------------------------------------ //

    public function test_update_profile_updates_fields(): void
    {
        [$user, $token] = $this->makeAuthenticatedUser();

        $this->withToken($token)
            ->putJson('/api/profile', [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'telegram_chat_id' => '123456789',
            ])
            ->assertOk()
            ->assertJsonPath('name', 'Updated Name')
            ->assertJsonPath('email', 'updated@example.com')
            ->assertJsonPath('telegram_chat_id', '123456789');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'telegram_chat_id' => '123456789',
        ]);
    }

    public function test_update_profile_rejects_taken_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        [, $token] = $this->makeAuthenticatedUser();

        $this->withToken($token)
            ->putJson('/api/profile', [
                'name' => 'Updated Name',
                'email' => 'taken@example.com',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_update_profile_requires_authentication(): void
    {
        $this->putJson('/api/profile', [
            'name' => 'No Auth',
            'email' => 'noauth@example.com',
        ])->assertUnauthorized();
    }

    // ------------------------------------------------------------------ //
    // Avatar upload / delete
    // ------------------------------------------------------------------ //

    public function test_avatar_upload_saves_file_and_sets_avatar(): void
    {
        Storage::fake('public');
        [$user, $token] = $this->makeAuthenticatedUser();

        $png = 'data:image/png;base64,' . base64_encode('fake-png-bytes');

        $this->withToken($token)
            ->postJson('/api/profile/avatar', ['avatar' => $png])
            ->assertOk()
            ->assertJsonStructure(['avatar']);

        $avatar = $user->fresh()->avatar;
        $this->assertNotNull($avatar);
        Storage::disk('public')->assertExists($avatar);
    }

    public function test_avatar_upload_replaces_previous_file(): void
    {
        Storage::fake('public');
        [$user, $token] = $this->makeAuthenticatedUser();

        // Seed an existing avatar so the controller must clean it up.
        $oldPath = 'avatars/old-avatar.png';
        Storage::disk('public')->put($oldPath, 'old-bytes');
        $user->update(['avatar' => $oldPath]);

        $png = 'data:image/png;base64,' . base64_encode('new-png-bytes');

        $this->withToken($token)
            ->postJson('/api/profile/avatar', ['avatar' => $png])
            ->assertOk();

        Storage::disk('public')->assertMissing($oldPath);

        $newAvatar = $user->fresh()->avatar;
        $this->assertNotSame($oldPath, $newAvatar);
        Storage::disk('public')->assertExists($newAvatar);
    }

    public function test_avatar_upload_rejects_invalid_image(): void
    {
        Storage::fake('public');
        [, $token] = $this->makeAuthenticatedUser();

        $this->withToken($token)
            ->postJson('/api/profile/avatar', ['avatar' => 'not-a-data-url'])
            ->assertUnprocessable()
            ->assertJson(['message' => 'Invalid avatar image.']);
    }

    public function test_avatar_delete_clears_avatar_and_file(): void
    {
        Storage::fake('public');
        [$user, $token] = $this->makeAuthenticatedUser();

        $path = 'avatars/to-delete.png';
        Storage::disk('public')->put($path, 'bytes');
        $user->update(['avatar' => $path]);

        $this->withToken($token)
            ->deleteJson('/api/profile/avatar')
            ->assertOk()
            ->assertJson(['message' => 'Avatar removed.']);

        $this->assertNull($user->fresh()->avatar);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_avatar_endpoints_require_authentication(): void
    {
        $this->postJson('/api/profile/avatar', ['avatar' => 'data:image/png;base64,abc'])
            ->assertUnauthorized();

        $this->deleteJson('/api/profile/avatar')
            ->assertUnauthorized();
    }

    // ------------------------------------------------------------------ //
    // Change password
    // ------------------------------------------------------------------ //

    public function test_change_password_succeeds_with_correct_current_password(): void
    {
        [$user, $token] = $this->makeAuthenticatedUser('OldPass123');

        $this->withToken($token)
            ->putJson('/api/change-password', [
                'current_password' => 'OldPass123',
                'password' => 'NewPass123',
                'password_confirmation' => 'NewPass123',
            ])
            ->assertOk()
            ->assertJson(['message' => 'Password changed successfully']);

        $this->assertTrue(Hash::check('NewPass123', $user->fresh()->password));
    }

    public function test_change_password_rejects_wrong_current_password(): void
    {
        [, $token] = $this->makeAuthenticatedUser('OldPass123');

        $this->withToken($token)
            ->putJson('/api/change-password', [
                'current_password' => 'WrongPass123',
                'password' => 'NewPass123',
                'password_confirmation' => 'NewPass123',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('current_password');
    }

    public function test_change_password_rejects_weak_password(): void
    {
        [, $token] = $this->makeAuthenticatedUser('OldPass123');

        // 'abcdefgh' is 8 chars (passes min:8) but lacks an uppercase letter
        // and a digit — proves the complexity regex is enforced, not just length.
        $this->withToken($token)
            ->putJson('/api/change-password', [
                'current_password' => 'OldPass123',
                'password' => 'abcdefgh',
                'password_confirmation' => 'abcdefgh',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');
    }
}

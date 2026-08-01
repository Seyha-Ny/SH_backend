<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    private const UNAUTHENTICATED_MESSAGE = 'Unauthenticated. Please provide a valid authentication token.';
    // Message returned for abort(403) on API requests: this Laravel version's
    // abort() throws a plain HttpException, so bootstrap/app.php's HttpException
    // render callback echoes the middleware's own message.
    private const FORBIDDEN_MESSAGE = 'Forbidden. Admin access required.';

    /**
     * Create a user with a real Sanctum token (persisted in DB).
     *
     * @return array{0: User, 1: string}
     */
    private function makeUserWithToken(array $attributes = []): array
    {
        $user = User::factory()->create($attributes);
        $token = $user->createToken('admin-test')->plainTextToken;

        return [$user, $token];
    }

    // ------------------------------------------------------------------ //
    // CDN purge (API — admin only)
    // ------------------------------------------------------------------ //

    public function test_cdn_purge_unauthenticated_returns_401(): void
    {
        $this->postJson('/api/cdn/purge')
            ->assertUnauthorized()
            ->assertJson(['message' => self::UNAUTHENTICATED_MESSAGE]);
    }

    public function test_cdn_purge_forbidden_for_regular_user(): void
    {
        [, $token] = $this->makeUserWithToken(['is_admin' => false, 'role' => 'customer']);

        $this->withToken($token)
            ->postJson('/api/cdn/purge')
            ->assertForbidden()
            ->assertJson(['message' => self::FORBIDDEN_MESSAGE]);
    }

    public function test_cdn_purge_forbidden_for_admin_role_but_not_admin_flag(): void
    {
        // A user with role 'admin' but is_admin = false must NOT pass.
        [, $token] = $this->makeUserWithToken(['is_admin' => false, 'role' => 'admin']);

        $this->withToken($token)
            ->postJson('/api/cdn/purge')
            ->assertForbidden()
            ->assertJson(['message' => self::FORBIDDEN_MESSAGE]);
    }

    public function test_cdn_purge_allows_admin(): void
    {
        config([
            'services.cloudflare.api_token' => 'test-token',
            'services.cloudflare.zone_id' => 'test-zone',
        ]);

        Http::fake([
            'api.cloudflare.com/*' => Http::response(['success' => true], 200),
        ]);

        [, $token] = $this->makeUserWithToken(['is_admin' => true, 'role' => 'admin']);

        $this->withToken($token)
            ->postJson('/api/cdn/purge', ['urls' => ['https://example.com/products']])
            ->assertOk()
            ->assertJsonPath('message', 'CDN cache purge submitted successfully.');

        // Specific URLs must be sent as a "files" payload to Cloudflare.
        Http::assertSent(fn ($request) =>
            str_contains($request->url(), 'purge_cache')
            && data_get($request->data(), 'files') === ['https://example.com/products']
        );
    }

    public function test_cdn_purge_allows_super_admin(): void
    {
        config([
            'services.cloudflare.api_token' => 'test-token',
            'services.cloudflare.zone_id' => 'test-zone',
        ]);

        Http::fake([
            'api.cloudflare.com/*' => Http::response(['success' => true], 200),
        ]);

        [, $token] = $this->makeUserWithToken(['is_admin' => true, 'role' => 'super_admin']);

        $this->withToken($token)
            ->postJson('/api/cdn/purge')
            ->assertOk()
            ->assertJsonPath('message', 'CDN cache purge submitted successfully.');

        // No URLs provided → the controller must send purge_everything.
        Http::assertSent(fn ($request) =>
            str_contains($request->url(), 'purge_cache')
            && data_get($request->data(), 'purge_everything') === true
        );
    }

    public function test_cdn_purge_returns_501_when_not_configured(): void
    {
        config([
            'services.cloudflare.api_token' => null,
            'services.cloudflare.zone_id' => null,
        ]);

        // Fake the CDN endpoint anyway so the test process is fully isolated:
        // assertNothingSent() then genuinely proves no request was attempted.
        Http::fake([
            'api.cloudflare.com/*' => Http::response(['success' => true], 200),
        ]);

        [, $token] = $this->makeUserWithToken(['is_admin' => true, 'role' => 'admin']);

        $this->withToken($token)
            ->postJson('/api/cdn/purge')
            ->assertStatus(501)
            ->assertJsonPath('message', 'CDN purge is not configured. Set CLOUDFLARE_API_TOKEN and CLOUDFLARE_ZONE_ID in .env');

        // No external request should be made when the CDN is not configured.
        Http::assertNothingSent();
    }

    // ------------------------------------------------------------------ //
    // Admin dashboard (web — admin only)
    // ------------------------------------------------------------------ //

    public function test_dashboard_redirects_unauthenticated_visitor_to_login(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/admin/login');
    }

    public function test_dashboard_forbidden_for_regular_user(): void
    {
        $user = User::factory()->create(['is_admin' => false, 'role' => 'customer']);

        $this->actingAs($user)
            ->get('/admin/dashboard')
            ->assertForbidden();
    }

    public function test_dashboard_allows_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Dashboard');
    }

    public function test_dashboard_allows_super_admin(): void
    {
        $superAdmin = User::factory()->create(['is_admin' => true, 'role' => 'super_admin']);

        $this->actingAs($superAdmin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Dashboard');
    }
}

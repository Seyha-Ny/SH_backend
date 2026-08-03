<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_admin_password_is_not_overwritten_on_re_seed(): void
    {
        // The owner changed their admin password — re-seeding must NOT
        // reset it back to the default.
        $customPassword = 'MySecretPass!42';
        $admin = User::factory()->create([
            'email' => 'admin@ecommerce.com',
            'name' => 'Custom Name',
            'password' => Hash::make($customPassword),
            'is_admin' => true,
            'role' => 'admin',
        ]);

        $this->seed(AdminUserSeeder::class);

        $admin->refresh();

        $this->assertTrue($admin->is_admin);
        $this->assertSame('admin', $admin->role);

        // The custom password must still work; the default must not.
        $this->assertTrue(Hash::check($customPassword, $admin->password));
        $this->assertFalse(Hash::check('admin123', $admin->password));
    }

    public function test_existing_non_admin_with_same_email_is_promoted_without_password_reset(): void
    {
        // Edge case: a customer account happened to use the admin email.
        // The seeder promotes it to admin but must preserve its password.
        $customerPassword = 'CustomerPass!1';
        $user = User::factory()->create([
            'email' => 'admin@ecommerce.com',
            'name' => 'Some Customer',
            'password' => Hash::make($customerPassword),
            'is_admin' => false,
            'role' => 'customer',
        ]);

        $this->seed(AdminUserSeeder::class);

        $user->refresh();

        $this->assertTrue($user->is_admin);
        $this->assertSame('admin', $user->role);
        $this->assertTrue(Hash::check($customerPassword, $user->password));
        $this->assertFalse(Hash::check('admin123', $user->password));
    }

    public function test_first_run_creates_admin_with_default_password(): void
    {
        // Guarantee the default path regardless of any ADMIN_PASSWORD set
        // in the developer's environment (.env, shell, phpunit.xml).
        putenv('ADMIN_PASSWORD');

        $this->assertDatabaseMissing('users', ['email' => 'admin@ecommerce.com']);

        $this->seed(AdminUserSeeder::class);

        $admin = User::where('email', 'admin@ecommerce.com')->first();

        $this->assertNotNull($admin);
        $this->assertTrue($admin->is_admin);
        $this->assertSame('admin', $admin->role);
        $this->assertTrue(Hash::check('admin123', $admin->password));
    }

    public function test_first_run_honors_admin_password_env_override(): void
    {
        putenv('ADMIN_PASSWORD=EnvCustomPass!9');

        try {
            $this->seed(AdminUserSeeder::class);
        } finally {
            putenv('ADMIN_PASSWORD');
        }

        $admin = User::where('email', 'admin@ecommerce.com')->first();

        $this->assertNotNull($admin);
        $this->assertTrue(Hash::check('EnvCustomPass!9', $admin->password));
        $this->assertFalse(Hash::check('admin123', $admin->password));
    }
}

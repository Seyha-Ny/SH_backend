<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * The default password used only when the admin user is first created.
     * Set ADMIN_PASSWORD in your .env to override it. Existing admins are
     * never touched — re-running the seeders will NOT reset their password.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@ecommerce.com')->first();

        if ($admin) {
            // Already exists — refresh name/role but never overwrite the
            // password, so a db:seed can't silently lock the owner out or
            // revert a password they changed.
            $admin->update([
                'name' => 'Admin',
                'is_admin' => true,
                'role' => 'admin',
            ]);

            return;
        }

        // First-time setup only.
        User::create([
            'name' => 'Admin',
            'email' => 'admin@ecommerce.com',
            'password' => env('ADMIN_PASSWORD', 'admin123'),
            'is_admin' => true,
            'role' => 'admin',
        ]);
    }
}

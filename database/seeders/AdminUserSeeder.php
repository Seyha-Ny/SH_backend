<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\User::updateOrCreate(
            ['email' => 'admin@ecommerce.com'],
            ['name' => 'Admin', 'password' => bcrypt('seyha1234!@#$'), 'is_admin' => true, 'role' => 'admin']
        );
    }
}

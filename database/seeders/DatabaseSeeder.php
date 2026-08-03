<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            // Canonical categories first — ProductSeeder builds its demo
            // products from CategorySeeder::CATEGORIES.
            CategorySeeder::class,
            ProductSeeder::class,
            ShippingMethodSeeder::class,
        ]);
    }
}

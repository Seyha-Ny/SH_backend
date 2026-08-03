<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Canonical storefront categories. This is the single source of truth —
     * ProductSeeder builds its demo products from the same list, so the two
     * seeders can never drift apart again.
     */
    public const CATEGORIES = [
        ['name' => 'Electronics', 'slug' => 'electronics', 'description' => 'Electronic devices and accessories'],
        ['name' => 'Fashion', 'slug' => 'fashion', 'description' => 'Fashion and apparel'],
        ['name' => 'Home & Living', 'slug' => 'home-living', 'description' => 'Home improvement and living essentials'],
        ['name' => 'Sports', 'slug' => 'sports', 'description' => 'Sports equipment and gear'],
        ['name' => 'Books', 'slug' => 'books', 'description' => 'Books and magazines'],
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $category) {
            // firstOrCreate on the unique slug keeps re-seeding idempotent:
            // existing categories are never duplicated.
            Category::firstOrCreate(['slug' => $category['slug']], $category);
        }
    }
}

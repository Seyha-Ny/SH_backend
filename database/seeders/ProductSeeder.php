<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Build demo products for the canonical categories from CategorySeeder
        // so both seeders stay in sync and never create overlapping sets.
        foreach (CategorySeeder::CATEGORIES as $categoryDef) {
            $categoryName = $categoryDef['name'];

            // firstOrCreate on the canonical slug keeps re-seeding idempotent:
            // existing categories (and their products below) are never
            // duplicated or overwritten. Using the canonical slug (not a
            // re-derived one) keeps CategorySeeder as the single source of
            // truth.
            $category = Category::firstOrCreate(
                ['slug' => $categoryDef['slug']],
                $categoryDef
            );

            $products = [
                ['Premium ' . $categoryName . ' A', 49.99, 100],
                ['Premium ' . $categoryName . ' B', 79.99, 80],
                ['Premium ' . $categoryName . ' C', 29.99, 150],
                ['Premium ' . $categoryName . ' D', 99.99, 50],
                ['Premium ' . $categoryName . ' E', 59.99, 90],
            ];

            foreach ($products as $product) {
                Product::firstOrCreate(
                    ['slug' => Str::slug($product[0])],
                    [
                        'category_id' => $category->id,
                        'name' => $product[0],
                        'description' => 'High-quality ' . strtolower($product[0]) . ' with excellent reviews.',
                        'price' => $product[1],
                        'stock' => $product[2],
                    ]
                );
            }
        }
    }
}

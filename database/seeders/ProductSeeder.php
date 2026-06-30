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
        $categories = ['Electronics', 'Fashion', 'Home & Living', 'Sports', 'Books'];

        foreach ($categories as $categoryName) {
            $category = Category::create([
                'name' => $categoryName,
                'slug' => Str::slug($categoryName),
                'description' => 'Shop the best ' . $categoryName . ' products.',
            ]);

            $products = [
                ['Premium ' . $categoryName . ' A', 49.99, 100],
                ['Premium ' . $categoryName . ' B', 79.99, 80],
                ['Premium ' . $categoryName . ' C', 29.99, 150],
                ['Premium ' . $categoryName . ' D', 99.99, 50],
                ['Premium ' . $categoryName . ' E', 59.99, 90],
            ];

            foreach ($products as $product) {
                Product::create([
                    'category_id' => $category->id,
                    'name' => $product[0],
                    'slug' => Str::slug($product[0]),
                    'description' => 'High-quality ' . strtolower($product[0]) . ' with excellent reviews.',
                    'price' => $product[1],
                    'stock' => $product[2],
                ]);
            }
        }
    }
}

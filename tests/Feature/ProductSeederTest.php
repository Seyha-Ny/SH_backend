<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_run_creates_categories_and_products(): void
    {
        $this->seed(ProductSeeder::class);

        // 5 categories, 5 products each.
        $this->assertDatabaseCount('categories', 5);
        $this->assertDatabaseCount('products', 25);
    }

    public function test_re_seed_is_idempotent_and_does_not_duplicate(): void
    {
        $this->seed(ProductSeeder::class);
        $this->seed(ProductSeeder::class);

        // The unique slug constraints would make the old create() approach
        // crash on the second run — firstOrCreate keeps it idempotent.
        $this->assertDatabaseCount('categories', 5);
        $this->assertDatabaseCount('products', 25);
    }

    public function test_existing_product_preserves_admin_edits(): void
    {
        // Seed once, then simulate an admin editing the product (price, stock,
        // description) — a second re-seed must not overwrite those edits.
        $this->seed(ProductSeeder::class);

        $product = Product::where('slug', 'premium-electronics-a')->first();
        $product->update([
            'description' => 'Edited description',
            'price' => 199.99,
            'stock' => 5,
        ]);

        $this->seed(ProductSeeder::class);

        $product->refresh();

        $this->assertNotNull($product);
        $this->assertEquals(199.99, (float) $product->price);
        $this->assertSame(5, $product->stock);
        $this->assertSame('Edited description', $product->description);
    }

    public function test_seeded_category_preserves_admin_edits_on_re_seed(): void
    {
        // The category was created BY the seeder (not pre-existing) — admin
        // edits to it must still survive a re-seed, with products linked.
        $this->seed(ProductSeeder::class);

        $category = Category::where('slug', 'electronics')->first();
        $category->update(['description' => 'Edited category description']);

        $this->seed(ProductSeeder::class);

        $category->refresh();

        $this->assertSame('Edited category description', $category->description);
        $this->assertSame(5, $category->products()->count());
        $this->assertDatabaseCount('categories', 5);
    }
}

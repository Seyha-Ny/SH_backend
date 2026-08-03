<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_seed_chain_produces_expected_dataset(): void
    {
        // Guarantee the default admin password path regardless of any
        // ADMIN_PASSWORD set in the developer's environment.
        putenv('ADMIN_PASSWORD');

        try {
            $this->seed(DatabaseSeeder::class);
        } finally {
            putenv('ADMIN_PASSWORD');
        }

        // Admin
        $this->assertDatabaseCount('users', 1);
        $admin = User::where('email', 'admin@ecommerce.com')->first();
        $this->assertNotNull($admin);
        $this->assertTrue($admin->is_admin);
        $this->assertSame('admin', $admin->role);
        $this->assertTrue(Hash::check('admin123', $admin->password));

        // Categories — exactly the canonical five, each with 5 products.
        $this->assertDatabaseCount('categories', 5);
        foreach (['electronics', 'fashion', 'home-living', 'sports', 'books'] as $slug) {
            $this->assertDatabaseHas('categories', ['slug' => $slug]);
        }

        // Products
        $this->assertDatabaseCount('products', 25);
        foreach (Category::all() as $category) {
            $this->assertSame(5, $category->products()->count());
        }

        // Shipping methods
        $this->assertDatabaseCount('courier_shipping_methods', 2);
        $this->assertDatabaseHas('courier_shipping_methods', ['code' => 'standard']);
        $this->assertDatabaseHas('courier_shipping_methods', ['code' => 'express']);
    }

    public function test_full_seed_chain_is_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        // Running the whole chain twice must not duplicate anything.
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('categories', 5);
        $this->assertDatabaseCount('products', 25);
        $this->assertDatabaseCount('courier_shipping_methods', 2);
    }

    public function test_full_seed_chain_preserves_existing_data_on_re_seed(): void
    {
        // Simulate real usage: seed once, admin edits a product and a
        // shipping method, a customer registers — a re-seed must not
        // overwrite any of it.
        $this->seed(DatabaseSeeder::class);

        $product = Product::where('slug', 'premium-electronics-a')->first();
        $product->update(['price' => 199.99, 'stock' => 5]);

        $standard = ShippingMethod::where('code', 'standard')->first();
        $standard->update(['fee' => 5.50, 'active' => false]);

        $customer = User::create([
            'name' => 'Customer',
            'email' => 'customer@example.com',
            'password' => Hash::make('customerpass'),
        ]);

        $this->seed(DatabaseSeeder::class);

        // Admin edits survive.
        $product->refresh();
        $this->assertEquals(199.99, (float) $product->price);
        $this->assertSame(5, $product->stock);

        $standard->refresh();
        $this->assertSame('5.50', (string) $standard->fee);
        $this->assertFalse($standard->active);

        // The customer account survives, and totals stay correct.
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('categories', 5);
        $this->assertDatabaseCount('products', 25);
        $this->assertDatabaseCount('courier_shipping_methods', 2);
    }
}

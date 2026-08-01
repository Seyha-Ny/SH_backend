<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminCrudRoutesTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------ //
    // Helpers
    // ------------------------------------------------------------------ //

    private function makeAdmin(): User
    {
        return User::factory()->create(['is_admin' => true, 'role' => 'admin']);
    }

    private function makeSuperAdmin(): User
    {
        return User::factory()->create(['is_admin' => true, 'role' => 'super_admin']);
    }

    private function makeRegularUser(): User
    {
        return User::factory()->create(['is_admin' => false, 'role' => 'customer']);
    }

    private function makeCategory(): Category
    {
        return Category::create([
            'name' => 'Gadgets',
            'slug' => 'gadgets',
        ]);
    }

    private function makeProduct(?Category $category = null): Product
    {
        $category ??= $this->makeCategory();

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Smart Watch',
            'slug' => 'smart-watch',
            'description' => 'A smart watch',
            'price' => 99.99,
            'stock' => 10,
        ]);
    }

    private function makeOrder(?User $user = null): Order
    {
        $user ??= User::factory()->create();

        return $user->orders()->create([
            'status' => 'pending',
            'total' => 99.98,
            'subtotal' => 99.98,
            'tax_amount' => 0,
            'shipping_amount' => 0,
        ]);
    }

    private function makeCoupon(): Coupon
    {
        return Coupon::create([
            'code' => 'SAVE10',
            'type' => 'percentage',
            'value' => 10,
            'active' => true,
        ]);
    }

    private function makeReview(?User $user = null, ?Product $product = null): Review
    {
        $user ??= User::factory()->create();
        $product ??= $this->makeProduct();

        return Review::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Great product',
            'approved' => false,
        ]);
    }

    private function makeShippingMethod(string $name, string $code, float $fee): ShippingMethod
    {
        return ShippingMethod::create([
            'name' => $name,
            'code' => $code,
            'fee' => $fee,
        ]);
    }

    // ------------------------------------------------------------------ //
    // Products
    // ------------------------------------------------------------------ //

    public function test_products_routes_forbidden_for_regular_user(): void
    {
        $this->actingAs($this->makeRegularUser());
        $product = $this->makeProduct();

        $this->get('/admin/products')->assertForbidden();
        $this->get('/admin/products/create')->assertForbidden();
        $this->post('/admin/products', ['name' => 'X', 'price' => 1, 'stock' => 1, 'category_id' => $product->category_id])->assertForbidden();
        $this->get("/admin/products/{$product->id}/edit")->assertForbidden();
        $this->put("/admin/products/{$product->id}", ['name' => 'X', 'price' => 1, 'stock' => 1, 'category_id' => $product->category_id])->assertForbidden();
        $this->post("/admin/products/{$product->id}/stock", ['stock_delta' => 1])->assertForbidden();
        $this->delete("/admin/products/{$product->id}")->assertForbidden();
        $this->get('/admin/products/export')->assertForbidden();
    }

    public function test_products_read_routes_allow_admin(): void
    {
        $this->actingAs($this->makeAdmin());
        $product = $this->makeProduct();

        $this->get('/admin/products')->assertOk()->assertSee('Smart Watch');
        $this->get('/admin/products/create')->assertOk();
        $this->get("/admin/products/{$product->id}/edit")->assertOk();
        $this->get('/admin/products/export')->assertOk();
    }

    public function test_products_store_allows_admin(): void
    {
        $this->actingAs($this->makeAdmin());
        $category = $this->makeCategory();

        $this->post('/admin/products', [
            'name' => 'New Product',
            'price' => 19.99,
            'stock' => 5,
            'category_id' => $category->id,
        ])
            ->assertRedirect('/admin/products');

        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
            'slug' => 'new-product',
            'price' => 19.99,
            'stock' => 5,
            'category_id' => $category->id,
        ]);
    }

    public function test_products_update_allows_admin(): void
    {
        $this->actingAs($this->makeAdmin());
        $product = $this->makeProduct();

        $this->put("/admin/products/{$product->id}", [
            'name' => 'Updated Watch',
            'price' => 129.99,
            'stock' => 3,
            'category_id' => $product->category_id,
        ])
            ->assertRedirect('/admin/products');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Watch',
            'price' => 129.99,
            'stock' => 3,
        ]);
    }

    public function test_products_adjust_stock_allows_admin(): void
    {
        $this->actingAs($this->makeAdmin());
        $product = $this->makeProduct(); // stock 10

        $this->post("/admin/products/{$product->id}/stock", ['stock_delta' => 5])
            ->assertStatus(302);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 15,
        ]);
    }

    public function test_products_destroy_allows_admin(): void
    {
        $this->actingAs($this->makeAdmin());
        $product = $this->makeProduct();

        $this->delete("/admin/products/{$product->id}")
            ->assertRedirect('/admin/products');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    // ------------------------------------------------------------------ //
    // Categories
    // ------------------------------------------------------------------ //

    public function test_categories_routes_forbidden_for_regular_user(): void
    {
        $this->actingAs($this->makeRegularUser());
        $category = $this->makeCategory();

        $this->get('/admin/categories')->assertForbidden();
        $this->get('/admin/categories/create')->assertForbidden();
        $this->post('/admin/categories', ['name' => 'X'])->assertForbidden();
        $this->get("/admin/categories/{$category->id}/edit")->assertForbidden();
        $this->put("/admin/categories/{$category->id}", ['name' => 'X'])->assertForbidden();
        $this->delete("/admin/categories/{$category->id}")->assertForbidden();
    }

    public function test_categories_read_routes_allow_admin(): void
    {
        $this->actingAs($this->makeAdmin());
        $category = $this->makeCategory();

        $this->get('/admin/categories')->assertOk()->assertSee('Gadgets');
        $this->get('/admin/categories/create')->assertOk();
        $this->get("/admin/categories/{$category->id}/edit")->assertOk();
    }

    public function test_categories_store_allows_admin(): void
    {
        $this->actingAs($this->makeAdmin());

        $this->post('/admin/categories', ['name' => 'Audio'])
            ->assertRedirect('/admin/categories');

        $this->assertDatabaseHas('categories', [
            'name' => 'Audio',
            'slug' => 'audio',
        ]);
    }

    public function test_categories_update_allows_admin(): void
    {
        $this->actingAs($this->makeAdmin());
        $category = $this->makeCategory();

        $this->put("/admin/categories/{$category->id}", ['name' => 'Electronics'])
            ->assertRedirect('/admin/categories');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Electronics',
        ]);
    }

    public function test_categories_destroy_allows_admin(): void
    {
        $this->actingAs($this->makeAdmin());
        $category = $this->makeCategory();

        $this->delete("/admin/categories/{$category->id}")
            ->assertRedirect('/admin/categories');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    // ------------------------------------------------------------------ //
    // Orders
    // ------------------------------------------------------------------ //

    public function test_orders_routes_forbidden_for_regular_user(): void
    {
        $this->actingAs($this->makeRegularUser());
        $order = $this->makeOrder();

        $this->get('/admin/orders')->assertForbidden();
        $this->get("/admin/orders/{$order->id}")->assertForbidden();
        $this->put("/admin/orders/{$order->id}/status", ['status' => 'completed'])->assertForbidden();
        $this->post("/admin/orders/{$order->id}/mail", ['subject' => 'Hi', 'message' => 'Body'])->assertForbidden();
        $this->put("/admin/orders/{$order->id}/process", ['action' => 'approve'])->assertForbidden();
        $this->get('/admin/orders/export')->assertForbidden();
    }

    public function test_orders_read_routes_allow_admin(): void
    {
        $this->actingAs($this->makeAdmin());
        $order = $this->makeOrder();

        $this->get('/admin/orders')->assertOk()->assertSee('#'.$order->id);
        $this->get("/admin/orders/{$order->id}")->assertOk();
        $this->get('/admin/orders/export')->assertOk();
    }

    public function test_orders_update_status_allows_admin(): void
    {
        Mail::fake();
        $this->actingAs($this->makeAdmin());
        $order = $this->makeOrder();

        $this->put("/admin/orders/{$order->id}/status", [
            'status' => 'completed',
            'tracking_number' => 'TRK123',
        ])->assertStatus(302);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'completed',
            'tracking_number' => 'TRK123',
        ]);
    }

    public function test_orders_send_mail_allows_admin(): void
    {
        Mail::fake();
        $this->actingAs($this->makeAdmin());
        $order = $this->makeOrder();

        $this->post("/admin/orders/{$order->id}/mail", [
            'subject' => 'Order update',
            'message' => 'Your order has shipped.',
        ])->assertStatus(302);
    }

    public function test_orders_process_request_allows_admin(): void
    {
        Mail::fake();
        $this->actingAs($this->makeAdmin());
        $user = User::factory()->create();
        $order = $user->orders()->create([
            'status' => 'cancellation_requested',
            'total' => 25.00,
            'subtotal' => 25.00,
            'tax_amount' => 0,
            'shipping_amount' => 0,
        ]);

        $this->put("/admin/orders/{$order->id}/process", ['action' => 'approve'])
            ->assertStatus(302);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
        ]);
    }

    // ------------------------------------------------------------------ //
    // Users
    // ------------------------------------------------------------------ //

    public function test_users_routes_forbidden_for_regular_user(): void
    {
        $this->actingAs($this->makeRegularUser());
        $target = User::factory()->create();

        $this->get('/admin/users')->assertForbidden();
        $this->get("/admin/users/{$target->id}")->assertForbidden();
        $this->get("/admin/users/{$target->id}/edit")->assertForbidden();
        $this->put("/admin/users/{$target->id}", ['name' => 'X', 'email' => 'x@example.com'])->assertForbidden();
        $this->delete("/admin/users/{$target->id}", ['confirm' => '1'])->assertForbidden();
    }

    public function test_users_read_routes_allow_admin(): void
    {
        $this->actingAs($this->makeAdmin());
        $target = User::factory()->create(['name' => 'Jane Doe']);

        $this->get('/admin/users')->assertOk();
        $this->get("/admin/users/{$target->id}")->assertOk()->assertSee('Jane Doe');
        $this->get("/admin/users/{$target->id}/edit")->assertOk();
    }

    public function test_users_update_allows_admin(): void
    {
        $this->actingAs($this->makeAdmin());
        $target = User::factory()->create();

        $this->put("/admin/users/{$target->id}", [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'is_admin' => 1,
            'role' => 'admin',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'is_admin' => true,
            'role' => 'admin',
        ]);
    }

    public function test_users_destroy_allows_admin(): void
    {
        $this->actingAs($this->makeAdmin());
        $target = User::factory()->create();

        $this->delete("/admin/users/{$target->id}", ['confirm' => '1'])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    // ------------------------------------------------------------------ //
    // Coupons
    // ------------------------------------------------------------------ //

    public function test_coupons_routes_forbidden_for_regular_user(): void
    {
        $this->actingAs($this->makeRegularUser());
        $coupon = $this->makeCoupon();

        $this->get('/admin/coupons')->assertForbidden();
        $this->get('/admin/coupons/create')->assertForbidden();
        $this->post('/admin/coupons', ['code' => 'NEW10', 'type' => 'percentage', 'value' => 10])->assertForbidden();
        $this->get("/admin/coupons/{$coupon->id}/edit")->assertForbidden();
        $this->put("/admin/coupons/{$coupon->id}", ['code' => 'NEW10', 'type' => 'percentage', 'value' => 10])->assertForbidden();
        $this->delete("/admin/coupons/{$coupon->id}", ['confirm_code' => 'SAVE10'])->assertForbidden();
    }

    public function test_coupons_read_routes_allow_admin(): void
    {
        $this->actingAs($this->makeAdmin());
        $coupon = $this->makeCoupon();

        $this->get('/admin/coupons')->assertOk()->assertSee('SAVE10');
        $this->get('/admin/coupons/create')->assertOk();
        $this->get("/admin/coupons/{$coupon->id}/edit")->assertOk();
    }

    public function test_coupons_store_allows_admin(): void
    {
        $this->actingAs($this->makeAdmin());

        $this->post('/admin/coupons', [
            'code' => 'WELCOME15',
            'type' => 'percentage',
            'value' => 15,
            'active' => 1,
        ])->assertRedirect(route('admin.coupons.index'));

        $this->assertDatabaseHas('coupons', [
            'code' => 'WELCOME15',
            'type' => 'percentage',
            'value' => 15.00,
        ]);
    }

    public function test_coupons_update_allows_admin(): void
    {
        $this->actingAs($this->makeAdmin());
        $coupon = $this->makeCoupon();

        $this->put("/admin/coupons/{$coupon->id}", [
            'code' => 'SAVE20',
            'type' => 'fixed',
            'value' => 20,
        ])->assertRedirect(route('admin.coupons.index'));

        $this->assertDatabaseHas('coupons', [
            'id' => $coupon->id,
            'code' => 'SAVE20',
            'type' => 'fixed',
            'value' => 20.00,
        ]);
    }

    public function test_coupons_destroy_allows_admin(): void
    {
        $this->actingAs($this->makeAdmin());
        $coupon = $this->makeCoupon();

        $this->delete("/admin/coupons/{$coupon->id}", ['confirm_code' => 'SAVE10'])
            ->assertRedirect(route('admin.coupons.index'));

        $this->assertDatabaseMissing('coupons', ['id' => $coupon->id]);
    }

    public function test_coupons_destroy_allows_super_admin(): void
    {
        $this->actingAs($this->makeSuperAdmin());
        $coupon = $this->makeCoupon();

        $this->delete("/admin/coupons/{$coupon->id}", ['confirm_code' => 'SAVE10'])
            ->assertRedirect(route('admin.coupons.index'));

        $this->assertDatabaseMissing('coupons', ['id' => $coupon->id]);
    }

    // ------------------------------------------------------------------ //
    // Reviews
    // ------------------------------------------------------------------ //

    public function test_reviews_routes_forbidden_for_regular_user(): void
    {
        $this->actingAs($this->makeRegularUser());
        $review = $this->makeReview();

        $this->get('/admin/reviews')->assertForbidden();
        $this->put("/admin/reviews/{$review->id}", ['approved' => 1])->assertForbidden();
        $this->delete("/admin/reviews/{$review->id}")->assertForbidden();
    }

    public function test_reviews_read_route_allows_admin(): void
    {
        $this->actingAs($this->makeAdmin());
        $this->makeReview();

        $this->get('/admin/reviews')->assertOk();
    }

    public function test_reviews_update_allows_admin(): void
    {
        $this->actingAs($this->makeAdmin());
        $review = $this->makeReview();

        $this->put("/admin/reviews/{$review->id}", ['approved' => 1])
            ->assertStatus(302);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'approved' => true,
        ]);
    }

    public function test_reviews_destroy_allows_admin(): void
    {
        $this->actingAs($this->makeAdmin());
        $review = $this->makeReview();

        $this->delete("/admin/reviews/{$review->id}")
            ->assertStatus(302);

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    // ------------------------------------------------------------------ //
    // Shipping methods (same: confirm_code validation was fixed here too)
    // ------------------------------------------------------------------ //

    public function test_shipping_methods_routes_forbidden_for_regular_user(): void
    {
        $this->actingAs($this->makeRegularUser());
        $method = $this->makeShippingMethod('Standard', 'STANDARD', 5.00);

        $this->get('/admin/shipping-methods')->assertForbidden();
        $this->get('/admin/shipping-methods/create')->assertForbidden();
        $this->post('/admin/shipping-methods', ['name' => 'X', 'code' => 'X', 'fee' => 1])->assertForbidden();
        $this->get("/admin/shipping-methods/{$method->id}/edit")->assertForbidden();
        $this->put("/admin/shipping-methods/{$method->id}", ['name' => 'X', 'code' => 'X', 'fee' => 1])->assertForbidden();
        $this->delete("/admin/shipping-methods/{$method->id}", ['confirm_code' => 'STANDARD'])->assertForbidden();
    }

    public function test_shipping_methods_destroy_allows_admin(): void
    {
        $this->actingAs($this->makeAdmin());
        $method = $this->makeShippingMethod('Express', 'EXPRESS', 12.50);

        $this->delete("/admin/shipping-methods/{$method->id}", ['confirm_code' => 'EXPRESS'])
            ->assertRedirect(route('admin.shipping-methods.index'));

        $this->assertDatabaseMissing('courier_shipping_methods', ['id' => $method->id]);
    }

    public function test_shipping_methods_destroy_allows_super_admin(): void
    {
        $this->actingAs($this->makeSuperAdmin());
        $method = $this->makeShippingMethod('Overnight', 'OVERNIGHT', 25.00);

        $this->delete("/admin/shipping-methods/{$method->id}", ['confirm_code' => 'OVERNIGHT'])
            ->assertRedirect(route('admin.shipping-methods.index'));

        $this->assertDatabaseMissing('courier_shipping_methods', ['id' => $method->id]);
    }
}

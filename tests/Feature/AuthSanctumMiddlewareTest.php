<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthSanctumMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Message returned by this app's custom AuthenticationException handler
     * in bootstrap/app.php for unauthenticated API requests.
     */
    private const UNAUTHENTICATED_MESSAGE = 'Unauthenticated. Please provide a valid authentication token.';

    /**
     * Create a user with a real Sanctum token (persisted in DB) and return both.
     *
     * A real token is used (rather than Sanctum::actingAs) because CartController
     * resolves the user from the bearer token via PersonalAccessToken::findToken(),
     * so the Authorization header must carry an actual token row.
     *
     * @return array{0: User, 1: string}
     */
    private function makeAuthenticatedUser(): array
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        return [$user, $token];
    }

    private function makeProduct(): Product
    {
        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'A test product',
            'price' => 49.99,
            'stock' => 10,
        ]);
    }

    // ------------------------------------------------------------------ //
    // Unauthenticated requests → 401
    // ------------------------------------------------------------------ //

    public function test_unauthenticated_cart_returns_401(): void
    {
        $this->getJson('/api/cart')->assertUnauthorized();
    }

    public function test_unauthenticated_add_to_cart_returns_401(): void
    {
        $this->postJson('/api/cart', ['product_id' => 1, 'quantity' => 1])
            ->assertUnauthorized();
    }

    public function test_unauthenticated_orders_returns_401(): void
    {
        $this->getJson('/api/orders')->assertUnauthorized();
    }

    public function test_unauthenticated_profile_returns_401(): void
    {
        $this->getJson('/api/profile')->assertUnauthorized();
    }

    public function test_unauthenticated_checkout_returns_401(): void
    {
        $this->postJson('/api/payment/stripe-session', [
            'address' => '123 Main St',
            'city' => 'Phnom Penh',
            'postal_code' => '12000',
            'phone' => '+85512345678',
        ])
            ->assertUnauthorized()
            ->assertJson(['message' => self::UNAUTHENTICATED_MESSAGE]);
    }

    public function test_unauthenticated_wishlist_returns_401(): void
    {
        $this->getJson('/api/wishlists')
            ->assertUnauthorized()
            ->assertJson(['message' => self::UNAUTHENTICATED_MESSAGE]);
    }

    public function test_unauthenticated_notifications_returns_401(): void
    {
        $this->getJson('/api/notifications')
            ->assertUnauthorized()
            ->assertJson(['message' => self::UNAUTHENTICATED_MESSAGE]);
    }

    // ------------------------------------------------------------------ //
    // Authenticated requests → 200 / 201
    // ------------------------------------------------------------------ //

    public function test_authenticated_profile_returns_200(): void
    {
        [$user, $token] = $this->makeAuthenticatedUser();

        $this->withToken($token)
            ->getJson('/api/profile')
            ->assertOk()
            ->assertJsonPath('id', $user->id)
            ->assertJsonPath('email', $user->email);
    }

    public function test_authenticated_cart_returns_200(): void
    {
        [, $token] = $this->makeAuthenticatedUser();

        $this->withToken($token)
            ->getJson('/api/cart')
            ->assertOk();
    }

    public function test_authenticated_add_to_cart_returns_201(): void
    {
        [, $token] = $this->makeAuthenticatedUser();
        $product = $this->makeProduct();

        $this->withToken($token)
            ->postJson('/api/cart', ['product_id' => $product->id, 'quantity' => 2])
            ->assertCreated()
            ->assertJsonPath('product_id', $product->id)
            ->assertJsonPath('quantity', 2);
    }

    public function test_authenticated_orders_returns_200(): void
    {
        [$user, $token] = $this->makeAuthenticatedUser();

        // A fresh user has no orders — the list should still respond 200 (empty page).
        $this->withToken($token)
            ->getJson('/api/orders')
            ->assertOk()
            ->assertJsonPath('total', 0);
    }

    public function test_authenticated_orders_shows_owned_orders_only(): void
    {
        [$user, $token] = $this->makeAuthenticatedUser();
        $other = User::factory()->create();

        // Order belonging to this user
        $user->orders()->create([
            'status' => 'pending',
            'total' => 99.98,
            'subtotal' => 99.98,
            'tax_amount' => 0,
            'shipping_amount' => 0,
        ]);

        // Order belonging to another user — must NOT appear
        $other->orders()->create([
            'status' => 'pending',
            'total' => 10.00,
            'subtotal' => 10.00,
            'tax_amount' => 0,
            'shipping_amount' => 0,
        ]);

        $this->withToken($token)
            ->getJson('/api/orders')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.user_id', $user->id);
    }

    // ------------------------------------------------------------------ //
    // Public routes must remain accessible without a token
    // ------------------------------------------------------------------ //

    public function test_public_product_listing_remains_accessible(): void
    {
        $this->getJson('/api/products')->assertOk();
    }

    public function test_invalid_token_returns_401(): void
    {
        $this->withToken('1|this-token-does-not-exist')
            ->getJson('/api/profile')
            ->assertUnauthorized();
    }
}

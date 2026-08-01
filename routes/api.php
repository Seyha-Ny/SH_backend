<?php

use App\Http\Controllers\Api\AuthController;
use App\Actions\SocialLoginAction;
use OpenApi\Annotations as OA;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\ShippingMethodController;
use App\Http\Controllers\Api\CdnController;
use App\Http\Controllers\Api\HealthController;
use Illuminate\Support\Facades\Route;

/**
 * @OA\Info(
 *     title="Ecommerce API",
 *     version="1.0.0",
 *     description="Ecommerce Backend API Documentation"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="apiKey",
 *     in="header",
 *     name="Authorization"
 * )
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Local development server"
 * )
 */

// ── Health check (monitoring tools need reliable access) ──
Route::get('health', HealthController::class)->middleware(['throttle:60,1', 'cache:private,0']);

// ── Public cacheable read-only endpoints ──
// These return data that changes infrequently. Cache-Control is set
// to public so CDNs and browsers can cache aggressively. The backend
// Redis cache handles first-misses; HTTP caching handles subsequent
// requests without hitting PHP at all.
Route::get('products/price-range', [ProductController::class, 'priceRange'])->middleware(['throttle:60,1', 'cache:public,300']);
Route::get('categories', [CategoryController::class, 'index'])->middleware(['throttle:60,1', 'cache:public,600']);
Route::get('categories/{category}', [CategoryController::class, 'show'])->middleware(['throttle:60,1', 'cache:public,600']);
Route::get('products', [ProductController::class, 'index'])->middleware(['throttle:60,1', 'cache:public,120']);
Route::get('products/{product}', [ProductController::class, 'show'])->middleware(['throttle:60,1', 'cache:public,120']);
Route::get('products/{product}/reviews', [ReviewController::class, 'index'])->middleware(['throttle:60,1', 'cache:public,60']);
Route::get('shipping-methods', [ShippingMethodController::class, 'index'])->middleware(['throttle:60,1', 'cache:public,600']);
Route::get('products/{product}/recommendations', [RecommendationController::class, 'forProduct'])->middleware(['throttle:60,1', 'cache:public,120']);

// ── Webhook (Stripe needs high limit) ──
Route::post('payment/stripe-webhook', [PaymentController::class, 'stripeWebhook'])->middleware('throttle:200,1');

// ── Coupon validation ──
Route::post('coupons/validate', [CouponController::class, 'validate'])->middleware('throttle:30,1');

// ── Auth routes (heavily rate-limited to prevent brute-force) ──
Route::post('register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('logout', [AuthController::class, 'logout'])->middleware('throttle:30,1');
Route::post('auth/social/callback', [AuthController::class, 'socialCallback'])->middleware('throttle:10,1');
Route::get('user', [AuthController::class, 'user'])->middleware('throttle:60,1');
Route::get('me', [AuthController::class, 'user'])->middleware('throttle:60,1');

// ── Profile (private, user-specific) ──
Route::get('profile', [ProfileController::class, 'show'])->middleware(['auth:sanctum', 'throttle:60,1', 'cache:private,30']);
Route::put('profile', [ProfileController::class, 'update'])->middleware(['auth:sanctum', 'throttle:30,1']);
Route::post('profile/avatar', [ProfileController::class, 'uploadAvatar'])->middleware(['auth:sanctum', 'throttle:10,1']);
Route::delete('profile/avatar', [ProfileController::class, 'deleteAvatar'])->middleware(['auth:sanctum', 'throttle:10,1']);
Route::put('change-password', [ProfileController::class, 'changePassword'])->middleware(['auth:sanctum', 'throttle:5,1']);

// ── Wishlist (private, user-specific) ──
Route::get('wishlists', [WishlistController::class, 'index'])->middleware(['auth:sanctum', 'throttle:60,1', 'cache:private,30']);
Route::post('wishlists', [WishlistController::class, 'store'])->middleware(['auth:sanctum', 'throttle:30,1']);
Route::delete('wishlists/{product}', [WishlistController::class, 'destroy'])->middleware(['auth:sanctum', 'throttle:30,1']);

// ── Cart (private, user-specific) ──
Route::get('cart', [CartController::class, 'index'])->middleware(['auth:sanctum', 'throttle:60,1', 'cache:private,30']);
Route::get('cart-simple', function () { return response()->json(['ok' => true]); })->middleware('throttle:60,1');
Route::post('cart', [CartController::class, 'store'])->middleware(['auth:sanctum', 'throttle:60,1']);
Route::put('cart/{cartItem}', [CartController::class, 'update'])->middleware(['auth:sanctum', 'throttle:60,1']);
Route::delete('cart/{cartItem}', [CartController::class, 'destroy'])->middleware(['auth:sanctum', 'throttle:60,1']);

// ── Orders (private, user-specific) ──
Route::get('orders', [OrderController::class, 'index'])->middleware(['auth:sanctum', 'throttle:30,1', 'cache:private,30']);
Route::get('orders/{order}', [OrderController::class, 'show'])->middleware(['auth:sanctum', 'throttle:30,1', 'cache:private,30']);
Route::get('orders/{order}/invoice', [OrderController::class, 'invoice'])->middleware(['auth:sanctum', 'throttle:30,1']);
Route::put('orders/{order}/cancel', [OrderController::class, 'requestCancel'])->middleware(['auth:sanctum', 'throttle:10,1']);
Route::put('orders/{order}/return', [OrderController::class, 'requestReturn'])->middleware(['auth:sanctum', 'throttle:10,1']);
Route::post('checkout', [OrderController::class, 'checkout'])->middleware(['auth:sanctum', 'throttle:10,1']);

// ── Payments (private — requires auth) ──
Route::post('payment/stripe-session', [PaymentController::class, 'stripeSession'])->middleware(['auth:sanctum', 'throttle:10,1']);

// ── Reviews (creating a review requires auth; reading is public) ──
Route::post('products/{product}/reviews', [ReviewController::class, 'store'])->middleware(['auth:sanctum', 'throttle:10,1']);

// ── Notifications (private, user-specific) ──
Route::get('notifications', [NotificationController::class, 'index'])->middleware(['auth:sanctum', 'throttle:60,1', 'cache:private,30']);
Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->middleware(['auth:sanctum', 'throttle:60,1', 'cache:private,30']);
Route::put('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->middleware(['auth:sanctum', 'throttle:60,1']);

// ── Recommendations (personalized — requires auth) ──
Route::get('recommendations', [RecommendationController::class, 'forUser'])->middleware(['auth:sanctum', 'throttle:30,1', 'cache:private,60']);

// ── CDN cache management (admin only) ──
// Requires CLOUDFLARE_API_TOKEN and CLOUDFLARE_ZONE_ID in .env
Route::post('cdn/purge', [CdnController::class, 'purge'])
    ->middleware(['auth:sanctum', 'admin', 'throttle:10,1']);

// ── Cart test ──
Route::get('cart-test', function (Illuminate\Http\Request $request) {
    return response()->json([
        'ok' => true,
        'has_sanctum_guard' => class_exists(\Laravel\Sanctum\HasApiTokens::class),
        'user_default' => $request->user()?->id,
        'user_sanctum' => method_exists($request, 'user') ? 'yes' : 'no',
        'guard' => auth()->getDefaultDriver(),
    ]);
})->middleware('throttle:30,1');

<?php

use App\Http\Controllers\Api\AuthController;
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

// Public routes
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{product}', [ProductController::class, 'show']);
Route::get('products/{product}/reviews', [ReviewController::class, 'index']);
Route::post('payment/stripe-webhook', [PaymentController::class, 'stripeWebhook']);
Route::post('coupons/validate', [CouponController::class, 'validate']);
Route::get('shipping-methods', [ShippingMethodController::class, 'index']);

// Recommendations
Route::get('products/{product}/recommendations', [RecommendationController::class, 'forProduct']);
Route::middleware('auth:sanctum')->get('recommendations', [RecommendationController::class, 'forUser']);

// Auth routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout']);
Route::get('user', [AuthController::class, 'user']);
Route::get('me', [AuthController::class, 'user']);

// Profile
Route::get('profile', [ProfileController::class, 'show']);
Route::put('profile', [ProfileController::class, 'update']);
Route::post('profile/avatar', [ProfileController::class, 'uploadAvatar']);
Route::delete('profile/avatar', [ProfileController::class, 'deleteAvatar']);
Route::put('change-password', [ProfileController::class, 'changePassword']);

// Wishlist
Route::get('wishlists', [WishlistController::class, 'index']);
Route::post('wishlists', [WishlistController::class, 'store']);
Route::delete('wishlists/{product}', [WishlistController::class, 'destroy']);

// Cart
Route::get('cart', [CartController::class, 'index']);
Route::get('cart-simple', function () { return response()->json(['ok' => true]); });
Route::post('cart', [CartController::class, 'store']);
Route::put('cart/{cartItem}', [CartController::class, 'update']);
Route::delete('cart/{cartItem}', [CartController::class, 'destroy']);

// Orders
Route::get('orders', [OrderController::class, 'index']);
Route::get('orders/{order}', [OrderController::class, 'show']);
Route::get('orders/{order}/invoice', [OrderController::class, 'invoice']);
Route::put('orders/{order}/cancel', [OrderController::class, 'requestCancel']);
Route::put('orders/{order}/return', [OrderController::class, 'requestReturn']);
Route::post('checkout', [OrderController::class, 'checkout']);

// Payments
Route::post('payment/stripe-session', [PaymentController::class, 'stripeSession']);

// Reviews
Route::post('products/{product}/reviews', [ReviewController::class, 'store']);

// Notifications
Route::get('notifications', [NotificationController::class, 'index']);
Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
Route::put('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
Route::get('cart-test', function (Illuminate\Http\Request $request) {
    $user = $request->user('sanctum');
    return response()->json(['user' => $user?->id]);
});

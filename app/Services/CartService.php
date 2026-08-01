<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class CartService
{
    /**
     * Get all cart items for a user with product details, cached briefly.
     *
     * Cart changes frequently (add/remove/update), so the TTL is short
     * at 60 seconds. This absorbs rapid page refreshes while ensuring
     * the user always sees near-realtime cart contents.
     */
    public function getUserCart(User $user): iterable
    {
        $cacheKey = "cart.user.{$user->id}";

        return Cache::remember($cacheKey, now()->addSeconds(60), function () use ($user) {
            return $user->cartItems()->with('product')->get();
        });
    }

    /**
     * Add a product to the cart (or update quantity if already in cart).
     */
    public function addItem(User $user, int $productId, int $quantity): CartItem
    {
        $product = Product::where('id', $productId)
            ->where('stock', '>', 0)
            ->firstOrFail();

        $cartItem = CartItem::updateOrCreate(
            ['user_id' => $user->id, 'product_id' => $product->id],
            ['quantity' => min($quantity, $product->stock)]
        );

        Cache::forget("cart.user.{$user->id}");

        return $cartItem->load('product');
    }

    /**
     * Update cart item quantity with stock validation.
     */
    public function updateItem(User $user, CartItem $cartItem, int $quantity): CartItem
    {
        if ($cartItem->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }

        $product = $cartItem->product;

        if (! $product || $product->stock <= 0) {
            throw new InsufficientStockException($product?->name ?? 'Product');
        }

        if ($quantity > $product->stock) {
            throw new InsufficientStockException(
                $product->name,
                $product->stock,
                $quantity
            );
        }

        $cartItem->update(['quantity' => $quantity]);

        Cache::forget("cart.user.{$user->id}");

        return $cartItem->fresh()->load('product');
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(User $user, CartItem $cartItem): void
    {
        if ($cartItem->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }

        $cartItem->delete();
    }

    /**
     * Clear all items from the user's cart.
     */
    public function clearCart(User $user): void
    {
        CartItem::where('user_id', $user->id)->delete();
    }

    /**
     * Get cart items and validate stock for all items.
     *
     * @throws InsufficientStockException
     */
    public function getValidatedCartItems(User $user): iterable
    {
        $cartItems = $user->cartItems()->with('product')->get();

        foreach ($cartItems as $item) {
            if ($item->product->stock < $item->quantity) {
                throw new InsufficientStockException(
                    $item->product->name,
                    $item->product->stock,
                    $item->quantity
                );
            }
        }

        return $cartItems;
    }

    /**
     * Calculate cart subtotal.
     */
    public function calculateSubtotal(iterable $cartItems): float
    {
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item->product->price * $item->quantity;
        }
        return round($subtotal, 2);
    }
}

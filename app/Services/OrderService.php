<?php

namespace App\Services;

use App\Events\OrderPlaced;
use App\Events\OrderStatusChanged;
use App\Exceptions\CartEmptyException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\OrderNotCancellableException;
use App\Exceptions\OrderNotEligibleForReturnException;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Get paginated orders for a user, cached briefly.
     *
     * Orders don't change often — a user browses their list, may refresh
     * a few times. The 5-minute cache absorbs those repeated loads.
     * Cache is busted when an order is placed or status changes.
     */
    public function getUserOrders(User $user, ?string $status = null, int $perPage = 10): LengthAwarePaginator
    {
        // Tagged cache — busting all variants at once via tag flush
        // Falls back to a plain cache when the driver doesn't support tags (e.g. file).
        $tag = "orders.user.{$user->id}";
        $cacheKey = $tag . '.' . md5(serialize([
            'status' => $status,
            'per_page' => $perPage,
            'page' => (int) request()->integer('page', 1),
        ]));

        return $this->rememberWithTag($tag, $cacheKey, now()->addMinutes(5), function () use ($user, $status, $perPage) {
            $query = $user->orders()->with('items.product', 'shippingMethod')->orderByDesc('created_at');

            if ($status) {
                $query->where('status', $status);
            }

            return $query->paginate(min($perPage, 100));
        });
    }

    /**
     * Get a single order with relationships, cached briefly.
     */
    public function getOrder(User $user, Order $order): Order
    {
        if ($order->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }

        $tag = "orders.user.{$user->id}";
        $cacheKey = "{$tag}.detail.{$order->id}";

        return $this->rememberWithTag($tag, $cacheKey, now()->addMinutes(3), function () use ($order) {
            return $order->load('items.product', 'shippingMethod');
        });
    }

    /**
     * Process checkout - create order from cart items.
     */
    public function checkout(
        User $user,
        ?int $shippingMethodId = null,
        ?string $couponCode = null
    ): Order {
        $cartItems = $user->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            throw new CartEmptyException();
        }

        // Validate stock for all items
        foreach ($cartItems as $item) {
            if ($item->product->stock < $item->quantity) {
                throw new InsufficientStockException(
                    $item->product->name,
                    $item->product->stock,
                    $item->quantity
                );
            }
        }

        $selectedShippingMethod = $shippingMethodId
            ? ShippingMethod::find($shippingMethodId)
            : null;

        $shippingFee = $selectedShippingMethod ? (float) $selectedShippingMethod->fee : 0;
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item->product->price * $item->quantity;
        }

        $taxRate = (float) config('services.tax.rate', 0);
        $taxAmount = round($subtotal * $taxRate, 2);

        // Process coupon
        $discountAmount = 0.0;
        $discountType = null;
        $discountValue = null;
        $appliedCouponCode = null;

        if ($couponCode) {
            $coupon = Coupon::where('code', strtoupper(trim($couponCode)))->first();
            if ($coupon && $coupon->isActiveNow() && (is_null($coupon->max_uses) || $coupon->used_count < $coupon->max_uses)) {
                $discountType = $coupon->type;
                $discountValue = (float) $coupon->value;

                if ($discountType === 'percentage') {
                    $discountAmount = round($subtotal * ($discountValue / 100), 2);
                } else {
                    $discountAmount = min($discountValue, $subtotal);
                }

                $appliedCouponCode = $coupon->code;
            }
        }

        $total = round(max(0, $subtotal + $shippingFee + $taxAmount - $discountAmount), 2);

        $order = DB::transaction(function () use ($user, $cartItems, $total, $subtotal, $taxAmount, $shippingFee, $selectedShippingMethod, $appliedCouponCode, $discountType, $discountAmount, $discountValue) {
            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'total' => $total,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'shipping_amount' => $shippingFee,
                'shipping_method_id' => $selectedShippingMethod?->id,
            ]);

            foreach ($cartItems as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);

                $item->product->decrement('stock', $item->quantity);
            }

            // Apply coupon usage
            if ($appliedCouponCode) {
                $order->update([
                    'coupon_code' => $appliedCouponCode,
                    'discount_type' => $discountType,
                    'discount_amount' => $discountAmount,
                    'discount_value' => $discountValue,
                ]);

                Coupon::where('code', $appliedCouponCode)->increment('used_count');
            }

            CartItem::where('user_id', $user->id)->delete();

            return $order;
        });

        $order->load('items.product', 'shippingMethod');

        // Bust user orders cache
        $this->bustUserOrdersCache($user);

        // Dispatch event
        event(new OrderPlaced($order));

        return $order;
    }

    /**
     * Request order cancellation.
     */
    public function requestCancel(User $user, Order $order, ?string $reason = null): Order
    {
        if ($order->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }

        if (! in_array($order->status, ['pending', 'processing'], true)) {
            throw new OrderNotCancellableException($order->status);
        }

        $order->update(['status' => 'cancellation_requested']);

        $this->bustUserOrderDetailCache($user, $order);

        event(new OrderStatusChanged($order, 'cancellation_requested', $reason));

        return $order->fresh();
    }

    /**
     * Request order return.
     */
    public function requestReturn(User $user, Order $order, ?string $reason = null): Order
    {
        if ($order->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }

        if (! in_array($order->status, ['completed', 'processing'], true)) {
            throw new OrderNotEligibleForReturnException($order->status);
        }

        $order->update(['status' => 'return_requested']);

        $this->bustUserOrderDetailCache($user, $order);

        event(new OrderStatusChanged($order, 'return_requested', $reason));

        return $order->fresh();
    }

    // ------------------------------------------------------------------ //
    // Cache helpers
    // ------------------------------------------------------------------ //

    /**
     * Remember a cache entry using tags when the driver supports them,
     * otherwise fall back to a plain (untagged) cache entry.
     */
    protected function rememberWithTag(string $tag, string $key, $ttl, \Closure $callback): mixed
    {
        try {
            return Cache::tags([$tag])->remember($key, $ttl, $callback);
        } catch (\BadMethodCallException) {
            return Cache::remember($key, $ttl, $callback);
        }
    }

    /**
     * Bust all cached order data for this user — list pages and detail pages.
     *
     * Uses tag-based flushing which Redis supports.
     * Falls back to no-op on cache drivers that don't support tags (TTL expires naturally).
     */
    protected function bustUserOrdersCache(User $user): void
    {
        try {
            Cache::tags(["orders.user.{$user->id}"])->flush();
        } catch (\BadMethodCallException) {
            // Cache driver doesn't support tags; TTL will expire naturally
        }
    }

    /**
     * Bust cached order detail for a specific order.
     */
    protected function bustUserOrderDetailCache(User $user, Order $order): void
    {
        $this->bustUserOrdersCache($user);
    }
}

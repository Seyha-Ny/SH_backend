<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;

class PaymentController extends Controller
{
    /**
     * @OA\Post(
 *     path="/api/payment/stripe-webhook",
 *     summary="Stripe webhook handler",
 *     tags={"Payments"},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Stripe webhook payload",
 *         @OA\JsonContent()
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Webhook handled",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Webhook handled")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid signature"
 *     )
 * )
     */
    /**
     * @OA\Post(
     *     path="/api/payment/stripe-session",
     *     summary="Create Stripe checkout session",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"address","city","postal_code","phone"},
     *             @OA\Property(property="address", type="string", example="123 Main St"),
     *             @OA\Property(property="city", type="string", example="New York"),
     *             @OA\Property(property="postal_code", type="string", example="10001"),
     *             @OA\Property(property="phone", type="string", example="+1234567890"),
     *             @OA\Property(property="shipping_method_id", type="integer", nullable=true, example=1),
     *             @OA\Property(property="coupon_code", type="string", nullable=true, example="SUMMER2024")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stripe session created",
     *         @OA\JsonContent(
     *             @OA\Property(property="url", type="string", example="https://checkout.stripe.com/...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Cart is empty"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or insufficient stock"
     *     )
     * )
     */
    public function stripeSession(Request $request): JsonResponse
    {
        $request->validate([
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'phone' => 'required|string|max:20',
            'shipping_method_id' => ['nullable', 'integer', 'exists:courier_shipping_methods,id'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ]);

        $user = $request->user();
        $cartItems = $user->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        foreach ($cartItems as $item) {
            if ($item->product->stock < $item->quantity) {
                return response()->json([
                    'message' => 'Insufficient stock for ' . $item->product->name,
                ], 422);
            }
        }

        $shipping = [
            'address' => $request->input('address'),
            'city' => $request->input('city'),
            'postal_code' => $request->input('postal_code'),
            'phone' => $request->input('phone'),
        ];

        $coupon = null;
        $couponCode = null;
        $discountAmount = 0.0;

        if ($request->filled('coupon_code')) {
            $couponCode = strtoupper(trim($request->input('coupon_code')));
            $coupon = \App\Models\Coupon::where('code', $couponCode)->first();

            if (! $coupon || ! $coupon->active) {
                return response()->json(['message' => 'Coupon is invalid or expired.'], 422);
            }

            if ($coupon->starts_at && now()->lt($coupon->starts_at)) {
                return response()->json(['message' => 'Coupon is not active yet.'], 422);
            }

            if ($coupon->ends_at && now()->gt($coupon->ends_at)) {
                return response()->json(['message' => 'Coupon has expired.'], 422);
            }

            if (! is_null($coupon->max_uses) && $coupon->used_count >= $coupon->max_uses) {
                return response()->json(['message' => 'Coupon usage limit reached.'], 422);
            }
        }

        $order = DB::transaction(function () use ($user, $cartItems, $shipping, $coupon) {
            $subtotal = $cartItems->sum(function ($item) {
                return $item->product->price * $item->quantity;
            });

            $selectedShippingMethod = \App\Models\ShippingMethod::find($request->input('shipping_method_id'));
            $shippingFee = $selectedShippingMethod ? (float) $selectedShippingMethod->fee : (float) config('services.shipping.fee', 0);
            $taxRate = (float) config('services.tax.rate', 0);
            $taxAmount = round($subtotal * $taxRate, 2);

            $discountAmount = 0.0;
            $discountType = null;
            $discountValue = null;

            if ($coupon) {
                $minOrder = (float) ($coupon->min_order_amount ?? 0);
                if ($minOrder > 0 && $subtotal < $minOrder) {
                    throw new \RuntimeException('Coupon requires a minimum order amount of ' . number_format($minOrder, 2));
                }

                $discountType = $coupon->type;
                $discountValue = (float) $coupon->value;

                if ($discountType === 'percentage') {
                    $discountAmount = round($subtotal * ($discountValue / 100), 2);
                } else {
                    $discountAmount = min($discountValue, $subtotal);
                }
            }

            $total = round(max(0, $subtotal + $shippingFee + $taxAmount - $discountAmount), 2);
            $couponCode = $coupon?->code;

            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'total' => $total,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'shipping_amount' => $shippingFee,
                'shipping_method_id' => $selectedShippingMethod?->id,
                'payment_status' => 'pending_payment',
                'payment_method' => 'stripe',
                'shipping_address' => $shipping['address'],
                'shipping_city' => $shipping['city'],
                'shipping_postal_code' => $shipping['postal_code'],
                'shipping_phone' => $shipping['phone'],
                'coupon_code' => $couponCode,
                'discount_type' => $discountType,
                'discount_amount' => $discountAmount,
                'discount_value' => $discountValue,
            ]);

            foreach ($cartItems as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);

                $item->product->decrement('stock', $item->quantity);
            }

            CartItem::where('user_id', $user->id)->delete();

            if ($coupon) {
                $coupon->increment('used_count');
            }

            return $order;
        });

        Stripe::setApiKey(config('services.stripe.secret'));

        $lineItems = $order->items()->with('product')->get()->map(function ($item) {
            return [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $item->product->name,
                    ],
                    'unit_amount' => (int) round($item->price * 100),
                ],
                'quantity' => $item->quantity,
            ];
        })->toArray();

        $shippingFee = (float) ($order->shipping_amount ?? 0);
        if ($shippingFee > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Shipping',
                    ],
                    'unit_amount' => (int) round($shippingFee * 100),
                ],
                'quantity' => 1,
            ];
        }

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => url('/checkout/success?session_id={CHECKOUT_SESSION_ID}'),
            'cancel_url' => url('/checkout/cancel?order_id=' . $order->id),
            'metadata' => [
                'order_id' => $order->id,
                'user_id' => $user->id,
            ],
        ]);

        $order->update(['stripe_session_id' => $session->id]);

        return response()->json(['url' => $session->url]);
    }

    /**
     * @OA\Post(
 *     path="/api/payment/stripe-webhook",
 *     summary="Stripe webhook handler",
 *     tags={"Payments"},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Stripe webhook payload",
 *         @OA\JsonContent()
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Webhook handled",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Webhook handled")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid signature"
 *     )
 * )
     */
    public function stripeWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed.', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $session = $event->data['object'] ?? [];

        if ($event->type === 'checkout.session.completed') {
            $orderId = $session['metadata']['order_id'] ?? null;
            if ($orderId) {
                $order = Order::find($orderId);
                if ($order) {
                    $order->update([
                        'payment_status' => 'paid',
                        'status' => 'processing',
                    ]);

                    if ($order->user) {
                        $order->user->notifications()->create([
                            'title' => 'Payment successful',
                            'message' => "Your order #{$order->getKey()} payment was successful.",
                        ]);
                    }

                    $this->notifyTelegram($order, 'paid');
                }
            }
        } elseif ($event->type === 'checkout.session.async_payment_succeeded') {
            $orderId = $session['metadata']['order_id'] ?? null;
            if ($orderId) {
                $order = Order::find($orderId);
                if ($order) {
                    $order->update(['payment_status' => 'paid']);

                    $this->notifyTelegram($order, 'paid');
                }
            }
        } elseif ($event->type === 'checkout.session.async_payment_failed') {
            $orderId = $session['metadata']['order_id'] ?? null;
            if ($orderId) {
                $order = Order::find($orderId);
                if ($order) {
                    $order->update(['payment_status' => 'failed']);

                    if ($order->user) {
                        $order->user->notifications()->create([
                            'title' => 'Payment failed',
                            'message' => "Your order #{$order->getKey()} payment failed.",
                        ]);
                    }

                    $this->notifyTelegram($order, 'failed');
                }
            }
        } elseif (in_array($event->type, ['checkout.session.expired', 'checkout.session.canceled'], true)) {
            $orderId = $session['metadata']['order_id'] ?? null;
            if ($orderId) {
                $order = Order::find($orderId);
                if ($order) {
                    $order->update(['payment_status' => 'canceled', 'status' => 'canceled']);
                    $order->items()->each(function ($item) {
                        $item->product->increment('stock', $item->quantity);
                    });

                    if ($order->user) {
                        $order->user->notifications()->create([
                            'title' => 'Order canceled',
                            'message' => "Your order #{$order->getKey()} was canceled.",
                        ]);
                    }

                    $this->notifyTelegram($order, 'canceled');
                }
            }
        }

        return response()->json(['message' => 'Webhook handled']);
    }

    private function notifyTelegram(Order $order, string $paymentStatus): void
    {
        $token = config('services.telegram.bot_token');
        $adminChatId = config('services.telegram.chat_id');
        $customerChatId = $order->user?->telegram_chat_id;

        if (! $token) {
            return;
        }

        $customerName = $order->user?->name ?? 'Guest';
        $total = number_format($order->total, 2);
        $orderId = $order->getKey();

        $text = "🛒 Order #{$orderId}\nCustomer: {$customerName}\nTotal: \${$total}\nPayment: {$paymentStatus}";

        if ($adminChatId) {
            $this->sendTelegramMessage($token, (string) $adminChatId, $text);
        }

        if ($customerChatId) {
            $this->sendTelegramMessage($token, (string) $customerChatId, $text);
        }
    }

    private function sendTelegramMessage(string $token, string $chatId, string $text): void
    {
        try {
            Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Telegram notification failed.', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}

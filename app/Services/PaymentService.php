<?php

namespace App\Services;

use App\Events\OrderStatusChanged;
use App\Exceptions\PaymentFailedException;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;

class PaymentService
{
    /**
     * Create a Stripe checkout session for an order.
     */
    public function createStripeSession(Order $order): ?Session
    {
        $stripeSecret = config('services.stripe.secret');

        if (! $stripeSecret || str_starts_with($stripeSecret, 'your_')) {
            return null;
        }

        Stripe::setApiKey($stripeSecret);

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
                    'product_data' => ['name' => 'Shipping'],
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
                'user_id' => $order->user_id,
            ],
        ]);

        $order->update(['stripe_session_id' => $session->id]);

        return $session;
    }

    /**
     * Handle Stripe webhook event.
     */
    public function handleWebhook(string $payload, string $sigHeader): void
    {
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed.', ['error' => $e->getMessage()]);
            throw $e;
        }

        $session = $event->data['object'] ?? [];
        $orderId = $session['metadata']['order_id'] ?? null;

        if (! $orderId) {
            return;
        }

        $order = Order::find($orderId);
        if (! $order) {
            return;
        }

        match ($event->type) {
            'checkout.session.completed' => $this->handlePaymentCompleted($order, $session),
            'checkout.session.async_payment_succeeded' => $this->handlePaymentCompleted($order, $session),
            'checkout.session.async_payment_failed' => $this->handlePaymentFailed($order),
            'checkout.session.expired', 'checkout.session.canceled' => $this->handlePaymentCanceled($order),
            default => null,
        };
    }

    /**
     * Process a refund for an order.
     */
    public function processRefund(Order $order): bool
    {
        $stripeSecret = config('services.stripe.secret');

        if (! $stripeSecret || str_starts_with($stripeSecret, 'your_')) {
            Log::warning('Stripe not configured for refund.', ['order_id' => $order->id]);
            return false;
        }

        try {
            Stripe::setApiKey($stripeSecret);

            $paymentIntentId = $order->stripe_payment_intent_id ?? null;
            if (! $paymentIntentId) {
                Log::warning('No payment intent ID for refund.', ['order_id' => $order->id]);
                return false;
            }

            $refund = \Stripe\Refund::create([
                'payment_intent' => $paymentIntentId,
                'amount' => (int) round($order->total * 100),
            ]);

            Log::info('Refund processed.', [
                'order_id' => $order->id,
                'refund_id' => $refund->id,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Refund failed.', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            throw new PaymentFailedException('Refund processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Send Telegram notification about order.
     */
    public function sendTelegramNotification(Order $order, string $paymentStatus): void
    {
        $customerName = $order->user?->name ?? 'Guest';
        $total = number_format($order->total, 2);
        $orderId = $order->getKey();

        $text = "🛒 Order #{$orderId}\nCustomer: {$customerName}\nTotal: \${$total}\nPayment: {$paymentStatus}";

        app(TelegramService::class)->sendToAdminChat($text);
        app(TelegramService::class)->sendToChat($order->user?->telegram_chat_id, $text);
    }

    private function handlePaymentCompleted(Order $order, array $session): void
    {
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

        $this->sendTelegramNotification($order, 'paid');
        event(new OrderStatusChanged($order, 'processing'));
    }

    private function handlePaymentFailed(Order $order): void
    {
        $order->update(['payment_status' => 'failed']);

        if ($order->user) {
            $order->user->notifications()->create([
                'title' => 'Payment failed',
                'message' => "Your order #{$order->getKey()} payment failed.",
            ]);
        }

        $this->sendTelegramNotification($order, 'failed');
    }

    private function handlePaymentCanceled(Order $order): void
    {
        $order->update(['payment_status' => 'canceled', 'status' => 'canceled']);

        // Restore stock
        $order->items()->each(function ($item) {
            $item->product->increment('stock', $item->quantity);
        });

        if ($order->user) {
            $order->user->notifications()->create([
                'title' => 'Order canceled',
                'message' => "Your order #{$order->getKey()} was canceled.",
            ]);
        }

        $this->sendTelegramNotification($order, 'canceled');
    }

}

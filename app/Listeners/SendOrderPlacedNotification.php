<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Jobs\GenerateInvoice;
use App\Jobs\SendOrderConfirmationEmail;
use Illuminate\Support\Facades\Log;

class SendOrderPlacedNotification
{
    /**
     * Handle the event.
     */
    public function handle(OrderPlaced $event): void
    {
        $order = $event->order;

        // Send order confirmation email via queue
        SendOrderConfirmationEmail::dispatch($order);

        // Generate invoice PDF via queue
        GenerateInvoice::dispatch($order);

        // Create in-app notification
        if ($order->user) {
            $order->user->notifications()->create([
                'title' => 'Order placed',
                'message' => "Your order #{$order->getKey()} has been placed successfully.",
            ]);
        }

        Log::info('Order placed event handled.', [
            'order_id' => $order->id,
        ]);
    }
}

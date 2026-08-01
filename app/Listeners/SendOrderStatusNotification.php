<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Mail\OrderCancellationRequested;
use App\Mail\OrderReturnRequested;
use App\Mail\OrderStatusUpdated;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusNotification
{
    /**
     * Handle the event.
     */
    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order;
        $newStatus = $event->newStatus;
        $reason = $event->reason;

        // Send email notification based on status
        if ($order->user && filter_var($order->user->email, FILTER_VALIDATE_EMAIL)) {
            try {
                match ($newStatus) {
                    'cancellation_requested' => Mail::to($order->user->email)
                        ->send(new OrderCancellationRequested($order, $reason)),
                    'return_requested' => Mail::to($order->user->email)
                        ->send(new OrderReturnRequested($order, $reason)),
                    default => Mail::to($order->user->email)
                        ->send(new OrderStatusUpdated($order)),
                };
            } catch (\Throwable $e) {
                Log::warning('Failed to send status notification email.', [
                    'order_id' => $order->id,
                    'status' => $newStatus,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Create in-app notification
        if ($order->user) {
            $statusLabels = [
                'cancellation_requested' => 'Cancellation requested',
                'return_requested' => 'Return requested',
                'processing' => 'Processing',
                'shipped' => 'Shipped',
                'delivered' => 'Delivered',
                'completed' => 'Completed',
                'canceled' => 'Canceled',
            ];

            $label = $statusLabels[$newStatus] ?? ucfirst($newStatus);

            $order->user->notifications()->create([
                'title' => "Order {$label}",
                'message' => "Your order #{$order->getKey()} status has been updated to: {$label}.",
            ]);
        }

        Log::info('Order status changed event handled.', [
            'order_id' => $order->id,
            'new_status' => $newStatus,
        ]);
    }
}

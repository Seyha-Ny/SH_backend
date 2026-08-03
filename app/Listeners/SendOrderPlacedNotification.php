<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Jobs\GenerateInvoice;
use App\Jobs\SendOrderConfirmationEmail;
use App\Models\User;
use App\Services\TelegramService;
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

        // Create in-app notification for the customer
        if ($order->user) {
            $order->user->notifications()->create([
                'title' => 'Order placed',
                'message' => "Your order #{$order->getKey()} has been placed successfully.",
            ]);
        }

        $this->notifyAdmins($event);

        Log::info('Order placed event handled.', [
            'order_id' => $order->id,
        ]);
    }

    /**
     * Alert every admin about the new order — both via an in-app
     * notification (shown in the admin panel bell) and, when a bot token is
     * configured, a Telegram message to the admin chat.
     */
    private function notifyAdmins(OrderPlaced $event): void
    {
        $order = $event->order;
        $customerName = $order->user?->name ?? 'Guest';
        $total = number_format((float) $order->total, 2);
        $orderId = $order->getKey();

        $admins = User::query()
            ->where('is_admin', true)
            ->whereIn('role', ['admin', 'super_admin'])
            ->get();

        foreach ($admins as $admin) {
            $admin->notifications()->create([
                'title' => 'New order placed',
                'message' => "New order #{$orderId} from {$customerName} — \${$total}.",
                'action_url' => route('admin.orders.show', $orderId),
            ]);
        }

        app(TelegramService::class)->sendToAdminChat(
            "🛒 New order #{$orderId}\nCustomer: {$customerName}\nTotal: \${$total}"
        );
    }
}

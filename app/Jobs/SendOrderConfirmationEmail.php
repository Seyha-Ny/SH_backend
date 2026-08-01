<?php

namespace App\Jobs;

use App\Mail\OrderStatusUpdated;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Order $order;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public int $maxExceptions = 2;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = $this->order->user;

        if (! $user || ! filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            Log::warning('Cannot send order confirmation email: invalid email.', [
                'order_id' => $this->order->id,
                'user_id' => $this->order->user_id,
            ]);
            return;
        }

        try {
            Mail::to($user->email)->send(new OrderStatusUpdated($this->order));
            Log::info('Order confirmation email sent.', [
                'order_id' => $this->order->id,
                'email' => $user->email,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send order confirmation email.', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
            ]);
            throw $e; // Re-throw for retry
        }
    }
}

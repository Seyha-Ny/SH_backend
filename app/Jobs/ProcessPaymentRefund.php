<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPaymentRefund implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Order $order;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(PaymentService $paymentService): void
    {
        try {
            $result = $paymentService->processRefund($this->order);

            if ($result) {
                $this->order->update(['payment_status' => 'refunded']);

                Log::info('Payment refund processed successfully.', [
                    'order_id' => $this->order->id,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Payment refund job failed.', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

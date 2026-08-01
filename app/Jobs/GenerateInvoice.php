<?php

namespace App\Jobs;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateInvoice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Order $order;

    public int $tries = 2;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->order->load('items.product', 'shippingMethod', 'user');

            $pdf = Pdf::loadView('invoices.show', [
                'order' => $this->order,
            ]);

            $filename = "invoices/order-{$this->order->id}.pdf";

            Storage::disk('public')->put($filename, $pdf->output());

            Log::info('Invoice generated.', [
                'order_id' => $this->order->id,
                'file' => $filename,
            ]);
        } catch (\Throwable $e) {
            Log::error('Invoice generation failed.', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

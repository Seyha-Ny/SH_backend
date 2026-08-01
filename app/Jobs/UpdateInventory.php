<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateInventory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Order $order;

    public string $action; // 'add' or 'remove'

    public int $tries = 2;

    /**
     * Create a new job instance.
     */
    public function __construct(Order $order, string $action = 'remove')
    {
        $this->order = $order;
        $this->action = $action;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->order->load('items.product');

            foreach ($this->order->items as $item) {
                $product = $item->product;

                if (! $product) {
                    continue;
                }

                if ($this->action === 'remove') {
                    $product->decrement('stock', $item->quantity);

                    Log::info('Inventory decremented.', [
                        'product_id' => $product->id,
                        'quantity' => $item->quantity,
                        'order_id' => $this->order->id,
                    ]);
                } elseif ($this->action === 'add') {
                    $product->increment('stock', $item->quantity);

                    Log::info('Inventory incremented (restored).', [
                        'product_id' => $product->id,
                        'quantity' => $item->quantity,
                        'order_id' => $this->order->id,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Inventory update failed.', [
                'order_id' => $this->order->id,
                'action' => $this->action,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

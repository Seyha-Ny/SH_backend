<?php

namespace Database\Seeders;

use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class ShippingMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'name' => 'Standard Shipping',
                'courier' => 'Post',
                'code' => 'standard',
                'fee' => 3.00,
                'description' => 'Affordable delivery via national postal service. Tracked within Cambodia.',
                'active' => true,
                'estimated_days_min' => 3,
                'estimated_days_max' => 7,
            ],
            [
                'name' => 'Express Shipping',
                'courier' => 'DHL',
                'code' => 'express',
                'fee' => 12.00,
                'description' => 'Fast, fully-tracked international courier delivery for time-sensitive orders.',
                'active' => true,
                'estimated_days_min' => 1,
                'estimated_days_max' => 3,
            ],
        ];

        foreach ($methods as $method) {
            // firstOrCreate on the unique code keeps re-seeding idempotent:
            // an existing method's fee, description or active flag (which an
            // admin may have customized in the panel) is never overwritten.
            ShippingMethod::firstOrCreate(['code' => $method['code']], $method);
        }

        // Bust the 6-hour controller cache so the API serves new methods immediately.
        Cache::forget('shipping_methods.active');
    }
}

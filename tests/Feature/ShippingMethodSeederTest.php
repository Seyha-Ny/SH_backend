<?php

namespace Tests\Feature;

use App\Models\ShippingMethod;
use Database\Seeders\ShippingMethodSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingMethodSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_run_creates_default_methods(): void
    {
        $this->assertDatabaseCount('courier_shipping_methods', 0);

        $this->seed(ShippingMethodSeeder::class);

        $this->assertDatabaseCount('courier_shipping_methods', 2);
        $this->assertDatabaseHas('courier_shipping_methods', ['code' => 'standard']);
        $this->assertDatabaseHas('courier_shipping_methods', ['code' => 'express']);
    }

    public function test_customized_method_fee_and_active_flag_survive_re_seed(): void
    {
        // Admin customized the Standard method in the panel — re-seeding must
        // NOT overwrite the fee or re-enable a deliberately deactivated method.
        ShippingMethod::create([
            'name' => 'Standard Shipping',
            'courier' => 'Post',
            'code' => 'standard',
            'fee' => 5.50,
            'description' => 'Customized description',
            'active' => false,
            'estimated_days_min' => 4,
            'estimated_days_max' => 10,
        ]);

        $this->seed(ShippingMethodSeeder::class);

        $standard = ShippingMethod::where('code', 'standard')->first();

        $this->assertNotNull($standard);
        $this->assertSame('5.50', (string) $standard->fee);
        $this->assertFalse($standard->active);
        $this->assertSame('Customized description', $standard->description);

        // The other method is still seeded in.
        $this->assertDatabaseCount('courier_shipping_methods', 2);
        $this->assertDatabaseHas('courier_shipping_methods', ['code' => 'express']);
    }

    public function test_re_seed_is_idempotent(): void
    {
        $this->seed(ShippingMethodSeeder::class);
        $this->seed(ShippingMethodSeeder::class);

        $this->assertDatabaseCount('courier_shipping_methods', 2);
    }
}

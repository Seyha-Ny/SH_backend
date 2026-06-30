<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('stripe_session_id')->nullable()->after('total');
            $table->string('payment_status')->default('pending_payment')->after('stripe_session_id');
            $table->string('payment_method')->nullable()->after('payment_status');
            $table->string('shipping_address')->nullable()->after('payment_method');
            $table->string('shipping_city')->nullable()->after('shipping_address');
            $table->string('shipping_postal_code')->nullable()->after('shipping_city');
            $table->string('shipping_phone')->nullable()->after('shipping_postal_code');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_session_id',
                'payment_status',
                'payment_method',
                'shipping_address',
                'shipping_city',
                'shipping_postal_code',
                'shipping_phone',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('subtotal', 12, 2)->after('total');
            $table->decimal('tax_amount', 12, 2)->default(0)->after('subtotal');
            $table->decimal('shipping_amount', 12, 2)->default(0)->after('tax_amount');
            $table->decimal('total', 12, 2)->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'tax_amount', 'shipping_amount']);
        });
    }
};

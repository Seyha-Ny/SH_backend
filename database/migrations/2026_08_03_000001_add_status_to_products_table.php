<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The admin product form has had Status (active/inactive) and SKU fields
     * since the start, but the products table never grew those columns — so
     * the values were silently dropped, every product rendered as "inactive"
     * in the admin table, and SKUs were always "—". Add both columns and
     * backfill existing rows to 'active' (matching the form's default).
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku', 64)->nullable()->after('slug');
            $table->string('status', 20)->default('active')->after('image');
        });

        \Illuminate\Support\Facades\DB::table('products')
            ->whereNull('status')
            ->orWhere('status', '')
            ->update(['status' => 'active']);

        Schema::table('products', function (Blueprint $table) {
            $table->index('status', 'idx_products_status');
            $table->index('sku', 'idx_products_sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_status');
            $table->dropIndex('idx_products_sku');
            $table->dropColumn('status');
            $table->dropColumn('sku');
        });
    }
};

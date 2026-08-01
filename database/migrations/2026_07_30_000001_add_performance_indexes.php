<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add performance indexes to frequently queried columns.
     */
    public function up(): void
    {
        // Products - add indexes for filtering and search
        Schema::table('products', function (Blueprint $table) {
            $table->index('category_id');
            $table->index('price');
            $table->index('stock');
            $table->index('name');
            $table->index('created_at');
        });

        // Orders - add indexes for status queries and user lookups
        Schema::table('orders', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('status');
            $table->index('payment_status');
            $table->index('created_at');
            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
        });

        // Order items - add indexes for order and product lookups
        Schema::table('order_items', function (Blueprint $table) {
            $table->index('order_id');
            $table->index('product_id');
        });

        // Cart items - add indexes for user lookups
        Schema::table('cart_items', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('product_id');
            $table->index(['user_id', 'product_id']);
        });

        // Reviews - add indexes for product and user lookups
        Schema::table('reviews', function (Blueprint $table) {
            $table->index('product_id');
            $table->index('user_id');
            $table->index('rating');
            $table->index('approved');
            $table->index(['product_id', 'approved']);
        });

        // Wishlists - add indexes (unique already exists on user_id+product_id)
        Schema::table('wishlists', function (Blueprint $table) {
            $table->index('product_id');
        });

        // Coupons - add indexes for lookup by code
        Schema::table('coupons', function (Blueprint $table) {
            $table->index('active');
            $table->index('starts_at');
            $table->index('ends_at');
            $table->index(['active', 'starts_at', 'ends_at']);
        });

        // Shipping methods
        Schema::table('courier_shipping_methods', function (Blueprint $table) {
            $table->index('active');
            $table->index('fee');
        });

        // User notifications
        Schema::table('user_notifications', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('read_at');
            $table->index(['user_id', 'read_at']);
            $table->index('created_at');
        });

        // Product images
        Schema::table('product_images', function (Blueprint $table) {
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
            $table->dropIndex(['price']);
            $table->dropIndex(['stock']);
            $table->dropIndex(['name']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['status', 'created_at']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['order_id']);
            $table->dropIndex(['product_id']);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['product_id']);
            $table->dropIndex(['user_id', 'product_id']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['rating']);
            $table->dropIndex(['approved']);
            $table->dropIndex(['product_id', 'approved']);
        });

        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropIndex(['active']);
            $table->dropIndex(['starts_at']);
            $table->dropIndex(['ends_at']);
            $table->dropIndex(['active', 'starts_at', 'ends_at']);
        });

        Schema::table('courier_shipping_methods', function (Blueprint $table) {
            $table->dropIndex(['active']);
            $table->dropIndex(['fee']);
        });

        Schema::table('user_notifications', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['read_at']);
            $table->dropIndex(['user_id', 'read_at']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
        });
    }
};

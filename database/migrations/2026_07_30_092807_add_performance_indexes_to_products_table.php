<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Speed up category-based filtering
            $table->index('category_id', 'idx_products_category_id');

            // Speed up price range filtering + sorting
            $table->index('price', 'idx_products_price');

            // Speed up latest() / order by created_at
            $table->index('created_at', 'idx_products_created_at');

            // Composite index for the most common query pattern:
            // filter by category + order by date
            $table->index(['category_id', 'created_at'], 'idx_products_category_created');

            // Composite index for price filtering within a category
            $table->index(['category_id', 'price'], 'idx_products_category_price');

            // Index for stock lookups (check availability)
            $table->index('stock', 'idx_products_stock');

            // Composite index for search + category filtering
            $table->index(['category_id', 'name', 'price'], 'idx_products_category_name_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_category_id');
            $table->dropIndex('idx_products_price');
            $table->dropIndex('idx_products_created_at');
            $table->dropIndex('idx_products_category_created');
            $table->dropIndex('idx_products_category_price');
            $table->dropIndex('idx_products_stock');
            $table->dropIndex('idx_products_category_name_price');
        });
    }
};

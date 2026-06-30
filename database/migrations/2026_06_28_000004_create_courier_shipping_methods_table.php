<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courier_shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('courier')->nullable();
            $table->string('code')->unique();
            $table->decimal('fee', 10, 2);
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->integer('estimated_days_min')->nullable();
            $table->integer('estimated_days_max')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_shipping_methods');
    }
};

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
        // Orders Table
        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('shopify_order_id')->unique()->nullable();
                $table->string('shop')->nullable();
                $table->decimal('total_price', 10, 2)->nullable();
                $table->json('payload')->nullable();
                $table->timestamps();
            });
        }

        // Products Table
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('shopify_product_id')->unique()->nullable();
                $table->json('payload')->nullable();
                $table->timestamps();
            });
        }

        // Customers Table
        if (!Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('shopify_customer_id')->unique()->nullable();
                $table->json('payload')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
        Schema::dropIfExists('customers');
    }
};

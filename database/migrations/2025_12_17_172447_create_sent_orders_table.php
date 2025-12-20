<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSentOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('sent_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('shop_setting_id')->constrained()->onDelete('cascade');
            $table->string('order_id');
            $table->string('order_number');
            $table->string('portal_reference')->nullable();
            $table->string('tracking_id')->nullable();
            $table->string('portal_order_id')->nullable();
            $table->json('portal_response')->nullable();
            $table->enum('collect_payment', ['yes', 'no'])->default('no');
            $table->enum('status', ['pending', 'sent', 'confirmed', 'failed'])->default('pending');
            $table->boolean('shopify_updated')->default(false);
            $table->string('fulfillment_id')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('portal_sync_at')->nullable();


            $table->timestamps();

            // 🔥 ADD THIS UNIQUE CONSTRAINT
            $table->unique(['user_id', 'order_id']);

            $table->index(['user_id', 'order_id']);
            $table->index('portal_reference');
            $table->index('tracking_id');
            $table->index('portal_order_id');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sent_orders');
    }
}

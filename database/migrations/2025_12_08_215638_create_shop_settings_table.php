<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('shop_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Shopify user id
            $table->string('shop_domain')->unique();
            $table->string('email');
            $table->string('user_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('user_type')->nullable();
            $table->string('hub_id')->nullable();
            $table->string('merchant_id')->nullable();
            $table->decimal('wallet_balance', 12, 2)->default(0);
            $table->string('api_token')->nullable();
            $table->json('api_response')->nullable();
            $table->string('fulfillment_location')->nullable();
            $table->boolean('fragile')->default(false);
            $table->unsignedBigInteger('portal_user_id')->nullable();
            $table->boolean('insurance')->default(false);
            $table->enum('account_type', ['live','offline'])->default('live');
            $table->boolean('auto_push_cms')->default(false);
            $table->decimal('price', 12, 2)->default(0); // ✅ Added price column
            $table->timestamps();

            // Foreign key to users table
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_settings');
    }
};

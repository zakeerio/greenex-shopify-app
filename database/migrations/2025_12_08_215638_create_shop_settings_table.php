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
        Schema::create('shop_settings', function (Blueprint $table) {
            $table->id();
            $table->string('shop_domain')->unique();
            $table->string('email');
            $table->string('password');
            $table->string('apikey');
            $table->string('api_secret');
            $table->string('fulfillment_location')->nullable();
            $table->boolean('fragile')->default(false);
            $table->boolean('insurance')->default(false);
            $table->enum('account_type', ['live','offline'])->default('live');
            $table->boolean('auto_push_cms')->default(false);
            // $table->decimal('price', 10, 2)->default(0);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_settings');
    }
};

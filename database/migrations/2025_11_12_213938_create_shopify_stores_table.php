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
        Schema::create('shopify_stores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index(); // link to your user
            $table->string('shop_domain')->unique();
            $table->string('access_token'); // store token here
            $table->string('api_version')->default('2025-01');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopify_stores');
    }
};

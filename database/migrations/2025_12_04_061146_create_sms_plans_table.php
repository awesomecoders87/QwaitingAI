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
        Schema::create('sms_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');             // e.g., "500 Credits"
            $table->string('description')->nullable(); // e.g., "Perfect for small campaigns"
            $table->integer('credit_amount')->nullable();   // e.g., 500, 1000
            $table->decimal('price', 10, 2);    // e.g., 49.00
            $table->string('currency_code');
            
            // Critical for Stripe integration
            $table->string('stripe_plan_id')->nullable();
            
            // UI Flags
            $table->boolean('is_popular')->default(false); // To trigger the "POPULAR" badge
            $table->boolean('is_active')->default(true);   // To hide plans without deleting data
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_plans');
    }
};

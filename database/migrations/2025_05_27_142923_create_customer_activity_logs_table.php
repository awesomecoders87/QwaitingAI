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
        Schema::create('customer_activity_logs', function (Blueprint $table) {
        $table->id();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('queue_id')->nullable();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->enum('type', ['queue', 'booking']);
            $table->unsignedBigInteger('customer_id');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_activity_logs');
    }
};

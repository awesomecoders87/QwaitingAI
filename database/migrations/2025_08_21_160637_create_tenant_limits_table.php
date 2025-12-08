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
        Schema::create('tenant_limits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->integer('is_ticket_limit_enabled')->default(0);
            $table->integer('ticket_limit')->default(50);
            $table->integer('is_booking_limit_enabled')->default(0);
            $table->integer('booking_limit')->default(50);
            $table->integer('staff_limit')->default(0);
            $table->integer('location_limit')->default(0);
            $table->integer('service_limit')->default(0);
            $table->integer('counter_limit')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_limits');
    }
};

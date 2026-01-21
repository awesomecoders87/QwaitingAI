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
        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['team_id', 'location_id', 'booking_date'], 'idx_bookings_team_location');
            $table->index('staff_id', 'idx_bookings_staff');
            $table->index('status', 'idx_bookings_status');
            $table->index('refID', 'idx_bookings_refid');
            $table->index('email', 'idx_bookings_email');
            $table->index('phone', 'idx_bookings_phone');
            $table->index('name', 'idx_bookings_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('idx_bookings_team_location');
            $table->dropIndex('idx_bookings_staff');
            $table->dropIndex('idx_bookings_status');
            $table->dropIndex('idx_bookings_refid');
            $table->dropIndex('idx_bookings_email');
            $table->dropIndex('idx_bookings_phone');
            $table->dropIndex('idx_bookings_name');
        });
    }
};

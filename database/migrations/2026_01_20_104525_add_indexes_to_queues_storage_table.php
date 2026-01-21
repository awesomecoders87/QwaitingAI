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
        Schema::table('queues_storage', function (Blueprint $table) {
            $table->index(['team_id', 'locations_id', 'status', 'arrives_time'], 'idx_queue_active');
            $table->index(['category_id', 'sub_category_id'], 'idx_queue_categories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queues_storage', function (Blueprint $table) {
            $table->dropIndex('idx_queue_active');
            $table->dropIndex('idx_queue_categories');
        });
    }
};

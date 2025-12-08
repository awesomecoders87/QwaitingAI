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
        if (Schema::hasTable('site_details') && !Schema::hasColumn('site_details', 'google_map_key')) {
            Schema::table('site_details', function (Blueprint $table) {
                $table->string('google_map_key')->nullable()->after('assigned_staff_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('site_details') && Schema::hasColumn('site_details', 'google_map_key')) {
            Schema::table('site_details', function (Blueprint $table) {
                $table->dropColumn('google_map_key');
            });
        }
    }
};

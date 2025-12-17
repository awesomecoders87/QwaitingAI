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
        Schema::table('message_detail', function (Blueprint $table) {
            $table->decimal('sms_sent_cost', 10, 4)->default(0)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('message_detail', function (Blueprint $table) {
            $table->dropColumn('sms_sent_cost');
        });
    }
};

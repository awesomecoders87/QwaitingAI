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
        Schema::table('message_templates', function (Blueprint $table) {
            $table->text('stripe_sms_message')->nullable()->after('recall_message_template');
            $table->boolean('stripe_sms_message_status')->default(0)->nullable()->after('stripe_sms_message');
            $table->string('stripe_sms_message_template')->nullable()->after('stripe_sms_message_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('message_templates', function (Blueprint $table) {
            $table->dropColumn(['stripe_sms_message', 'stripe_sms_message_status', 'stripe_sms_message_template']);
        });
    }
};

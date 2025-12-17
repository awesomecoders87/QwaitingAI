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
        Schema::table('notification_templates', function (Blueprint $table) {
            $table->text('stripe_sms_notification')->nullable()->after('booking_confirmed_admin_notification_status');
            $table->string('stripe_sms_notification_subject')->nullable()->after('stripe_sms_notification');
            $table->boolean('stripe_sms_notification_status')->default(0)->nullable()->after('stripe_sms_notification_subject');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_templates', function (Blueprint $table) {
            $table->dropColumn(['stripe_sms_notification', 'stripe_sms_notification_subject', 'stripe_sms_notification_status']);
        });
    }
};

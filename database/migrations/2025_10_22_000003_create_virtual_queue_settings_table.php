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
        Schema::create('virtual_queue_settings', function (Blueprint $table) {
            $table->id();
            $table->string('team_id');
            $table->unsignedBigInteger('location_id');
            
            // Feature toggles
            $table->boolean('enable_virtual_queue')->default(false);
            $table->boolean('enable_ai_agent')->default(false);
            $table->boolean('enable_human_agent')->default(true);
            
            // AI Agent settings
            $table->string('ai_provider')->default('openai'); // openai, azure, custom
            $table->string('ai_api_key')->nullable();
            $table->string('ai_model')->default('gpt-4');
            $table->string('ai_voice')->default('alloy');
            $table->json('supported_languages')->nullable();
            $table->string('default_language')->default('en');
            
            // Avatar settings
            $table->string('ai_avatar_type')->default('default'); // default, custom, heygen
            $table->string('ai_avatar_url')->nullable();
            $table->string('heygen_avatar_id')->nullable();
            $table->string('heygen_api_key')->nullable();
            
            // Video call settings
            $table->string('video_provider')->default('twilio'); // twilio, agora, daily
            $table->string('video_api_key')->nullable();
            $table->string('video_api_secret')->nullable();
            
            // Transfer settings
            $table->boolean('auto_transfer_on_failure')->default(true);
            $table->integer('max_ai_attempts')->default(3);
            $table->integer('transfer_timeout_seconds')->default(300);
            
            // Queue settings
            $table->integer('max_concurrent_ai_sessions')->default(10);
            $table->integer('max_concurrent_human_sessions')->default(5);
            $table->integer('session_timeout_minutes')->default(30);
            
            // Notification settings
            $table->boolean('send_sms_notification')->default(true);
            $table->boolean('send_email_notification')->default(true);
            $table->boolean('send_whatsapp_notification')->default(false);
            
            // Custom prompts
            $table->text('ai_system_prompt')->nullable();
            $table->text('ai_greeting_message')->nullable();
            $table->text('transfer_message')->nullable();
            
            $table->timestamps();
            
            $table->unique(['team_id', 'location_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('virtual_queue_settings');
    }
};

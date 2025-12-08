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
        Schema::create('ai_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('team_id');
            $table->unsignedBigInteger('virtual_queue_id');
            $table->string('session_id')->unique();
            
            // AI Configuration
            $table->string('ai_model')->default('gpt-4');
            $table->string('ai_voice')->default('alloy');
            $table->string('language')->default('en');
            $table->string('avatar_url')->nullable();
            
            // Conversation tracking
            $table->json('conversation_history')->nullable();
            $table->text('customer_query')->nullable();
            $table->text('ai_response')->nullable();
            $table->boolean('query_resolved')->default(false);
            
            // Transfer tracking
            $table->boolean('escalated')->default(false);
            $table->text('escalation_reason')->nullable();
            $table->timestamp('escalated_at')->nullable();
            
            // Sentiment analysis
            $table->string('customer_sentiment')->nullable(); // positive, neutral, negative
            $table->integer('satisfaction_score')->nullable(); // 1-5
            
            // Session metrics
            $table->integer('message_count')->default(0);
            $table->integer('ai_response_time_ms')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            
            $table->timestamps();
            
            $table->foreign('virtual_queue_id')->references('id')->on('virtual_queues')->onDelete('cascade');
            $table->index(['team_id', 'session_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_sessions');
    }
};

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
        Schema::create('virtual_queues', function (Blueprint $table) {
            $table->id();
            $table->string('team_id');
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('queue_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('ticket_number')->unique();
            
            // Virtual queue type: 'ai_agent' or 'human_agent'
            $table->enum('queue_type', ['ai_agent', 'human_agent'])->default('ai_agent');
            
            // Session details
            $table->string('session_id')->nullable();
            $table->string('meeting_link')->nullable();
            $table->string('meeting_room_id')->nullable();
            
            // AI Agent details
            $table->string('ai_agent_id')->nullable();
            $table->string('selected_language')->default('en');
            $table->text('conversation_summary')->nullable();
            
            // Transfer details
            $table->boolean('transferred_to_human')->default(false);
            $table->unsignedBigInteger('human_agent_id')->nullable();
            $table->timestamp('transferred_at')->nullable();
            $table->text('transfer_reason')->nullable();
            
            // Status tracking
            $table->enum('status', ['pending', 'ai_connected', 'human_connected', 'completed', 'cancelled', 'failed'])->default('pending');
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // Customer details
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            
            // Metrics
            $table->integer('wait_time_seconds')->default(0);
            $table->integer('call_duration_seconds')->default(0);
            $table->integer('ai_duration_seconds')->default(0);
            $table->integer('human_duration_seconds')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['team_id', 'location_id']);
            $table->index('status');
            $table->index('queue_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('virtual_queues');
    }
};

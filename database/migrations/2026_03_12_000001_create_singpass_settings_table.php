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
        Schema::create('singpass_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->index();
            $table->unsignedBigInteger('location_id')->index();
            $table->string('client_id', 255)->nullable();
            $table->enum('environment', ['staging', 'production'])->default('staging');
            $table->longText('signing_private_key')->nullable();
            $table->longText('signing_public_key')->nullable();
            $table->longText('enc_private_key')->nullable();
            $table->longText('enc_public_key')->nullable();
            $table->timestamp('keys_generated_at')->nullable();
            $table->tinyInteger('is_enabled')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'location_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('singpass_settings');
    }
};

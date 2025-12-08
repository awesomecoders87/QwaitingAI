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
        Schema::create('salesforce_settings', function (Blueprint $table) {
          $table->id();
            $table->unsignedBigInteger('team_id')->index();
            $table->unsignedBigInteger('location_id')->index();
            $table->string('client_id')->nullable();
            $table->string('client_secret')->nullable();
            $table->string('redirect_uri')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salesforce_settings');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('queue_import_failures', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('team_id')->nullable()->index();
            $table->unsignedBigInteger('locations_id')->nullable()->index();
            $table->string('source', 64)->nullable()->index(); // e.g., 'web-upload'
            $table->unsignedBigInteger('chunk_size')->nullable();
            $table->unsignedBigInteger('row_index')->nullable();
            $table->text('error')->nullable();
            $table->longText('payload')->nullable(); // original row data as JSON text
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_import_failures');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publication_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('external_publication_id')->constrained()->restrictOnDelete();
            $table->char('source_hash', 64);
            $table->text('artifact_path');
            $table->string('operation');
            $table->string('status')->default('awaiting_manual_action');
            $table->unsignedTinyInteger('active_slot')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
            $table->unique(['external_publication_id', 'active_slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publication_attempts');
    }
};

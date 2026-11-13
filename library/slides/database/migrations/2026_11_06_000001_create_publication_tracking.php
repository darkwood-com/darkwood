<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presentations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('source_slug')->unique();
            $table->string('title');
            $table->unsignedInteger('slide_count');
            $table->timestamps();
        });
        Schema::create('external_publications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('slide_id')->constrained('presentations')->restrictOnDelete();
            $table->string('provider');
            $table->string('remote_id')->nullable();
            $table->text('remote_url')->nullable();
            $table->string('remote_slug')->nullable();
            $table->string('status')->default('not_published');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->char('source_hash', 64)->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
            $table->unique(['slide_id', 'provider']);
            $table->unique(['provider', 'remote_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_publications');
        Schema::dropIfExists('presentations');
    }
};

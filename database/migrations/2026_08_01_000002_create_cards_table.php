<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->string('external_id')->index();
            $table->string('name');
            $table->string('set_name')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamps();

            $table->unique(['game_id', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};

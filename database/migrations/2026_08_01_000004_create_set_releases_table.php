<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('set_releases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('external_id')->index();
            $table->date('release_date')->nullable();
            $table->date('announced_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique(['game_id', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('set_releases');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_trending_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedTinyInteger('rank');
            $table->decimal('price', 10, 2);
            $table->decimal('price_change_percent', 5, 2);
            $table->string('currency', 3)->default('USD');
            $table->timestamps();

            $table->unique(['game_id', 'date', 'rank']);
            $table->unique(['game_id', 'date', 'card_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_trending_cards');
    }
};

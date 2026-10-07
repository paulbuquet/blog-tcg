<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@tcg-blog.local',
            'password' => env('ADMIN_PASSWORD', 'password123'),
        ]);

        $this->seedGames();
    }

    private function seedGames(): void
    {
        $games = [
            ['name' => 'Pokémon', 'slug' => 'pokemon', 'api_identifier' => 'pokemon', 'is_active' => true],
            ['name' => 'Magic: The Gathering', 'slug' => 'magic', 'api_identifier' => 'magic', 'is_active' => true],
            ['name' => 'Yu-Gi-Oh!', 'slug' => 'yugioh', 'api_identifier' => 'yugioh', 'is_active' => true],
            ['name' => 'One Piece', 'slug' => 'one-piece', 'api_identifier' => 'one-piece-card-game', 'is_active' => true],
        ];

        foreach ($games as $game) {
            Game::firstOrCreate(['slug' => $game['slug']], $game);
        }
    }
}

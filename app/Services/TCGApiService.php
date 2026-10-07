<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TCGApiService
{
    private PendingRequest $client;

    public function __construct()
    {
        $this->client = Http::baseUrl(config('services.tcg_api.base_url'))
            ->withHeaders(['X-API-Key' => config('services.tcg_api.key')])
            ->acceptJson()
            ->throw();
    }

    /**
     * @return array<int, array<string, mixed>> Cartes triées par variation de prix (déjà triées côté API)
     */
    public function topMovers(string $game, string $direction = 'up', string $period = '24h', int $limit = 10): array
    {
        return $this->request('/prices/top-movers', [
            'game' => $game,
            'direction' => $direction,
            'period' => $period,
            'limit' => $limit,
        ])['data'] ?? [];
    }

    /**
     * @return array<int, array<string, mixed>> Tous les sets du jeu (pagination gérée, 1 requête/page)
     */
    public function sets(string $game): array
    {
        $sets = [];
        $page = 1;

        do {
            $response = $this->request('/sets', [
                'game' => $game,
                'page' => $page,
                'per_page' => 100,
            ]);

            $data = $response['data'] ?? [];
            if (isset($data['id'])) {
                $sets[] = $data;
            } else {
                array_push($sets, ...$data);
            }

            $hasMore = $response['meta']['has_more'] ?? false;
            $page++;
        } while ($hasMore);

        return $sets;
    }

    /**
     * @return array<int, array<string, mixed>> Liste des jeux supportés (endpoint public, sans clé)
     */
    public function games(): array
    {
        return $this->request('/games', ['per_page' => 100])['data'] ?? [];
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    private function request(string $path, array $query = []): array
    {
        if (blank(config('services.tcg_api.key'))) {
            throw new RuntimeException('Clé TCG_API_KEY manquante dans .env.');
        }

        return $this->client->get($path, $query)->json();
    }
}

<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use RuntimeException;

class OpenAIService
{
    public function __construct(
        private readonly string $model = 'gpt-4o-mini',
    ) {}

    /**
     * Génère un brouillon d'article d'annonce de sortie en français (Markdown).
     *
     * @param  array<string, mixed>  $set  Informations sur le set (name, game_name, release_date, ...)
     */
    public function generateReleaseArticle(array $set): string
    {
        if (blank(config('openai.api_key'))) {
            throw new RuntimeException('Clé OPENAI_API_KEY manquante dans .env.');
        }

        $game = $set['game_name'] ?? 'Trading Card Game';
        $setName = $set['name'] ?? 'un set';
        $releaseDate = $set['release_date'] ?? 'à une date non précisée';

        $prompt = <<<PROMPT
Tu es un rédacteur spécialisé dans les Trading Card Games. Rédige un article d'annonce de sortie en français, en Markdown, d'environ 300 mots, pour le blog d'un site d'actualités TCG.

Contexte :
- Jeu : {$game}
- Set : {$setName}
- Date de sortie annoncée : {$releaseDate}

Structure attendue :
# Titre accrocheur en H1
Un paragraphe d'introduction présentant l'annonce officielle du set.
## Ce qu'on sait déjà
2-3 paragraphes sur le thème, les nouveautés et l'ambiance du set.
## Date de sortie et informations pratiques
Un paragraphe avec la date de sortie et le rappel que les informations détaillées (cartes, mécaniques) seront publiées dès que disponibles.

Consignes :
- Rédige uniquement le corps de l'article en Markdown, SANS titre H1 en début de texte (le titre sera ajouté séparément).
- Ne jamais inventer de mécaniques, de cartes ou de faits non fournis.
- Mentionner explicitement que la sortie est annoncée mais que les détails complets restent à venir.
- Ton : informatif, enthousiaste mais factuel.
PROMPT;

        $response = OpenAI::chat()->create([
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => 'Tu es un rédacteur professionnel de blog TCG.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.7,
        ]);

        return trim($response->choices[0]->message->content ?? '');
    }
}

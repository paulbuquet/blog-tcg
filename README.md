# TCG Blog

Un blog d'actualités multi-TCG (Pokémon, Magic: The Gathering, Yu-Gi-Oh!, One Piece) alimenté par IA, avec système de tendances de prix quotidien.

## Fonctionnalités

- **Articles IA** : Génération automatique d'articles d'annonce de sorties de sets via OpenAI (GPT-4o-mini), validés manuellement avant publication
- **Articles manuels** : Rédaction d'actualités TCG (banlists, errata, annonces diverses) via Filament
- **Widget tendances** : Top 10 des cartes en hausse de prix par jeu, mis à jour quotidiennement via l'API tcgapi.dev
- **Back-office Filament** : Interface d'admin complète pour gérer jeux, articles, sets et superviser les jobs
- **Scheduler + Queue** : Jobs asynchrones quotidiens pour fetch prix, détecter nouveaux sets, générer articles

## Stack technique

| Composant | Technologie |
|-----------|-------------|
| Framework | Laravel 13.x (PHP 8.3+) |
| Admin | Filament 5.x |
| Frontend | Blade + Livewire 3 + Tailwind CSS 4 |
| Queue/Scheduler | Laravel Queue (database) + Scheduler |
| IA | OpenAI API (openai-php/laravel) |
| API Prix | tcgapi.dev (endpoint `/v1/prices/top-movers`) |
| Base de données | SQLite (dev) / PostgreSQL/MySQL (prod) |
| Markdown | league/commonmark (GitHub Flavored) |

## Architecture

### Modèles de données

- **Game** : Jeux supportés (Pokémon, Magic, Yu-Gi-Oh!, One Piece...)
- **Card** : Cartes référencées (cache local depuis l'API prix)
- **DailyTrendingCard** : Top 10 quotidien par jeu (alimente le widget)
- **SetRelease** : Log des sorties détectées (évite les doublons)
- **Article** : Contenu du blog (type: `release_announcement` | `tcg_news`, statut: `draft` | `published`)

### Pipeline quotidien (jobs)

1. **FetchTrendingCards** (07:00) — Récupère le top 10 cartes en hausse par jeu via tcgapi.dev
2. **CheckNewReleases** (07:15) — Détecte les nouveaux sets via l'API sets, crée entrées `SetRelease`
3. **GenerateReleaseArticle** — Génère un brouillon d'article IA pour chaque nouveau set détecté
4. **Validation humaine** — L'admin relit/édite dans Filament puis publie manuellement

## Installation

### Prérequis

- PHP 8.3+
- Composer
- Node.js 20+ & npm
- SQLite (dev) ou PostgreSQL/MySQL (prod)
- Clés API : [tcgapi.dev](https://tcgapi.dev) + [OpenAI](https://platform.openai.com)

### Setup rapide

```bash
# Cloner le repo
git clone <repo-url>
cd tcg-blog

# Dépendances PHP
composer install

# Dépendances JS
npm install

# Environnement
cp .env.example .env
php artisan key:generate

# Base de données (SQLite par défaut)
touch database/database.sqlite
php artisan migrate --seed

# Build assets
npm run build

# Lancer le serveur de dev
composer run dev
```

Le serveur sera accessible sur `http://localhost:8000` et l'admin sur `http://localhost:8000/admin`.

### Variables d'environnement requises

| Variable | Description | Requis |
|----------|-------------|--------|
| `TCG_API_KEY` | Clé API tcgapi.dev (free: 100 req/jour) | Oui |
| `OPENAI_API_KEY` | Clé API OpenAI pour génération articles | Oui |
| `ADMIN_PASSWORD` | Mot de passe admin (défaut: `password123`) | Non* |
| `APP_URL` | URL publique de l'application | Prod |

*Recommandé de changer `ADMIN_PASSWORD` en production.

## Configuration

### Jeux supportés (seed par défaut)

```php
[
    ['name' => 'Pokémon',           'slug' => 'pokemon',           'api_identifier' => 'pokemon'],
    ['name' => 'Magic: The Gathering', 'slug' => 'magic',        'api_identifier' => 'magic'],
    ['name' => 'Yu-Gi-Oh!',         'slug' => 'yugioh',          'api_identifier' => 'yugioh'],
    ['name' => 'One Piece',         'slug' => 'one-piece',       'api_identifier' => 'one-piece-card-game'],
]
```

Chaque jeu a un thème couleur dédié (badge, gradient, point) défini dans `Game::accent()`.

### Planning des jobs (routes/console.php)

```php
// 07:00 - Tendances prix
Schedule::call(fn () => Game::active()->each(fn ($g) => FetchTrendingCardsJob::dispatch($g)))
    ->dailyAt('07:00')->withoutOverlapping();

// 07:15 - Nouveaux sets
Schedule::call(fn () => Game::active()->each(fn ($g) => CheckNewReleasesJob::dispatch($g)))
    ->dailyAt('07:15')->withoutOverlapping();
```

### Queue worker (requis en prod)

```bash
php artisan queue:work --tries=3 --timeout=180
```

## Utilisation

### Frontend public

- `/` — Page d'accueil avec articles récents + widgets tendances par jeu
- `/article/{slug}` — Lecture article complet (Markdown renderisé)
- `/jeu/{slug}` — Articles filtrés par jeu + widget tendances

### Back-office Filament (`/admin`)

- **Articles** : CRUD complet, actions Publier/Dépublier, filtres par statut/type/jeu
- **Jeux** : Gestion jeux + identifiants API + bannières
- **Sets** : Log des sorties détectées, liaison articles
- **Cartes** : Cache local des cartes (peu édité manuellement)

### Commandes utiles

```bash
# Tests
composer run test          # ou: php artisan test

# Lint/Format (Pint)
./vendor/bin/pint

# Vider cache config
php artisan config:clear

# Relancer migrations + seed
php artisan migrate:fresh --seed

# Exécuter jobs manuellement
php artisan queue:work --once
```

## Structure du projet

```
app/
├── Enums/              # ArticleType, ArticleStatus
├── Filament/           # Resources (Article, Game, Card, SetRelease), Widgets
├── Http/Controllers/   # ArticleController (frontend public)
├── Jobs/               # FetchTrendingCardsJob, CheckNewReleasesJob, GenerateReleaseArticleJob
├── Livewire/           # TrendingCardsWidget
├── Models/             # Game, Card, DailyTrendingCard, SetRelease, Article
├── Services/           # TCGApiService, OpenAIService
└── Support/            # Markdown (commonmark converter)

database/
├── migrations/         # 5 tables principales + jobs/cache/users
├── factories/          # Factories pour tests
└── seeders/            # DatabaseSeeder (admin + 4 jeux)

resources/
├── views/
│   ├── layouts/app.blade.php       # Layout principal (header, footer, nav jeux)
│   ├── articles/index.blade.php    # Homepage
│   ├── articles/show.blade.php     # Article detail
│   ├── games/show.blade.php        # Page jeu
│   ├── livewire/trending-cards-widget.blade.php
│   └── components/article-card.blade.php

routes/
├── web.php             # Routes frontend public
└── console.php         # Scheduler jobs quotidiens

tests/
├── Feature/            # Tests pages publiques + admin panel
└── Unit/               # Tests unitaires services
```

## API tcgapi.dev - Notes importantes

- **Tarification** : Free tier = 100 requêtes/jour (non-commercial)
- **Usage estimé** : ~6-12 req/jour (3-4 jeux × top-movers + sets)
- **Prix** : En USD (source TCGPlayer), pas EUR/Cardmarket
- **Endpoints utilisés** :
  - `GET /v1/prices/top-movers` — Top cartes par variation prix (24h)
  - `GET /v1/sets` — Liste sets par jeu (pagination)

## Déploiement

### Contraintes d'hébergement

Le scheduler et les queues doivent tourner en continu → **pas de serverless** (Vercel, Netlify).

Options recommandées :
- **VPS** (Hetzner, DigitalOcean, Scaleway) + systemd/supervisor pour queue worker + cron pour scheduler
- **Laravel Cloud** (quand disponible)
- **Laravel Forge** + VPS pour gestion simplifiée

### Checklist prod

- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] `APP_KEY` fort (généré)
- [ ] `ADMIN_PASSWORD` changé
- [ ] Base PostgreSQL/MySQL configurée
- [ ] `QUEUE_CONNECTION=database` (ou redis) + worker supervisé
- [ ] `php artisan schedule:run` dans cron (toutes les minutes)
- [ ] `npm run build` (assets compilés)
- [ ] `php artisan config:cache && php artisan route:cache && php artisan view:cache`
- [ ] HTTPS + headers sécurité
- [ ] Backup base de données automatisé

## Tests

```bash
# Suite complète
php artisan test

# Avec coverage (nécessite xdebug/pcov)
php artisan test --coverage
```

Tests couvrants : pages publiques (home, article, jeu), admin panel (auth, resources), jobs.

## Licence

MIT License — voir [LICENSE](LICENSE) si présent, sinon licence par défaut Laravel.

---

*Projet personnel — pas de vocation commerciale.*
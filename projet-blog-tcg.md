# Projet : Blog/Newsletter TCG multi-jeux alimenté par IA

## Concept général

Un blog qui couvre plusieurs Trading Card Games (Pokémon, Magic, Yu-Gi-Oh, et potentiellement d'autres) avec deux types de contenu :

1. **Articles générés par IA** annonçant les nouvelles sorties de sets, dès l'annonce officielle (même si la sortie réelle est dans plusieurs mois)
2. **Articles rédigés manuellement** pour les actualités TCG (banlists, errata, annonces diverses)

En complément, un **widget dynamique** (pas un article) affichant le top 10 des cartes les plus en tendance (prix) par jeu, mis à jour quotidiennement.

Projet actuellement **purement personnel**, pas de vocation commerciale pour le moment.

---

## Stack technique retenue

- **Laravel** — application principale, scheduler pour les cron jobs quotidiens
- **Filament** — back-office pour superviser les jobs, éditer/valider les articles générés par l'IA avant publication, rédiger manuellement les articles de type actu/banlist
- **Blade + Livewire** — front public : articles en Blade classique, composants Livewire pour le widget de tendances de prix (pas besoin d'un front ultra-interactif type Next.js, un blog classique + quelques widgets suffit)
- **Queue Laravel** — nécessaire pour les appels API multi-jeux sans bloquer le scheduler
- **Hébergement** — à trancher plus tard (pistes envisagées : VPS nu type Hetzner + config manuelle, ou Laravel Cloud à évaluer). Contrainte clé : le scheduler et les queues doivent tourner en continu, donc pas de plateforme serverless type Vercel/Netlify pour ce projet.

### Pourquoi Laravel + Filament plutôt que Next.js
Le projet a une problématique de back-office/ETL (fetch multi-API, génération IA, supervision, édition manuelle) plus qu'une problématique de frontend riche. Filament donne un BO quasi gratuit (CRUD auto, scheduler natif, queues) adapté à un usage solo où l'utilisateur est son propre admin.

---

## Schéma de base de données

### `games`
| Colonne | Type | Notes |
|---|---|---|
| id | bigint PK | |
| name | string | Pokémon, Magic, Yu-Gi-Oh... |
| slug | string unique | |
| api_identifier | string | identifiant du jeu côté API prix |
| is_active | boolean | activer/désactiver un jeu sans le supprimer |

### `cards`
| Colonne | Type | Notes |
|---|---|---|
| id | bigint PK | |
| game_id | FK → games | |
| external_id | string | id côté API prix |
| name | string | |
| set_name | string | |
| image_url | string nullable | |

### `daily_trending_cards`
Alimente uniquement le widget public — jamais d'article généré à partir de cette table.

| Colonne | Type | Notes |
|---|---|---|
| id | bigint PK | |
| game_id | FK → games | dénormalisé pour requêter sans join |
| card_id | FK → cards | |
| date | date | |
| rank | tinyint | 1 à 10, par jeu |
| price | decimal(10,2) | |
| price_change_percent | decimal(5,2) | |
| currency | string(3) | |

Contraintes uniques : `(game_id, date, rank)` et `(game_id, date, card_id)`.

### `set_releases`
Log des sorties détectées, sert à distinguer une nouvelle annonce d'une sortie déjà connue, et à éviter les doublons d'articles.

| Colonne | Type | Notes |
|---|---|---|
| id | bigint PK | |
| game_id | FK → games | |
| name | string | |
| external_id | string | |
| release_date | date | peut être dans le futur |
| announced_at | date | date de première détection par notre système |
| processed_at | timestamp nullable | rempli quand un article a été généré |

Contrainte unique : `(game_id, external_id)`.

### `articles`
Table pivot centrale du blog, unique système de contenu (pas de table séparée pour les banlists).

| Colonne | Type | Notes |
|---|---|---|
| id | bigint PK | |
| game_id | FK → games, nullable | nullable si l'article couvre plusieurs jeux |
| type | enum | `release_announcement`, `tcg_news` |
| title | string | |
| content | longtext | markdown |
| status | enum | draft / published |
| published_at | timestamp nullable | |

---

## Pipeline / Jobs

1. **`FetchTrendingCards`** (cron quotidien, par jeu)
   Appelle l'API prix pour récupérer le top 10 des cartes en tendance du jour, remplit `daily_trending_cards`. N'alimente que le widget, ne déclenche jamais d'article.

2. **`CheckNewReleases`** (cron quotidien, par jeu)
   Interroge l'API du jeu pour détecter les sets pas encore présents en base (comparaison sur `external_id`). Si nouveau(x) set(s) détecté(s) → insertion dans `set_releases` avec `announced_at = aujourd'hui` → déclenche `GenerateReleaseArticle`.

3. **`GenerateReleaseArticle`**
   Génère un article (`type: release_announcement`, `status: draft`) uniquement pour les sets fraîchement annoncés du jour. Marque leur `processed_at`.

4. **Rédaction manuelle dans Filament**
   L'utilisateur crée manuellement les articles `type: tcg_news` (banlists, errata, annonces diverses) — pas d'automatisation IA sur cette partie, car elle nécessite de lire/comprendre l'annonce officielle de toute façon.

5. **Widget "Top 10 du jour"**
   Composant Livewire qui lit directement `daily_trending_cards` filtré sur la date du jour et le jeu — affichage dynamique, aucun lien avec le système d'articles.

### Workflow éditorial
L'IA génère un brouillon d'article de sortie → l'utilisateur relit/édite dans Filament → publication manuelle. Rien n'est publié automatiquement sans validation humaine.

---

## API de prix retenue : TCG API (tcgapi.dev)

### Pourquoi ce choix
- Cardmarket a une API officielle (MKM API) mais **n'accepte plus de nouvelles candidatures développeur** actuellement — piste fermée.
- Les wrappers tiers autour de Cardmarket (cardmarketapi.com, cardmarket-api.com) reposent sur du scraping non-officiel : fragile pour une brique de prod qui tourne quotidiennement, zone grise vis-à-vis des CGU Cardmarket.
- pokemon-api.com donne des prix EUR natifs (Cardmarket) mais ne couvre que Pokémon/Lorcana/One Piece/Star Wars/Riftbound — **pas Magic ni Yu-Gi-Oh**, insuffisant pour le besoin multi-TCG.
- TCG API couvre 89+ jeux dont Pokémon, Magic et Yu-Gi-Oh (les 3 prioritaires), avec un endpoint dédié exactement adapté au besoin.

### Point d'attention
Prix en **USD** (source TCGPlayer), pas en EUR/Cardmarket comme souhaité initialement. Une conversion EUR simple sera nécessaire à l'affichage si besoin. Compromis accepté au profit de la couverture multi-jeux et de la fiabilité.

### Endpoint clé
```
GET /v1/prices/top-movers
```

Paramètres :
- `game` — filtre par jeu (slug)
- `direction` — `up` ou `down`
- `period` — `24h`, `7d`, ou `30d` (24h retenu pour l'usage quotidien)
- `limit` — jusqu'à 50 (10 pour le besoin actuel)

Retourne directement les cartes triées par variation de prix, avec `price_change` en %, prêt à mapper vers `daily_trending_cards` sans tri applicatif nécessaire.

### Tarifs
- **Free** : 100 requêtes/jour, non-commercial — suffisant pour démarrer avec 3-4 jeux
- Hobby (9,99$/mo), Starter (19,99$/mo), Pro (49,99$/mo — inclut licence commerciale), Business (99,99$/mo)

### Vigilance budget requêtes
Avec le tier gratuit, si on multiplie (top 10 × plusieurs jeux × 2 directions up/down) chaque jour, le quota de 100 req/jour peut être limitant en ajoutant beaucoup de jeux. Rester sur 3-4 jeux au démarrage (Pokémon, Magic, Yu-Gi-Oh, éventuellement One Piece).

---

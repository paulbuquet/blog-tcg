<?php

namespace App\Enums;

enum ArticleType: string
{
    case ReleaseAnnouncement = 'release_announcement';
    case TcgNews = 'tcg_news';

    public function label(): string
    {
        return match ($this) {
            self::ReleaseAnnouncement => 'Annonce de sortie',
            self::TcgNews => 'Actualité TCG',
        };
    }
}

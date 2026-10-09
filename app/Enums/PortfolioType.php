<?php

namespace App\Enums;

enum PortfolioType: string
{
    case Album = 'album';
    case Video = 'video';

    public function label(): string
    {
        return match ($this) {
            self::Album => 'Photo albums',
            self::Video => 'Videos',
        };
    }
}

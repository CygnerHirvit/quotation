<?php

namespace App\Enums;

enum ItemSection: string
{
    case Video = 'video';
    case Photo = 'photo';

    public function label(): string
    {
        return match ($this) {
            self::Video => 'Video',
            self::Photo => 'Photos',
        };
    }

    public function priceCategory(): PriceCategory
    {
        return match ($this) {
            self::Video => PriceCategory::Video,
            self::Photo => PriceCategory::Photo,
        };
    }
}

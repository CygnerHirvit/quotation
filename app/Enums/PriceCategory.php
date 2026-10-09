<?php

namespace App\Enums;

enum PriceCategory: string
{
    case Crew = 'crew';
    case Video = 'video';
    case Photo = 'photo';

    public function label(): string
    {
        return match ($this) {
            self::Crew => 'Crew',
            self::Video => 'Video',
            self::Photo => 'Photos',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Crew => 'People and equipment booked per function, e.g. Candid Photographer, Drone, LED wall. The rate is per function.',
            self::Video => 'Video deliverables, e.g. Wedding Full Film, Highlight, Reels.',
            self::Photo => 'Photo deliverables, e.g. Edited couple photos, Online gallery link, Face recognition link.',
        };
    }
}

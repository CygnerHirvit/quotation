<?php

namespace App\Enums;

enum QuotationStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Viewed = 'viewed';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Sent => 'Sent',
            self::Viewed => 'Viewed',
            self::Accepted => 'Accepted',
            self::Rejected => 'Rejected',
            self::Expired => 'Expired',
        };
    }

    /**
     * Statuses the studio may pick by hand. Viewed and Expired are set by the system.
     *
     * @return list<self>
     */
    public static function selectable(): array
    {
        return [self::Draft, self::Sent, self::Accepted, self::Rejected];
    }

    /**
     * Tailwind classes for the status badge.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Draft => 'bg-canvas text-muted border border-line',
            self::Sent => 'bg-sky-50 text-sky-700',
            self::Viewed => 'bg-violet-50 text-violet-700',
            self::Accepted => 'bg-emerald-50 text-emerald-700',
            self::Rejected => 'bg-red-50 text-red-700',
            self::Expired => 'bg-amber-50 text-amber-800',
        };
    }
}

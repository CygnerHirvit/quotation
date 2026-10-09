<?php

namespace App\Enums;

enum QuotationTemplate: string
{
    case Wedding = 'wedding';
    case Corporate = 'corporate';

    public function label(): string
    {
        return match ($this) {
            self::Wedding => 'Wedding',
            self::Corporate => 'Corporate',
        };
    }
}

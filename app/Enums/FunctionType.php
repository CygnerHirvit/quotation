<?php

namespace App\Enums;

enum FunctionType: string
{
    case Wedding = 'wedding';
    case Engagement = 'engagement';
    case PreWedding = 'pre_wedding';
    case Reception = 'reception';
    case Corporate = 'corporate';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Wedding => 'Wedding',
            self::Engagement => 'Engagement',
            self::PreWedding => 'Pre-wedding',
            self::Reception => 'Reception',
            self::Corporate => 'Corporate',
            self::Other => 'Other',
        };
    }

    /**
     * Which terms and cover photo the quotation uses.
     */
    public function template(): QuotationTemplate
    {
        return $this === self::Corporate ? QuotationTemplate::Corporate : QuotationTemplate::Wedding;
    }
}

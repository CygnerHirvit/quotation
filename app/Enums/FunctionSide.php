<?php

namespace App\Enums;

enum FunctionSide: string
{
    case BothSides = 'both';
    case BrideSide = 'bride';
    case GroomSide = 'groom';

    public function label(): string
    {
        return match ($this) {
            self::BothSides => 'Both Sides',
            self::BrideSide => 'Bride Side',
            self::GroomSide => 'Groom Side',
        };
    }
}

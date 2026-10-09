<?php

namespace App\Support;

/**
 * Indian rupee formatting (₹7,40,000) without depending on the intl extension,
 * which shared hosting does not always have.
 */
class Inr
{
    public static function format(int|float $amount, bool $withSymbol = true): string
    {
        $rounded = (int) round($amount);
        $sign = $rounded < 0 ? '-' : '';
        $digits = (string) abs($rounded);

        if (strlen($digits) > 3) {
            $lastThree = substr($digits, -3);
            $rest = substr($digits, 0, -3);
            $digits = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest).','.$lastThree;
        }

        return $sign.($withSymbol ? '₹' : '').$digits;
    }
}

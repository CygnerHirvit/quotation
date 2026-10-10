<?php

namespace App\Enums;

enum StudioTheme: string
{
    case TealMarigold = 'teal_marigold';
    case MidnightGold = 'midnight_gold';
    case BlushEditorial = 'blush_editorial';
    case RoyalCrimson = 'royal_crimson';

    public function label(): string
    {
        return match ($this) {
            self::TealMarigold => 'Teal Marigold',
            self::MidnightGold => 'Midnight Gold',
            self::BlushEditorial => 'Blush Editorial',
            self::RoyalCrimson => 'Royal Crimson',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::TealMarigold => 'Festive Indian colours on a warm light page',
            self::MidnightGold => 'Dark and luxurious, for premium studios',
            self::BlushEditorial => 'Clean white, magazine style',
            self::RoyalCrimson => 'Regal crimson on warm ivory with framed card elegance',
        };
    }

    /**
     * Every colour the client page uses, as CSS custom property values.
     *
     * @return array{bg: string, surface: string, soft: string, ink: string, muted: string, line: string, accent: string, on-accent: string, highlight: string}
     */
    public function tokens(): array
    {
        return match ($this) {
            self::TealMarigold => [
                'bg' => '#FBF6EC', 'surface' => '#FFFDF8', 'soft' => '#F6E3BC', 'ink' => '#14302F', 'muted' => '#5F7372',
                'line' => '#EAD9B8', 'accent' => '#0F4C4A', 'on-accent' => '#FCE9C2', 'highlight' => '#B8661A',
            ],
            self::MidnightGold => [
                'bg' => '#12161F', 'surface' => '#181D29', 'soft' => '#1F2633', 'ink' => '#EFE9DD', 'muted' => '#A3A9B4',
                'line' => '#2A3140', 'accent' => '#C9A86A', 'on-accent' => '#12161F', 'highlight' => '#C9A86A',
            ],
            self::BlushEditorial => [
                'bg' => '#FFFFFF', 'surface' => '#FFFFFF', 'soft' => '#FBF1F2', 'ink' => '#27242A', 'muted' => '#7D7584',
                'line' => '#EFE6E8', 'accent' => '#A65A6B', 'on-accent' => '#FFFFFF', 'highlight' => '#A65A6B',
            ],
            self::RoyalCrimson => [
                'bg' => '#F8F6F2', 'surface' => '#FFFFFF', 'soft' => '#F5ECE8', 'ink' => '#1C1917', 'muted' => '#78716C',
                'line' => '#EAE4DC', 'accent' => '#7E151A', 'on-accent' => '#FFFFFF', 'highlight' => '#7E151A',
            ],
        };
    }

    /**
     * Display and body font families, and the Bunny Fonts query that loads them.
     *
     * @return array{display: string, body: string, query: string}
     */
    public function fonts(): array
    {
        return match ($this) {
            self::TealMarigold => ['display' => "'Marcellus', Georgia, serif", 'body' => "'Mulish', system-ui, sans-serif", 'query' => 'marcellus:400|mulish:400,600,700'],
            self::MidnightGold => ['display' => "'Cormorant Garamond', Georgia, serif", 'body' => "'Manrope', system-ui, sans-serif", 'query' => 'cormorant-garamond:500,600|manrope:400,600,700'],
            self::BlushEditorial => ['display' => "'Bodoni Moda', Georgia, serif", 'body' => "'Karla', system-ui, sans-serif", 'query' => 'bodoni-moda:400,600|karla:400,500,700'],
            self::RoyalCrimson => ['display' => "'Playfair Display', 'Cormorant Garamond', Georgia, serif", 'body' => "'Plus Jakarta Sans', system-ui, sans-serif", 'query' => 'playfair-display:400,600,700|plus-jakarta-sans:400,500,600,700'],
        };
    }

    /**
     * Background, accent, soft fill and ink colours, used for previews.
     *
     * @return array{background: string, accent: string, soft: string, ink: string}
     */
    public function palette(): array
    {
        return match ($this) {
            self::TealMarigold => ['background' => '#FBF6EC', 'accent' => '#0F4C4A', 'soft' => '#F6E3BC', 'ink' => '#14302F'],
            self::MidnightGold => ['background' => '#12161F', 'accent' => '#C9A86A', 'soft' => '#1B212D', 'ink' => '#EFE9DD'],
            self::BlushEditorial => ['background' => '#FFFFFF', 'accent' => '#B4697A', 'soft' => '#FBF1F2', 'ink' => '#27242A'],
            self::RoyalCrimson => ['background' => '#F8F6F2', 'accent' => '#7E151A', 'soft' => '#F5ECE8', 'ink' => '#1C1917'],
        };
    }
}

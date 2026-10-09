<?php

namespace App\Support;

use App\Enums\GstMode;

/**
 * The money maths of a quotation. All amounts are whole rupees.
 *
 *   subtotal   = crew + video + photo (included lines only)
 *   discount   = subtotal × discount%
 *   taxable    = subtotal − discount
 *   gst        = taxable × gst%   (only when GST is added to the total)
 *   calculated = taxable + gst
 *   grand      = manual total, when set, otherwise calculated
 */
final readonly class QuotationTotals
{
    public function __construct(
        public int $crew,
        public int $video,
        public int $photo,
        public int $subtotal,
        public int $discount,
        public int $taxable,
        public int $gst,
        public int $calculated,
        public int $grand,
        public int $crewCount,
        public bool $isManual,
    ) {}

    /**
     * @param  iterable<array{qty: int|string, rate: int|string}>  $crewLines
     * @param  iterable<array{qty: int|string, rate: int|string, included: bool, section: string}>  $itemLines
     */
    public static function calculate(
        iterable $crewLines,
        iterable $itemLines,
        float|int|string $discountPercent,
        float|int|string $gstPercent,
        GstMode $gstMode,
        int|string|null $manualTotal = null,
    ): self {
        $crew = 0;
        $crewCount = 0;

        foreach ($crewLines as $line) {
            $qty = max(0, (int) $line['qty']);
            $crew += $qty * max(0, (int) $line['rate']);
            $crewCount += $qty;
        }

        $video = 0;
        $photo = 0;

        foreach ($itemLines as $line) {
            if (! $line['included']) {
                continue;
            }

            $amount = max(0, (int) $line['qty']) * max(0, (int) $line['rate']);

            if ($line['section'] === 'video') {
                $video += $amount;
            } else {
                $photo += $amount;
            }
        }

        $subtotal = $crew + $video + $photo;
        $discount = (int) round($subtotal * self::percent($discountPercent) / 100);
        $taxable = $subtotal - $discount;
        $gst = $gstMode === GstMode::Included ? (int) round($taxable * self::percent($gstPercent) / 100) : 0;
        $calculated = $taxable + $gst;

        $isManual = $manualTotal !== null && $manualTotal !== '';

        return new self(
            crew: $crew,
            video: $video,
            photo: $photo,
            subtotal: $subtotal,
            discount: $discount,
            taxable: $taxable,
            gst: $gst,
            calculated: $calculated,
            grand: $isManual ? max(0, (int) $manualTotal) : $calculated,
            crewCount: $crewCount,
            isManual: $isManual,
        );
    }

    private static function percent(float|int|string $value): float
    {
        return min(100, max(0, (float) $value));
    }
}

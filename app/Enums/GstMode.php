<?php

namespace App\Enums;

/**
 * How GST is handled on a quotation.
 */
enum GstMode: string
{
    /** GST % is calculated and added to the grand total ("Including 18% GST"). */
    case Included = 'included';

    /** No GST is calculated; the client pays GST on top ("Exclusive of GST"). */
    case Extra = 'extra';

    /** GST does not apply and nothing is shown. */
    case None = 'none';

    public function label(): string
    {
        return match ($this) {
            self::Included => 'Add GST to total',
            self::Extra => 'GST extra (not added)',
            self::None => 'No GST',
        };
    }
}

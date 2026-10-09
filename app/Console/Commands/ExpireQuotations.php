<?php

namespace App\Console\Commands;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('quotations:expire')]
#[Description('Mark quotations whose validity date has passed as expired')]
class ExpireQuotations extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = Quotation::withoutGlobalScope('studio')
            ->whereIn('status', [QuotationStatus::Draft, QuotationStatus::Sent, QuotationStatus::Viewed])
            ->whereDate('valid_until', '<', today())
            ->update(['status' => QuotationStatus::Expired, 'updated_at' => now()]);

        $this->info("{$count} quotation(s) marked as expired.");

        return self::SUCCESS;
    }
}

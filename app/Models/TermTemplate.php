<?php

namespace App\Models;

use App\Enums\QuotationTemplate;
use App\Models\Concerns\BelongsToStudio;
use App\Models\Concerns\HasSortOrder;
use Database\Factories\TermTemplateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A studio's default term for wedding or corporate quotations.
 * Each new quotation copies these, so later edits never change quotations already sent.
 */
#[Fillable(['studio_id', 'template', 'title', 'body', 'sort_order'])]
class TermTemplate extends Model
{
    /** @use HasFactory<TermTemplateFactory> */
    use BelongsToStudio, HasFactory, HasSortOrder;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'template' => QuotationTemplate::class,
        ];
    }

    protected function sortGroupColumns(): array
    {
        return ['studio_id', 'template'];
    }

    /**
     * Starter terms a studio can load and then edit.
     *
     * @return list<array{title: string, body: string}>
     */
    public static function starterTerms(QuotationTemplate $template): array
    {
        return match ($template) {
            QuotationTemplate::Wedding => [
                ['title' => 'Booking', 'body' => 'Your dates are confirmed only after we receive the advance payment.'],
                ['title' => 'Travel and stay', 'body' => 'For functions outside our city, you arrange travel, stay and meals for our crew.'],
                ['title' => 'Cancellation', 'body' => 'If the booking is cancelled after the advance is paid, the advance is not refunded, because the team dates were reserved for you.'],
                ['title' => 'Change of dates', 'body' => 'If a function moves to a new date, we will cover it with the best available team. Extra charges may apply.'],
                ['title' => 'Albums', 'body' => 'Printed albums are not included unless listed in this quotation. They are billed separately after you select the photos.'],
                ['title' => 'Changes to edits', 'body' => 'Send all changes for a deliverable together, within 15 days of delivery. Further rounds of changes are charged.'],
                ['title' => 'Data storage', 'body' => 'Please give us a hard drive or SSD for your full data. We keep a backup for 3 months after delivery.'],
                ['title' => 'Drone', 'body' => 'Drone coverage depends on local permissions and weather on the day.'],
                ['title' => 'Partner services', 'body' => 'Services such as LED walls and live streaming are provided by partners. We are not responsible for their quality.'],
                ['title' => 'Portfolio use', 'body' => 'We may share selected photos and videos on our website and social media, unless you ask us not to in writing.'],
            ],
            QuotationTemplate::Corporate => [
                ['title' => 'Scope', 'body' => 'Coverage is limited to the events and hours listed. Extra hours are charged separately.'],
                ['title' => 'Payment', 'body' => '50% advance to confirm the booking. The balance is due before the final files are delivered.'],
                ['title' => 'Usage rights', 'body' => 'You receive rights to use the final photos and videos for promotion, website and social media after full payment.'],
                ['title' => 'Raw files', 'body' => 'Raw footage and project files are not included unless listed in this quotation.'],
                ['title' => 'Revisions', 'body' => 'Two rounds of changes are included for each deliverable. Further rounds are charged.'],
                ['title' => 'Cancellation', 'body' => 'If the booking is cancelled after the advance is paid, the advance is not refunded.'],
            ],
        };
    }
}

<?php

namespace App\Models;

use App\Enums\FunctionSide;
use App\Enums\FunctionType;
use App\Enums\GstMode;
use App\Enums\QuotationStatus;
use App\Enums\QuotationTemplate;
use App\Enums\UserRole;
use App\Models\Concerns\BelongsToStudio;
use App\Support\QuotationTotals;
use Database\Factories\QuotationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'studio_id', 'created_by', 'token', 'number', 'function_type', 'function_side',
    'bride', 'groom', 'client_name', 'client_phone', 'venue', 'city',
    'dates_tbd', 'start_date', 'end_date',
    'discount_percent', 'gst_percent', 'gst_mode', 'manual_total', 'show_breakdown',
    'internal_notes', 'status', 'valid_until',
    'cached_grand_total', 'cached_crew_count',
])]
class Quotation extends Model
{
    /** @use HasFactory<QuotationFactory> */
    use BelongsToStudio, HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'function_type' => FunctionType::class,
            'function_side' => FunctionSide::class,
            'status' => QuotationStatus::class,
            'gst_mode' => GstMode::class,
            'dates_tbd' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
            'valid_until' => 'date',
            'discount_percent' => 'decimal:2',
            'gst_percent' => 'decimal:2',
            'manual_total' => 'integer',
            'show_breakdown' => 'boolean',
            'sent_at' => 'datetime',
            'viewed_at' => 'datetime',
            'accepted_at' => 'datetime',
            'cached_grand_total' => 'integer',
            'cached_crew_count' => 'integer',
        ];
    }

    /**
     * Start a new draft for the user's studio with the studio's defaults, the next number and a copy of its terms.
     */
    public static function createDraft(User $user, FunctionType $functionType = FunctionType::Wedding): self
    {
        $studio = $user->studio;

        return DB::transaction(function () use ($user, $studio, $functionType): self {
            $quotation = self::create([
                'studio_id' => $studio->id,
                'created_by' => $user->id,
                'token' => Str::random(40),
                'number' => self::nextNumber($studio),
                'function_type' => $functionType,
                'function_side' => $functionType === FunctionType::Corporate ? null : FunctionSide::BothSides,
                'city' => "",
                'dates_tbd' => true,
                'discount_percent' => 0,
                'gst_percent' => $studio->gst_percent,
                'gst_mode' => (float) $studio->gst_percent > 0 ? GstMode::Included : GstMode::None,
                'show_breakdown' => $studio->show_breakdown_default,
                'status' => QuotationStatus::Draft,
                'valid_until' => now()->addDays($studio->validity_days),
            ]);

            $quotation->copyTermsFromTemplate();

            return $quotation;
        });
    }

    /**
     * Copy this quotation with its events, crew, items and terms into a new draft with a new number and link.
     * Status, signature and sent/viewed/accepted history are not copied.
     */
    public function duplicate(User $user): self
    {
        $this->loadMissing(['events.crew', 'items', 'terms', 'studio']);

        return DB::transaction(function () use ($user): self {
            $copy = $this->replicate([
                'token', 'number', 'status', 'sent_at', 'viewed_at', 'accepted_at',
                'signature_path', 'signature_method', 'signed_name', 'signer_ip', 'signer_agent',
            ]);

            $copy->fill([
                'token' => Str::random(40),
                'number' => self::nextNumber($this->studio),
                'status' => QuotationStatus::Draft,
                'created_by' => $user->id,
                'valid_until' => now()->addDays($this->studio->validity_days),
            ])->save();

            foreach ($this->events as $event) {
                $eventCopy = $copy->events()->create($event->only(['name', 'event_date', 'venue', 'note', 'sort_order']));

                foreach ($event->crew as $crew) {
                    $eventCopy->crew()->create($crew->only(['price_item_id', 'name', 'qty', 'rate', 'sort_order']));
                }
            }

            foreach ($this->items as $item) {
                $copy->items()->create($item->only(['price_item_id', 'section', 'name', 'rate', 'qty', 'note', 'delivery', 'included', 'sort_order']));
            }

            foreach ($this->terms as $term) {
                $copy->terms()->create($term->only(['title', 'body', 'sort_order']));
            }

            return $copy;
        });
    }

    /**
     * The next quotation number for the studio, e.g. MMP-2026-044. The counter restarts every year.
     */
    public static function nextNumber(Studio $studio): string
    {
        return DB::transaction(function () use ($studio): string {
            $year = (int) now()->format('Y');

            DB::table('quote_counters')->insertOrIgnore([
                'studio_id' => $studio->id,
                'year' => $year,
                'last_number' => 0,
            ]);

            $counter = DB::table('quote_counters')
                ->where('studio_id', $studio->id)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            $next = $counter->last_number + 1;

            DB::table('quote_counters')
                ->where('studio_id', $studio->id)
                ->where('year', $year)
                ->update(['last_number' => $next]);

            return sprintf('%s-%d-%03d', $studio->quote_prefix, $year, $next);
        });
    }

    /**
     * Replace this quotation's terms with the studio's current terms for its template.
     */
    public function copyTermsFromTemplate(): void
    {
        $this->terms()->delete();

        TermTemplate::withoutGlobalScopes()
            ->where('studio_id', $this->studio_id)
            ->where('template', $this->template())
            ->ordered()
            ->get()
            ->each(fn (TermTemplate $term) => $this->terms()->create([
                'title' => $term->title,
                'body' => $term->body,
                'sort_order' => $term->sort_order,
            ]));
    }

    public function template(): QuotationTemplate
    {
        return $this->function_type->template();
    }

    /**
     * "Bride & Groom", the client name for corporate quotations, or a placeholder for empty drafts.
     */
    public function displayName(): string
    {
        $couple = collect([$this->bride, $this->groom])->filter()->join(' & ');

        return $couple ?: ($this->client_name ?: 'Untitled quotation');
    }

    /**
     * "Dates TBD", "19 Nov 2026" or "28 to 29 Jan 2027".
     */
    public function datesLabel(): string
    {
        if ($this->dates_tbd || $this->start_date === null) {
            return 'Dates TBD';
        }

        $start = $this->start_date;
        $end = $this->end_date;

        if ($end === null || $end->isSameDay($start)) {
            return $start->format('j M Y');
        }

        if ($start->isSameMonth($end)) {
            return $start->format('j').' to '.$end->format('j M Y');
        }

        return $start->format('j M').' to '.$end->format('j M Y');
    }

    /**
     * The link the client opens. It needs no login, so the token is long and random.
     */
    public function publicUrl(): string
    {
        return route('quotations.public', $this->token);
    }

    /**
     * The studio's WhatsApp message with this quotation's details filled in.
     */
    public function whatsappMessage(): string
    {
        return strtr($this->studio->whatsappTemplate(), [
            '{client}' => $this->displayName(),
            '{studio}' => $this->studio->name,
            '{number}' => $this->number,
            '{link}' => $this->publicUrl(),
        ]);
    }

    /**
     * A wa.me link that opens WhatsApp with the message ready. Indian 10-digit numbers get the 91 country code.
     */
    public function whatsappUrl(): string
    {
        $digits = preg_replace('/\D+/', '', (string) $this->client_phone);

        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        if (strlen($digits) === 10) {
            $digits = '91'.$digits;
        }

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($this->whatsappMessage());
    }

    /**
     * Mark a draft as sent the first time its link is shared.
     */
    public function markShared(): void
    {
        if ($this->status === QuotationStatus::Draft) {
            $this->forceFill(['status' => QuotationStatus::Sent, 'sent_at' => $this->sent_at ?? now()])->save();
        }
    }

    /**
     * Record the client's first visit. Drafts and sent quotations become "Viewed"; later statuses stay as they are.
     * Returns true only for the first visit.
     */
    public function markViewed(): bool
    {
        if ($this->viewed_at !== null) {
            return false;
        }

        $this->viewed_at = now();

        if (in_array($this->status, [QuotationStatus::Draft, QuotationStatus::Sent], true)) {
            $this->status = QuotationStatus::Viewed;
        }

        $this->save();

        return true;
    }

    /**
     * Who hears about client activity: the studio's active admins and the person who made the quotation.
     *
     * @return Collection<int, User>
     */
    public function teamToNotify(): Collection
    {
        return User::where('studio_id', $this->studio_id)
            ->where('is_active', true)
            ->where(fn ($query) => $query->where('role', UserRole::StudioAdmin)->orWhere('id', $this->created_by))
            ->get();
    }

    public function isAccepted(): bool
    {
        return $this->status === QuotationStatus::Accepted;
    }

    /**
     * Whether the client can still accept online: not accepted yet, not expired and the studio is active.
     */
    public function canBeAccepted(): bool
    {
        return ! $this->isAccepted() && ! $this->isExpired() && $this->studio->isActive();
    }

    /**
     * Record the client's acceptance. A drawn signature is stored privately as a PNG; a typed one is just the name.
     *
     * @param  string|null  $signaturePng  Raw PNG bytes for a drawn signature.
     */
    public function accept(string $signedName, ?string $signaturePng, string $ip, ?string $userAgent): void
    {
        $path = null;

        if ($signaturePng !== null) {
            $path = 'signatures/'.$this->studio_id.'/'.$this->id.'-'.Str::random(16).'.png';
            Storage::disk('local')->put($path, $signaturePng);
        }

        $this->forceFill([
            'status' => QuotationStatus::Accepted,
            'accepted_at' => now(),
            'signed_name' => $signedName,
            'signature_path' => $path,
            'signature_method' => $path ? 'draw' : 'type',
            'signer_ip' => $ip,
            'signer_agent' => $userAgent ? mb_substr($userAgent, 0, 500) : null,
        ])->save();
    }

    /**
     * Undo an acceptance so the studio can change the quotation. The signature is deleted and the client has to sign again.
     */
    public function unlockForRevision(): void
    {
        if ($this->signature_path) {
            Storage::disk('local')->delete($this->signature_path);
        }

        $this->forceFill([
            'status' => QuotationStatus::Sent,
            'accepted_at' => null,
            'signed_name' => null,
            'signature_path' => null,
            'signature_method' => null,
            'signer_ip' => null,
            'signer_agent' => null,
        ])->save();
    }

    /**
     * The drawn signature as a data URI, so the private file can be shown without a public URL.
     */
    public function signatureDataUri(): ?string
    {
        if (! $this->signature_path || ! Storage::disk('local')->exists($this->signature_path)) {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode(Storage::disk('local')->get($this->signature_path));
    }

    public function isExpired(): bool
    {
        return $this->valid_until !== null
            && $this->valid_until->endOfDay()->isPast()
            && $this->status !== QuotationStatus::Accepted;
    }

    /**
     * "Wedding Photography Quotation" or "Corporate Photography Quotation".
     */
    public function documentTitle(): string
    {
        return $this->function_type->label().' Photography Quotation';
    }

    /**
     * Totals from the saved lines. Loads events, crew and items when they are not loaded yet.
     */
    public function totals(): QuotationTotals
    {
        $this->loadMissing(['events.crew', 'items']);

        return QuotationTotals::calculate(
            crewLines: $this->events->flatMap->crew->map(fn (QuotationEventCrew $crew) => ['qty' => $crew->qty, 'rate' => $crew->rate]),
            itemLines: $this->items->map(fn (QuotationItem $item) => [
                'section' => $item->section->value,
                'qty' => $item->qty,
                'rate' => $item->rate,
                'included' => $item->included,
            ]),
            discountPercent: $this->discount_percent,
            gstPercent: $this->gst_percent,
            gstMode: $this->gst_mode,
            manualTotal: $this->manual_total,
        );
    }

    /**
     * Recalculate and store the grand total and crew count shown on the quotation list.
     */
    public function refreshCachedTotals(): QuotationTotals
    {
        $totals = $this->unsetRelation('events')->unsetRelation('items')->totals();

        $this->forceFill([
            'cached_grand_total' => $totals->grand,
            'cached_crew_count' => $totals->crewCount,
        ])->saveQuietly();

        return $totals;
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<QuotationEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(QuotationEvent::class)->ordered();
    }

    /**
     * @return HasMany<QuotationItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->ordered();
    }

    /**
     * @return HasMany<QuotationTerm, $this>
     */
    public function terms(): HasMany
    {
        return $this->hasMany(QuotationTerm::class)->ordered();
    }
}

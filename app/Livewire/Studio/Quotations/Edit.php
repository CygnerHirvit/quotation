<?php

namespace App\Livewire\Studio\Quotations;

use App\Enums\FunctionSide;
use App\Enums\FunctionType;
use App\Enums\GstMode;
use App\Enums\ItemSection;
use App\Enums\PriceCategory;
use App\Enums\QuotationStatus;
use App\Models\PriceItem;
use App\Models\Quotation;
use App\Models\QuotationEvent;
use App\Models\QuotationEventCrew;
use App\Models\QuotationItem;
use App\Models\QuotationTerm;
use App\Models\User;
use App\Support\QuotationTotals;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * The quotation editor. Every change is saved as soon as a field is left or a button is pressed.
 *
 * Events, crew and items are held as plain lists (each row carries its database id) so their order survives
 * the round trip to the browser. The updated() hook maps a changed path back to the row and column to save.
 */
#[Title('Quotation')]
class Edit extends Component
{
    public Quotation $quotation;

    public string $bride = '';

    public int $bridePosition = 1;

    public string $groom = '';

    public int $groomPosition = 2;

    public string $clientName = '';

    public string $clientPhone = '';

    public string $venue = '';

    public string $city = '';

    public bool $datesTbd = true;

    public string $startDate = '';

    public string $endDate = '';

    public string $validUntil = '';

    public string $functionType = '';

    public string $functionSide = '';

    public string $status = '';

    public string $createdBy = '';

    public string $discountPercent = '0';

    public string $gstPercent = '0';

    public string $gstMode = '';

    public string $manualTotal = '';

    public bool $showBreakdown = false;

    public string $internalNotes = '';

    /** @var list<array{id: int, name: string, date: string, venue: string, note: string, crew: list<array{id: int, name: string, qty: int, rate: int}>}> */
    public array $events = [];

    /** @var array{video: list<array<string, mixed>>, photo: list<array<string, mixed>>} */
    public array $items = ['video' => [], 'photo' => []];

    /** @var list<array{id: int, title: string, body: string}> */
    public array $terms = [];

    /**
     * Detail properties and the quotation column each one is saved to.
     *
     * @var array<string, string>
     */
    protected const DETAIL_COLUMNS = [
        'bride' => 'bride',
        'bridePosition' => 'bride_position',
        'groom' => 'groom',
        'groomPosition' => 'groom_position',
        'clientName' => 'client_name',
        'clientPhone' => 'client_phone',
        'venue' => 'venue',
        'city' => 'city',
        'datesTbd' => 'dates_tbd',
        'startDate' => 'start_date',
        'endDate' => 'end_date',
        'validUntil' => 'valid_until',
        'functionType' => 'function_type',
        'functionSide' => 'function_side',
        'status' => 'status',
        'createdBy' => 'created_by',
        'discountPercent' => 'discount_percent',
        'gstPercent' => 'gst_percent',
        'gstMode' => 'gst_mode',
        'manualTotal' => 'manual_total',
        'showBreakdown' => 'show_breakdown',
        'internalNotes' => 'internal_notes',
    ];

    public function mount(Quotation $quotation): void
    {
        $this->authorize('update', $quotation);

        $this->quotation = $quotation;
        $this->fillDetails();
        $this->loadEvents();
        $this->loadItems();
        $this->loadTerms();
    }

    /**
     * Re-check access on every request, not only on the first page load.
     */
    public function hydrate(): void
    {
        $this->authorize('update', $this->quotation);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $line = ['required', 'string', 'max:150'];
        $optional = ['nullable', 'string', 'max:190'];
        $qty = ['required', 'integer', 'min:1', 'max:99'];
        $rate = ['required', 'integer', 'min:0', 'max:100000000'];

        return [
            'bride' => ['nullable', 'string', 'max:120'],
            'bridePosition' => ['required', 'integer', 'in:1,2'],
            'groom' => ['nullable', 'string', 'max:120'],
            'groomPosition' => ['required', 'integer', 'in:1,2'],
            'clientName' => ['nullable', 'string', 'max:150'],
            'clientPhone' => ['nullable', 'string', 'max:30'],
            'venue' => ['nullable', 'string', 'max:190'],
            'city' => ['nullable', 'string', 'max:80'],
            'datesTbd' => ['boolean'],
            'startDate' => ['nullable', 'date'],
            'endDate' => ['nullable', 'date', 'after_or_equal:startDate'],
            'validUntil' => ['required', 'date'],
            'functionType' => ['required', Rule::enum(FunctionType::class)],
            'functionSide' => ['nullable', Rule::enum(FunctionSide::class)],
            'status' => ['required', Rule::in(array_map(fn (QuotationStatus $status) => $status->value, QuotationStatus::selectable()))],
            'createdBy' => ['required', Rule::exists('users', 'id')->where('studio_id', $this->quotation->studio_id)],
            'discountPercent' => ['required', 'numeric', 'min:0', 'max:100'],
            'gstPercent' => ['required', 'numeric', 'min:0', 'max:100'],
            'gstMode' => ['required', Rule::enum(GstMode::class)],
            'manualTotal' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'showBreakdown' => ['boolean'],
            'internalNotes' => ['nullable', 'string', 'max:5000'],

            'events.*.name' => $line,
            'events.*.date' => ['nullable', 'date'],
            'events.*.venue' => $optional,
            'events.*.note' => $optional,
            'events.*.crew.*.name' => $line,
            'events.*.crew.*.qty' => $qty,
            'events.*.crew.*.rate' => $rate,

            'items.*.*.name' => $line,
            'items.*.*.rate' => $rate,
            'items.*.*.qty' => $qty,
            'items.*.*.note' => $optional,
            'items.*.*.delivery' => $optional,

            'terms.*.title' => ['nullable', 'string', 'max:120'],
            'terms.*.body' => ['required', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'bridePosition' => 'bride position',
            'groomPosition' => 'groom position',
            'endDate' => 'end date',
            'startDate' => 'start date',
            'validUntil' => 'valid until date',
            'discountPercent' => 'discount',
            'gstPercent' => 'GST',
            'manualTotal' => 'final amount',
            'events.*.name' => 'function name',
            'events.*.crew.*.name' => 'crew name',
            'events.*.crew.*.qty' => 'quantity',
            'events.*.crew.*.rate' => 'rate',
            'items.*.*.name' => 'item name',
            'items.*.*.rate' => 'rate',
            'items.*.*.qty' => 'quantity',
            'terms.*.body' => 'term text',
        ];
    }

    /**
     * Save a single changed field.
     */
    public function updated(string $property, mixed $value = null): void
    {
        $this->ensureEditable();

        $this->validateOnly($property);

        if ($property === 'bridePosition') {
            $this->bridePosition = (int) $this->bridePosition;
            $this->groomPosition = $this->bridePosition === 1 ? 2 : 1;
            $this->quotation->bride_position = $this->bridePosition;
            $this->quotation->groom_position = $this->groomPosition;
            $this->quotation->save();
            $this->quotation->refreshCachedTotals();

            return;
        }

        if ($property === 'groomPosition') {
            $this->groomPosition = (int) $this->groomPosition;
            $this->bridePosition = $this->groomPosition === 1 ? 2 : 1;
            $this->quotation->groom_position = $this->groomPosition;
            $this->quotation->bride_position = $this->bridePosition;
            $this->quotation->save();
            $this->quotation->refreshCachedTotals();

            return;
        }

        $segments = explode('.', $property);

        match (true) {
            array_key_exists($property, self::DETAIL_COLUMNS) => $this->saveDetail($property),
            $segments[0] === 'events' && count($segments) === 3 => $this->saveEventField((int) $segments[1], $segments[2]),
            $segments[0] === 'events' && count($segments) === 5 => $this->saveCrewField((int) $segments[1], (int) $segments[3], $segments[4]),
            $segments[0] === 'items' && count($segments) === 4 => $this->saveItemField($segments[1], (int) $segments[2], $segments[3]),
            $segments[0] === 'terms' && count($segments) === 3 => $this->saveTermField((int) $segments[1], $segments[2]),
            default => null,
        };

        $this->quotation->refreshCachedTotals();
    }

    protected function saveDetail(string $property): void
    {
        $column = self::DETAIL_COLUMNS[$property];
        $value = $this->{$property};

        if (is_string($value) && $value === '' && ! in_array($property, ['discountPercent', 'gstPercent'], true)) {
            $value = null;
        }

        $previousTemplate = $this->quotation->template();

        $this->quotation->{$column} = $value;

        if ($property === 'status' && $value === QuotationStatus::Sent->value && $this->quotation->sent_at === null) {
            $this->quotation->sent_at = now();
        }

        if ($property === 'status' && $value === QuotationStatus::Accepted->value) {
            $this->quotation->accepted_at = now();
        }

        // Extending an expired quotation reopens it for the client.
        if ($property === 'validUntil' && $this->quotation->status === QuotationStatus::Expired && ! $this->quotation->isExpired()) {
            $this->quotation->status = $this->quotation->viewed_at ? QuotationStatus::Viewed : QuotationStatus::Sent;
            $this->status = $this->quotation->status->value;
        }

        if ($property === 'functionType' && $this->quotation->function_type === FunctionType::Corporate) {
            $this->quotation->function_side = null;
            $this->functionSide = '';
        }

        $this->quotation->save();

        if ($property === 'functionType' && $this->quotation->template() !== $previousTemplate) {
            $this->quotation->copyTermsFromTemplate();
            $this->loadTerms();
            session()->flash('status', "Terms switched to your {$this->quotation->template()->label()} terms.");
        }
    }

    protected function saveEventField(int $index, string $field): void
    {
        $columns = ['name' => 'name', 'date' => 'event_date', 'venue' => 'venue', 'note' => 'note'];

        if (! isset($columns[$field], $this->events[$index])) {
            return;
        }

        $value = $this->events[$index][$field];

        $this->findEvent($this->events[$index]['id'])->update([$columns[$field] => $value === '' ? null : $value]);
    }

    protected function saveCrewField(int $eventIndex, int $crewIndex, string $field): void
    {
        if (! in_array($field, ['name', 'qty', 'rate'], true) || ! isset($this->events[$eventIndex]['crew'][$crewIndex])) {
            return;
        }

        $row = $this->events[$eventIndex]['crew'][$crewIndex];

        $this->findCrew($row['id'])->update([$field => $row[$field]]);
    }

    protected function saveItemField(string $section, int $index, string $field): void
    {
        if (! in_array($field, ['name', 'rate', 'qty', 'note', 'delivery'], true) || ! isset($this->items[$section][$index])) {
            return;
        }

        $value = $this->items[$section][$index][$field];

        $this->findItem($this->items[$section][$index]['id'])->update([$field => $value === '' ? null : $value]);
    }

    protected function saveTermField(int $index, string $field): void
    {
        if (! in_array($field, ['title', 'body'], true) || ! isset($this->terms[$index])) {
            return;
        }

        $value = $this->terms[$index][$field];

        $this->findTerm($this->terms[$index]['id'])->update([$field => $value === '' ? null : $value]);
    }

    public function clearManualTotal(): void
    {
        $this->ensureEditable();

        $this->manualTotal = '';
        $this->quotation->update(['manual_total' => null]);
        $this->quotation->refreshCachedTotals();
    }

    public function useCalculatedAsManualTotal(): void
    {
        $this->ensureEditable();

        $this->manualTotal = (string) $this->calculateTotals()->calculated;
        $this->quotation->update(['manual_total' => (int) $this->manualTotal]);
        $this->quotation->refreshCachedTotals();
    }

    // Events ------------------------------------------------------------------------------------

    public function addEvent(string $name = ''): void
    {
        $this->ensureEditable();

        $this->quotation->events()->create([
            'name' => trim($name) !== '' ? mb_substr(trim($name), 0, 150) : 'New function',
            'venue' => $this->quotation->city,
        ]);

        $this->loadEvents();
    }

    public function removeEvent(int $eventId): void
    {
        $this->ensureEditable();

        $this->findEvent($eventId)->delete();

        $this->loadEvents();
        $this->quotation->refreshCachedTotals();
    }

    public function sortEvents(int $eventId, int $position): void
    {
        $this->ensureEditable();

        $this->findEvent($eventId)->moveToPosition($position);

        $this->loadEvents();
    }

    /**
     * Add a crew line from the price list ("custom" adds an empty line).
     */
    public function addCrew(int $eventId, string $choice): void
    {
        $this->ensureEditable();

        $event = $this->findEvent($eventId);
        $priceItem = $this->priceItemFor($choice, PriceCategory::Crew);

        if ($choice !== 'custom' && $priceItem === null) {
            return;
        }

        $event->crew()->create([
            'price_item_id' => $priceItem?->id,
            'name' => $priceItem?->name ?? 'Crew member',
            'rate' => $priceItem?->rate ?? 0,
            'qty' => 1,
        ]);

        $this->loadEvents();
        $this->quotation->refreshCachedTotals();
    }

    public function removeCrew(int $crewId): void
    {
        $this->ensureEditable();

        $this->findCrew($crewId)->delete();

        $this->loadEvents();
        $this->quotation->refreshCachedTotals();
    }

    public function changeCrewQty(int $crewId, int $delta): void
    {
        $this->ensureEditable();

        $crew = $this->findCrew($crewId);
        $crew->update(['qty' => max(1, min(99, $crew->qty + $delta))]);

        $this->loadEvents();
        $this->quotation->refreshCachedTotals();
    }

    // Video and photo items ------------------------------------------------------------------------

    public function addItem(string $section, string $choice): void
    {
        $this->ensureEditable();

        $itemSection = ItemSection::tryFrom($section);

        if ($itemSection === null) {
            return;
        }

        $priceItem = $this->priceItemFor($choice, $itemSection->priceCategory());

        if ($choice !== 'custom' && $priceItem === null) {
            return;
        }

        $this->quotation->items()->create([
            'section' => $itemSection,
            'price_item_id' => $priceItem?->id,
            'name' => $priceItem?->name ?? 'Custom item',
            'rate' => $priceItem?->rate ?? 0,
            'qty' => 1,
            'note' => $priceItem?->note,
            'delivery' => $priceItem?->delivery,
            'included' => true,
        ]);

        $this->loadItems();
        $this->quotation->refreshCachedTotals();
    }

    public function removeItem(int $itemId): void
    {
        $this->ensureEditable();

        $this->findItem($itemId)->delete();

        $this->loadItems();
        $this->quotation->refreshCachedTotals();
    }

    public function changeItemQty(int $itemId, int $delta): void
    {
        $this->ensureEditable();

        $item = $this->findItem($itemId);
        $item->update(['qty' => max(1, min(99, $item->qty + $delta))]);

        $this->loadItems();
        $this->quotation->refreshCachedTotals();
    }

    public function toggleIncluded(int $itemId): void
    {
        $this->ensureEditable();

        $item = $this->findItem($itemId);
        $item->update(['included' => ! $item->included]);

        $this->loadItems();
        $this->quotation->refreshCachedTotals();
    }

    public function sortItems(int $itemId, int $position): void
    {
        $this->ensureEditable();

        $this->findItem($itemId)->moveToPosition($position);

        $this->loadItems();
    }

    // Terms ------------------------------------------------------------------------------------

    public function addTerm(): void
    {
        $this->ensureEditable();

        $this->quotation->terms()->create(['title' => null, 'body' => 'New term']);

        $this->loadTerms();
    }

    public function removeTerm(int $termId): void
    {
        $this->ensureEditable();

        $this->findTerm($termId)->delete();

        $this->loadTerms();
    }

    public function sortTerms(int $termId, int $position): void
    {
        $this->ensureEditable();

        $this->findTerm($termId)->moveToPosition($position);

        $this->loadTerms();
    }

    /**
     * Throw away this quotation's terms and copy the studio's current ones again.
     */
    public function resetTerms(): void
    {
        $this->ensureEditable();

        $this->quotation->copyTermsFromTemplate();

        $this->loadTerms();

        session()->flash('status', "Terms reset to your current {$this->quotation->template()->label()} terms.");
    }

    /**
     * Accepted quotations are locked so the signed version can't change. The studio must unlock them first.
     */
    protected function ensureEditable(): void
    {
        abort_if($this->quotation->isAccepted(), 403, 'This quotation is accepted. Unlock it to make changes.');
    }

    /**
     * Clear the client's signature so the quotation can be changed. The client will need to accept again.
     */
    public function unlockForRevision(): void
    {
        if (! $this->quotation->isAccepted()) {
            return;
        }

        $this->quotation->unlockForRevision();
        $this->status = $this->quotation->status->value;

        session()->flash('status', 'Unlocked. Share the link again so the client can accept the new version.');
    }

    /**
     * Swap the display order of the Bride and Groom names.
     */
    public function swapPositions(): void
    {
        $this->ensureEditable();

        $oldBride = $this->bridePosition;
        $this->bridePosition = $this->groomPosition;
        $this->groomPosition = $oldBride;

        if ($this->bridePosition === $this->groomPosition) {
            $this->bridePosition = 1;
            $this->groomPosition = 2;
        }

        $this->quotation->bride_position = $this->bridePosition;
        $this->quotation->groom_position = $this->groomPosition;
        $this->quotation->save();
        $this->quotation->refreshCachedTotals();
    }

    /**
     * Called when the share link is copied or sent on WhatsApp. A draft becomes "Sent".
     */
    public function markShared(): void
    {
        $this->quotation->markShared();
        $this->status = $this->quotation->status->value;
    }

    public function duplicate(): void
    {
        $this->authorize('create', Quotation::class);

        $copy = $this->quotation->duplicate(auth()->user());

        session()->flash('status', "Copied {$this->quotation->number} to {$copy->number}.");

        $this->redirectRoute('studio.quotations.edit', $copy, navigate: true);
    }

    // Loading ------------------------------------------------------------------------------------

    protected function fillDetails(): void
    {
        $quotation = $this->quotation;

        $this->bride = (string) $quotation->bride;
        $this->bridePosition = (int) ($quotation->bride_position ?? 1);
        $this->groom = (string) $quotation->groom;
        $this->groomPosition = (int) ($quotation->groom_position ?? 2);
        $this->clientName = (string) $quotation->client_name;
        $this->clientPhone = (string) $quotation->client_phone;
        $this->venue = (string) $quotation->venue;
        $this->city = (string) $quotation->city;
        $this->datesTbd = (bool) $quotation->dates_tbd;
        $this->startDate = (string) $quotation->start_date?->format('Y-m-d');
        $this->endDate = (string) $quotation->end_date?->format('Y-m-d');
        $this->validUntil = (string) $quotation->valid_until?->format('Y-m-d');
        $this->functionType = $quotation->function_type->value;
        $this->functionSide = (string) $quotation->function_side?->value;
        $this->status = $quotation->status->value;
        $this->createdBy = (string) $quotation->created_by;
        $this->discountPercent = (string) (float) $quotation->discount_percent;
        $this->gstPercent = (string) (float) $quotation->gst_percent;
        $this->gstMode = $quotation->gst_mode->value;
        $this->manualTotal = (string) $quotation->manual_total;
        $this->showBreakdown = (bool) $quotation->show_breakdown;
        $this->internalNotes = (string) $quotation->internal_notes;
    }

    protected function loadEvents(): void
    {
        $this->events = $this->quotation->events()->with('crew')->get()
            ->map(fn (QuotationEvent $event) => [
                'id' => $event->id,
                'name' => $event->name,
                'date' => (string) $event->event_date?->format('Y-m-d'),
                'venue' => (string) $event->venue,
                'note' => (string) $event->note,
                'crew' => $event->crew->map(fn (QuotationEventCrew $crew) => [
                    'id' => $crew->id,
                    'name' => $crew->name,
                    'qty' => $crew->qty,
                    'rate' => $crew->rate,
                ])->all(),
            ])
            ->all();
    }

    protected function loadItems(): void
    {
        $items = $this->quotation->items()->get();

        foreach (ItemSection::cases() as $section) {
            $this->items[$section->value] = $items
                ->filter(fn (QuotationItem $item) => $item->section === $section)
                ->map(fn (QuotationItem $item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'rate' => $item->rate,
                    'qty' => $item->qty,
                    'note' => (string) $item->note,
                    'delivery' => (string) $item->delivery,
                    'included' => $item->included,
                ])
                ->values()
                ->all();
        }
    }

    protected function loadTerms(): void
    {
        $this->terms = $this->quotation->terms()->get()
            ->map(fn (QuotationTerm $term) => ['id' => $term->id, 'title' => (string) $term->title, 'body' => $term->body])
            ->all();
    }

    // Lookups that keep every id inside this quotation -----------------------------------------------

    protected function findEvent(int $eventId): QuotationEvent
    {
        return $this->quotation->events()->findOrFail($eventId);
    }

    protected function findCrew(int $crewId): QuotationEventCrew
    {
        return QuotationEventCrew::query()
            ->whereHas('event', fn ($query) => $query->where('quotation_id', $this->quotation->id))
            ->findOrFail($crewId);
    }

    protected function findItem(int $itemId): QuotationItem
    {
        return $this->quotation->items()->findOrFail($itemId);
    }

    protected function findTerm(int $termId): QuotationTerm
    {
        return $this->quotation->terms()->findOrFail($termId);
    }

    protected function priceItemFor(string $choice, PriceCategory $category): ?PriceItem
    {
        if (! ctype_digit($choice)) {
            return null;
        }

        return PriceItem::where('category', $category)->where('is_active', true)->find((int) $choice);
    }

    /**
     * Totals from the current form state, so the summary updates without reloading from the database.
     */
    protected function calculateTotals(): QuotationTotals
    {
        $itemLines = collect(ItemSection::cases())->flatMap(
            fn (ItemSection $section) => collect($this->items[$section->value])->map(fn (array $item) => [...$item, 'section' => $section->value])
        );

        return QuotationTotals::calculate(
            crewLines: collect($this->events)->flatMap(fn (array $event) => $event['crew']),
            itemLines: $itemLines,
            discountPercent: is_numeric($this->discountPercent) ? $this->discountPercent : 0,
            gstPercent: is_numeric($this->gstPercent) ? $this->gstPercent : 0,
            gstMode: GstMode::tryFrom($this->gstMode) ?? GstMode::None,
            manualTotal: ctype_digit($this->manualTotal) ? $this->manualTotal : null,
        );
    }

    /**
     * Active price list items of a category, grouped for the "Add from price list" menus.
     *
     * @return Collection<int, PriceItem>
     */
    protected function priceOptions(PriceCategory $category): Collection
    {
        return PriceItem::where('category', $category)->where('is_active', true)->ordered()->get(['id', 'name', 'internal_label', 'rate']);
    }

    public function render(): View
    {
        $functionType = FunctionType::tryFrom($this->functionType) ?? FunctionType::Wedding;

        return view('livewire.studio.quotations.edit', [
            'isLocked' => $this->quotation->isAccepted(),
            'signatureImage' => $this->quotation->isAccepted() ? $this->quotation->signatureDataUri() : null,
            'totals' => $this->calculateTotals(),
            'isCorporate' => $functionType === FunctionType::Corporate,
            'functionTypes' => FunctionType::cases(),
            'functionSides' => FunctionSide::cases(),
            'statuses' => QuotationStatus::selectable(),
            'gstModes' => GstMode::cases(),
            'sections' => ItemSection::cases(),
            'team' => User::where('studio_id', $this->quotation->studio_id)->orderBy('name')->get(['id', 'name']),
            'crewOptions' => $this->priceOptions(PriceCategory::Crew),
            'itemOptions' => [
                ItemSection::Video->value => $this->priceOptions(PriceCategory::Video),
                ItemSection::Photo->value => $this->priceOptions(PriceCategory::Photo),
            ],
            'eventSuggestions' => Arr::get([
                'wedding' => ['Engagement', 'Haldi', 'Mehendi', 'Sangeet', 'Garba', 'Wedding', 'Reception', 'Pre-wedding'],
                'corporate' => ['Conference', 'Product Launch', 'Award Night', 'Team Outing'],
            ], $functionType->template()->value, []),
        ]);
    }
}

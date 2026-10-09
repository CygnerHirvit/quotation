<?php

namespace App\Livewire\Studio;

use App\Enums\PriceCategory;
use App\Models\PriceItem;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Price list')]
class PriceList extends Component
{
    #[Url]
    public string $category = PriceCategory::Crew->value;

    /** Id of the item being edited, 0 for a new item, null when the form is closed. */
    public ?int $editingId = null;

    public string $name = '';

    public string $internalLabel = '';

    public string $rate = '';

    public string $note = '';

    public string $delivery = '';

    public bool $isActive = true;

    public function mount(): void
    {
        $this->authorize('manage-studio-content');

        if (PriceCategory::tryFrom($this->category) === null) {
            $this->category = PriceCategory::Crew->value;
        }
    }

    public function updatedCategory(): void
    {
        if (PriceCategory::tryFrom($this->category) === null) {
            $this->category = PriceCategory::Crew->value;
        }

        $this->closeForm();
    }

    public function create(): void
    {
        $this->closeForm();
        $this->editingId = 0;
    }

    public function edit(int $itemId): void
    {
        $item = PriceItem::findOrFail($itemId);

        $this->closeForm();
        $this->editingId = $item->id;
        $this->name = $item->name;
        $this->internalLabel = (string) $item->internal_label;
        $this->rate = (string) $item->rate;
        $this->note = (string) $item->note;
        $this->delivery = (string) $item->delivery;
        $this->isActive = $item->is_active;
    }

    public function closeForm(): void
    {
        $this->reset('editingId', 'name', 'internalLabel', 'rate', 'note', 'delivery', 'isActive');
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->authorize('manage-studio-content');

        $validated = $this->validate([
            'category' => ['required', Rule::enum(PriceCategory::class)],
            'name' => ['required', 'string', 'max:150'],
            'internalLabel' => ['nullable', 'string', 'max:60'],
            'rate' => ['required', 'integer', 'min:0', 'max:100000000'],
            'note' => ['nullable', 'string', 'max:190'],
            'delivery' => ['nullable', 'string', 'max:190'],
            'isActive' => ['boolean'],
        ], attributes: ['internalLabel' => 'internal label']);

        $attributes = [
            'name' => $validated['name'],
            'internal_label' => $validated['internalLabel'] ?: null,
            'rate' => (int) $validated['rate'],
            'note' => $validated['note'] ?: null,
            'delivery' => $validated['delivery'] ?: null,
            'is_active' => $validated['isActive'],
        ];

        if ($this->editingId) {
            PriceItem::findOrFail($this->editingId)->update($attributes);
            session()->flash('status', "{$validated['name']} updated.");
        } else {
            PriceItem::create([...$attributes, 'category' => $this->category]);
            session()->flash('status', "{$validated['name']} added.");
        }

        $this->closeForm();
    }

    public function toggleActive(int $itemId): void
    {
        $this->authorize('manage-studio-content');

        $item = PriceItem::findOrFail($itemId);
        $item->update(['is_active' => ! $item->is_active]);
    }

    public function delete(int $itemId): void
    {
        $this->authorize('manage-studio-content');

        $item = PriceItem::findOrFail($itemId);
        $item->delete();

        if ($this->editingId === $itemId) {
            $this->closeForm();
        }

        session()->flash('status', "{$item->name} deleted. Quotations that already use it keep their copy.");
    }

    /**
     * Called by wire:sort after an item is dragged.
     */
    public function sort(int $itemId, int $position): void
    {
        $this->authorize('manage-studio-content');

        PriceItem::findOrFail($itemId)->moveToPosition($position);
    }

    public function render(): View
    {
        $current = PriceCategory::from($this->category);

        return view('livewire.studio.price-list', [
            'categories' => PriceCategory::cases(),
            'current' => $current,
            'items' => PriceItem::where('category', $current)->ordered()->get(),
            'counts' => PriceItem::query()->toBase()->selectRaw('category, count(*) as total')->groupBy('category')->pluck('total', 'category'),
        ]);
    }
}

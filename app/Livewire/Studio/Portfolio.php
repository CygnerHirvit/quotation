<?php

namespace App\Livewire\Studio;

use App\Enums\PortfolioType;
use App\Models\PortfolioItem;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Title('Portfolio')]
class Portfolio extends Component
{
    use WithFileUploads;

    /** Id of the item being edited, 0 for a new item, null when the form is closed. */
    public ?int $editingId = null;

    public string $type = PortfolioType::Album->value;

    public string $title = '';

    public string $url = '';

    /** @var TemporaryUploadedFile|null */
    public $cover = null;

    public bool $removeCover = false;

    public bool $isActive = true;

    public function mount(): void
    {
        $this->authorize('manage-studio-content');
    }

    public function create(string $type): void
    {
        $this->closeForm();
        $this->editingId = 0;
        $this->type = PortfolioType::tryFrom($type)?->value ?? PortfolioType::Album->value;
    }

    public function edit(int $itemId): void
    {
        $item = PortfolioItem::findOrFail($itemId);

        $this->closeForm();
        $this->editingId = $item->id;
        $this->type = $item->type->value;
        $this->title = $item->title;
        $this->url = $item->url;
        $this->isActive = $item->is_active;
    }

    public function closeForm(): void
    {
        $this->reset('editingId', 'type', 'title', 'url', 'cover', 'removeCover', 'isActive');
        $this->resetValidation();
    }

    public function updatedCover(): void
    {
        $this->removeCover = false;
        $this->validateOnly('cover');
    }

    public function clearSelectedCover(): void
    {
        $this->cover = null;
        $this->resetValidation('cover');
    }

    public function markCoverForRemoval(): void
    {
        $this->removeCover = true;
        $this->cover = null;
        $this->resetValidation('cover');
    }

    public function undoCoverRemoval(): void
    {
        $this->removeCover = false;
    }

    public function removeCover(): void
    {
        $this->markCoverForRemoval();
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(PortfolioType::class)],
            'title' => ['required', 'string', 'max:150'],
            'url' => ['required', 'url:http,https', 'max:500'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'isActive' => ['boolean'],
        ];
    }

    public function save(): void
    {
        $this->authorize('manage-studio-content');

        $validated = $this->validate();

        $item = $this->editingId ? PortfolioItem::findOrFail($this->editingId) : new PortfolioItem(['type' => $validated['type']]);

        $item->fill([
            'title' => $validated['title'],
            'url' => $validated['url'],
            'is_active' => $validated['isActive'],
        ]);

        if ($this->removeCover) {
            if ($item->cover_path) {
                $previous = $item->cover_path;
                $item->cover_path = null;

                if (Storage::disk('public')->exists($previous)) {
                    Storage::disk('public')->delete($previous);
                }
            }
        } elseif ($this->cover !== null) {
            $previous = $item->cover_path;
            $item->cover_path = $this->cover->store('studios/portfolio', 'public');

            if ($previous && Storage::disk('public')->exists($previous)) {
                Storage::disk('public')->delete($previous);
            }
        }

        $item->save();

        session()->flash('status', $this->editingId ? "{$item->title} updated." : "{$item->title} added.");

        $this->closeForm();
    }

    public function toggleActive(int $itemId): void
    {
        $this->authorize('manage-studio-content');

        $item = PortfolioItem::findOrFail($itemId);
        $item->update(['is_active' => ! $item->is_active]);
    }

    public function delete(int $itemId): void
    {
        $this->authorize('manage-studio-content');

        $item = PortfolioItem::findOrFail($itemId);

        if ($item->cover_path) {
            Storage::disk('public')->delete($item->cover_path);
        }

        $item->delete();

        if ($this->editingId === $itemId) {
            $this->closeForm();
        }

        session()->flash('status', "{$item->title} deleted.");
    }

    /**
     * Called by wire:sort after an item is dragged.
     */
    public function sort(int $itemId, int $position): void
    {
        $this->authorize('manage-studio-content');

        PortfolioItem::findOrFail($itemId)->moveToPosition($position);
    }

    public function render(): View
    {
        $items = PortfolioItem::ordered()->get()->groupBy(fn (PortfolioItem $item) => $item->type->value);

        return view('livewire.studio.portfolio', [
            'types' => PortfolioType::cases(),
            'itemsByType' => $items,
            'editingItem' => $this->editingId ? PortfolioItem::find($this->editingId) : null,
        ]);
    }
}

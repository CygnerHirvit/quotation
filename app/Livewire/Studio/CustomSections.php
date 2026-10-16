<?php

namespace App\Livewire\Studio;

use App\Models\CustomSection;
use App\Models\CustomSectionItem;
use App\Models\Studio;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Custom sections')]
class CustomSections extends Component
{
    #[Url(as: 'section')]
    public ?int $selectedSectionId = null;

    /** Section form state: null = closed, 0 = new, >0 = editing */
    public ?int $editingSectionId = null;

    public string $sectionTitle = '';

    public string $sectionTagline = '';

    public string $sectionPosition = 'after_coverage';

    public bool $sectionIsActive = true;

    /** Item form state: null = closed, 0 = new, >0 = editing */
    public ?int $editingItemId = null;

    public string $itemTitle = '';

    public string $itemDescription = '';

    public bool $itemIsActive = true;

    public static function positionOptions(): array
    {
        return [
            'after_cover' => 'After Cover (Position 02)',
            'after_coverage' => 'After Coverage / Functions (Position 03)',
            'after_deliverables' => 'After Inclusions / Deliverables (Position 04)',
            'after_price' => 'After Price / Investment (Position 05)',
        ];
    }

    public function mount(): void
    {
        $this->authorize('manage-studio-content');

        $studio = $this->studio();

        if (! $this->selectedSectionId) {
            $first = $studio->customSections()->ordered()->first();
            $this->selectedSectionId = $first?->id;
        } else {
            // Verify selected section belongs to studio
            $exists = $studio->customSections()->whereKey($this->selectedSectionId)->exists();
            if (! $exists) {
                $first = $studio->customSections()->ordered()->first();
                $this->selectedSectionId = $first?->id;
            }
        }
    }

    protected function studio(): Studio
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        return $user->studio;
    }

    public function selectSection(int $sectionId): void
    {
        $this->selectedSectionId = $sectionId;
        $this->closeSectionForm();
        $this->closeItemForm();
    }

    // -------------------------------------------------------------
    // Section Management
    // -------------------------------------------------------------

    public function createSection(): void
    {
        $this->closeItemForm();
        $this->editingSectionId = 0;
        $this->sectionTitle = '';
        $this->sectionTagline = '';
        $this->sectionPosition = 'after_coverage';
        $this->sectionIsActive = true;
        $this->resetValidation();
    }

    public function editSection(int $sectionId): void
    {
        $this->closeItemForm();
        $section = $this->studio()->customSections()->findOrFail($sectionId);

        $this->editingSectionId = $section->id;
        $this->sectionTitle = $section->title;
        $this->sectionTagline = (string) $section->tagline;
        $this->sectionPosition = $section->position;
        $this->sectionIsActive = (bool) $section->is_active;
        $this->resetValidation();
    }

    public function closeSectionForm(): void
    {
        $this->editingSectionId = null;
        $this->reset('sectionTitle', 'sectionTagline', 'sectionPosition', 'sectionIsActive');
        $this->resetValidation();
    }

    public function saveSection(): void
    {
        $this->authorize('manage-studio-content');

        $validated = $this->validate([
            'sectionTitle' => ['required', 'string', 'max:120'],
            'sectionTagline' => ['nullable', 'string', 'max:255'],
            'sectionPosition' => ['required', 'string', Rule::in(array_keys(self::positionOptions()))],
            'sectionIsActive' => ['boolean'],
        ]);

        $attributes = [
            'title' => $validated['sectionTitle'],
            'tagline' => $validated['sectionTagline'] ?: null,
            'position' => $validated['sectionPosition'],
            'is_active' => $validated['sectionIsActive'],
        ];

        if ($this->editingSectionId) {
            $section = $this->studio()->customSections()->findOrFail($this->editingSectionId);
            $oldPosition = $section->position;
            $newPosition = $validated['sectionPosition'];

            if ($oldPosition !== $newPosition) {
                $maxOrder = (int) $this->studio()->customSections()
                    ->where('position', $newPosition)
                    ->max('sort_order');
                $attributes['sort_order'] = $maxOrder + 1;
            }

            $section->update($attributes);

            if ($oldPosition !== $newPosition) {
                $this->renumberPosition($oldPosition);
            }

            session()->flash('status', 'Section "'.$section->title.'" updated.');
        } else {
            $section = $this->studio()->customSections()->create($attributes);
            $this->selectedSectionId = $section->id;
            session()->flash('status', 'Section "'.$section->title.'" created.');
        }

        $this->closeSectionForm();
    }

    public function toggleSectionActive(int $sectionId): void
    {
        $this->authorize('manage-studio-content');

        $section = $this->studio()->customSections()->findOrFail($sectionId);
        $section->update(['is_active' => ! $section->is_active]);

        session()->flash('status', 'Section "'.$section->title.'" '.($section->is_active ? 'enabled' : 'hidden').' on quotations.');
    }

    public function deleteSection(int $sectionId): void
    {
        $this->authorize('manage-studio-content');

        $section = $this->studio()->customSections()->findOrFail($sectionId);
        $title = $section->title;
        $position = $section->position;
        $section->delete();

        $this->renumberPosition($position);

        if ($this->selectedSectionId === $sectionId) {
            $first = $this->studio()->customSections()->ordered()->first();
            $this->selectedSectionId = $first?->id;
        }

        if ($this->editingSectionId === $sectionId) {
            $this->closeSectionForm();
        }

        session()->flash('status', 'Section "'.$title.'" deleted.');
    }

    public function moveSectionUp(int $sectionId): void
    {
        $this->authorize('manage-studio-content');

        $section = $this->studio()->customSections()->findOrFail($sectionId);

        $siblings = $this->studio()->customSections()
            ->where('position', $section->position)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $index = $siblings->search(fn ($s) => $s->id === $sectionId);

        if ($index === false || $index === 0) {
            return;
        }

        DB::transaction(function () use ($siblings, $index): void {
            $item = $siblings->splice($index, 1)->first();
            $siblings->splice($index - 1, 0, [$item]);

            foreach ($siblings->values() as $idx => $s) {
                $s->update(['sort_order' => $idx + 1]);
            }
        });

        session()->flash('status', '"'.$section->title.'" is now showing earlier in '.self::positionOptions()[$section->position].'.');
    }

    public function moveSectionDown(int $sectionId): void
    {
        $this->authorize('manage-studio-content');

        $section = $this->studio()->customSections()->findOrFail($sectionId);

        $siblings = $this->studio()->customSections()
            ->where('position', $section->position)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $index = $siblings->search(fn ($s) => $s->id === $sectionId);

        if ($index === false || $index >= $siblings->count() - 1) {
            return;
        }

        DB::transaction(function () use ($siblings, $index): void {
            $item = $siblings->splice($index, 1)->first();
            $siblings->splice($index + 1, 0, [$item]);

            foreach ($siblings->values() as $idx => $s) {
                $s->update(['sort_order' => $idx + 1]);
            }
        });

        session()->flash('status', '"'.$section->title.'" is now showing later in '.self::positionOptions()[$section->position].'.');
    }

    public function sortSections(int $sectionId, int $position): void
    {
        $this->authorize('manage-studio-content');

        $section = $this->studio()->customSections()->findOrFail($sectionId);
        $section->moveToPosition($position);

        session()->flash('status', 'Section order updated.');
    }

    protected function renumberPosition(string $position): void
    {
        $sections = $this->studio()->customSections()
            ->where('position', $position)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        foreach ($sections->values() as $idx => $s) {
            $s->update(['sort_order' => $idx + 1]);
        }
    }

    // -------------------------------------------------------------
    // Item Management
    // -------------------------------------------------------------

    public function createItem(): void
    {
        $this->closeSectionForm();
        $this->editingItemId = 0;
        $this->itemTitle = '';
        $this->itemDescription = '';
        $this->itemIsActive = true;
        $this->resetValidation();
    }

    public function editItem(int $itemId): void
    {
        $this->closeSectionForm();
        $item = CustomSectionItem::whereHas('customSection', function ($q) {
            $q->where('studio_id', $this->studio()->id);
        })->findOrFail($itemId);

        $this->editingItemId = $item->id;
        $this->itemTitle = $item->title;
        $this->itemDescription = $item->description;
        $this->itemIsActive = (bool) $item->is_active;
        $this->resetValidation();
    }

    public function closeItemForm(): void
    {
        $this->editingItemId = null;
        $this->reset('itemTitle', 'itemDescription', 'itemIsActive');
        $this->resetValidation();
    }

    public function saveItem(): void
    {
        $this->authorize('manage-studio-content');

        if (! $this->selectedSectionId) {
            return;
        }

        $section = $this->studio()->customSections()->findOrFail($this->selectedSectionId);

        $validated = $this->validate([
            'itemTitle' => ['required', 'string', 'max:160'],
            'itemDescription' => ['required', 'string', 'max:2000'],
            'itemIsActive' => ['boolean'],
        ]);

        $attributes = [
            'title' => $validated['itemTitle'],
            'description' => $validated['itemDescription'],
            'is_active' => $validated['itemIsActive'],
        ];

        if ($this->editingItemId) {
            $item = $section->items()->findOrFail($this->editingItemId);
            $item->update($attributes);
            session()->flash('status', 'Item "'.$item->title.'" updated.');
        } else {
            $item = $section->items()->create($attributes);
            session()->flash('status', 'Item "'.$item->title.'" added.');
        }

        $this->closeItemForm();
    }

    public function toggleItemActive(int $itemId): void
    {
        $this->authorize('manage-studio-content');

        $item = CustomSectionItem::whereHas('customSection', function ($q) {
            $q->where('studio_id', $this->studio()->id);
        })->findOrFail($itemId);

        $item->update(['is_active' => ! $item->is_active]);

        session()->flash('status', 'Item "'.$item->title.'" '.($item->is_active ? 'enabled' : 'hidden').'.');
    }

    public function deleteItem(int $itemId): void
    {
        $this->authorize('manage-studio-content');

        $item = CustomSectionItem::whereHas('customSection', function ($q) {
            $q->where('studio_id', $this->studio()->id);
        })->findOrFail($itemId);

        $title = $item->title;
        $item->delete();

        if ($this->editingItemId === $itemId) {
            $this->closeItemForm();
        }

        session()->flash('status', 'Item "'.$title.'" deleted.');
    }

    public function sortItems(int $itemId, int $position): void
    {
        $this->authorize('manage-studio-content');

        $item = CustomSectionItem::whereHas('customSection', function ($q) {
            $q->where('studio_id', $this->studio()->id);
        })->findOrFail($itemId);

        $item->moveToPosition($position);
    }


    public function render(): View
    {
        $studio = $this->studio();
        $sections = $studio->customSections()->withCount('items')->ordered()->get();

        $selectedSection = $this->selectedSectionId
            ? $sections->firstWhere('id', $this->selectedSectionId)
            : $sections->first();

        $items = $selectedSection
            ? $selectedSection->items()->ordered()->get()
            : collect();

        return view('livewire.studio.custom-sections', [
            'studio' => $studio,
            'sections' => $sections,
            'selectedSection' => $selectedSection,
            'items' => $items,
            'positionOptions' => self::positionOptions(),
        ]);
    }
}


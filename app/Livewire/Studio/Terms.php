<?php

namespace App\Livewire\Studio;

use App\Enums\QuotationTemplate;
use App\Models\TermTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Terms & conditions')]
class Terms extends Component
{
    #[Url]
    public string $template = QuotationTemplate::Wedding->value;

    /** Id of the term being edited, 0 for a new term, null when the form is closed. */
    public ?int $editingId = null;

    public string $title = '';

    public string $body = '';

    public function mount(): void
    {
        $this->authorize('manage-studio-content');

        if (QuotationTemplate::tryFrom($this->template) === null) {
            $this->template = QuotationTemplate::Wedding->value;
        }
    }

    public function updatedTemplate(): void
    {
        if (QuotationTemplate::tryFrom($this->template) === null) {
            $this->template = QuotationTemplate::Wedding->value;
        }

        $this->closeForm();
    }

    public function create(): void
    {
        $this->closeForm();
        $this->editingId = 0;
    }

    public function edit(int $termId): void
    {
        $term = TermTemplate::findOrFail($termId);

        $this->closeForm();
        $this->editingId = $term->id;
        $this->title = (string) $term->title;
        $this->body = $term->body;
    }

    public function closeForm(): void
    {
        $this->reset('editingId', 'title', 'body');
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->authorize('manage-studio-content');

        $validated = $this->validate([
            'title' => ['nullable', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $attributes = ['title' => $validated['title'] ?: null, 'body' => $validated['body']];

        if ($this->editingId) {
            TermTemplate::findOrFail($this->editingId)->update($attributes);
            session()->flash('status', 'Term updated.');
        } else {
            TermTemplate::create([...$attributes, 'template' => $this->currentTemplate()]);
            session()->flash('status', 'Term added.');
        }

        $this->closeForm();
    }

    public function delete(int $termId): void
    {
        $this->authorize('manage-studio-content');

        TermTemplate::findOrFail($termId)->delete();

        if ($this->editingId === $termId) {
            $this->closeForm();
        }

        session()->flash('status', 'Term deleted.');
    }

    /**
     * Fill an empty list with starter terms the studio can then edit.
     */
    public function loadStarterTerms(): void
    {
        $this->authorize('manage-studio-content');

        $template = $this->currentTemplate();

        if (TermTemplate::where('template', $template)->exists()) {
            return;
        }

        DB::transaction(function () use ($template): void {
            foreach (TermTemplate::starterTerms($template) as $term) {
                TermTemplate::create([...$term, 'template' => $template]);
            }
        });

        session()->flash('status', 'Starter terms added. Edit them to match how your studio works.');
    }

    /**
     * Called by wire:sort after a term is dragged.
     */
    public function sort(int $termId, int $position): void
    {
        $this->authorize('manage-studio-content');

        TermTemplate::findOrFail($termId)->moveToPosition($position);
    }

    protected function currentTemplate(): QuotationTemplate
    {
        return QuotationTemplate::from($this->template);
    }

    public function render(): View
    {
        $current = $this->currentTemplate();

        return view('livewire.studio.terms', [
            'templates' => QuotationTemplate::cases(),
            'current' => $current,
            'terms' => TermTemplate::where('template', $current)->ordered()->get(),
        ]);
    }
}

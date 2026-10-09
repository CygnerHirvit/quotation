<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-semibold">Terms &amp; conditions</h1>
            <p class="mt-1 text-muted">Copied into each new quotation. Changing them here doesn't change quotations already created.</p>
        </div>
        @if ($editingId === null)
            <button type="button" wire:click="create" class="btn-primary">+ Add term</button>
        @endif
    </div>

    @include('partials.flash')

    <div class="flex gap-2" role="tablist">
        @foreach ($templates as $templateOption)
            <button
                type="button"
                role="tab"
                aria-selected="{{ $current === $templateOption ? 'true' : 'false' }}"
                wire:click="$set('template', '{{ $templateOption->value }}')"
                @class([
                    'rounded-full px-4 py-2 text-sm font-semibold transition',
                    'bg-ink text-white' => $current === $templateOption,
                    'border border-line bg-surface text-muted hover:text-ink' => $current !== $templateOption,
                ])
            >{{ $templateOption->label() }}</button>
        @endforeach
    </div>

    @if ($editingId !== null)
        <form wire:submit="save" class="card space-y-5 p-6">
            <h2 class="text-lg font-semibold">{{ $editingId ? 'Edit term' : 'New '.strtolower($current->label()).' term' }}</h2>
            <div>
                <label for="term-title" class="label">Title (optional)</label>
                <input id="term-title" type="text" wire:model="title" class="input" placeholder="Cancellation">
                @error('title') <p class="input-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="term-body" class="label">Text</label>
                <textarea id="term-body" wire:model="body" rows="4" class="input"></textarea>
                @error('body') <p class="input-error">{{ $message }}</p> @enderror
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" wire:click="closeForm" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">{{ $editingId ? 'Save' : 'Add term' }}</button>
            </div>
        </form>
    @endif

    <div class="card">
        @if ($terms->isEmpty())
            <div class="space-y-3 px-6 py-14 text-center">
                <p class="font-medium">No {{ strtolower($current->label()) }} terms yet</p>
                <p class="text-sm text-muted">Start with our sample terms and edit them, or write your own.</p>
                <button type="button" wire:click="loadStarterTerms" class="btn-secondary">Load sample terms</button>
            </div>
        @else
            <ol wire:sort="sort" class="divide-y divide-line">
                @foreach ($terms as $term)
                    <li wire:key="term-{{ $term->id }}" wire:sort:item="{{ $term->id }}" class="flex items-start gap-3 bg-surface px-4 py-4">
                        <button type="button" wire:sort:handle class="cursor-grab px-1 text-lg leading-none text-muted" aria-label="Drag to reorder">⋮⋮</button>
                        <span class="w-6 shrink-0 pt-0.5 text-sm font-semibold text-muted tabular-nums">{{ $loop->iteration }}</span>
                        <div class="min-w-0 flex-1">
                            @if ($term->title)
                                <p class="font-semibold">{{ $term->title }}</p>
                            @endif
                            <p class="text-sm whitespace-pre-line text-muted">{{ $term->body }}</p>
                        </div>
                        <div class="flex gap-1" wire:sort:ignore>
                            <button type="button" wire:click="edit({{ $term->id }})" class="btn-secondary px-3">Edit</button>
                            <button type="button" wire:click="delete({{ $term->id }})" wire:confirm="Delete this term?" class="btn-danger px-3" aria-label="Delete term">✕</button>
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif
    </div>
</div>

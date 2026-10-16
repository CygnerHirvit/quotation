<div class="mx-auto max-w-4xl space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight text-ink">Custom sections</h1>
            <p class="mt-1 text-sm text-muted">Create and manage dynamic quotation pages like Scope of work, Process, or Why Choose Us.</p>
        </div>
        @if ($editingSectionId === null && $editingItemId === null)
            <button type="button" wire:click="createSection" class="btn-primary !py-2 !px-4 text-sm flex items-center gap-1.5 self-start sm:self-auto shrink-0 shadow-xs">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Add section</span>
            </button>
        @endif
    </div>

    @include('partials.flash')

    {{-- Section Creation/Editing Form --}}
    @if ($editingSectionId !== null)
        <form wire:submit="saveSection" class="card space-y-5 p-5 sm:p-6 shadow-sm border border-brand-200">
            <div class="flex items-center justify-between border-b border-line pb-4">
                <h2 class="text-base sm:text-lg font-semibold text-ink">
                    {{ $editingSectionId ? 'Edit section: '.$sectionTitle : 'New custom section' }}
                </h2>
                <button type="button" wire:click="closeSectionForm" class="p-1 rounded-lg text-muted hover:text-ink hover:bg-canvas transition" title="Close">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="section-title" class="label">Section title <span class="text-red-500">*</span></label>
                    <input id="section-title" type="text" wire:model="sectionTitle" class="input" placeholder="Scope of work">
                    @error('sectionTitle') <p class="input-error text-xs">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="section-tagline" class="label">Highlight tagline banner <span class="text-xs text-muted font-normal">(optional)</span></label>
                    <input id="section-tagline" type="text" wire:model="sectionTagline" class="input" placeholder="e.g. Timelessly planned & captured">
                    <p class="mt-1 text-xs text-muted">Displays in an accent-colored banner pill across the section, matching the design.</p>
                    @error('sectionTagline') <p class="input-error text-xs">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="section-position" class="label">Quotation placement <span class="text-red-500">*</span></label>
                    <select id="section-position" wire:model="sectionPosition" class="input">
                        @foreach ($positionOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-muted">Where this section appears in client quotations. Multiple sections in the same placement can be ordered anytime.</p>
                    @error('sectionPosition') <p class="input-error text-xs">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label">Status</label>
                    <label for="section-is-active" class="flex items-center gap-3 p-3 rounded-xl border border-line bg-canvas/40 cursor-pointer text-sm font-medium text-ink transition hover:bg-canvas">
                        <input id="section-is-active" type="checkbox" wire:model="sectionIsActive" class="size-4 rounded border-line text-brand-600 focus:ring-brand-500">
                        <span>Active on client quotation &amp; PDF</span>
                    </label>
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2.5 pt-4 border-t border-line">
                <button type="button" wire:click="closeSectionForm" class="btn-secondary w-full sm:w-auto justify-center">
                    Cancel
                </button>
                <button type="submit" class="btn-primary w-full sm:w-auto justify-center">
                    {{ $editingSectionId ? 'Save changes' : 'Create section' }}
                </button>
            </div>
        </form>
    @endif

    {{-- Section Selector Tabs --}}
    @if ($sections->isNotEmpty())
        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none" role="tablist">
            @foreach ($sections as $sec)
                <button
                    type="button"
                    role="tab"
                    aria-selected="{{ $selectedSection && $selectedSection->id === $sec->id ? 'true' : 'false' }}"
                    wire:click="selectSection({{ $sec->id }})"
                    @class([
                        'rounded-full px-3.5 py-1.5 text-xs sm:text-sm font-semibold transition shrink-0 flex items-center gap-2 shadow-2xs',
                        'bg-ink text-white shadow-xs' => $selectedSection && $selectedSection->id === $sec->id,
                        'border border-line bg-surface text-muted hover:text-ink hover:bg-canvas' => ! $selectedSection || $selectedSection->id !== $sec->id,
                        'opacity-60' => ! $sec->is_active,
                    ])
                >
                    <span @class([
                        'text-[10px] sm:text-xs font-mono font-bold px-1.5 py-0.5 rounded',
                        'bg-white/20 text-white' => $selectedSection && $selectedSection->id === $sec->id,
                        'bg-canvas text-ink/80 border border-line/60' => ! $selectedSection || $selectedSection->id !== $sec->id,
                    ])>
                        {{ match($sec->position) {
                            'after_cover' => '02',
                            'after_coverage' => '03',
                            'after_deliverables' => '04',
                            'after_price' => '05',
                            default => '—'
                        } }}
                    </span>
                    <span>{{ $sec->title }}</span>
                    <span @class([
                        'text-[11px] sm:text-xs px-2 py-0.5 rounded-full font-bold',
                        'bg-white/20 text-white' => $selectedSection && $selectedSection->id === $sec->id,
                        'bg-canvas text-muted border border-line/60' => ! $selectedSection || $selectedSection->id !== $sec->id,
                    ])>{{ $sec->items_count }}</span>
                    @if (! $sec->is_active)
                        <span class="text-[10px] text-amber-500 font-bold" title="Hidden from quotation">●</span>
                    @endif
                </button>
            @endforeach

            @if ($editingSectionId === null)
                <button type="button" wire:click="createSection" class="rounded-full border border-dashed border-line px-3.5 py-1.5 text-xs sm:text-sm font-semibold text-muted hover:text-brand-600 hover:border-brand-500 transition shrink-0 flex items-center gap-1.5">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>New section</span>
                </button>
            @endif
        </div>
    @endif

    {{-- Selected Section Card --}}
    @if (! $selectedSection)
        <div class="card p-10 sm:p-14 text-center space-y-4">
            <div class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
                <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-ink">No custom sections yet</h3>
                <p class="text-sm text-muted mt-1 max-w-sm mx-auto">Create your first custom section like Scope of work or Process.</p>
            </div>
            <button type="button" wire:click="createSection" class="btn-primary !py-2 !px-4 text-sm">
                + Add your first custom section
            </button>
        </div>
    @else
        {{-- Section Overview & Actions Bar --}}
        <div class="card p-5 sm:p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div class="space-y-2 min-w-0">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h2 class="text-xl sm:text-2xl font-bold text-ink truncate">{{ $selectedSection->title }}</h2>
                        <span @class([
                            'text-xs font-semibold px-2.5 py-0.5 rounded-full shrink-0',
                            'bg-emerald-100 text-emerald-800' => $selectedSection->is_active,
                            'bg-gray-100 text-gray-600' => ! $selectedSection->is_active,
                        ])>
                            {{ $selectedSection->is_active ? 'Active' : 'Hidden from quotation' }}
                        </span>
                    </div>

                    @php
                        $siblingsInPosition = $sections->where('position', $selectedSection->position)->values();
                        $siblingCount = $siblingsInPosition->count();
                        $currentIndex = $siblingsInPosition->search(fn ($s) => $s->id === $selectedSection->id);
                        $currentNum = $currentIndex !== false ? $currentIndex + 1 : 1;
                    @endphp

                    <div class="flex items-center gap-2.5 text-xs text-muted flex-wrap">
                        <div class="flex items-center gap-1.5">
                            <span class="text-muted">Placement:</span>
                            <span class="font-semibold text-ink bg-canvas px-2.5 py-1 rounded-md border border-line/60">
                                {{ match($selectedSection->position) {
                                    'after_cover' => 'After Cover (Position 02)',
                                    'after_coverage' => 'After Coverage / Functions (Position 03)',
                                    'after_deliverables' => 'After Inclusions / Deliverables (Position 04)',
                                    'after_price' => 'After Price / Investment (Position 05)',
                                    default => $selectedSection->position
                                } }}
                            </span>
                        </div>

                        @if ($siblingCount > 1)
                            <div class="flex items-center gap-2 pl-0 sm:border-l sm:border-line sm:pl-3 w-full sm:w-auto">
                                <span class="text-muted">Order:</span>
                                <span class="font-bold px-2.5 py-1 rounded-md bg-brand-50 text-brand-700 border border-brand-200">
                                    #{{ $currentNum }} of {{ $siblingCount }}
                                    ({{ $currentNum === 1 ? 'Shows first' : ($currentNum === 2 && $siblingCount === 2 ? 'Shows second' : 'Order #'.$currentNum) }})
                                </span>

                                <div class="inline-flex items-center rounded-lg border border-line bg-surface p-0.5 shadow-2xs">
                                    <button
                                        type="button"
                                        wire:click="moveSectionUp({{ $selectedSection->id }})"
                                        @disabled($currentIndex === 0)
                                        class="p-1 rounded text-muted hover:text-ink disabled:opacity-30 disabled:cursor-not-allowed hover:bg-canvas transition"
                                        title="{{ $currentIndex === 0 ? 'Already showing first' : 'Move earlier (show first)' }}"
                                    >
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="moveSectionDown({{ $selectedSection->id }})"
                                        @disabled($currentIndex >= $siblingCount - 1)
                                        class="p-1 rounded text-muted hover:text-ink disabled:opacity-30 disabled:cursor-not-allowed hover:bg-canvas transition"
                                        title="{{ $currentIndex >= $siblingCount - 1 ? 'Already showing last' : 'Move later (show after)' }}"
                                    >
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0 flex-wrap">
                    <button type="button"
                            wire:click="toggleSectionActive({{ $selectedSection->id }})"
                            class="btn-secondary !py-1.5 !px-3 text-xs">
                        {{ $selectedSection->is_active ? 'Hide section' : 'Enable section' }}
                    </button>
                    <button type="button"
                            wire:click="editSection({{ $selectedSection->id }})"
                            class="btn-secondary !py-1.5 !px-3 text-xs flex items-center gap-1.5">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        <span>Edit</span>
                    </button>
                    <button type="button"
                            wire:click="deleteSection({{ $selectedSection->id }})"
                            wire:confirm="Delete section '{{ $selectedSection->title }}' and all its items?"
                            class="btn-secondary !py-1.5 !px-3 text-xs text-red-600 hover:text-red-700 hover:bg-red-50 flex items-center gap-1.5">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span>Delete</span>
                    </button>
                </div>
            </div>

            {{-- Highlight banner preview if set --}}
            @if ($selectedSection->tagline)
                <div class="rounded-xl border border-line bg-canvas/40 p-3.5 space-y-1.5">
                    <p class="text-[11px] font-bold text-muted uppercase tracking-wider">Highlight Tagline Banner</p>
                    <div class="inline-block rounded-full px-4 py-1.5 text-xs font-bold shadow-xs break-words max-w-full"
                         style="background-color: {{ $studio->themeTokens()['accent'] }}; color: {{ $studio->themeTokens()['on-accent'] }}">
                        {{ $selectedSection->tagline }}
                    </div>
                </div>
            @endif
        </div>

        {{-- Item Form --}}
        @if ($editingItemId !== null)
            <form wire:submit="saveItem" class="card space-y-4 p-5 sm:p-6 shadow-sm border border-brand-200">
                <div class="flex items-center justify-between border-b border-line pb-3">
                    <h3 class="text-base font-semibold text-ink">
                        {{ $editingItemId ? 'Edit item' : 'New item for '.$selectedSection->title }}
                    </h3>
                    <button type="button" wire:click="closeItemForm" class="p-1 rounded-lg text-muted hover:text-ink hover:bg-canvas transition" title="Close">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div>
                    <label for="item-title" class="label">Item title <span class="text-red-500">*</span></label>
                    <input id="item-title" type="text" wire:model="itemTitle" class="input" placeholder="e.g. Creative Planning">
                    @error('itemTitle') <p class="input-error text-xs">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="item-description" class="label">Description <span class="text-red-500">*</span></label>
                    <textarea id="item-description" wire:model="itemDescription" rows="3" class="input" placeholder="Describe what is included..."></textarea>
                    @error('itemDescription') <p class="input-error text-xs">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="item-is-active" class="flex items-center gap-2.5 cursor-pointer text-sm font-medium text-ink">
                        <input id="item-is-active" type="checkbox" wire:model="itemIsActive" class="size-4 rounded border-line text-brand-600 focus:ring-brand-500">
                        <span>Active (visible on quotation)</span>
                    </label>
                </div>

                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2.5 pt-3 border-t border-line">
                    <button type="button" wire:click="closeItemForm" class="btn-secondary w-full sm:w-auto justify-center">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary w-full sm:w-auto justify-center">
                        {{ $editingItemId ? 'Save changes' : 'Add item' }}
                    </button>
                </div>
            </form>
        @endif

        {{-- Items List Card --}}
        <div class="card overflow-hidden">
            <div class="p-5 sm:p-6 flex flex-wrap items-center justify-between gap-3 border-b border-line bg-surface">
                <div>
                    <h3 class="text-base sm:text-lg font-semibold text-ink">Items in {{ $selectedSection->title }}</h3>
                    <p class="text-xs text-muted mt-0.5">Drag any item to reorder how it appears on the page.</p>
                </div>
                @if ($editingItemId === null)
                    <button type="button" wire:click="createItem" class="btn-primary !py-1.5 !px-3.5 text-xs flex items-center gap-1.5 shrink-0 shadow-xs">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>Add item</span>
                    </button>
                @endif
            </div>

            @if ($items->isEmpty())
                <div class="px-6 py-12 text-center space-y-3">
                    <p class="font-medium text-ink">No items in this section yet</p>
                    <p class="text-xs sm:text-sm text-muted max-w-sm mx-auto">Add points or items to appear in this section on client quotations.</p>
                    <div class="pt-1">
                        <button type="button" wire:click="createItem" class="btn-primary !py-1.5 !px-3.5 text-xs">
                            + Add item
                        </button>
                    </div>
                </div>
            @else
                <ol wire:sort="sortItems" class="divide-y divide-line">
                    @foreach ($items as $item)
                        <li wire:key="item-row-{{ $item->id }}"
                            wire:sort:item="{{ $item->id }}"
                            @class([
                                'flex items-start gap-3 sm:gap-4 p-4 sm:p-5 bg-surface transition hover:bg-canvas/30',
                                'opacity-50' => ! $item->is_active,
                            ])>
                            {{-- Drag Handle --}}
                            <button type="button"
                                    wire:sort:handle
                                    class="mt-1 cursor-grab text-muted hover:text-ink active:cursor-grabbing p-1 rounded-md hover:bg-canvas transition shrink-0"
                                    aria-label="Drag to reorder item">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9h.01M16 9h.01M8 15h.01M16 15h.01" />
                                </svg>
                            </button>

                            {{-- Details --}}
                            <div class="min-w-0 flex-1 space-y-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h4 class="font-bold text-sm text-ink leading-snug">{{ $item->title }}</h4>
                                    @if (! $item->is_active)
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">Hidden</span>
                                    @endif
                                </div>
                                <p class="text-xs sm:text-sm text-muted leading-relaxed">{{ $item->description }}</p>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="flex items-center gap-1 shrink-0 self-start">
                                <button type="button"
                                        wire:click="toggleItemActive({{ $item->id }})"
                                        class="p-1.5 rounded-lg text-muted hover:text-ink hover:bg-canvas transition"
                                        title="{{ $item->is_active ? 'Hide item' : 'Enable item' }}">
                                    @if ($item->is_active)
                                        <svg class="size-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    @else
                                        <svg class="size-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                        </svg>
                                    @endif
                                </button>
                                <button type="button"
                                        wire:click="editItem({{ $item->id }})"
                                        class="p-1.5 rounded-lg text-muted hover:text-ink hover:bg-canvas transition"
                                        title="Edit item">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <button type="button"
                                        wire:click="deleteItem({{ $item->id }})"
                                        wire:confirm="Delete '{{ $item->title }}'?"
                                        class="p-1.5 rounded-lg text-muted hover:text-red-600 hover:bg-canvas transition"
                                        title="Delete item">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>
    @endif

    {{-- Quotation Sequence & Section Ordering Card --}}
    @if ($sections->isNotEmpty())
        <div class="card p-5 sm:p-6 space-y-5 bg-canvas/30 border border-line">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 border-b border-line pb-3">
                <div>
                    <h3 class="text-sm font-bold text-ink">Quotation sequence &amp; section order</h3>
                    <p class="text-xs text-muted mt-0.5">Control which section appears first when multiple sections share the same placement position.</p>
                </div>
                <span class="text-xs text-muted">Pages auto-numbered on web quotation &amp; PDF</span>
            </div>

            @php
                $groupedPositions = [
                    'after_cover' => [
                        'label' => 'After Cover (Position 02)',
                        'sections' => $sections->where('position', 'after_cover')->values(),
                    ],
                    'after_coverage' => [
                        'label' => 'After Coverage / Functions (Position 03)',
                        'sections' => $sections->where('position', 'after_coverage')->values(),
                    ],
                    'after_deliverables' => [
                        'label' => 'After Inclusions / Deliverables (Position 04)',
                        'sections' => $sections->where('position', 'after_deliverables')->values(),
                    ],
                    'after_price' => [
                        'label' => 'After Price / Investment (Position 05)',
                        'sections' => $sections->where('position', 'after_price')->values(),
                    ],
                ];
            @endphp

            {{-- Positions with multiple sections --}}
            <div class="space-y-4">
                @foreach ($groupedPositions as $posKey => $posData)
                    @if ($posData['sections']->count() > 1)
                        <div class="rounded-xl border border-line bg-surface p-4 space-y-3 shadow-2xs">
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <div class="flex items-center gap-2">
                                    <span class="size-2 rounded-full bg-brand-500"></span>
                                    <h4 class="text-xs sm:text-sm font-bold text-ink">{{ $posData['label'] }}</h4>
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-brand-50 text-brand-700 font-semibold border border-brand-200">
                                        {{ $posData['sections']->count() }} sections
                                    </span>
                                </div>
                                <span class="text-[11px] text-muted">Drag or use arrows to change which section shows first</span>
                            </div>

                            <ol wire:sort="sortSections" class="divide-y divide-line/60 rounded-lg border border-line overflow-hidden bg-canvas/30">
                                @foreach ($posData['sections'] as $idx => $sec)
                                    <li wire:key="pos-sec-{{ $sec->id }}"
                                        wire:sort:item="{{ $sec->id }}"
                                        @class([
                                            'flex items-center justify-between gap-3 p-3 transition',
                                            'bg-brand-50/40' => $selectedSection && $selectedSection->id === $sec->id,
                                            'bg-surface hover:bg-canvas/50' => ! $selectedSection || $selectedSection->id !== $sec->id,
                                            'opacity-60' => ! $sec->is_active,
                                        ])>
                                        <div class="flex items-center gap-3 min-w-0">
                                            <button type="button"
                                                    wire:sort:handle
                                                    class="cursor-grab text-muted hover:text-ink active:cursor-grabbing p-1 rounded hover:bg-canvas transition shrink-0"
                                                    aria-label="Drag to reorder {{ $sec->title }}">
                                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9h.01M16 9h.01M8 15h.01M16 15h.01" />
                                                </svg>
                                            </button>

                                            <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-md bg-canvas border border-line text-ink shrink-0">
                                                #{{ $idx + 1 }}
                                            </span>

                                            <div class="min-w-0">
                                                <button type="button" wire:click="selectSection({{ $sec->id }})" class="text-sm font-bold text-ink hover:text-brand-600 truncate text-left block">
                                                    {{ $sec->title }}
                                                </button>
                                                <div class="flex items-center gap-2 text-[11px] text-muted">
                                                    <span>{{ $idx === 0 ? 'Shows first' : ($idx === 1 && $posData['sections']->count() === 2 ? 'Shows second' : 'Order #'.($idx + 1)) }}</span>
                                                    <span>·</span>
                                                    <span>{{ $sec->items_count }} {{ \Illuminate\Support\Str::plural('item', $sec->items_count) }}</span>
                                                    @if (! $sec->is_active)
                                                        <span>·</span>
                                                        <span class="text-amber-600 font-semibold">Hidden</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-1 shrink-0" wire:sort:ignore>
                                            <button
                                                type="button"
                                                wire:click="moveSectionUp({{ $sec->id }})"
                                                @disabled($idx === 0)
                                                class="p-1.5 rounded-lg border border-line bg-surface text-muted hover:text-ink disabled:opacity-30 disabled:cursor-not-allowed hover:bg-canvas transition"
                                                title="{{ $idx === 0 ? 'Already showing first' : 'Move up (show earlier)' }}"
                                            >
                                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                                                </svg>
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="moveSectionDown({{ $sec->id }})"
                                                @disabled($idx >= $posData['sections']->count() - 1)
                                                class="p-1.5 rounded-lg border border-line bg-surface text-muted hover:text-ink disabled:opacity-30 disabled:cursor-not-allowed hover:bg-canvas transition"
                                                title="{{ $idx >= $posData['sections']->count() - 1 ? 'Already showing last' : 'Move down (show later)' }}"
                                            >
                                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                            @if (! $selectedSection || $selectedSection->id !== $sec->id)
                                                <button type="button" wire:click="selectSection({{ $sec->id }})" class="btn-secondary !py-1 !px-2.5 text-xs ml-1">
                                                    Manage
                                                </button>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Full Page Flow Preview --}}
            <div class="space-y-2 pt-1">
                <span class="text-xs font-semibold uppercase tracking-wider text-muted block">Quotation page flow preview</span>
                <div class="flex items-center gap-2 flex-wrap text-xs">
                    @php
                        $seq = 1;
                        $customSecs = $sections->where('is_active', true);
                    @endphp
                    <span class="px-2.5 py-1 rounded-md border border-line bg-surface font-medium text-ink">{{ sprintf('%02d', $seq++) }} Cover</span>
                    <span class="text-muted">→</span>

                    @foreach ($customSecs->where('position', 'after_cover') as $cs)
                        <button type="button" wire:click="selectSection({{ $cs->id }})"
                                @class([
                                    'px-2.5 py-1 rounded-md border-2 font-bold transition flex items-center gap-1 cursor-pointer',
                                    'border-brand-600 bg-brand-100 text-brand-900 shadow-2xs' => $selectedSection && $selectedSection->id === $cs->id,
                                    'border-brand-400 bg-brand-50/70 text-brand-700 hover:bg-brand-100/70' => ! $selectedSection || $selectedSection->id !== $cs->id,
                                ])>
                            <span>{{ sprintf('%02d', $seq++) }}</span>
                            <span>{{ $cs->title }}</span>
                        </button>
                        <span class="text-muted">→</span>
                    @endforeach

                    <span class="px-2.5 py-1 rounded-md border border-line bg-surface font-medium text-ink">{{ sprintf('%02d', $seq++) }} Coverage</span>
                    <span class="text-muted">→</span>

                    @foreach ($customSecs->where('position', 'after_coverage') as $cs)
                        <button type="button" wire:click="selectSection({{ $cs->id }})"
                                @class([
                                    'px-2.5 py-1 rounded-md border-2 font-bold transition flex items-center gap-1 cursor-pointer',
                                    'border-brand-600 bg-brand-100 text-brand-900 shadow-2xs' => $selectedSection && $selectedSection->id === $cs->id,
                                    'border-brand-400 bg-brand-50/70 text-brand-700 hover:bg-brand-100/70' => ! $selectedSection || $selectedSection->id !== $cs->id,
                                ])>
                            <span>{{ sprintf('%02d', $seq++) }}</span>
                            <span>{{ $cs->title }}</span>
                        </button>
                        <span class="text-muted">→</span>
                    @endforeach

                    <span class="px-2.5 py-1 rounded-md border border-line bg-surface font-medium text-ink">{{ sprintf('%02d', $seq++) }} Inclusions</span>
                    <span class="text-muted">→</span>

                    @foreach ($customSecs->where('position', 'after_deliverables') as $cs)
                        <button type="button" wire:click="selectSection({{ $cs->id }})"
                                @class([
                                    'px-2.5 py-1 rounded-md border-2 font-bold transition flex items-center gap-1 cursor-pointer',
                                    'border-brand-600 bg-brand-100 text-brand-900 shadow-2xs' => $selectedSection && $selectedSection->id === $cs->id,
                                    'border-brand-400 bg-brand-50/70 text-brand-700 hover:bg-brand-100/70' => ! $selectedSection || $selectedSection->id !== $cs->id,
                                ])>
                            <span>{{ sprintf('%02d', $seq++) }}</span>
                            <span>{{ $cs->title }}</span>
                        </button>
                        <span class="text-muted">→</span>
                    @endforeach

                    <span class="px-2.5 py-1 rounded-md border border-line bg-surface font-medium text-ink">{{ sprintf('%02d', $seq++) }} Price</span>
                    <span class="text-muted">→</span>

                    @foreach ($customSecs->where('position', 'after_price') as $cs)
                        <button type="button" wire:click="selectSection({{ $cs->id }})"
                                @class([
                                    'px-2.5 py-1 rounded-md border-2 font-bold transition flex items-center gap-1 cursor-pointer',
                                    'border-brand-600 bg-brand-100 text-brand-900 shadow-2xs' => $selectedSection && $selectedSection->id === $cs->id,
                                    'border-brand-400 bg-brand-50/70 text-brand-700 hover:bg-brand-100/70' => ! $selectedSection || $selectedSection->id !== $cs->id,
                                ])>
                            <span>{{ sprintf('%02d', $seq++) }}</span>
                            <span>{{ $cs->title }}</span>
                        </button>
                        <span class="text-muted">→</span>
                    @endforeach

                    <span class="px-2.5 py-1 rounded-md border border-line bg-surface font-medium text-ink">{{ sprintf('%02d', $seq++) }} Terms</span>
                </div>
            </div>
        </div>
    @endif
</div>

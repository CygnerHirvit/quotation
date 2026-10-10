<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-semibold">Price list</h1>
            <p class="mt-1 text-muted">Items you pick from when building a quotation. Rates are only visible to your team.</p>
        </div>
        @if ($editingId === null)
            <button type="button" wire:click="create" class="btn-primary">+ Add {{ strtolower($current->label()) }} item</button>
        @endif
    </div>

    @include('partials.flash')

    <div class="flex flex-wrap gap-2" role="tablist">
        @foreach ($categories as $categoryOption)
            <button
                type="button"
                role="tab"
                aria-selected="{{ $current === $categoryOption ? 'true' : 'false' }}"
                wire:click="$set('category', '{{ $categoryOption->value }}')"
                @class([
                    'rounded-full px-4 py-2 text-sm font-semibold transition',
                    'bg-ink text-white' => $current === $categoryOption,
                    'bg-surface text-muted hover:text-ink border border-line' => $current !== $categoryOption,
                ])
            >
                {{ $categoryOption->label() }}
                <span class="ml-1 opacity-70 tabular-nums">{{ $counts[$categoryOption->value] ?? 0 }}</span>
            </button>
        @endforeach
    </div>

    <p class="text-sm text-muted">{{ $current->description() }}</p>

    @if ($editingId !== null)
        <form wire:submit="save" class="card space-y-5 p-6">
            <h2 class="text-lg font-semibold">{{ $editingId ? 'Edit item' : 'New '.strtolower($current->label()).' item' }}</h2>

            <div class="grid gap-5 sm:grid-cols-6">
                <div class="sm:col-span-4">
                    <label for="item-name" class="label">Name (shown to client)</label>
                    <input id="item-name" type="text" wire:model="name" class="input" placeholder="{{ $current === \App\Enums\PriceCategory::Crew ? 'Candid Photographer' : 'Wedding Full Film' }}">
                    @error('name') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="item-rate" class="label">Rate (₹)</label>
                    <input id="item-rate" type="number" min="0" step="1" wire:model="rate" class="input tabular-nums">
                    @error('rate') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="item-label" class="label">Internal label</label>
                    <input id="item-label" type="text" wire:model="internalLabel" class="input" placeholder="Senior">
                    <p class="mt-1.5 text-xs text-muted">Only your team sees this.</p>
                    @error('internalLabel') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="item-note" class="label">Note</label>
                    <input id="item-note" type="text" wire:model="note" class="input" placeholder="valid for 2 years">
                    @error('note') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="item-delivery" class="label">Delivery</label>
                    <input id="item-delivery" type="text" wire:model="delivery" class="input" placeholder="45 days">
                    @error('delivery') <p class="input-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <label for="item-active" class="flex items-center gap-2 text-sm">
                <input id="item-active" type="checkbox" wire:model="isActive" class="size-4 accent-brand-500">
                Show in “Add from price list”
            </label>

            <div class="flex justify-end gap-3">
                <button type="button" wire:click="closeForm" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">{{ $editingId ? 'Save' : 'Add item' }}</button>
            </div>
        </form>
    @endif

    <div class="card">
        @if ($items->isEmpty())
            <div class="px-6 py-14 text-center">
                <p class="font-medium">No {{ strtolower($current->label()) }} items yet</p>
                <p class="mt-1 text-sm text-muted">Add the items you quote most often, with your usual rate.</p>
            </div>
        @else
            <ul wire:sort="sort" class="divide-y divide-line">
                @foreach ($items as $item)
                    <li wire:key="price-item-{{ $item->id }}" wire:sort:item="{{ $item->id }}" @class(['flex flex-col sm:flex-row sm:items-center gap-3 bg-surface px-4 py-3.5', 'opacity-60' => ! $item->is_active])>
                        <div class="flex items-start gap-2.5 min-w-0 flex-1">
                            <button type="button" wire:sort:handle class="mt-1 sm:mt-0 cursor-grab px-1 text-lg leading-none text-muted shrink-0" aria-label="Drag to reorder">⋮⋮</button>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-baseline justify-between sm:justify-start gap-2 flex-wrap">
                                    <p class="font-medium text-ink">
                                        {{ $item->name }}
                                        @if ($item->internal_label)
                                            <span class="badge ml-1 bg-canvas text-muted">{{ $item->internal_label }}</span>
                                        @endif
                                        @unless ($item->is_active)
                                            <span class="badge ml-1 bg-canvas text-muted">Hidden</span>
                                        @endunless
                                    </p>
                                    <p class="sm:hidden font-semibold tabular-nums text-ink">{{ \App\Support\Inr::format($item->rate) }}</p>
                                </div>
                                @if ($item->note || $item->delivery)
                                    <p class="truncate text-sm text-muted mt-0.5">
                                        {{ collect([$item->note, $item->delivery ? 'Delivery: '.$item->delivery : null])->filter()->join(' · ') }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <p class="hidden sm:block w-28 text-right font-semibold tabular-nums shrink-0">{{ \App\Support\Inr::format($item->rate) }}</p>

                        <div class="flex items-center justify-end gap-1.5 w-full sm:w-auto pt-2 sm:pt-0 border-t border-line/60 sm:border-t-0 pl-7 sm:pl-0" wire:sort:ignore>
                            <button type="button" wire:click="edit({{ $item->id }})" class="btn-secondary !py-1.5 !px-3 text-xs flex-1 sm:flex-none text-center">Edit</button>
                            <button type="button" wire:click="toggleActive({{ $item->id }})" class="btn-secondary !py-1.5 !px-3 text-xs flex-1 sm:flex-none text-center" title="{{ $item->is_active ? 'Hide from quotations' : 'Show in quotations' }}">
                                {{ $item->is_active ? 'Hide' : 'Show' }}
                            </button>
                            <button type="button" wire:click="delete({{ $item->id }})" wire:confirm="Delete {{ $item->name }}?" class="btn-danger !py-1.5 !px-3 text-xs" aria-label="Delete {{ $item->name }}">✕</button>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

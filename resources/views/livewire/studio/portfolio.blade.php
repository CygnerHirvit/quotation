<div class="mx-auto max-w-4xl space-y-6">
    <div>
        <h1 class="text-3xl font-semibold">Portfolio</h1>
        <p class="mt-1 text-muted">Albums and videos shown on every quotation, so clients can see your work.</p>
    </div>

    @include('partials.flash')

    @if ($editingId !== null)
        @php($isVideo = $type === \App\Enums\PortfolioType::Video->value)
        <form wire:submit="save" class="card space-y-5 p-6">
            <h2 class="text-lg font-semibold">{{ $editingId ? 'Edit' : 'Add' }} {{ $isVideo ? 'video' : 'photo album' }}</h2>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="portfolio-title" class="label">Title</label>
                    <input id="portfolio-title" type="text" wire:model="title" class="input" placeholder="{{ $isVideo ? 'Naitik & Bansi · Engagement' : 'Wedding' }}">
                    @error('title') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="portfolio-url" class="label">{{ $isVideo ? 'YouTube or Vimeo link' : 'Gallery link' }}</label>
                    <input id="portfolio-url" type="url" wire:model="url" class="input" placeholder="{{ $isVideo ? 'https://youtu.be/…' : 'https://yourstudio.smugmug.com/…' }}">
                    @error('url') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="portfolio-cover" class="label">Cover image {{ $isVideo ? '(optional, YouTube thumbnails are used automatically)' : '' }}</label>
                    <div class="flex flex-wrap items-center gap-4">
                        @if ($cover?->isPreviewable())
                            <img src="{{ $cover->temporaryUrl() }}" alt="" class="h-16 w-28 rounded-lg border border-line object-cover">
                        @elseif ($editingItem?->coverUrl())
                            <img src="{{ $editingItem->coverUrl() }}" alt="" class="h-16 w-28 rounded-lg border border-line object-cover">
                        @endif
                        <input id="portfolio-cover" type="file" wire:model="cover" accept="image/png,image/jpeg,image/webp" class="min-w-0 text-sm">
                        @if ($editingItem?->cover_path)
                            <button type="button" wire:click="removeCover" class="text-sm font-semibold text-red-700">Remove</button>
                        @endif
                    </div>
                    <div wire:loading wire:target="cover" class="mt-1 text-sm text-muted">Uploading…</div>
                    @error('cover') <p class="input-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <label for="portfolio-active" class="flex items-center gap-2 text-sm">
                <input id="portfolio-active" type="checkbox" wire:model="isActive" class="size-4 accent-brand-500">
                Show on quotations
            </label>

            <div class="flex justify-end gap-3">
                <button type="button" wire:click="closeForm" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">{{ $editingId ? 'Save' : 'Add' }}</button>
            </div>
        </form>
    @endif

    @foreach ($types as $portfolioType)
        @php($items = $itemsByType->get($portfolioType->value, collect()))
        <section class="card" wire:key="type-{{ $portfolioType->value }}">
            <div class="flex items-center justify-between border-b border-line px-6 py-4">
                <h2 class="text-lg font-semibold">{{ $portfolioType->label() }}</h2>
                @if ($editingId === null)
                    <button type="button" wire:click="create('{{ $portfolioType->value }}')" class="btn-secondary">+ Add</button>
                @endif
            </div>

            @if ($items->isEmpty())
                <p class="px-6 py-10 text-center text-sm text-muted">
                    {{ $portfolioType === \App\Enums\PortfolioType::Album ? 'Add links to your best galleries (SmugMug, Pixieset, Google Drive…).' : 'Add your best films from YouTube or Vimeo.' }}
                </p>
            @else
                <ul wire:sort="sort" class="divide-y divide-line">
                    @foreach ($items as $item)
                        <li wire:key="portfolio-{{ $item->id }}" wire:sort:item="{{ $item->id }}" @class(['flex items-center gap-3 bg-surface px-4 py-3', 'opacity-60' => ! $item->is_active])>
                            <button type="button" wire:sort:handle class="cursor-grab px-1 text-lg leading-none text-muted" aria-label="Drag to reorder">⋮⋮</button>

                            @if ($item->coverUrl())
                                <img src="{{ $item->coverUrl() }}" alt="" class="h-12 w-20 shrink-0 rounded-md object-cover">
                            @else
                                <span class="flex h-12 w-20 shrink-0 items-center justify-center rounded-md bg-canvas text-xs text-muted">No cover</span>
                            @endif

                            <div class="min-w-0 flex-1">
                                <p class="font-medium">
                                    {{ $item->title }}
                                    @unless ($item->is_active)
                                        <span class="badge ml-1 bg-canvas text-muted">Hidden</span>
                                    @endunless
                                </p>
                                <a href="{{ $item->url }}" target="_blank" rel="noopener" class="block truncate text-sm text-brand-600 hover:underline">{{ $item->url }}</a>
                            </div>

                            <div class="flex gap-1" wire:sort:ignore>
                                <button type="button" wire:click="edit({{ $item->id }})" class="btn-secondary px-3">Edit</button>
                                <button type="button" wire:click="toggleActive({{ $item->id }})" class="btn-secondary px-3">{{ $item->is_active ? 'Hide' : 'Show' }}</button>
                                <button type="button" wire:click="delete({{ $item->id }})" wire:confirm="Delete {{ $item->title }}?" class="btn-danger px-3" aria-label="Delete {{ $item->title }}">✕</button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endforeach
</div>

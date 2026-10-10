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
                <div class="sm:col-span-2 rounded-xl border border-line bg-canvas/40 p-4 sm:p-5" wire:key="portfolio-cover-card">
                    <input id="portfolio-cover" type="file" wire:model="cover" accept="image/png,image/jpeg,image/webp" class="sr-only">

                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-6">
                        {{-- Cover Banner / Preview Box (16:9 Aspect Ratio) --}}
                        <div class="relative group w-36 sm:w-44 aspect-video shrink-0">
                            <label for="portfolio-cover" @class([
                                'relative flex size-full cursor-pointer items-center justify-center overflow-hidden rounded-xl bg-surface transition hover:border-brand-500 hover:bg-brand-50/30 focus-within:ring-2 focus-within:ring-brand-500',
                                'border border-line shadow-xs' => ($cover?->isPreviewable() || (! $removeCover && $editingItem?->coverUrl())),
                                'border-2 border-dashed border-line' => (! $cover?->isPreviewable() && ($removeCover || ! $editingItem?->coverUrl())),
                            ])>
                                @if ($cover?->isPreviewable())
                                    <img src="{{ $cover->temporaryUrl() }}" alt="New cover preview" class="size-full object-cover">
                                    <div class="absolute inset-0 flex flex-col items-center justify-center bg-black/50 text-white opacity-0 transition group-hover:opacity-100">
                                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                                        </svg>
                                        <span class="text-[10px] font-semibold mt-0.5">Change</span>
                                    </div>
                                @elseif (! $removeCover && $editingItem?->coverUrl())
                                    <img src="{{ $editingItem->coverUrl() }}" alt="{{ $title }} cover" class="size-full object-cover">
                                    <div class="absolute inset-0 flex flex-col items-center justify-center bg-black/50 text-white opacity-0 transition group-hover:opacity-100">
                                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                                        </svg>
                                        <span class="text-[10px] font-semibold mt-0.5">Change</span>
                                    </div>
                                @elseif ($removeCover)
                                    <div class="flex flex-col items-center justify-center text-red-500/80 p-2 text-center">
                                        <svg class="size-6 stroke-[1.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                        <span class="text-[10px] font-semibold mt-1">Removed</span>
                                    </div>
                                @else
                                    <div class="flex flex-col items-center justify-center text-muted group-hover:text-brand-600 transition p-2 text-center">
                                        <svg class="size-6 stroke-[1.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                        </svg>
                                        <span class="text-[10px] font-semibold mt-1">Add cover</span>
                                    </div>
                                @endif

                                {{-- Loading spinner overlay --}}
                                <div wire:loading.flex wire:target="cover" class="absolute inset-0 flex flex-col items-center justify-center bg-surface/90 backdrop-blur-xs rounded-xl z-10">
                                    <svg class="size-5 animate-spin text-brand-600" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </label>
                        </div>

                        {{-- Details & Actions --}}
                        <div class="flex-1 min-w-0 space-y-1.5">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-sm font-semibold text-ink">Cover image {{ $isVideo ? '(optional)' : '' }}</h3>
                            </div>
                            <p class="text-xs text-muted">
                                {{ $isVideo ? 'Optional, YouTube thumbnails are used automatically if empty.' : 'PNG, JPG or WEBP up to 3 MB. 16:9 landscape format recommended.' }}
                            </p>

                            <div class="flex flex-wrap items-center gap-2 pt-1">
                                @if ($cover?->isPreviewable())
                                    <label for="portfolio-cover" class="btn-secondary cursor-pointer !py-1 !px-3 text-xs">
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 16.5v-2.25m-14.25-5.25L12 3m0 0 5.25 5.25M12 3v12" />
                                        </svg>
                                        <span>Change cover</span>
                                    </label>
                                    <button type="button" wire:click="clearSelectedCover" class="btn-secondary !py-1 !px-3 text-xs text-red-600 hover:text-red-700">
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                        <span>Clear selection</span>
                                    </button>
                                @elseif ($removeCover)
                                    <button type="button" wire:click="undoCoverRemoval" class="btn-secondary !py-1 !px-3 text-xs">
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                                        </svg>
                                        <span>Undo removal</span>
                                    </button>
                                    <label for="portfolio-cover" class="btn-secondary cursor-pointer !py-1 !px-3 text-xs">
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 16.5v-2.25m-14.25-5.25L12 3m0 0 5.25 5.25M12 3v12" />
                                        </svg>
                                        <span>Upload new</span>
                                    </label>
                                @elseif ($editingItem?->coverUrl())
                                    <label for="portfolio-cover" class="btn-secondary cursor-pointer !py-1 !px-3 text-xs">
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 16.5v-2.25m-14.25-5.25L12 3m0 0 5.25 5.25M12 3v12" />
                                        </svg>
                                        <span>Change cover</span>
                                    </label>
                                    <button type="button" wire:click="markCoverForRemoval" class="btn-danger !py-1 !px-3 text-xs">
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                        <span>Remove</span>
                                    </button>
                                @else
                                    <label for="portfolio-cover" class="btn-secondary cursor-pointer !py-1 !px-3 text-xs">
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 16.5v-2.25m-14.25-5.25L12 3m0 0 5.25 5.25M12 3v12" />
                                        </svg>
                                        <span>Upload cover</span>
                                    </label>
                                @endif

                                <div wire:loading wire:target="cover" class="inline-flex items-center gap-1.5 text-xs font-medium text-brand-600">
                                    <svg class="size-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>Uploading preview…</span>
                                </div>
                            </div>

                            @error('cover')
                                <p class="input-error text-xs">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
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
                        <li wire:key="portfolio-{{ $item->id }}" wire:sort:item="{{ $item->id }}" @class(['flex flex-col sm:flex-row sm:items-center gap-3 bg-surface px-4 py-3.5', 'opacity-60' => ! $item->is_active])>
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <button type="button" wire:sort:handle class="cursor-grab px-1 text-lg leading-none text-muted shrink-0" aria-label="Drag to reorder">⋮⋮</button>

                                @if ($item->coverUrl())
                                    <img src="{{ $item->coverUrl() }}" alt="" class="h-12 w-20 shrink-0 rounded-md object-cover">
                                @else
                                    <span class="flex h-12 w-20 shrink-0 items-center justify-center rounded-md bg-canvas text-xs text-muted">No cover</span>
                                @endif

                                <div class="min-w-0 flex-1">
                                    <p class="font-medium text-ink truncate">
                                        {{ $item->title }}
                                        @unless ($item->is_active)
                                            <span class="badge ml-1 bg-canvas text-muted">Hidden</span>
                                        @endunless
                                    </p>
                                    <a href="{{ $item->url }}" target="_blank" rel="noopener" class="block truncate text-xs sm:text-sm text-brand-600 hover:underline">{{ $item->url }}</a>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-1.5 w-full sm:w-auto pt-2 sm:pt-0 border-t border-line/60 sm:border-t-0 pl-7 sm:pl-0" wire:sort:ignore>
                                <button type="button" wire:click="edit({{ $item->id }})" class="btn-secondary !py-1.5 !px-3 text-xs flex-1 sm:flex-none text-center">Edit</button>
                                <button type="button" wire:click="toggleActive({{ $item->id }})" class="btn-secondary !py-1.5 !px-3 text-xs flex-1 sm:flex-none text-center">{{ $item->is_active ? 'Hide' : 'Show' }}</button>
                                <button type="button" wire:click="delete({{ $item->id }})" wire:confirm="Delete {{ $item->title }}?" class="btn-danger !py-1.5 !px-3 text-xs" aria-label="Delete {{ $item->title }}">✕</button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endforeach
</div>

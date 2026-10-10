<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-semibold">Quotations</h1>
            <p class="mt-1 text-muted">Showing {{ $quotations->total() }} of {{ $total }}</p>
        </div>
        <button type="button" wire:click="createQuotation" class="btn-primary" wire:loading.attr="disabled" wire:target="createQuotation">
            <span wire:loading.remove wire:target="createQuotation">+ New quotation</span>
            <span wire:loading wire:target="createQuotation">Creating…</span>
        </button>
    </div>

    @include('partials.flash')

    <div class="flex flex-wrap items-center gap-2">
        <input id="search" type="search" wire:model.live.debounce.300ms="search" placeholder="Search name, number or city" aria-label="Search quotations" class="input w-full sm:w-64 sm:max-w-xs">

        <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2 w-full sm:w-auto">
            <select id="type" wire:model.live="type" aria-label="Function type" class="input w-full sm:w-auto">
                <option value="">All functions</option>
                @foreach ($types as $typeOption)
                    <option value="{{ $typeOption->value }}">{{ $typeOption->label() }}</option>
                @endforeach
            </select>

            <select id="year" wire:model.live="year" aria-label="Year" class="input w-full sm:w-auto">
                <option value="">All years</option>
                @foreach ($years as $yearOption)
                    <option value="{{ $yearOption }}">{{ $yearOption }}</option>
                @endforeach
            </select>

            <select id="month" wire:model.live="month" aria-label="Month" class="input w-full sm:w-auto">
                <option value="">All months</option>
                @foreach (range(1, 12) as $monthNumber)
                    <option value="{{ $monthNumber }}">{{ \Illuminate\Support\Carbon::create(null, $monthNumber, 1)->format('F') }}</option>
                @endforeach
            </select>

            <select id="status" wire:model.live="status" aria-label="Status" class="input w-full sm:w-auto">
                <option value="">All statuses</option>
                @foreach ($statuses as $statusOption)
                    <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                @endforeach
            </select>

            <select id="creator" wire:model.live="creator" aria-label="Created by" class="input w-full sm:w-auto col-span-2 sm:col-span-1">
                <option value="">Everyone</option>
                @foreach ($creators as $creatorOption)
                    <option value="{{ $creatorOption->id }}">{{ $creatorOption->name }}</option>
                @endforeach
            </select>
        </div>

        @if ($hasFilters)
            <button type="button" wire:click="clearFilters" class="text-sm font-semibold text-muted hover:text-ink py-1.5 px-2">Clear filters</button>
        @endif
    </div>

    @if ($quotations->isEmpty())
        <div class="card px-6 py-16 text-center">
            @if ($total === 0)
                <p class="text-lg font-medium">No quotations yet</p>
                <p class="mt-1 text-sm text-muted">Create your first quotation. It gets the next number automatically.</p>
                <button type="button" wire:click="createQuotation" class="btn-primary mt-5">+ New quotation</button>
            @else
                <p class="font-medium">No quotations match these filters</p>
                <button type="button" wire:click="clearFilters" class="btn-secondary mt-4">Clear filters</button>
            @endif
        </div>
    @else
        {{-- Mobile Cards (<640px) --}}
        <div class="space-y-3 sm:hidden">
            @foreach ($quotations as $quotation)
                <div wire:key="quotation-card-{{ $quotation->id }}" class="card p-4 space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('studio.quotations.edit', $quotation) }}" wire:navigate class="block font-semibold text-base text-ink hover:text-brand-600 truncate">
                                {{ $quotation->displayName() }}
                            </a>
                            <div class="mt-1 flex flex-wrap items-center gap-1.5 text-xs text-muted">
                                <span class="font-mono font-semibold text-brand-700">{{ $quotation->number }}</span>
                                <span class="badge bg-brand-50 text-brand-700 uppercase">{{ $quotation->function_type->label() }}</span>
                            </div>
                        </div>
                        <span class="badge {{ $quotation->status->badgeClasses() }} shrink-0">{{ $quotation->status->label() }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs text-muted pt-2 border-t border-line/60">
                        <div>
                            <span class="block font-medium text-ink">{{ $quotation->datesLabel() }}</span>
                            <span>{{ $quotation->city ?: 'City TBD' }}</span>
                        </div>
                        <div class="text-right">
                            <span class="block text-base font-bold text-ink tabular-nums">{{ \App\Support\Inr::format($quotation->cached_grand_total) }}</span>
                            <span class="truncate block">
                                @if ($quotation->cached_crew_count)
                                    {{ $quotation->cached_crew_count }} crew
                                @endif
                                @if ($quotation->creator)
                                    · {{ $quotation->creator->name }}
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2 border-t border-line/60">
                        <a href="{{ route('studio.quotations.edit', $quotation) }}" wire:navigate class="btn-secondary !py-1.5 !px-3 text-xs flex-1 text-center justify-center">Open</a>
                        <button type="button" wire:click="duplicate({{ $quotation->id }})" class="btn-secondary !py-1.5 !px-3 text-xs" title="Make a copy with a new number">Copy</button>
                        <button
                            type="button"
                            wire:click="delete({{ $quotation->id }})"
                            wire:confirm="Delete {{ $quotation->number }} ({{ $quotation->displayName() }})? Its share link will stop working."
                            class="btn-danger !py-1.5 !px-3 text-xs"
                            aria-label="Delete {{ $quotation->number }}"
                        >✕</button>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Desktop & Tablet Table (>=640px) --}}
        <div class="card overflow-x-auto hidden sm:block">
            <table class="w-full min-w-[760px] text-sm">
                <thead class="border-b border-line text-left text-xs tracking-wide text-muted uppercase">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Client / event</th>
                        <th class="px-5 py-3 font-semibold">Event date</th>
                        <th class="px-5 py-3 font-semibold">City</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 text-right font-semibold">Grand total</th>
                        <th class="px-5 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach ($quotations as $quotation)
                        <tr wire:key="quotation-row-{{ $quotation->id }}" class="hover:bg-canvas/60">
                            <td class="px-5 py-4">
                                <a href="{{ route('studio.quotations.edit', $quotation) }}" wire:navigate class="block">
                                    <span class="block text-base font-semibold">{{ $quotation->displayName() }}</span>
                                    <span class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-muted">
                                        <span class="font-mono font-semibold text-brand-700">{{ $quotation->number }}</span>
                                        <span class="badge bg-brand-50 text-brand-700 uppercase">{{ $quotation->function_type->label() }}</span>
                                        @if ($quotation->cached_crew_count)
                                            <span>{{ $quotation->cached_crew_count }} crew</span>
                                        @endif
                                        @if ($quotation->creator)
                                            <span>· {{ $quotation->creator->name }}</span>
                                        @endif
                                    </span>
                                </a>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">{{ $quotation->datesLabel() }}</td>
                            <td class="px-5 py-4">{{ $quotation->city ?: '—' }}</td>
                            <td class="px-5 py-4">
                                <span class="badge {{ $quotation->status->badgeClasses() }}">{{ $quotation->status->label() }}</span>
                            </td>
                            <td class="px-5 py-4 text-right text-base font-semibold tabular-nums">{{ \App\Support\Inr::format($quotation->cached_grand_total) }}</td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('studio.quotations.edit', $quotation) }}" wire:navigate class="btn-secondary px-3">Open</a>
                                    <button type="button" wire:click="duplicate({{ $quotation->id }})" class="btn-secondary px-3" title="Make a copy with a new number">Copy</button>
                                    <button
                                        type="button"
                                        wire:click="delete({{ $quotation->id }})"
                                        wire:confirm="Delete {{ $quotation->number }} ({{ $quotation->displayName() }})? Its share link will stop working."
                                        class="btn-danger px-3"
                                        aria-label="Delete {{ $quotation->number }}"
                                    >✕</button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $quotations->links() }}
    @endif
</div>

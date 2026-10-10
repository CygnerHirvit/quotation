@php
    use App\Support\Inr;

    $title = collect([$bride, $groom])->filter()->join(' & ') ?: ($clientName ?: 'Untitled quotation');
@endphp

<div class="space-y-6">
    {{-- Header --}}
    <div class="space-y-3">
        <a href="{{ route('studio.dashboard') }}" wire:navigate class="text-sm text-muted hover:text-ink">← All quotations</a>

        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div class="min-w-0">
                <h1 class="text-2xl sm:text-3xl font-semibold text-balance">{{ $title }}</h1>
                <p class="mt-1 text-sm text-muted">
                    <span class="font-mono font-semibold text-brand-700">{{ $quotation->number }}</span>
                    · {{ $datesTbd ? 'Dates TBD' : ($startDate ? \Illuminate\Support\Carbon::parse($startDate)->format('j M Y') : 'No date') }}
                    · {{ $city ?: 'City TBD' }}
                </p>
            </div>

            {{-- Action Controls & Meta Bar --}}
            <div x-data="{ copied: false }" class="w-full lg:w-auto space-y-2.5">
                {{-- Status & Save Indicator Row --}}
                <div class="flex items-center justify-between lg:justify-end gap-3 text-xs sm:text-sm">
                    <span class="flex items-center gap-1.5 text-muted" aria-live="polite">
                        <span wire:loading.remove class="size-2 rounded-full bg-emerald-500 shrink-0"></span>
                        <span wire:loading.remove class="text-xs font-medium">All changes saved</span>
                        <span wire:loading class="size-2 animate-pulse rounded-full bg-amber-500 shrink-0"></span>
                        <span wire:loading class="text-xs font-medium">Saving…</span>
                    </span>

                    <div class="flex items-center gap-2">
                        <label for="status" class="sr-only">Status</label>
                        <select
                            id="status"
                            wire:model.live="status"
                            class="input w-auto !py-1.5 !px-3 text-xs sm:text-sm font-semibold rounded-lg bg-surface border-line cursor-pointer"
                            @disabled($isLocked)
                        >
                            @unless (in_array($quotation->status, $statuses, true))
                                <option value="{{ $quotation->status->value }}" disabled>{{ $quotation->status->label() }}</option>
                            @endunless
                            @foreach ($statuses as $statusOption)
                                <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Action Buttons Row: 2-column grid on mobile (<640px), flex row on tablet/desktop (>=640px) --}}
                <div class="grid grid-cols-2 sm:flex sm:flex-wrap sm:items-center sm:justify-end gap-2 text-xs sm:text-sm">
                    {{-- Copy Share Link --}}
                    <button
                        type="button"
                        class="btn-secondary !py-2 !px-3 text-xs sm:text-sm font-medium justify-center"
                        x-on:click="
                            navigator.clipboard.writeText(@js($quotation->publicUrl()))
                                .then(() => { copied = true; setTimeout(() => copied = false, 2000) })
                                .catch(() => window.prompt('Copy this link', @js($quotation->publicUrl())));
                            $wire.markShared()
                        "
                    >
                        <svg class="size-4 shrink-0 text-muted" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                        </svg>
                        <span x-show="! copied">Copy link</span>
                        <span x-show="copied" x-cloak class="text-brand-600 font-semibold">Copied ✓</span>
                    </button>

                    {{-- View Copy --}}
                    <a
                        href="{{ $quotation->publicUrl() }}"
                        target="_blank"
                        rel="noopener"
                        class="btn-secondary !py-2 !px-3 text-xs sm:text-sm font-medium justify-center"
                    >
                        <svg class="size-4 shrink-0 text-muted" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                        </svg>
                        <span>View copy</span>
                    </a>

                    {{-- Export PDF --}}
                    <a
                        href="{{ $quotation->publicUrl() }}?print=1"
                        target="_blank"
                        rel="noopener"
                        class="btn-secondary !py-2 !px-3 text-xs sm:text-sm font-medium justify-center"
                        title="Opens the client copy and the print window. Choose “Save as PDF”."
                    >
                        <svg class="size-4 shrink-0 text-muted" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        <span>Export PDF</span>
                    </a>

                    {{-- WhatsApp --}}
                    <a
                        href="{{ $quotation->whatsappUrl() }}"
                        target="_blank"
                        rel="noopener"
                        x-on:click="$wire.markShared()"
                        class="btn bg-[#25D366] text-white hover:bg-[#1ebe5a] !py-2 !px-3 text-xs sm:text-sm font-semibold justify-center shadow-xs"
                        title="{{ $clientPhone ? 'Send to '.$clientPhone : 'Add the client WhatsApp number to send directly' }}"
                    >
                        <svg class="size-4 shrink-0 fill-current" viewBox="0 0 24 24">
                            <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91s-4.45-9.92-9.91-9.92zm5.82 14.03c-.24.67-1.39 1.28-1.92 1.34-.51.05-1.17.07-3.79-.98-3.34-1.35-5.5-4.74-5.67-4.96-.16-.22-1.36-1.81-1.36-3.46 0-1.64.86-2.45 1.17-2.78.3-.33.67-.41.89-.41.22 0 .45 0 .64.01.21.01.49-.08.77.58.29.69.99 2.42 1.08 2.6.09.18.15.4.03.63-.12.24-.18.38-.36.59-.18.21-.38.47-.54.63-.18.18-.37.38-.16.74.21.36.94 1.55 2.01 2.51 1.38 1.23 2.54 1.61 2.9 1.79.36.18.57.15.78-.09.21-.24.9-1.05 1.14-1.41.24-.36.48-.3.81-.18.33.12 2.09.99 2.45 1.17.36.18.6.27.69.42.09.15.09.87-.15 1.54z"/>
                        </svg>
                        <span>WhatsApp</span>
                    </a>

                    {{-- Duplicate --}}
                    <button
                        type="button"
                        wire:click="duplicate"
                        class="btn-secondary !py-2 !px-3 text-xs sm:text-sm font-medium justify-center"
                        title="Make a copy with a new number"
                    >
                        <svg class="size-4 shrink-0 text-muted" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                        </svg>
                        <span>Duplicate</span>
                    </button>

                    {{-- Creator Selector --}}
                    <div class="relative col-span-1 sm:w-auto">
                        <label for="created-by" class="sr-only">Created by</label>
                        <select
                            id="created-by"
                            wire:model.live="createdBy"
                            class="input w-full sm:w-auto !py-2 !px-3 text-xs sm:text-sm font-medium cursor-pointer"
                            title="Created by"
                            @disabled($isLocked)
                        >
                            @foreach ($team as $member)
                                <option value="{{ $member->id }}">By {{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('partials.flash')

    @if ($isLocked)
        <div class="flex flex-wrap items-center gap-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
            @if ($quotation->signed_name)
                <div class="flex h-16 min-w-32 items-center justify-center rounded-lg border border-emerald-200 bg-white px-3">
                    @if ($signatureImage)
                        <img src="{{ $signatureImage }}" alt="Client signature" class="max-h-14">
                    @else
                        <span class="text-2xl text-emerald-900" style="font-family: 'Dancing Script', 'Brush Script MT', cursive">{{ $quotation->signed_name }}</span>
                    @endif
                </div>
            @endif
            <div class="min-w-0 flex-1 text-sm text-emerald-900">
                @if ($quotation->signed_name)
                    <p class="font-semibold">Accepted by {{ $quotation->signed_name }}{{ $quotation->accepted_at ? ' on '.$quotation->accepted_at->format('j M Y, g:i A') : '' }}</p>
                    <p>{{ $quotation->signature_method === 'draw' ? 'Drawn signature' : 'Typed name' }} · IP {{ $quotation->signer_ip }}. This quotation is locked so the signed version stays as it was.</p>
                @else
                    <p class="font-semibold">Marked as accepted by your team</p>
                    <p>The client has not signed online. This quotation is locked so the accepted version stays as it was.</p>
                @endif
            </div>
            <button
                type="button"
                wire:click="unlockForRevision"
                wire:confirm="Unlock this quotation? The client's signature will be removed and they will need to accept again."
                class="btn-secondary"
            >Unlock &amp; revise</button>
        </div>
    @endif

    <fieldset @disabled($isLocked) class="m-0 min-w-0 border-0 p-0">
    <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
        <div class="min-w-0 space-y-6">
            {{-- Client & quotation --}}
            <section class="card space-y-5 p-6">
                <h2 class="text-lg font-semibold">{{ $isCorporate ? 'Client & quotation' : 'Couple & quotation' }}</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    @if ($isCorporate)
                        <div class="sm:col-span-2">
                            <label for="client-name" class="label">Company / client name</label>
                            <input id="client-name" type="text" wire:model.live.debounce.500ms="clientName" class="input">
                            @error('clientName') <p class="input-error">{{ $message }}</p> @enderror
                        </div>
                    @else
                        <div>
                            <label for="bride" class="label">Bride</label>
                            <input id="bride" type="text" wire:model.live.debounce.500ms="bride" class="input" placeholder="Bride's name">
                            @error('bride') <p class="input-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="groom" class="label">Groom</label>
                            <input id="groom" type="text" wire:model.live.debounce.500ms="groom" class="input" placeholder="Groom's name">
                            @error('groom') <p class="input-error">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div>
                        <label for="venue" class="label">Venue</label>
                        <input id="venue" type="text" wire:model.live.debounce.500ms="venue" class="input">
                        @error('venue') <p class="input-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="city" class="label">City</label>
                        <input id="city" type="text" wire:model.live.debounce.500ms="city" class="input">
                        @error('city') <p class="input-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="client-phone" class="label">Client WhatsApp number</label>
                        <input id="client-phone" type="tel" wire:model.live.debounce.500ms="clientPhone" class="input" placeholder="+91 98765 43210">
                        @error('clientPhone') <p class="input-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <p class="label mb-0">Event dates</p>
                        <label for="dates-tbd" class="flex items-center gap-2 rounded-full border border-line px-3 py-1 text-sm">
                            <input id="dates-tbd" type="checkbox" wire:model.live="datesTbd" class="size-4 accent-brand-500">
                            Dates TBD (to be decided)
                        </label>
                    </div>

                    @if ($datesTbd)
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-dashed border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                            <span><strong>Dates TBD:</strong> the client has not fixed the dates yet.</span>
                            <button type="button" wire:click="$set('datesTbd', false)" class="font-semibold underline">Fill dates now</button>
                        </div>
                    @else
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="start-date" class="label">From</label>
                                <input id="start-date" type="date" wire:model.live="startDate" class="input">
                                @error('startDate') <p class="input-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="end-date" class="label">To (optional)</label>
                                <input id="end-date" type="date" wire:model.live="endDate" class="input">
                                @error('endDate') <p class="input-error">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @endif
                </div>

                <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label for="function-type" class="label">Function type</label>
                        <select id="function-type" wire:model.live="functionType" class="input">
                            @foreach ($functionTypes as $typeOption)
                                <option value="{{ $typeOption->value }}">{{ $typeOption->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    @unless ($isCorporate)
                        <div>
                            <label for="function-side" class="label">Function side</label>
                            <select id="function-side" wire:model.live="functionSide" class="input">
                                <option value="">—</option>
                                @foreach ($functionSides as $sideOption)
                                    <option value="{{ $sideOption->value }}">{{ $sideOption->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endunless
                    <div>
                        <p class="label">Quotation no.</p>
                        <p class="rounded-lg bg-canvas px-3 py-2.5 font-mono">{{ $quotation->number }}</p>
                    </div>
                    <div>
                        <label for="valid-until" class="label">Valid until</label>
                        <input id="valid-until" type="date" wire:model.live="validUntil" class="input">
                        @error('validUntil') <p class="input-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            {{-- Events / functions --}}
            <section class="card">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-6 py-4">
                    <div>
                        <h2 class="text-lg font-semibold">Events / functions</h2>
                        <p class="text-sm text-muted">Add each function and the team for it. The client sees the team under each function.</p>
                    </div>
                    <button type="button" wire:click="addEvent" class="btn-secondary">+ Add event</button>
                </div>

                @if (empty($events))
                    <div class="space-y-3 px-6 py-8 text-center">
                        <p class="text-sm text-muted">No functions yet. Quick add:</p>
                        <div class="flex flex-wrap justify-center gap-2">
                            @foreach ($eventSuggestions as $suggestion)
                                <button type="button" wire:click="addEvent('{{ $suggestion }}')" class="rounded-full border border-line px-3 py-1 text-sm hover:border-brand-500 hover:text-brand-700">+ {{ $suggestion }}</button>
                            @endforeach
                        </div>
                    </div>
                @else
                    <ol wire:sort="sortEvents" class="divide-y divide-line">
                        @foreach ($events as $eventIndex => $event)
                            @php($eventCost = collect($event['crew'])->sum(fn ($crew) => (int) $crew['qty'] * (int) $crew['rate']))
                            <li wire:key="event-{{ $event['id'] }}" wire:sort:item="{{ $event['id'] }}" class="space-y-4 bg-surface px-6 py-5">
                                <div class="flex items-start gap-3">
                                    <button type="button" wire:sort:handle class="mt-2 cursor-grab text-lg leading-none text-muted" aria-label="Drag to reorder">⋮⋮</button>
                                    <span class="mt-2 flex size-6 shrink-0 items-center justify-center rounded-full bg-brand-50 text-xs font-bold text-brand-700">{{ $eventIndex + 1 }}</span>

                                    <div class="grid min-w-0 flex-1 gap-3 sm:grid-cols-3" wire:sort:ignore>
                                        <div>
                                            <label for="event-{{ $event['id'] }}-name" class="sr-only">Function name</label>
                                            <input id="event-{{ $event['id'] }}-name" type="text" wire:model.live.debounce.500ms="events.{{ $eventIndex }}.name" class="input font-semibold" placeholder="Function name">
                                            @error("events.$eventIndex.name") <p class="input-error">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label for="event-{{ $event['id'] }}-date" class="sr-only">Date</label>
                                            <input id="event-{{ $event['id'] }}-date" type="date" wire:model.live="events.{{ $eventIndex }}.date" class="input" title="Leave empty if the date is not decided">
                                        </div>
                                        <div>
                                            <label for="event-{{ $event['id'] }}-venue" class="sr-only">Venue</label>
                                            <input id="event-{{ $event['id'] }}-venue" type="text" wire:model.live.debounce.500ms="events.{{ $eventIndex }}.venue" class="input" placeholder="Venue / city">
                                        </div>
                                    </div>

                                    <button type="button" wire:click="removeEvent({{ $event['id'] }})" wire:confirm="Remove {{ $event['name'] }} and its crew?" class="btn-danger mt-0.5 px-3" aria-label="Remove {{ $event['name'] }}" wire:sort:ignore>✕</button>
                                </div>

                                <div class="space-y-2 sm:pl-14" wire:sort:ignore>
                                    @foreach ($event['crew'] as $crewIndex => $crew)
                                        <div wire:key="crew-{{ $crew['id'] }}" class="rounded-xl border border-line/60 bg-canvas/30 p-2 sm:border-0 sm:bg-transparent sm:p-0 flex flex-col sm:flex-row sm:items-center gap-2">
                                            <div class="w-full sm:flex-1 sm:min-w-0">
                                                <label for="crew-{{ $crew['id'] }}-name" class="sr-only">Crew</label>
                                                <input id="crew-{{ $crew['id'] }}-name" type="text" wire:model.live.debounce.500ms="events.{{ $eventIndex }}.crew.{{ $crewIndex }}.name" class="input py-2 text-sm sm:text-base font-medium">
                                            </div>

                                            <div class="flex items-center justify-between sm:justify-start gap-2 w-full sm:w-auto">
                                                <div class="flex items-center rounded-lg border border-line bg-surface">
                                                    <button type="button" wire:click="changeCrewQty({{ $crew['id'] }}, -1)" class="px-2.5 py-1.5 sm:py-2 text-muted hover:text-ink text-sm sm:text-base" aria-label="Less">−</button>
                                                    <span class="w-6 text-center tabular-nums text-sm font-semibold">{{ $crew['qty'] }}</span>
                                                    <button type="button" wire:click="changeCrewQty({{ $crew['id'] }}, 1)" class="px-2.5 py-1.5 sm:py-2 text-muted hover:text-ink text-sm sm:text-base" aria-label="More">+</button>
                                                </div>

                                                <label for="crew-{{ $crew['id'] }}-rate" class="sr-only">Rate</label>
                                                <div class="relative w-24 sm:w-28">
                                                    <span class="pointer-events-none absolute top-1/2 left-2.5 -translate-y-1/2 text-xs sm:text-sm text-muted">₹</span>
                                                    <input id="crew-{{ $crew['id'] }}-rate" type="number" min="0" wire:model.live.debounce.500ms="events.{{ $eventIndex }}.crew.{{ $crewIndex }}.rate" class="input py-1.5 sm:py-2 pl-6 sm:pl-7 tabular-nums text-sm sm:text-base">
                                                </div>

                                                <span class="min-w-20 text-right text-sm font-semibold tabular-nums">{{ Inr::format((int) $crew['qty'] * (int) $crew['rate']) }}</span>

                                                <button type="button" wire:click="removeCrew({{ $crew['id'] }})" class="p-1.5 text-muted hover:text-red-700 shrink-0" aria-label="Remove {{ $crew['name'] }}">✕</button>
                                            </div>
                                        </div>
                                        @error("events.$eventIndex.crew.$crewIndex.*") <p class="input-error">{{ $message }}</p> @enderror
                                    @endforeach

                                    <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
                                        <select
                                            aria-label="Add crew to {{ $event['name'] }}"
                                            x-on:change="$wire.addCrew({{ $event['id'] }}, $event.target.value); $event.target.value = ''"
                                            class="input w-auto py-2 text-sm"
                                        >
                                            <option value="">+ Add crew…</option>
                                            @foreach ($crewOptions as $option)
                                                <option value="{{ $option->id }}">{{ $option->name }}{{ $option->internal_label ? ' ('.$option->internal_label.')' : '' }} · {{ Inr::format($option->rate) }}</option>
                                            @endforeach
                                            <option value="custom">Custom crew…</option>
                                        </select>
                                        @if ($event['crew'])
                                            <span class="text-sm text-muted">
                                                {{ collect($event['crew'])->sum('qty') }} crew · <span class="font-semibold text-ink">{{ Inr::format($eventCost) }}</span>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ol>

                    <div class="flex flex-wrap gap-2 border-t border-line px-6 py-4">
                        @foreach ($eventSuggestions as $suggestion)
                            <button type="button" wire:click="addEvent('{{ $suggestion }}')" class="rounded-full border border-line px-3 py-1 text-xs text-muted hover:border-brand-500 hover:text-brand-700">+ {{ $suggestion }}</button>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- Video and photo deliverables --}}
            @foreach ($sections as $section)
                @php($rows = $items[$section->value])
                <section class="card" wire:key="section-{{ $section->value }}">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-6 py-4">
                        <div>
                            <h2 class="text-lg font-semibold">{{ $section->label() }}</h2>
                            <p class="text-sm text-muted">Quantity × rate is calculated automatically.</p>
                        </div>
                        <select
                            aria-label="Add {{ strtolower($section->label()) }} item"
                            x-on:change="$wire.addItem('{{ $section->value }}', $event.target.value); $event.target.value = ''"
                            class="input w-auto py-2 text-sm"
                        >
                            <option value="">+ Add from price list…</option>
                            @foreach ($itemOptions[$section->value] as $option)
                                <option value="{{ $option->id }}">{{ $option->name }} · {{ Inr::format($option->rate) }}</option>
                            @endforeach
                            <option value="custom">+ Custom item</option>
                        </select>
                    </div>

                    @if (empty($rows))
                        <p class="px-6 py-8 text-center text-sm text-muted">No {{ strtolower($section->label()) }} items yet.</p>
                    @else
                        <ul wire:sort="sortItems" class="divide-y divide-line">
                            @foreach ($rows as $itemIndex => $item)
                                <li wire:key="item-{{ $item['id'] }}" wire:sort:item="{{ $item['id'] }}" @class(['flex flex-col sm:flex-row items-start gap-3 bg-surface px-4 py-4 sm:px-6', 'opacity-60' => ! $item['included']])>
                                    <div class="flex items-start gap-2.5 w-full sm:flex-1 sm:min-w-0">
                                        <button type="button" wire:sort:handle class="mt-2.5 cursor-grab text-lg leading-none text-muted shrink-0" aria-label="Drag to reorder">⋮⋮</button>

                                        <div class="min-w-0 flex-1 space-y-2" wire:sort:ignore>
                                            <label for="item-{{ $item['id'] }}-name" class="sr-only">Item name</label>
                                            <input id="item-{{ $item['id'] }}-name" type="text" wire:model.live.debounce.500ms="items.{{ $section->value }}.{{ $itemIndex }}.name" class="input py-2 font-medium">
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                <input aria-label="Note" type="text" wire:model.live.debounce.500ms="items.{{ $section->value }}.{{ $itemIndex }}.note" class="input py-1.5 text-sm" placeholder="Note (e.g. valid for 2 years)">
                                                <input aria-label="Delivery" type="text" wire:model.live.debounce.500ms="items.{{ $section->value }}.{{ $itemIndex }}.delivery" class="input py-1.5 text-sm" placeholder="Delivery (e.g. 45 days)">
                                            </div>
                                            @error("items.{$section->value}.$itemIndex.*") <p class="input-error">{{ $message }}</p> @enderror
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap items-center justify-between sm:justify-start gap-2 w-full sm:w-auto pt-2 sm:pt-0 border-t border-line/60 sm:border-t-0 pl-7 sm:pl-0" wire:sort:ignore>
                                        <div class="flex items-center gap-2">
                                            <div class="relative w-24 sm:w-28">
                                                <span class="pointer-events-none absolute top-1/2 left-2.5 -translate-y-1/2 text-xs sm:text-sm text-muted">₹</span>
                                                <input aria-label="Rate" type="number" min="0" wire:model.live.debounce.500ms="items.{{ $section->value }}.{{ $itemIndex }}.rate" class="input py-1.5 sm:py-2 pl-6 sm:pl-7 tabular-nums text-sm sm:text-base">
                                            </div>

                                            <div class="flex items-center rounded-lg border border-line bg-surface">
                                                <button type="button" wire:click="changeItemQty({{ $item['id'] }}, -1)" class="px-2.5 py-1.5 sm:py-2 text-muted hover:text-ink text-sm sm:text-base" aria-label="Less">−</button>
                                                <span class="w-6 text-center tabular-nums text-sm font-semibold">{{ $item['qty'] }}</span>
                                                <button type="button" wire:click="changeItemQty({{ $item['id'] }}, 1)" class="px-2.5 py-1.5 sm:py-2 text-muted hover:text-ink text-sm sm:text-base" aria-label="More">+</button>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <span @class(['w-20 sm:w-24 text-right font-semibold tabular-nums text-sm sm:text-base', 'line-through' => ! $item['included']])>{{ Inr::format((int) $item['qty'] * (int) $item['rate']) }}</span>

                                            <button
                                                type="button"
                                                wire:click="toggleIncluded({{ $item['id'] }})"
                                                aria-pressed="{{ $item['included'] ? 'true' : 'false' }}"
                                                @class([
                                                    'btn !py-1.5 !px-3 text-xs',
                                                    'bg-brand-500 text-white hover:bg-brand-600' => $item['included'],
                                                    'border border-line bg-surface text-muted' => ! $item['included'],
                                                ])
                                                title="{{ $item['included'] ? 'Shown to client and counted in the total' : 'Hidden from client and not counted' }}"
                                            >{{ $item['included'] ? 'Included' : 'Not included' }}</button>

                                            <button type="button" wire:click="removeItem({{ $item['id'] }})" class="p-1.5 text-muted hover:text-red-700" aria-label="Remove {{ $item['name'] }}">✕</button>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <div class="flex justify-between border-t border-line px-6 py-3 text-sm">
                            <span class="text-muted">{{ $section->label() }} total</span>
                            <span class="font-semibold tabular-nums">{{ Inr::format($section === \App\Enums\ItemSection::Video ? $totals->video : $totals->photo) }}</span>
                        </div>
                    @endif
                </section>
            @endforeach

            {{-- Terms --}}
            <section class="card">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-6 py-4">
                    <div>
                        <h2 class="text-lg font-semibold">Terms &amp; conditions</h2>
                        <p class="text-sm text-muted">Changes here apply to this quotation only.</p>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" wire:click="resetTerms" wire:confirm="Replace these terms with your studio's current {{ $isCorporate ? 'corporate' : 'wedding' }} terms?" class="btn-secondary">Reset</button>
                        <button type="button" wire:click="addTerm" class="btn-secondary">+ Add term</button>
                    </div>
                </div>

                @if (empty($terms))
                    <p class="px-6 py-8 text-center text-sm text-muted">
                        No terms on this quotation. Add one, or set up your default terms on the
                        @can('manage-studio-content')
                            <a href="{{ route('studio.terms') }}" wire:navigate class="font-semibold text-brand-600">Terms page</a>.
                        @else
                            Terms page (ask your studio admin).
                        @endcan
                    </p>
                @else
                    <ol wire:sort="sortTerms" class="divide-y divide-line">
                        @foreach ($terms as $termIndex => $term)
                            <li wire:key="term-{{ $term['id'] }}" wire:sort:item="{{ $term['id'] }}" class="flex items-start gap-3 bg-surface px-4 py-4 sm:px-6">
                                <button type="button" wire:sort:handle class="mt-2 cursor-grab text-lg leading-none text-muted" aria-label="Drag to reorder">⋮⋮</button>
                                <span class="mt-2 w-5 shrink-0 text-sm font-semibold text-muted tabular-nums">{{ $termIndex + 1 }}</span>
                                <div class="min-w-0 flex-1 space-y-2" wire:sort:ignore>
                                    <input aria-label="Term title" type="text" wire:model.live.debounce.500ms="terms.{{ $termIndex }}.title" class="input py-2 font-semibold" placeholder="Title (optional)">
                                    <textarea aria-label="Term text" wire:model.live.debounce.500ms="terms.{{ $termIndex }}.body" rows="2" class="input py-2 text-sm"></textarea>
                                    @error("terms.$termIndex.body") <p class="input-error">{{ $message }}</p> @enderror
                                </div>
                                <button type="button" wire:click="removeTerm({{ $term['id'] }})" class="mt-2 px-2 text-muted hover:text-red-700" aria-label="Remove term" wire:sort:ignore>✕</button>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </section>

            {{-- Internal notes --}}
            <section class="card space-y-3 p-6">
                <label for="internal-notes" class="text-lg font-semibold">Internal notes</label>
                <p class="text-sm text-muted">Only your team sees these.</p>
                <textarea id="internal-notes" wire:model.live.debounce.500ms="internalNotes" rows="3" class="input"></textarea>
                @error('internalNotes') <p class="input-error">{{ $message }}</p> @enderror
            </section>
        </div>

        {{-- Price summary --}}
        <aside class="card space-y-4 p-5 lg:sticky lg:top-20">
            <h2 class="text-lg font-semibold">Price summary</h2>

            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-muted">Crew ({{ $totals->crewCount }})</dt><dd class="tabular-nums">{{ Inr::format($totals->crew) }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted">Video &amp; photos</dt><dd class="tabular-nums">{{ Inr::format($totals->video + $totals->photo) }}</dd></div>
                <div class="flex justify-between border-t border-line pt-2 font-semibold"><dt>Subtotal</dt><dd class="tabular-nums">{{ Inr::format($totals->subtotal) }}</dd></div>
            </dl>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="discount" class="label text-xs tracking-wide text-muted uppercase">Discount %</label>
                    <input id="discount" type="number" min="0" max="100" step="0.01" wire:model.live.debounce.500ms="discountPercent" class="input py-2">
                </div>
                <div>
                    <label for="gst" class="label text-xs tracking-wide text-muted uppercase">GST %</label>
                    <input id="gst" type="number" min="0" max="100" step="0.01" wire:model.live.debounce.500ms="gstPercent" class="input py-2" @disabled($gstMode !== 'included')>
                </div>
                <div class="col-span-2">
                    <label for="gst-mode" class="sr-only">GST handling</label>
                    <select id="gst-mode" wire:model.live="gstMode" class="input py-2 text-sm">
                        @foreach ($gstModes as $modeOption)
                            <option value="{{ $modeOption->value }}">{{ $modeOption->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @error('discountPercent') <p class="input-error">{{ $message }}</p> @enderror
            @error('gstPercent') <p class="input-error">{{ $message }}</p> @enderror

            <dl class="space-y-2 text-sm">
                @if ($totals->discount)
                    <div class="flex justify-between text-red-700"><dt>Discount ({{ (float) $discountPercent }}%)</dt><dd class="tabular-nums">−{{ Inr::format($totals->discount) }}</dd></div>
                @endif
                @if ($gstMode === 'included')
                    <div class="flex justify-between"><dt class="text-muted">GST ({{ (float) $gstPercent }}%)</dt><dd class="tabular-nums">{{ Inr::format($totals->gst) }}</dd></div>
                @endif
            </dl>

            <div class="border-t border-line pt-4">
                <div class="flex items-baseline justify-between gap-2">
                    <span class="text-lg font-semibold">Grand total</span>
                    <span class="text-2xl font-bold text-brand-700 tabular-nums">{{ Inr::format($totals->grand) }}</span>
                </div>
                <p class="mt-1 text-xs text-muted">
                    @if ($gstMode === 'included')
                        Including {{ (float) $gstPercent }}% GST
                    @elseif ($gstMode === 'extra')
                        Exclusive of GST
                    @else
                        No GST
                    @endif
                    @if ($totals->isManual)
                        · set manually (calculated {{ Inr::format($totals->calculated) }})
                    @endif
                </p>
            </div>

            <div class="space-y-2 rounded-xl bg-canvas p-3">
                <label for="manual-total" class="label text-sm">Final amount (optional)</label>
                <div class="relative">
                    <span class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-sm text-muted">₹</span>
                    <input id="manual-total" type="number" min="0" wire:model.live.debounce.500ms="manualTotal" class="input py-2 pl-7 tabular-nums" placeholder="Use calculated total">
                </div>
                @error('manualTotal') <p class="input-error">{{ $message }}</p> @enderror
                <div class="flex gap-3 text-xs font-semibold">
                    @if ($totals->isManual)
                        <button type="button" wire:click="clearManualTotal" class="text-muted hover:text-ink">Use calculated total</button>
                    @else
                        <button type="button" wire:click="useCalculatedAsManualTotal" class="text-muted hover:text-ink">Start from {{ Inr::format($totals->calculated) }}</button>
                    @endif
                </div>
            </div>

            <label for="show-breakdown" class="flex items-start gap-2 text-sm">
                <input id="show-breakdown" type="checkbox" wire:model.live="showBreakdown" class="mt-0.5 size-4 accent-brand-500">
                <span>
                    <span class="block font-medium">Show price breakdown to client</span>
                    <span class="block text-xs text-muted">Off: the client sees only the grand total.</span>
                </span>
            </label>
        </aside>
    </div>
</fieldset>
</div>

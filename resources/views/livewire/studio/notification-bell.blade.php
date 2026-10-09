<div class="relative" x-data="{ open: false }" x-on:keydown.escape.window="open = false" wire:poll.60s>
    <button
        type="button"
        x-on:click="open = ! open"
        class="relative flex size-10 items-center justify-center rounded-full border border-line hover:bg-canvas"
        aria-label="Notifications{{ $unreadCount ? ", {$unreadCount} unread" : '' }}"
        x-bind:aria-expanded="open"
    >
        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path d="M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9H3s3-2 3-9" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" stroke-linecap="round" />
        </svg>
        @if ($unreadCount)
            <span class="absolute -top-1 -right-1 flex min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-xs font-bold text-white tabular-nums">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
        @endif
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition.origin.top.right
        x-on:click.outside="open = false"
        class="absolute right-0 z-40 mt-2 w-80 max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border border-line bg-surface shadow-xl"
    >
        <div class="flex items-center justify-between border-b border-line px-4 py-3">
            <p class="font-semibold">Client activity</p>
            @if ($unreadCount)
                <button type="button" wire:click="markAllRead" class="text-xs font-semibold text-brand-600">Mark all read</button>
            @endif
        </div>

        @if ($notifications->isEmpty())
            <p class="px-4 py-8 text-center text-sm text-muted">You'll see here when a client opens or accepts a quotation.</p>
        @else
            <ul class="max-h-96 divide-y divide-line overflow-y-auto">
                @foreach ($notifications as $notification)
                    @php($accepted = ($notification->data['kind'] ?? '') === 'accepted')
                    <li wire:key="notification-{{ $notification->id }}">
                        <button type="button" wire:click="open('{{ $notification->id }}')" @class(['flex w-full gap-3 px-4 py-3 text-left hover:bg-canvas', 'bg-brand-50/60' => ! $notification->read_at])>
                            <span @class([
                                'mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full text-sm',
                                'bg-emerald-100 text-emerald-700' => $accepted,
                                'bg-violet-100 text-violet-700' => ! $accepted,
                            ]) aria-hidden="true">{{ $accepted ? '✓' : '👁' }}</span>
                            <span class="min-w-0">
                                <span class="block text-sm {{ $notification->read_at ? '' : 'font-semibold' }}">{{ $notification->data['message'] ?? '' }}</span>
                                <span class="block text-xs text-muted">{{ $notification->created_at->diffForHumans() }}</span>
                            </span>
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

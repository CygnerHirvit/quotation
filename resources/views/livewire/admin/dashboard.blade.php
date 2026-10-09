<div class="space-y-8">
    <div>
        <h1 class="text-3xl font-semibold">Platform overview</h1>
        <p class="mt-1 text-muted">All studios on the platform.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-line bg-surface p-5">
            <p class="text-xs font-semibold tracking-wide text-muted uppercase">Studios</p>
            <p class="mt-2 text-3xl font-semibold tabular-nums">{{ $studioCount }}</p>
        </div>
        <div class="rounded-2xl border border-line bg-surface p-5">
            <p class="text-xs font-semibold tracking-wide text-muted uppercase">Active studios</p>
            <p class="mt-2 text-3xl font-semibold tabular-nums">{{ $activeStudioCount }}</p>
        </div>
        <div class="rounded-2xl border border-line bg-surface p-5">
            <p class="text-xs font-semibold tracking-wide text-muted uppercase">Studio users</p>
            <p class="mt-2 text-3xl font-semibold tabular-nums">{{ $userCount }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-line bg-surface">
        <div class="flex items-center justify-between border-b border-line px-5 py-4">
            <h2 class="font-semibold">Latest studios</h2>
            <a href="{{ route('admin.studios.index') }}" wire:navigate class="text-sm font-semibold text-brand-600">Manage studios →</a>
        </div>
        <ul class="divide-y divide-line">
            @forelse ($recentStudios as $studio)
                <li wire:key="studio-{{ $studio->id }}" class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
                    <div>
                        <p class="font-medium">{{ $studio->name }}</p>
                        <p class="text-sm text-muted">{{ $studio->city ?? 'No city' }} · {{ $studio->quote_prefix }} · {{ $studio->users_count }} users</p>
                    </div>
                    <span @class([
                        'rounded-full px-3 py-1 text-xs font-semibold',
                        'bg-brand-50 text-brand-700' => $studio->isActive(),
                        'bg-red-50 text-red-700' => ! $studio->isActive(),
                    ])>{{ $studio->status->label() }}</span>
                </li>
            @empty
                <li class="px-5 py-8 text-center text-muted">No studios yet.</li>
            @endforelse
        </ul>
    </div>
</div>

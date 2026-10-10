<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-semibold">Studios</h1>
            <p class="mt-1 text-muted">Showing {{ $studios->total() }} {{ str('studio')->plural($studios->total()) }}</p>
        </div>
        <a href="{{ route('admin.studios.create') }}" wire:navigate class="btn-primary">+ New studio</a>
    </div>

    @include('partials.flash')

    <div class="flex flex-wrap items-center gap-3">
        <input
            id="search"
            type="search"
            wire:model.live.debounce.300ms="search"
            placeholder="Search name, city or prefix"
            aria-label="Search studios"
            class="input w-full sm:w-64 sm:max-w-xs"
        >
        <select id="status" wire:model.live="status" aria-label="Filter by status" class="input w-full sm:w-auto">
            <option value="">All statuses</option>
            @foreach ($statuses as $statusOption)
                <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
            @endforeach
        </select>
    </div>

    {{-- Mobile Cards (<640px) --}}
    <div class="space-y-3 sm:hidden">
        @forelse ($studios as $studio)
            <div wire:key="studio-card-{{ $studio->id }}" class="card p-4 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <a href="{{ route('admin.studios.edit', $studio) }}" wire:navigate class="flex items-center gap-3 min-w-0 flex-1">
                        @if ($studio->logoUrl())
                            <img src="{{ $studio->logoUrl() }}" alt="" class="size-10 rounded-full object-cover shrink-0">
                        @else
                            <span class="flex size-10 items-center justify-center rounded-full bg-brand-50 font-bold text-brand-700 shrink-0">{{ $studio->initial() }}</span>
                        @endif
                        <div class="min-w-0 flex-1">
                            <span class="block font-semibold text-ink truncate">{{ $studio->name }}</span>
                            <span class="block text-xs text-muted truncate">{{ $studio->city ?? 'No city' }} · <span class="font-mono text-brand-700">{{ $studio->quote_prefix }}</span></span>
                        </div>
                    </a>
                    <span @class([
                        'badge shrink-0',
                        'bg-brand-50 text-brand-700' => $studio->isActive(),
                        'bg-red-50 text-red-700' => ! $studio->isActive(),
                    ])>{{ $studio->status->label() }}</span>
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs text-muted pt-2 border-t border-line/60">
                    <div>
                        <span class="block font-medium text-ink">Admin</span>
                        @if ($admin = $studio->admins->first())
                            <span class="truncate block">{{ $admin->name }}</span>
                        @else
                            <span>No admin</span>
                        @endif
                    </div>
                    <div class="text-right">
                        <span class="block font-medium text-ink">Users</span>
                        <span class="tabular-nums">{{ $studio->users_count }} members</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-2 border-t border-line/60">
                    <a href="{{ route('admin.studios.edit', $studio) }}" wire:navigate class="btn-secondary !py-1.5 !px-3 text-xs flex-1 text-center justify-center">Edit</a>
                    <button
                        type="button"
                        wire:click="toggleStatus({{ $studio->id }})"
                        wire:confirm="{{ $studio->isActive() ? "Block {$studio->name}? Its users will be signed out." : "Activate {$studio->name} again?" }}"
                        class="{{ $studio->isActive() ? 'btn-danger' : 'btn-secondary' }} !py-1.5 !px-3 text-xs flex-1 text-center justify-center"
                    >{{ $studio->isActive() ? 'Block' : 'Activate' }}</button>
                </div>
            </div>
        @empty
            <div class="card p-8 text-center text-muted text-sm">
                No studios match. <a href="{{ route('admin.studios.create') }}" wire:navigate class="font-semibold text-brand-600">Create the first one</a>.
            </div>
        @endforelse
    </div>

    {{-- Desktop & Tablet Table (>=640px) --}}
    <div class="card overflow-x-auto hidden sm:block">
        <table class="w-full min-w-[720px] text-sm">
            <thead class="border-b border-line text-left text-xs tracking-wide text-muted uppercase">
                <tr>
                    <th class="px-5 py-3 font-semibold">Studio</th>
                    <th class="px-5 py-3 font-semibold">Admin</th>
                    <th class="px-5 py-3 font-semibold">Prefix</th>
                    <th class="px-5 py-3 font-semibold">Users</th>
                    <th class="px-5 py-3 font-semibold">Status</th>
                    <th class="px-5 py-3"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($studios as $studio)
                    <tr wire:key="studio-row-{{ $studio->id }}">
                        <td class="px-5 py-4">
                            <a href="{{ route('admin.studios.edit', $studio) }}" wire:navigate class="flex items-center gap-3">
                                @if ($studio->logoUrl())
                                    <img src="{{ $studio->logoUrl() }}" alt="" class="size-9 rounded-full object-cover">
                                @else
                                    <span class="flex size-9 items-center justify-center rounded-full bg-brand-50 font-bold text-brand-700">{{ $studio->initial() }}</span>
                                @endif
                                <span>
                                    <span class="block font-semibold">{{ $studio->name }}</span>
                                    <span class="block text-muted">{{ $studio->city ?? 'No city' }}</span>
                                </span>
                            </a>
                        </td>
                        <td class="px-5 py-4">
                            @if ($admin = $studio->admins->first())
                                <span class="block">{{ $admin->name }}</span>
                                <span class="block text-muted">{{ $admin->email }}</span>
                            @else
                                <span class="text-muted">No admin</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 font-mono">{{ $studio->quote_prefix }}</td>
                        <td class="px-5 py-4 tabular-nums">{{ $studio->users_count }}</td>
                        <td class="px-5 py-4">
                            <span @class([
                                'badge',
                                'bg-brand-50 text-brand-700' => $studio->isActive(),
                                'bg-red-50 text-red-700' => ! $studio->isActive(),
                            ])>{{ $studio->status->label() }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.studios.edit', $studio) }}" wire:navigate class="btn-secondary">Edit</a>
                                <button
                                    type="button"
                                    wire:click="toggleStatus({{ $studio->id }})"
                                    wire:confirm="{{ $studio->isActive() ? "Block {$studio->name}? Its users will be signed out." : "Activate {$studio->name} again?" }}"
                                    class="{{ $studio->isActive() ? 'btn-danger' : 'btn-secondary' }}"
                                >{{ $studio->isActive() ? 'Block' : 'Activate' }}</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-muted">
                            No studios match. <a href="{{ route('admin.studios.create') }}" wire:navigate class="font-semibold text-brand-600">Create the first one</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $studios->links() }}
</div>

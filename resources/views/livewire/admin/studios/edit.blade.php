<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <a href="{{ route('admin.studios.index') }}" wire:navigate class="text-sm text-muted hover:text-ink">← All studios</a>
            <h1 class="mt-2 text-3xl font-semibold">{{ $studio->name }}</h1>
            <p class="mt-1 text-muted">Created {{ $studio->created_at->format('d M Y') }} · {{ $studio->slug }}</p>
        </div>

        @can('impersonate', $studio)
            <form method="POST" action="{{ route('admin.studios.impersonate', $studio) }}">
                @csrf
                <button type="submit" class="btn-secondary">Log in as studio admin</button>
            </form>
        @endcan
    </div>

    @include('partials.flash')

    <form wire:submit="save" class="card space-y-5 p-6">
        <h2 class="text-lg font-semibold">Studio details</h2>
        @include('livewire.admin.studios.partials.studio-fields')

        <div class="flex justify-end">
            <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">Save changes</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
        </div>
    </form>

    <section class="card">
        <div class="border-b border-line px-6 py-4">
            <h2 class="text-lg font-semibold">Users</h2>
            <p class="mt-1 text-sm text-muted">Studio admins add their own staff. You can reset passwords or turn off a login here.</p>
        </div>

        <ul class="divide-y divide-line">
            @foreach ($users as $studioUser)
                <li wire:key="user-{{ $studioUser->id }}" class="space-y-3 px-6 py-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="font-medium">
                                {{ $studioUser->name }}
                                <span class="badge ml-1 bg-canvas text-muted">{{ $studioUser->role->label() }}</span>
                                @unless ($studioUser->is_active)
                                    <span class="badge ml-1 bg-red-50 text-red-700">Inactive</span>
                                @endunless
                            </p>
                            <p class="text-sm text-muted">{{ $studioUser->email }}</p>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" wire:click="startPasswordReset({{ $studioUser->id }})" class="btn-secondary">Reset password</button>
                            <button
                                type="button"
                                wire:click="toggleUserActive({{ $studioUser->id }})"
                                class="{{ $studioUser->is_active ? 'btn-danger' : 'btn-secondary' }}"
                            >{{ $studioUser->is_active ? 'Deactivate' : 'Activate' }}</button>
                        </div>
                    </div>

                    @if ($passwordUserId === $studioUser->id)
                        <form wire:submit="savePassword" class="flex flex-wrap items-start gap-3 rounded-xl bg-canvas p-4">
                            <div class="min-w-0 flex-1">
                                <label for="new-password" class="sr-only">New password</label>
                                <input id="new-password" type="text" wire:model="newPassword" placeholder="New password (min 8 characters)" class="input font-mono" autocomplete="new-password">
                                @error('newPassword') <p class="input-error">{{ $message }}</p> @enderror
                            </div>
                            <button type="submit" class="btn-primary">Save password</button>
                            <button type="button" wire:click="cancelPasswordReset" class="btn-secondary">Cancel</button>
                        </form>
                    @endif
                </li>
            @endforeach
        </ul>

        <form wire:submit="addAdmin" class="space-y-4 border-t border-line px-6 py-5">
            <h3 class="font-semibold">Add another studio admin</h3>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="new-admin-name" class="label">Name</label>
                    <input id="new-admin-name" type="text" wire:model="newAdminName" class="input">
                    @error('newAdminName') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="new-admin-email" class="label">Email</label>
                    <input id="new-admin-email" type="email" wire:model="newAdminEmail" class="input" autocomplete="off">
                    @error('newAdminEmail') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="new-admin-password" class="label">Password</label>
                    <input id="new-admin-password" type="text" wire:model="newAdminPassword" class="input font-mono" autocomplete="new-password">
                    @error('newAdminPassword') <p class="input-error">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="btn-secondary">Add admin</button>
            </div>
        </form>
    </section>
</div>

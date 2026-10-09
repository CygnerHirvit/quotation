<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-semibold">Team</h1>
            <p class="mt-1 text-muted">Admins manage everything. Staff can only create and send quotations.</p>
        </div>
        @if ($editingId === null)
            <button type="button" wire:click="create" class="btn-primary">+ Add person</button>
        @endif
    </div>

    @include('partials.flash')

    @if ($editingId !== null)
        @php($editingSelf = $editingId === auth()->id())
        <form wire:submit="save" class="card space-y-5 p-6">
            <h2 class="text-lg font-semibold">{{ $editingId ? 'Edit '.$name : 'Add a person' }}</h2>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="team-name" class="label">Name</label>
                    <input id="team-name" type="text" wire:model="name" class="input">
                    @error('name') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="team-email" class="label">Email (used to sign in)</label>
                    <input id="team-email" type="email" wire:model="email" class="input" autocomplete="off">
                    @error('email') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="team-phone" class="label">Phone</label>
                    <input id="team-phone" type="tel" wire:model="phone" class="input">
                    @error('phone') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="team-role" class="label">Role</label>
                    <select id="team-role" wire:model="role" class="input" @disabled($editingSelf)>
                        @foreach ($roles as $roleOption)
                            <option value="{{ $roleOption->value }}">{{ $roleOption->label() }}</option>
                        @endforeach
                    </select>
                    @if ($editingSelf)
                        <p class="mt-1.5 text-xs text-muted">You can't change your own role.</p>
                    @endif
                    @error('role') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="team-password" class="label">{{ $editingId ? 'New password' : 'Password' }}</label>
                    <input id="team-password" type="text" wire:model="password" class="input font-mono" autocomplete="new-password">
                    <p class="mt-1.5 text-xs text-muted">{{ $editingId ? 'Leave empty to keep the current password.' : 'At least 8 characters. Share it with them.' }}</p>
                    @error('password') <p class="input-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" wire:click="cancel" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">{{ $editingId ? 'Save' : 'Add person' }}</button>
            </div>
        </form>
    @endif

    <ul class="card divide-y divide-line">
        @foreach ($members as $member)
            <li wire:key="member-{{ $member->id }}" class="flex flex-wrap items-center justify-between gap-3 px-6 py-4">
                <div class="min-w-0">
                    <p class="font-medium">
                        {{ $member->name }}
                        @if ($member->id === auth()->id())
                            <span class="text-sm font-normal text-muted">(you)</span>
                        @endif
                        <span @class([
                            'badge ml-1',
                            'bg-brand-50 text-brand-700' => $member->isStudioAdmin(),
                            'bg-canvas text-muted' => ! $member->isStudioAdmin(),
                        ])>{{ $member->role->label() }}</span>
                        @unless ($member->is_active)
                            <span class="badge ml-1 bg-red-50 text-red-700">Inactive</span>
                        @endunless
                    </p>
                    <p class="truncate text-sm text-muted">{{ $member->email }}{{ $member->phone ? ' · '.$member->phone : '' }}</p>
                </div>
                <div class="flex gap-2">
                    <button type="button" wire:click="edit({{ $member->id }})" class="btn-secondary">Edit</button>
                    @can('changeAccess', $member)
                        <button
                            type="button"
                            wire:click="toggleActive({{ $member->id }})"
                            @if ($member->is_active) wire:confirm="Turn off {{ $member->name }}'s login?" @endif
                            class="{{ $member->is_active ? 'btn-danger' : 'btn-secondary' }}"
                        >{{ $member->is_active ? 'Deactivate' : 'Activate' }}</button>
                    @endcan
                </div>
            </li>
        @endforeach
    </ul>
</div>

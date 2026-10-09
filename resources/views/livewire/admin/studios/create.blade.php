<div class="mx-auto max-w-3xl space-y-6">
    <div>
        <a href="{{ route('admin.studios.index') }}" wire:navigate class="text-sm text-muted hover:text-ink">← All studios</a>
        <h1 class="mt-2 text-3xl font-semibold">New studio</h1>
        <p class="mt-1 text-muted">The studio admin can fill in the rest (tagline, GST, terms, price list) after signing in.</p>
    </div>

    <form wire:submit="save" class="space-y-6">
        <section class="card space-y-5 p-6">
            <h2 class="text-lg font-semibold">Studio</h2>
            @include('livewire.admin.studios.partials.studio-fields')
        </section>

        <section class="card space-y-5 p-6">
            <div>
                <h2 class="text-lg font-semibold">Studio admin login</h2>
                <p class="mt-1 text-sm text-muted">This person manages the studio. Share these details with them.</p>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="admin-name" class="label">Name</label>
                    <input id="admin-name" type="text" wire:model="adminName" class="input">
                    @error('adminName') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="admin-email" class="label">Email</label>
                    <input id="admin-email" type="email" wire:model="adminEmail" class="input" autocomplete="off">
                    @error('adminEmail') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="admin-password" class="label">Password</label>
                    <input id="admin-password" type="text" wire:model="adminPassword" class="input font-mono" autocomplete="new-password">
                    <p class="mt-1.5 text-xs text-muted">At least 8 characters.</p>
                    @error('adminPassword') <p class="input-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.studios.index') }}" wire:navigate class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">Create studio</span>
                <span wire:loading wire:target="save">Creating…</span>
            </button>
        </div>
    </form>
</div>

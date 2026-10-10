{{-- Shared studio fields for the create and edit pages. Expects $themes and optionally $statuses. --}}
<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="form-name" class="label">Studio name</label>
        <input id="form-name" type="text" wire:model="form.name" class="input" placeholder="e.g. Matesmania Production">
        @error('form.name') <p class="input-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="form-city" class="label">City</label>
        <input id="form-city" type="text" wire:model="form.city" class="input" placeholder="">
        @error('form.city') <p class="input-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="form-quote-prefix" class="label">Quotation prefix</label>
        <input id="form-quote-prefix" type="text" wire:model.live.debounce.300ms="form.quotePrefix" maxlength="6" class="input font-mono uppercase" placeholder="MMP">
        <p class="mt-1.5 text-xs text-muted">Numbers look like {{ str($form->quotePrefix ?: 'ABC')->upper() }}-{{ now()->year }}-001</p>
        @error('form.quotePrefix') <p class="input-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="form-phone" class="label">Phone</label>
        <input id="form-phone" type="tel" wire:model="form.phone" class="input" placeholder="+91 98765 43210">
        @error('form.phone') <p class="input-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="form-email" class="label">Studio email</label>
        <input id="form-email" type="email" wire:model="form.email" class="input" placeholder="hello@studio.com">
        @error('form.email') <p class="input-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="form-theme" class="label">Client page theme</label>
        <select id="form-theme" wire:model="form.theme" class="input">
            @foreach ($themes as $theme)
                <option value="{{ $theme->value }}">{{ $theme->label() }}</option>
            @endforeach
        </select>
        @error('form.theme') <p class="input-error">{{ $message }}</p> @enderror
    </div>

    @isset($statuses)
        <div>
            <label for="form-status" class="label">Status</label>
            <select id="form-status" wire:model="form.status" class="input">
                @foreach ($statuses as $statusOption)
                    <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                @endforeach
            </select>
            @error('form.status') <p class="input-error">{{ $message }}</p> @enderror
        </div>
    @endisset

    <div class="sm:col-span-2">
        <label for="form-logo" class="label">Logo</label>
        <div class="flex items-center gap-4">
            @if ($form->logo?->isPreviewable())
                <img src="{{ $form->logo->temporaryUrl() }}" alt="New logo preview" class="size-14 rounded-full border border-line object-cover">
            @elseif ($form->studio?->logoUrl())
                <img src="{{ $form->studio->logoUrl() }}" alt="Current logo" class="size-14 rounded-full border border-line object-cover">
            @endif
            <input id="form-logo" type="file" wire:model="form.logo" accept="image/png,image/jpeg,image/webp" class="text-sm">
        </div>
        <p class="mt-1.5 text-xs text-muted">PNG, JPG or WEBP, up to 2 MB. A square image works best.</p>
        <div wire:loading wire:target="form.logo" class="mt-1.5 text-sm text-muted">Uploading…</div>
        @error('form.logo') <p class="input-error">{{ $message }}</p> @enderror
    </div>
</div>

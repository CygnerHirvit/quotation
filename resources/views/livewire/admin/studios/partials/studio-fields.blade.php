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

    {{-- Studio Logo --}}
    <div class="sm:col-span-2 rounded-xl border border-line bg-canvas/40 p-4 sm:p-5" wire:key="admin-studio-logo-card">
        <input id="form-logo" type="file" wire:model="form.logo" accept="image/png,image/jpeg,image/webp" class="sr-only">

        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-6">
            {{-- Logo Avatar / Preview Box (1:1 Aspect Ratio) --}}
            <div class="relative group size-16 sm:size-20 shrink-0">
                <label for="form-logo" @class([
                    'relative flex size-full cursor-pointer items-center justify-center overflow-hidden rounded-2xl bg-surface transition hover:border-brand-500 hover:bg-brand-50/30 focus-within:ring-2 focus-within:ring-brand-500',
                    'border border-line shadow-xs' => ($form->logo?->isPreviewable() || (! $form->removeLogo && $form->studio?->logoUrl())),
                    'border-2 border-dashed border-line' => (! $form->logo?->isPreviewable() && ($form->removeLogo || ! $form->studio?->logoUrl())),
                ])>
                    @if ($form->logo?->isPreviewable())
                        <img src="{{ $form->logo->temporaryUrl() }}" alt="New logo preview" class="size-full object-cover">
                        <div class="absolute inset-0 flex flex-col items-center justify-center bg-black/50 text-white opacity-0 transition group-hover:opacity-100">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                            </svg>
                            <span class="text-[9px] font-semibold mt-0.5">Change</span>
                        </div>
                    @elseif (! $form->removeLogo && $form->studio?->logoUrl())
                        <img src="{{ $form->studio->logoUrl() }}" alt="{{ $form->name }} logo" class="size-full object-cover">
                        <div class="absolute inset-0 flex flex-col items-center justify-center bg-black/50 text-white opacity-0 transition group-hover:opacity-100">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                            </svg>
                            <span class="text-[9px] font-semibold mt-0.5">Change</span>
                        </div>
                    @elseif ($form->removeLogo)
                        <div class="flex flex-col items-center justify-center text-red-500/80 p-2 text-center">
                            <svg class="size-5 stroke-[1.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                            <span class="text-[9px] font-semibold mt-0.5">Removed</span>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center text-muted group-hover:text-brand-600 transition p-2 text-center">
                            <svg class="size-5 stroke-[1.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                            </svg>
                            <span class="text-[9px] font-semibold mt-0.5">Add logo</span>
                        </div>
                    @endif

                    {{-- Loading spinner overlay --}}
                    <div wire:loading.flex wire:target="form.logo" class="absolute inset-0 flex flex-col items-center justify-center bg-surface/90 backdrop-blur-xs rounded-2xl z-10">
                        <svg class="size-4 animate-spin text-brand-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </label>
            </div>

            {{-- Logo Details & Actions --}}
            <div class="flex-1 min-w-0 space-y-1.5">
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="text-sm font-semibold text-ink">Studio logo</h3>
                </div>
                <p class="text-xs text-muted">PNG, JPG or WEBP up to 2 MB. Square format recommended.</p>

                <div class="flex flex-wrap items-center gap-2 pt-1">
                    @if ($form->logo?->isPreviewable())
                        <label for="form-logo" class="btn-secondary cursor-pointer !py-1 !px-3 text-xs">
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 16.5v-2.25m-14.25-5.25L12 3m0 0 5.25 5.25M12 3v12" />
                            </svg>
                            <span>Change logo</span>
                        </label>
                        <button type="button" wire:click="clearSelectedLogo" class="btn-secondary !py-1 !px-3 text-xs text-red-600 hover:text-red-700">
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                            <span>Clear selection</span>
                        </button>
                    @elseif ($form->removeLogo)
                        <button type="button" wire:click="undoLogoRemoval" class="btn-secondary !py-1 !px-3 text-xs">
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                            </svg>
                            <span>Undo removal</span>
                        </button>
                        <label for="form-logo" class="btn-secondary cursor-pointer !py-1 !px-3 text-xs">
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 16.5v-2.25m-14.25-5.25L12 3m0 0 5.25 5.25M12 3v12" />
                            </svg>
                            <span>Upload new</span>
                        </label>
                    @elseif ($form->studio?->logoUrl())
                        <label for="form-logo" class="btn-secondary cursor-pointer !py-1 !px-3 text-xs">
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 16.5v-2.25m-14.25-5.25L12 3m0 0 5.25 5.25M12 3v12" />
                            </svg>
                            <span>Change logo</span>
                        </label>
                        <button type="button" wire:click="markLogoForRemoval" class="btn-danger !py-1 !px-3 text-xs">
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                            <span>Remove</span>
                        </button>
                    @else
                        <label for="form-logo" class="btn-secondary cursor-pointer !py-1 !px-3 text-xs">
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 16.5v-2.25m-14.25-5.25L12 3m0 0 5.25 5.25M12 3v12" />
                            </svg>
                            <span>Upload logo</span>
                        </label>
                    @endif

                    <div wire:loading wire:target="form.logo" class="inline-flex items-center gap-1.5 text-xs font-medium text-brand-600">
                        <svg class="size-3 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Uploading preview…</span>
                    </div>
                </div>

                @error('form.logo')
                    <p class="input-error text-xs">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</div>

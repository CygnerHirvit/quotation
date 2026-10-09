@php
    $studio = $form->studio;

    $imageFields = [
        ['property' => 'logo', 'column' => 'logo_path', 'label' => 'Logo', 'hint' => 'Square PNG, JPG or WEBP, up to 2 MB.', 'current' => $studio->logoUrl(), 'shape' => 'size-16 rounded-full'],
        ['property' => 'coverWedding', 'column' => 'cover_wedding_path', 'label' => 'Wedding cover photo', 'hint' => 'Shown at the top of wedding quotations. Landscape, up to 5 MB.', 'current' => $studio->coverUrl('wedding'), 'shape' => 'h-16 w-28 rounded-lg'],
        ['property' => 'coverCorporate', 'column' => 'cover_corporate_path', 'label' => 'Corporate cover photo', 'hint' => 'Shown at the top of corporate quotations. Landscape, up to 5 MB.', 'current' => $studio->coverUrl('corporate'), 'shape' => 'h-16 w-28 rounded-lg'],
    ];
@endphp

<div class="mx-auto max-w-3xl space-y-6">
    <div>
        <h1 class="text-3xl font-semibold">Studio settings</h1>
        <p class="mt-1 text-muted">These details appear on every quotation you send.</p>
    </div>

    @include('partials.flash')

    <form wire:submit="save" class="space-y-6">
        {{-- Branding --}}
        <section class="card space-y-5 p-6">
            <h2 class="text-lg font-semibold">Studio details</h2>

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="settings-name" class="label">Studio name</label>
                    <input id="settings-name" type="text" wire:model="form.name" class="input">
                    @error('form.name') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="settings-tagline" class="label">Tagline</label>
                    <input id="settings-tagline" type="text" wire:model="form.tagline" class="input" placeholder="Fueling stories, powered by friendship.">
                    @error('form.tagline') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="settings-phone" class="label">Phone</label>
                    <input id="settings-phone" type="tel" wire:model="form.phone" class="input">
                    @error('form.phone') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="settings-email" class="label">Email</label>
                    <input id="settings-email" type="email" wire:model="form.email" class="input">
                    @error('form.email') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="settings-city" class="label">City</label>
                    <input id="settings-city" type="text" wire:model="form.city" class="input">
                    @error('form.city') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="settings-signature" class="label">Authorized signature name</label>
                    <input id="settings-signature" type="text" wire:model="form.signatureName" class="input" placeholder="Shown under “Authorized Signature”">
                    @error('form.signatureName') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="settings-address" class="label">Address</label>
                    <textarea id="settings-address" wire:model="form.address" rows="2" class="input"></textarea>
                    @error('form.address') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <p class="label">Quotation prefix</p>
                    <p class="rounded-lg bg-canvas px-3 py-2.5 font-mono">{{ $studio->quote_prefix }}-{{ now()->year }}-001</p>
                    <p class="mt-1.5 text-xs text-muted">Ask the platform admin to change it.</p>
                </div>
            </div>
        </section>

        {{-- Images --}}
        <section class="card space-y-5 p-6">
            <h2 class="text-lg font-semibold">Logo and cover photos</h2>

            @foreach ($imageFields as $field)
                <div wire:key="image-{{ $field['property'] }}">
                    <label for="settings-{{ $field['property'] }}" class="label">{{ $field['label'] }}</label>
                    <div class="flex flex-wrap items-center gap-4">
                        @if ($form->{$field['property']}?->isPreviewable())
                            <img src="{{ $form->{$field['property']}->temporaryUrl() }}" alt="" class="{{ $field['shape'] }} border border-line object-cover">
                        @elseif ($field['current'])
                            <img src="{{ $field['current'] }}" alt="" class="{{ $field['shape'] }} border border-line object-cover">
                        @else
                            <span class="{{ $field['shape'] }} flex items-center justify-center border border-dashed border-line text-xs text-muted">None</span>
                        @endif

                        <input id="settings-{{ $field['property'] }}" type="file" wire:model="form.{{ $field['property'] }}" accept="image/png,image/jpeg,image/webp" class="min-w-0 text-sm">

                        @if ($field['current'])
                            <button type="button" wire:click="removeImage('{{ $field['column'] }}')" wire:confirm="Remove this image?" class="text-sm font-semibold text-red-700">Remove</button>
                        @endif
                    </div>
                    <p class="mt-1.5 text-xs text-muted">{{ $field['hint'] }}</p>
                    <div wire:loading wire:target="form.{{ $field['property'] }}" class="mt-1 text-sm text-muted">Uploading…</div>
                    @error('form.'.$field['property']) <p class="input-error">{{ $message }}</p> @enderror
                </div>
            @endforeach
        </section>

        {{-- Theme --}}
        <section class="card space-y-5 p-6">
            <div>
                <h2 class="text-lg font-semibold">Client page look</h2>
                <p class="mt-1 text-sm text-muted">How your quotations look to couples and clients.</p>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                @foreach ($themes as $theme)
                    @php($palette = $theme->palette())
                    <label wire:key="theme-{{ $theme->value }}" @class([
                        'cursor-pointer overflow-hidden rounded-xl border-2 transition',
                        'border-brand-500' => $form->theme === $theme->value,
                        'border-line hover:border-brand-100' => $form->theme !== $theme->value,
                    ])>
                        <input type="radio" wire:model.live="form.theme" value="{{ $theme->value }}" class="sr-only">
                        <span class="block px-4 py-5" style="background: {{ $palette['background'] }}; color: {{ $palette['ink'] }}">
                            <span class="block text-lg" style="font-family: Georgia, serif">Bride <span style="color: {{ $palette['accent'] }}">&amp;</span> Groom</span>
                            <span class="mt-3 flex gap-1.5">
                                @foreach ($palette as $colour)
                                    <span class="size-4 rounded-full border border-black/10" style="background: {{ $colour }}"></span>
                                @endforeach
                            </span>
                        </span>
                        <span class="block border-t border-line px-4 py-3">
                            <span class="block font-semibold">{{ $theme->label() }}</span>
                            <span class="block text-xs text-muted">{{ $theme->description() }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            @error('form.theme') <p class="input-error">{{ $message }}</p> @enderror

            <div>
                <label for="settings-accent" class="label">Accent colour (optional)</label>
                <div class="flex items-center gap-3">
                    <input type="color" aria-label="Pick accent colour" value="{{ $form->accentColor ?: '#0F4C4A' }}" wire:model.live="form.accentColor" class="h-10 w-14 cursor-pointer rounded-lg border border-line bg-surface p-1">
                    <input id="settings-accent" type="text" wire:model.live.debounce.400ms="form.accentColor" class="input max-w-36 font-mono" placeholder="#0F4C4A">
                    @if ($form->accentColor)
                        <button type="button" wire:click="$set('form.accentColor', '')" class="text-sm font-semibold text-muted hover:text-ink">Use theme colour</button>
                    @endif
                </div>
                <p class="mt-1.5 text-xs text-muted">Replaces the theme's main colour, for example to match your logo.</p>
                @error('form.accentColor') <p class="input-error">{{ $message }}</p> @enderror
            </div>
        </section>

        {{-- Quotation defaults --}}
        <section class="card space-y-5 p-6">
            <div>
                <h2 class="text-lg font-semibold">Quotation defaults</h2>
                <p class="mt-1 text-sm text-muted">Used for new quotations. You can still change them on each quotation.</p>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="settings-gst" class="label">GST %</label>
                    <input id="settings-gst" type="number" step="0.01" min="0" max="100" wire:model="form.gstPercent" class="input">
                    @error('form.gstPercent') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="settings-validity" class="label">Quotation valid for (days)</label>
                    <input id="settings-validity" type="number" min="1" max="365" wire:model="form.validityDays" class="input">
                    @error('form.validityDays') <p class="input-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <label for="settings-notify-email" class="flex items-start gap-3">
                <input id="settings-notify-email" type="checkbox" wire:model="form.notifyEmailOnAccept" class="mt-1 size-4 accent-brand-500">
                <span>
                    <span class="block font-medium">Email me when a client accepts</span>
                    <span class="block text-sm text-muted">Sent to the studio email above{{ $form->email ? ' ('.$form->email.')' : '. Add an email address to use this' }}.</span>
                </span>
            </label>

            <label for="settings-breakdown" class="flex items-start gap-3">
                <input id="settings-breakdown" type="checkbox" wire:model="form.showBreakdownDefault" class="mt-1 size-4 accent-brand-500">
                <span>
                    <span class="block font-medium">Show full price breakdown to clients</span>
                    <span class="block text-sm text-muted">When off, clients only see the Grand Total. Item rates are never sent to them.</span>
                </span>
            </label>
        </section>

        {{-- WhatsApp --}}
        <section class="card space-y-5 p-6">
            <div>
                <h2 class="text-lg font-semibold">WhatsApp message</h2>
                <p class="mt-1 text-sm text-muted">
                    Sent with the “Share on WhatsApp” button. You can use
                    <code class="rounded bg-canvas px-1">{client}</code>
                    <code class="rounded bg-canvas px-1">{studio}</code>
                    <code class="rounded bg-canvas px-1">{number}</code>
                    <code class="rounded bg-canvas px-1">{link}</code>
                </p>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="settings-whatsapp" class="label">Message</label>
                    <textarea id="settings-whatsapp" wire:model.live.debounce.400ms="form.whatsappMessage" rows="5" class="input"></textarea>
                    @error('form.whatsappMessage') <p class="input-error">{{ $message }}</p> @enderror
                    <button type="button" wire:click="resetWhatsappMessage" class="mt-2 text-sm font-semibold text-muted hover:text-ink">Use default message</button>
                </div>
                <div>
                    <p class="label">Preview</p>
                    <div class="rounded-xl bg-[#e7f6e9] p-4 text-sm whitespace-pre-line text-[#1f2c1f]">{{ $whatsappPreview }}</div>
                </div>
            </div>
        </section>

        <div class="sticky bottom-4 flex justify-end">
            <button type="submit" class="btn-primary shadow-lg" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">Save settings</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
        </div>
    </form>
</div>

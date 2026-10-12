@php
    $studio = $form->studio;
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

        {{-- Authorized E-Signature --}}
        <section class="card space-y-5 p-5 sm:p-7"
                 x-data="{
                     openPad: false,
                     mode: 'draw',
                     typedName: '{{ $form->signatureName ?: $studio->name }}',
                     hasInk: false,
                     drawing: false,
                     context: null,
                     ratio: window.devicePixelRatio || 1,
                     init() {
                         this.$watch('openPad', val => {
                             if (val) {
                                 this.$nextTick(() => {
                                     this.resizeCanvas();
                                     setTimeout(() => this.resizeCanvas(), 60);
                                 });
                             }
                         });
                         window.addEventListener('resize', () => {
                             if (this.openPad && this.mode === 'draw' && !this.hasInk) {
                                 this.resizeCanvas();
                             }
                         });
                     },
                     resizeCanvas() {
                         const canvas = this.$refs.sigCanvas;
                         if (!canvas) return;
                         if (!this.context) {
                             this.context = canvas.getContext('2d');
                         }
                         const box = canvas.getBoundingClientRect();
                         const width = Math.floor(box.width) || 420;
                         const height = 140;
                         canvas.width = width * this.ratio;
                         canvas.height = height * this.ratio;
                         this.context.setTransform(this.ratio, 0, 0, this.ratio, 0, 0);
                         this.context.lineWidth = 2.4;
                         this.context.lineCap = 'round';
                         this.context.lineJoin = 'round';
                         this.context.strokeStyle = '#111827';
                         this.hasInk = false;
                     },
                     getPos(e) {
                         const box = this.$refs.sigCanvas.getBoundingClientRect();
                         return { x: e.clientX - box.left, y: e.clientY - box.top };
                     },
                     startDraw(e) {
                         this.drawing = true;
                         try { this.$refs.sigCanvas.setPointerCapture(e.pointerId); } catch (_) {}
                         const pos = this.getPos(e);
                         this.context.beginPath();
                         this.context.moveTo(pos.x, pos.y);
                         this.context.lineTo(pos.x + 0.1, pos.y + 0.1);
                         this.context.stroke();
                         this.hasInk = true;
                     },
                     moveDraw(e) {
                         if (!this.drawing) return;
                         const pos = this.getPos(e);
                         this.context.lineTo(pos.x, pos.y);
                         this.context.stroke();
                     },
                     endDraw(e) {
                         this.drawing = false;
                         if (e && e.pointerId) {
                             try { this.$refs.sigCanvas.releasePointerCapture(e.pointerId); } catch (_) {}
                         }
                     },
                     clearPad() {
                         if (this.context && this.$refs.sigCanvas) {
                             this.context.clearRect(0, 0, this.$refs.sigCanvas.width, this.$refs.sigCanvas.height);
                             this.hasInk = false;
                         }
                         $wire.set('form.signatureData', null);
                     },
                     adoptDrawn() {
                         if (this.hasInk && this.$refs.sigCanvas) {
                             const data = this.$refs.sigCanvas.toDataURL('image/png');
                             $wire.set('form.signatureData', data);
                             $wire.set('form.removeAuthorizedSignature', false);
                         }
                     },
                     generateTypedPng() {
                         const name = (this.typedName || $wire.form.signatureName || '{{ $studio->name }}').trim();
                         const off = document.createElement('canvas');
                         const ctx = off.getContext('2d');
                         ctx.font = '700 48px \'Dancing Script\', \'Brush Script MT\', cursive';
                         const textMetrics = ctx.measureText(name);
                         const width = Math.max(260, Math.ceil(textMetrics.width) + 60);
                         const height = 120;
                         off.width = width;
                         off.height = height;
                         ctx.clearRect(0, 0, width, height);
                         ctx.fillStyle = '#111827';
                         ctx.font = '700 48px \'Dancing Script\', \'Brush Script MT\', cursive';
                         ctx.textBaseline = 'middle';
                         ctx.fillText(name, 30, height / 2);
                         const data = off.toDataURL('image/png');
                         $wire.set('form.signatureData', data);
                         $wire.set('form.removeAuthorizedSignature', false);
                     }
                 }"
                 x-on:saved.window="openPad = false; clearPad()">
            <div class="border-b border-line pb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-ink">Authorized e-signature</h2>
                    <p class="mt-0.5 text-sm text-muted">E-sign directly using your finger or mouse to stamp quotations automatically.</p>
                </div>
                @if ($studio->authorizedSignatureUrl() && ! $form->removeAuthorizedSignature)
                    <button type="button"
                            wire:click="$set('form.removeAuthorizedSignature', true); $set('form.signatureData', null)"
                            class="btn-danger !py-1.5 !px-3 text-xs self-start sm:self-auto">
                        Remove signature
                    </button>
                @elseif ($form->removeAuthorizedSignature)
                    <button type="button"
                            wire:click="$set('form.removeAuthorizedSignature', false)"
                            class="btn-secondary !py-1.5 !px-3 text-xs self-start sm:self-auto">
                        Undo removal
                    </button>
                @endif
            </div>

            {{-- Current signature preview or empty prompt --}}
            @if ($form->signatureData)
                <div class="rounded-xl border border-line bg-canvas/40 p-4">
                    <p class="text-xs font-medium text-muted uppercase tracking-wider mb-2">New signature preview (click Save settings to apply)</p>
                    <div class="h-20 w-52 sm:w-64 max-w-full rounded-lg border border-line bg-white flex items-center justify-center p-3 shadow-xs">
                        <img src="{{ $form->signatureData }}" alt="New signature preview" class="max-h-full max-w-full object-contain">
                    </div>
                    <div class="mt-3 flex items-center gap-2">
                        <button type="button" x-on:click="openPad = true; $nextTick(() => { resizeCanvas(); setTimeout(() => resizeCanvas(), 60); })" class="text-xs font-semibold text-brand-600 hover:text-brand-700">Re-sign</button>
                        <span class="text-xs text-muted">·</span>
                        <button type="button" wire:click="$set('form.signatureData', null)" class="text-xs font-semibold text-red-600 hover:text-red-700">Discard changes</button>
                    </div>
                </div>
            @elseif ($studio->authorizedSignatureUrl() && ! $form->removeAuthorizedSignature)
                <div class="rounded-xl border border-line bg-canvas/40 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-medium text-muted uppercase tracking-wider mb-2">Saved authorized signature</p>
                            <div class="h-20 w-52 sm:w-64 max-w-full rounded-lg border border-line bg-white flex items-center justify-center p-3 shadow-xs">
                                <img src="{{ $studio->authorizedSignatureUrl() }}" alt="Authorized signature" class="max-h-full max-w-full object-contain">
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-2">
                        <button type="button" x-on:click="openPad = true; $nextTick(() => { resizeCanvas(); setTimeout(() => resizeCanvas(), 60); })" class="btn-secondary !py-1.5 !px-3.5 text-xs">
                            ✍ Update e-signature
                        </button>
                    </div>
                </div>
            @elseif ($form->removeAuthorizedSignature)
                <div class="rounded-xl border border-red-200 bg-red-50/50 p-4 text-sm text-red-700">
                    Signature marked for removal. It will be removed once you save settings.
                </div>
            @else
                <div class="rounded-xl border-2 border-dashed border-line bg-canvas/40 p-6 text-center">
                    <div class="mx-auto size-12 rounded-full bg-surface border border-line flex items-center justify-center text-muted text-xl mb-3">
                        ✍
                    </div>
                    <p class="text-sm font-semibold text-ink">No authorized signature yet</p>
                    <p class="mt-1 text-xs text-muted max-w-md mx-auto">Create an e-signature by drawing on screen or generating a handwritten script signature.</p>
                    <div class="mt-4">
                        <button type="button" x-on:click="openPad = true; $nextTick(() => { resizeCanvas(); setTimeout(() => resizeCanvas(), 60); })" class="btn-primary !py-2 !px-4 text-xs sm:text-sm">
                            ✍ Create e-signature
                        </button>
                    </div>
                </div>
            @endif

            {{-- E-Signature Draw / Type Drawer --}}
            <div x-show="openPad" x-cloak class="rounded-2xl border-2 border-brand-500/40 bg-surface p-4 sm:p-5 space-y-4 shadow-sm">
                <div class="flex items-center justify-between gap-2 flex-wrap sm:flex-nowrap">
                    <div class="inline-flex rounded-lg border border-line p-1 bg-canvas shrink-0">
                        <button type="button"
                                x-on:click="mode = 'draw'; $nextTick(() => { resizeCanvas(); setTimeout(() => resizeCanvas(), 60); })"
                                x-bind:class="{ 'bg-surface shadow-xs font-semibold text-ink': mode === 'draw', 'text-muted': mode !== 'draw' }"
                                class="px-3 py-1 text-xs rounded-md transition font-medium">
                            Draw signature
                        </button>
                        <button type="button"
                                x-on:click="mode = 'type'"
                                x-bind:class="{ 'bg-surface shadow-xs font-semibold text-ink': mode === 'type', 'text-muted': mode !== 'type' }"
                                class="px-3 py-1 text-xs rounded-md transition font-medium">
                            Type name script
                        </button>
                    </div>

                    <button type="button" x-on:click="openPad = false" class="text-xs font-medium text-muted hover:text-ink shrink-0">
                        ✕ Cancel
                    </button>
                </div>

                {{-- Draw Mode --}}
                <div x-show="mode === 'draw'" class="space-y-3">
                    <div class="relative rounded-xl border border-line bg-white overflow-hidden">
                        <canvas x-ref="sigCanvas"
                                x-on:pointerdown="startDraw($event)"
                                x-on:pointermove="moveDraw($event)"
                                x-on:pointerup="endDraw($event)"
                                x-on:pointercancel="endDraw($event)"
                                x-on:pointerleave="endDraw($event)"
                                style="touch-action: none;"
                                class="w-full h-36 cursor-crosshair block bg-white"></canvas>
                        <div class="absolute bottom-2 left-3 pointer-events-none text-[11px] text-zinc-400">
                            Sign above with finger or mouse
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <button type="button" x-on:click="clearPad()" class="text-xs text-muted hover:text-ink font-medium shrink-0">
                            Clear canvas
                        </button>

                        <button type="button"
                                x-on:click="adoptDrawn(); openPad = false"
                                x-bind:disabled="!hasInk"
                                class="btn-primary !py-1.5 !px-3.5 text-xs disabled:opacity-40 shrink-0">
                            Use drawn signature
                        </button>
                    </div>
                </div>

                {{-- Type Mode --}}
                <div x-show="mode === 'type'" class="space-y-4">
                    <div>
                        <label for="typed-sig-input" class="label">Enter name for signature</label>
                        <input id="typed-sig-input"
                                type="text"
                                x-model="typedName"
                                placeholder="e.g. {{ $form->signatureName ?: $studio->name }}"
                                class="input">
                        <p class="mt-1 text-xs text-muted">Type the name as you want it styled in the signature.</p>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-muted uppercase tracking-wider mb-1.5">Signature preview</p>
                        <div class="p-4 sm:p-6 rounded-xl border border-line bg-white flex items-center justify-center min-h-[100px] shadow-xs overflow-hidden">
                            <span class="text-center break-words max-w-full" style="font-family: 'Dancing Script', 'Brush Script MT', cursive; font-size: clamp(24px, 6vw, 42px); font-weight: 700; line-height: 1.1; color: #111827; letter-spacing: 0.5px;">
                                <span x-text="typedName.trim() || '{{ $form->signatureName ?: $studio->name }}'"></span>
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5 pt-1">
                        <p class="text-xs text-muted">Stamps an elegant handwritten e-signature on quotations.</p>
                        <button type="button"
                                x-on:click="generateTypedPng(); openPad = false"
                                class="btn-primary !py-2 !px-4 text-xs font-semibold shrink-0 self-end sm:self-auto">
                            Use this signature
                        </button>
                    </div>
                </div>
            </div>
        </section>

        {{-- Images --}}
        <section class="card space-y-6 p-6 sm:p-7">
            <div class="border-b border-line pb-4">
                <h2 class="text-lg font-semibold text-ink">Logo and cover photos</h2>
                <p class="mt-0.5 text-sm text-muted">Customize your studio branding.</p>
            </div>

            {{-- Studio Logo --}}
            <div wire:key="image-logo-card"
                 x-data="{
                     previewUrl: null,
                     handleFileSelect(event) {
                         const file = event.target.files && event.target.files[0];
                         if (file && file.type.startsWith('image/')) {
                             if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
                             this.previewUrl = URL.createObjectURL(file);
                         }
                     },
                     clearSelection() {
                         if (this.previewUrl) {
                             URL.revokeObjectURL(this.previewUrl);
                             this.previewUrl = null;
                         }
                         const el = this.$el.querySelector('input[type=file]');
                         if (el) el.value = '';
                     }
                 }"
                 x-on:saved.window="clearSelection()"
                 class="rounded-xl border border-line bg-canvas/40 p-4 sm:p-5">
                <input id="settings-logo" type="file" wire:model="form.logo" accept="image/png,image/jpeg,image/webp" x-on:change="handleFileSelect($event)" class="sr-only">

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-6">
                    {{-- Logo Avatar / Preview Box (1:1 Aspect Ratio) --}}
                    <div class="relative group size-20 sm:size-24 shrink-0">
                        <label for="settings-logo"
                            x-bind:class="{
                                'border border-line shadow-xs': previewUrl || {{ ($form->logo?->isPreviewable() || (! $form->removeLogo && $studio->logoUrl())) ? 'true' : 'false' }},
                                'border-2 border-dashed border-line': !previewUrl && {{ (! $form->logo?->isPreviewable() && ($form->removeLogo || ! $studio->logoUrl())) ? 'true' : 'false' }}
                            }"
                            @class([
                                'relative flex size-full cursor-pointer items-center justify-center overflow-hidden rounded-2xl bg-surface transition hover:border-brand-500 hover:bg-brand-50/30 focus-within:ring-2 focus-within:ring-brand-500',
                            ])>

                            {{-- Client-side Instant Preview (takes priority for immediate 0ms display on local & live) --}}
                            <template x-if="previewUrl">
                                <div class="size-full relative">
                                    <img :src="previewUrl" alt="Selected logo preview" class="size-full object-cover">
                                    <div class="absolute inset-0 flex flex-col items-center justify-center bg-black/50 text-white opacity-0 transition group-hover:opacity-100">
                                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                                        </svg>
                                        <span class="text-[10px] font-semibold mt-0.5">Change</span>
                                    </div>
                                </div>
                            </template>

                            {{-- Server-rendered Fallback --}}
                            <div x-show="!previewUrl" class="size-full flex items-center justify-center">
                                @if ($form->logo?->isPreviewable())
                                    <img src="{{ $form->logo->temporaryUrl() }}" alt="New logo preview" class="size-full object-cover" onerror="this.style.display='none'">
                                    <div class="absolute inset-0 flex flex-col items-center justify-center bg-black/50 text-white opacity-0 transition group-hover:opacity-100">
                                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                                        </svg>
                                        <span class="text-[10px] font-semibold mt-0.5">Change</span>
                                    </div>
                                @elseif (! $form->removeLogo && $studio->logoUrl())
                                    <img src="{{ $studio->logoUrl() }}" alt="{{ $studio->name }} logo" class="size-full object-cover">
                                    <div class="absolute inset-0 flex flex-col items-center justify-center bg-black/50 text-white opacity-0 transition group-hover:opacity-100">
                                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                                        </svg>
                                        <span class="text-[10px] font-semibold mt-0.5">Change</span>
                                    </div>
                                @elseif ($form->removeLogo)
                                    <div class="flex flex-col items-center justify-center text-red-500/80 p-2 text-center">
                                        <svg class="size-6 stroke-[1.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                        <span class="text-[10px] font-semibold mt-1">Removed</span>
                                    </div>
                                @else
                                    <div class="flex flex-col items-center justify-center text-muted group-hover:text-brand-600 transition p-2 text-center">
                                        <svg class="size-6 stroke-[1.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                        </svg>
                                        <span class="text-[10px] font-semibold mt-1">Add logo</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Loading spinner overlay --}}
                            <div wire:loading.flex wire:target="form.logo" class="absolute inset-0 flex flex-col items-center justify-center bg-surface/90 backdrop-blur-xs rounded-2xl z-10">
                                <svg class="size-5 animate-spin text-brand-600" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </label>
                    </div>

                    {{-- Logo Details & Actions --}}
                    <div class="flex-1 min-w-0 space-y-2">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-sm font-semibold text-ink">Studio logo</h3>
                        </div>
                        <p class="text-xs text-muted">Square PNG, JPG, or WEBP up to 2 MB. Displayed on quotation headers, navigation bar, and emails.</p>

                        <div class="flex flex-wrap items-center gap-2 pt-1">
                            {{-- When a new image is selected (client preview active or server temporary file ready) --}}
                            <div x-show="previewUrl || {{ $form->logo?->isPreviewable() ? 'true' : 'false' }}" class="flex flex-wrap items-center gap-2">
                                <label for="settings-logo" class="btn-secondary cursor-pointer !py-1.5 !px-3.5 text-xs sm:text-sm">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 16.5v-2.25m-14.25-5.25L12 3m0 0 5.25 5.25M12 3v12" />
                                    </svg>
                                    <span>Change logo</span>
                                </label>
                                <button type="button" x-on:click="clearSelection()" wire:click="clearSelectedImage('logo')" class="btn-secondary !py-1.5 !px-3.5 text-xs sm:text-sm text-red-600 hover:text-red-700">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                    <span>Clear selection</span>
                                </button>
                            </div>

                            {{-- When no new image is selected --}}
                            <div x-show="!previewUrl && {{ ! $form->logo?->isPreviewable() ? 'true' : 'false' }}" class="flex flex-wrap items-center gap-2">
                                @if ($form->removeLogo)
                                    <button type="button" wire:click="undoImageRemoval('logo')" class="btn-secondary !py-1.5 !px-3.5 text-xs sm:text-sm">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                                        </svg>
                                        <span>Undo removal</span>
                                    </button>
                                    <label for="settings-logo" class="btn-secondary cursor-pointer !py-1.5 !px-3.5 text-xs sm:text-sm">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 16.5v-2.25m-14.25-5.25L12 3m0 0 5.25 5.25M12 3v12" />
                                        </svg>
                                        <span>Upload new logo</span>
                                    </label>
                                @elseif ($studio->logoUrl())
                                    <label for="settings-logo" class="btn-secondary cursor-pointer !py-1.5 !px-3.5 text-xs sm:text-sm">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 16.5v-2.25m-14.25-5.25L12 3m0 0 5.25 5.25M12 3v12" />
                                        </svg>
                                        <span>Change logo</span>
                                    </label>
                                    <button type="button" wire:click="markImageForRemoval('logo')" class="btn-danger !py-1.5 !px-3.5 text-xs sm:text-sm">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                        <span>Remove</span>
                                    </button>
                                @else
                                    <label for="settings-logo" class="btn-secondary cursor-pointer !py-1.5 !px-3.5 text-xs sm:text-sm">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 16.5v-2.25m-14.25-5.25L12 3m0 0 5.25 5.25M12 3v12" />
                                        </svg>
                                        <span>Upload logo</span>
                                    </label>
                                @endif
                            </div>

                            <div wire:loading wire:target="form.logo" class="inline-flex items-center gap-1.5 text-xs font-medium text-brand-600">
                                <svg class="size-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Processing…</span>
                            </div>
                        </div>

                        @error('form.logo')
                            <p class="input-error text-xs">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Cover Photos Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Wedding Cover Photo --}}
                <div wire:key="image-coverWedding-card"
                     x-data="{
                         previewUrl: null,
                         handleFileSelect(event) {
                             const file = event.target.files && event.target.files[0];
                             if (file && file.type.startsWith('image/')) {
                                 if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
                                 this.previewUrl = URL.createObjectURL(file);
                             }
                         },
                         clearSelection() {
                             if (this.previewUrl) {
                                 URL.revokeObjectURL(this.previewUrl);
                                 this.previewUrl = null;
                             }
                             const el = this.$el.querySelector('input[type=file]');
                             if (el) el.value = '';
                         }
                     }"
                     x-on:saved.window="clearSelection()"
                     class="flex flex-col justify-between rounded-xl border border-line bg-canvas/40 p-4 sm:p-5 transition hover:border-line/80">
                    <input id="settings-coverWedding" type="file" wire:model="form.coverWedding" accept="image/png,image/jpeg,image/webp" x-on:change="handleFileSelect($event)" class="sr-only">

                    <div class="space-y-3">
                        <div>
                            <h3 class="text-sm font-semibold text-ink">Wedding cover photo</h3>
                            <p class="text-xs text-muted mt-0.5">Shown at the top of wedding quotations. Landscape, up to 5 MB.</p>
                        </div>

                        {{-- Banner Preview (16:9 Aspect Ratio) --}}
                        <div x-bind:class="{
                                'border border-line shadow-xs': previewUrl || {{ ($form->coverWedding?->isPreviewable() || (! $form->removeCoverWedding && $studio->coverUrl('wedding'))) ? 'true' : 'false' }},
                                'border-2 border-dashed border-line': !previewUrl && {{ (! $form->coverWedding?->isPreviewable() && ($form->removeCoverWedding || ! $studio->coverUrl('wedding'))) ? 'true' : 'false' }}
                            }"
                            @class([
                                'relative group aspect-video w-full overflow-hidden rounded-xl bg-surface transition',
                            ])>
                            <label for="settings-coverWedding" class="relative flex size-full cursor-pointer items-center justify-center transition focus-within:ring-2 focus-within:ring-brand-500">
                                {{-- Client-side Preview --}}
                                <template x-if="previewUrl">
                                    <div class="size-full relative">
                                        <img :src="previewUrl" alt="Selected wedding cover preview" class="size-full object-cover">
                                        <div class="absolute inset-0 flex flex-col items-center justify-center bg-black/45 text-white opacity-0 transition group-hover:opacity-100">
                                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                                            </svg>
                                            <span class="text-xs font-medium mt-1">Change photo</span>
                                        </div>
                                    </div>
                                </template>

                                {{-- Server-rendered Fallback --}}
                                <div x-show="!previewUrl" class="size-full flex items-center justify-center">
                                    @if ($form->coverWedding?->isPreviewable())
                                        <img src="{{ $form->coverWedding->temporaryUrl() }}" alt="Wedding cover preview" class="size-full object-cover" onerror="this.style.display='none'">
                                        <div class="absolute inset-0 flex flex-col items-center justify-center bg-black/45 text-white opacity-0 transition group-hover:opacity-100">
                                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                                            </svg>
                                            <span class="text-xs font-medium mt-1">Change photo</span>
                                        </div>
                                    @elseif (! $form->removeCoverWedding && $studio->coverUrl('wedding'))
                                        <img src="{{ $studio->coverUrl('wedding') }}" alt="Wedding cover" class="size-full object-cover">
                                        <div class="absolute inset-0 flex flex-col items-center justify-center bg-black/45 text-white opacity-0 transition group-hover:opacity-100">
                                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                                            </svg>
                                            <span class="text-xs font-medium mt-1">Change photo</span>
                                        </div>
                                    @elseif ($form->removeCoverWedding)
                                        <div class="flex flex-col items-center justify-center text-red-500/80 p-4 text-center">
                                            <svg class="size-8 stroke-[1.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                            <span class="text-xs font-semibold mt-1.5">Marked for removal</span>
                                            <span class="text-[11px] text-muted">Will be removed when settings are saved</span>
                                        </div>
                                    @else
                                        <div class="flex flex-col items-center justify-center text-muted group-hover:text-brand-600 transition p-4 text-center">
                                            <svg class="size-8 stroke-[1.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                            </svg>
                                            <span class="text-xs font-semibold mt-1.5">Add wedding cover</span>
                                            <span class="text-[11px] text-muted">16:9 landscape format</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Loading Overlay --}}
                                <div wire:loading.flex wire:target="form.coverWedding" class="absolute inset-0 flex flex-col items-center justify-center bg-surface/90 backdrop-blur-xs z-10">
                                    <svg class="size-6 animate-spin text-brand-600" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-xs font-semibold text-brand-600 mt-2">Processing…</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="mt-4 pt-3 border-t border-line/60 space-y-2">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="flex flex-wrap items-center gap-2">
                                {{-- When new file is selected (client or server) --}}
                                <div x-show="previewUrl || {{ $form->coverWedding?->isPreviewable() ? 'true' : 'false' }}" class="flex flex-wrap items-center gap-2">
                                    <label for="settings-coverWedding" class="btn-secondary cursor-pointer !py-1.5 !px-3 text-xs">
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 16.5v-2.25m-14.25-5.25L12 3m0 0 5.25 5.25M12 3v12" />
                                        </svg>
                                        <span>Change photo</span>
                                    </label>
                                    <button type="button" x-on:click="clearSelection()" wire:click="clearSelectedImage('coverWedding')" class="btn-secondary !py-1.5 !px-3 text-xs text-red-600 hover:text-red-700">
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                        <span>Clear selection</span>
                                    </button>
                                </div>

                                {{-- When no new file selected --}}
                                <div x-show="!previewUrl && {{ ! $form->coverWedding?->isPreviewable() ? 'true' : 'false' }}" class="flex flex-wrap items-center gap-2">
                                    @if ($form->removeCoverWedding)
                                        <button type="button" wire:click="undoImageRemoval('coverWedding')" class="btn-secondary !py-1.5 !px-3 text-xs">
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                                            </svg>
                                            <span>Undo removal</span>
                                        </button>
                                        <label for="settings-coverWedding" class="btn-secondary cursor-pointer !py-1.5 !px-3 text-xs">
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 16.5v-2.25m-14.25-5.25L12 3m0 0 5.25 5.25M12 3v12" />
                                            </svg>
                                            <span>Upload new</span>
                                        </label>
                                    @elseif ($studio->coverUrl('wedding'))
                                        <label for="settings-coverWedding" class="btn-secondary cursor-pointer !py-1.5 !px-3 text-xs">
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 16.5v-2.25m-14.25-5.25L12 3m0 0 5.25 5.25M12 3v12" />
                                            </svg>
                                            <span>Change photo</span>
                                        </label>
                                        <button type="button" wire:click="markImageForRemoval('coverWedding')" class="btn-danger !py-1.5 !px-3 text-xs">
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                            <span>Remove</span>
                                        </button>
                                    @else
                                        <label for="settings-coverWedding" class="btn-secondary cursor-pointer !py-1.5 !px-3 text-xs">
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 16.5v-2.25m-14.25-5.25L12 3m0 0 5.25 5.25M12 3v12" />
                                            </svg>
                                            <span>Upload photo</span>
                                        </label>
                                    @endif
                                </div>
                            </div>

                            <div wire:loading wire:target="form.coverWedding" class="text-xs text-brand-600 font-medium">
                                Processing…
                            </div>
                        </div>

                        @error('form.coverWedding')
                            <p class="input-error text-xs">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Corporate Cover Photo --}}
                <div wire:key="image-coverCorporate-card"
                     x-data="{
                         previewUrl: null,
                         handleFileSelect(event) {
                             const file = event.target.files && event.target.files[0];
                             if (file && file.type.startsWith('image/')) {
                                 if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
                                 this.previewUrl = URL.createObjectURL(file);
                             }
                         },
                         clearSelection() {
                             if (this.previewUrl) {
                                 URL.revokeObjectURL(this.previewUrl);
                                 this.previewUrl = null;
                             }
                             const el = this.$el.querySelector('input[type=file]');
                             if (el) el.value = '';
                         }
                     }"
                     x-on:saved.window="clearSelection()"
                     class="flex flex-col justify-between rounded-xl border border-line bg-canvas/40 p-4 sm:p-5 transition hover:border-line/80">
                    <input id="settings-coverCorporate" type="file" wire:model="form.coverCorporate" accept="image/png,image/jpeg,image/webp" x-on:change="handleFileSelect($event)" class="sr-only">

                    <div class="space-y-3">
                        <div>
                            <h3 class="text-sm font-semibold text-ink">Corporate cover photo</h3>
                            <p class="text-xs text-muted mt-0.5">Shown at the top of corporate quotations. Landscape, up to 5 MB.</p>
                        </div>

                        {{-- Banner Preview (16:9 Aspect Ratio) --}}
                        <div x-bind:class="{
                                'border border-line shadow-xs': previewUrl || {{ ($form->coverCorporate?->isPreviewable() || (! $form->removeCoverCorporate && $studio->coverUrl('corporate'))) ? 'true' : 'false' }},
                                'border-2 border-dashed border-line': !previewUrl && {{ (! $form->coverCorporate?->isPreviewable() && ($form->removeCoverCorporate || ! $studio->coverUrl('corporate'))) ? 'true' : 'false' }}
                            }"
                            @class([
                                'relative group aspect-video w-full overflow-hidden rounded-xl bg-surface transition',
                            ])>
                            <label for="settings-coverCorporate" class="relative flex size-full cursor-pointer items-center justify-center transition focus-within:ring-2 focus-within:ring-brand-500">
                                {{-- Client-side Preview --}}
                                <template x-if="previewUrl">
                                    <div class="size-full relative">
                                        <img :src="previewUrl" alt="Selected corporate cover preview" class="size-full object-cover">
                                        <div class="absolute inset-0 flex flex-col items-center justify-center bg-black/45 text-white opacity-0 transition group-hover:opacity-100">
                                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                                            </svg>
                                            <span class="text-xs font-medium mt-1">Change photo</span>
                                        </div>
                                    </div>
                                </template>

                                {{-- Server-rendered Fallback --}}
                                <div x-show="!previewUrl" class="size-full flex items-center justify-center">
                                    @if ($form->coverCorporate?->isPreviewable())
                                        <img src="{{ $form->coverCorporate->temporaryUrl() }}" alt="Corporate cover preview" class="size-full object-cover" onerror="this.style.display='none'">
                                        <div class="absolute inset-0 flex flex-col items-center justify-center bg-black/45 text-white opacity-0 transition group-hover:opacity-100">
                                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                                            </svg>
                                            <span class="text-xs font-medium mt-1">Change photo</span>
                                        </div>
                                    @elseif (! $form->removeCoverCorporate && $studio->coverUrl('corporate'))
                                        <img src="{{ $studio->coverUrl('corporate') }}" alt="Corporate cover" class="size-full object-cover">
                                        <div class="absolute inset-0 flex flex-col items-center justify-center bg-black/45 text-white opacity-0 transition group-hover:opacity-100">
                                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                                            </svg>
                                            <span class="text-xs font-medium mt-1">Change photo</span>
                                        </div>
                                    @elseif ($form->removeCoverCorporate)
                                        <div class="flex flex-col items-center justify-center text-red-500/80 p-4 text-center">
                                            <svg class="size-8 stroke-[1.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                            <span class="text-xs font-semibold mt-1.5">Marked for removal</span>
                                            <span class="text-[11px] text-muted">Will be removed when settings are saved</span>
                                        </div>
                                    @else
                                        <div class="flex flex-col items-center justify-center text-muted group-hover:text-brand-600 transition p-4 text-center">
                                            <svg class="size-8 stroke-[1.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                            </svg>
                                            <span class="text-xs font-semibold mt-1.5">Add corporate cover</span>
                                            <span class="text-[11px] text-muted">16:9 landscape format</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Loading Overlay --}}
                                <div wire:loading.flex wire:target="form.coverCorporate" class="absolute inset-0 flex flex-col items-center justify-center bg-surface/90 backdrop-blur-xs z-10">
                                    <svg class="size-6 animate-spin text-brand-600" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-xs font-semibold text-brand-600 mt-2">Processing…</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="mt-4 pt-3 border-t border-line/60 space-y-2">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="flex flex-wrap items-center gap-2">
                                {{-- When new file is selected (client or server) --}}
                                <div x-show="previewUrl || {{ $form->coverCorporate?->isPreviewable() ? 'true' : 'false' }}" class="flex flex-wrap items-center gap-2">
                                    <label for="settings-coverCorporate" class="btn-secondary cursor-pointer !py-1.5 !px-3 text-xs">
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 16.5v-2.25m-14.25-5.25L12 3m0 0 5.25 5.25M12 3v12" />
                                        </svg>
                                        <span>Change photo</span>
                                    </label>
                                    <button type="button" x-on:click="clearSelection()" wire:click="clearSelectedImage('coverCorporate')" class="btn-secondary !py-1.5 !px-3 text-xs text-red-600 hover:text-red-700">
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                        <span>Clear selection</span>
                                    </button>
                                </div>

                                {{-- When no new file selected --}}
                                <div x-show="!previewUrl && {{ ! $form->coverCorporate?->isPreviewable() ? 'true' : 'false' }}" class="flex flex-wrap items-center gap-2">
                                    @if ($form->removeCoverCorporate)
                                        <button type="button" wire:click="undoImageRemoval('coverCorporate')" class="btn-secondary !py-1.5 !px-3 text-xs">
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                                            </svg>
                                            <span>Undo removal</span>
                                        </button>
                                        <label for="settings-coverCorporate" class="btn-secondary cursor-pointer !py-1.5 !px-3 text-xs">
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 16.5v-2.25m-14.25-5.25L12 3m0 0 5.25 5.25M12 3v12" />
                                            </svg>
                                            <span>Upload new</span>
                                        </label>
                                    @elseif ($studio->coverUrl('corporate'))
                                        <label for="settings-coverCorporate" class="btn-secondary cursor-pointer !py-1.5 !px-3 text-xs">
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 16.5v-2.25m-14.25-5.25L12 3m0 0 5.25 5.25M12 3v12" />
                                            </svg>
                                            <span>Change photo</span>
                                        </label>
                                        <button type="button" wire:click="markImageForRemoval('coverCorporate')" class="btn-danger !py-1.5 !px-3 text-xs">
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                            <span>Remove</span>
                                        </button>
                                    @else
                                        <label for="settings-coverCorporate" class="btn-secondary cursor-pointer !py-1.5 !px-3 text-xs">
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 16.5v-2.25m-14.25-5.25L12 3m0 0 5.25 5.25M12 3v12" />
                                            </svg>
                                            <span>Upload photo</span>
                                        </label>
                                    @endif
                                </div>
                            </div>

                            <div wire:loading wire:target="form.coverCorporate" class="text-xs text-brand-600 font-medium">
                                Processing…
                            </div>
                        </div>

                        @error('form.coverCorporate')
                            <p class="input-error text-xs">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
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
                <div class="flex items-center gap-3 flex-wrap">
                    <input type="color" aria-label="Pick accent colour" value="{{ $form->accentColor ?: '#0F4C4A' }}" wire:model.live="form.accentColor" class="h-10 w-14 cursor-pointer rounded-lg border border-line bg-surface p-1 shrink-0">
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

        <div class="sticky bottom-4 z-20 flex justify-end">
            <button type="submit" class="btn-primary shadow-lg w-full sm:w-auto" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">Save settings</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
        </div>
    </form>
</div>

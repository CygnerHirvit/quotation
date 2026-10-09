<div class="w-full max-w-sm">
    <div class="mb-8 text-center">
        <span class="mx-auto mb-4 flex size-12 items-center justify-center rounded-full bg-brand-500 text-lg font-bold text-white">Q</span>
        <h1 class="text-2xl font-semibold">Sign in</h1>
        <p class="mt-1 text-sm text-muted">Use the login your studio admin gave you.</p>
    </div>

    <form wire:submit="login" class="space-y-5 rounded-2xl border border-line bg-surface p-6 shadow-sm">
        <div>
            <label for="email" class="mb-1.5 block text-sm font-medium">Email</label>
            <input
                id="email"
                type="email"
                wire:model="email"
                autocomplete="email"
                autofocus
                required
                class="w-full rounded-lg border border-line px-3 py-2.5 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
            >
            @error('email')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="mb-1.5 block text-sm font-medium">Password</label>
            <input
                id="password"
                type="password"
                wire:model="password"
                autocomplete="current-password"
                required
                class="w-full rounded-lg border border-line px-3 py-2.5 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
            >
            @error('password')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <label for="remember" class="flex items-center gap-2 text-sm">
            <input id="remember" type="checkbox" wire:model="remember" class="size-4 rounded accent-brand-500">
            Keep me signed in
        </label>

        <button
            type="submit"
            class="w-full rounded-lg bg-brand-500 px-4 py-2.5 font-semibold text-white transition hover:bg-brand-600 disabled:opacity-60"
            wire:loading.attr="disabled"
        >
            <span wire:loading.remove wire:target="login">Sign in</span>
            <span wire:loading wire:target="login">Signing in…</span>
        </button>
    </form>
</div>

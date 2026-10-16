@php
    $user = auth()->user();

    $navigation = $user->isSuperAdmin()
        ? [
            ['label' => 'Overview', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
            ['label' => 'Studios', 'route' => 'admin.studios.index', 'active' => 'admin.studios.*'],
        ]
        : [
            ['label' => 'Quotations', 'route' => 'studio.dashboard', 'active' => ['studio.dashboard', 'studio.quotations.*']],
            ...($user->isStudioAdmin() ? [
                ['label' => 'Price list', 'route' => 'studio.price-list', 'active' => 'studio.price-list'],
                ['label' => 'Portfolio', 'route' => 'studio.portfolio', 'active' => 'studio.portfolio'],
                ['label' => 'Custom sections', 'route' => 'studio.custom-sections', 'active' => ['studio.custom-sections', 'studio.scope-of-work']],
                ['label' => 'Terms', 'route' => 'studio.terms', 'active' => 'studio.terms'],
                ['label' => 'Team', 'route' => 'studio.team', 'active' => 'studio.team'],
                ['label' => 'Settings', 'route' => 'studio.settings', 'active' => 'studio.settings'],
            ] : []),
        ];

    $isImpersonating = session()->has(\App\Http\Controllers\Admin\ImpersonationController::SESSION_KEY);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body
        class="min-h-screen"
        x-data="{ mobileMenuOpen: false }"
        x-on:keydown.escape.window="mobileMenuOpen = false"
        x-effect="document.body.style.overflow = mobileMenuOpen ? 'hidden' : ''"
        x-on:resize.window="if (window.innerWidth >= 1024) mobileMenuOpen = false"
        x-on:livewire:navigated.window="mobileMenuOpen = false; document.body.style.overflow = ''"
    >
        @if ($isImpersonating)
            <div class="bg-amber-100 text-amber-900">
                <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-2 text-sm">
                    <span>You are signed in as <strong>{{ $user->name }}</strong> ({{ $user->studio?->name }}) for support.</span>
                    <form method="POST" action="{{ route('impersonate.stop') }}">
                        @csrf
                        <button type="submit" class="font-semibold underline">Return to super admin</button>
                    </form>
                </div>
            </div>
        @endif

        <header class="sticky top-0 z-30 border-b border-line bg-surface/90 backdrop-blur">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-3 px-4 py-3">
                <a href="{{ route($user->homeRoute()) }}" wire:navigate class="flex items-center gap-3 min-w-0">
                    @if ($user->studio?->logoUrl())
                        <img src="{{ $user->studio->logoUrl() }}" alt="" class="size-9 rounded-full object-cover shrink-0">
                    @else
                        <span class="flex size-9 items-center justify-center rounded-full bg-brand-500 text-sm font-bold text-white shrink-0">
                            {{ str($user->studio?->name ?? config('app.name'))->substr(0, 1)->upper() }}
                        </span>
                    @endif
                    <span class="leading-tight truncate">
                        <span class="block font-semibold truncate">{{ $user->studio?->name ?? config('app.name') }}</span>
                        <span class="block text-xs tracking-wide text-muted uppercase">{{ $user->role->label() }}</span>
                    </span>
                </a>

                {{-- Desktop Navigation --}}
                <nav class="hidden lg:flex items-center gap-1 text-sm">
                    @foreach ($navigation as $item)
                        <a
                            href="{{ route($item['route']) }}"
                            wire:navigate
                            @class([
                                'rounded-full px-3.5 py-1.5 font-medium transition whitespace-nowrap',
                                'bg-brand-500 text-white shadow-xs' => request()->routeIs($item['active']),
                                'text-muted hover:bg-brand-50 hover:text-ink' => ! request()->routeIs($item['active']),
                            ])
                        >{{ $item['label'] }}</a>
                    @endforeach
                </nav>

                {{-- Right Actions --}}
                <div class="flex items-center gap-2 sm:gap-3 text-sm shrink-0">
                    @if ($user->studio_id)
                        <livewire:studio.notification-bell />
                    @endif
                    <span class="hidden md:inline text-muted text-xs sm:text-sm font-medium">{{ $user->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="hidden sm:inline">
                        @csrf
                        <button type="submit" class="rounded-full border border-line px-3.5 py-1.5 font-medium hover:bg-canvas text-xs sm:text-sm transition">
                            Log out
                        </button>
                    </form>

                    {{-- Mobile Menu Hamburger Button --}}
                    <button
                        type="button"
                        class="lg:hidden inline-flex size-10 items-center justify-center rounded-full border border-line text-ink hover:bg-canvas focus:outline-none transition shrink-0"
                        x-on:click="mobileMenuOpen = true"
                        :aria-expanded="mobileMenuOpen"
                        aria-label="Open navigation menu"
                    >
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                </div>
            </div>
        </header>

        {{-- Mobile Left Slide-out Navigation Drawer --}}
        <div
            x-show="mobileMenuOpen"
            x-cloak
            class="fixed inset-0 z-50 lg:hidden"
            role="dialog"
            aria-modal="true"
            aria-label="Navigation menu"
        >
            {{-- Semi-transparent backdrop with blur --}}
            <div
                x-show="mobileMenuOpen"
                x-transition:enter="transition-opacity ease-linear duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-linear duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
                x-on:click="mobileMenuOpen = false"
                aria-hidden="true"
            ></div>

            {{-- Slide-out panel from left --}}
            <div
                x-show="mobileMenuOpen"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                class="fixed inset-y-0 left-0 z-10 flex w-full max-w-xs flex-col bg-surface shadow-2xl border-r border-line"
            >
                {{-- Drawer Header with Branding & Close Button --}}
                <div class="flex items-center justify-between gap-3 border-b border-line px-4 py-3.5 shrink-0 bg-surface">
                    <a href="{{ route($user->homeRoute()) }}" wire:navigate x-on:click="mobileMenuOpen = false" class="flex items-center gap-3 min-w-0">
                        @if ($user->studio?->logoUrl())
                            <img src="{{ $user->studio->logoUrl() }}" alt="" class="size-9 rounded-full object-cover shrink-0">
                        @else
                            <span class="flex size-9 items-center justify-center rounded-full bg-brand-500 text-sm font-bold text-white shrink-0">
                                {{ str($user->studio?->name ?? config('app.name'))->substr(0, 1)->upper() }}
                            </span>
                        @endif
                        <span class="leading-tight truncate">
                            <span class="block font-semibold truncate text-ink">{{ $user->studio?->name ?? config('app.name') }}</span>
                            <span class="block text-xs tracking-wide text-muted uppercase">{{ $user->role->label() }}</span>
                        </span>
                    </a>

                    <button
                        type="button"
                        class="inline-flex size-9 items-center justify-center rounded-full border border-line text-muted hover:text-ink hover:bg-canvas focus:outline-none transition shrink-0"
                        x-on:click="mobileMenuOpen = false"
                        aria-label="Close navigation menu"
                    >
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Drawer Body: Links & Impersonation Banner --}}
                <div class="flex-1 overflow-y-auto px-4 py-4 space-y-3">
                    @if ($isImpersonating)
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-900">
                            <p class="font-medium">Support impersonation active</p>
                            <p class="text-amber-800 mt-0.5">Signed in as {{ $user->name }}</p>
                            <form method="POST" action="{{ route('impersonate.stop') }}" class="mt-2">
                                @csrf
                                <button type="submit" class="font-semibold underline">Return to super admin</button>
                            </form>
                        </div>
                    @endif

                    <nav class="flex flex-col gap-1" aria-label="Mobile links">
                        @foreach ($navigation as $item)
                            <a
                                href="{{ route($item['route']) }}"
                                wire:navigate
                                x-on:click="mobileMenuOpen = false"
                                @class([
                                    'flex items-center justify-between rounded-xl px-3.5 py-2.5 text-sm font-semibold transition',
                                    'bg-brand-500 text-white shadow-xs' => request()->routeIs($item['active']),
                                    'text-ink hover:bg-brand-50 hover:text-brand-700' => ! request()->routeIs($item['active']),
                                ])
                            >
                                <span>{{ $item['label'] }}</span>
                                @if (request()->routeIs($item['active']))
                                    <svg class="size-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                @endif
                            </a>
                        @endforeach
                    </nav>
                </div>

                {{-- Drawer Footer: User Profile & Logout --}}
                <div class="border-t border-line bg-canvas/40 px-4 py-4 shrink-0 flex items-center justify-between gap-3 text-sm">
                    <div class="min-w-0 pr-2">
                        <span class="block font-semibold text-ink truncate">{{ $user->name }}</span>
                        <span class="block text-xs text-muted truncate">{{ $user->email }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                        @csrf
                        <button type="submit" class="btn-secondary !py-1.5 !px-3 text-xs font-semibold">
                            Log out
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <main class="mx-auto max-w-6xl px-4 py-6 sm:py-8">
            {{ $slot }}
        </main>

        @livewireScripts
    </body>
</html>

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
    <body class="min-h-screen">
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
            <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-4 py-3">
                <a href="{{ route($user->homeRoute()) }}" wire:navigate class="flex items-center gap-3">
                    @if ($user->studio?->logoUrl())
                        <img src="{{ $user->studio->logoUrl() }}" alt="" class="size-9 rounded-full object-cover">
                    @else
                        <span class="flex size-9 items-center justify-center rounded-full bg-brand-500 text-sm font-bold text-white">
                            {{ str($user->studio?->name ?? config('app.name'))->substr(0, 1)->upper() }}
                        </span>
                    @endif
                    <span class="leading-tight">
                        <span class="block font-semibold">{{ $user->studio?->name ?? config('app.name') }}</span>
                        <span class="block text-xs tracking-wide text-muted uppercase">{{ $user->role->label() }}</span>
                    </span>
                </a>

                <nav class="flex items-center gap-1 text-sm">
                    @foreach ($navigation as $item)
                        <a
                            href="{{ route($item['route']) }}"
                            wire:navigate
                            @class([
                                'rounded-full px-4 py-2 font-medium transition',
                                'bg-brand-500 text-white' => request()->routeIs($item['active']),
                                'text-muted hover:bg-brand-50 hover:text-ink' => ! request()->routeIs($item['active']),
                            ])
                        >{{ $item['label'] }}</a>
                    @endforeach
                </nav>

                <div class="flex items-center gap-3 text-sm">
                    @if ($user->studio_id)
                        <livewire:studio.notification-bell />
                    @endif
                    <span class="hidden text-muted sm:inline">{{ $user->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-full border border-line px-4 py-2 font-medium hover:bg-canvas">
                            Log out
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8">
            {{ $slot }}
        </main>

        @livewireScripts
    </body>
</html>

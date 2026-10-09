<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen">
        <main class="flex min-h-screen items-center justify-center px-4 py-12">
            {{ $slot }}
        </main>

        @livewireScripts
    </body>
</html>

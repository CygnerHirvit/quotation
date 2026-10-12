<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ isset($title) ? $title.' · ' : '' }}{{ config('app.name') }}</title>

<link rel="preconnect" href="https://fonts.bunny.net">
<link rel="stylesheet" href="https://fonts.bunny.net/css?family=dancing-script:600,700&display=swap">

@fonts
@vite(['resources/css/app.css', 'resources/js/app.js'])
@livewireStyles

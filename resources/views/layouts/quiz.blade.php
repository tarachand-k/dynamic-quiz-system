<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">

<nav class="bg-white border-b border-gray-200 sticky top-0 z-10">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between">
        <a href="{{ url('/') }}" class="font-bold text-indigo-600 text-lg tracking-tight">
            {{ config('app.name') }}
        </a>
    </div>
</nav>

<main class="max-w-4xl mx-auto px-4 sm:px-6 py-8">
    {{ $slot }}
</main>

@stack('scripts')
</body>
</html>

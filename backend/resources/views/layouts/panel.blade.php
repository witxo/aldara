<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo_hospedacheck.png') }}">
    <title>@yield('title', 'Panel') — {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>[x-cloak] { display: none !important; }</style>
    @livewireStyles
    @stack('head')
</head>
<body class="bg-gray-50 text-gray-900 antialiased">
    <div class="min-h-screen flex">
        @include('panels._sidebar')
        <div class="flex-1 flex flex-col">
            @include('panels._header')
            <main class="flex-1 p-6">
                @if (session('success'))
                    <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700">{{ session('error') }}</div>
                @endif
                @yield('content')
            </main>
            @include('panels._footer')
        </div>
    </div>
    @livewireScripts
    @stack('scripts')
    @vite(['resources/js/mrz-reader-entry.js'])
</body>
</html>

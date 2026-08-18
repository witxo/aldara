<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo_hospedacheck.png') }}">
    <title>@yield('title', 'Admin') — {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
    @stack('head')
</head>
<body class="bg-gray-900 text-gray-100 antialiased">
    <div class="min-h-screen flex">
        <aside class="w-64 bg-gray-800 border-r border-gray-700 hidden lg:flex flex-col">
            <div class="p-4 border-b border-gray-700">
                <img src="{{ asset('images/logo_hospedacheck.png') }}" alt="HospedaCheck" class="h-8 mb-2 brightness-200">
                <h1 class="text-xl font-bold text-blue-400">{{ config('app.name') }}</h1>
                <p class="text-xs text-gray-400 mt-1">Panel de administración</p>
            </div>
            <nav class="flex-1 p-4 space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-700 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-700 text-blue-400' : 'text-gray-300' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.tenants') }}" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-700 {{ request()->routeIs('admin.tenants*') ? 'bg-gray-700 text-blue-400' : 'text-gray-300' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Tenants
                </a>
                <a href="{{ route('admin.users') }}" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-700 {{ request()->routeIs('admin.users*') ? 'bg-gray-700 text-blue-400' : 'text-gray-300' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                    Usuarios
                </a>
                <a href="{{ route('admin.database') }}" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-700 {{ request()->routeIs('admin.database*') ? 'bg-gray-700 text-blue-400' : 'text-gray-300' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7c-2 0-3 1-3 3z"/></svg>
                    Base de datos
                </a>
            </nav>
            <div class="p-4 border-t border-gray-700">
                <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2 text-sm text-gray-400 hover:text-gray-200">
                    ← Volver al panel principal
                </a>
            </div>
        </aside>
        <div class="flex-1 flex flex-col">
            <header class="bg-gray-800 border-b border-gray-700 px-6 py-3 flex justify-between items-center">
                <h2 class="text-lg font-semibold">@yield('title', 'Admin')</h2>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-400">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-red-400 hover:text-red-300">Cerrar sesión</button>
                    </form>
                </div>
            </header>
            <main class="flex-1 p-6 bg-gray-900">
                @if (session('success'))
                    <div class="mb-4 p-4 bg-green-900 border-l-4 border-green-500 text-green-300 rounded">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="mb-4 p-4 bg-red-900 border-l-4 border-red-500 text-red-300 rounded">{{ session('error') }}</div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    @livewireScripts
    @stack('scripts')
</body>
</html>

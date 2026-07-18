<header class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
    <div>
        <h2 class="text-lg font-semibold text-gray-800">@yield('title', 'Panel')</h2>
    </div>
    <div class="flex items-center space-x-4">
        <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">Cerrar sesión</button>
        </form>
    </div>
</header>

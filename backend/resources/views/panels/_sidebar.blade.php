<aside class="w-64 bg-white border-r border-gray-200 hidden lg:flex flex-col">
    <div class="p-4 border-b border-gray-200">
        <img src="{{ asset('images/logo_aldara.png') }}" alt="Aldara" class="h-8 mb-2">
        <h1 class="text-xl font-bold text-blue-600">{{ config('app.name') }}</h1>
        @if($tenant = current_tenant())
            <p class="text-sm text-gray-500 mt-1">{{ $tenant->company_name }}</p>
        @endif
    </div>
    <nav class="flex-1 p-4 space-y-1">
        <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </a>
        <a href="{{ route('reservations.index') }}" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('reservations.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Reservas
        </a>
        <a href="{{ route('properties.index') }}" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('properties.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            Alojamientos
        </a>
        <a href="{{ route('guests.index') }}" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('guests.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Huéspedes
        </a>
        <a href="{{ route('checkins.index') }}" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('checkins.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Check-ins
        </a>
        <hr class="my-2 border-gray-200">
        <a href="{{ route('integrations.index') }}" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('integrations.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/></svg>
            Integraciones
        </a>
        <a href="{{ route('ses.index') }}" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('ses.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            SES Hospedajes
        </a>
        <hr class="my-2 border-gray-200">
        <a href="{{ route('billing.index') }}" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('billing.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            Facturación
        </a>
        <a href="{{ route('tenant-users.index') }}" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('tenant-users.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
            Usuarios
        </a>
        <a href="{{ route('settings.index') }}" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('settings.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Ajustes
        </a>
    </nav>
    <div class="p-4 border-t border-gray-200">
        <a href="{{ route('audit.index') }}" class="flex items-center px-3 py-2 text-sm text-gray-500 hover:text-gray-700">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Auditoría
        </a>
    </div>
</aside>

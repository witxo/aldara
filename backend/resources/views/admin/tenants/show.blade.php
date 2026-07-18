@extends('layouts.admin')
@section('title', $tenant->company_name)
@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-xl font-semibold">{{ $tenant->company_name }}</h3>
                    <p class="text-gray-400 mt-1">{{ $tenant->email }} {{ $tenant->tax_id ? '· CIF: '.$tenant->tax_id : '' }}</p>
                </div>
                <form method="POST" action="{{ route('admin.tenants.toggle', $tenant) }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-lg text-sm {{ $tenant->status === 'suspended' ? 'bg-green-600 text-white hover:bg-green-700' : 'bg-red-600 text-white hover:bg-red-700' }}">
                        {{ $tenant->status === 'suspended' ? 'Activar' : 'Suspender' }}
                    </button>
                </form>
            </div>
            <div class="mt-4 flex gap-4">
                <span class="px-3 py-1 text-sm rounded {{ $tenant->status === 'active' ? 'bg-green-900 text-green-300' : ($tenant->status === 'trialing' ? 'bg-blue-900 text-blue-300' : ($tenant->status === 'suspended' ? 'bg-red-900 text-red-300' : 'bg-yellow-900 text-yellow-300')) }}">
                    {{ $tenant->status }}
                </span>
                @if($tenant->trial_ends_at)
                <span class="text-sm text-gray-400">Trial hasta: {{ $tenant->trial_ends_at->format('d/m/Y') }}</span>
                @endif
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h4 class="font-semibold mb-4">Usuarios ({{ $tenant->users->count() }})</h4>
            @forelse($tenant->users as $user)
            <div class="flex justify-between py-2 border-b border-gray-700 last:border-0">
                <div>
                    <span class="text-white">{{ $user->name }}</span>
                    <span class="text-gray-400 text-sm ml-2">{{ $user->email }}</span>
                </div>
                <span class="text-xs px-2 py-1 rounded {{ $user->pivot->role === 'admin' ? 'bg-purple-900 text-purple-300' : 'bg-gray-700 text-gray-300' }}">
                    {{ $user->pivot->role }}
                </span>
            </div>
            @empty
            <p class="text-gray-500">Sin usuarios</p>
            @endforelse
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h4 class="font-semibold mb-4">Alojamientos ({{ $tenant->properties->count() }})</h4>
            @forelse($tenant->properties as $property)
            <div class="py-2 border-b border-gray-700 last:border-0">
                <span class="text-white">{{ $property->name }}</span>
                <span class="text-gray-400 text-sm ml-2">{{ $property->city }}</span>
            </div>
            @empty
            <p class="text-gray-500">Sin alojamientos</p>
            @endforelse
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h4 class="font-semibold mb-4">Plan actual</h4>
            @php $sub = $tenant->subscriptions->firstWhere('status', '!=', 'canceled'); @endphp
            @if($sub && $sub->plan)
            <div class="text-2xl font-bold text-blue-400">{{ $sub->plan->name }}</div>
            <div class="text-sm text-gray-400 mt-1">{{ number_format($sub->plan->price_monthly, 0) }}€/mes</div>
            <div class="text-sm text-gray-400 mt-1">Estado: {{ $sub->status }}</div>
            @else
            <p class="text-gray-500">Sin suscripción activa</p>
            @endif
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h4 class="font-semibold mb-4">Cambiar plan</h4>
            <form method="POST" action="{{ route('admin.tenants.plan', $tenant) }}">
                @csrf
                <select name="plan_code" class="w-full bg-gray-700 border-gray-600 rounded-lg text-white mb-3 focus:border-blue-500 focus:ring-blue-500">
                    @foreach($plans as $plan)
                    <option value="{{ $plan->code }}" {{ $sub && $sub->plan && $sub->plan->code === $plan->code ? 'selected' : '' }}>
                        {{ $plan->name }} — {{ number_format($plan->price_monthly, 0) }}€/mes
                    </option>
                    @endforeach
                </select>
                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg text-sm hover:bg-blue-700">Cambiar plan</button>
            </form>
        </div>
    </div>
</div>
@endsection

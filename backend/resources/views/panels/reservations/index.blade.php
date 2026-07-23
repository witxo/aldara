@extends('layouts.panel')
@section('title', 'Reservas')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h3 class="text-lg font-semibold">Todas las reservas</h3>
    <a href="{{ route('reservations.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">Nueva reserva</a>
</div>
<div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b">
        <form method="GET" class="flex gap-3">
            <input type="text" name="search" placeholder="Buscar huésped o código..." value="{{ request('search') }}" class="rounded-lg border-gray-300 text-sm flex-1">
            <select name="status" class="rounded-lg border-gray-300 text-sm">
                <option value="">Todos los estados</option>
                @foreach(['pending','confirmed','checkin_sent','partial','completed','cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="direction" value="{{ $direction }}">
            <button type="submit" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm">Filtrar</button>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500 border-b bg-gray-50">
                @php $th = fn($col, $label) => '<th class="p-3"><a href="' . e(request()->fullUrlWithQuery(['sort' => $col, 'direction' => $sort === $col && $direction === 'asc' ? 'desc' : 'asc'])) . '" class="hover:text-gray-800">' . $label . ($sort === $col ? ($direction === 'asc' ? ' ↑' : ' ↓') : '') . '</a></th>'; @endphp
                {!! $th('code', 'Código') !!}
                {!! $th('guest_name', 'Huésped') !!}
                {!! $th('property_name', 'Alojamiento') !!}
                {!! $th('checkin_date', 'Entrada') !!}
                {!! $th('checkout_date', 'Salida') !!}
                {!! $th('source', 'Origen') !!}
                {!! $th('status', 'Estado') !!}
                <th class="p-3"></th>
            </tr></thead>
            <tbody>
                @forelse($reservations as $res)
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3 font-medium">{{ $res->code }}</td>
                    <td class="p-3">{{ $res->guest_name }}</td>
                    <td class="p-3">{{ $res->property->name ?? '—' }}</td>
                    <td class="p-3">{{ $res->checkin_date->format('d/m/Y') }}</td>
                    <td class="p-3">{{ $res->checkout_date->format('d/m/Y') }}</td>
                    <td class="p-3">{{ $res->source }}</td>
                    <td class="p-3">
                        <span class="px-2 py-1 text-xs rounded-full
                            @if($res->status === 'confirmed') bg-blue-100 text-blue-700
                            @elseif($res->status === 'checkin_sent') bg-yellow-100 text-yellow-700
                            @elseif($res->status === 'completed') bg-green-100 text-green-700
                            @elseif($res->status === 'cancelled') bg-red-100 text-red-700
                            @else bg-gray-100 text-gray-700 @endif">{{ $res->status }}</span>
                    </td>
                    <td class="p-3">
                        <a href="{{ route('reservations.show', $res) }}" class="text-blue-600 hover:underline text-xs">Ver</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="p-8 text-center text-gray-500">No hay reservas</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">{{ $reservations->links() }}</div>
</div>
@endsection

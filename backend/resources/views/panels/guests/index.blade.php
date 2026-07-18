@extends('layouts.panel')
@section('title', 'Huéspedes')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h3 class="text-lg font-semibold">Huéspedes</h3>
</div>
<div class="bg-white rounded-lg shadow">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500 border-b bg-gray-50">
                <th class="p-3">Nombre</th>
                <th class="p-3">Nacionalidad</th>
                <th class="p-3">Documento</th>
                <th class="p-3">Reserva</th>
                <th class="p-3"></th>
            </tr></thead>
            <tbody>
                @forelse($guests as $guest)
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3 font-medium">{{ $guest->first_name }} {{ $guest->last_name }}</td>
                    <td class="p-3">{{ $guest->nationality }}</td>
                    <td class="p-3 text-gray-500">{{ $guest->document_type }}: ****{{ substr($guest->document_number, -4) }}</td>
                    <td class="p-3">{{ $guest->reservation?->code ?? '—' }}</td>
                    <td class="p-3"><a href="{{ route('guests.show', $guest) }}" class="text-blue-600 text-xs hover:underline">Ver</a></td>
                </tr>
                @empty
                <tr><td colspan="5" class="p-8 text-center text-gray-500">No hay huéspedes</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3 border-t">@if(method_exists($guests, 'links')){{ $guests->links() }}@endif</div>
</div>
@endsection

@extends('layouts.panel')
@section('title', 'Check-ins')
@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b">
        <h3 class="text-lg font-semibold">Todos los check-ins</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500 border-b bg-gray-50">
                <th class="p-3">Reserva</th>
                <th class="p-3">Huésped</th>
                <th class="p-3">Tipo</th>
                <th class="p-3">Estado</th>
                <th class="p-3">Completado</th>
                <th class="p-3">Verificado</th>
                <th class="p-3"></th>
            </tr></thead>
            <tbody>
                @forelse($checkins as $c)
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3">{{ $c->reservation->code ?? '—' }}</td>
                    <td class="p-3">{{ $c->reservation->guest_name ?? '—' }}</td>
                    <td class="p-3">{{ $c->type }}</td>
                    <td class="p-3">
                        <span class="px-2 py-1 text-xs rounded-full
                            @if($c->status === 'verified') bg-green-100 text-green-700
                            @elseif($c->status === 'completed') bg-blue-100 text-blue-700
                            @elseif($c->status === 'rejected') bg-red-100 text-red-700
                            @else bg-yellow-100 text-yellow-700 @endif">{{ $c->status }}</span>
                    </td>
                    <td class="p-3">{{ $c->completed_at?->format('d/m/Y H:i') ?? '—' }}</td>
                    <td class="p-3">{{ $c->verified_at?->format('d/m/Y H:i') ?? '—' }}</td>
                    <td class="p-3"><a href="{{ route('checkins.show', $c) }}" class="text-blue-600 text-xs">Ver</a></td>
                </tr>
                @empty
                <tr><td colspan="7" class="p-8 text-center text-gray-500">No hay check-ins</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">{{ $checkins->links() }}</div>
</div>
@endsection

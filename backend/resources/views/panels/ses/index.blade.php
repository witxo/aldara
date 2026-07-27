@extends('layouts.panel')
@section('title', 'SES Hospedajes')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h3 class="text-lg font-semibold">Envíos SES Hospedajes</h3>
    <form action="{{ route('ses.export') }}" method="GET">
        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm">Exportar todo</button>
    </form>
</div>
<div class="bg-white rounded-lg shadow">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500 border-b bg-gray-50">
                <th class="p-3">ID</th>
                <th class="p-3">Reserva</th>
                <th class="p-3">Estado</th>
                <th class="p-3">Modo</th>
                <th class="p-3">Referencia</th>
                <th class="p-3">Creado</th>
                <th class="p-3"></th>
            </tr></thead>
            <tbody>
                @forelse($submissions as $s)
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3">#{{ $s->id }}</td>
                    <td class="p-3">{{ $s->reservation->code ?? '—' }}</td>
                    <td class="p-3">
                        <span class="px-2 py-1 text-xs rounded-full
                            @if($s->status === 'sent' || $s->status === 'acknowledged') bg-green-100 text-green-700
                            @elseif($s->status === 'partially_sent') bg-yellow-100 text-yellow-700
                            @elseif($s->status === 'failed' || $s->status === 'rejected') bg-red-100 text-red-700
                            @elseif($s->status === 'ready') bg-blue-100 text-blue-700
                            @else bg-gray-100 text-gray-700 @endif">{{ $s->status }}</span>
                    </td>
                    <td class="p-3">{{ $s->mode }}</td>
                    <td class="p-3">{{ $s->reference ?? '—' }}</td>
                    <td class="p-3">{{ $s->created_at->format('d/m/Y H:i') }}</td>
                    <td class="p-3 flex items-center gap-2">
                        <a href="{{ route('ses.show', $s) }}" class="text-blue-600 text-xs hover:underline">Ver</a>
                        @if(in_array($s->status, ['draft', 'failed']))
                        <form method="POST" action="{{ route('ses.destroy', $s) }}" onsubmit="return confirm('¿Eliminar este envío?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 text-xs hover:underline">Eliminar</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="p-8 text-center text-gray-500">No hay envíos SES</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">{{ $submissions->links() }}</div>
</div>
@endsection

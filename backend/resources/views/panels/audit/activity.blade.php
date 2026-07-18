@extends('layouts.panel')
@section('title', 'Actividad')
@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500 border-b bg-gray-50">
                <th class="p-3">Fecha</th>
                <th class="p-3">Descripción</th>
                <th class="p-3">Usuario</th>
                <th class="p-3">Log</th>
            </tr></thead>
            <tbody>
                @forelse($activities as $a)
                <tr class="border-b hover:bg-gray-50 text-sm">
                    <td class="p-3 text-gray-500">{{ $a->created_at->format('d/m/Y H:i') }}</td>
                    <td class="p-3">{{ $a->description }}</td>
                    <td class="p-3">{{ $a->causer?->name ?? '—' }}</td>
                    <td class="p-3"><span class="px-2 py-0.5 text-xs rounded bg-gray-100">{{ $a->log_name }}</span></td>
                </tr>
                @empty
                <tr><td colspan="4" class="p-8 text-center text-gray-500">Sin actividad registrada</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3 border-t">@if(method_exists($activities, 'links')){{ $activities->links() }}@endif</div>
</div>
@endsection

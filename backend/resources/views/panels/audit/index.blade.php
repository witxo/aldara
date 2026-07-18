@extends('layouts.panel')
@section('title', 'Auditoría')
@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500 border-b bg-gray-50">
                <th class="p-3">Fecha</th>
                <th class="p-3">Evento</th>
                <th class="p-3">Tipo</th>
                <th class="p-3">Usuario</th>
                <th class="p-3">IP</th>
            </tr></thead>
            <tbody>
                @forelse($logs as $log)
                <tr class="border-b hover:bg-gray-50 text-sm">
                    <td class="p-3 text-gray-500">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                    <td class="p-3">{{ $log->event }}</td>
                    <td class="p-3 text-gray-500">{{ $log->auditable_type }}</td>
                    <td class="p-3">{{ $log->user?->name ?? '—' }}</td>
                    <td class="p-3 text-gray-500">{{ $log->ip_address ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="p-8 text-center text-gray-500">Sin registros de auditoría</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3 border-t">@if(method_exists($logs, 'links')){{ $logs->links() }}@endif</div>
</div>
@endsection

@extends('layouts.panel')
@section('title', 'Facturas')
@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500 border-b bg-gray-50">
                <th class="p-3">Nº factura</th>
                <th class="p-3">Fecha</th>
                <th class="p-3">Concepto</th>
                <th class="p-3">Importe</th>
                <th class="p-3">Estado</th>
            </tr></thead>
            <tbody>
                @forelse($invoices as $inv)
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3 font-medium">{{ $inv->number ?? '—' }}</td>
                    <td class="p-3 text-gray-500">{{ $inv->created_at->format('d/m/Y') }}</td>
                    <td class="p-3">{{ $inv->description ?? 'Suscripción' }}</td>
                    <td class="p-3">{{ number_format($inv->amount, 2) }} €</td>
                    <td class="p-3">
                        <span class="px-2 py-0.5 text-xs rounded {{ $inv->status === 'paid' ? 'bg-green-100 text-green-700' : ($inv->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">{{ $inv->status }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="p-8 text-center text-gray-500">Sin facturas</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3 border-t">@if(method_exists($invoices, 'links')){{ $invoices->links() }}@endif</div>
</div>
@endsection

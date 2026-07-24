@extends('layouts.admin')

@section('title', 'Base de datos')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold">Base de datos</h2>
    <p class="text-gray-400 mt-1">Explorador de tablas de la base de datos</p>
</div>

<div class="bg-gray-800 rounded-lg overflow-hidden">
    <div class="p-4 border-b border-gray-700">
        <span class="text-sm text-gray-400">{{ count($tables) }} tablas</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-700 text-gray-300">
                    <th class="text-left px-4 py-3">Tabla</th>
                    <th class="text-left px-4 py-3">Motor</th>
                    <th class="text-right px-4 py-3">Filas</th>
                    <th class="text-right px-4 py-3">Tamaño</th>
                    <th class="text-left px-4 py-3">Cotejamiento</th>
                    <th class="text-left px-4 py-3">Comentario</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @foreach($tables as $table)
                <tr class="hover:bg-gray-750">
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.database.table', $table->Name) }}" class="text-blue-400 hover:text-blue-300 font-medium">
                            {{ $table->Name }}
                        </a>
                    </td>
                    <td class="px-4 py-3 text-gray-400">{{ $table->Engine ?? '-' }}</td>
                    <td class="px-4 py-3 text-right text-gray-400">{{ number_format($table->Rows) }}</td>
                    <td class="px-4 py-3 text-right text-gray-400">
                        @php
                            $size = ($table->Data_length + $table->Index_length);
                            echo $size > 1048576 ? round($size / 1048576, 1) . ' MB' : round($size / 1024, 1) . ' KB';
                        @endphp
                    </td>
                    <td class="px-4 py-3 text-gray-400">{{ $table->Collation ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $table->Comment ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

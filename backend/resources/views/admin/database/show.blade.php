@extends('layouts.admin')

@section('title', "Tabla: {$table}")

@section('content')
<div class="mb-6 flex items-center justify-between flex-wrap gap-4">
    <div>
        <h2 class="text-2xl font-bold">Tabla: <code class="text-blue-400">{{ $table }}</code></h2>
        <p class="text-gray-400 mt-1">
            <a href="{{ route('admin.database') }}" class="text-blue-400 hover:text-blue-300">← Volver a tablas</a>
        </p>
    </div>
</div>

<div class="bg-gray-800 rounded-lg overflow-hidden mb-6">
    <div class="p-4 border-b border-gray-700">
        <h3 class="text-lg font-semibold">Estructura</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-700 text-gray-300">
                    <th class="text-left px-4 py-2">Columna</th>
                    <th class="text-left px-4 py-2">Tipo</th>
                    <th class="text-center px-4 py-2">Nulo</th>
                    <th class="text-center px-4 py-2">Clave</th>
                    <th class="text-left px-4 py-2">Default</th>
                    <th class="text-left px-4 py-2">Extra</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @foreach($columns as $col)
                <tr class="hover:bg-gray-750">
                    <td class="px-4 py-2 font-medium {{ $col->Field === $primaryKey ? 'text-yellow-400' : 'text-gray-200' }}">
                        {{ $col->Field }}
                        @if($col->Field === $primaryKey)
                            <span class="text-xs text-yellow-500 ml-1">PK</span>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-gray-400"><code>{{ $col->Type }}</code></td>
                    <td class="px-4 py-2 text-center text-gray-400">{{ $col->Null }}</td>
                    <td class="px-4 py-2 text-center text-gray-400">{{ $col->Key }}</td>
                    <td class="px-4 py-2 text-gray-400"><code>{{ $col->Default ?? 'NULL' }}</code></td>
                    <td class="px-4 py-2 text-gray-400">{{ $col->Extra }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="bg-gray-800 rounded-lg overflow-hidden">
    <div class="p-4 border-b border-gray-700 flex items-center justify-between flex-wrap gap-4">
        <h3 class="text-lg font-semibold">Contenido ({{ $paginator->total() }} registros)</h3>
        <form method="GET" action="{{ route('admin.database.table', $table) }}" class="flex items-center gap-2">
            <input type="text" name="search" value="{{ $search }}" placeholder="Buscar..." class="px-3 py-1.5 bg-gray-700 border border-gray-600 rounded text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-blue-500">
            <input type="hidden" name="per_page" value="{{ $perPage }}">
            <button type="submit" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 rounded text-sm">Buscar</button>
            @if($search)
                <a href="{{ route('admin.database.table', $table) }}" class="px-3 py-1.5 bg-gray-700 hover:bg-gray-600 rounded text-sm">Limpiar</a>
            @endif
        </form>
    </div>
    <div class="overflow-x-auto">
        @if(count($rows) > 0)
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-700 text-gray-300">
                    @foreach($columns as $col)
                        <th class="text-left px-4 py-2 whitespace-nowrap">
                            <a href="{{ route('admin.database.table', [$table, 'sort' => $col->Field, 'direction' => ($sort === $col->Field && $direction === 'asc') ? 'desc' : 'asc', 'search' => $search, 'per_page' => $perPage]) }}" class="hover:text-blue-400">
                                {{ $col->Field }}
                                @if($sort === $col->Field)
                                    <span class="ml-1">{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                    @endforeach
                    <th class="px-4 py-2 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @foreach($rows as $row)
                @php $rowArray = (array) $row; @endphp
                <tr class="hover:bg-gray-750">
                    @foreach($columns as $col)
                        <td class="px-4 py-2 text-gray-300 max-w-xs truncate" title="{{ $rowArray[$col->Field] ?? '' }}">
                            @php $val = $rowArray[$col->Field] ?? null; @endphp
                            @if($val === null)
                                <span class="text-gray-600 italic">NULL</span>
                            @elseif(is_string($val) && in_array(\App\Http\Controllers\Admin\DatabaseExplorerController::getColumnType($col->Type), ['text', 'mediumtext', 'longtext', 'json']) || (is_string($val) && strlen($val) > 100))
                                <span title="{{ $val }}">{{ Str::limit($val, 80) }}</span>
                            @elseif($col->Field === $primaryKey)
                                <span class="text-yellow-400 font-mono">{{ $val }}</span>
                            @else
                                {{ $val }}
                            @endif
                        </td>
                    @endforeach
                    <td class="px-4 py-2 text-center whitespace-nowrap">
                        <a href="{{ route('admin.database.edit', [$table, $rowArray[$primaryKey]]) }}" class="text-blue-400 hover:text-blue-300 mr-2">Editar</a>
                        <form method="POST" action="{{ route('admin.database.destroy', [$table, $rowArray[$primaryKey]]) }}" class="inline" onsubmit="return confirm('¿Eliminar registro #{{ $rowArray[$primaryKey] }} de {{ $table }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="p-8 text-center text-gray-500">
            @if($search)
                No se encontraron registros para "{{ $search }}".
            @else
                La tabla está vacía.
            @endif
        </div>
        @endif
    </div>
    @if($paginator->hasPages())
    <div class="p-4 border-t border-gray-700">
        {{ $paginator->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection

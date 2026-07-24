@extends('layouts.admin')

@section('title', "Editar: {$table} #{$row->{$primaryKey}}")

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold">
        Editar <code class="text-blue-400">{{ $table }}</code>
        <span class="text-gray-400">#{{ $row->{$primaryKey} }}</span>
    </h2>
    <p class="text-gray-400 mt-1">
        <a href="{{ route('admin.database.table', $table) }}" class="text-blue-400 hover:text-blue-300">← Volver a {{ $table }}</a>
    </p>
</div>

<form method="POST" action="{{ route('admin.database.update', [$table, $row->{$primaryKey}]) }}" class="space-y-4">
    @csrf
    @method('PUT')

    @foreach($columns as $column)
        @php
            $colType = \App\Http\Controllers\Admin\DatabaseExplorerController::getColumnType($column->Type);
            $value = $row->{$column->Field};
            $isPk = $column->Field === $primaryKey;
            $isTimestamp = in_array($column->Field, ['created_at', 'updated_at', 'deleted_at']);
            $isAuto = str_contains($column->Extra ?? '', 'auto_increment');
            $readonly = $isPk || $isAuto || $isTimestamp;
        @endphp
        <div class="bg-gray-800 rounded-lg p-4">
            <label for="field-{{ $column->Field }}" class="block text-sm font-medium mb-1 {{ $isPk ? 'text-yellow-400' : 'text-gray-300' }}">
                {{ $column->Field }}
                @if($isPk) <span class="text-xs text-yellow-500">(PK)</span> @endif
                @if($isTimestamp) <span class="text-xs text-gray-500">(auto)</span> @endif
                @if($isAuto) <span class="text-xs text-gray-500">(auto-increment)</span> @endif
            </label>
            @if($readonly)
                <input type="text" value="{{ $value }}" disabled class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-gray-500 cursor-not-allowed">
                @if($isPk)
                    <input type="hidden" name="{{ $column->Field }}" value="{{ $value }}">
                @endif
            @elseif($colType === 'boolean')
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="{{ $column->Field }}" value="0">
                    <input type="checkbox" name="{{ $column->Field }}" value="1" {{ $value ? 'checked' : '' }} class="rounded bg-gray-700 border-gray-600 text-blue-600 focus:ring-blue-500">
                    <span class="text-gray-400">Activado</span>
                </label>
            @elseif($colType === 'enum')
                @php
                    preg_match("/^enum\('(.+?)'\)$/", $column->Type, $matches);
                    $options = $matches ? explode("','", $matches[1]) : [];
                @endphp
                <select name="{{ $column->Field }}" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-gray-200 focus:outline-none focus:border-blue-500">
                    @if($column->Null === 'YES')
                        <option value="" {{ $value === null ? 'selected' : '' }}>NULL</option>
                    @endif
                    @foreach($options as $opt)
                        <option value="{{ $opt }}" {{ (string) $value === (string) $opt ? 'selected' : '' }}>{{ $opt }}</option>
                    @endforeach
                </select>
            @elseif(in_array($colType, ['text', 'mediumtext', 'longtext', 'json']))
                <textarea name="{{ $column->Field }}" rows="4" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-gray-200 font-mono text-sm focus:outline-none focus:border-blue-500">{{ $colType === 'json' && $value ? json_encode($value, JSON_PRETTY_PRINT) : $value }}</textarea>
            @elseif($colType === 'datetime' || $colType === 'timestamp')
                <input type="datetime-local" name="{{ $column->Field }}" value="{{ $value ? date('Y-m-d\TH:i:s', strtotime($value)) : '' }}" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-gray-200 focus:outline-none focus:border-blue-500">
            @elseif($colType === 'date')
                <input type="date" name="{{ $column->Field }}" value="{{ $value ? date('Y-m-d', strtotime($value)) : '' }}" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-gray-200 focus:outline-none focus:border-blue-500">
            @elseif($colType === 'time')
                <input type="time" name="{{ $column->Field }}" value="{{ $value }}" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-gray-200 focus:outline-none focus:border-blue-500">
            @elseif(in_array($colType, ['integer', 'tinyint', 'smallint', 'bigint']))
                <input type="number" name="{{ $column->Field }}" value="{{ $value }}" step="1" {{ $colType === 'tinyint' ? 'min="0" max="255"' : '' }} class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-gray-200 focus:outline-none focus:border-blue-500">
            @elseif(in_array($colType, ['decimal', 'float', 'double']))
                <input type="number" name="{{ $column->Field }}" value="{{ $value }}" step="any" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-gray-200 focus:outline-none focus:border-blue-500">
            @else
                <input type="text" name="{{ $column->Field }}" value="{{ $value }}" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-gray-200 focus:outline-none focus:border-blue-500">
            @endif
            @if($column->Null === 'YES' && !$readonly)
                <div class="mt-1 text-xs text-gray-500">Puede ser nulo</div>
            @endif
        </div>
    @endforeach

    <div class="flex items-center gap-4 pt-2">
        <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 rounded-lg font-medium">Guardar cambios</button>
        <a href="{{ route('admin.database.table', $table) }}" class="px-6 py-2.5 bg-gray-700 hover:bg-gray-600 rounded-lg text-gray-300">Cancelar</a>
    </div>
</form>
@endsection

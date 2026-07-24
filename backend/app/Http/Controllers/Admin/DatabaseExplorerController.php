<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class DatabaseExplorerController extends Controller
{
    public function index()
    {
        $tables = DB::select('SHOW TABLE STATUS');

        return view('admin.database.index', compact('tables'));
    }

    public function show(Request $request, string $table)
    {
        $this->validateTable($table);

        $columns = DB::select('SHOW FULL COLUMNS FROM `' . $table . '`');
        $primaryKey = $this->getPrimaryKey($table);

        $perPage = $request->input('per_page', 50);
        $sort = $request->input('sort', $primaryKey ?: 'id');
        $direction = $request->input('direction', 'asc');
        $search = $request->input('search');

        $query = DB::table($table);

        if ($search) {
            $searchable = array_filter($columns, fn($c) => in_array($c->Type, ['varchar', 'char', 'text', 'mediumtext', 'longtext']));
            $query->where(function ($q) use ($searchable, $search) {
                foreach ($searchable as $col) {
                    $q->orWhere($col->Field, 'like', "%{$search}%");
                }
            });
        }

        $total = $query->count();
        $page = LengthAwarePaginator::resolveCurrentPage();
        $rows = $query->orderBy($sort, $direction)
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $paginator = new LengthAwarePaginator(
            $rows,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.database.show', compact('table', 'columns', 'primaryKey', 'rows', 'paginator', 'sort', 'direction', 'search', 'perPage'));
    }

    public function edit(string $table, string $id)
    {
        $this->validateTable($table);

        $columns = DB::select('SHOW FULL COLUMNS FROM `' . $table . '`');
        $primaryKey = $this->getPrimaryKey($table);

        if (!$primaryKey) {
            return redirect()->route('admin.database.table', $table)
                ->with('error', 'La tabla no tiene una clave primaria.');
        }

        $row = DB::table($table)->where($primaryKey, $id)->first();

        if (!$row) {
            return redirect()->route('admin.database.table', $table)
                ->with('error', 'Registro no encontrado.');
        }

        return view('admin.database.edit', compact('table', 'columns', 'primaryKey', 'row'));
    }

    public function update(Request $request, string $table, string $id)
    {
        $this->validateTable($table);

        $columns = DB::select('SHOW FULL COLUMNS FROM `' . $table . '`');
        $primaryKey = $this->getPrimaryKey($table);

        if (!$primaryKey) {
            return redirect()->route('admin.database.table', $table)
                ->with('error', 'La tabla no tiene una clave primaria.');
        }

        $oldRow = DB::table($table)->where($primaryKey, $id)->first();
        if (!$oldRow) {
            return redirect()->route('admin.database.table', $table)
                ->with('error', 'Registro no encontrado.');
        }

        $data = [];
        foreach ($columns as $column) {
            if ($column->Field === $primaryKey) continue;

            $colType = $this->getColumnType($column->Type);
            $value = $request->input($column->Field);

            if ($value === null && $column->Null === 'YES') {
                $data[$column->Field] = null;
            } elseif (in_array($colType, ['integer', 'tinyint', 'smallint', 'bigint'])) {
                $data[$column->Field] = $value !== null && $value !== '' ? (int) $value : null;
            } elseif (in_array($colType, ['decimal', 'float', 'double'])) {
                $data[$column->Field] = $value !== null && $value !== '' ? (float) $value : null;
            } elseif ($colType === 'boolean') {
                $data[$column->Field] = $request->boolean($column->Field);
            } elseif (in_array($colType, ['json', 'text', 'mediumtext', 'longtext'])) {
                $data[$column->Field] = $value;
            } else {
                $data[$column->Field] = $value;
            }
        }

        DB::table($table)->where($primaryKey, $id)->update($data);

        $oldValues = json_decode(json_encode($oldRow), true);
        $newRow = DB::table($table)->where($primaryKey, $id)->first();
        $newValues = json_decode(json_encode($newRow), true);
        $changed = [];
        foreach ($newValues as $key => $val) {
            if (($oldValues[$key] ?? null) !== $val) {
                $changed[$key] = $val;
            }
        }

        AuditLog::create([
            'tenant_id' => null,
            'user_id' => auth()->id(),
            'auditable_type' => 'database_table',
            'auditable_id' => $table . '.' . $id,
            'event' => 'updated',
            'old_values' => $oldValues,
            'new_values' => $changed,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('admin.database.table', $table)
            ->with('success', "Registro #{$id} actualizado correctamente.");
    }

    public function destroy(string $table, string $id)
    {
        $this->validateTable($table);

        $primaryKey = $this->getPrimaryKey($table);
        if (!$primaryKey) {
            return redirect()->route('admin.database.table', $table)
                ->with('error', 'La tabla no tiene una clave primaria.');
        }

        $oldRow = DB::table($table)->where($primaryKey, $id)->first();
        if (!$oldRow) {
            return redirect()->route('admin.database.table', $table)
                ->with('error', 'Registro no encontrado.');
        }

        DB::table($table)->where($primaryKey, $id)->delete();

        AuditLog::create([
            'tenant_id' => null,
            'user_id' => auth()->id(),
            'auditable_type' => 'database_table',
            'auditable_id' => $table . '.' . $id,
            'event' => 'deleted',
            'old_values' => json_decode(json_encode($oldRow), true),
            'new_values' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('admin.database.table', $table)
            ->with('success', "Registro #{$id} eliminado correctamente.");
    }

    private function validateTable(string $table): void
    {
        $allTables = DB::select('SHOW TABLES');
        $tableNames = array_map('current', (array) $allTables);

        abort_unless(in_array($table, $tableNames), 404, "Tabla '{$table}' no encontrada.");
    }

    private function getPrimaryKey(string $table): ?string
    {
        $keys = DB::select('SHOW KEYS FROM `' . $table . '` WHERE Key_name = "PRIMARY"');
        return $keys[0]->Column_name ?? null;
    }

    public static function getColumnType(string $typeDef): string
    {
        $typeDef = strtolower($typeDef);
        if (str_starts_with($typeDef, 'tinyint(1)')) return 'boolean';
        if (str_starts_with($typeDef, 'tinyint')) return 'tinyint';
        if (str_starts_with($typeDef, 'smallint')) return 'smallint';
        if (str_starts_with($typeDef, 'bigint')) return 'bigint';
        if (str_starts_with($typeDef, 'int')) return 'integer';
        if (str_starts_with($typeDef, 'decimal')) return 'decimal';
        if (str_starts_with($typeDef, 'float')) return 'float';
        if (str_starts_with($typeDef, 'double')) return 'double';
        if (str_starts_with($typeDef, 'json')) return 'json';
        if (str_starts_with($typeDef, 'text')) return 'text';
        if (str_starts_with($typeDef, 'mediumtext')) return 'mediumtext';
        if (str_starts_with($typeDef, 'longtext')) return 'longtext';
        if (str_starts_with($typeDef, 'varchar')) return 'varchar';
        if (str_starts_with($typeDef, 'char')) return 'char';
        if (str_starts_with($typeDef, 'date')) return 'date';
        if (str_starts_with($typeDef, 'datetime')) return 'datetime';
        if (str_starts_with($typeDef, 'timestamp')) return 'timestamp';
        if (str_starts_with($typeDef, 'time')) return 'time';
        if (str_starts_with($typeDef, 'year')) return 'year';
        if (str_starts_with($typeDef, 'enum')) return 'enum';
        if (str_starts_with($typeDef, 'set')) return 'set';
        if (str_starts_with($typeDef, 'blob')) return 'blob';
        return 'string';
    }
}

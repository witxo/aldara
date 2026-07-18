<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE tenant_user MODIFY COLUMN role ENUM('admin', 'operator') NOT NULL DEFAULT 'operator'");
        DB::table('tenant_user')
            ->whereIn('role', ['owner', 'manager'])
            ->update(['role' => 'admin']);
        DB::table('tenant_user')
            ->where('role', 'receptionist')
            ->update(['role' => 'operator']);
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE tenant_user MODIFY COLUMN role ENUM('owner', 'manager', 'receptionist') NOT NULL DEFAULT 'receptionist'");
    }
};

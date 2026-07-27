<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('subscription_plans')
            ->where('code', 'basic')
            ->update(['max_reservations' => 30, 'description' => '1 alojamiento, 1 usuario, 30 reservas/mes.']);
    }

    public function down(): void
    {
        DB::table('subscription_plans')
            ->where('code', 'basic')
            ->update(['max_reservations' => -1, 'description' => '1 alojamiento, 1 usuario, reservas ilimitadas.']);
    }
};

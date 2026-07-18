<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('ics_calendar_id')
                ->nullable()
                ->constrained('ics_calendars')
                ->nullOnDelete();
            $table->index('ics_calendar_id');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['ics_calendar_id']);
            $table->dropIndex(['ics_calendar_id']);
            $table->dropColumn('ics_calendar_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $calendars = DB::table('ics_calendars')->where('is_active', true)->get();

        foreach ($calendars as $calendar) {
            DB::table('reservations')
                ->whereNull('ics_calendar_id')
                ->where('source', $calendar->provider)
                ->where('property_id', $calendar->property_id)
                ->update(['ics_calendar_id' => $calendar->id]);
        }
    }

    public function down(): void
    {
        DB::table('reservations')->whereNotNull('ics_calendar_id')->update(['ics_calendar_id' => null]);
    }
};

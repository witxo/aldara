<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->char('checkin_code', 32)->nullable()->unique()->after('uuid');
        });

        DB::table('properties')->whereNull('checkin_code')->orderBy('id')->each(function ($row) {
            DB::table('properties')->where('id', $row->id)->update(['checkin_code' => Str::random(32)]);
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->char('checkin_code', 32)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('checkin_code');
        });
    }
};

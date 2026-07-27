<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->foreignId('checkin_id')->nullable()->after('reservation_id')->constrained()->nullOnDelete();
            $table->index('checkin_id');
        });
    }

    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropForeign(['checkin_id']);
            $table->dropIndex(['checkin_id']);
            $table->dropColumn('checkin_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('ses_establishment_id');
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->string('ses_establecimiento_code', 10)->nullable()->after('license_number');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('ses_establecimiento_code');
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->unsignedBigInteger('ses_establishment_id')->nullable()->after('license_number');
        });
    }
};

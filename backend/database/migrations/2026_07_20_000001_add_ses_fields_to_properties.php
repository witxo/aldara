<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('ses_username')->nullable()->after('ses_establecimiento_code');
            $table->string('ses_password')->nullable()->after('ses_username');
            $table->string('ses_codigo_arrendador', 10)->nullable()->after('ses_password');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['ses_username', 'ses_password', 'ses_codigo_arrendador']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->string('parentesco', 5)->nullable()->after('is_main_guest');
            $table->string('address_line1', 100)->nullable()->after('parentesco');
            $table->string('address_line2', 100)->nullable()->after('address_line1');
            $table->string('address_city', 100)->nullable()->after('address_line2');
            $table->string('address_postal_code', 20)->nullable()->after('address_city');
            $table->string('address_country', 3)->nullable()->after('address_postal_code');
        });
    }

    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropColumn(['parentesco', 'address_line1', 'address_line2', 'address_city', 'address_postal_code', 'address_country']);
        });
    }
};

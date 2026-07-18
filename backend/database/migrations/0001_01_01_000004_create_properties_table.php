<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->char('uuid', 36)->unique();
            $table->string('name');
            $table->enum('type', ['apartment', 'house', 'villa', 'studio', 'hotel', 'rural', 'other'])->default('apartment');
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('city', 100);
            $table->string('state', 100);
            $table->string('postal_code', 10);
            $table->string('country', 5)->default('ES');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('license_number', 50)->nullable();
            $table->integer('capacity')->default(2);
            $table->time('checkin_time')->default('15:00');
            $table->time('checkout_time')->default('11:00');
            $table->string('currency', 3)->default('EUR');
            $table->string('language', 5)->default('es');
            $table->string('timezone', 50)->default('Europe/Madrid');
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index('license_number');
            $table->index('city');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->char('uuid', 36)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->enum('document_type', ['dni', 'nie', 'passport', 'other'])->default('dni');
            $table->string('document_number', 50);
            $table->string('nationality', 5)->default('ES');
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female', 'other', 'unspecified'])->nullable();
            $table->boolean('is_main_guest')->default(false);
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 5)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index('reservation_id');
            $table->index(['document_type', 'document_number']);
            $table->index('is_main_guest');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};

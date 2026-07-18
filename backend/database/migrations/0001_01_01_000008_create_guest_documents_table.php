<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('checkin_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['passport', 'dni', 'visa', 'other'])->default('dni');
            $table->string('filename');
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->integer('size');
            $table->string('disk', 50)->default('local');
            $table->string('path', 500);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index('guest_id');
            $table->index('checkin_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_documents');
    }
};

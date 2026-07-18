<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ics_calendars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->enum('provider', ['airbnb', 'booking', 'other'])->default('other');
            $table->string('label', 100);
            $table->text('url');
            $table->string('color', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sync_at')->nullable();
            $table->string('last_sync_status', 20)->nullable();
            $table->text('last_error')->nullable();
            $table->integer('last_sync_count')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index('property_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ics_calendars');
    }
};

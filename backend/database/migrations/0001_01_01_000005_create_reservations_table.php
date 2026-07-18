<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->char('uuid', 36)->unique();
            $table->string('code', 50);
            $table->string('external_code')->nullable();
            $table->enum('source', ['manual', 'booking', 'airbnb', 'web', 'pms', 'other'])->default('manual');
            $table->enum('status', ['pending', 'confirmed', 'checkin_sent', 'partial', 'completed', 'cancelled'])->default('pending');
            $table->string('guest_name');
            $table->string('guest_email')->nullable();
            $table->string('guest_phone', 20)->nullable();
            $table->integer('adults')->default(1);
            $table->integer('children')->default(0);
            $table->integer('infants')->default(0);
            $table->date('checkin_date');
            $table->date('checkout_date');
            $table->time('checkin_time')->nullable();
            $table->time('checkout_time')->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->text('notes')->nullable();
            $table->json('channel_data')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->string('checkin_token', 64)->nullable()->unique();
            $table->timestamp('checkin_token_expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index('property_id');
            $table->index('status');
            $table->index(['checkin_date', 'checkout_date']);
            $table->index('source');
            $table->index('external_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};

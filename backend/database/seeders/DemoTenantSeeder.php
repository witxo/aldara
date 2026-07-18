<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoTenantSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = DB::table('tenants')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'company_name' => 'Gestión Turística Demo SL',
            'tax_id' => 'B12345678',
            'email' => 'demo@checkin.local',
            'phone' => '+34 612 345 678',
            'language' => 'es',
            'timezone' => 'Europe/Madrid',
            'status' => 'active',
            'settings' => json_encode([
                'default_checkin_time' => '15:00',
                'default_checkout_time' => '11:00',
                'require_signature' => true,
                'retention_days' => 1095,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $planId = DB::table('subscription_plans')->where('code', 'pro')->first()->id;

        DB::table('subscriptions')->insert([
            'tenant_id' => $tenantId,
            'plan_id' => $planId,
            'status' => 'active',
            'starts_at' => now(),
            'next_payment_at' => now()->addMonth(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userId = DB::table('users')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'name' => 'Carlos Martínez (Admin Demo)',
            'email' => 'demo-owner@checkin.local',
            'password' => Hash::make('password'),
            'language' => 'es',
            'is_superadmin' => false,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('tenant_user')->insert([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'role' => 'admin',
            'is_active' => true,
            'accepted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $staffUserId = DB::table('users')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'name' => 'Laura García (Operadora)',
            'email' => 'staff@checkin.local',
            'password' => Hash::make('password'),
            'language' => 'es',
            'is_superadmin' => false,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('tenant_user')->insert([
            'tenant_id' => $tenantId,
            'user_id' => $staffUserId,
            'role' => 'operator',
            'is_active' => true,
            'accepted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $propertyId = DB::table('properties')->insertGetId([
            'tenant_id' => $tenantId,
            'uuid' => (string) Str::uuid(),
            'name' => 'Apartamento Sol Centro',
            'type' => 'apartment',
            'address_line1' => 'Calle Mayor 15, 2ºB',
            'city' => 'Málaga',
            'state' => 'Málaga',
            'postal_code' => '29001',
            'country' => 'ES',
            'license_number' => 'VFT/MA/54321',
            'capacity' => 4,
            'checkin_time' => '15:00',
            'checkout_time' => '11:00',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $propertyId2 = DB::table('properties')->insertGetId([
            'tenant_id' => $tenantId,
            'uuid' => (string) Str::uuid(),
            'name' => 'Casa Rural La Sierra',
            'type' => 'rural',
            'address_line1' => 'Camino del Valle 7',
            'city' => 'Ronda',
            'state' => 'Málaga',
            'postal_code' => '29400',
            'country' => 'ES',
            'license_number' => 'VFT/MA/54322',
            'capacity' => 8,
            'checkin_time' => '14:00',
            'checkout_time' => '12:00',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('reservations')->insert([
            'tenant_id' => $tenantId,
            'property_id' => $propertyId,
            'uuid' => (string) Str::uuid(),
            'code' => 'RES-001',
            'source' => 'booking',
            'status' => 'confirmed',
            'guest_name' => 'Ana López Ruiz',
            'guest_email' => 'ana@example.com',
            'guest_phone' => '+34 600 111 222',
            'adults' => 2,
            'children' => 1,
            'checkin_date' => now()->addDays(2)->format('Y-m-d'),
            'checkout_date' => now()->addDays(5)->format('Y-m-d'),
            'total_amount' => 450.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('reservations')->insert([
            'tenant_id' => $tenantId,
            'property_id' => $propertyId,
            'uuid' => (string) Str::uuid(),
            'code' => 'RES-002',
            'source' => 'airbnb',
            'status' => 'checkin_sent',
            'guest_name' => 'Marcos Díaz',
            'guest_email' => 'marcos@example.com',
            'guest_phone' => '+34 600 222 333',
            'adults' => 1,
            'children' => 0,
            'checkin_date' => now()->addDay()->format('Y-m-d'),
            'checkout_date' => now()->addDays(3)->format('Y-m-d'),
            'total_amount' => 200.00,
            'checkin_token' => Str::random(40),
            'checkin_token_expires_at' => now()->addHours(48),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('reservations')->insert([
            'tenant_id' => $tenantId,
            'property_id' => $propertyId2,
            'uuid' => (string) Str::uuid(),
            'code' => 'RES-003',
            'source' => 'manual',
            'status' => 'completed',
            'guest_name' => 'Familia García Pérez',
            'guest_email' => 'familia@example.com',
            'guest_phone' => '+34 600 333 444',
            'adults' => 4,
            'children' => 2,
            'checkin_date' => now()->subDays(2)->format('Y-m-d'),
            'checkout_date' => now()->addDays(2)->format('Y-m-d'),
            'total_amount' => 1200.00,
            'checked_in_at' => now()->subDays(2),
            'infants' => 1,
            'created_at' => now()->subDays(5),
            'updated_at' => now(),
        ]);
    }
}

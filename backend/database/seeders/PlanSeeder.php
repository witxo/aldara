<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('subscription_plans')->insert([
            [
                'name' => 'Básico',
                'code' => 'basico',
                'description' => 'Plan básico. 3 alojamientos, 3 usuarios, 100 reservas/mes.',
                'price_monthly' => 29.00,
                'price_yearly' => 290.00,
                'max_properties' => 3,
                'max_users' => 3,
                'max_reservations' => 100,
                'features' => json_encode([
                    'checkin_online', 'checkin_presential', 'guests_management',
                    'ses_manual', 'panel_full', 'ics_import', 'api_access',
                    'email_notifications',
                ]),
                'trial_days' => 0,
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pro',
                'code' => 'pro',
                'description' => 'Plan profesional. 10 alojamientos, 10 usuarios, reservas ilimitadas.',
                'price_monthly' => 79.00,
                'price_yearly' => 790.00,
                'max_properties' => 10,
                'max_users' => 10,
                'max_reservations' => -1,
                'features' => json_encode([
                    'checkin_online', 'checkin_presential', 'guests_management',
                    'ses_manual', 'ses_auto', 'panel_full', 'ics_import',
                    'api_access', 'email_notifications', 'booking_connector',
                    'airbnb_connector', '2fa', 'export_advanced',
                    'multi_language', 'priority_support',
                ]),
                'trial_days' => 0,
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $features = json_encode([
            'checkin_online', 'checkin_presential', 'guests_management',
            'ses_manual', 'panel_full', 'ics_import', 'api_access',
            'email_notifications',
        ]);

        $now = now();

        DB::table('subscription_plans')->insert([
            [
                'name' => 'Basic',
                'code' => 'basic',
                'description' => '1 alojamiento, 1 usuario, reservas ilimitadas.',
                'price_monthly' => null,
                'price_yearly' => 48.00,
                'max_properties' => 1,
                'max_users' => 1,
                'max_reservations' => -1,
                'features' => $features,
                'trial_days' => 15,
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Advanced',
                'code' => 'advanced',
                'description' => '3 alojamientos, 3 usuarios, reservas ilimitadas.',
                'price_monthly' => null,
                'price_yearly' => 120.00,
                'max_properties' => 3,
                'max_users' => 3,
                'max_reservations' => -1,
                'features' => $features,
                'trial_days' => 15,
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Enterprise',
                'code' => 'enterprise',
                'description' => 'Alojamientos y usuarios ilimitados. Bajo demanda.',
                'price_monthly' => null,
                'price_yearly' => 0,
                'max_properties' => -1,
                'max_users' => -1,
                'max_reservations' => -1,
                'features' => $features,
                'trial_days' => 0,
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Desactivar planes antiguos
        DB::table('subscription_plans')->whereIn('code', ['basico', 'pro'])->update(['is_active' => false]);

        // 2. Insertar nuevos planes
        $features = json_encode([
            'checkin_online', 'checkin_presential', 'guests_management',
            'ses_manual', 'panel_full', 'ics_import', 'api_access',
            'email_notifications',
        ]);

        $now = now();

        $basicId = DB::table('subscription_plans')->insertGetId([
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
        ]);

        $advancedId = DB::table('subscription_plans')->insertGetId([
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
        ]);

        $enterpriseId = DB::table('subscription_plans')->insertGetId([
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
        ]);

        // 3. Migrar suscriptores existentes
        $oldBasico = DB::table('subscription_plans')->where('code', 'basico')->value('id');
        $oldPro = DB::table('subscription_plans')->where('code', 'pro')->value('id');

        if ($oldBasico) {
            DB::table('subscriptions')->where('plan_id', $oldBasico)->update([
                'plan_id' => $advancedId,
                'status' => 'active',
                'updated_at' => $now,
            ]);
        }

        if ($oldPro) {
            DB::table('subscriptions')->where('plan_id', $oldPro)->update([
                'plan_id' => $enterpriseId,
                'status' => 'active',
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        $now = now();

        $basicoId = DB::table('subscription_plans')->insertGetId([
            'name' => 'Básico',
            'code' => 'basico',
            'description' => 'Plan básico. 3 alojamientos, 3 usuarios, 100 reservas/mes.',
            'price_monthly' => 29.00,
            'price_yearly' => 290.00,
            'max_properties' => 3,
            'max_users' => 3,
            'max_reservations' => 100,
            'features' => json_encode(['checkin_online', 'checkin_presential', 'guests_management', 'ses_manual', 'panel_full', 'ics_import', 'api_access', 'email_notifications']),
            'trial_days' => 0,
            'is_active' => true,
            'sort_order' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $proId = DB::table('subscription_plans')->insertGetId([
            'name' => 'Pro',
            'code' => 'pro',
            'description' => 'Plan profesional. 10 alojamientos, 10 usuarios, reservas ilimitadas.',
            'price_monthly' => 79.00,
            'price_yearly' => 790.00,
            'max_properties' => 10,
            'max_users' => 10,
            'max_reservations' => -1,
            'features' => json_encode(['checkin_online', 'checkin_presential', 'guests_management', 'ses_manual', 'ses_auto', 'panel_full', 'ics_import', 'api_access', 'email_notifications', 'booking_connector', 'airbnb_connector', '2fa', 'export_advanced', 'multi_language', 'priority_support']),
            'trial_days' => 0,
            'is_active' => true,
            'sort_order' => 2,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $advancedId = DB::table('subscription_plans')->where('code', 'advanced')->value('id');
        $enterpriseId = DB::table('subscription_plans')->where('code', 'enterprise')->value('id');

        if ($advancedId) {
            DB::table('subscriptions')->where('plan_id', $advancedId)->update(['plan_id' => $basicoId, 'updated_at' => $now]);
        }
        if ($enterpriseId) {
            DB::table('subscriptions')->where('plan_id', $enterpriseId)->update(['plan_id' => $proId, 'updated_at' => $now]);
        }

        DB::table('subscription_plans')->whereIn('code', ['basic', 'advanced', 'enterprise'])->delete();
        DB::table('subscription_plans')->whereIn('code', ['basico', 'pro'])->update(['is_active' => true]);
    }
};

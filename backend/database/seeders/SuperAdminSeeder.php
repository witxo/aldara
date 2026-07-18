<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            'uuid' => (string) Str::uuid(),
            'name' => 'Super Admin',
            'email' => 'admin@checkin.local',
            'password' => Hash::make('password'),
            'language' => 'es',
            'is_superadmin' => true,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

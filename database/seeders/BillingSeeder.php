<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BillingSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('admins')->updateOrInsert(
            ['email' => 'admin@miniport.local'],
            [
                'name' => 'MiniPort Administrator',
                'password' => Hash::make('password123'),
                'last_login_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $adminId = DB::table('admins')
            ->where('email', 'admin@miniport.local')
            ->value('id');

        $service = Service::updateOrCreate(
            ['service_name' => 'Object Storage'],
            [
                'description' => 'Layanan penyimpanan object berbasis bucket S3.',
                'is_active' => true,
                'admin_id' => $adminId,
            ]
        );

        $plans = [
            [
                'plan_name' => 'Free',
                'price' => 0,
                'storage_limit_mb' => 50,
            ],
            [
                'plan_name' => 'Basic',
                'price' => 25000,
                'storage_limit_mb' => 200,
            ],
            [
                'plan_name' => 'Pro',
                'price' => 75000,
                'storage_limit_mb' => 1024,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(
                [
                    'service_id' => $service->id,
                    'plan_name' => $plan['plan_name'],
                ],
                [
                    'price' => $plan['price'],
                    'storage_limit_mb' => $plan['storage_limit_mb'],
                ]
            );
        }
    }
}
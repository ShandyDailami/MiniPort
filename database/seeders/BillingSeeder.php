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

        // Menambahkan properti baru untuk masing-masing plan
        $plans = [
            [
                'plan_name' => 'Free',
                'price' => 0,
                'storage_limit_mb' => 50,
                'description' => 'Paket gratis untuk uji coba sandbox MiniPort.',
                'max_buckets' => 1,
                'max_file_size_mb' => 5,
                'allow_presigned_links' => true,
            ],
            [
                'plan_name' => 'Basic',
                'price' => 25000,
                'storage_limit_mb' => 200,
                'description' => 'Paket dasar dengan kapasitas lebih besar untuk personal.',
                'max_buckets' => 5,
                'max_file_size_mb' => 25,
                'allow_presigned_links' => true,
            ],
            [
                'plan_name' => 'Pro',
                'price' => 75000,
                'storage_limit_mb' => 1024,
                'description' => 'Paket premium dengan kuota besar untuk proyek lanjutan.',
                'max_buckets' => 20,
                'max_file_size_mb' => 100,
                'allow_presigned_links' => true,
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
                    // Memasukkan kolom baru agar tersimpan ke database
                    'description' => $plan['description'],
                    'max_buckets' => $plan['max_buckets'],
                    'max_file_size_mb' => $plan['max_file_size_mb'],
                    'allow_presigned_links' => $plan['allow_presigned_links'],
                ]
            );
        }
    }
}
<?php

namespace Database\Seeders;

use App\Models\Rt;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'nama_rw',
                'value' => 'RW 02',
                'group' => 'general',
                'description' => 'Nama RW',
            ],
            [
                'key' => 'kelurahan',
                'value' => 'Kelurahan Contoh',
                'group' => 'general',
                'description' => 'Nama Kelurahan',
            ],
            [
                'key' => 'kecamatan',
                'value' => 'Kecamatan Contoh',
                'group' => 'general',
                'description' => 'Nama Kecamatan',
            ],
            [
                'key' => 'kota',
                'value' => 'Kota Tangerang',
                'group' => 'general',
                'description' => 'Nama Kota / Kabupaten',
            ],
            [
                'key' => 'nama_ketua_rw',
                'value' => 'Bapak Ketua RW',
                'group' => 'general',
                'description' => 'Nama Ketua RW',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $rt001 = Rt::updateOrCreate(['nomor_rt' => '001']);
        Rt::updateOrCreate(['nomor_rt' => '002']);
        Rt::updateOrCreate(['nomor_rt' => '003']);

        $allPermissions = ['warga', 'iuran', 'bank_sampah', 'posyandu'];

        User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Super Admin RW',
                'password' => 'password123',
                'role' => 'super_admin',
                'rt_id' => null,
                'permissions' => $allPermissions,
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['username' => 'pengurus01'],
            [
                'name' => 'Pengurus RT 01',
                'password' => 'password123',
                'role' => 'pengurus',
                'rt_id' => $rt001->id,
                'permissions' => $allPermissions,
                'is_active' => true,
            ]
        );
    }
}

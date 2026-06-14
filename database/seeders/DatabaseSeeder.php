<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('user_accounts')->insert([
            [
                'email' => 'kepsek@belajarcerdas.id',
                'password' => Hash::make('password123'),
                'no_hp' => '081111111111',
                'role' => 'Guru',
                'status_akun' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'kurikulum@belajarcerdas.id',
                'password' => Hash::make('password123'),
                'no_hp' => '082222222222',
                'role' => 'Administrator',
                'status_akun' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'guru@belajarcerdas.id',
                'password' => Hash::make('password123'),
                'no_hp' => '083333333333',
                'role' => 'Guru',
                'status_akun' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'murid@belajarcerdas.id',
                'password' => Hash::make('password123'),
                'no_hp' => '084444444444',
                'role' => 'Murid',
                'status_akun' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'orangtua@belajarcerdas.id',
                'password' => Hash::make('password123'),
                'no_hp' => '085555555555',
                'role' => 'Siswa',
                'status_akun' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // YAYASAN
        $yayasanId = DB::table('yayasans')->insertGetId([
            'nama_yayasan' => 'Yayasan Pendidikan Cerdas',
            'npwp' => '01.234.567.8-999.000',
            'alamat' => 'Jl. Pendidikan No. 1, Jakarta',
            'kontak' => '021-12345678',
            'email' => 'info@yayasancerdas.id',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userId = DB::table('user_accounts')->insertGetId([
            'email' => 'yayasan@belajarcerdas.id',
            'password' => Hash::make('password123'),
            'no_hp' => '086666666666',
            'role' => 'Yayasan',
            'status_akun' => 'aktif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('yayasan_profiles')->insert([
            'user_id' => $userId,
            'yayasan_id' => $yayasanId,
            'nama_lengkap' => 'Pengurus Yayasan',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

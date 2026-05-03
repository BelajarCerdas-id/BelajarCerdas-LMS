<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\UserAccount; 
use App\Models\ParentProfile; 

class OrangTuaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tentukan ID Sekolah Partner (Misal: 4 untuk SMPN 2 SETU BEKASI)
        $schoolId = 4; 

        $orangTuaData = [
            [
                // Data untuk user_accounts
                'email'       => 'ortubudi@belajarcerdas.id',
                'password'    => Hash::make('12345678'),
                'no_hp'       => '081200000001',
                'role'        => 'Orang Tua',
                'status_akun' => 'aktif',
                
                // Data untuk parent_profiles
                'nama_lengkap' => 'Bapak Budi Santoso',
                'pekerjaan'    => 'Wiraswasta',
                'alamat'       => 'Jl. Merdeka No. 1, Bekasi',
                'student_id'   => 189, // <-- ID Siswa anak Bapak Budi
            ],
            [
                'email'       => 'ortusiti@belajarcerdas.id',
                'password'    => Hash::make('12345678'),
                'no_hp'       => '081200000002',
                'role'        => 'Orang Tua',
                'status_akun' => 'aktif',
                
                'nama_lengkap' => 'Ibu Siti Aminah',
                'pekerjaan'    => 'PNS',
                'alamat'       => 'Jl. Sudirman No. 2, Bekasi',
                'student_id'   => 63, // <-- ID Siswa anak Ibu Siti
            ],
            [
                'email'       => 'ortuandi@belajarcerdas.id',
                'password'    => Hash::make('12345678'),
                'no_hp'       => '081200000003',
                'role'        => 'Orang Tua',
                'status_akun' => 'aktif',
                
                'nama_lengkap' => 'Bapak Andi Wijaya',
                'pekerjaan'    => 'Karyawan Swasta',
                'alamat'       => 'Jl. Pahlawan No. 3, Bekasi',
                'student_id'   => 64, // <-- ID Siswa anak Bapak Andi
            ],
        ];

        foreach ($orangTuaData as $data) {
            
            // 1. Buat atau Update Akun Login (Tabel user_accounts)
            $user = UserAccount::updateOrCreate(
                ['email' => $data['email']], 
                [
                    'password'    => $data['password'],
                    'no_hp'       => $data['no_hp'],
                    'role'        => $data['role'],
                    'status_akun' => $data['status_akun'],
                ]
            );

            // 2. Buat atau Update Profil Orang Tua (Tabel parent_profiles)
            ParentProfile::updateOrCreate(
                ['user_id' => $user->id], 
                [
                    'school_partner_id' => $schoolId,
                    'student_id'        => $data['student_id'], // Otomatis mengisi 60, 63, 64
                    'nama_lengkap'      => $data['nama_lengkap'],
                    'pekerjaan'         => $data['pekerjaan'],
                    'alamat'            => $data['alamat'],
                ]
            );
        }

        $this->command->info('Seeder Akun & Profil Orang Tua berhasil dijalankan! student_id (60, 63, 64) sukses dihubungkan.');
    }
}
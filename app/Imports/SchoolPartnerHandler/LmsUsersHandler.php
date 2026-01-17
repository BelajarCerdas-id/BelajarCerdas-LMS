<?php

namespace App\Imports\SchoolPartnerHandler;

use App\Events\BulkUploadCreateAccount;
use App\Models\Fase;
use App\Models\Kelas;
use App\Models\SchoolClass;
use App\Models\SchoolMajor;
use App\Models\SchoolPartner;
use App\Models\SchoolStaffProfile;
use App\Models\StudentProfile;
use App\Models\StudentSchoolClass;
use App\Models\UserAccount;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class LmsUsersHandler
{
    protected $userId;
    protected $sheetTitle;

    public function __construct($userId, $sheetTitle = '')
    {
        $this->userId = $userId;
        $this->sheetTitle = $sheetTitle;
    }

    public function headingRow(): int
    {
        return 2;
    }

    public function startRow(): int
    {
        return 3;
    }

    public function handle(Collection $rows)
    {
        $errors = [];
        $emailsInFile = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 3;

            DB::beginTransaction();
            try {

                /* =======================
                 * VALIDASI UMUM
                 * ======================= */
                $generalRules = [
                    'nama_user' => 'required',
                    'email_user' => [
                        'required',
                        'email',
                        'regex:/^[a-zA-z0-9._%+-]+@gmail\.com$/',
                    ],
                    'no_hp' => [
                        'required',
                        'regex:/^08\d{9,11}$/',
                    ],
                    'email_akun' => [
                        'required',
                        'email',
                        'regex:/^[a-zA-z0-9._%+-]+@belajarcerdas\.id$/',
                    ],
                    'password_akun' => 'required',
                    'enrollment_type' => 'required',
                    'role_account' => 'required',
                    'nama_sekolah' => 'required',
                    'npsn' => 'required',
                    'jenjang_sekolah' => 'required',
                    'pembelian_fitur' => 'required',
                ];

                // jika role selain siswa, maka nik_user wajib
                if ($row['role_account'] !== 'Siswa') {
                    $generalRules['nik_user'] = 'required';
                }

                // jika role siswa, maka  wajib
                if ($row['role_account'] === 'Siswa') {
                    $generalRules['tipe_kelas'] = 'required';
                    $generalRules['akun_wali_kelas'] = 'required';
                    $generalRules['tahun_ajaran'] = 'required';

                    if ($row['jenjang_sekolah'] === 'SMA' || $row['jenjang_sekolah'] === 'SMK') {
                        $generalRules['nama_jurusan'] = 'required';
                        $generalRules['kode_jurusan'] = 'required';
                    }
                }

                $validator = Validator::make($row->toArray(), $generalRules, [
                    'nama_user.required' => 'Nama tidak boleh kosong.',
                    'nik_user.required' => "NIK tidak boleh kosong.",
                    'email_user.required' => "Email tidak boleh kosong.",
                    'email_user.email' => "Email tidak valid.",
                    'email_user.regex' => "Email tidak valid.",
                    'no_hp.required' => "Nomor HP tidak boleh kosong.",
                    'no_hp.regex' => "Nomor HP tidak valid.",
                    'tipe_kelas.required' => "Tipe Kelas tidak boleh kosong.",
                    'akun_wali_kelas.required' => "Akun Wali Kelas tidak boleh kosong.",
                    'tahun_ajaran.required' => "Tahun Ajaran tidak boleh kosong.",
                    'nama_jurusan.required' => "Nama Jurusan tidak boleh kosong.",
                    'kode_jurusan.required' => "Kode Jurusan tidak boleh kosong.",
                    'email_akun.required' => "Email Akun tidak boleh kosong.",
                    'email_akun.email' => "Email Akun tidak valid.",
                    'email_akun.regex' => "Email Akun tidak valid.",
                    'password_akun.required' => "Password Akun tidak boleh kosong.",
                    'enrollment_type.required' => "Tipe Pendaftaran tidak boleh kosong.",
                    'role_account.required' => "Role Akun tidak boleh kosong.",
                    'nama_sekolah.required' => "Nama Sekolah tidak boleh kosong.",
                    'npsn.required' => "NPSN tidak boleh kosong.",
                    'jenjang_sekolah.required' => "Jenjang Sekolah tidak boleh kosong.",
                    'pembelian_fitur.required' => "Pembelian Fitur tidak boleh kosong.",
                ]);

                if ($validator->fails()) {
                    throw new \Exception($validator->errors()->first());
                }

                /* =======================
                 * CEK DUPLIKASI EMAIL DALAM FILE
                 * ======================= */
                $emailAkun = strtolower($row['email_akun']);
                if (in_array($emailAkun, $emailsInFile)) {
                    throw new \Exception("Email akun {$row['email_akun']} duplikat dalam file.");
                }
                
                $emailsInFile[] = $emailAkun;

                // cek apakah email atau no_hp sudah ada di database
                // Cari user yang sudah ada berdasarkan email yang diinput dari file (kolom 'email_akun')
                $existingUserByEmail = UserAccount::where('email', $row['email_akun'])->first();

                // Cari user yang sudah ada berdasarkan nomor HP yang diinput dari file (kolom 'no_hp')
                $existingUserByPhone = UserAccount::where('no_hp', $row['no_hp'])->first();

                // Cek pertama: jika email sudah ada di database,
                // tapi nomor HP yang terdaftar pada email itu berbeda dengan yang ada di baris ini (satu email_akun hanya bisa digunakan oleh satu no_hp),
                if ($existingUserByEmail && $existingUserByEmail->no_hp !== $row['no_hp']) {
                    // Tambahkan pesan error untuk baris ini agar user tahu penyebab duplikasi
                    throw new \Exception("Email {$row['email_akun']} sudah digunakan oleh akun lain dengan nomor HP berbeda.");
                }

                // Cek kedua: jika nomor HP sudah ada di database,
                // tapi email yang terdaftar pada nomor HP itu berbeda dengan yang ada di baris ini (satu no_hp hanya bisa digunakan oleh satu email_akun),
                if ($existingUserByPhone && $existingUserByPhone->email !== $row['email_akun']) {
                    // Tambahkan pesan error yang menjelaskan nomor HP sudah digunakan oleh email lain
                    throw new \Exception("Nomor HP {$row['no_hp']} sudah digunakan oleh akun lain dengan email berbeda ({$existingUserByPhone->email}).");
                }

                /* =======================
                 * SCHOOL PARTNER
                 * ======================= */
                $schoolPartner = SchoolPartner::where('npsn', $row['npsn'])->first();

                if (!$schoolPartner) {
                    throw new \Exception("NPSN {$row['npsn']} tidak terdaftar.");
                }

                /* =======================
                 * USER ACCOUNT
                 * ======================= */
                $user = UserAccount::updateOrCreate(
                    ['email' => $row['email_akun']],
                    [
                        'password' => bcrypt($row['password_akun']),
                        'no_hp' => $row['no_hp'],
                        'role' => $row['role_account'],
                        'status_akun' => 'aktif',
                    ]
                );

                if ($row['role_account'] === 'Siswa') {
                    $getFase = Fase::where('nama_fase', $row['fase'])->first();
                    $getKelas = Kelas::where('kelas', $row['kelas'])->first();
                    $getWaliKelas = UserAccount::where('email', $row['akun_wali_kelas'])->first();
    
                    // validasi jika fase tidak terdaftar
                    if (!$getFase) {
                        throw new \Exception("Fase tidak boleh kosong.");
                    }
    
                    // validasi jika kelas tidak terdaftar
                    if (!$getKelas) {
                        throw new \Exception("Kelas tidak boleh kosong.");
                    }
    
                    if ($getKelas->fase_id !== $getFase->id) {
                        throw new \Exception("{$row['kelas']} tidak terdaftar pada {$row['fase']}.");
                    }
    
                    if (!$getWaliKelas) {
                        throw new \Exception("Wali Kelas tidak terdaftar.");
                    }
    
                    StudentProfile::updateOrCreate(
                        ['personal_email' => $row['email_user']],
                        [
                            'user_id' => $user->id,
                            'nama_lengkap' => $row['nama_user'],
                            'enrollment_type' => $row['enrollment_type'],
                            'school_partner_id' => $schoolPartner->id,
                        ]
                    );
    
                    /* =======================
                    * SCHOOL MAJORS
                    * ======================= */
                    if ($row['jenjang_sekolah'] === 'SMA' || $row['jenjang_sekolah'] === 'SMK') {
                        $schoolMajors = SchoolMajor::updateOrCreate([
                            'school_partner_id' => $schoolPartner->id,
                            'major_name' => $row['nama_jurusan'],
                        ], [
                            'major_code' => $row['kode_jurusan'],
                        ]);
                    }
    
                    /* =======================
                    * SCHOOL CLASS
                    * ======================= */
                    $schoolClass = SchoolClass::updateOrCreate([
                        'school_partner_id' => $schoolPartner->id,
                        'class_name' => $row['tipe_kelas'],
                        'tahun_ajaran' => $row['tahun_ajaran'],
                    ],
                    [
                        'fase_id' => $getFase->id,
                        'kelas_id' => $getKelas->id,
                        'major_id' => $schoolMajors->id ?? null,
                        'wali_kelas_id' => $getWaliKelas->id,
                    ]);
    
                    /* =======================
                    * STUDENT SCHOOL CLASS
                    * ======================= */
                    StudentSchoolClass::create([
                        'student_id' => $user->id,
                        'school_class_id' => $schoolClass->id,
                    ]);

                } else {
                    SchoolStaffProfile::updateOrCreate(
                        ['personal_email' => $row['email_user']],
                        [
                            'user_id' => $user->id,
                            'school_partner_id' => $schoolPartner->id,
                            'enrollment_type' => $row['enrollment_type'],
                            'nama_lengkap' => $row['nama_user'],
                            'nik' => $row['nik_user'],
                        ]
                    );
                }

                DB::commit();

                broadcast(new BulkUploadCreateAccount($user))->toOthers();

            } catch (\Throwable $e) {
                DB::rollBack();
                $errors[] = "Sheet {$this->sheetTitle} - Baris {$rowNumber}: {$e->getMessage()}";
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages(['import' => $errors]);
        }
    }
}
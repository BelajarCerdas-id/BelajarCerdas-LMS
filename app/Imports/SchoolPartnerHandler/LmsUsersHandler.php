<?php

namespace App\Imports\SchoolPartnerHandler;

use App\Events\BulkUploadCreateAccount;
use App\Models\Fase;
use App\Models\Kelas;
use App\Models\ParentProfile;
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
        // 1. DAPATKAN SEMUA ERROR VALIDASI AWAL
        $validationErrors = $this->getBulkValidationErrors($rows->toArray());

        $errors = [];
        $emailsInFile = [];
        $usersToBroadcast = [];
        $passwordCache = []; 

        // 2. PRE-FETCH DATA UNTUK MENCEGAH N+1 QUERIES
        $npsns = $rows->pluck('npsn')->filter()->unique();
        $schoolPartners = SchoolPartner::whereIn('npsn', $npsns)->get()->keyBy('npsn');

        $faseNames = $rows->pluck('fase')->filter()->unique();
        $fases = Fase::whereIn('nama_fase', $faseNames)->get()->keyBy('nama_fase');

        $kelasNames = $rows->pluck('kelas')->filter()->unique();
        $kelasModels = Kelas::whereIn('kelas', $kelasNames)->get()->keyBy('kelas');

        $emails = $rows->pluck('email_akun')
            ->merge($rows->pluck('email_akun_orang_tua'))
            ->merge($rows->pluck('akun_wali_kelas'))
            ->filter()->unique();
        $existingUsers = UserAccount::whereIn('email', $emails)->get()->keyBy('email');
        $sheetTitle = $this->sheetTitle;
																	   

        // 3. MATIKAN EVENT ELOQUENT & JALANKAN GLOBAL TRANSACTION
        UserAccount::withoutEvents(function () use ($rows, $schoolPartners, $fases, $kelasModels, &$existingUsers, &$emailsInFile, &$passwordCache, &$errors, &$usersToBroadcast, $validationErrors, $sheetTitle) {
            DB::beginTransaction();
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 3;
                try {
                    // Cek jika baris ini gagal pada bulk validator
                    if (isset($validationErrors[$index])) {
                        throw new \Exception($validationErrors[$index]);
                    }
                    $roleAccount = $row['role_account'] ?? null;
                    $roleOrangTua = $row['role_account_orang_tua'] ?? null;
                   
                    // Validasi Bisnis: Duplikasi Email dalam File
                    $emailAkun = strtolower($row['email_akun']);
                    if (in_array($emailAkun, $emailsInFile)) {
                        throw new \Exception("Email akun {$row['email_akun']} duplikat dalam file.");
                    }
                    $emailsInFile[] = $emailAkun;

                    // Validasi Bisnis: Pengecekan Nomor HP Akun Utama
                    $existingUserByEmail = $existingUsers->get($row['email_akun']);
                    if ($existingUserByEmail && $existingUserByEmail->no_hp !== $row['no_hp']) {
                        throw new \Exception("Email akun {$row['email_akun']} sudah digunakan oleh nomor HP berbeda.");
                    }

                    // Validasi Bisnis: Pengecekan Nomor HP Akun Orang Tua
                    $existingParentUser = null;
                    if ($roleOrangTua === 'Orang Tua' && $roleAccount === 'Siswa') {
                        $existingParentUser = $existingUsers->get($row['email_akun_orang_tua']);
                        if ($existingParentUser && $existingParentUser->no_hp !== $row['no_hp_orang_tua']) {
                            throw new \Exception("Email akun orang tua {$row['email_akun_orang_tua']} sudah digunakan oleh nomor HP berbeda.");
                        }
                    }

                    // Validasi Bisnis: Ketersediaan NPSN
                    $schoolPartner = $schoolPartners->get($row['npsn']);
                    if (!$schoolPartner) {
                        throw new \Exception("NPSN {$row['npsn']} tidak terdaftar.");
                    }

                    // Optimasi Bcrypt: Caching Password Identik
                    $plainPasswordAkun = $row['password_akun'] ?? '';
                    if ($plainPasswordAkun && !isset($passwordCache[$plainPasswordAkun])) {
                        $passwordCache[$plainPasswordAkun] = bcrypt($plainPasswordAkun);										   
                    }
				 

                    $plainPasswordParent = $row['password_akun_orang_tua'] ?? '';
                    if ($plainPasswordParent && !isset($passwordCache[$plainPasswordParent])) {
                        $passwordCache[$plainPasswordParent] = bcrypt($plainPasswordParent);	  
                    }

                    // USER ACCOUNT UTAMA
																					 
                    $user = UserAccount::updateOrCreate(
                        ['email' => $row['email_akun']],
                        [
                            'password' => $passwordCache[$plainPasswordAkun] ?? '',
                            'no_hp' => $row['no_hp'],
                            'role' => $roleAccount,
                            'status_akun' => 'aktif',
                        ]
                    );
                    $existingUsers->put($user->email, $user);

                    // AKUN ORANG TUA
                    $parent = null;
                    if ($roleOrangTua === 'Orang Tua' && $roleAccount === 'Siswa') {
                        $parent = UserAccount::updateOrCreate(
                            ['email' => $row['email_akun_orang_tua']],
                            [
                                'password' => $passwordCache[$plainPasswordParent] ?? '',
                                'no_hp' => $row['no_hp_orang_tua'],
                                'role' => $roleOrangTua,
                                'status_akun' => 'aktif',
                            ]
                        );
                        $existingUsers->put($parent->email, $parent);
                    }

                    // PENANGANAN BERDASARKAN ROLE
                    if ($roleAccount === 'Siswa') {
                        
                        $getFase = $fases->get($row['fase']);
                        $getKelas = $kelasModels->get($row['kelas']);
                        $getWaliKelas = $existingUsers->get($row['akun_wali_kelas']);

                        if (!$getFase) throw new \Exception("Fase tidak boleh kosong atau tidak terdaftar.");
                        if (!$getKelas) throw new \Exception("Kelas tidak boleh kosong atau tidak terdaftar.");
											 
                        if ($getKelas->fase_id !== $getFase->id) throw new \Exception("{$row['kelas']} tidak terdaftar pada {$row['fase']}.");
                        if (!$getWaliKelas) throw new \Exception("Wali Kelas tidak terdaftar.");
					 

                        StudentProfile::withoutEvents(fn() => StudentProfile::updateOrCreate(
                            ['user_id' => $user->id],
                            [
                                'personal_email' => $row['email_user'],
                                'nama_lengkap' => $row['nama_user'],
                                'enrollment_type' => $row['enrollment_type'],
                                'school_partner_id' => $schoolPartner->id,
                            ]
                        ));

                        $schoolMajorId = null;
                        if (in_array($row['jenjang_sekolah'] ?? '', ['SMA', 'SMK'])) {
                            $schoolMajors = SchoolMajor::withoutEvents(fn() => SchoolMajor::updateOrCreate(
                                [
                                    'school_partner_id' => $schoolPartner->id,
						  
						 
                                    'major_name' => $row['nama_jurusan'],
                                ],
                                ['major_code' => $row['kode_jurusan']]
                            ));
                            $schoolMajorId = $schoolMajors->id;
                        }

                        $schoolClass = SchoolClass::withoutEvents(fn() => SchoolClass::updateOrCreate(										
                            [
                                'school_partner_id' => $schoolPartner->id,
                                'class_name' => $row['tipe_kelas'],
                                'tahun_ajaran' => $row['tahun_ajaran'],
                            ],
                            [
                                'fase_id' => $getFase->id,
                                'kelas_id' => $getKelas->id,
                                'major_id' => $schoolMajorId,
                                'wali_kelas_id' => $getWaliKelas->id,
                            ]
                        ));
                        StudentSchoolClass::withoutEvents(fn() => StudentSchoolClass::updateOrCreate(
                            [
                                'student_id' => $user->id,
                                'school_class_id' => $schoolClass->id,
                            ]
                        ));
                        if ($roleOrangTua === 'Orang Tua' && $parent) {
                            $existingParentProfile = ParentProfile::where('user_id', $parent->id)->first();
                            
                            if ($existingParentProfile && $existingParentProfile->school_partner_id != $schoolPartner->id) {
                                throw new \Exception("Akun orang tua {$row['email_akun_orang_tua']} sudah terdaftar pada sekolah lain.");
                            }
                            ParentProfile::withoutEvents(fn() => ParentProfile::firstOrCreate(
                                ['user_id' => $parent->id],
                                [
                                    'school_partner_id' => $schoolPartner->id,
                                    'nama_lengkap' => $row['nama_orang_tua_siswa'],
                                ]
                            ));
                            StudentProfile::withoutEvents(fn() => StudentProfile::where('user_id', $user->id)->update(['parent_id' => $parent->id]));  
                        }

                    } else {
                        // PENANGANAN STAFF
                        SchoolStaffProfile::withoutEvents(fn() => SchoolStaffProfile::updateOrCreate(
                            ['user_id' => $user->id],
                            [
                                'school_partner_id' => $schoolPartner->id,
                                'enrollment_type' => $row['enrollment_type'],
                                'nama_lengkap' => $row['nama_user'],
                                'nik' => $row['nik_user'],
                                'personal_email' => $row['email_user'],
                            ]
                        ));   
                    }
                    $usersToBroadcast[] = $user;
                } catch (\Throwable $e) {
                    $errors[] = "Sheet {$sheetTitle} - Baris {$rowNumber}: {$e->getMessage()}";
                }
            }

            if (!empty($errors)) {
                DB::rollBack();
                // Exception ditangkap di luar callback withoutEvents
            } else {
                DB::commit();
            }
        });

        // Lempar error keseluruhan ke frontend jika ada
        if (!empty($errors)) {
            throw ValidationException::withMessages(['import' => $errors]);
        }

        // 4. BULK BROADCAST
        foreach ($usersToBroadcast as $broadcastUser) {
            broadcast(new BulkUploadCreateAccount($broadcastUser))->toOthers();
        }

        return ['status' => 'success', 'message' => 'Data imported successfully.'];
    }

    /**
     * Helper Method: Mengembalikan array error validasi berdasarkan index baris
     */
    private function getBulkValidationErrors(array $rowsArray): array
    {
        $rules = [
            '*.enrollment_type' => 'required',
            '*.role_account'    => 'required',
            '*.nama_sekolah'    => 'required',
            '*.npsn'            => 'required',
            '*.jenjang_sekolah' => 'required',
            '*.email_akun'      => ['required', 'email', 'regex:/^[a-zA-z0-9._%+-]+@belajarcerdas\.id$/'],
            '*.pembelian_fitur' => 'required',
        ];

        // Menerapkan logika kondisional untuk rule array
        foreach ($rowsArray as $index => $row) {
            if (($row['role_account_orang_tua'] ?? null) === 'Orang Tua' && ($row['role_account'] ?? null) === 'Siswa') {
                $rules["{$index}.nama_orang_tua_siswa"] = 'required';
                $rules["{$index}.no_hp_orang_tua"] = ['required', 'regex:/^08\d{9,11}$/'];
                $rules["{$index}.email_akun_orang_tua"] = ['required', 'email', 'regex:/^[a-zA-z0-9._%+-]+@belajarcerdas\.id$/'];
                $rules["{$index}.password_akun_orang_tua"] = 'required';
            } else {
                $rules["{$index}.nama_user"] = 'required';
                $rules["{$index}.email_user"] = ['required', 'email', 'regex:/^[a-zA-z0-9._%+-]+@gmail\.com$/'];
                $rules["{$index}.password_akun"] = 'required';
                $rules["{$index}.no_hp"] = ['required', 'regex:/^08\d{9,11}$/'];
            }

            if (($row['role_account'] ?? null) !== 'Siswa') {
                $rules["{$index}.nik_user"] = 'required';
            }

            if (($row['role_account'] ?? null) === 'Siswa') {
                $rules["{$index}.tipe_kelas"] = 'required';
                $rules["{$index}.akun_wali_kelas"] = 'required';
                $rules["{$index}.tahun_ajaran"] = 'required';

                if (in_array($row['jenjang_sekolah'] ?? '', ['SMA', 'SMK'])) {
                    $rules["{$index}.nama_jurusan"] = 'required';
                    $rules["{$index}.kode_jurusan"] = 'required';
                }
            }
        }

        // Pesan kustom mengikuti kode asli
        $messages = [
            '*.nama_user.required' => 'Nama tidak boleh kosong.',
            '*.nik_user.required' => 'NIK tidak boleh kosong.',
            '*.email_user.required' => 'Email tidak boleh kosong.',
            '*.email_user.email' => 'Email tidak valid.',
            '*.email_user.regex' => 'Email tidak valid.',
            '*.no_hp.required' => 'Nomor HP tidak boleh kosong.',
            '*.no_hp.regex' => 'Nomor HP tidak valid.',
            '*.tipe_kelas.required' => 'Tipe Kelas tidak boleh kosong.',
            '*.akun_wali_kelas.required' => 'Akun Wali Kelas tidak boleh kosong.',
            '*.tahun_ajaran.required' => 'Tahun Ajaran tidak boleh kosong.',
            '*.nama_jurusan.required' => 'Nama Jurusan tidak boleh kosong.',
            '*.kode_jurusan.required' => 'Kode Jurusan tidak boleh kosong.',
            '*.email_akun.required' => 'Email Akun tidak boleh kosong.',
            '*.email_akun.email' => 'Email akun tidak valid.',
            '*.email_akun.regex' => 'Email akun tidak valid.',
            '*.password_akun.required' => 'Password Akun tidak boleh kosong.',
            '*.nama_orang_tua_siswa.required' => 'Nama Orang Tua Siswa tidak boleh kosong.',
            '*.no_hp_orang_tua.required' => 'Nomor HP Orang Tua tidak boleh kosong.',
            '*.no_hp_orang_tua.regex' => 'Nomor HP Orang Tua tidak valid.',
            '*.email_akun_orang_tua.required' => 'Email Akun Orang Tua tidak boleh kosong.',
            '*.email_akun_orang_tua.email' => 'Email akun orang tua tidak valid.',
            '*.email_akun_orang_tua.regex' => 'Email akun orang tua tidak valid.',
            '*.password_akun_orang_tua.required' => 'Password Akun Orang Tua tidak boleh kosong.',
            '*.enrollment_type.required' => 'Tipe Pendaftaran tidak boleh kosong.',
            '*.role_account.required' => 'Role Akun tidak boleh kosong.',
            '*.nama_sekolah.required' => 'Nama Sekolah tidak boleh kosong.',
            '*.npsn.required' => 'NPSN tidak boleh kosong.',
            '*.jenjang_sekolah.required' => 'Jenjang Sekolah tidak boleh kosong.',
            '*.pembelian_fitur.required' => 'Pembelian Fitur tidak boleh kosong.',
        ];

        $validator = Validator::make($rowsArray, $rules, $messages);

        $rowErrors = [];
        
        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $message) {
                // Ekstrak index (misalnya dari key "99.email_akun")
                preg_match('/^(\d+)\./', $key, $matches);
                if (isset($matches[1])) {
                    $index = (int)$matches[1];
                    // Hanya simpan error pertama yang ditemukan per baris
                    if (!isset($rowErrors[$index])) {
                        $rowErrors[$index] = $message[0];
                    }
                }
            }
        }

        return $rowErrors;
    }
}
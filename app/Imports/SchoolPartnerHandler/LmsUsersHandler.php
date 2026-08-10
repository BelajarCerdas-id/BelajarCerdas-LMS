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
    protected $onlyValidate;

    public function __construct($userId, $sheetTitle = '', $onlyValidate = false)
    {
        $this->userId = $userId;
        $this->sheetTitle = $sheetTitle;
        $this->onlyValidate = $onlyValidate;
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
        $this->validateRows($rows);

        if (!$this->onlyValidate) {
            $this->importRows($rows);
        }

        return [
            'status' => 'success',
            'message' => 'Data imported successfully.',
        ];
    }

    private function importRows(Collection $rows)
    {
        $usersToBroadcast = [];
        $passwordCache = [];

        // preload data
        $npsns = $rows->pluck('npsn')->filter()->unique();
        $schoolPartners = SchoolPartner::whereIn('npsn', $npsns)->get()->keyBy('npsn');

        $faseNames = $rows->pluck('fase')->filter()->unique();
        $fases = Fase::whereIn('nama_fase', $faseNames)->get()->keyBy('nama_fase');

        $kelasNames = $rows->pluck('kelas')->filter()->unique();
        $kelasModels = Kelas::whereIn('kelas', $kelasNames)->get()->keyBy('kelas');

        $emails = $rows->pluck('email_akun')->merge($rows->pluck('email_akun_orang_tua'))->merge($rows->pluck('akun_wali_kelas'))->filter()->unique();

        $existingUsers = UserAccount::whereIn('email', $emails)->get()->keyBy('email');

        UserAccount::withoutEvents(function () use ($rows, $schoolPartners, $fases, $kelasModels, &$existingUsers, &$passwordCache, &$usersToBroadcast) {

            DB::beginTransaction();

            try {

                foreach ($rows as $row) {

                    $roleAccount = $row['role_account'] ?? null;
                    $roleOrangTua = $row['role_account_orang_tua'] ?? null;

                    $schoolPartner = $schoolPartners->get($row['npsn']);

                    // Cache Password
                    $plainPassword = $row['password_akun'] ?? '';

                    if ($plainPassword && !isset($passwordCache[$plainPassword])) {
                        $passwordCache[$plainPassword] = bcrypt($plainPassword);
                    }

                    $plainParentPassword = $row['password_akun_orang_tua'] ?? '';

                    if ($plainParentPassword && !isset($passwordCache[$plainParentPassword])) {
                        $passwordCache[$plainParentPassword] = bcrypt($plainParentPassword);
                    }

                    // User Account
                    $user = UserAccount::updateOrCreate(
                        [
                            'email' => $row['email_akun']
                        ],
                        [
                            'password' => $passwordCache[$plainPassword] ?? '',
                            'no_hp' => $row['no_hp'],
                            'role' => $roleAccount,
                            'status_akun' => 'aktif',
                        ]
                    );

                    $existingUsers->put($user->email, $user);

                    // Parent Account
                    $parent = null;

                    if ($roleAccount === 'Siswa' && $roleOrangTua === 'Orang Tua') {

                        $parent = UserAccount::updateOrCreate(
                            [
                                'email' => $row['email_akun_orang_tua']
                            ],
                            [
                                'password' => $passwordCache[$plainParentPassword] ?? '',
                                'no_hp' => $row['no_hp_orang_tua'],
                                'role' => $roleOrangTua,
                                'status_akun' => 'aktif',
                            ]
                        );

                        $existingUsers->put($parent->email, $parent);
                    }

                    // Student
                    if ($roleAccount === 'Siswa') {

                        $fase = $fases->get($row['fase']);
                        $kelas = $kelasModels->get($row['kelas']);
                        $waliKelas = $existingUsers->get($row['akun_wali_kelas']);

                        StudentProfile::withoutEvents(fn() =>
                            StudentProfile::updateOrCreate(
                                ['user_id' => $user->id],
                                [
                                    'personal_email' => $row['email_user'], 
                                    'nisn' => $row['nisn'],
                                    'nama_lengkap' => $row['nama_user'],
                                    'enrollment_type' => $row['enrollment_type'],
                                    'school_partner_id' => $schoolPartner->id,
                                ]
                            )
                        );

                        $majorId = null;

                        if (in_array($row['jenjang_sekolah'], ['SMA', 'SMK'])) {

                            $major = SchoolMajor::withoutEvents(fn() =>
                                SchoolMajor::updateOrCreate(
                                    [
                                        'school_partner_id' => $schoolPartner->id,
                                        'major_name' => $row['nama_jurusan'],
                                    ],
                                    [
                                        'major_code' => $row['kode_jurusan']
                                    ]
                                )
                            );

                            $majorId = $major->id;
                        }

                        $schoolClass = SchoolClass::withoutEvents(fn() =>
                            SchoolClass::updateOrCreate(
                                [
                                    'school_partner_id' => $schoolPartner->id,
                                    'class_name' => $row['tipe_kelas'],
                                    'tahun_ajaran' => $row['tahun_ajaran'],
                                ],
                                [
                                    'fase_id' => $fase->id,
                                    'kelas_id' => $kelas->id,
                                    'major_id' => $majorId,
                                    'wali_kelas_id' => $waliKelas->id,
                                ]
                            )
                        );

                        StudentSchoolClass::withoutEvents(fn() =>
                            StudentSchoolClass::updateOrCreate(
                                [
                                    'student_id' => $user->id,
                                    'school_class_id' => $schoolClass->id,
                                ]
                            )
                        );

                        if ($parent) {

                            ParentProfile::withoutEvents(fn() =>
                                ParentProfile::firstOrCreate(
                                    [
                                        'user_id' => $parent->id,
                                    ],
                                    [
                                        'school_partner_id' => $schoolPartner->id,
                                        'nama_lengkap' => $row['nama_orang_tua_siswa'],
                                    ]
                                )
                            );

                            StudentProfile::withoutEvents(fn() =>
                                StudentProfile::where('user_id', $user->id)
                                    ->update([
                                        'parent_id' => $parent->id
                                    ])
                            );
                        }

                    } else {

                        // Staff
                        SchoolStaffProfile::withoutEvents(fn() =>
                            SchoolStaffProfile::updateOrCreate(
                                [
                                    'user_id' => $user->id,
                                ],
                                [
                                    'school_partner_id' => $schoolPartner->id,
                                    'enrollment_type' => $row['enrollment_type'],
                                    'nama_lengkap' => $row['nama_user'],
                                    'nik' => $row['nik_user'],
                                    'personal_email' => $row['email_user'],
                                ]
                            )
                        );
                    }

                    $usersToBroadcast[] = $user;
                }

                DB::commit();

            } catch (\Throwable $e) {
                DB::rollBack();
                throw $e;
            }
        });

        foreach ($usersToBroadcast as $user) {
            broadcast(new BulkUploadCreateAccount($user))->toOthers();
        }
    }

    private function validateRows(Collection $rows)
    {
        // Validasi struktur kolom (required, regex, dll)
        $validationErrors = $this->getBulkValidationErrors($rows->toArray());

        $errors = [];
        $emailsInFile = [];
        $nisnInFile = [];

        // Preload data untuk validasi
        $npsns = $rows->pluck('npsn')->filter()->unique();
        $schoolPartners = SchoolPartner::whereIn('npsn', $npsns)->get()->keyBy('npsn');

        $faseNames = $rows->pluck('fase')->filter()->unique();
        $fases = Fase::whereIn('nama_fase', $faseNames)->get()->keyBy('nama_fase');

        $kelasNames = $rows->pluck('kelas')->filter()->unique();
        $kelasModels = Kelas::whereIn('kelas', $kelasNames)->get()->keyBy('kelas');

        $emails = $rows->pluck('email_akun')->merge($rows->pluck('email_akun_orang_tua'))->merge($rows->pluck('akun_wali_kelas'))->filter()->unique();

        $existingUsers = UserAccount::whereIn('email', $emails)->get()->keyBy('email');

        $fileStaffEmails = $rows->filter(function ($row) {
            return ($row['role_account'] ?? null) !== 'Siswa';
        })->pluck('email_akun')->map(fn($email) => strtolower(trim($email)))->unique();

        foreach ($rows as $index => $row) {

            $rowNumber = $index + 3;

            try {

                // Error dari Validator
                if (isset($validationErrors[$index])) {
                    throw new \Exception($validationErrors[$index]);
                }

                $roleAccount   = $row['role_account'] ?? null;
                $roleOrangTua  = $row['role_account_orang_tua'] ?? null;

                // Email duplikat dalam file
                $emailAkun = strtolower($row['email_akun']);

                if (in_array($emailAkun, $emailsInFile)) {
                    throw new \Exception("Email akun {$row['email_akun']} duplikat dalam file.");
                }

                $emailsInFile[] = $emailAkun;

                // Email sudah ada tetapi nomor HP berbeda
                $existingUser = $existingUsers->get($row['email_akun']);

                if ($existingUser && $existingUser->no_hp !== $row['no_hp']) {
                    throw new \Exception("Email akun {$row['email_akun']} sudah digunakan oleh nomor HP berbeda.");
                }

                // Validasi akun orang tua
                if ($roleAccount === 'Siswa' && $roleOrangTua === 'Orang Tua') {

                    $existingParent = $existingUsers->get($row['email_akun_orang_tua']);

                    if ($existingParent && $existingParent->no_hp !== $row['no_hp_orang_tua']) {
                        throw new \Exception("Email akun orang tua {$row['email_akun_orang_tua']} sudah digunakan oleh nomor HP berbeda.");
                    }

                    $existingParentProfile = ParentProfile::where('user_id', $existingParent?->id)->first();

                    if (
                        $existingParentProfile &&
                        $existingParentProfile->school_partner_id != optional($schoolPartners->get($row['npsn']))->id
                    ) {
                        throw new \Exception("Akun orang tua {$row['email_akun_orang_tua']} sudah terdaftar pada sekolah lain.");
                    }
                }

                // NPSN
                $schoolPartner = $schoolPartners->get($row['npsn']);

                if (!$schoolPartner) {
                    throw new \Exception("NPSN {$row['npsn']} tidak terdaftar.");
                }

                // Validasi khusus siswa
                if ($roleAccount === 'Siswa') {

                    $fase = $fases->get($row['fase']);
                    $kelas = $kelasModels->get($row['kelas']);
                    $waliKelasEmail = strtolower(trim($row['akun_wali_kelas']));

                    if (!$fase) {
                        throw new \Exception("Fase tidak boleh kosong atau tidak terdaftar.");
                    }

                    if (!$kelas) {
                        throw new \Exception("Kelas tidak boleh kosong atau tidak terdaftar.");
                    }

                    if ($kelas->fase_id != $fase->id) {
                        throw new \Exception("{$row['kelas']} tidak terdaftar pada {$row['fase']}.");
                    }

                    $exists = $existingUsers->has($waliKelasEmail) || $fileStaffEmails->contains($waliKelasEmail);

                    if (!$exists) {
                        throw new \Exception("Wali Kelas tidak terdaftar.");
                    }

                    $checkNisn = StudentProfile::where('nisn', $row['nisn'])->first();

                    if ($checkNisn) {
                        throw new \Exception("NISN {$row['nisn']} sudah terdaftar.");
                    }

                    // nisn duplikat dalam file
                    $nisn = strtolower($row['nisn']);

                    if (in_array($nisn, $nisnInFile)) {
                        throw new \Exception("NISN {$row['nisn']} duplikat dalam file.");
                    }

                    $nisnInFile[] = $nisn;
                }

            } catch (\Throwable $e) {
                $errors[] = "Sheet {$this->sheetTitle} - Baris {$rowNumber}: {$e->getMessage()}";
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages([
                'import' => $errors,
            ]);
        }
    }

    // Mengembalikan array error validasi berdasarkan index baris
    private function getBulkValidationErrors(array $rowsArray): array
    {
        $rules = [
            '*.enrollment_type' => 'required',
            '*.role_account'    => 'required',
            '*.nama_sekolah'    => 'required',
            '*.npsn'            => 'required',
            '*.jenjang_sekolah' => 'required',
            '*.email_akun'      => ['required', 'email', 'regex:/^[A-z0-9._-]+@(belajar|belajarcerdas|gmail)\.(id|com)$'],
            '*.pembelian_fitur' => 'required',
        ];

        foreach ($rowsArray as $index => $row) {
            if (($row['role_account_orang_tua'] ?? null) === 'Orang Tua' && ($row['role_account'] ?? null) === 'Siswa') {
                $rules["{$index}.nama_orang_tua_siswa"] = 'required';
                $rules["{$index}.no_hp_orang_tua"] = ['required', 'regex:/^08\d{8,11}$/'];
                $rules["{$index}.email_akun_orang_tua"] = ['required', 'email', 'regex:/^[A-z0-9._-]+@(belajar|belajarcerdas|gmail)\.(id|com)$'];
                $rules["{$index}.password_akun_orang_tua"] = 'required';
            } else {
                $rules["{$index}.nama_user"] = 'required';
                $rules["{$index}.email_user"] = ['required', 'email', 'regex:/^[a-zA-z0-9._%+-]+@gmail\.com$/'];
                $rules["{$index}.password_akun"] = 'required';
                $rules["{$index}.no_hp"] = ['required', 'regex:/^08\d{8,11}$/'];
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
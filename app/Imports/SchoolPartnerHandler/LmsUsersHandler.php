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
                    'enrollment_type' => 'required',
                    'role_account' => 'required',
                    'nama_sekolah' => 'required',
                    'npsn' => 'required',
                    'jenjang_sekolah' => 'required',
                    'email_akun' => [
                        'required',
                        'email',
                        'regex:/^[a-zA-z0-9._%+-]+@belajarcerdas\.id$/',
                    ],
                    'pembelian_fitur' => 'required',
                ];

                if ($row['role_account_orang_tua'] === 'Orang Tua' && $row['role_account'] === 'Siswa') {
                    $generalRules['nama_orang_tua_siswa'] = 'required';

                    $generalRules['no_hp_orang_tua'] = [
                        'required',
                        'regex:/^08\d{9,11}$/',
                    ];

                    $generalRules['email_akun_orang_tua'] = [
                        'required',
                        'email',
                        'regex:/^[a-zA-z0-9._%+-]+@belajarcerdas\.id$/',
                    ];

                    $generalRules['password_akun_orang_tua'] = 'required';
                } else {
                    $generalRules['nama_user'] = 'required';

                    $generalRules['email_user'] = [
                        'required',
                        'email',
                        'regex:/^[a-zA-z0-9._%+-]+@gmail\.com$/',
                    ];

                    $generalRules['password_akun'] = 'required';

                    $generalRules['no_hp'] = [
                        'required',
                        'regex:/^08\d{9,11}$/',
                    ];
                }

                // jika role selain siswa, maka nik_user wajib
                if ($row['role_account'] !== 'Siswa') {
                    $generalRules['nik_user'] = 'required';
                }

                // jika role siswa, maka wajib
                if ($row['role_account'] === 'Siswa') {

                    $generalRules['tipe_kelas'] = 'required';
                    $generalRules['akun_wali_kelas'] = 'required';
                    $generalRules['tahun_ajaran'] = 'required';

                    if (
                        $row['jenjang_sekolah'] === 'SMA' ||
                        $row['jenjang_sekolah'] === 'SMK'
                    ) {
                        $generalRules['nama_jurusan'] = 'required';
                        $generalRules['kode_jurusan'] = 'required';
                    }
                }

                $validator = Validator::make(
                    $row->toArray(),
                    $generalRules,
                    [
                        'nama_user.required' => 'Nama tidak boleh kosong.',

                        'nik_user.required' => 'NIK tidak boleh kosong.',

                        'email_user.required' => 'Email tidak boleh kosong.',
                        'email_user.email' => 'Email tidak valid.',
                        'email_user.regex' => 'Email tidak valid.',

                        'no_hp.required' => 'Nomor HP tidak boleh kosong.',
                        'no_hp.regex' => 'Nomor HP tidak valid.',

                        'tipe_kelas.required' => 'Tipe Kelas tidak boleh kosong.',
                        'akun_wali_kelas.required' => 'Akun Wali Kelas tidak boleh kosong.',
                        'tahun_ajaran.required' => 'Tahun Ajaran tidak boleh kosong.',

                        'nama_jurusan.required' => 'Nama Jurusan tidak boleh kosong.',
                        'kode_jurusan.required' => 'Kode Jurusan tidak boleh kosong.',

                        'email_akun.required' => 'Email Akun tidak boleh kosong.',
                        'email_akun.email' => 'Email Akun tidak valid.',
                        'email_akun.regex' => 'Email Akun tidak valid.',

                        'password_akun.required' => 'Password Akun tidak boleh kosong.',

                        'nama_orang_tua_siswa.required' => 'Nama Orang Tua Siswa tidak boleh kosong.',

                        'no_hp_orang_tua.required' => 'Nomor HP Orang Tua tidak boleh kosong.',
                        'no_hp_orang_tua.regex' => 'Nomor HP Orang Tua tidak valid.',

                        'email_akun_orang_tua.required' => 'Email Akun Orang Tua tidak boleh kosong.',
                        'email_akun_orang_tua.email' => 'Email Akun Orang Tua tidak valid.',
                        'email_akun_orang_tua.regex' => 'Email Akun Orang Tua tidak valid.',

                        'password_akun_orang_tua.required' => 'Password Akun Orang Tua tidak boleh kosong.',

                        'enrollment_type.required' => 'Tipe Pendaftaran tidak boleh kosong.',
                        'role_account.required' => 'Role Akun tidak boleh kosong.',

                        'nama_sekolah.required' => 'Nama Sekolah tidak boleh kosong.',
                        'npsn.required' => 'NPSN tidak boleh kosong.',
                        'jenjang_sekolah.required' => 'Jenjang Sekolah tidak boleh kosong.',
                        'pembelian_fitur.required' => 'Pembelian Fitur tidak boleh kosong.',
                    ]
                );

                if ($validator->fails()) {
                    throw new \Exception($validator->errors()->first());
                }

                // CEK DUPLIKASI EMAIL DALAM FILE
                $emailAkun = strtolower($row['email_akun']);

                if (in_array($emailAkun, $emailsInFile)) {
                    throw new \Exception(
                        "Email akun {$row['email_akun']} duplikat dalam file."
                    );
                }

                $emailsInFile[] = $emailAkun;

                // cari berdasarkan email akun
                $existingUserByEmail = UserAccount::where('email', $row['email_akun'])->first();

                // jika email sudah ada tapi no hp beda -> error
                if ($existingUserByEmail && $existingUserByEmail->no_hp !== $row['no_hp']) 
                {
                    throw new \Exception("Email akun {$row['email_akun']} sudah digunakan oleh nomor HP berbeda.");
                }

                $existingParentUser = null;

                if ($row['role_account_orang_tua'] === 'Orang Tua' && $row['role_account'] === 'Siswa') {

                    $existingParentUser = UserAccount::where('email', $row['email_akun_orang_tua'])->first();

                    if ($existingParentUser && $existingParentUser->no_hp !== $row['no_hp_orang_tua']) {
                        throw new \Exception(
                            "Email akun orang tua {$row['email_akun_orang_tua']} sudah digunakan oleh nomor HP berbeda."
                        );
                    }
                }

                // SCHOOL PARTNER
                $schoolPartner = SchoolPartner::where('npsn', $row['npsn'])->first();

                if (!$schoolPartner) {
                    throw new \Exception(
                        "NPSN {$row['npsn']} tidak terdaftar."
                    );
                }

                // USER ACCOUNT
                if ($row['role_account_orang_tua'] === 'Orang Tua' && $row['role_account'] === 'Siswa') {
                    $user = UserAccount::firstOrCreate(
                        [
                            'email' => $row['email_akun_orang_tua'],
                        ],
                        [
                            'password' => bcrypt($row['password_akun_orang_tua']),
                            'no_hp' => $row['no_hp_orang_tua'],
                            'role' => $row['role_account_orang_tua'],
                            'status_akun' => 'aktif',
                        ]
                    );
                } else {
                    $user = UserAccount::updateOrCreate(
                        [
                            'email' => $row['email_akun'],
                        ],
                        [
                            'password' => bcrypt($row['password_akun']),
                            'no_hp' => $row['no_hp'],
                            'role' => $row['role_account'],
                            'status_akun' => 'aktif',
                        ]
                    );
                }

                // ROLE SISWA
                if ($row['role_account_orang_tua'] === 'Orang Tua' && $row['role_account'] === 'Siswa') {

                    $studentId = UserAccount::where('email', $row['email_akun'])->first();

                    $existingParentProfile = ParentProfile::where('user_id', $user->id)->first();

                    if ($existingParentProfile && $existingParentProfile->school_partner_id != $schoolPartner->id) {
                        throw new \Exception(
                            "Akun orang tua {$row['email_akun_orang_tua']} sudah terdaftar pada sekolah lain."
                        );
                    }

                    if (!$studentId) {
                        throw new \Exception(
                            "Akun siswa {$row['email_akun']} dari orang tua {$row['nama_orang_tua_siswa']} tidak terdaftar."
                        );
                    }

                    // PARENT PROFILE
                    ParentProfile::firstOrCreate(
                        [
                            'user_id' => $user->id,
                        ],
                        [
                            'school_partner_id' => $schoolPartner->id,
                            'nama_lengkap' => $row['nama_orang_tua_siswa'],
                        ]
                    );

                    StudentProfile::where('user_id', $studentId->id)->update([
                        'parent_id' => $user->id,
                    ]);
                } elseif ($row['role_account'] === 'Siswa') {

                    $getFase = Fase::where('nama_fase', $row['fase'])->first();

                    $getKelas = Kelas::where('kelas', $row['kelas'])->first();

                    $getWaliKelas = UserAccount::where('email', $row['akun_wali_kelas'])->first();

                    // validasi fase
                    if (!$getFase) {
                        throw new \Exception(
                            "Fase tidak boleh kosong."
                        );
                    }

                    // validasi kelas
                    if (!$getKelas) {
                        throw new \Exception(
                            "Kelas tidak boleh kosong."
                        );
                    }

                    // validasi relasi kelas & fase
                    if ($getKelas->fase_id !== $getFase->id) {
                        throw new \Exception(
                            "{$row['kelas']} tidak terdaftar pada {$row['fase']}."
                        );
                    }

                    // validasi wali kelas
                    if (!$getWaliKelas) {
                        throw new \Exception(
                            "Wali Kelas tidak terdaftar."
                        );
                    }

                    // STUDENT PROFILE
                    StudentProfile::updateOrCreate(
                        [
                            'user_id' => $user->id,
                        ],
                        [
                            'personal_email' => $row['email_user'],
                            'nama_lengkap' => $row['nama_user'],
                            'enrollment_type' => $row['enrollment_type'],
                            'school_partner_id' => $schoolPartner->id,
                        ]
                    );

                    // SCHOOL MAJORS
                    if (
                        $row['jenjang_sekolah'] === 'SMA' ||
                        $row['jenjang_sekolah'] === 'SMK'
                    ) {

                        $schoolMajors = SchoolMajor::updateOrCreate(
                            [
                                'school_partner_id' => $schoolPartner->id,
                                'major_name' => $row['nama_jurusan'],
                            ],
                            [
                                'major_code' => $row['kode_jurusan'],
                            ]
                        );
                    }

                    // SCHOOL CLASS
                    $schoolClass = SchoolClass::updateOrCreate(
                        [
                            'school_partner_id' => $schoolPartner->id,
                            'class_name' => $row['tipe_kelas'],
                            'tahun_ajaran' => $row['tahun_ajaran'],
                        ],
                        [
                            'fase_id' => $getFase->id,
                            'kelas_id' => $getKelas->id,
                            'major_id' => $schoolMajors->id ?? null,
                            'wali_kelas_id' => $getWaliKelas->id,
                        ]
                    );

                    // STUDENT SCHOOL CLASS
                    StudentSchoolClass::updateOrCreate(
                        [
                            'student_id' => $user->id,
                            'school_class_id' => $schoolClass->id,
                        ],
                        [
                            'student_id' => $user->id,
                            'school_class_id' => $schoolClass->id,
                        ]
                    );

                } else {

                    // SCHOOL STAFF PROFILE
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
                    );
                }

                DB::commit();

                broadcast(
                    new BulkUploadCreateAccount($user)
                )->toOthers();

            } catch (\Throwable $e) {

                DB::rollBack();

                $errors[] =
                    "Sheet {$this->sheetTitle} - Baris {$rowNumber}: {$e->getMessage()}";
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages([
                'import' => $errors
            ]);
        }
    }
}
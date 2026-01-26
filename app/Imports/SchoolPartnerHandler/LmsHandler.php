<?php

namespace App\Imports\SchoolPartnerHandler;

use App\Events\LmsSchoolSubscription;
use App\Models\FeaturePrice;
use App\Models\Feature;
use App\Models\Mapel;
use App\Models\SchoolLmsSubscription;
use App\Models\SchoolMapel;
use App\Models\SchoolPartner;
use App\Models\SchoolStaffProfile;
use App\Models\Transaction;
use App\Models\UserAccount;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LmsHandler
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
        $kepalaSekolahCount = 0;
        $lockedNpsn = null;

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 3;

            DB::beginTransaction();
            try {
                /* =======================
                 * GENERAL VALIDATION
                 * ======================= */
                $validator = Validator::make($row->toArray(), [
                    'nama_kepsek' => 'required',
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
                    'jenjang_sekolah' => 'required',
                    'role_account' => 'required',
                    'nama_sekolah' => 'required',
                    'npsn' => 'required',
                    'nik_kepsek' => 'required',
                    'pembelian_fitur' => 'required',
                    'durasi' => 'required',
                    'metode_pembayaran' => 'required',
                ], [
                    'nama_kepsek.required' => 'Nama tidak boleh kosong.',
                    'email_user.required' => 'Email tidak boleh kosong.',
                    'email_user.email' => "Sheet {$this->sheetTitle} - Baris $rowNumber: Format email_user harus @gmail.com.",
                    'email_user.regex' => "Sheet {$this->sheetTitle} - Baris $rowNumber: Format email_user harus @gmail.com.",
                    'no_hp.required' => 'No.HP tidak boleh kosong.',
                    'no_hp.regex' => 'No.HP tidak valid.',
                    'email_akun.required' => 'Email akun tidak boleh kosong.',
                    'email_akun.email' => "Sheet {$this->sheetTitle} - Baris $rowNumber: Format email_akun harus @belajarcerdas.id.",
                    'email_akun.regex' => "Sheet {$this->sheetTitle} - Baris $rowNumber: Format email_akun harus @belajarcerdas.id.",
                    'password_akun.required' => 'Password akun tidak boleh kosong.',
                    'enrollment_type.required' => 'Enrollment type tidak boleh kosong.',
                    'jenjang_sekolah.required' => "Jenjang Sekolah tidak boleh kosong.",
                    'role_account.required' => 'Role akun tidak boleh kosong.',
                    'nama_sekolah.required' => 'Nama sekolah tidak boleh kosong.',
                    'npsn.required' => 'NPSN tidak boleh kosong.',
                    'nik_kepsek.required' => 'NIK kepala sekolah tidak boleh kosong.',
                    'pembelian_fitur.required' => 'Pembelian fitur tidak boleh kosong.',
                    'durasi.required' => 'Durasi tidak boleh kosong.',
                    'metode_pembayaran.required' => 'Metode pembayaran tidak boleh kosong.',
                ]);

                // mengambil npsn pada baris pertama
                if ($lockedNpsn === null) {
                    $lockedNpsn = $row['npsn'];
                }

                // ambil tanggal hari ini
                // $today = now()->format('Y-m-d');
                $today = Carbon::createFromFormat('Y-m-d', '2027-01-16')->format('Y-m-d');

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

                // Catat email yang sudah dipakai (supaya baris berikutnya tahu)
                $emailsInFile[] = $emailAkun;

                // ambil akun user
                $user = UserAccount::where('email', $row['email_akun'])->first();

                // ambil nama fitur yang sesuai dengan $row pada excel
                $feature = Feature::where('nama_fitur', $row['pembelian_fitur'])->first();

                // ambil nama varian fitur yang dibeli
                if ($feature) {
                    $variantFeature = FeaturePrice::where('variant_name', $row['durasi'])->where('feature_id', $feature->id)->first();
                }

                // validasi jika fitur tidak terdaftar
                if (!$feature) {
                    throw new \Exception("Fitur {$row['pembelian_fitur']} tidak terdaftar.");
                }

                // validasi jika variant feature (durasi) tidak terdaftar
                if (!$variantFeature) {
                    throw new \Exception("Durasi {$row['durasi']} tidak terdaftar pada fitur {$row['pembelian_fitur']}.");
                }

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

                if ($row['npsn'] !== $lockedNpsn) {
                    throw new \Exception(
                        "File hanya boleh berisi SATU sekolah (NPSN: {$lockedNpsn}). Ditemukan sekolah lain dengan NPSN {$row['npsn']}."
                    );
                }

                if ($row['role_account'] === 'Kepala Sekolah') {
                    $kepalaSekolahCount++;

                    if ($kepalaSekolahCount > 1) {
                        throw new \Exception("Tidak dapat menginput lebih dari satu Kepala Sekolah.");
                    }
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

                /* =======================
                 * SCHOOL PARTNER
                 * ======================= */
                $schoolPartner = SchoolPartner::where('npsn', $row['npsn'])->first();

                if (!$schoolPartner) {
                    $schoolPartner = SchoolPartner::create([
                        'npsn' => $row['npsn'],
                        'nama_sekolah' => $row['nama_sekolah'],
                        'kepsek_id' => $user->id,
                        'jenjang_sekolah' => $row['jenjang_sekolah'],
                    ]);
                } else {
                    $schoolPartner->update([
                        'nama_sekolah' => $row['nama_sekolah'],
                        'jenjang_sekolah' => $row['jenjang_sekolah'],
                    ]);
                }

                // ambil lms subscription history
                $getSubscriptionHistory = SchoolLmsSubscription::where('school_partner_id', $schoolPartner->id)
                    ->whereHas('Transaction', function ($query) use ($feature) {
                        $query->where('feature_id', $feature->id)
                            ->where('transaction_status', 'Berhasil')
                            ->where('transaction_source', 'school_partner');
                    })->whereDate('end_date', '>=', $today)->where('subscription_status', 'active') // pastikan masih aktif
                    ->first();

                // cek jika siswa masih memiliki fitur yang aktif, maka tampilkan error
                if ($getSubscriptionHistory) {
                    throw new \Exception("Sekolah {$row['nama_sekolah']} masih memiliki fitur {$row['pembelian_fitur']} yang aktif.");
                }

                $kepsekIsActive = UserAccount::with(['SchoolStaffProfile'])
                    ->whereHas('SchoolStaffProfile', function ($query) use ($schoolPartner) {
                        $query->where('school_partner_id', $schoolPartner->id);
                    })->where('role', 'Kepala Sekolah')->where('status_akun', 'aktif')->first();

                if ($kepsekIsActive && $kepsekIsActive->email !== $row['email_akun']) {
                    $kepsekIsActive->update([
                        'status_akun' => 'non-aktif',
                    ]);

                    $schoolPartner->update([
                        'kepsek_id' => $user->id,
                    ]);
                }

                /* =======================
                 * ROLE MAPPING
                 * ======================= */
                switch ($row['role_account']) {
                    case 'Kepala Sekolah':
                        SchoolStaffProfile::updateOrCreate(
                            ['personal_email' => $row['email_user']],
                            [
                                'user_id' => $user->id,
                                'school_partner_id' => $schoolPartner->id,
                                'enrollment_type' => $row['enrollment_type'],
                                'nama_lengkap' => $row['nama_kepsek'],
                                'nik' => $row['nik_kepsek'],
                            ]
                        );
                        break;
                    default:
                        throw new \Exception("Hanya dapat membuat akun kepala sekolah pada saat proses transaksi.");
                }

                $jenjang = strtoupper(trim($schoolPartner->jenjang_sekolah));

                $mappingClasses = [
                    'SD'  => ['kelas 1','kelas 2','kelas 3','kelas 4','kelas 5','kelas 6'],
                    'MI'  => ['kelas 1','kelas 2','kelas 3','kelas 4','kelas 5','kelas 6'],
                    'SMP' => ['kelas 7','kelas 8','kelas 9'],
                    'MTS' => ['kelas 7','kelas 8','kelas 9'],
                    'SMA' => ['kelas 10','kelas 11','kelas 12'],
                    'SMK' => ['kelas 10','kelas 11','kelas 12'],
                    'MA'  => ['kelas 10','kelas 11','kelas 12'],
                    'MAK' => ['kelas 10','kelas 11','kelas 12'],
                ];

                $allowedKelas = $mappingClasses[$jenjang] ?? [];

                $defaultMapels = Mapel::whereNull('school_partner_id')
                    ->whereHas('Kelas', function ($q) use ($allowedKelas) {
                        $q->whereIn(DB::raw('LOWER(kelas)'), $allowedKelas);
                    })
                    ->get();

                $defaultMapels->each(function ($mapel) use ($schoolPartner) {
                    SchoolMapel::firstOrCreate([
                        'school_partner_id' => $schoolPartner->id,
                        'mapel_id' => $mapel->id,
                    ]);
                });

                /* =======================
                 * TRANSACTION
                 * ======================= */
                $orderId = 'BC-co-lms-' . Str::uuid();

                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'school_partner_id' => $schoolPartner->id,
                    'feature_id' => $feature->id,
                    'feature_variant_id' => $variantFeature->id,
                    'order_id' => $orderId,
                    'payment_method' => $row['metode_pembayaran'],
                    'transaction_status' => 'Berhasil',
                    'price' => $variantFeature->price,
                    'transaction_source' => 'school_partner',
                ]);

                $months = (int) filter_var($variantFeature->duration, FILTER_SANITIZE_NUMBER_INT);
                $start = Carbon::now();
                $end = $start->copy()->addMonths($months);

                $SchoolLmsSubscription = SchoolLmsSubscription::create([
                    'school_partner_id' => $schoolPartner->id,
                    'transaction_id' => $transaction->id,
                    'start_date' => $start,
                    'end_date' => $end,
                    'subscription_status' => 'active',
                ]);

                DB::commit();

                broadcast(new LmsSchoolSubscription($SchoolLmsSubscription))->toOthers();

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
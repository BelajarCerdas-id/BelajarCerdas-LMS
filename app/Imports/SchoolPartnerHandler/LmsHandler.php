<?php

namespace App\Imports\SchoolPartnerHandler;

use App\Events\LmsSchoolSubscription;
use App\Models\FeaturePrice;
use App\Models\Feature;
use App\Models\Mapel;
use App\Models\SchContract;
use App\Models\SchContractTerm;
use App\Models\SchoolMapel;
use App\Models\SchoolPartner;
use App\Models\SchoolStaffProfile;
use App\Models\UserAccount;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class LmsHandler
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

        // Preload Data
        $npsns = $rows->pluck('npsn')->filter()->unique();

        $schoolPartners = SchoolPartner::whereIn('npsn', $npsns)->get()->keyBy('npsn');

        $featureNames = $rows->pluck('pembelian_fitur')->filter()->unique();

        $features = Feature::whereIn('nama_fitur', $featureNames)->get()->keyBy('nama_fitur');

        $variantNames = $rows->pluck('durasi_kontrak')->filter()->unique();

        $featurePrices = FeaturePrice::whereIn('variant_name', $variantNames)->get()->groupBy('feature_id');

        UserAccount::withoutEvents(function () use ($rows, $schoolPartners, $features, $featurePrices, &$passwordCache, &$usersToBroadcast) {

            DB::beginTransaction();

            try {

                foreach ($rows as $row) {

                    $feature = $features->get($row['pembelian_fitur']);

                    $variantFeature = $featurePrices->get($feature->id)?->firstWhere('variant_name', $row['durasi_kontrak']);

                    // Password Cache
                    $plainPassword = $row['password_akun'] ?? '';

                    if ($plainPassword && !isset($passwordCache[$plainPassword])) {
                        $passwordCache[$plainPassword] = bcrypt($plainPassword);
                    }

                    // User Account
                    $user = UserAccount::updateOrCreate(
                        [
                            'email' => $row['email_akun'],
                        ],
                        [
                            'password' => $passwordCache[$plainPassword],
                            'no_hp' => $row['no_hp'],
                            'role' => $row['role_account'],
                            'status_akun' => 'aktif',
                        ]
                    );

                    // School Partner
                    $schoolPartner = $schoolPartners->get($row['npsn']);

                    if (!$schoolPartner) {

                        $schoolPartner = SchoolPartner::create([
                            'npsn' => $row['npsn'],
                            'nama_sekolah' => $row['nama_sekolah'],
                            'kepsek_id' => $user->id,
                            'jenjang_sekolah' => $row['jenjang_sekolah'],
                        ]);

                        $schoolPartners->put($schoolPartner->npsn, $schoolPartner);

                    } else {

                        $schoolPartner->update([
                            'nama_sekolah' => $row['nama_sekolah'],
                            'jenjang_sekolah' => $row['jenjang_sekolah'],
                        ]);

                        if ($row['role_account'] === 'Kepala Sekolah') {

                            $kepsekAktif = UserAccount::with('SchoolStaffProfile')
                                ->whereHas('SchoolStaffProfile', function ($q) use ($schoolPartner) {
                                    $q->where('school_partner_id', $schoolPartner->id);
                                })
                                ->where('role', 'Kepala Sekolah')
                                ->where('status_akun', 'aktif')
                                ->first();

                            if ($kepsekAktif && $kepsekAktif->email !== $row['email_akun']) {

                                $kepsekAktif->update([
                                    'status_akun' => 'non-aktif',
                                ]);

                                $schoolPartner->update([
                                    'kepsek_id' => $user->id,
                                ]);
                            }
                        }
                    }

                    // School Staff
                    SchoolStaffProfile::updateOrCreate(
                        [
                            'personal_email' => $row['email_user'],
                        ],
                        [
                            'user_id' => $user->id,
                            'school_partner_id' => $schoolPartner->id,
                            'enrollment_type' => $row['enrollment_type'],
                            'nama_lengkap' => $row['nama_kepsek'],
                            'nik' => $row['nik_kepsek'],
                        ]
                    );

                    // Default Mapel
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

                    foreach ($defaultMapels as $mapel) {

                        SchoolMapel::firstOrCreate([
                            'school_partner_id' => $schoolPartner->id,
                            'mapel_id' => $mapel->id,
                        ]);

                    }

                    // Contract
                    $lastContract = SchContract::latest('id')->first();

                    $nextNumber = ($lastContract?->id ?? 0) + 1;

                    $contractNumber = 'CTR-' . date('Y') . '-' . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);

                    $months = (int) filter_var($variantFeature->duration, FILTER_SANITIZE_NUMBER_INT);

                    $start = Carbon::now();

                    $end = $start->copy()->addMonths($months);

                    $contract = SchContract::create([
                        'user_id' => $this->userId,
                        'school_partner_id' => $schoolPartner->id,
                        'feature_id' => $feature->id,
                        'feature_price_id' => $variantFeature->id,
                        'contract_number' => $contractNumber,
                        'start_contract' => $start,
                        'end_contract' => $end,
                        'init_student_count' => $row['jumlah_siswa'],
                        'price_per_student' => $row['harga_per_siswa'],
                        'total_term' => $row['total_term'],
                    ]);

                    $startDate = Carbon::parse($contract->start_contract);

                    $totalMonths = Carbon::parse($contract->start_contract)->diffInMonths(Carbon::parse($contract->end_contract));

                    $monthsPerTerm = floor($totalMonths / $contract->total_term);

                    for ($i = 1; $i <= $contract->total_term; $i++) {

                        $termStart = $startDate->copy();

                        $termEnd = $i == $contract->total_term ? Carbon::parse($contract->end_contract) : $termStart->copy()->addMonths($monthsPerTerm)->subDay();

                        SchContractTerm::create([
                            'contract_id' => $contract->id,
                            'term_number' => $i,
                            'period_start' => $termStart,
                            'period_end' => $termEnd,
                            'status' => 'unpaid',
                        ]);

                        $startDate = $termEnd->copy()->addDay();
                    }

                    $usersToBroadcast[] = $contract;
                }

                DB::commit();

            } catch (\Throwable $e) {

                DB::rollBack();

                throw $e;
            }

        });

        foreach ($usersToBroadcast as $contract) {
            broadcast(new LmsSchoolSubscription($contract))->toOthers();
        }
    }

    private function validateRows(Collection $rows)
    {
        $validationErrors = $this->getBulkValidationErrors($rows->toArray());

        $errors = [];
        $emailsInFile = [];
        $kepalaSekolahCount = 0;
        $lockedNpsn = null;

        // Preload Data
        $emails = $rows->pluck('email_akun')->filter()->unique();

        $existingUsersByEmail = UserAccount::whereIn('email', $emails)->get()->keyBy('email');

        $existingUsersByPhone = UserAccount::whereIn(
            'no_hp',
            $rows->pluck('no_hp')->filter()->unique()
        )->get()->keyBy('no_hp');

        $featureNames = $rows->pluck('pembelian_fitur')->filter()->unique();

        $features = Feature::whereIn('nama_fitur', $featureNames)->get()->keyBy('nama_fitur');

        $featurePrices = FeaturePrice::all();

        $schoolPartners = SchoolPartner::whereIn(
            'npsn',
            $rows->pluck('npsn')->filter()->unique()
        )->get()->keyBy('npsn');

        // Validate Each Row
        foreach ($rows as $index => $row) {

            $rowNumber = $index + 3;

            try {

                // Validator Error
                if (isset($validationErrors[$index])) {
                    throw new \Exception($validationErrors[$index]);
                }

                // Lock NPSN
                if ($lockedNpsn === null) {
                    $lockedNpsn = $row['npsn'];
                }

                if ($row['npsn'] !== $lockedNpsn) {
                    throw new \Exception(
                        "File hanya boleh berisi SATU sekolah (NPSN: {$lockedNpsn}). Ditemukan sekolah lain dengan NPSN {$row['npsn']}."
                    );
                }

                // Kepala Sekolah hanya satu
                if ($row['role_account'] === 'Kepala Sekolah') {

                    $kepalaSekolahCount++;

                    if ($kepalaSekolahCount > 1) {
                        throw new \Exception("Tidak dapat menginput lebih dari satu Kepala Sekolah.");
                    }
                }

                // Duplicate Email in File
                $emailAkun = strtolower($row['email_akun']);

                if (in_array($emailAkun, $emailsInFile)) {
                    throw new \Exception("Email akun {$row['email_akun']} duplikat dalam file.");
                }

                $emailsInFile[] = $emailAkun;

                // Existing User
                $existingUserByEmail = $existingUsersByEmail->get($row['email_akun']);

                if ($existingUserByEmail && $existingUserByEmail->no_hp !== $row['no_hp']) {
                    throw new \Exception(
                        "Email {$row['email_akun']} sudah digunakan oleh akun lain dengan nomor HP berbeda."
                    );
                }

                $existingUserByPhone = $existingUsersByPhone->get($row['no_hp']);

                if ($existingUserByPhone && $existingUserByPhone->email !== $row['email_akun']) {
                    throw new \Exception(
                        "Nomor HP {$row['no_hp']} sudah digunakan oleh akun lain dengan email berbeda ({$existingUserByPhone->email})."
                    );
                }

                // Feature
                $feature = $features->get($row['pembelian_fitur']);

                if (!$feature) {
                    throw new \Exception(
                        "Fitur {$row['pembelian_fitur']} tidak terdaftar."
                    );
                }

                $variantFeature = $featurePrices
                    ->where('feature_id', $feature->id)
                    ->firstWhere('variant_name', $row['durasi_kontrak']);

                if (!$variantFeature) {
                    throw new \Exception(
                        "Durasi kontrak {$row['durasi_kontrak']} tidak terdaftar pada fitur {$row['pembelian_fitur']}."
                    );
                }

                // Existing School Contract
                $schoolPartner = $schoolPartners->get($row['npsn']);

                if ($schoolPartner) {

                    $today = now()->toDateString();

                    $contract = SchContract::where('school_partner_id', $schoolPartner->id)->whereDate('end_contract', '>=', $today)->where('status', 'active')->first();

                    if ($contract) {
                        throw new \Exception(
                            "Sekolah {$row['nama_sekolah']} masih memiliki fitur {$row['pembelian_fitur']} yang aktif."
                        );
                    }
                }

                // Role Validation
                if ($row['role_account'] !== 'Kepala Sekolah') {
                    throw new \Exception(
                        "Hanya dapat membuat akun kepala sekolah pada saat proses transaksi."
                    );
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
            '*.nama_kepsek' => 'required',
            '*.email_user' => [
                'required',
                'email',
                'regex:/^[a-zA-z0-9._%+-]+@gmail\.com$/',
            ],
            '*.no_hp' => [
                'required',
                'regex:/^08\d{9,11}$/',
            ],
            '*.email_akun' => [
                'required',
                'email',
                'regex:/^[a-zA-z0-9._%+-]+@belajarcerdas\.id$/',
            ],
            '*.password_akun'    => 'required',
            '*.enrollment_type'  => 'required',
            '*.jenjang_sekolah'  => 'required',
            '*.role_account'     => 'required',
            '*.nama_sekolah'     => 'required',
            '*.npsn'             => 'required',
            '*.nik_kepsek'       => 'required',
            '*.pembelian_fitur'  => 'required',
            '*.durasi_kontrak'   => 'required',
        ];

        $messages = [
            '*.nama_kepsek.required'      => 'Nama tidak boleh kosong.',

            '*.email_user.required'       => 'Email tidak boleh kosong.',
            '*.email_user.email'          => 'Format email_user harus @gmail.com.',
            '*.email_user.regex'          => 'Format email_user harus @gmail.com.',

            '*.no_hp.required'            => 'No.HP tidak boleh kosong.',
            '*.no_hp.regex'               => 'No.HP tidak valid.',

            '*.email_akun.required'       => 'Email akun tidak boleh kosong.',
            '*.email_akun.email'          => 'Format email_akun harus @belajarcerdas.id.',
            '*.email_akun.regex'          => 'Format email_akun harus @belajarcerdas.id.',

            '*.password_akun.required'    => 'Password akun tidak boleh kosong.',

            '*.enrollment_type.required'  => 'Enrollment type tidak boleh kosong.',
            '*.jenjang_sekolah.required'  => 'Jenjang Sekolah tidak boleh kosong.',
            '*.role_account.required'     => 'Role akun tidak boleh kosong.',
            '*.nama_sekolah.required'     => 'Nama sekolah tidak boleh kosong.',
            '*.npsn.required'             => 'NPSN tidak boleh kosong.',
            '*.nik_kepsek.required'       => 'NIK kepala sekolah tidak boleh kosong.',
            '*.pembelian_fitur.required'  => 'Pembelian fitur tidak boleh kosong.',
            '*.durasi_kontrak.required'   => 'Durasi kontrak tidak boleh kosong.',
        ];

        $validator = Validator::make($rowsArray, $rules, $messages);

        $rowErrors = [];

        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $message) {

                // contoh key: "5.email_akun"
                preg_match('/^(\d+)\./', $key, $matches);

                if (isset($matches[1])) {

                    $index = (int) $matches[1];

                    // hanya simpan error pertama setiap baris
                    if (!isset($rowErrors[$index])) {
                        $rowErrors[$index] = $message[0];
                    }
                }
            }
        }

        return $rowErrors;
    }
}
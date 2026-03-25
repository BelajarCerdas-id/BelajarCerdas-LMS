<?php

namespace Database\Seeders;

use App\Models\OfficeProfile;
use App\Models\SchoolPartner;
use App\Models\SchoolStaffProfile;
use App\Models\StudentProfile;
use App\Models\UserAccount;
use App\Models\Kurikulum;
use App\Models\Fase;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\SchoolClass;
use App\Models\SchoolMajor;
use App\Models\TeacherMapel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class IndonesiaRegionSeeder extends Seeder
{
    // Data Provinsi di Indonesia
    private array $provinces = [
        'DKI Jakarta',
        'Jawa Barat',
        'Jawa Tengah',
        'Jawa Timur',
        'Banten',
        'DI Yogyakarta',
        'Sumatera Utara',
        'Sumatera Barat',
        'Sumatera Selatan',
        'Riau',
        'Lampung',
        'Bali',
        'Kalimantan Timur',
        'Sulawesi Selatan',
    ];

    // Data Kota/Kabupaten per Provinsi
    private array $cities = [
        'DKI Jakarta' => [
            'Jakarta Pusat', 'Jakarta Utara', 'Jakarta Barat', 'Jakarta Selatan', 'Jakarta Timur',
        ],
        'Jawa Barat' => [
            'Bandung', 'Bekasi', 'Depok', 'Bogor', 'Cimahi', 'Tasikmalaya', 'Cirebon', 'Sukabumi',
            'Kabupaten Bandung', 'Kabupaten Bekasi', 'Kabupaten Bogor', 'Kabupaten Garut',
        ],
        'Jawa Tengah' => [
            'Semarang', 'Surakarta', 'Magelang', 'Pekalongan', 'Tegal', 'Salatiga',
            'Kabupaten Semarang', 'Kabupaten Boyolali', 'Kabupaten Klaten', 'Kabupaten Sukoharjo',
        ],
        'Jawa Timur' => [
            'Surabaya', 'Malang', 'Batu', 'Madiun', 'Kediri', 'Blitar', 'Probolinggo', 'Pasuruan',
            'Kabupaten Malang', 'Kabupaten Sidoarjo', 'Kabupaten Gresik', 'Kabupaten Mojokerto',
        ],
        'Banten' => [
            'Serang', 'Tangerang', 'Cilegon', 'Tangerang Selatan',
            'Kabupaten Serang', 'Kabupaten Tangerang', 'Kabupaten Lebak', 'Kabupaten Pandeglang',
        ],
        'DI Yogyakarta' => [
            'Yogyakarta', 'Magelang',
            'Kabupaten Sleman', 'Kabupaten Bantul', 'Kabupaten Gunung Kidul', 'Kabupaten Kulon Progo',
        ],
        'Sumatera Utara' => [
            'Medan', 'Binjai', 'Tebing Tinggi', 'Pematang Siantar',
            'Kabupaten Deli Serdang', 'Kabupaten Langkat', 'Kabupaten Karo', 'Kabupaten Simalungun',
        ],
        'Sumatera Barat' => [
            'Padang', 'Bukittinggi', 'Payakumbuh', 'Padang Panjang',
            'Kabupaten Padang Pariaman', 'Kabupaten Agam', 'Kabupaten Limapuluh Kota',
        ],
        'Sumatera Selatan' => [
            'Palembang', 'Prabumulih', 'Lubuklinggau',
            'Kabupaten Ogan Ilir', 'Kabupaten Ogan Komering Ilir', 'Kabupaten Muara Enim',
        ],
        'Riau' => [
            'Pekanbaru', 'Dumai',
            'Kabupaten Kampar', 'Kabupaten Rokan Hulu', 'Kabupaten Bengkalis', 'Kabupaten Indragiri Hulu',
        ],
        'Lampung' => [
            'Bandar Lampung', 'Metro',
            'Kabupaten Lampung Selatan', 'Kabupaten Lampung Tengah', 'Kabupaten Lampung Utara',
        ],
        'Bali' => [
            'Denpasar',
            'Kabupaten Badung', 'Kabupaten Gianyar', 'Kabupaten Tabanan', 'Kabupaten Klungkung',
        ],
        'Kalimantan Timur' => [
            'Balikpapan', 'Samarinda', 'Bontang',
            'Kabupaten Kutai Kartanegara', 'Kabupaten Berau', 'Kabupaten Kutai Barat',
        ],
        'Sulawesi Selatan' => [
            'Makassar', 'Parepare', 'Palopo',
            'Kabupaten Gowa', 'Kabupaten Maros', 'Kabupaten Bone', 'Kabupaten Wajo',
        ],
    ];

    // Data Kecamatan per Kota (sample)
    private array $districts = [
        'Jakarta Pusat' => ['Gambir', 'Tanah Abang', 'Menteng', 'Senen', 'Cempaka Putih', 'Johar Baru'],
        'Bandung' => ['Bandung Wetan', 'Cicendo', 'Coblong', 'Sukasari', 'Andir', 'Astana Anyar'],
        'Semarang' => ['Semarang Tengah', 'Semarang Utara', 'Semarang Selatan', 'Candisari', 'Gajahmungkur'],
        'Surabaya' => ['Tegalsari', 'Genteng', 'Bubutan', 'Tambaksari', 'Kenjeran', 'Gubeng'],
        'Yogyakarta' => ['Gondokusuman', 'Jetis', 'Tegalrejo', 'Gedongtengen', 'Ngampilan', 'Wirobrajan'],
        'Medan' => ['Medan Kota', 'Medan Barat', 'Medan Timur', 'Medan Helvetia', 'Medan Petisah'],
        'Makassar' => ['Makassar', 'Ujung Pandang', 'Wajo', 'Bontoala', 'Tallo', 'Ujung Tanah'],
        'Denpasar' => ['Denpasar Barat', 'Denpasar Timur', 'Denpasar Selatan', 'Denpasar Utara'],
    ];

    // Nama-nama Indonesia umum
    private array $maleNames = [
        'Muhammad', 'Ahmad', 'Budi', 'Agus', 'Dedi', 'Eko', 'Fajar', 'Gunawan', 'Hendra', 'Indra',
        'Joko', 'Kurniawan', 'Lukman', 'Muhammad Rizki', 'Nanda', 'Oki', 'Putra', 'Rizky', 'Surya', 'Taufik',
        'Andi', 'Rudi', 'Heru', 'Wahyu', 'Yudi', 'Zainal', 'Farhan', 'Galih', 'Hamza', 'Irfan',
    ];

    private array $femaleNames = [
        'Siti', 'Aisyah', 'Dewi', 'Rina', 'Putri', 'Nurul', 'Fitri', 'Lestari', 'Maya', 'Novi',
        'Ratna', 'Sri', 'Tuti', 'Wulan', 'Yuni', 'Zahra', 'Anisa', 'Bayu', 'Citra', 'Diana',
        'Eka', 'Fani', 'Gita', 'Hana', 'Indah', 'Jasmine',
    ];

    private array $lastNames = [
        'Pratama', 'Wijaya', 'Santoso', 'Kusuma', 'Putra', 'Saputra', 'Hidayat', 'Rahman', 'Fauzi',
        'Nugroho', 'Setiawan', 'Gunawan', 'Purnomo', 'Hariyanto', 'Wibowo', 'Utomo', 'Susanto', 'Asmara',
        'Siregar', 'Nasution', 'Harahap', 'Simatupang', 'Panjaitan', 'Situmorang',
    ];

    // Nama sekolah
    private array $sdNames = [
        'Sekolah Dasar Negeri', 'Sekolah Dasar Islam', 'Sekolah Dasar Katolik', 'Sekolah Dasar Kristen',
    ];

    private array $smpNames = [
        'Sekolah Menengah Pertama Negeri', 'Sekolah Menengah Pertama Islam', 'Sekolah Menengah Pertama Katolik',
    ];

    private array $smaNames = [
        'Sekolah Menengah Atas Negeri', 'Sekolah Menengah Atas Islam', 'Sekolah Menengah Atas Kristen',
    ];

    private array $smkNames = [
        'Sekolah Menengah Kejuruan Negeri', 'Sekolah Menengah Kejuruan Islam',
    ];

    private array $smkMajors = [
        'Teknik Komputer dan Jaringan', 'Akuntansi', 'Perkantoran', 'Pemasaran',
        'Teknik Kendaraan Ringan', 'Teknik Sepeda Motor', 'Tata Boga', 'Tata Busana',
        'Farmasi', 'Keperawatan', 'Multimedia', 'Rekayasa Perangkat Lunak',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "=== Memulai Seed Data Region Indonesia ===\n";

        // 1. Create Admin User
        echo "Membuat Admin User...\n";
        $adminUser = $this->createAdminUser();

        // 2. Create Office Profile (Admin)
        echo "Membuat Office Profile...\n";
        $this->createOfficeProfile($adminUser);

        // 3. Create Kurikulum
        echo "Membuat Kurikulum...\n";
        $kurikulumMerdeka = $this->createKurikulum($adminUser, 'Kurikulum Merdeka', 'KURMER');
        $kurikulumK13 = $this->createKurikulum($adminUser, 'Kurikulum 2013', 'K13');

        // 4. Create Fases (Kurikulum Merdeka)
        echo "Membuat Fase...\n";
        $fases = $this->createFases($adminUser, $kurikulumMerdeka);

        // 5. Create Kelas
        echo "Membuat Kelas...\n";
        $kelasMap = $this->createKelas($adminUser, $kurikulumMerdeka, $fases);

        // 6. Create Schools (School Partners)
        echo "Membuat Sekolah (School Partners)...\n";
        $schools = $this->createSchools($adminUser);

        // 7. Create School Staff (Guru & Staff)
        echo "Membuat Guru dan Staff...\n";
        $teachers = $this->createSchoolStaff($adminUser, $schools, 'guru');

        // 8. Create Students
        echo "Membuat Siswa...\n";
        $students = $this->createStudents($adminUser, $schools);

        // 9. Create School Classes
        echo "Membuat Kelas Sekolah...\n";
        $this->createSchoolClasses($schools, $kelasMap);

        // 10. Create Mapels
        echo "Membuat Mata Pelajaran...\n";
        $this->createMapels($adminUser, $schools, $kelasMap, $fases, $teachers);

        // 11. Create School Majors (untuk SMK)
        echo "Membuat Jurusan SMK...\n";
        $this->createSchoolMajors($schools);

        echo "\n=== Seed Data Region Indonesia Selesai ===\n";
        echo "Total Provinsi: " . count($this->provinces) . "\n";
        echo "Total Sekolah: " . count($schools) . "\n";
        echo "Total Guru: " . count($teachers) . "\n";
        echo "Total Siswa: " . count($students) . "\n";
    }

    private function createAdminUser(): UserAccount
    {
        // Check if admin already exists
        $admin = UserAccount::where('email', 'admin@belajarcerdas.id')->first();
        
        if ($admin) {
            return $admin;
        }
        
        return UserAccount::create([
            'email' => 'admin@belajarcerdas.id',
            'password' => Hash::make('password123'),
            'no_hp' => '081234567890',
            'role' => 'Administrator',
            'status_akun' => 'aktif',
        ]);
    }

    private function createOfficeProfile(UserAccount $user): void
    {
        OfficeProfile::create([
            'user_id' => $user->id,
            'nama_lengkap' => 'Administrator Belajar Cerdas',
        ]);
    }

    private function createKurikulum(UserAccount $user, string $name, string $code): Kurikulum
    {
        return Kurikulum::create([
            'user_id' => $user->id,
            'nama_kurikulum' => $name,
            'kode' => $code,
        ]);
    }

    private function createFases(UserAccount $user, Kurikulum $kurikulum): array
    {
        $fasesData = [
            ['A', 'Fase A'],  // Kelas 1-2 SD
            ['B', 'Fase B'],  // Kelas 3-4 SD
            ['C', 'Fase C'],  // Kelas 5-6 SD
            ['D', 'Fase D'],  // Kelas 7-9 SMP
            ['E', 'Fase E'],  // Kelas 10 SMA
            ['F', 'Fase F'],  // Kelas 11-12 SMA
        ];

        $fases = [];
        foreach ($fasesData as $faseData) {
            $fases[$faseData[0]] = Fase::create([
                'user_id' => $user->id,
                'nama_fase' => $faseData[1],
                'kode' => $faseData[0],
                'kurikulum_id' => $kurikulum->id,
            ]);
        }

        return $fases;
    }

    private function createKelas(UserAccount $user, Kurikulum $kurikulum, array $fases): array
    {
        $kelasData = [
            '1' => ['1', 'KLS1', 'A'],
            '2' => ['2', 'KLS2', 'A'],
            '3' => ['3', 'KLS3', 'B'],
            '4' => ['4', 'KLS4', 'B'],
            '5' => ['5', 'KLS5', 'C'],
            '6' => ['6', 'KLS6', 'C'],
            '7' => ['7', 'KLS7', 'D'],
            '8' => ['8', 'KLS8', 'D'],
            '9' => ['9', 'KLS9', 'D'],
            '10' => ['10', 'KLS10', 'E'],
            '11' => ['11', 'KLS11', 'F'],
            '12' => ['12', 'KLS12', 'F'],
        ];

        $kelasMap = [];
        foreach ($kelasData as $key => $data) {
            $kelasMap[$key] = Kelas::create([
                'user_id' => $user->id,
                'kelas' => $data[0],
                'kode' => $data[1],
                'fase_id' => $fases[$data[2]]->id,
                'kurikulum_id' => $kurikulum->id,
            ]);
        }

        return $kelasMap;
    }

    private function createSchools(UserAccount $user): array
    {
        $schools = [];
        $npsnCounter = 10000000;

        foreach ($this->provinces as $province) {
            if (!isset($this->cities[$province])) {
                continue;
            }

            foreach ($this->cities[$province] as $city) {
                // Create 1 SD, 1 SMP, 1 SMA, 1 SMK per city
                $schoolTypes = [
                    ['type' => 'SD', 'names' => $this->sdNames],
                    ['type' => 'SMP', 'names' => $this->smpNames],
                    ['type' => 'SMA', 'names' => $this->smaNames],
                    ['type' => 'SMK', 'names' => $this->smkNames],
                ];

                foreach ($schoolTypes as $schoolType) {
                    $npsnCounter++;
                    $schoolName = $this->generateSchoolName($schoolType['names'], $city);
                    $district = $this->getRandomDistrict($city);

                    $school = SchoolPartner::create([
                        'nama_sekolah' => $schoolName,
                        'npsn' => (string) $npsnCounter,
                        'kepsek_id' => $user->id,
                        'jenjang_sekolah' => $schoolType['type'],
                    ]);

                    // Store additional info for later use
                    $school->province = $province;
                    $school->city = $city;
                    $school->district = $district;
                    $school->address = $this->generateAddress($district, $city, $province);

                    $schools[] = $school;

                    // Limit to 50 schools for performance
                    if (count($schools) >= 50) {
                        break 3;
                    }
                }
            }
        }

        return $schools;
    }

    private function createSchoolStaff(UserAccount $user, array $schools, string $role): array
    {
        $staff = [];

        foreach ($schools as $school) {
            // Create 3-5 teachers per school
            $teacherCount = rand(3, 5);

            for ($i = 0; $i < $teacherCount; $i++) {
                $gender = rand(0, 1) === 0 ? 'male' : 'female';
                $name = $this->generateName($gender);
                $email = $this->generateEmail($name, $school->nama_sekolah, $i);

                $teacherUser = UserAccount::create([
                    'email' => $email,
                    'password' => Hash::make('password123'),
                    'no_hp' => $this->generatePhoneNumber(),
                    'role' => 'Guru',
                    'status_akun' => 'aktif',
                ]);

                $teacherProfile = SchoolStaffProfile::create([
                    'user_id' => $teacherUser->id,
                    'school_partner_id' => $school->id,
                    'enrollment_type' => rand(0, 1) === 0 ? 'B2B' : 'B2G',
                    'nama_lengkap' => $name,
                    'nik' => $this->generateNIK(),
                    'personal_email' => $email,
                ]);

                $teacherProfile->school = $school;
                $staff[] = $teacherProfile;
            }
        }

        return $staff;
    }

    private function createStudents(UserAccount $user, array $schools): array
    {
        $students = [];

        foreach ($schools as $school) {
            // Create 5-10 students per school
            $studentCount = rand(5, 10);

            for ($i = 0; $i < $studentCount; $i++) {
                $gender = rand(0, 1) === 0 ? 'male' : 'female';
                $name = $this->generateName($gender);
                $email = $this->generateStudentEmail($name, $school->nama_sekolah, $i);

                $studentUser = UserAccount::create([
                    'email' => $email,
                    'password' => Hash::make('password123'),
                    'no_hp' => $this->generatePhoneNumber(),
                    'role' => 'Siswa',
                    'status_akun' => 'aktif',
                ]);

                $studentProfile = StudentProfile::create([
                    'user_id' => $studentUser->id,
                    'nama_lengkap' => $name,
                    'personal_email' => $email,
                    'enrollment_type' => rand(0, 1) === 0 ? 'B2B' : 'B2G',
                    'school_partner_id' => $school->id,
                ]);

                $studentProfile->school = $school;
                $students[] = $studentProfile;
            }
        }

        return $students;
    }

    private function createSchoolClasses(array $schools, array $kelasMap): void
    {
        // Get admin user for wali_kelas fallback
        $adminUser = UserAccount::where('role', 'administrator')->first();
        
        foreach ($schools as $school) {
            // Create 1-2 classes per grade level based on school type
            $gradeLevels = $this->getGradeLevelsForSchool($school->jenjang_sekolah);

            foreach ($gradeLevels as $grade) {
                if (isset($kelasMap[$grade])) {
                    $classCount = rand(1, 2);
                    $fase = $kelasMap[$grade]->fase;

                    for ($i = 0; $i < $classCount; $i++) {
                        $className = "Kelas {$grade}";
                        if ($classCount > 1) {
                            $className .= " " . chr(65 + $i); // A, B, C
                        }

                        SchoolClass::create([
                            'school_partner_id' => $school->id,
                            'class_name' => $className,
                            'fase_id' => $fase?->id ?? null,
                            'kelas_id' => $kelasMap[$grade]->id,
                            'major_id' => null, // Will be assigned for SMK
                            'wali_kelas_id' => $adminUser?->id ?? 1,
                            'tahun_ajaran' => '2025/2026',
                            'status_class' => 'active',
                        ]);
                    }
                }
            }
        }
    }

    private function createMapels(
        UserAccount $user,
        array $schools,
        array $kelasMap,
        array $fases,
        array $teachers
    ): void {
        $mapelsSD = ['Matematika', 'Bahasa Indonesia', 'IPA', 'IPS', 'Bahasa Inggris', 'PJOK', 'Seni Budaya'];
        $mapelsSMP = ['Matematika', 'Bahasa Indonesia', 'Bahasa Inggris', 'IPA', 'IPS', 'PJOK', 'Seni Budaya', 'Informatika'];
        $mapelsSMA = ['Matematika', 'Bahasa Indonesia', 'Bahasa Inggris', 'Fisika', 'Kimia', 'Biologi', 'Ekonomi', 'Sosiologi', 'Sejarah'];
        $mapelsSMK = ['Matematika', 'Bahasa Indonesia', 'Bahasa Inggris', 'Produktif', 'PKK', 'IPA', 'IPS'];

        $teacherIndex = 0;

        foreach ($schools as $school) {
            $mapels = $this->getMapelsForSchool($school->jenjang_sekolah);
            $gradeLevels = $this->getGradeLevelsForSchool($school->jenjang_sekolah);

            foreach ($mapels as $mapelName) {
                foreach ($gradeLevels as $grade) {
                    if (isset($kelasMap[$grade])) {
                        $teacher = $teachers[$teacherIndex % count($teachers)] ?? null;

                        Mapel::create([
                            'user_id' => $user->id,
                            'mata_pelajaran' => $mapelName,
                            'kode' => strtoupper(substr(str_replace(' ', '', $mapelName), 0, 3)) . $grade,
                            'kelas_id' => $kelasMap[$grade]->id,
                            'fase_id' => $kelasMap[$grade]->fase->id ?? null,
                            'kurikulum_id' => $kelasMap[$grade]->kurikulum_id,
                            'school_partner_id' => $school->id,
                            'status_mata_pelajaran' => 'active',
                        ]);

                        // Get school classes for this grade
                        $schoolClasses = SchoolClass::where('school_partner_id', $school->id)
                            ->where('kelas_id', $kelasMap[$grade]->id)
                            ->get();

                        if ($schoolClasses->isNotEmpty()) {
                            $teacher = $teachers[$teacherIndex % count($teachers)] ?? null;

                            if ($teacher) {
                                foreach ($schoolClasses as $schoolClass) {
                                    TeacherMapel::create([
                                        'user_id' => $user->id,
                                        'mapel_id' => Mapel::where('mata_pelajaran', $mapelName)
                                            ->where('school_partner_id', $school->id)
                                            ->latest()->first()?->id,
                                        'school_class_id' => $schoolClass->id,
                                        'is_active' => true,
                                    ]);
                                }
                            }
                        }

                        $teacherIndex++;
                    }
                }
            }
        }
    }

    private function createSchoolMajors(array $schools): void
    {
        foreach ($schools as $school) {
            if ($school->jenjang_sekolah === 'SMK') {
                // Create 2-4 majors per SMK
                $majorCount = rand(2, 4);
                $shuffledMajors = $this->smkMajors;
                shuffle($shuffledMajors);

                for ($i = 0; $i < $majorCount; $i++) {
                    SchoolMajor::create([
                        'school_partner_id' => $school->id,
                        'major_name' => $shuffledMajors[$i],
                        'major_code' => strtoupper(substr(str_replace(' ', '', $shuffledMajors[$i]), 0, 5)),
                        'status_major' => 'active',
                    ]);
                }
            }
        }
    }

    // Helper Functions

    private function generateSchoolName(array $namePrefixes, string $city): string
    {
        $prefix = $namePrefixes[array_rand($namePrefixes)];
        $number = rand(1, 50);
        $location = explode(' ', $city)[0]; // Take first word of city name

        return "{$prefix} {$number} {$location}";
    }

    private function getRandomDistrict(string $city): string
    {
        if (isset($this->districts[$city])) {
            return $this->districts[$city][array_rand($this->districts[$city])];
        }

        $defaultDistricts = ['Kecamatan Pusat', 'Kecamatan Utara', 'Kecamatan Selatan', 'Kecamatan Barat', 'Kecamatan Timur'];
        return $defaultDistricts[array_rand($defaultDistricts)];
    }

    private function generateAddress(string $district, string $city, string $province): string
    {
        $streetNames = ['Jl. Merdeka', 'Jl. Sudirman', 'Jl. Ahmad Yani', 'Jl. Gatot Subroto', 'Jl. Diponegoro', 'Jl. Kartini'];
        $streetNumber = rand(1, 200);
        $rt = rand(1, 10);
        $rw = rand(1, 5);

        return "{$streetNames[array_rand($streetNames)]} No. {$streetNumber}, RT {$rt}/RW {$rw}, {$district}, {$city}, {$province}";
    }

    private function generateName(string $gender): string
    {
        $names = $gender === 'male' ? $this->maleNames : $this->femaleNames;
        $firstName = $names[array_rand($names)];
        $lastName = $this->lastNames[array_rand($this->lastNames)];

        return "{$firstName} {$lastName}";
    }

    private function generateEmail(string $name, string $schoolName, int $index = 0): string
    {
        $nameParts = explode(' ', strtolower($name));
        $firstName = $nameParts[0];
        $schoolShort = strtolower(substr(str_replace(' ', '', $schoolName), 0, 10));
        $uniqueId = uniqid();

        return "{$firstName}.{$schoolShort}{$index}.{$uniqueId}@belajarcerdas.id";
    }

    private function generateStudentEmail(string $name, string $schoolName, int $index): string
    {
        $nameParts = explode(' ', strtolower($name));
        $firstName = $nameParts[0];
        $uniqueId = uniqid();

        return "student.{$firstName}{$index}.{$uniqueId}@belajarcerdas.id";
    }

    private function generateNIK(): string
    {
        // Generate random 16-digit NIK
        $nik = '';
        for ($i = 0; $i < 16; $i++) {
            $nik .= rand(0, 9);
        }
        return $nik;
    }

    private function generatePhoneNumber(): string
    {
        // Generate random Indonesian phone number (08xx-xxxx-xxxx)
        $prefixes = ['081', '082', '083', '085', '087', '088', '089'];
        $prefix = $prefixes[array_rand($prefixes)];
        
        $number = $prefix;
        for ($i = 0; $i < 8; $i++) {
            $number .= rand(0, 9);
        }
        
        return $number;
    }

    private function getGradeLevelsForSchool(string $schoolType): array
    {
        return match ($schoolType) {
            'SD' => ['1', '2', '3', '4', '5', '6'],
            'SMP' => ['7', '8', '9'],
            'SMA' => ['10', '11', '12'],
            'SMK' => ['10', '11', '12'],
            default => ['10'],
        };
    }

    private function getMapelsForSchool(string $schoolType): array
    {
        return match ($schoolType) {
            'SD' => ['Matematika', 'Bahasa Indonesia', 'IPA', 'IPS', 'Bahasa Inggris', 'PJOK'],
            'SMP' => ['Matematika', 'Bahasa Indonesia', 'Bahasa Inggris', 'IPA', 'IPS', 'PJOK', 'Informatika'],
            'SMA' => ['Matematika', 'Bahasa Indonesia', 'Bahasa Inggris', 'Fisika', 'Kimia', 'Biologi', 'Ekonomi'],
            'SMK' => ['Matematika', 'Bahasa Indonesia', 'Bahasa Inggris', 'Produktif', 'PKK', 'IPA'],
            default => ['Matematika'],
        };
    }
}

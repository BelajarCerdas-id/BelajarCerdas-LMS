<?php

namespace Database\Seeders;

use App\Models\LearningResource;
use App\Models\TkaExam;
use App\Models\TkaExamQuestion;
use App\Models\PracticeExam;
use App\Models\PracticeExamQuestion;
use App\Models\VirtualLab;
use App\Models\Kurikulum;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Bab;
use App\Models\SubBab;
use App\Models\UserAccount;
use App\Models\SchoolPartner;
use App\Models\LmsQuestionBank;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class LearningFeaturesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "=== Memulai Seed Learning Features ===\n";

        // Get necessary data
        $adminUser = UserAccount::where('role', 'Administrator')->first();
        $kurikulum = Kurikulum::first();
        $sekolah = SchoolPartner::first();
        
        if (!$adminUser || !$kurikulum) {
            $this->command->warn('Admin user or kurikulum not found. Please run IndonesiaRegionSeeder first.');
            return;
        }

        // Create Learning Resources (Library Series, PPT, LKPD)
        echo "Membuat Learning Resources (Library Series, PPT, LKPD)...\n";
        $this->createLearningResources($adminUser, $kurikulum);

        // Create TKA Exams
        echo "Membuat TKA Exams...\n";
        $this->createTkaExams($adminUser, $kurikulum);

        // Create Practice Exams
        echo "Membuat Practice Exams...\n";
        $this->createPracticeExams($adminUser, $kurikulum);

        // Create Virtual Labs
        echo "Membuat Virtual Labs...\n";
        $this->createVirtualLabs($adminUser, $kurikulum);

        echo "\n=== Seed Learning Features Selesai ===\n";
    }

    private function createLearningResources(UserAccount $user, Kurikulum $kurikulum): void
    {
        $sekolah = SchoolPartner::first();
        $kelasList = Kelas::limit(6)->get(); // Get first 6 kelas
        $mapels = [
            'Matematika' => ['Aljabar', 'Geometri', 'Kalkulus', 'Statistika'],
            'IPA' => ['Fisika', 'Kimia', 'Biologi', 'Astronomi'],
            'Bahasa Indonesia' => ['Sastra', 'Tata Bahasa', 'Menulis', 'Membaca'],
            'Bahasa Inggris' => ['Grammar', 'Vocabulary', 'Reading', 'Writing'],
            'IPS' => ['Sejarah', 'Geografi', 'Ekonomi', 'Sosiologi'],
        ];

        $resourceTypes = ['library_series', 'ppt', 'lkpd'];
        
        $resources = [
            // Library Series (E-book)
            [
                'type' => 'library_series',
                'title' => 'Modul Lengkap Matematika SMA - Aljabar',
                'subject' => 'Matematika',
                'description' => 'Modul lengkap pembelajaran aljabar untuk SMA dengan contoh soal dan pembahasan detail.',
                'publisher' => 'Belajar Cerdas Press',
                'author' => 'Dr. Budi Santoso, M.Pd',
            ],
            [
                'type' => 'library_series',
                'title' => 'Ensiklopedia IPA - Fisika Dasar',
                'subject' => 'IPA',
                'description' => 'Ensiklopedia lengkap fisika dasar dengan ilustrasi dan eksperimen sederhana.',
                'publisher' => 'Belajar Cerdas Press',
                'author' => 'Prof. Siti Aminah, M.Si',
            ],
            [
                'type' => 'library_series',
                'title' => 'Kumpulan Cerpen Bahasa Indonesia',
                'subject' => 'Bahasa Indonesia',
                'description' => 'Kumpulan cerita pendek untuk meningkatkan kemampuan membaca dan analisis sastra.',
                'publisher' => 'Belajar Cerdas Press',
                'author' => 'Ayu Utami',
            ],
            
            // PPT Presentations
            [
                'type' => 'ppt',
                'title' => 'Presentasi Interaktif - Sistem Tata Surya',
                'subject' => 'IPA',
                'description' => 'Presentasi interaktif tentang sistem tata surya dengan animasi dan video.',
                'publisher' => 'Belajar Cerdas',
                'author' => 'Tim IPA Belajar Cerdas',
            ],
            [
                'type' => 'ppt',
                'title' => 'Presentasi Matematika - Persamaan Kuadrat',
                'subject' => 'Matematika',
                'description' => 'Presentasi langkah demi langkah menyelesaikan persamaan kuadrat.',
                'publisher' => 'Belajar Cerdas',
                'author' => 'Tim Matematika',
            ],
            [
                'type' => 'ppt',
                'title' => 'English Grammar - Tenses Complete Guide',
                'subject' => 'Bahasa Inggris',
                'description' => 'Presentasi lengkap tentang tenses dalam bahasa Inggris dengan contoh.',
                'publisher' => 'Belajar Cerdas',
                'author' => 'Sarah Johnson, M.Ed',
            ],
            
            // LKPD (Lembar Kerja Peserta Didik)
            [
                'type' => 'lkpd',
                'title' => 'LKPD Matematika - Fungsi Linear',
                'subject' => 'Matematika',
                'description' => 'Lembar kerja peserta didik untuk memahami fungsi linear dengan latihan soal.',
                'publisher' => 'Belajar Cerdas',
                'author' => 'Tim Matematika SMA',
            ],
            [
                'type' => 'lkpd',
                'title' => 'LKPD IPA - Reaksi Kimia Dasar',
                'subject' => 'IPA',
                'description' => 'Lembar kerja eksperimen reaksi kimia dasar dengan panduan lengkap.',
                'publisher' => 'Belajar Cerdas',
                'author' => 'Tim Kimia',
            ],
            [
                'type' => 'lkpd',
                'title' => 'LKPD Bahasa Indonesia - Analisis Teks Eksposisi',
                'subject' => 'Bahasa Indonesia',
                'description' => 'Lembar kerja untuk melatih kemampuan analisis teks eksposisi.',
                'publisher' => 'Belajar Cerdas',
                'author' => 'Tim Bahasa Indonesia',
            ],
        ];

        foreach ($resources as $index => $resource) {
            $kelas = $kelasList->random();
            $mapelName = $resource['subject'];
            
            // Create dummy file path (in production, actual files would be uploaded)
            $fileName = 'dummy-' . $resource['type'] . '-' . ($index + 1) . '.pdf';
            $filePath = 'public/learning-resources/' . $fileName;
            
            LearningResource::create([
                'user_id' => $user->id,
                'school_partner_id' => $sekolah?->id,
                'kurikulum_id' => $kurikulum->id,
                'kelas_id' => $kelas->id,
                'mapel_id' => Mapel::where('mata_pelajaran', $mapelName)->first()?->id,
                'resource_type' => $resource['type'],
                'title' => $resource['title'],
                'description' => $resource['description'],
                'publisher' => $resource['publisher'],
                'author' => $resource['author'],
                'subject' => $resource['subject'],
                'class_level' => $kelas->kelas,
                'file_path' => $filePath,
                'original_file_name' => $fileName,
                'file_extension' => 'pdf',
                'file_mime' => 'application/pdf',
                'file_size' => rand(100000, 5000000),
                'thumbnail_path' => 'public/learning-resources/thumbnails/thumb-' . ($index + 1) . '.jpg',
                'preview_pages' => 3,
                'tags' => [$resource['subject'], 'kelas ' . $kelas->kelas, $resource['type']],
                'status' => 'published',
                'is_active' => true,
            ]);
        }

        $this->command->info('Created ' . count($resources) . ' learning resources');
    }

    private function createTkaExams(UserAccount $user, Kurikulum $kurikulum): void
    {
        $tkaExams = [
            [
                'title' => 'Simulasi TKA Matematika & IPA #1',
                'description' => 'Simulasi Tes Kompetensi Akademik untuk mata pelajaran Matematika dan IPA. Cocok untuk persiapan UTBK-SBMPTN.',
                'subjects' => ['Matematika', 'IPA'],
                'difficulty' => 'hard',
                'duration_minutes' => 90,
                'passing_score' => 70,
            ],
            [
                'title' => 'Simulasi TKA Bahasa Indonesia & Inggris #1',
                'description' => 'Simulasi TKA untuk mata pelajaran Bahasa Indonesia dan Bahasa Inggris tingkat SMA.',
                'subjects' => ['Bahasa Indonesia', 'Bahasa Inggris'],
                'difficulty' => 'medium',
                'duration_minutes' => 60,
                'passing_score' => 65,
            ],
            [
                'title' => 'Simulasi TKA IPS Comprehensive',
                'description' => 'Simulasi lengkap TKA untuk mata pelajaran IPS (Ekonomi, Sosiologi, Geografi, Sejarah).',
                'subjects' => ['IPS'],
                'difficulty' => 'mixed',
                'duration_minutes' => 120,
                'passing_score' => 68,
            ],
            [
                'title' => 'Try Out TKA Saintek Full Package',
                'description' => 'Paket lengkap try out TKA untuk jurusan Saintek (Matematika, Fisika, Kimia, Biologi).',
                'subjects' => ['Matematika', 'IPA'],
                'difficulty' => 'hard',
                'duration_minutes' => 150,
                'passing_score' => 75,
            ],
            [
                'title' => 'Simulasi TKA Dasar - Pemula',
                'description' => 'Simulasi TKA tingkat dasar untuk siswa yang baru mulai belajar. Soal-soal fundamental.',
                'subjects' => ['Matematika', 'Bahasa Indonesia'],
                'difficulty' => 'easy',
                'duration_minutes' => 45,
                'passing_score' => 60,
            ],
        ];

        foreach ($tkaExams as $index => $exam) {
            $tkaExam = TkaExam::create([
                'user_id' => $user->id,
                'school_partner_id' => null,
                'title' => $exam['title'],
                'description' => $exam['description'],
                'subjects' => json_encode($exam['subjects']),
                'difficulty' => $exam['difficulty'],
                'passing_score' => $exam['passing_score'],
                'duration_minutes' => $exam['duration_minutes'],
                'total_questions' => rand(30, 50),
                'randomize_questions' => true,
                'show_results_immediately' => false,
                'start_date' => now(),
                'end_date' => now()->addMonths(6),
                'status' => 'published',
                'is_active' => true,
            ]);

            // Link some question banks to this TKA exam
            $questionBanks = LmsQuestionBank::inRandomOrder()->limit(10)->get();
            
            foreach ($questionBanks as $index => $qb) {
                TkaExamQuestion::create([
                    'tka_exam_id' => $tkaExam->id,
                    'lms_question_bank_id' => $qb->id,
                    'question_number' => $index + 1,
                    'points' => 1,
                    'subject_category' => $exam['subjects'][array_rand($exam['subjects'])],
                ]);
            }
        }

        $this->command->info('Created ' . count($tkaExams) . ' TKA exams');
    }

    private function createPracticeExams(UserAccount $user, Kurikulum $kurikulum): void
    {
        $sekolah = SchoolPartner::first();
        $kelasList = Kelas::limit(6)->get();
        
        $practiceExams = [
            // Daily Practice
            [
                'type' => 'daily_practice',
                'title' => 'Latihan Harian Matematika - Persamaan Linear',
                'description' => 'Latihan soal harian untuk menguasai persamaan linear satu variabel.',
                'subject' => 'Matematika',
                'difficulty' => 'easy',
                'duration_minutes' => 30,
                'passing_score' => 70,
            ],
            [
                'type' => 'daily_practice',
                'title' => 'Latihan Harian IPA - Gaya dan Gerak',
                'description' => 'Latihan soal tentang konsep gaya dan gerak dalam fisika.',
                'subject' => 'IPA',
                'difficulty' => 'medium',
                'duration_minutes' => 25,
                'passing_score' => 65,
            ],
            
            // Chapter Test
            [
                'type' => 'chapter_test',
                'title' => 'Ujian Bab 1 - Fungsi Kuadrat',
                'description' => 'Ujian komprehensif untuk bab fungsi kuadrat.',
                'subject' => 'Matematika',
                'difficulty' => 'medium',
                'duration_minutes' => 45,
                'passing_score' => 75,
            ],
            [
                'type' => 'chapter_test',
                'title' => 'Ujian Bab - Sistem Pencernaan Manusia',
                'description' => 'Ujian bab tentang sistem pencernaan manusia.',
                'subject' => 'IPA',
                'difficulty' => 'medium',
                'duration_minutes' => 40,
                'passing_score' => 70,
            ],
            
            // Midterm (UTS)
            [
                'type' => 'midterm',
                'title' => 'UTS Matematika Semester Ganjil',
                'description' => 'Ujian Tengah Semester Matematika untuk semester ganjil.',
                'subject' => 'Matematika',
                'difficulty' => 'medium',
                'duration_minutes' => 90,
                'passing_score' => 70,
            ],
            [
                'type' => 'midterm',
                'title' => 'UTS IPA Terpadu Semester Ganjil',
                'description' => 'Ujian Tengah Semester IPA Terpadu (Fisika, Kimia, Biologi).',
                'subject' => 'IPA',
                'difficulty' => 'medium',
                'duration_minutes' => 90,
                'passing_score' => 70,
            ],
            
            // Final (UAS)
            [
                'type' => 'final',
                'title' => 'UAS Matematika Semester Genap',
                'description' => 'Ujian Akhir Semester Matematika untuk semester genap.',
                'subject' => 'Matematika',
                'difficulty' => 'hard',
                'duration_minutes' => 120,
                'passing_score' => 75,
            ],
            [
                'type' => 'final',
                'title' => 'UAS Bahasa Indonesia Semester Genap',
                'description' => 'Ujian Akhir Semester Bahasa Indonesia.',
                'subject' => 'Bahasa Indonesia',
                'difficulty' => 'medium',
                'duration_minutes' => 90,
                'passing_score' => 70,
            ],
            
            // School Exam
            [
                'type' => 'school_exam',
                'title' => 'Ujian Sekolah Matematika 2025',
                'description' => 'Ujian Sekolah Matematika untuk kelulusan.',
                'subject' => 'Matematika',
                'difficulty' => 'hard',
                'duration_minutes' => 120,
                'passing_score' => 75,
            ],
            [
                'type' => 'school_exam',
                'title' => 'Ujian Sekolah IPA 2025',
                'description' => 'Ujian Sekolah IPA untuk kelulusan.',
                'subject' => 'IPA',
                'difficulty' => 'hard',
                'duration_minutes' => 120,
                'passing_score' => 75,
            ],
        ];

        foreach ($practiceExams as $index => $exam) {
            $kelas = $kelasList->random();
            $mapel = Mapel::where('mata_pelajaran', $exam['subject'])->first();
            
            $practiceExam = PracticeExam::create([
                'user_id' => $user->id,
                'school_partner_id' => $sekolah?->id,
                'kurikulum_id' => $kurikulum->id,
                'kelas_id' => $kelas->id,
                'mapel_id' => $mapel?->id,
                'title' => $exam['title'],
                'description' => $exam['description'],
                'exam_type' => $exam['type'],
                'difficulty' => $exam['difficulty'],
                'duration_minutes' => $exam['duration_minutes'],
                'total_questions' => rand(20, 40),
                'passing_score' => $exam['passing_score'],
                'randomize_questions' => true,
                'show_explanation' => true,
                'allow_retry' => true,
                'status' => 'published',
                'is_active' => true,
            ]);

            // Link some question banks
            $questionBanks = LmsQuestionBank::inRandomOrder()->limit(8)->get();
            
            foreach ($questionBanks as $qIndex => $qb) {
                PracticeExamQuestion::create([
                    'practice_exam_id' => $practiceExam->id,
                    'lms_question_bank_id' => $qb->id,
                    'question_number' => $qIndex + 1,
                    'points' => 1,
                ]);
            }
        }

        $this->command->info('Created ' . count($practiceExams) . ' practice exams');
    }

    private function createVirtualLabs(UserAccount $user, Kurikulum $kurikulum): void
    {
        $sekolah = SchoolPartner::first();
        $kelasList = Kelas::limit(6)->get();
        
        $virtualLabs = [
            [
                'title' => 'Eksperimen Virtual: Hukum Newton',
                'description' => 'Simulasi virtual untuk memahami hukum Newton tentang gerak dengan eksperimen interaktif.',
                'subject' => 'Fisika',
                'experiment_type' => 'Mekanika',
                'duration_seconds' => 300,
                'materials' => ['Troli', 'Timer', 'Pita ketik', 'Beban'],
                'objectives' => ['Memahami Hukum Newton I', 'Memahami Hukum Newton II', 'Memahami Hukum Newton III'],
                'safety_notes' => 'Pastikan semua peralatan terpasang dengan benar.',
            ],
            [
                'title' => 'Eksperimen Virtual: Reaksi Asam Basa',
                'description' => 'Simulasi reaksi kimia antara asam dan basa dengan indikator universal.',
                'subject' => 'Kimia',
                'experiment_type' => 'Kimia Analitik',
                'duration_seconds' => 240,
                'materials' => ['Larutan HCl', 'Larutan NaOH', 'Indikator universal', 'Gelas kimia'],
                'objectives' => ['Memahami konsep asam basa', 'Menggunakan indikator dengan benar'],
                'safety_notes' => 'Gunakan sarung tangan dan kacamata pelindung.',
            ],
            [
                'title' => 'Eksperimen Virtual: Sistem Pencernaan',
                'description' => 'Animasi 3D interaktif tentang proses pencernaan makanan pada manusia.',
                'subject' => 'Biologi',
                'experiment_type' => 'Anatomi',
                'duration_seconds' => 360,
                'materials' => ['Model 3D sistem pencernaan', 'Video animasi'],
                'objectives' => ['Memahami organ pencernaan', 'Memahami proses pencernaan'],
                'safety_notes' => null,
            ],
            [
                'title' => 'Eksperimen Virtual: Rangkaian Listrik',
                'description' => 'Simulasi rangkaian listrik seri dan paralel dengan pengukuran arus dan tegangan.',
                'subject' => 'Fisika',
                'experiment_type' => 'Listrik',
                'duration_seconds' => 280,
                'materials' => ['Baterai', 'Lampu', 'Kabel', 'Amperemeter', 'Voltmeter'],
                'objectives' => ['Memahami rangkaian seri', 'Memahami rangkaian paralel'],
                'safety_notes' => 'Hati-hati dengan korsleting.',
            ],
            [
                'title' => 'Eksperimen Virtual: Fotosintesis',
                'description' => 'Simulasi proses fotosintesis pada tumbuhan dengan berbagai variabel.',
                'subject' => 'Biologi',
                'experiment_type' => 'Fisiologi Tumbuhan',
                'duration_seconds' => 320,
                'materials' => ['Tanaman Hydrilla', 'Gelas kimia', 'Corong', 'Tabung reaksi'],
                'objectives' => ['Memahami proses fotosintesis', 'Faktor yang mempengaruhi fotosintesis'],
                'safety_notes' => null,
            ],
            [
                'title' => 'Eksperimen Virtual: Tabel Periodik',
                'description' => 'Eksplorasi interaktif tabel periodik unsur dengan sifat-sifat kimia.',
                'subject' => 'Kimia',
                'experiment_type' => 'Kimia Anorganik',
                'duration_seconds' => 200,
                'materials' => ['Tabel periodik interaktif', 'Database unsur'],
                'objectives' => ['Memahami struktur tabel periodik', 'Sifat unsur golongan'],
                'safety_notes' => null,
            ],
        ];

        foreach ($virtualLabs as $index => $lab) {
            $kelas = $kelasList->random();
            $mapel = Mapel::where('mata_pelajaran', $lab['subject'])->first();
            
            $fileName = 'virtual-lab-' . ($index + 1) . '.mp4';
            $filePath = 'public/virtual-labs/' . $fileName;
            
            VirtualLab::create([
                'user_id' => $user->id,
                'school_partner_id' => $sekolah?->id,
                'kurikulum_id' => $kurikulum->id,
                'kelas_id' => $kelas->id,
                'mapel_id' => $mapel?->id,
                'title' => $lab['title'],
                'description' => $lab['description'],
                'subject' => $lab['subject'],
                'experiment_type' => $lab['experiment_type'],
                'class_level' => $kelas->kelas,
                'video_path' => $filePath,
                'original_video_name' => $fileName,
                'video_extension' => 'mp4',
                'video_mime' => 'video/mp4',
                'video_size' => rand(10000000, 50000000),
                'duration_seconds' => $lab['duration_seconds'],
                'preview_duration' => 30,
                'materials_needed' => json_encode($lab['materials']),
                'learning_objectives' => json_encode($lab['objectives']),
                'safety_notes' => $lab['safety_notes'],
                'requires_supervision' => in_array($lab['subject'], ['Kimia', 'Fisika']),
                'tags' => [$lab['subject'], $lab['experiment_type'], 'virtual lab'],
                'thumbnail_path' => 'public/virtual-labs/thumbnails/thumb-' . ($index + 1) . '.jpg',
                'status' => 'published',
                'is_active' => true,
            ]);
        }

        $this->command->info('Created ' . count($virtualLabs) . ' virtual labs');
    }
}

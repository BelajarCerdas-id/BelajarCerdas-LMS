<?php

namespace Database\Seeders;

use App\Models\TkaExam;
use App\Models\TkaExamQuestion;
use App\Models\LmsQuestionBank;
use App\Models\UserAccount;
use Illuminate\Database\Seeder;

class TkaExamDummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = UserAccount::where('email', 'admin@belajarcerdas.id')->first();

        if (!$adminUser) {
            $this->command->error('Admin user not found. Please run IndonesiaRegionSeeder first.');
            return;
        }

        $tkaExams = [
            // TKA Matematika
            [
                'title' => 'Simulasi TKA Matematika Dasar #1',
                'description' => 'Latihan soal Matematika Dasar untuk persiapan UTBK-SBMPTN. Meliputi aljabar, fungsi, trigonometri, dan kalkulus dasar.',
                'subjects' => ['Matematika'],
                'difficulty' => 'medium',
                'duration_minutes' => 90,
                'passing_score' => 70,
                'total_questions' => 30,
            ],
            [
                'title' => 'Simulasi TKA Matematika IPA #1',
                'description' => 'Soal Matematika IPA dengan tingkat kesulitan tinggi. Mencakup kalkulus, aljabar linear, dan statistika.',
                'subjects' => ['Matematika'],
                'difficulty' => 'hard',
                'duration_minutes' => 120,
                'passing_score' => 75,
                'total_questions' => 40,
            ],
            
            // TKA Bahasa
            [
                'title' => 'Simulasi TKA Bahasa Indonesia #1',
                'description' => 'Latihan soal Bahasa Indonesia: pemahaman bacaan, ejaan, kalimat efektif, dan paragraf.',
                'subjects' => ['Bahasa Indonesia'],
                'difficulty' => 'medium',
                'duration_minutes' => 60,
                'passing_score' => 65,
                'total_questions' => 30,
            ],
            [
                'title' => 'Simulasi TKA Bahasa Inggris #1',
                'description' => 'Soal Bahasa Inggris untuk persiapan UTBK: reading comprehension, grammar, dan vocabulary.',
                'subjects' => ['Bahasa Inggris'],
                'difficulty' => 'medium',
                'duration_minutes' => 75,
                'passing_score' => 68,
                'total_questions' => 35,
            ],
            
            // TKA IPA
            [
                'title' => 'Simulasi TKA Fisika #1',
                'description' => 'Latihan soal Fisika: mekanika, termodinamika, gelombang, listrik magnet, dan fisika modern.',
                'subjects' => ['Fisika'],
                'difficulty' => 'hard',
                'duration_minutes' => 90,
                'passing_score' => 72,
                'total_questions' => 30,
            ],
            [
                'title' => 'Simulasi TKA Kimia #1',
                'description' => 'Soal Kimia lengkap: kimia dasar, kimia organik, stoikiometri, dan elektrokimia.',
                'subjects' => ['Kimia'],
                'difficulty' => 'hard',
                'duration_minutes' => 90,
                'passing_score' => 70,
                'total_questions' => 30,
            ],
            [
                'title' => 'Simulasi TKA Biologi #1',
                'description' => 'Latihan Biologi: sel, genetika, evolusi, ekologi, dan sistem tubuh manusia.',
                'subjects' => ['Biologi'],
                'difficulty' => 'medium',
                'duration_minutes' => 75,
                'passing_score' => 68,
                'total_questions' => 35,
            ],
            
            // TKA IPS
            [
                'title' => 'Simulasi TKA Ekonomi #1',
                'description' => 'Soal Ekonomi: mikroekonomi, makroekonomi, akuntansi, dan ekonomi internasional.',
                'subjects' => ['Ekonomi'],
                'difficulty' => 'medium',
                'duration_minutes' => 75,
                'passing_score' => 65,
                'total_questions' => 35,
            ],
            [
                'title' => 'Simulasi TKA Geografi #1',
                'description' => 'Latihan Geografi: geografi fisik, geografi manusia, dan analisis spasial.',
                'subjects' => ['Geografi'],
                'difficulty' => 'medium',
                'duration_minutes' => 60,
                'passing_score' => 65,
                'total_questions' => 30,
            ],
            [
                'title' => 'Simulasi TKA Sejarah #1',
                'description' => 'Soal Sejarah: sejarah Indonesia, sejarah dunia, dan analisis peristiwa bersejarah.',
                'subjects' => ['Sejarah'],
                'difficulty' => 'easy',
                'duration_minutes' => 60,
                'passing_score' => 60,
                'total_questions' => 30,
            ],
            
            // TKA Comprehensive (Mixed Subjects)
            [
                'title' => 'Try Out TKA Saintek Comprehensive',
                'description' => 'Try Out lengkap Saintek: Matematika IPA, Fisika, Kimia, dan Biologi. Simulasi penuh seperti UTBK.',
                'subjects' => ['Matematika', 'Fisika', 'Kimia', 'Biologi'],
                'difficulty' => 'hard',
                'duration_minutes' => 150,
                'passing_score' => 75,
                'total_questions' => 50,
            ],
            [
                'title' => 'Try Out TKA Soshum Comprehensive',
                'description' => 'Try Out lengkap Soshum: Matematika IPS, Ekonomi, Geografi, Sosiologi, dan Sejarah.',
                'subjects' => ['Matematika', 'Ekonomi', 'Geografi', 'Sejarah', 'Sosiologi'],
                'difficulty' => 'hard',
                'duration_minutes' => 150,
                'passing_score' => 73,
                'total_questions' => 50,
            ],
            
            // TKA Pemula (Easy Level)
            [
                'title' => 'TKA Pemula - Matematika Dasar',
                'description' => 'Soal Matematika tingkat dasar untuk pemula. Cocok untuk yang baru mulai belajar TKA.',
                'subjects' => ['Matematika'],
                'difficulty' => 'easy',
                'duration_minutes' => 45,
                'passing_score' => 60,
                'total_questions' => 20,
            ],
            [
                'title' => 'TKA Pemula - Bahasa Indonesia',
                'description' => 'Latihan dasar Bahasa Indonesia untuk persiapan TKA. Fokus pada pemahaman bacaan dan tata bahasa.',
                'subjects' => ['Bahasa Indonesia'],
                'difficulty' => 'easy',
                'duration_minutes' => 45,
                'passing_score' => 60,
                'total_questions' => 20,
            ],
        ];

        $questionBanks = LmsQuestionBank::count();
        
        if ($questionBanks === 0) {
            $this->command->warn('No question banks found. TKA exams will be created without questions.');
        }

        foreach ($tkaExams as $index => $exam) {
            $tkaExam = TkaExam::create([
                'user_id' => $adminUser->id,
                'school_partner_id' => null,
                'title' => $exam['title'],
                'description' => $exam['description'],
                'subjects' => json_encode($exam['subjects']),
                'difficulty' => $exam['difficulty'],
                'passing_score' => $exam['passing_score'],
                'duration_minutes' => $exam['duration_minutes'],
                'total_questions' => $exam['total_questions'],
                'randomize_questions' => true,
                'show_results_immediately' => true,
                'start_date' => now(),
                'end_date' => now()->addYear(),
                'status' => 'published',
                'is_active' => true,
            ]);

            // Link question banks if available
            if ($questionBanks > 0) {
                $questionsToLink = min($exam['total_questions'], $questionBanks);
                $selectedQuestions = LmsQuestionBank::inRandomOrder()
                    ->limit($questionsToLink)
                    ->get();

                foreach ($selectedQuestions as $qIndex => $qb) {
                    TkaExamQuestion::create([
                        'tka_exam_id' => $tkaExam->id,
                        'lms_question_bank_id' => $qb->id,
                        'question_number' => $qIndex + 1,
                        'points' => 1,
                        'subject_category' => $exam['subjects'][array_rand($exam['subjects'])],
                    ]);
                }
            }
        }

        $this->command->info('✅ Created ' . count($tkaExams) . ' TKA exams successfully!');
        $this->command->info('📊 Breakdown:');
        $this->command->info('   - Matematika: 3 exams');
        $this->command->info('   - Bahasa: 2 exams');
        $this->command->info('   - IPA (Fisika, Kimia, Biologi): 3 exams');
        $this->command->info('   - IPS (Ekonomi, Geografi, Sejarah): 3 exams');
        $this->command->info('   - Comprehensive: 2 exams');
        $this->command->info('   - Pemula: 2 exams');
    }
}

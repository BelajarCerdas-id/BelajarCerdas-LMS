<?php

namespace Database\Seeders;

use App\Models\LearningResource;
use App\Models\UserAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class LearningResourceDummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "=== Memulai Seed Learning Resources ===\n";

        // Get admin user
        $user = UserAccount::where('role', 'Administrator')->first();
        
        if (!$user) {
            $this->command->warn('Administrator not found. Please run IndonesiaRegionSeeder first.');
            return;
        }

        $resources = [
            // Library Series (E-book)
            [
                'type' => 'library_series',
                'title' => 'Modul Lengkap Matematika - Aljabar Linear',
                'subject' => 'Matematika',
                'class_level' => '10',
                'description' => 'Modul lengkap pembelajaran aljabar linear dengan contoh soal dan pembahasan detail untuk SMA kelas 10.',
                'author' => 'Dr. Budi Santoso, M.Pd',
            ],
            [
                'type' => 'library_series',
                'title' => 'Ensiklopedia IPA - Fisika Dasar',
                'subject' => 'IPA',
                'class_level' => '7',
                'description' => 'Ensiklopedia lengkap fisika dasar dengan ilustrasi dan penjelasan yang mudah dipahami.',
                'author' => 'Prof. Siti Aminah, M.Si',
            ],
            [
                'type' => 'library_series',
                'title' => 'Kumpulan Cerpen Bahasa Indonesia',
                'subject' => 'Bahasa Indonesia',
                'class_level' => '11',
                'description' => 'Kumpulan cerita pendek sastra Indonesia untuk meningkatkan kemampuan analisis sastra.',
                'author' => 'Ayu Utami',
            ],
            [
                'type' => 'library_series',
                'title' => 'English Grammar Complete Guide',
                'subject' => 'Bahasa Inggris',
                'class_level' => '12',
                'description' => 'Panduan lengkap tata bahasa Inggris dengan contoh-contoh aplikatif.',
                'author' => 'Sarah Johnson, M.Ed',
            ],
            
            // PPT Presentations
            [
                'type' => 'ppt',
                'title' => 'Presentasi Interaktif - Sistem Tata Surya',
                'subject' => 'IPA',
                'class_level' => '9',
                'description' => 'Presentasi interaktif tentang sistem tata surya dengan animasi dan video penjelasan.',
                'author' => 'Tim IPA Belajar Cerdas',
            ],
            [
                'type' => 'ppt',
                'title' => 'Matematika - Persamaan Kuadrat',
                'subject' => 'Matematika',
                'class_level' => '10',
                'description' => 'Presentasi langkah demi langkah menyelesaikan persamaan kuadrat dengan berbagai metode.',
                'author' => 'Tim Matematika',
            ],
            [
                'type' => 'ppt',
                'title' => 'Sejarah Kemerdekaan Indonesia',
                'subject' => 'IPS',
                'class_level' => '8',
                'description' => 'Presentasi lengkap tentang perjuangan kemerdekaan Indonesia dengan foto dan timeline.',
                'author' => 'Tim Sejarah',
            ],
            [
                'type' => 'ppt',
                'title' => 'Tenses dalam Bahasa Inggris',
                'subject' => 'Bahasa Inggris',
                'class_level' => '11',
                'description' => 'Presentasi lengkap 16 tenses dalam bahasa Inggris dengan contoh kalimat.',
                'author' => 'Mr. David Smith',
            ],
            
            // LKPD (Lembar Kerja Peserta Didik)
            [
                'type' => 'lkpd',
                'title' => 'LKPD Matematika - Fungsi Linear',
                'subject' => 'Matematika',
                'class_level' => '10',
                'description' => 'Lembar kerja peserta didik untuk memahami fungsi linear dengan latihan soal bertingkat.',
                'author' => 'Tim Matematika SMA',
            ],
            [
                'type' => 'lkpd',
                'title' => 'LKPD IPA - Reaksi Kimia Dasar',
                'subject' => 'IPA',
                'class_level' => '9',
                'description' => 'Lembar kerja eksperimen reaksi kimia dasar dengan panduan lengkap dan aman.',
                'author' => 'Tim Kimia',
            ],
            [
                'type' => 'lkpd',
                'title' => 'LKPD Bahasa Indonesia - Teks Eksposisi',
                'subject' => 'Bahasa Indonesia',
                'class_level' => '11',
                'description' => 'Lembar kerja untuk melatih kemampuan analisis dan menulis teks eksposisi.',
                'author' => 'Tim Bahasa Indonesia',
            ],
            [
                'type' => 'lkpd',
                'title' => 'LKPD IPS - Peta dan Atlas',
                'subject' => 'IPS',
                'class_level' => '7',
                'description' => 'Lembar kerja pembelajaran tentang cara membaca peta dan menggunakan atlas.',
                'author' => 'Tim Geografi',
            ],
        ];

        foreach ($resources as $index => $resource) {
            // Create dummy file path
            $fileName = 'dummy-' . $resource['type'] . '-' . ($index + 1) . '.pdf';
            $filePath = 'public/learning-resources/' . $fileName;
            
            LearningResource::create([
                'user_id' => $user->id,
                'title' => $resource['title'],
                'resource_type' => $resource['type'],
                'subject' => $resource['subject'],
                'class_level' => $resource['class_level'],
                'description' => $resource['description'],
                'author' => $resource['author'],
                'file_path' => $filePath,
                'file_name' => $fileName,
                'file_size' => rand(100000, 5000000), // Random size 100KB - 5MB
                'thumbnail_path' => 'public/learning-resources/thumbnails/thumb-' . ($index + 1) . '.jpg',
                'preview_pages' => rand(3, 5),
                'status' => 'published',
            ]);
        }

        $this->command->info('Created ' . count($resources) . ' learning resources');
        $this->command->info('  - Library Series: ' . count(array_filter($resources, fn($r) => $r['type'] === 'library_series')));
        $this->command->info('  - PPT: ' . count(array_filter($resources, fn($r) => $r['type'] === 'ppt')));
        $this->command->info('  - LKPD: ' . count(array_filter($resources, fn($r) => $r['type'] === 'lkpd')));
        
        echo "\n=== Seed Learning Resources Selesai ===\n";
    }
}

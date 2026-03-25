<?php

namespace Database\Seeders;

use App\Models\PublicLibrary;
use App\Models\UserAccount;
use Illuminate\Database\Seeder;

class PublicLibraryDummySeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = UserAccount::query()
            ->whereRaw('LOWER(role) IN (?, ?)', ['administrator', 'admin'])
            ->first();

        $fallbackUser = $adminUser ?? UserAccount::query()->first();

        if (!$fallbackUser) {
            $this->command?->warn('Tidak ada user pada tabel user_accounts. Dummy Public Library tidak dibuat.');

            return;
        }

        $authorName = $this->resolveAuthorName($fallbackUser);

        $thumbnailDir = public_path('uploads/public-library/thumbnails');
        $fileDir = public_path('uploads/public-library/files');

        if (!is_dir($thumbnailDir)) {
            mkdir($thumbnailDir, 0755, true);
        }

        if (!is_dir($fileDir)) {
            mkdir($fileDir, 0755, true);
        }

        // PNG 1x1 (valid image) untuk thumbnail dummy.
        $pngBinary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIHWP4////fwAJ+wP9KobjigAAAABJRU5ErkJggg==');

        $dummyItems = [
            [
                'title' => 'Modul Matematika Dasar Kelas 7',
                'publisher' => 'Belajar Cerdas Press',
                'subject' => 'Matematika',
                'class_level' => '7',
                'description' => 'Materi penguatan konsep bilangan, operasi hitung, dan latihan soal dasar untuk kelas 7.',
            ],
            [
                'title' => 'Ringkasan IPA Sistem Tata Surya',
                'publisher' => 'Belajar Cerdas Press',
                'subject' => 'IPA',
                'class_level' => '7',
                'description' => 'Rangkuman sistem tata surya lengkap dengan penjelasan planet dan kuis evaluasi singkat.',
            ],
            [
                'title' => 'Latihan Soal Bahasa Indonesia Teks Eksplanasi',
                'publisher' => 'Belajar Cerdas Press',
                'subject' => 'Bahasa Indonesia',
                'class_level' => '8',
                'description' => 'Kumpulan latihan memahami struktur, kaidah kebahasaan, dan analisis teks eksplanasi.',
            ],
            [
                'title' => 'Worksheet Bahasa Inggris Daily Activity',
                'publisher' => 'Belajar Cerdas Press',
                'subject' => 'Bahasa Inggris',
                'class_level' => '8',
                'description' => 'Worksheet kosakata dan grammar untuk topik aktivitas harian dengan latihan terarah.',
            ],
            [
                'title' => 'Materi IPS Interaksi Sosial',
                'publisher' => 'Belajar Cerdas Press',
                'subject' => 'IPS',
                'class_level' => '9',
                'description' => 'Pembahasan konsep interaksi sosial, faktor pendorong, serta studi kasus di lingkungan sekitar.',
            ],
            [
                'title' => 'Panduan Informatika Berpikir Komputasional',
                'publisher' => 'Belajar Cerdas Press',
                'subject' => 'Informatika',
                'class_level' => '9',
                'description' => 'Panduan langkah berpikir komputasional mulai dari dekomposisi hingga penyusunan algoritma sederhana.',
            ],
            [
                'title' => 'Modul PJOK Kebugaran Jasmani',
                'publisher' => 'Belajar Cerdas Press',
                'subject' => 'PJOK',
                'class_level' => '10',
                'description' => 'Modul latihan kebugaran jasmani dan panduan kegiatan fisik aman untuk siswa SMA.',
            ],
            [
                'title' => 'Pengantar Ekonomi Permintaan dan Penawaran',
                'publisher' => 'Belajar Cerdas Press',
                'subject' => 'Ekonomi',
                'class_level' => '11',
                'description' => 'Pengantar konsep permintaan-penawaran disertai contoh grafik, kasus pasar, dan latihan analisis.',
            ],
            [
                'title' => 'Modul Fisika Gerak Lurus',
                'publisher' => 'Belajar Cerdas Press',
                'subject' => 'Fisika',
                'class_level' => '10',
                'description' => 'Materi gerak lurus beraturan dan berubah beraturan dengan contoh soal dan pembahasan.',
            ],
            [
                'title' => 'Catatan Kimia Struktur Atom',
                'publisher' => 'Belajar Cerdas Press',
                'subject' => 'Kimia',
                'class_level' => '10',
                'description' => 'Ringkasan struktur atom, konfigurasi elektron, dan latihan soal tingkat dasar-menengah.',
            ],
        ];

        $subjects = [
            'Matematika',
            'IPA',
            'Bahasa Indonesia',
            'Bahasa Inggris',
            'IPS',
            'Informatika',
            'PJOK',
            'Ekonomi',
            'Fisika',
            'Kimia',
            'Biologi',
            'Sejarah',
        ];

        $classLevels = ['7', '8', '9', '10', '11', '12'];
        $topics = [
            'Latihan Inti',
            'Rangkuman Konsep',
            'Bank Soal',
            'Pembahasan Cepat',
            'Kuis Harian',
            'Penguatan Materi',
        ];

        $targetTotalItems = 36;
        for ($i = count($dummyItems) + 1; $i <= $targetTotalItems; $i++) {
            $subject = $subjects[($i - 1) % count($subjects)];
            $classLevel = $classLevels[($i - 1) % count($classLevels)];
            $topic = $topics[($i - 1) % count($topics)];

            $dummyItems[] = [
                'title' => "Paket {$subject} {$topic} Seri {$i}",
                'publisher' => 'Belajar Cerdas Press',
                'subject' => $subject,
                'class_level' => $classLevel,
                'description' => "Materi {$subject} {$topic} untuk kelas {$classLevel}, dilengkapi ringkasan konsep dan latihan terstruktur.",
            ];
        }

        foreach ($dummyItems as $index => $item) {
            $number = $index + 1;
            $thumbnailFileName = "dummy-library-{$number}.png";
            $fileName = "dummy-library-{$number}.pdf";

            $thumbnailFullPath = $thumbnailDir . DIRECTORY_SEPARATOR . $thumbnailFileName;
            $fileFullPath = $fileDir . DIRECTORY_SEPARATOR . $fileName;

            if (!file_exists($thumbnailFullPath)) {
                file_put_contents($thumbnailFullPath, $pngBinary);
            }

            $this->createSimplePdf(
                $fileFullPath,
                [
                    'Dummy Public Library',
                    "Judul: {$item['title']}",
                    "Author: {$authorName}",
                    "Mapel: {$item['subject']}",
                    "Kelas: {$item['class_level']}",
                ]
            );

            PublicLibrary::updateOrCreate(
                ['title' => $item['title']],
                [
                    'user_id' => $fallbackUser->id,
                    'publisher' => $authorName,
                    'subject' => $item['subject'],
                    'class_level' => $item['class_level'],
                    'description' => $item['description'] ?? null,
                    'thumbnail_path' => 'uploads/public-library/thumbnails/' . $thumbnailFileName,
                    'file_path' => 'uploads/public-library/files/' . $fileName,
                    'original_file_name' => $fileName,
                    'file_extension' => 'pdf',
                    'file_mime' => 'application/pdf',
                    'file_size' => filesize($fileFullPath) ?: 0,
                ]
            );
        }

        $this->command?->info('Dummy Public Library berhasil dibuat/diupdate. Total item: ' . count($dummyItems));
    }

    private function resolveAuthorName(UserAccount $user): string
    {
        $name = trim((string) (
            $user->OfficeProfile?->nama_lengkap
            ?? $user->SchoolStaffProfile?->nama_lengkap
            ?? $user->StudentProfile?->nama_lengkap
            ?? ''
        ));

        if ($name !== '') {
            return $name;
        }

        $email = trim((string) ($user->email ?? ''));

        if ($email !== '') {
            return $email;
        }

        return 'Unknown Author';
    }

    private function createSimplePdf(string $path, array $lines): void
    {
        $fontSize = 14;
        $lineHeight = 24;
        $startX = 60;
        $startY = 780;

        $contentCommands = ["BT /F1 {$fontSize} Tf"];
        foreach ($lines as $index => $line) {
            $safeLine = $this->escapePdfText((string) $line);
            $y = $startY - ($index * $lineHeight);
            $contentCommands[] = "{$startX} {$y} Td ({$safeLine}) Tj";
        }
        $contentCommands[] = 'ET';

        $stream = implode("\n", $contentCommands);

        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            4 => "<< /Length " . strlen($stream) . " >>\nstream\n{$stream}\nendstream",
            5 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $number => $content) {
            $offsets[$number] = strlen($pdf);
            $pdf .= "{$number} 0 obj\n{$content}\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        file_put_contents($path, $pdf);
    }

    private function escapePdfText(string $text): string
    {
        return str_replace(
            ['\\', '(', ')'],
            ['\\\\', '\\(', '\\)'],
            $text
        );
    }
}

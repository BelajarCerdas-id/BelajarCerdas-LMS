# Learning Features Documentation

## Overview
Dokumentasi lengkap untuk fitur-fitur pembelajaran baru di BelajarCerdas LMS:
- Learning Resources (Library Series, PPT, LKPD)
- Simulasi Soal TKA
- Koleksi Latihan Soal/Ujian
- Virtual Lab

## Fitur yang Dibuat

### 1. Learning Resources (Library Series, PPT, LKPD)

**Deskripsi:**
Platform untuk mengakses materi pembelajaran dalam berbagai format:
- **Library Series**: E-book/modul pembelajaran lengkap
- **PPT**: Presentasi interaktif untuk pembelajaran
- **LKPD**: Lembar Kerja Peserta Didik untuk latihan

**Fitur Utama:**
- Preview halaman terbatas (default 3 halaman)
- Download resource lengkap
- Filter berdasarkan tipe, kelas, dan mata pelajaran
- Search functionality
- Related resources recommendation

**Routes:**
```php
GET  /learning-resources              - Index (list semua resource)
GET  /learning-resources/{id}         - Show detail resource
GET  /learning-resources/{id}/preview - Preview resource
GET  /learning-resources/{id}/download - Download resource
```

**Database:**
- Table: `learning_resources`
- Resource types: `library_series`, `ppt`, `lkpd`
- Supports: thumbnail, file preview, tags, metadata

---

### 2. Simulasi Soal TKA (Tes Kompetensi Akademik)

**Deskripsi:**
Simulasi ujian untuk persiapan UTBK-SBMPTN dengan soal-soal TKA yang mengukur pengetahuan dan pemahaman mendalam dalam mata pelajaran tertentu.

**Fitur Utama:**
- Multiple subjects (Matematika, IPA, Bahasa, IPS)
- Timer countdown real-time
- Randomize questions
- Auto-submit ketika waktu habis
- Immediate/delayed results
- Multiple attempts tracking
- Score calculation dengan passing grade
- History of attempts

**Exam Flow:**
1. Student memilih TKA exam
2. Start exam → sistem membuat attempt baru
3. Mengerjakan soal dengan timer
4. Save answer (auto-save)
5. Submit exam (manual atau auto)
6. View results dengan analisis

**Routes:**
```php
GET  /tka-exams                        - Index TKA exams
GET  /tka-exams/{id}                   - Detail exam
GET  /tka-exams/{id}/start             - Start exam
GET  /tka-exams/attempts/{id}/take     - Take exam
POST /tka-exams/attempts/{id}/save-answer - Save answer
POST /tka-exams/attempts/{id}/submit   - Submit exam
GET  /tka-exams/attempts/{id}/result   - View results
```

**Database:**
- `tka_exams` - Exam definitions
- `tka_exam_questions` - Questions in exam
- `tka_exam_attempts` - Student attempts
- `tka_exam_answers` - Student answers

---

### 3. Koleksi Latihan Soal/Ujian (Non-TKA)

**Deskripsi:**
Platform latihan soal dan ujian untuk pembelajaran sehari-hari dengan berbagai tipe:
- **Latihan Harian** (Daily Practice)
- **Ujian Bab** (Chapter Test)
- **UTS** (Midterm Exam)
- **UAS** (Final Exam)
- **Ujian Sekolah** (School Exam)

**Fitur Utama:**
- Multiple exam types
- Per bab/sub-bab organization
- Timer (optional - bisa tanpa waktu)
- Show explanation after submit
- Allow retry (configurable)
- Best score tracking
- Progress tracking
- Question explanation view

**Exam Flow:**
1. Browse latihan soal by type/kelas/mapel
2. View detail exam
3. Start exam → create attempt
4. Kerjakan soal (dengan/tanpa timer)
5. Submit dan lihat hasil
6. Review explanation (jika enabled)
7. Retry jika belum passing

**Routes:**
```php
GET  /practice-exams                        - Index practice exams
GET  /practice-exams/{id}                   - Detail exam
GET  /practice-exams/{id}/start             - Start exam
GET  /practice-exams/attempts/{id}/take     - Take exam
POST /practice-exams/attempts/{id}/save-answer - Save answer
POST /practice-exams/attempts/{id}/submit   - Submit exam
GET  /practice-exams/attempts/{id}/result   - View results
GET  /practice-exams/attempts/{id}/questions/{qid}/explanation - View explanation
```

**Database:**
- `practice_exams` - Exam definitions
- `practice_exam_questions` - Questions in exam
- `practice_exam_attempts` - Student attempts
- `practice_exam_answers` - Student answers

---

### 4. Koleksi Virtual Lab

**Deskripsi:**
Platform video pembelajaran untuk eksperimen sains virtual (Virtual Lab) dengan materi IPA (Fisika, Kimia, Biologi).

**Fitur Utama:**
- Video streaming dengan preview (30 detik default)
- Progress tracking (auto-save position)
- Learning objectives
- Materials needed list
- Safety notes
- Rating & review system
- Related labs recommendation
- Experiment categorization

**Video Player Features:**
- Play/pause with progress tracking
- Auto-save watched duration
- Mark as completed when finished
- Resume from last position

**Routes:**
```php
GET  /virtual-labs                        - Index virtual labs
GET  /virtual-labs/{id}                   - Detail lab with video player
GET  /virtual-labs/{id}/preview           - Preview video
POST /virtual-labs/{id}/track-progress    - Track viewing progress
POST /virtual-labs/{id}/review            - Submit review
```

**Database:**
- `virtual_labs` - Lab definitions
- `virtual_lab_views` - Student viewing progress
- `virtual_lab_reviews` - Student reviews

---

## Installation & Setup

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Seed Dummy Data
```bash
php artisan db:seed --class=LearningFeaturesSeeder
```

Atau jika fresh install:
```bash
php artisan migrate:fresh --seed
```

### 3. Access Features

Login sebagai siswa dan akses melalui sidebar menu:
- **Learning Resources** → Menu "Learning Resources"
- **Simulasi TKA** → Menu "Simulasi TKA"
- **Latihan Soal** → Menu "Latihan Soal"
- **Virtual Lab** → Menu "Virtual Lab"

---

## File Structure

```
app/
├── Http/
│   └── Controllers/
│       ├── LearningResourceController.php
│       ├── TkaExamController.php
│       ├── PracticeExamController.php
│       └── VirtualLabController.php
├── Models/
│   ├── LearningResource.php
│   ├── TkaExam.php
│   ├── TkaExamQuestion.php
│   ├── TkaExamAttempt.php
│   ├── TkaExamAnswer.php
│   ├── PracticeExam.php
│   ├── PracticeExamQuestion.php
│   ├── PracticeExamAttempt.php
│   ├── PracticeExamAnswer.php
│   ├── VirtualLab.php
│   ├── VirtualLabView.php
│   └── VirtualLabReview.php
└── ...

database/
├── migrations/
│   ├── 2026_03_24_100000_create_learning_resources_table.php
│   ├── 2026_03_24_110000_create_tka_exams_tables.php
│   ├── 2026_03_24_120000_create_practice_exams_tables.php
│   └── 2026_03_24_130000_create_virtual_labs_table.php
└── seeders/
    └── LearningFeaturesSeeder.php

resources/
└── views/
    ├── learning-resources/
    │   ├── index.blade.php
    │   └── show.blade.php
    ├── tka-exams/
    │   ├── index.blade.php
    │   ├── show.blade.php
    │   ├── take.blade.php
    │   └── result.blade.php
    ├── practice-exams/
    │   ├── index.blade.php
    │   ├── show.blade.php
    │   ├── take.blade.php
    │   └── result.blade.php
    └── virtual-labs/
        ├── index.blade.php
        └── show.blade.php

routes/
└── web.php (routes added)
```

---

## Data Dummy yang Dibuat

### Learning Resources (9 items)
- 3 Library Series (E-book)
- 3 PPT Presentations
- 3 LKPD

### TKA Exams (5 exams)
- Simulasi TKA Matematika & IPA
- Simulasi TKA Bahasa
- Simulasi TKA IPS
- Try Out TKA Saintek
- Simulasi TKA Dasar

### Practice Exams (10 exams)
- 2 Latihan Harian
- 2 Ujian Bab
- 2 UTS
- 2 UAS
- 2 Ujian Sekolah

### Virtual Labs (6 labs)
- 2 Fisika (Hukum Newton, Rangkaian Listrik)
- 2 Kimia (Asam Basa, Tabel Periodik)
- 2 Biologi (Sistem Pencernaan, Fotosintesis)

---

## API Endpoints (AJAX)

### Save Answer (TKA & Practice)
```javascript
POST /tka-exams/attempts/{id}/save-answer
POST /practice-exams/attempts/{id}/save-answer

Body: {
    "question_id": 123,
    "answer": "A"
}

Response: { "success": true }
```

### Track Video Progress
```javascript
POST /virtual-labs/{id}/track-progress

Body: {
    "watched_duration": 120,
    "last_position": 120,
    "is_completed": false
}

Response: { "success": true, "view": {...} }
```

---

## Security & Authorization

- Semua routes dilindungi middleware `AuthMiddleware`
- Students hanya bisa akses exam mereka sendiri
- Preview hanya untuk published resources
- Download hanya untuk published resources
- Answer saving validated berdasarkan student_id

---

## Future Enhancements

### Planned Features:
1. **Leaderboard** - Ranking berdasarkan score TKA/Practice
2. **Analytics Dashboard** - Progress tracking per student
3. **Question Bank Integration** - Auto-generate exam from question bank
4. **Certificate Generation** - E-certificate setelah lulus exam
5. **Discussion Forum** - Tanya jawab per exam/resource
6. **Bookmarks** - Simpan resource favorit
7. **Notes** - Catat penting saat preview
8. **Offline Mode** - Download untuk akses offline
9. **Mobile App** - React Native / Flutter app
10. **Adaptive Learning** - Rekomendasi based on performance

---

## Troubleshooting

### Common Issues:

**1. "Route not found"**
```bash
php artisan route:clear
php artisan route:cache
```

**2. "View not found"**
```bash
php artisan view:clear
```

**3. "Class not found"**
```bash
composer dump-autoload
```

**4. Migration errors**
```bash
php artisan migrate:rollback
php artisan migrate
```

**5. Seeder errors**
```bash
php artisan db:seed --class=LearningFeaturesSeeder --force
```

---

## Credits

Developed for BelajarCerdas LMS - Indonesian Learning Management System

**Features by:**
- Learning Resources Management
- TKA Exam System
- Practice Exam System  
- Virtual Lab Platform

**Tech Stack:**
- Laravel 11
- Blade Templates
- TailwindCSS
- Vanilla JavaScript
- SQLite/MySQL

---

## Support

Untuk pertanyaan atau issue, silakan hubungi tim development atau buat issue tracker.

**Last Updated:** March 24, 2025

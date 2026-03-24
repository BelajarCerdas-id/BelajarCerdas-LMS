# Indonesia Region Dummy Data Seeder

## Overview
Seeder ini membuat data dummy lengkap untuk region Indonesia dengan struktur sekolah, guru, siswa, dan kurikulum.

## Cara Menggunakan

### Fresh Migration + Seed
```bash
php artisan migrate:fresh --seed
```

### Atau Seed Saja
```bash
php artisan db:seed --class=IndonesiaRegionSeeder
```

## Data yang Dibuat

### Statistik
- **Total Users**: ~570 accounts
- **Total Sekolah**: 50 schools
- **Total Guru**: ~196 teachers
- **Total Siswa**: ~372 students
- **Total Kelas**: ~286 classes
- **Total Mapel**: ~1,200 subjects
- **Total Jurusan SMK**: ~35 majors

### Admin Account
- **Email**: `admin@belajarcerdas.id`
- **Password**: `password123`
- **Role**: `Administrator`

## Struktur Data

### 1. Provinsi (14 Provinsi)
- DKI Jakarta
- Jawa Barat
- Jawa Tengah
- Jawa Timur
- Banten
- DI Yogyakarta
- Sumatera Utara
- Sumatera Barat
- Sumatera Selatan
- Riau
- Lampung
- Bali
- Kalimantan Timur
- Sulawesi Selatan

### 2. Jenjang Sekolah
- **SD** (Sekolah Dasar) - Kelas 1-6
- **SMP** (Sekolah Menengah Pertama) - Kelas 7-9
- **SMA** (Sekolah Menengah Atas) - Kelas 10-12
- **SMK** (Sekolah Menengah Kejuruan) - Kelas 10-12

### 3. Kurikulum
- **Kurikulum Merdeka** (KURMER)
  - Fase A (Kelas 1-2)
  - Fase B (Kelas 3-4)
  - Fase C (Kelas 5-6)
  - Fase D (Kelas 7-9)
  - Fase E (Kelas 10)
  - Fase F (Kelas 11-12)
- **Kurikulum 2013** (K13)

### 4. Mata Pelajaran

**SD:**
- Matematika, Bahasa Indonesia, IPA, IPS, Bahasa Inggris, PJOK, Seni Budaya

**SMP:**
- Matematika, Bahasa Indonesia, Bahasa Inggris, IPA, IPS, PJOK, Informatika

**SMA:**
- Matematika, Bahasa Indonesia, Bahasa Inggris, Fisika, Kimia, Biologi, Ekonomi, Sosiologi, Sejarah

**SMK:**
- Matematika, Bahasa Indonesia, Bahasa Inggris, Produktif, PKK, IPA, IPS

### 5. Jurusan SMK
- Teknik Komputer dan Jaringan
- Akuntansi
- Perkantoran
- Pemasaran
- Teknik Kendaraan Ringan
- Teknik Sepeda Motor
- Tata Boga
- Tata Busana
- Farmasi
- Keperawatan
- Multimedia
- Rekayasa Perangkat Lunak

## Fitur Data Dummy

### Users
- Role dengan kapitalisasi yang benar (`Administrator`, `Guru`, `Siswa`)
- Email unik dengan format Indonesian
- Nomor HP Indonesian (08xx-xxxx-xxxx)
- Password terenkripsi

### Sekolah
- Nama sekolah realistis dengan nomor urut
- NPSN unik (10000001 - 10000050)
- Distribusi merata di berbagai kota

### Guru & Siswa
- Nama Indonesia umum (Muhammad, Siti, Dewi, dll)
- NIK 16 digit
- Email personal
- Enrollment type (B2B/B2G)

### Kelas & Mapel
- Kelas per jenjang sekolah
- Mata pelajaran sesuai jenjang
- Teacher-Mapel assignments
- Tahun ajaran 2025/2026

## File Terkait

- **Seeder**: `database/seeders/IndonesiaRegionSeeder.php`
- **Database Seeder**: `database/seeders/DatabaseSeeder.php`

## Catatan

1. Seeder ini sudah terintegrasi dengan `DatabaseSeeder.php`
2. Data yang dibuat sesuai dengan struktur migrations yang ada
3. Roles menggunakan kapitalisasi yang sesuai dengan views (`Administrator`, `Guru`, `Siswa`)
4. Untuk reset data, gunakan `php artisan migrate:fresh --seed`

## Troubleshooting

### "You do not have access to this dashboard"
Pastikan role user menggunakan kapitalisasi yang benar:
- ✅ `Administrator`
- ✅ `Guru`
- ✅ `Siswa`
- ❌ `administrator`
- ❌ `guru`
- ❌ `siswa`

### Email Duplicate Error
Seeder sudah menggunakan `uniqid()` untuk memastikan email unik. Jika masih ada error, wipe database terlebih dahulu.

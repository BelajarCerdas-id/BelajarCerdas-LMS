<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TeacherContentController extends Controller
{
    /**
     * Menampilkan halaman Dashboard / Content Management Guru
     */
    public function teacherContentManagement($role, $schoolName, $schoolId)
    {
        $user = Auth::user();
        
        // Proteksi tambahan: Pastikan yang akses benar-benar Guru
        if ($user->role !== 'Guru') {
            abort(403, 'Akses Ditolak. Halaman ini khusus untuk Guru.');
        }

        $teacherName = $user->name; // Sesuaikan jika kamu pakai $user->TeacherProfile->nama_lengkap

        // =========================================================
        // 1. QUICK STATS CARDS (Statistik Cepat)
        // =========================================================
        
        // Menghitung total kelas yang diajar oleh guru ini
        $totalKelas = DB::table('lesson_schedules')
            ->where('school_partner_id', $schoolId)
            ->where('teacher_name', $teacherName)
            ->distinct('class_name')
            ->count('class_name');

        // Menghitung modul yang diupload guru ini (sesuaikan nama tabelmu)
        $totalModul = DB::table('modules') // ganti 'modules' dengan tabelmu
            ->where('school_partner_id', $schoolId)
            ->where('teacher_id', $user->id)
            ->count();

        // Menghitung tugas yang butuh dinilai (sesuaikan logika tabelmu)
        $tugasPending = DB::table('task_submissions') // ganti 'task_submissions' dengan tabelmu
            ->join('tasks', 'task_submissions.task_id', '=', 'tasks.id')
            ->where('tasks.teacher_id', $user->id)
            ->where('task_submissions.status', 'belum_dinilai') 
            ->count();

        // =========================================================
        // 2. JADWAL MENGAJAR HARI INI
        // =========================================================
        $hariInggris = date('l');
        $mapHari = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
        ];
        $hariIni = $mapHari[$hariInggris] ?? 'Senin';

        $dbSchedules = \App\Models\LessonSchedule::where('school_partner_id', $schoolId)
            ->where('teacher_name', $teacherName)
            ->where('day_of_week', $hariIni)
            ->where('status', 'published')
            ->orderBy('start_time', 'asc')
            ->get();

        $jadwalMengajar = [];
        foreach ($dbSchedules as $jadwal) {
            $jadwalMengajar[] = (object)[
                'id'          => $jadwal->id,
                'jam_mulai'   => substr($jadwal->start_time, 0, 5),
                'jam_selesai' => substr($jadwal->end_time, 0, 5),
                'mapel'       => $jadwal->subject_name,
                'kelas'       => $jadwal->class_name,
                'ruangan'     => $jadwal->room_name ?? 'Ruang Kelas', 
            ];
        }

        // =========================================================
        // 3. TUGAS MENUNGGU PENILAIAN (Daftar List)
        // =========================================================
        $tugasMenunggu = DB::table('tasks')
            ->where('teacher_id', $user->id)
            ->where('school_partner_id', $schoolId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($task) {
                $jumlahPengumpul = DB::table('task_submissions')
                    ->where('task_id', $task->id)
                    ->count();

                return (object)[
                    'id'               => $task->id,
                    'nama_tugas'       => $task->title ?? 'Tugas',
                    'kelas'            => $task->class_name ?? '-',
                    'jumlah_pengumpul' => $jumlahPengumpul
                ];
            })
            ->filter(function ($task) {
                return $task->jumlah_pengumpul > 0;
            });

        // =========================================================
        // 4. AGENDA KALENDER & LIBUR NASIONAL
        // =========================================================
        $currentMonth = date('m');
        $currentYear = date('Y');
        $allAgenda = [];
        
        $dbEvents = \App\Models\AcademicCalendar::where('school_partner_id', $schoolId)
            ->where('status', 'published') 
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->get();

        foreach ($dbEvents as $ev) {
            $allAgenda[] = [
                'date'  => $ev->date,
                'title' => $ev->title,
                'color' => $ev->color
            ];
        }

        // Libur Nasional & Cuti Bersama Lengkap Tahun 2026
        $nationalHolidays = [
            "2026-01-01" => "Tahun Baru 2026 Masehi", "2026-01-16" => "Isra Mikraj Nabi Muhammad SAW",
            "2026-02-16" => "Cuti Bersama Imlek", "2026-02-17" => "Tahun Baru Imlek 2577 Kongzili", 
            "2026-03-18" => "Cuti Bersama Nyepi", "2026-03-19" => "Hari Suci Nyepi",
            "2026-03-20" => "Cuti Bersama Idul Fitri", "2026-03-21" => "Idul Fitri 1447 Hijriah", 
            "2026-03-22" => "Idul Fitri 1447 Hijriah", "2026-03-23" => "Cuti Bersama Idul Fitri", 
            "2026-03-24" => "Cuti Bersama Idul Fitri", "2026-04-03" => "Wafat Yesus Kristus", 
            "2026-04-05" => "Hari Paskah", "2026-05-01" => "Hari Buruh Internasional", 
            "2026-05-14" => "Kenaikan Yesus Kristus", "2026-05-15" => "Cuti Bersama Kenaikan Yesus Kristus",
            "2026-05-27" => "Idul Adha 1447 Hijriah", "2026-05-28" => "Cuti Bersama Idul Adha", 
            "2026-05-31" => "Hari Raya Waisak 2570 BE", "2026-06-01" => "Hari Lahir Pancasila", 
            "2026-06-16" => "Tahun Baru Islam 1448 Hijriah", "2026-08-17" => "Proklamasi Kemerdekaan RI", 
            "2026-08-25" => "Maulid Nabi Muhammad SAW", "2026-12-24" => "Cuti Bersama Natal",
            "2026-12-25" => "Hari Raya Natal"
        ];
        
        $prefixFilter = $currentYear . '-' . $currentMonth;
        foreach ($nationalHolidays as $date => $title) {
            if (strpos($date, $prefixFilter) === 0) {
                $allAgenda[] = ['date' => $date, 'title' => $title, 'color' => '#B91C1C'];
            }
        }

        usort($allAgenda, function ($a, $b) { return strtotime($a['date']) - strtotime($b['date']); });
        $monthlyEvents = json_decode(json_encode($allAgenda));

        // =========================================================
        // 5. RENDER KE VIEW
        // =========================================================
        // PENTING: Sesuaikan path ini dengan folder tempat kamu menyimpan dashboardTeacher.blade.php
        return view('features.lms.teachers.dashboardTeacher', compact(
            'role',
            'schoolName',
            'schoolId',
            'totalKelas',
            'totalModul',
            'tugasPending',
            'jadwalMengajar',
            'tugasMenunggu',
            'monthlyEvents'
        ));
    }
}
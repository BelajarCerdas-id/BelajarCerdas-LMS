<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ParentProfile;
use App\Models\StudentProfile;
use App\Models\AcademicCalendar;
use App\Models\Poll;
use Carbon\Carbon;

class ParentController extends Controller
{
    public function index($role, $schoolName, $schoolId)
    {
        $user = Auth::user();

        // 1. Validasi Dasar
        if (!$user || $user->role !== 'Orang Tua') {
            abort(403, 'Akses Ditolak.');
        }

        $profilOrangTua = ParentProfile::where('user_id', $user->id)->first();
        if (!$profilOrangTua) {
            abort(403, 'Profil Orang Tua tidak ditemukan.');
        }

        // logika pencarian anak
        $studentProfile = StudentProfile::where('parent_id', $user->id)->first();

        $studentUserId = $studentProfile?->user_id;

        $studentClass = '-';
        $studentClassId = null;
        $statusHadir = 'Belum Ada Data';

        if ($studentUserId) {
            // Ambil Kelas
            $classRecord = DB::table('student_school_classes')
                ->join('school_classes', 'student_school_classes.school_class_id', '=', 'school_classes.id')
                ->where('student_school_classes.student_id', $studentUserId)
                ->where('student_school_classes.student_class_status', 'active')
                ->select('school_classes.id as class_id', 'school_classes.class_name')
                ->first();

            if ($classRecord) {
                $studentClass = $classRecord->class_name;
                $studentClassId = $classRecord->class_id;
            }

            // Ambil Absen Hari Ini
            $attendance = DB::table('attendances')
                ->where('student_id', $studentUserId)
                ->whereDate('date', now()->format('Y-m-d'))
                ->first();
            
            if ($attendance) {
                $statusHadir = ucfirst($attendance->status);
            }
        }

        // Data Dasar Anak untuk View
        $dataAnak = (object)[
            'nama_lengkap' => $studentProfile->nama_lengkap ?? "Siswa Tidak Ditemukan",
            'kelas' => $studentClass,
            'kehadiran_hari_ini' => $statusHadir
        ];

        // =========================================================
        // 4. STATISTIK KPI ANAK (Nilai, Hadir, Tugas Pending)
        // =========================================================
        $rataNilai = DB::table('class_task_submissions')->where('student_id', $studentUserId)->avg('score') ?? 0;
        
        $totalHadir = DB::table('attendances')->where('student_id', $studentUserId)->whereIn('status', ['Hadir', 'hadir'])->count();
        $totalCatatan = DB::table('attendances')->where('student_id', $studentUserId)->count();
        $persentaseHadir = $totalCatatan > 0 ? round(($totalHadir / $totalCatatan) * 100) : 0; 
        
        $alpaCount = DB::table('attendances')->where('student_id', $studentUserId)->whereIn('status', ['Alpa', 'alpa'])->count();

        // Menghitung tugas
        $tugasKelas = [];
        if($studentClassId){
            $tugasKelas = DB::table('school_assessments')->where('school_class_id', $studentClassId)->pluck('id')->toArray();
        }
        $tugasDikerjakan = DB::table('class_task_submissions')->where('student_id', $studentUserId)->whereIn('task_id', $tugasKelas)->count();
        $tugasPending = max(0, count($tugasKelas) - $tugasDikerjakan);

        $statsAnak = (object)[
            'persentase_hadir' => $persentaseHadir,
            'rata_nilai'       => round($rataNilai, 1),
            'tugas_pending'    => $tugasPending,
            'alpa'             => $alpaCount
        ];

        // =========================================================
        // 5. JADWAL HARI INI & TUGAS ANAK TERBARU
        // =========================================================
        $hariIndo = ['Monday'=>'Senin', 'Tuesday'=>'Selasa', 'Wednesday'=>'Rabu', 'Thursday'=>'Kamis', 'Friday'=>'Jumat', 'Saturday'=>'Sabtu', 'Sunday'=>'Minggu'];
        $hariIni = $hariIndo[now()->format('l')];

        $jadwalHariIni = collect([]);
        if ($studentClassId) {
            $jadwalHariIni = DB::table('lesson_schedule_items')
                ->join('lesson_schedules', 'lesson_schedule_items.lesson_schedule_id', '=', 'lesson_schedules.id')
                ->leftJoin('school_staff_profiles', 'lesson_schedule_items.teacher_id', '=', 'school_staff_profiles.user_id')
                ->where('lesson_schedules.class_id', $studentClassId)
                ->where('lesson_schedule_items.day_of_week', $hariIni)
                ->where('lesson_schedules.status', 'published')
                ->select(
                    'lesson_schedule_items.start_time', 
                    'lesson_schedule_items.subject_name as mata_pelajaran', 
                    'school_staff_profiles.nama_lengkap as nama_guru'
                )
                ->orderBy('lesson_schedule_items.start_time')
                ->get()
                ->map(function($j) use ($dataAnak) {
                    $j->status_kehadiran = $dataAnak->kehadiran_hari_ini;
                    $j->nama_guru = $j->nama_guru ?? 'Guru Mapel';
                    return $j;
                });
        }

        $tugasAnak = collect([]);
        if ($studentClassId && $studentUserId) {
            $tugasAnak = DB::table('school_assessments')
                ->where('school_assessments.school_class_id', $studentClassId)
                ->leftJoin('class_task_submissions', function($join) use ($studentUserId) {
                    $join->on('school_assessments.id', '=', 'class_task_submissions.task_id')
                         ->where('class_task_submissions.student_id', '=', $studentUserId);
                })
                ->select(
                    'school_assessments.title as judul_tugas', 
                    'school_assessments.end_date as deadline', 
                    'class_task_submissions.id as submission_id'
                )
                ->orderBy('school_assessments.created_at', 'desc')
                ->take(4)
                ->get()
                ->map(function($t) {
                    $t->sudah_dikumpul = !is_null($t->submission_id);
                    $t->judul_tugas = $t->judul_tugas ?? 'Tugas Kelas';
                    $t->mata_pelajaran = 'Mata Pelajaran';
                    return $t;
                });
        }

        // =========================================================
        // 6. AGENDA SEKOLAH & STATISTIK MAPEL
        // =========================================================
        $agendaSekolah = AcademicCalendar::where('school_partner_id', $schoolId)
            ->where('status', 'published')
            ->whereMonth('date', now()->month)
            ->orderBy('date', 'asc')
            ->get()
            ->map(fn($ev) => (object)[
                'tanggal'  => Carbon::parse($ev->date)->format('d M Y'),
                'kegiatan' => $ev->title,
                'color'    => $ev->color ?? '#0071BC'
            ]);

        $kumpulanMapel = [];
        if ($studentClassId && $studentUserId) {
            $semuaTugas = \App\Models\SchoolAssessment::with('Mapel')
                ->where('school_class_id', $studentClassId)
                ->get();

            $tugasSiswaSelesai = DB::table('class_task_submissions')
                ->where('student_id', $studentUserId)
                ->pluck('task_id')
                ->toArray();

            $semuaMateri = \App\Models\LmsMeetingContent::with('Mapel')
                ->where('school_class_id', $studentClassId)
                ->where('is_active', 1)
                ->get();

            $materiDibacaSiswa = []; 

            foreach ($semuaTugas as $tugas) {
                $namaMapel = $tugas->Mapel->mata_pelajaran ?? 'Mata Pelajaran Lainnya';
                if (!isset($kumpulanMapel[$namaMapel])) {
                    $kumpulanMapel[$namaMapel] = ['tugas_total' => 0, 'tugas_selesai' => 0, 'materi_total' => 0, 'materi_dibaca' => 0];
                }
                $kumpulanMapel[$namaMapel]['tugas_total']++;
                if (in_array($tugas->id, $tugasSiswaSelesai)) {
                    $kumpulanMapel[$namaMapel]['tugas_selesai']++;
                }
            }

            foreach ($semuaMateri as $materi) {
                $namaMapel = $materi->Mapel->mata_pelajaran ?? 'Mata Pelajaran Lainnya';
                if (!isset($kumpulanMapel[$namaMapel])) {
                    $kumpulanMapel[$namaMapel] = ['tugas_total' => 0, 'tugas_selesai' => 0, 'materi_total' => 0, 'materi_dibaca' => 0];
                }
                $kumpulanMapel[$namaMapel]['materi_total']++;
                if (in_array($materi->id, $materiDibacaSiswa)) {
                    $kumpulanMapel[$namaMapel]['materi_dibaca']++;
                }
            }
        }

        $statistikMapel = collect();
        foreach ($kumpulanMapel as $mapel => $data) {
            $statistikMapel->push((object)[
                'mapel'         => $mapel,
                'tugas_total'   => $data['tugas_total'],
                'tugas_selesai' => $data['tugas_selesai'],
                'materi_total'  => $data['materi_total'],
                'materi_dibaca' => $data['materi_dibaca'],
            ]);
        }

        // =========================================================
        // 7. POLLING ORANG TUA (DIFILTER BERDASARKAN KELAS & TARGET)
        // =========================================================
        $pollsDb = Poll::where('school_partner_id', $schoolId)
            ->where('status', 'active')
            // 1. Pastikan targetnya memang untuk Orang Tua atau Warga Sekolah
            ->where(function($query) {
                $query->whereIn('target', ['Orang Tua', 'Semua Orang Tua', 'Semua Warga Sekolah', 'Semua'])
                      ->orWhere('target', 'like', '%Orang Tua%');
            })
            // 2. Filter Kelas: Tampilkan jika Global (Null) ATAU khusus kelas anak ini
            ->where(function ($query) use ($studentClassId) {
                $query->whereNull('class_id');
                if ($studentClassId) {
                    $query->orWhere('class_id', $studentClassId);
                }
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $polls = [];

        foreach ($pollsDb as $poll) {
            $pengirim = $poll->author_role ?? 'Manajemen Sekolah';
            $target = $poll->target ?? 'Orang Tua'; 

            // NAMA KELAS DINAMIS (Agar bisa ditampilkan di UI Orang Tua)
            $namaKelas = 'Semua Kelas (Global)';
            if ($poll->class_id) {
                $kelasInfo = DB::table('school_classes')->where('id', $poll->class_id)->first();
                $namaKelas = $kelasInfo ? $kelasInfo->class_name : 'Kelas Dihapus';
            }

            // Cek apakah Orang Tua sudah vote
            $parentVote = DB::table('poll_votes')
                ->where('poll_id', $poll->id)
                ->where('user_id', $user->id) 
                ->first();
            
            $hasVoted = $parentVote ? true : false;
            $votedOptionId = $hasVoted ? $parentVote->poll_option_id : null;

            // Hitung opsi dan persentase
            $totalVotes = DB::table('poll_votes')->where('poll_id', $poll->id)->count();
            $options = \App\Models\PollOption::where('poll_id', $poll->id)->get();

            $formattedOptions = [];
            foreach ($options as $opt) {
                $votesForOption = DB::table('poll_votes')->where('poll_option_id', $opt->id)->count();
                $percentage = $totalVotes > 0 ? round(($votesForOption / $totalVotes) * 100) : 0;
                
                $formattedOptions[] = (object)[
                    'id'          => $opt->id,
                    'text'        => $opt->option_text,
                    'option_text' => $opt->option_text,
                    'votes'       => $votesForOption,
                    'percentage'  => $percentage,
                    'is_selected' => ($opt->id == $votedOptionId) 
                ];
            }

            $polls[] = (object)[
                'id'              => $poll->id,
                'pertanyaan'      => $poll->question,
                'target'          => $target,
                'pengirim'        => $pengirim, 
                'nama_kelas'      => $namaKelas,
                'total_votes'     => $totalVotes,
                'opsi'            => $formattedOptions,
                'options'         => $formattedOptions,
                'sudah_vote'      => $hasVoted,
                'voted_option_id' => $votedOptionId,
                'jawaban_anak'    => null,
                'created_at'      => $poll->created_at 
            ];
        }

        return view('features.lms.parents.dashboard', compact(
            'role', 'schoolName', 'schoolId', 
            'profilOrangTua', 'dataAnak', 'agendaSekolah', 
            'statistikMapel', 'polls', 'statsAnak', 'jadwalHariIni', 'tugasAnak'
        ));
    }

    public function submitPoll(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $pollId = $id;

            $request->validate([
                'option_id' => 'required' 
            ]);

            $sudahVote = DB::table('poll_votes')
                ->where('poll_id', $pollId)
                ->where('user_id', $user->id)
                ->exists();

            if ($sudahVote) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Anda sudah memberikan suara pada polling ini sebelumnya.'
                ]);
            }

            DB::table('poll_votes')->insert([
                'poll_id'        => $pollId,
                'poll_option_id' => $request->option_id,
                'user_id'        => $user->id,
                'created_at'     => now(),
                'updated_at'     => now()
            ]);

            DB::table('poll_options')->where('id', $request->option_id)->increment('votes_count');

            return response()->json([
                'success' => true, 
                'message' => 'Terima kasih! Suara Anda berhasil disimpan.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========================================================
    // FUNGSI BANTUAN: Mencari Data Anak yang Terhubung
    // ========================================================
    private function getAnakInfo()
    {
        $user = Auth::user();

        $profilOrangTua = ParentProfile::where('user_id', $user->id)->first();

        if (!$profilOrangTua) {
            return null;
        }

        // Cari siswa berdasarkan parent_id (user_id orang tua)
        $studentProfile = StudentProfile::where('parent_id', $user->id)->first();

        if (!$studentProfile) {
            return null;
        }

        $studentUserId = $studentProfile->user_id;

        // Ambil kelas aktif siswa
        $classRecord = DB::table('student_school_classes')
            ->where('student_id', $studentUserId)
            ->where('student_class_status', 'active')
            ->first();

        return (object) [
            'user_id'   => $studentUserId,
            'class_id'  => $classRecord->school_class_id ?? null,
            'school_id' => $profilOrangTua->school_partner_id,
        ];
    }

    // ========================================================
    // 1. HALAMAN LAPORAN NILAI
    // ========================================================
    public function laporanNilai()
    {
        $anak = $this->getAnakInfo();
        abort_if(!$anak || !$anak->user_id, 404, 'Data Siswa tidak ditemukan.');

        $nilaiTugas = DB::table('school_assessments')
            ->where('school_assessments.school_class_id', $anak->class_id)
            ->leftJoin('class_task_submissions', function($join) use ($anak) {
                $join->on('school_assessments.id', '=', 'class_task_submissions.task_id')
                     ->where('class_task_submissions.student_id', '=', $anak->user_id);
            })
            ->select('school_assessments.title as judul', 'school_assessments.end_date', 'class_task_submissions.score as nilai', 'class_task_submissions.created_at as tanggal_kumpul')
            ->orderBy('school_assessments.created_at', 'desc')
            ->get();

        return view('features.lms.parents.laporan-nilai', compact('nilaiTugas'));
    }

    // ========================================================
    // 2. HALAMAN KEHADIRAN
    // ========================================================
    public function kehadiran()
    {
        $anak = $this->getAnakInfo();
        abort_if(!$anak || !$anak->user_id, 404, 'Data Siswa tidak ditemukan.');

        $absensi = DB::table('attendances')
            ->where('student_id', $anak->user_id)
            ->orderBy('date', 'desc')
            ->get();

        return view('features.lms.parents.kehadiran', compact('absensi'));
    }

    // ========================================================
    // 3. HALAMAN JADWAL PELAJARAN
    // ========================================================
    public function jadwalPelajaran()
    {
        $anak = $this->getAnakInfo();
        abort_if(!$anak || !$anak->class_id, 404, 'Data Kelas Siswa tidak ditemukan.');

        $jadwalRaw = DB::table('lesson_schedule_items')
            ->join('lesson_schedules', 'lesson_schedule_items.lesson_schedule_id', '=', 'lesson_schedules.id')
            ->leftJoin('school_staff_profiles', 'lesson_schedule_items.teacher_id', '=', 'school_staff_profiles.user_id')
            ->where('lesson_schedules.class_id', $anak->class_id)
            ->where('lesson_schedules.status', 'published')
            ->select('lesson_schedule_items.day_of_week', 'lesson_schedule_items.start_time', 'lesson_schedule_items.end_time', 'lesson_schedule_items.subject_name', 'school_staff_profiles.nama_lengkap as guru')
            ->orderBy('lesson_schedule_items.start_time')
            ->get();

        // Mengelompokkan jadwal berdasarkan Hari
        $jadwalPerHari = $jadwalRaw->groupBy('day_of_week');

        return view('features.lms.parents.jadwal', compact('jadwalPerHari'));
    }

    // ========================================================
    // 4. HALAMAN KALENDER AKADEMIK
    // ========================================================
    public function kalenderAkademik()
    {
        $anak = $this->getAnakInfo();
        abort_if(!$anak || !$anak->school_id, 404, 'Data Sekolah tidak ditemukan.');

        $kalender = AcademicCalendar::where('school_partner_id', $anak->school_id)
            ->where('status', 'published')
            ->orderBy('date', 'asc')
            ->get();

        return view('features.lms.parents.kalender', compact('kalender'));
    }

    
}
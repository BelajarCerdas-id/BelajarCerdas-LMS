<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ParentProfile;
use App\Models\StudentProfile;
use App\Models\AcademicCalendar;
use App\Models\Announcement;
use App\Models\Poll;
use App\Models\StudentAssessmentAttempt;
use App\Models\ExtracurricularStudent;
use App\Models\ExtracurricularMeeting;
use App\Models\ExtracurricularAttendance;
use Carbon\Carbon;

class ParentController extends Controller
{
    public function index($role, $schoolName, $schoolId, $studentId = null)
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
        if ($studentId) {
            $studentProfile = StudentProfile::where('user_id', $studentId)->first();
        } else {
            $studentProfile = StudentProfile::where('parent_id', $user->id)->first();
        }

        // ambil seluruh anak yang memiliki parent_id yang sesuai dengan user yang sedang login
        $childrens = StudentProfile::where('parent_id', $user->id)->orderBy('nama_lengkap')->get();

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
            $attendance = DB::table('subject_attendances')
                ->where('student_id', $studentUserId)
                ->whereDate('created_at', now()->format('Y-m-d'))
                ->first();
            
            if ($attendance) {
                $statusHadir = ucfirst($attendance->attendance_status);
            }
        }

        // Data Dasar Anak untuk View
        // Data Dasar Anak untuk View
$dataAnak = (object)[
    'id' => $studentProfile?->id,
    'user_id' => $studentProfile?->user_id,
    'nama_lengkap' => $studentProfile?->nama_lengkap ?? 'Siswa Tidak Ditemukan',
    'kelas' => $studentClass,
    'kehadiran_hari_ini' => $statusHadir,
];

        /* =========================================================
 * EKSTRAKURIKULER ANAK
 * ========================================================= */

$extracurricularSessions = collect();

if ($dataAnak) {

    // Ambil ekskul yang diikuti anak
   $memberEkskul = ExtracurricularStudent::with('extracurricular')
    ->where('student_profile_id', $studentProfile->id)
    ->get();

    foreach ($memberEkskul as $member) {

        $extracurricular = $member->extracurricular;

        if (!$extracurricular) {
            continue;
        }

        /*
         * Ambil seluruh pertemuan ekskul
         */
        $meetings = ExtracurricularMeeting::where(
                'extracurricular_id',
                $extracurricular->id
            )
            ->orderBy('meeting_date')
            ->get();

        $history = collect();

        $hadir = 0;
        $alpa = 0;

        foreach ($meetings as $meeting) {

            /*
             * Ambil absensi anak pada pertemuan tersebut.
             *
             * Jika FK di database kamu bernama
             * extracurricular_meeting_id, gunakan ini.
             */
            $attendance = ExtracurricularAttendance::where(
                'student_profile_id',
                $studentProfile->id
            )
            ->where(
                'meeting_id',
                $meeting->id
            )
            ->first();

            $status = strtolower(
                $attendance->status ?? 'not_recorded'
            );

            /*
             * Normalisasi status
             */
            if (in_array($status, ['present', 'hadir'])) {

                $status = 'hadir';
                $hadir++;

            } elseif (in_array($status, ['absent', 'alpa', 'alpha'])) {

                $status = 'alpa';
                $alpa++;

            } else {

                $status = 'not_recorded';
            }

            $history->push([
                'status' => $status,
                'date' => $meeting->meeting_date,
            ]);
        }

        $totalPertemuan = $meetings->count();

        $percentage = $totalPertemuan > 0
            ? round(($hadir / $totalPertemuan) * 100)
            : 0;

        $extracurricularSessions->push([
            'name' => $extracurricular->name,

            'tipe_kelas' =>
                $extracurricular->tipe_kelas ?? null,

            'kelas' =>
                $extracurricular->kelas ?? null,

            'status' =>
                $history->last()['status'] ?? 'not_recorded',

            'hadir' => $hadir,
            'alpa' => $alpa,

            'total_pertemuan' =>
                $totalPertemuan,

            'percentage' =>
                $percentage,

            'attendance_history' =>
                $history,
        ]);
    }
}

        // =========================================================
        // 4. STATISTIK KPI ANAK (Nilai, Hadir, Tugas Pending)
        // =========================================================
        $rataNilai = DB::table('class_task_submissions')->where('student_id', $studentUserId)->avg('score') ?? 0;
        
        $totalHadir = DB::table('subject_attendances')->where('student_id', $studentUserId)->whereIn('attendance_status', ['Hadir', 'hadir'])->count();
        $totalCatatan = DB::table('subject_attendances')->where('student_id', $studentUserId)->count();
        $persentaseHadir = $totalCatatan > 0 ? round(($totalHadir / $totalCatatan) * 100) : 0; 
        
        $alpaCount = DB::table('subject_attendances')->where('student_id', $studentUserId)->whereIn('attendance_status', ['Alpa', 'alpa'])->count();

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

                // SEMUA ASESMEN
                $semuaTugas = \App\Models\SchoolAssessment::with('Mapel')->where('school_class_id', $studentClassId)->get();

                // ASESMEN YANG SUDAH DIKERJAKAN SISWA
                $tugasSiswaSelesai = \App\Models\StudentAssessmentSummary::where('student_id', $studentUserId)->pluck('root_assessment_id')->toArray();

                // SEMUA MATERI
                $semuaMateri = \App\Models\LmsMeetingContent::with('Mapel')->where('school_class_id', $studentClassId)->where('is_active', 1)->get();

                // MATERI YANG SUDAH DIBACA
                $materiDibacaSiswa = \App\Models\LmsContentRead::where('student_id', $studentUserId)->where('status', 'completed')->pluck('lms_meeting_content_id')->toArray();

                // HITUNG TUGAS
                foreach ($semuaTugas as $tugas) {

                    $namaMapel = $tugas->Mapel->mata_pelajaran ?? 'Mata Pelajaran Lainnya';

                    if (!isset($kumpulanMapel[$namaMapel])) {
                        $kumpulanMapel[$namaMapel] = [
                            'tugas_total'   => 0,
                            'tugas_selesai' => 0,
                            'materi_total'  => 0,
                            'materi_dibaca' => 0,
                        ];
                    }

                    $kumpulanMapel[$namaMapel]['tugas_total']++;

                    if (in_array($tugas->id, $tugasSiswaSelesai)) {
                        $kumpulanMapel[$namaMapel]['tugas_selesai']++;
                    }
                }

                // HITUNG MATERI
                foreach ($semuaMateri as $materi) {

                    $namaMapel = $materi->Mapel->mata_pelajaran ?? 'Mata Pelajaran Lainnya';

                    if (!isset($kumpulanMapel[$namaMapel])) {
                        $kumpulanMapel[$namaMapel] = [
                            'tugas_total'   => 0,
                            'tugas_selesai' => 0,
                            'materi_total'  => 0,
                            'materi_dibaca' => 0,
                        ];
                    }

                    $kumpulanMapel[$namaMapel]['materi_total']++;

                    if (in_array($materi->id, $materiDibacaSiswa)) {
                        $kumpulanMapel[$namaMapel]['materi_dibaca']++;
                    }
                }
            }

            $statistikMapel = collect();

            foreach ($kumpulanMapel as $mapel => $data) {

                $statistikMapel->push((object) [
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

        // QUERY CHEATING
        $query = StudentAssessmentAttempt::with(['UserAccount.StudentProfile', 'SchoolAssessment.Mapel', 'SchoolAssessment.SchoolClass', 'SchoolAssessment.SchoolAssessmentType'])
        ->where('status', 'cheating')->where('student_id', $studentUserId);

        $cheatingHistory = $query->latest()->get();

        // ANNOUNCEMENT
        $announcements = Announcement::query()->with('author')->where('school_partner_id', $schoolId)->where(function ($query) {

            // Pengumuman untuk Orang Tua atau guru yang ditujukan ke siswa
            $query->where('target', 'Orang Tua')->orWhere(function ($q) {
                $q->where('author_role', 'Guru')->where('target', 'Siswa');
            });

        })->where(function ($query) use ($studentClassId) {
            $query->whereNull('target_class_id')->orWhere('target_class_id', $studentClassId);
        })->latest()->take(8)->get();

        return view(
    'features.lms.parents.dashboard',
    compact(
        'profilOrangTua',
        'dataAnak',
        'childrens',
        'studentId',
        'role',
        'schoolName',
        'schoolId',
        'statsAnak',
        'jadwalHariIni',
        'tugasAnak',
        'statistikMapel',
        'announcements',
        'agendaSekolah',
        'cheatingHistory',
        'polls',
        'extracurricularSessions'
    )
);
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
    private function getAnakInfo($studentId = null)
    {
        $user = Auth::user();

        $profilOrangTua = ParentProfile::where('user_id', $user->id)->first();

        if (!$profilOrangTua) {
            return null;
        }

        $studentQuery = StudentProfile::where('parent_id', $user->id);

        if ($studentId) {
            $studentQuery->where('user_id', $studentId);
        }

        $studentProfile = $studentQuery->orderBy('nama_lengkap')->first();

        if (!$studentProfile) {
            return null;
        }

        $classRecord = DB::table('student_school_classes')->where('student_id', $studentProfile->user_id)->where('student_class_status', 'active')->first();

        return (object)[
            'user_id'   => $studentProfile->user_id,
            'class_id'  => $classRecord->school_class_id ?? null,
            'school_id' => $profilOrangTua->school_partner_id,
        ];
    }

    // ========================================================
    // 1. HALAMAN LAPORAN NILAI
    // ========================================================
    public function laporanNilai($studentId = null)
    {
        $anak = $this->getAnakInfo($studentId);

        abort_if(!$anak || !$anak->user_id, 404, 'Data Siswa tidak ditemukan.');

        $nilaiTugas = \App\Models\StudentAssessmentSummary::with([
            'SchoolAssessment.Mapel',
            'SchoolAssessment.SchoolClass',
            'SchoolAssessment.SchoolAssessmentType',
        ])
        ->where('student_id', $anak->user_id)
        ->whereHas('SchoolAssessment', function ($query) use ($anak) {
            $query->where('school_class_id', $anak->class_id);
        })
        ->orderByDesc('updated_at')
        ->get()
        ->map(function ($item) {

            $assessment = $item->SchoolAssessment;

            return (object) [
                'judul'             => $assessment->title ?? '-',
                'mapel'             => $assessment->Mapel->mata_pelajaran ?? '-',
                'kelas'             => $assessment->SchoolClass->class_name ?? '-',
                'tipe'              => $assessment->SchoolAssessmentType->name ?? '-',
                'kategori'          => $assessment->assessment_category ?? '-',
                'deadline'          => $assessment->end_date,
                'nilai'             => $item->final_score,
                'status'            => $item->final_score !== null ? 'Selesai' : 'Belum Dinilai',
                'score_source'      => $item->score_source,
                'tanggal_update'    => $item->updated_at,
            ];
        });

        return view(
            'features.lms.parents.laporan-nilai',
            compact('nilaiTugas')
        );
    }

    // ========================================================
    // 2. HALAMAN KEHADIRAN
    // ========================================================
    public function kehadiran($studentId = null)
    {
        $anak = $this->getAnakInfo($studentId);
        abort_if(!$anak || !$anak->user_id, 404, 'Data Siswa tidak ditemukan.');

        $absensi = DB::table('subject_attendances')
            ->where('student_id', $anak->user_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('features.lms.parents.kehadiran', compact('absensi'));
    }

    // ========================================================
    // 3. HALAMAN JADWAL PELAJARAN
    // ========================================================
    public function jadwalPelajaran($studentId = null)
    {
        $anak = $this->getAnakInfo($studentId);
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
    public function kalenderAkademik($studentId = null)
    {
        $anak = $this->getAnakInfo($studentId);
        abort_if(!$anak || !$anak->school_id, 404, 'Data Sekolah tidak ditemukan.');

        $kalender = AcademicCalendar::where('school_partner_id', $anak->school_id)
            ->where('status', 'published')
            ->orderBy('date', 'asc')
            ->get();

        return view('features.lms.parents.kalender', compact('kalender'));
    }

    
}
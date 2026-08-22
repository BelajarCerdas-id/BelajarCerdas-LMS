<?php

namespace App\Http\Controllers;

use App\Events\DailyReflectionLivePreview;
use App\Models\Announcement;
use App\Models\LmsContentRead;
use App\Models\LmsMeetingContent;
use App\Models\SchoolClass;
use App\Models\SchReflAnswer;
use App\Models\SchReflQuestion;
use App\Models\SchReflTarget;
use App\Models\Extracurricular;
use App\Models\ExtracurricularStudent;
use App\Models\ExtracurricularAttendance;
use App\Models\ExtracurricularMeeting;
use App\Models\StudentProfile;
use App\Models\StudentAssessmentAttempt;
use App\Models\StudentSchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class StudentDashboardController extends Controller
{
    // private function guessMime
    private function guessMime($ext)
    {
        return match (strtolower($ext)) {
            'mp4', 'webm', 'ogg' => 'video/' . $ext,
            'pdf'               => 'application/pdf',
            'jpg', 'jpeg', 'png', 'webp' => 'image/' . $ext,
            default             => 'application/octet-stream',
        };
    }

    public function index(Request $request, $role, $schoolName, $schoolId)
    {
        $user = Auth::user();

        $today = now()->format('Y-m-d');

        // 1. Validasi Akses
        if (!$user || $user->role !== 'Siswa') {
            abort(403, 'Akses Ditolak. Halaman ini khusus untuk Siswa.');
        }

        $studentProfile = \App\Models\StudentProfile::where('user_id', $user->id)->first();
        if (!$studentProfile) {
            abort(403, 'Profil Siswa tidak ditemukan.');
        }

        $schoolId = $studentProfile->school_partner_id;
        $studentUserId = $user->id;

        // Ambil Nama Sekolah
        $schoolName = 'Belum Ada Sekolah';
        if ($schoolId) {
            $schoolRecord = DB::table('school_partners')->where('id', $schoolId)->first();
            if ($schoolRecord) {
                $schoolName = $schoolRecord->nama_sekolah ?? $schoolRecord->nama_sekolah ?? 'Sekolah Mitra';
            }
        }

        $studentClass = 'Belum Ada Kelas';
        $studentClassId = null;
        $statusHadir = 'Belum Ada Data';

        // Ambil Kelas Siswa
        $classRecord = DB::table('student_school_classes')
            ->join('school_classes', 'student_school_classes.school_class_id', '=', 'school_classes.id')
            ->where('student_school_classes.student_id', $studentUserId)
            ->where('student_school_classes.student_class_status', 'active')
            ->select('school_classes.id as class_id', 'school_classes.class_name', 'student_school_classes.school_class_id') 
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

        // Data Utama Siswa
        $dataSiswa = (object)[
            'nama_lengkap'       => $studentProfile->nama_lengkap,
            'kelas'              => $studentClass,
            'kehadiran_hari_ini' => $statusHadir
        ];

// =========================================================
// EKSTRAKURIKULER SISWA
// =========================================================

$studentProfileId = $studentProfile->id;

// Semua ekskul yang benar-benar diikuti siswa
$studentExtracurriculars = ExtracurricularStudent::with('extracurricular')
    ->where('student_profile_id', $studentProfileId)
    ->where('status', 'active')
    ->get()
    ->filter(function ($member) {
        return $member->extracurricular
            && $member->extracurricular->status === 'active';
    })
    ->values();


// =========================================================
// AMBIL SEMUA MEETING / SESI
// =========================================================

$meetings = ExtracurricularMeeting::whereIn(
        'extracurricular_id',
        $studentExtracurriculars
            ->pluck('extracurricular_id')
            ->unique()
            ->values()
    )
    ->orderBy('meeting_date', 'asc')
    ->get();


// =========================================================
// BUAT SESI BERDASARKAN TANGGAL MEETING
// =========================================================

$extracurricularSessions = collect();

foreach ($meetings->groupBy(function ($meeting) {
    return Carbon::parse($meeting->meeting_date)->format('Y-m-d');
}) as $date => $dateMeetings) {

    $items = collect();

    foreach ($dateMeetings as $meeting) {

    $membership = $studentExtracurriculars
        ->firstWhere('extracurricular_id', $meeting->extracurricular_id);

    if (!$membership) {
        continue;
    }

    // =====================================================
    // ABSENSI PADA SESI INI
    // =====================================================

    $attendance = ExtracurricularAttendance::where(
            'meeting_id',
            $meeting->id
        )
        ->where('student_profile_id', $studentProfileId)
        ->first();


    // =====================================================
    // SELURUH MEETING EKSKUL INI
    // Untuk menampilkan riwayat saat kartu di-expand
    // =====================================================

    $extracurricularMeetings = $meetings
        ->where('extracurricular_id', $meeting->extracurricular_id)
        ->sortBy('meeting_date')
        ->values();


    $attendanceHistory = collect();

    foreach ($extracurricularMeetings as $historyMeeting) {

        $historyAttendance = ExtracurricularAttendance::where(
                'meeting_id',
                $historyMeeting->id
            )
            ->where('student_profile_id', $studentProfileId)
            ->first();

        $attendanceHistory->push([
            'meeting_id' => $historyMeeting->id,

            'date' => Carbon::parse(
                $historyMeeting->meeting_date
            ),

            'status' => $historyAttendance?->status
                ?? 'not_recorded',
        ]);
    }


    // =====================================================
    // REKAP ABSENSI
    // =====================================================

    $hadir = $attendanceHistory->filter(function ($item) {
        return in_array(
            strtolower($item['status']),
            ['present', 'hadir']
        );
    })->count();

    $izin = $attendanceHistory->filter(function ($item) {
        return in_array(
            strtolower($item['status']),
            ['permission', 'izin']
        );
    })->count();

    $sakit = $attendanceHistory->filter(function ($item) {
        return in_array(
            strtolower($item['status']),
            ['sick', 'sakit']
        );
    })->count();

    $alpa = $attendanceHistory->filter(function ($item) {
        return in_array(
            strtolower($item['status']),
            ['absent', 'alpa', 'alpha']
        );
    })->count();

    $totalPertemuan = $attendanceHistory->count();

    $percentage = $totalPertemuan > 0
        ? round(($hadir / $totalPertemuan) * 100, 1)
        : 0;


    // =====================================================
    // PUSH DATA
    // =====================================================

    $items->push([

        'extracurricular_id' =>
            $membership->extracurricular_id,

        'name' =>
            $membership->extracurricular->name,

        'kelas' =>
            $membership->kelas,

        'tipe_kelas' =>
            $membership->tipe_kelas,

        'status' =>
            $attendance?->status ?? 'not_recorded',

        'meeting_id' =>
            $meeting->id,

        'meeting_date' =>
            Carbon::parse($meeting->meeting_date),

        // DATA RIWAYAT
        'attendance_history' =>
            $attendanceHistory,

        // REKAP
        'hadir' => $hadir,
        'izin' => $izin,
        'sakit' => $sakit,
        'alpa' => $alpa,

        'total_pertemuan' =>
            $totalPertemuan,

        'percentage' =>
            $percentage,
    ]);
}

    if ($items->isNotEmpty()) {

        $extracurricularSessions->push([
            'date' => Carbon::parse($date),
            'items' => $items,
        ]);
    }
}


// =========================================================
// EKSKUL YANG BELUM MASUK SESI / BELUM PUNYA MEETING
// MASUKKAN KE SESI 1
// =========================================================

$alreadyInSession = $extracurricularSessions
    ->flatMap(function ($session) {
        return $session['items'];
    })
    ->pluck('extracurricular_id')
    ->unique();

$notYetInSession = $studentExtracurriculars
    ->whereNotIn('extracurricular_id', $alreadyInSession);


// =========================================================
// JIKA ADA EKSKUL YANG BELUM MASUK SESI
// =========================================================

if ($notYetInSession->isNotEmpty()) {

    // Kalau sudah ada sesi, masuk ke sesi pertama
    if ($extracurricularSessions->isNotEmpty()) {

        $firstSession = $extracurricularSessions->first();

        foreach ($notYetInSession as $membership) {

    $attendanceHistory = collect();

    // Semua meeting ekskul ini kalau ternyata ada
    $extracurricularMeetings = $meetings
        ->where('extracurricular_id', $membership->extracurricular_id)
        ->sortBy('meeting_date')
        ->values();

    foreach ($extracurricularMeetings as $historyMeeting) {

        $historyAttendance = ExtracurricularAttendance::where(
                'meeting_id',
                $historyMeeting->id
            )
            ->where('student_profile_id', $studentProfileId)
            ->first();

        $attendanceHistory->push([
            'meeting_id' => $historyMeeting->id,

            'date' => Carbon::parse(
                $historyMeeting->meeting_date
            ),

            'status' =>
                $historyAttendance?->status
                ?? 'not_recorded',
        ]);
    }

    $hadir = $attendanceHistory->filter(function ($item) {
        return in_array(
            strtolower($item['status']),
            ['present', 'hadir']
        );
    })->count();

    $izin = $attendanceHistory->filter(function ($item) {
        return in_array(
            strtolower($item['status']),
            ['permission', 'izin']
        );
    })->count();

    $sakit = $attendanceHistory->filter(function ($item) {
        return in_array(
            strtolower($item['status']),
            ['sick', 'sakit']
        );
    })->count();

    $alpa = $attendanceHistory->filter(function ($item) {
        return in_array(
            strtolower($item['status']),
            ['absent', 'alpa', 'alpha']
        );
    })->count();

    $totalPertemuan = $attendanceHistory->count();

    $percentage = $totalPertemuan > 0
        ? round(($hadir / $totalPertemuan) * 100, 1)
        : 0;


    $firstSession['items']->push([

        'extracurricular_id' =>
            $membership->extracurricular_id,

        'name' =>
            $membership->extracurricular->name,

        'kelas' =>
            $membership->kelas,

        'tipe_kelas' =>
            $membership->tipe_kelas,

        'status' =>
            'not_recorded',

        'meeting_id' =>
            null,

        'meeting_date' =>
            $firstSession['date'],

        'attendance_history' =>
            $attendanceHistory,

        'hadir' => $hadir,
        'izin' => $izin,
        'sakit' => $sakit,
        'alpa' => $alpa,

        'total_pertemuan' =>
            $totalPertemuan,

        'percentage' =>
            $percentage,
    ]);
}

        // Update sesi pertama
        $extracurricularSessions->put(
            0,
            $firstSession
        );

    } else {

        // =====================================================
        // BELUM ADA SESI SAMA SEKALI
        // BUAT SESI 1
        // =====================================================

        $items = collect();

        foreach ($notYetInSession as $membership) {

            $items->push([

                'extracurricular_id' =>
                    $membership->extracurricular_id,

                'name' =>
                    $membership->extracurricular->name,

                'kelas' =>
                    $membership->kelas,

                'tipe_kelas' =>
                    $membership->tipe_kelas,

                'status' =>
                    'not_recorded',

                'meeting_id' =>
                    null,

                'meeting_date' =>
                    now(),

            ]);
        }

        $extracurricularSessions->push([
            'date' => now(),
            'items' => $items,
        ]);
    }
}
        // =========================================================
        // 3. JADWAL PELAJARAN
        // =========================================================
        $selectedJadwalDate = $request->query('jadwal_date', now()->format('Y-m-d'));
        $carbonJadwal = Carbon::parse($selectedJadwalDate);
        
        $hariInggris = $carbonJadwal->format('l');
        $mapHari = [
            'Monday'    => 'Senin', 
            'Tuesday'   => 'Selasa', 
            'Wednesday' => 'Rabu', 
            'Thursday'  => 'Kamis', 
            'Friday'    => 'Jumat', 
            'Saturday'  => 'Sabtu', 
            'Sunday'    => 'Minggu'
        ];
        
        $hariDipilih = isset($mapHari[$hariInggris]) ? $mapHari[$hariInggris] : ''; 
        $jadwalHariIni = []; 

        if ($schoolId && $studentClassId && $hariDipilih !== '') {
            $dbSchedules = DB::table('lesson_schedule_items')
                ->join('lesson_schedules', 'lesson_schedule_items.lesson_schedule_id', '=', 'lesson_schedules.id')
                ->leftJoin('school_staff_profiles', 'lesson_schedule_items.teacher_id', '=', 'school_staff_profiles.user_id')
                ->where('lesson_schedules.school_partner_id', $schoolId)
                ->where('lesson_schedules.class_id', $studentClassId) 
                ->where('lesson_schedule_items.day_of_week', $hariDipilih) 
                ->where('lesson_schedules.status', 'published')
                ->orderBy('lesson_schedule_items.start_time', 'asc')
                ->select(
                    'lesson_schedule_items.start_time',
                    'lesson_schedule_items.end_time',
                    'lesson_schedule_items.subject_name',
                    'lesson_schedules.class_name',
                    'school_staff_profiles.nama_lengkap as teacher_name'
                )
                ->get();

            foreach ($dbSchedules as $jadwal) {
                $jadwalHariIni[] = [
                    'is_break'   => false,
                    'start_time' => $jadwal->start_time,
                    'jam'        => substr($jadwal->start_time, 0, 5) . ' - ' . substr($jadwal->end_time, 0, 5),
                    'mapel'      => $jadwal->subject_name,
                    'guru'       => $jadwal->teacher_name, 
                    'ruang'      => $jadwal->class_name,
                    'color'      => '#0071BC'
                ];
            }

            if (in_array($hariDipilih, ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']) && count($jadwalHariIni) > 0) {
                $jadwalHariIni[] = ['is_break' => true, 'start_time' => '10:00:00', 'jam' => '10:00 - 10:45', 'mapel' => 'ISTIRAHAT PERTAMA', 'color' => '#f97316'];
                $jadwalHariIni[] = ['is_break' => true, 'start_time' => '12:15:00', 'jam' => '12:15 - 13:00', 'mapel' => 'ISTIRAHAT KEDUA', 'color' => '#f97316'];
            }
            
            if (count($jadwalHariIni) > 0) {
                usort($jadwalHariIni, function ($a, $b) { 
                    return strcmp($a['start_time'], $b['start_time']); 
                });
            }
        }

        $hariIni = $hariDipilih; 

        // =========================================================
        // 4. AGENDA MINGGUAN
        // =========================================================
        $selectedDate = $request->query('date', now()->format('Y-m-d'));
        $startOfWeek = Carbon::parse($selectedDate)->startOfWeek()->format('Y-m-d');
        $endOfWeek   = Carbon::parse($selectedDate)->endOfWeek()->format('Y-m-d');

        $agendaSekolah = [];
        if ($schoolId) {
            $agendaSekolah = \App\Models\AcademicCalendar::where('school_partner_id', $schoolId)
                ->where('status', 'published')
                ->whereBetween('date', [$startOfWeek, $endOfWeek])
                ->orderBy('date', 'asc')
                ->get();
        }

        // =========================================================
        // 5. CONTENT (MODUL / MATERI)
        // =========================================================
        $unreadModules = collect();
        if ($studentClassId) {
            $materiSiswaRaw = \App\Models\LmsMeetingContent::with([
                    'LmsContent.LmsContentItem',
                    'Mapel'
                ])
                ->where('school_class_id', $studentClassId)
                ->where('is_active', 1)

                // hanya materi yang belum dibaca
                ->whereDoesntHave('LmsContentRead', function ($query) use ($studentUserId) {
                    $query->where('student_id', $studentUserId)
                        ->where('status', 'completed');
                })

                ->orderBy('meeting_date', 'desc')
                ->take(6)
                ->get();

            foreach ($materiSiswaRaw as $materi) {
                $judul = 'Materi Pembelajaran';
                $deskripsi = 'Silakan pelajari modul materi ini untuk persiapan belajar.';
                $fileUrl = '#';

                if ($materi->LmsContent && $materi->LmsContent->LmsContentItem && $materi->LmsContent->LmsContentItem->count() > 0) {
                    $item = $materi->LmsContent->LmsContentItem->first();
                    $judul = $item->original_filename ?? 'Materi Pembelajaran';
                    $rawText = strip_tags($item->value_text);
                    
                    if(!empty($rawText)){
                        $deskripsi = substr($rawText, 0, 100) . '...';
                    }
                    if (!empty($item->value_file)) {
                        $fileUrl = asset('lms-contents/' . $item->value_file);
                    }
                }

                $unreadModules->push((object)[
                    'id'        => $materi->id,
                    'mapel'     => $materi->Mapel->mata_pelajaran ?? 'Mata Pelajaran',
                    'judul'     => $judul,
                    'deskripsi' => $deskripsi,
                    'file_url'  => $fileUrl
                ]);
            }
        }

        // =========================================================
        // 6. ASSESSMENT (TUGAS & UJIAN)
        // =========================================================
        $pendingTasks = collect();
        $jadwalUjian = collect();

        if ($studentClassId) {
            $semuaAsesmenSiswa = \App\Models\SchoolAssessment::with(['SchoolAssessmentType.AssessmentMode', 'Mapel'])
                ->where('school_class_id', $studentClassId)
                ->get();

            // A. TUGAS PENDING (Mode: project)
            $tugasRaw = $semuaAsesmenSiswa->filter(function ($item) use ($today) {
                return $item->SchoolAssessmentType && $item->SchoolAssessmentType->AssessmentMode && $item->SchoolAssessmentType->AssessmentMode->code !== 'exam'
                && Carbon::parse($item->start_date)->toDateString() <= $today
                && Carbon::parse($item->end_date)->toDateString() >= $today;
            })->sortByDesc('start_date');

            foreach ($tugasRaw as $tugas) {
                $sudahKirim = \Illuminate\Support\Facades\DB::table('class_task_submissions')
                    ->where('task_id', $tugas->id)
                    ->where('student_id', $studentUserId)
                    ->exists();
                
                if (!$sudahKirim) {
                    $pendingTasks->push((object)[
                        'id'          => $tugas->id,
                        'judul_tugas' => $tugas->title,
                        'mapel'       => $tugas->Mapel->mata_pelajaran ?? 'Mata Pelajaran',
                        'assessment_type' => $tugas->SchoolAssessmentType->name,
                        'deadline' => Carbon::parse($tugas->start_date)->translatedFormat('d M Y H:i')
                            . ' - ' . Carbon::parse($tugas->end_date)->translatedFormat('d M Y H:i'),
                        'curriculumId' => $tugas->Mapel->Kurikulum->id,
                        'mapelId' => $tugas->mapel_id,
                        'assessmentTypeId' => $tugas->assessment_type_id,
                        'semester' => $tugas->semester,
                        'assessmentMode' => $tugas->SchoolAssessmentType->AssessmentMode->code
                    ]);
                }
            }

            // B. JADWAL UJIAN (Mode: non-project)
            $ujianRaw = $semuaAsesmenSiswa->filter(function($item) use ($today) {
                return $item->SchoolAssessmentType 
                    && $item->SchoolAssessmentType->AssessmentMode 
                    && $item->SchoolAssessmentType->AssessmentMode->code === 'exam'
                    && Carbon::parse($item->start_date)->toDateString() <= $today
                    && Carbon::parse($item->end_date)->toDateString() >= $today;
            })->sortByDesc('start_date');

            foreach ($ujianRaw as $ujian) {
                if (\Carbon\Carbon::parse($ujian->end_date)->isPast() && !\Carbon\Carbon::parse($ujian->end_date)->isToday()) {
                    continue; 
                }

                $tglMulai = \Carbon\Carbon::parse($ujian->start_date);
                $tglAkhir = \Carbon\Carbon::parse($ujian->end_date);
                $selisihHari = now()->startOfDay()->diffInDays($tglMulai->copy()->startOfDay(), false); 
                
                $h_min = '';
                if ($selisihHari < 0) {
                    $h_min = 'Berlangsung';
                } elseif ($selisihHari == 0) {
                    $h_min = 'Hari Ini';
                } else {
                    $h_min = 'H-' . ceil($selisihHari);
                }

                $jadwalUjian->push((object)[
                    'id'      => $ujian->id,
                    'tipe'    => $ujian->SchoolAssessmentType->name ?? 'Ujian', 
                    'mapel'   => $ujian->Mapel->mata_pelajaran ?? 'Mata Pelajaran', 
                    'tanggalMulai' => $tglMulai->format('d M Y'),
                    'waktuMulai'   => $tglMulai->format('H:i'),
                    'tanggalAkhir' => $tglAkhir->format('d M Y'),
                    'waktuAkhir'   => $tglAkhir->format('H:i'),
                    'h_min'   => $h_min,
                    'curriculumId' => $ujian->Mapel->Kurikulum->id,
                    'mapelId' => $ujian->mapel_id,
                    'assessmentTypeId' => $ujian->assessment_type_id,
                    'semester' => $ujian->semester,
                ]);
            }
        }

        // =========================================================
        // 7. STATISTIK TUGAS & MATERI (PLACEHOLDER SEMENTARA)
        // =========================================================
        $statistikMapel = collect([
            (object)['mapel' => 'Matematika Peminatan', 'tugas_total' => 12, 'tugas_selesai' => 10, 'materi_total' => 5, 'materi_dibaca' => 5],
            (object)['mapel' => 'Bahasa Inggris', 'tugas_total' => 8, 'tugas_selesai' => 8, 'materi_total' => 4, 'materi_dibaca' => 2],
        ]);

        // =========================================================
        // 8. POLLING
        // =========================================================
        $activePolls = [];
        $votedPolls = [];

        if ($schoolId) {
            $pollsDb = \App\Models\Poll::where('school_partner_id', $schoolId)
                ->where('status', 'active')
                ->whereIn('target', ['Semua Warga Sekolah', 'Semua Siswa', 'Semua'])
                ->where(function ($query) use ($studentClassId) {
                    $query->where('class_id', $studentClassId)
                          ->orWhereNull('class_id'); 
                })
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($pollsDb as $poll) {
                $namaKelas = 'Semua Kelas (Global)';
                if ($poll->class_id) {
                    $kelasInfo = DB::table('school_classes')->where('id', $poll->class_id)->first();
                    $namaKelas = $kelasInfo ? $kelasInfo->class_name : 'Kelas Dihapus';
                }

                $userVote = \App\Models\PollVote::where('poll_id', $poll->id)
                    ->where('user_id', $studentUserId) 
                    ->first();
                
                $hasVoted = $userVote ? true : false;
                $votedOptionId = $hasVoted ? $userVote->poll_option_id : null;
                
                $totalVotes = \App\Models\PollVote::where('poll_id', $poll->id)->count();
                $options = \App\Models\PollOption::where('poll_id', $poll->id)->get();

                $formattedOptions = [];
                foreach ($options as $opt) {
                    $votesForOption = \App\Models\PollVote::where('poll_option_id', $opt->id)->count();
                    $percentage = $totalVotes > 0 ? round(($votesForOption / $totalVotes) * 100) : 0;
                    
                    $formattedOptions[] = (object)[
                        'id'          => $opt->id,
                        'text'        => $opt->option_text,
                        'votes'       => $votesForOption,
                        'percentage'  => $percentage,
                        'is_selected' => ($opt->id === $votedOptionId) 
                    ];
                }

                $pollData = (object)[
                    'id'              => $poll->id,
                    'pertanyaan'      => $poll->question,
                    'pembuat'         => $poll->author_role ?? 'Guru',
                    'target'          => $poll->target ?? 'Semua Warga Sekolah',
                    'nama_kelas'      => $namaKelas,
                    'total_votes'     => $totalVotes,
                    'opsi'            => $formattedOptions,
                    'sudah_vote'      => $hasVoted,
                    'voted_option_id' => $votedOptionId,
                    'created_at'      => $poll->created_at
                ];

                if ($hasVoted) {
                    $votedPolls[] = $pollData; 
                } else {
                    $activePolls[] = $pollData; 
                }
            }
        }

        // Bagian 9 di StudentDashboardController
        $pengumumanTerkini = Announcement::query()->with('author')->where('school_partner_id', $schoolId)->where('target', 'Siswa')
        ->where(function ($query) use ($studentClassId) {
            $query->whereNull('target_class_id')->orWhere('target_class_id', $studentClassId);
        })->withExists([
            'views as is_read' => function ($query) use ($studentUserId) {
                $query->where('user_id', $studentUserId);
            }
        ])->latest()->take(4)->get();
            
        return view('features.lms.students.dashboard', compact(
            'dataSiswa',
            'role',
            'schoolName',
            'schoolId',
            'agendaSekolah',
            'selectedDate',
            'jadwalUjian',
            'statistikMapel',
            'activePolls',
            'votedPolls',
            'pengumumanTerkini',
            'jadwalHariIni',
            'hariIni',
            'selectedJadwalDate',
            'hariDipilih',
            'unreadModules',
            'pendingTasks',
            'extracurricularSessions'
        ));
    }

    public function showStudentContent($role, $schoolName, $schoolId, $meetingId)
    {
        $user = Auth::user();

        $meeting = LmsMeetingContent::with([
            'LmsContent.Service',
            'LmsContent.LmsContentItem'
        ])
        ->where('school_partner_id', $schoolId)
        ->findOrFail($meetingId);

        $contentItem = $meeting->LmsContent?->LmsContentItem?->first();

        if (!$contentItem) {
            return response()->json([
                'message' => 'Konten tidak ditemukan.'
            ], 404);
        }

        $serviceName = $meeting->LmsContent?->Service?->name;

        $contentRead = LmsContentRead::firstOrCreate(
            [
                'student_id' => $user->id,
                'lms_meeting_content_id' => $meeting->id,
            ],
            [
                'status' => 'opened',
            ]
        );

        // TEXT
        if (empty($contentItem->value_file)) {

            return response()->json([
                'type'         => 'text',
                'service_name' => $serviceName,
                'value_text'   => $contentItem->value_text,
                'read_status'  => $contentRead->status,
            ]);

        }

        // FILE
        $extension = pathinfo($contentItem->value_file, PATHINFO_EXTENSION);

        $mime = $this->guessMime($extension);

        $fileUrl = asset('lms-contents/' . $contentItem->value_file);

        return response()->json([
            'type'         => 'file',
            'service_name' => $serviceName,
            'file_url'     => $fileUrl,
            'mime'         => $mime,
            'read_status'  => $contentRead->status,
        ]);
    }

    public function markStudentContentRead($role, $schoolName, $schoolId, $meetingId)
    {
        $user = Auth::user();

        $meeting = LmsMeetingContent::where('school_partner_id', $schoolId)
            ->findOrFail($meetingId);

        $contentRead = LmsContentRead::firstOrCreate(
            [
                'student_id' => $user->id,
                'lms_meeting_content_id' => $meeting->id,
            ]
        );

        $contentRead->update([
            'status' => 'completed'
        ]);

        return response()->json([
            'success' => true
        ]);
    }

    public function submitVote(Request $request)
    {
        try {
            $userId = Auth::id();
            $pollId = $request->poll_id;
            $optionId = $request->option_id;

            $sudahVote = \App\Models\PollVote::where('poll_id', $pollId)
                ->where('user_id', $userId) 
                ->exists();

            if ($sudahVote) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Kamu sudah pernah mengisi polling ini!'
                ]);
            }

            \App\Models\PollVote::insert([
                'poll_id'        => $pollId,
                'poll_option_id' => $optionId,
                'user_id'        => $userId,
                'created_at'     => now(),
                'updated_at'     => now()
            ]);

            \App\Models\PollOption::where('id', $optionId)->increment('votes_count');

            return response()->json([
                'success' => true, 
                'message' => 'Hore! Suaramu berhasil direkam!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Error Sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    // function get daily reflection
    public function getDailyReflection($role, $schoolName, $schoolId)
    {
        $user = Auth::user();

        $timezone = request()->header('X-Timezone', 'Asia/Jakarta');
        $today = now($timezone)->toDateString();

        $activeClass = StudentSchoolClass::with('SchoolClass')->where('student_id', $user->id)->where('student_class_status', 'active')->first();

        if (!$activeClass) {
            return response()->json([
                'data' => [],
                'links' => '',
                'emotion_status' => config('reflection-management.emotion-status'),
            ]);
        }

        $kelasId = $activeClass->SchoolClass->kelas_id;

        $SchRefl = SchReflQuestion::with(['SchReflAnswer' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }
        ])->whereHas('SchReflTarget', function ($query) use ($kelasId) {
            $query->where('target_class_id', $kelasId);
        })->where('school_partner_id', $schoolId)->whereDate('created_at', $today)->latest()->paginate(1);

        return response()->json([
            'data' => $SchRefl->items(),
            'links' => (string) $SchRefl->links(),
            'emotion_status' => config('reflection-management.emotion-status'),
        ]);
    }

    // function daily reflection store
    public function dailyReflectionStore(Request $request, $role, $schoolName, $schoolId)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'sch_refl_question_id' => 'required|exists:sch_refl_questions,id',
            'answer' => 'required',
            'emotion_status' => 'required',
        ], [
            'sch_refl_question_id.required' => 'Refleksi tidak valid.',
            'sch_refl_question_id.exists' => 'Refleksi tidak ditemukan.',
            'answer.required' => 'Harap isi jawaban refleksi.',
            'emotion_status.required' => 'Harap pilih status emosi.',
        ]);

        $existingAnswer = SchReflAnswer::where('user_id', $user->id)
            ->where('sch_refl_question_id', $request->sch_refl_question_id)
            ->first();

        if ($existingAnswer) {
            return response()->json([
                'message' => 'Anda sudah mengisi refleksi ini sebelumnya.'
            ], 409);
        }

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $currentSchoolClass = StudentSchoolClass::where('student_id', $user->id)->where('student_class_status', 'active')->first();

        $answer = SchReflAnswer::firstOrCreate(
            [
                'user_id' => $user->id,
                'sch_refl_question_id' => $request->sch_refl_question_id,
            ],
            [
                'school_class_id' => $currentSchoolClass->school_class_id,
                'answer' => $request->answer,
                'emotion_status' => $request->emotion_status,
            ]
        );

        $reflection = SchReflQuestion::with(['SchReflAnswer'])->findOrFail($request->sch_refl_question_id);

        $totalAnswers = SchReflAnswer::where('sch_refl_question_id', $reflection->id)->count();

        $emotionConfig = config('reflection-management.emotion-status');

        $emotionCounts = SchReflAnswer::select(
                'emotion_status',
                DB::raw('COUNT(*) as total')
            )
            ->where('sch_refl_question_id', $reflection->id)
            ->groupBy('emotion_status')
            ->pluck('total', 'emotion_status');

        $formattedEmotions = collect($emotionConfig)->map(function ($emotion) use ($emotionCounts, $totalAnswers) {

            $total = $emotionCounts[$emotion['value']] ?? 0;

            preg_match('/hover:bg-(\w+)-50/', $emotion['classes']['hover'], $matches);

            $color = $matches[1] ?? 'slate';

            return [
                'label' => $emotion['label'],
                'value' => $emotion['value'],
                'icon' => $emotion['icon'],
                'color' => $color,
                'total' => $total,
                'percentage' => $totalAnswers > 0 ? round(($total / $totalAnswers) * 100) : 0,
            ];
        });

        $targetKelasIds = $reflection->SchReflTarget->pluck('target_class_id');

        $schoolClassIds = SchoolClass::where('school_partner_id', $schoolId)->where('tahun_ajaran', $reflection->tahun_ajaran)->whereIn('kelas_id', $targetKelasIds)->pluck('id');

        $totalStudents = StudentSchoolClass::whereIn('school_class_id', $schoolClassIds)->where('student_class_status', 'active')->count();

        $participationPercentage = $totalStudents > 0 ? round(($totalAnswers / $totalStudents) * 100, 1) : 0;

        $reflectionTargets = SchReflTarget::with('Kelas')->where('sch_refl_question_id', $reflection->id)->get();

        $targetClasses = $reflectionTargets->map(function ($target) {
            return $target->Kelas->kelas;
        });

        $schoolClasses = SchoolClass::with('Kelas')
            ->where('school_partner_id', $schoolId)
            ->where('tahun_ajaran', $reflection->tahun_ajaran)
            ->whereIn('kelas_id', $targetKelasIds)
            ->get();

        $barChart = $schoolClasses->map(function ($schoolClass) use ($reflection) {

            $studentIds = StudentSchoolClass::where(
                    'school_class_id',
                    $schoolClass->id
                )
                ->where('student_class_status', 'active')
                ->pluck('student_id');

            $totalSiswa = $studentIds->count();

            $answered = SchReflAnswer::where(
                    'sch_refl_question_id',
                    $reflection->id
                )
                ->whereIn('user_id', $studentIds)
                ->count();

            return [
                'kelas' => $schoolClass->Kelas->kelas,
                'answered' => $answered,
                'unanswered' => max(0, $totalSiswa - $answered),
            ];
        });

        $positive = 0;
        $neutral = 0;
        $attention = 0;

        foreach ($emotionConfig as $emotion) {
            $total = $emotionCounts[$emotion['value']] ?? 0;

            switch ($emotion['category']) {

                case 'positive':
                    $positive += $total;
                    break;

                case 'neutral':
                    $neutral += $total;
                    break;

                case 'attention':
                    $attention += $total;
                    break;
            }
        }

        $emotion = collect($emotionConfig)
            ->firstWhere('value', $answer->emotion_status);

        preg_match(
            '/hover:bg-(\w+)-50/',
            $emotion['classes']['hover'],
            $matches
        );

        $answerData = [
            'nama_lengkap' => $user->StudentProfile->nama_lengkap,
            'class_name' => $currentSchoolClass->SchoolClass->class_name,
            'answer' => $answer->answer,
            'emotion_status' => $answer->emotion_status,
            'emotion_color' => $matches[1] ?? 'slate',
        ];

        broadcast(new DailyReflectionLivePreview('SchReflAnswer', 'create', [
                'reflection_id' => $reflection->id,
                'new_answer' => $answerData,
                'bar_chart' => [
                    'labels' => $targetClasses,
                    'answered' => $barChart->pluck('answered'),
                    'unanswered' => $barChart->pluck('unanswered'),
                ],

                'emotions' => $formattedEmotions,
                'total_responden' => $totalAnswers,
                'participation_percentage' => $participationPercentage,
                'positive' => $positive,
                'neutral' => $neutral,
                'attention' => $attention,
            ]
        ))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Jawaban refleksi berhasil disimpan.',
        ]);
    }

    // function get student assessment cheating history
    public function getStudentAssessmentCheatingHistory()
    {
        $user = Auth::user();

        // QUERY CHEATING
        $query = StudentAssessmentAttempt::with(['UserAccount.StudentProfile', 'SchoolAssessment.Mapel', 'SchoolAssessment.SchoolClass', 'SchoolAssessment.SchoolAssessmentType'])
            ->where('status', 'cheating')->where('student_id', $user->id);

        $data = $query->latest()->get();

        return response()->json([
            'data' => $data,
        ]);
    }
    
    // Tambahkan di dalam class StudentDashboardController, misalnya di bawah fungsi submitVote
    public function markAnnouncementAsRead(Request $request)
    {
        try {
            $userId = Auth::id();
            $announcementId = $request->announcement_id;

            // Pastikan data tidak kosong
            if (!$announcementId) {
                return response()->json(['success' => false, 'message' => 'ID Pengumuman tidak valid.']);
            }

            // Catat ke database jika belum ada (mencegah duplikasi data jika siswa klik berkali-kali)
            DB::table('announcement_views')->updateOrInsert(
                ['announcement_id' => $announcementId, 'user_id' => $userId],
                ['created_at' => now(), 'updated_at' => now()]
            );

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\StudentAssessmentAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudentDashboardController extends Controller
{
    public function index(Request $request, $role, $schoolName, $schoolId)
    {
        $user = Auth::user();

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
                $schoolName = $schoolRecord->school_name ?? $schoolRecord->name ?? 'Sekolah Mitra';
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
            $materiSiswaRaw = \App\Models\LmsMeetingContent::with(['LmsContent.LmsContentItem', 'Mapel'])
                ->where('school_class_id', $studentClassId)
                ->where('is_active', 1)
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
            $tugasRaw = $semuaAsesmenSiswa->filter(function($item) {
                return $item->SchoolAssessmentType 
                    && $item->SchoolAssessmentType->AssessmentMode 
                    && $item->SchoolAssessmentType->AssessmentMode->code === 'project';
            })->sortBy('end_date');

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
                        'deadline'    => $tugas->end_date
                    ]);
                }
            }

            // B. JADWAL UJIAN (Mode: non-project)
            $ujianRaw = $semuaAsesmenSiswa->filter(function($item) {
                return $item->SchoolAssessmentType 
                    && $item->SchoolAssessmentType->AssessmentMode 
                    && $item->SchoolAssessmentType->AssessmentMode->code !== 'project';
            })->sortBy('start_date');

            foreach ($ujianRaw as $ujian) {
                if (\Carbon\Carbon::parse($ujian->end_date)->isPast() && !\Carbon\Carbon::parse($ujian->end_date)->isToday()) {
                    continue; 
                }

                $tglMulai = \Carbon\Carbon::parse($ujian->start_date);
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
                    'tanggal' => $tglMulai->format('d M Y'),
                    'waktu'   => $tglMulai->format('H:i'),
                    'h_min'   => $h_min,
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
    $pengumumanTerkini = [];
    if ($schoolId) {
        $pengumumanTerkini = DB::table('announcements')
            ->leftJoin('users', 'announcements.author_id', '=', 'users.id')
            ->where('announcements.school_partner_id', $schoolId)
            ->where('announcements.target', 'Siswa') // Wajib target Siswa
            ->where('announcements.author_role', 'Guru') // Hierarki: Murid hanya terima dari Guru
            ->where(function ($query) use ($studentClassId) {
                $query->where('announcements.target_class_id', $studentClassId)
                    ->orWhereNull('announcements.target_class_id');
            })
            ->select('announcements.*', 'users.name as nama_pengirim')
            // Cek status baca
            ->selectRaw('(EXISTS (SELECT 1 FROM announcement_views WHERE announcement_views.announcement_id = announcements.id AND announcement_views.user_id = ?)) as is_read', [$studentUserId])
            ->orderBy('announcements.created_at', 'desc')
            ->take(4)
            ->get();
    }
        return view('features.lms.students.dashboard', compact(
            'dataSiswa', 'schoolName', 'agendaSekolah', 'selectedDate', 
            'jadwalUjian', 'statistikMapel', 'activePolls', 'votedPolls', 'pengumumanTerkini',
            'jadwalHariIni', 'hariIni', 'selectedJadwalDate', 'hariDipilih',
            'unreadModules', 'pendingTasks'
        ));
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
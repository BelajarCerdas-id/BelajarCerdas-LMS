<?php

namespace App\Http\Controllers;

use App\Events\LmsSchoolSubscription;
use App\Models\SchoolAssessmentType;
use App\Models\SchoolLmsSubscription;
use App\Models\SchoolPartner;
use App\Models\StudentAssessmentAttempt;
use App\Models\TeacherMapel;
use App\Services\ClassName\ClassNameService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LmsController extends Controller
{
    // function extract class level
    private function extractClassLevel($className)
    {
        $classNameService = new ClassNameService();
        return $classNameService->extractClassLevel($className);
    }
    
    // function lms school subscription view
    public function lmsSchoolSubscriptionView($role)
    {
        return view('features.lms.administrator.lms-school-subscription', compact('role'));
    }

    // function pagiante lms school subscription
    public function paginateLmsSchoolSubscription(Request $request)
    {
        $today = now()->format('Y-m-d');

        $lmsSchoolSubscription = SchoolLmsSubscription::whereHas('Transaction', function ($query) {
            $query->where('transaction_status', 'Berhasil');
        })->where('end_date', '<', $today)->get();

        if ($lmsSchoolSubscription) {
            foreach ($lmsSchoolSubscription as $history) {
                $history->update([
                    'subscription_status' => 'tidak_aktif'
                ]);
            }
        }

        $lmsSchoolSubscription = SchoolPartner::with(['UserAccount.SchoolStaffProfile', 'SchoolLmsSubscription' => function ($query) {
            $query->whereHas('transaction', function ($q) {
                $q->where('transaction_status', 'Berhasil');
            })->orderByDesc('start_date')->limit(1); // ambil subscription terbaru
        }
        ])->orderBy('updated_at', 'desc');


        // Filter school
        if ($request->filled('search_school')) {
            $search = $request->search_school;
            $lmsSchoolSubscription->where('nama_sekolah', 'LIKE', "%{$search}%");
        }

        $paginated = $lmsSchoolSubscription->paginate(20);

        return response()->json([
            'data' => $paginated->values(), // flat array, bukan nested
            'links' => (string) $paginated->links(),
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
            'lmsAcademicManagement' => '/lms/:role/school-subscription/:schoolName/:schoolId/academic-management',
        ]);
    }

    // function activate lms school subscription
    public function lmsSchoolSubscriptionActivate(Request $request, $subscriptionId)
    {
        $subscription = SchoolLmsSubscription::findOrFail($subscriptionId);

        $subscription->update([
            'subscription_status' => $request->subscription_status,
        ]);

        broadcast(new LmsSchoolSubscription($subscription))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Subscription status updated successfully',
            'subscription' => $subscription
        ]);
    }

    // function edit school logo
    public function editSchoolLogo(Request $request, $schoolName, $schoolId)
    {
        // VALIDATION
        $validator = Validator::make($request->all(), [
            'school_logo' => 'required|image|mimes:jpg,jpeg,png|max:2000'
        ], [
            'school_logo.required' => 'Logo wajib diupload.',
            'school_logo.image'    => 'File harus berupa gambar.',
            'school_logo.mimes'    => 'Format harus JPG, JPEG, atau PNG.',
            'school_logo.max'      => 'Ukuran maksimal 2MB.'
        ]);

        // HANDLE ERROR 422
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        // AMBIL DATA
        $school = SchoolPartner::findOrFail($schoolId);

        // HANDLE FILE
        if ($request->hasFile('school_logo')) {

            $file = $request->file('school_logo');

            // nama file unik
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();

            $destinationPath = public_path('school-logo');

            // buat folder kalau belum ada
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            // HAPUS FILE LAMA
            if (!empty($school->logo) && file_exists(public_path($school->logo))) {
                unlink(public_path($school->logo));
            }

            // SIMPAN FILE
            $file->move($destinationPath, $filename);

            // simpan ke db
            $school->logo = 'school-logo/' . $filename;
            $school->save();
        }

        // RESPONSE SUCCESS
        return response()->json([
            'message'  => 'Logo berhasil diperbarui',
            'logo_url' => asset($school->logo)
        ], 200);
    }

    // function get teacher assessment cheating history
    public function getTeacherAssessmentCheatingHistory(Request $request, $schoolName, $schoolId)
    {
        $user = Auth::user();

        $schoolPartner = SchoolPartner::findOrFail($schoolId);
        $jenjang = strtoupper($schoolPartner->jenjang_sekolah);

        $startLevelMap = [
            'SD'  => 1, 'MI'  => 1,
            'SMP' => 7, 'MTS' => 7,
            'SMA' => 10, 'SMK' => 10,
            'MA'  => 10, 'MAK' => 10,
        ];

        $defaultLevel = $startLevelMap[$jenjang] ?? 1;

        // TEACHER MAPEL
        $baseQuery = TeacherMapel::where('user_id', $user->id)->where('is_active', true)->whereHas('SchoolClass', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            })
            ->whereHas('Mapel', function ($q) use ($schoolId) {
                // MAPEL KHUSUS SEKOLAH
                $q->whereHas('SchoolMapel', function ($q1) use ($schoolId) {
                    $q1->where('school_partner_id', $schoolId)
                        ->where('is_active', 1);
                })

                // ATAU MAPEL GLOBAL
                ->orWhere(function ($q2) use ($schoolId) {
                    $q2->whereNull('school_partner_id')->where('status_mata_pelajaran', 'active')

                        // JANGAN AMBIL JIKA ADA SCHOOL OVERRIDE
                        ->whereDoesntHave('SchoolMapel', function ($sq) use ($schoolId) {
                            $sq->where('school_partner_id', $schoolId);
                    });
                });
            })->with(['Mapel', 'SchoolClass' => function ($q) {
                    $q->withCount(['StudentSchoolClass as student_school_class_count' => function ($q) {
                        $q->where('student_class_status', 'active')
                        ->where(function ($sub) {
                            $sub->whereNull('academic_action')
                                ->orWhere('academic_action', '');
                        });
                    }]);
                }
            ]);

        $allData = $baseQuery->get();

        // TAHUN AJARAN
        $tahunAjaran = $allData->pluck('SchoolClass.tahun_ajaran')->filter()->unique()->sortDesc()->values();

        $searchYear = $request->search_year ?? $tahunAjaran->first();

        $dataByYear = $allData->where('SchoolClass.tahun_ajaran', $searchYear);

        // KELAS LEVEL
        $classLevels = $dataByYear->pluck('SchoolClass.class_name')->map(fn($c) => (int) $this->extractClassLevel($c))->unique()->sort()->values();

        $selectedClass = $request->search_class ?? $classLevels->first() ?? $defaultLevel;

        $dataByClass = $dataByYear->filter(function ($item) use ($selectedClass) {
            return (int)$this->extractClassLevel($item->SchoolClass->class_name) === (int)$selectedClass;
        });

        // MAPEL
        $subjects = $dataByClass->unique('mapel_id')->map(fn($item) => [
            'id' => $item->mapel_id,
            'name' => $item->Mapel->mata_pelajaran ?? '-'
        ])->values();

        $subjectId = $request->subject_id ?? null;

        // ambil assessment type
        $schoolAssessmentType = SchoolAssessmentType::where('school_partner_id', $schoolId)->where('is_active', 1)->get()->map(fn($t) => [
            'id' => $t->id,
            'name' => $t->name
        ]);

        // ambil filter assessment type
        $assessmentTypeId = $request->search_assessment_type ?? null;

        // AMBIL ASSIGNMENT
        $teacherAssignments = $dataByClass;

        // QUERY CHEATING
        $query = StudentAssessmentAttempt::with(['UserAccount.StudentProfile', 'SchoolAssessment.Mapel', 'SchoolAssessment.SchoolClass', 'SchoolAssessment.SchoolAssessmentType'])
            ->where('status', 'cheating')->whereHas('SchoolAssessment', function ($q) use ($teacherAssignments, $schoolId, $user, $searchYear, $subjectId, $assessmentTypeId) {
                $q->where('school_partner_id', $schoolId);

                $q->where(function ($subQ) use ($teacherAssignments, $user) {
                    foreach ($teacherAssignments as $assign) {
                        $subQ->orWhere(function ($inner) use ($assign, $user) {
                            $inner->where('school_class_id', $assign->school_class_id)
                                ->where('mapel_id', $assign->mapel_id)
                                ->where('user_id', $user->id);
                        });
                    }
                });

                if ($searchYear) {
                    $q->whereHas('SchoolClass', function ($qq) use ($searchYear) {
                        $qq->where('tahun_ajaran', $searchYear);
                    });
                }

                if ($subjectId) {
                    $q->where('mapel_id', $subjectId);
                }

                // filter assessment type
                if ($assessmentTypeId) {
                    $q->where('assessment_type_id', $assessmentTypeId);
                }
            });

        $data = $query->latest()->get();

        return response()->json([
            'data' => $data,
            'tahunAjaran' => $tahunAjaran,
            'selectedYear' => $searchYear,
            'className' => $classLevels,
            'selectedClass' => $selectedClass,
            'subject' => $subjects,
            'schoolAssessmentType' => $schoolAssessmentType,
        ]);
    }
   public function lmsTeacherView($role,$schoolName, $schoolId)
    {
        $tanggalDipilih = request('date', date('Y-m-d'));
        $user = \Illuminate\Support\Facades\Auth::user();
    
        if (!$user || $user->role !== 'Guru') {
            abort(403, 'Akses Ditolak.');
        }   

        $teacherId = $user->teacher_id ?? $user->id; 
        $userId = $user->id; 

        // ========================================================
        // 0. AMBIL DAFTAR KELAS YANG DIAJAR GURU INI
        // (Hanya dari tabel teacher_mapels sesuai user yang login)
        // ========================================================
        $daftarKelas = \Illuminate\Support\Facades\DB::table('lesson_schedule_items')
            ->join('lesson_schedules', 'lesson_schedule_items.lesson_schedule_id', '=', 'lesson_schedules.id')
            ->join('school_classes', 'lesson_schedules.class_id', '=', 'school_classes.id')
            ->where('lesson_schedules.school_partner_id', $schoolId)
            ->where('lesson_schedule_items.teacher_id', $teacherId)
            ->select('school_classes.id', 'school_classes.class_name')
            ->distinct() // Mencegah duplikasi nama kelas jika diajar 2 sesi
            ->orderBy('school_classes.class_name', 'asc')
            ->get();
        // -- JADWAL & KELAS --
        $totalKelas = \Illuminate\Support\Facades\DB::table('lesson_schedule_items')
            ->join('lesson_schedules', 'lesson_schedule_items.lesson_schedule_id', '=', 'lesson_schedules.id')
            ->where('lesson_schedules.school_partner_id', $schoolId)
            ->where('lesson_schedule_items.teacher_id', $teacherId)
            ->distinct('lesson_schedules.class_id')
            ->count('lesson_schedules.class_id');

        $englishDaySekarang = \Carbon\Carbon::parse($tanggalDipilih)->format('l');
        $mapHari = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
        ];
        $hariIni = $mapHari[$englishDaySekarang] ?? 'Senin';

        $dbSchedules = \Illuminate\Support\Facades\DB::table('lesson_schedule_items')
            ->join('lesson_schedules', 'lesson_schedule_items.lesson_schedule_id', '=', 'lesson_schedules.id')
            ->where('lesson_schedules.school_partner_id', $schoolId)
            ->where('lesson_schedule_items.teacher_id', $teacherId)
            ->where('lesson_schedule_items.day_of_week', $hariIni)
            ->where('lesson_schedules.status', 'published')
            ->orderBy('lesson_schedule_items.start_time', 'asc')
            ->select(
                'lesson_schedules.id',
                'lesson_schedule_items.subject_name',
                'lesson_schedule_items.start_time',
                'lesson_schedule_items.end_time',
                'lesson_schedules.class_name'
            )
            ->get();

        $jadwalMengajar = [];
        $totalJadwalHariIni = 0;
        $currentGroup = null;

        foreach ($dbSchedules as $schedule) {
            $totalJadwalHariIni++; 

            if (!$currentGroup ||
                $currentGroup->mapel !== $schedule->subject_name ||
                $currentGroup->kelas !== $schedule->class_name) {

                if ($currentGroup) {
                    $jadwalMengajar[] = $currentGroup;
                }

                $currentGroup = (object)[
                    'ids'     => $schedule->id,
                    'mapel'   => $schedule->subject_name,
                    'kelas'   => $schedule->class_name,
                    'ruangan' => 'Ruang ' . $schedule->class_name,
                    'details' => [
                        (object)[
                            'jam_mulai'   => substr($schedule->start_time, 0, 5),
                            'jam_selesai' => substr($schedule->end_time, 0, 5)
                        ]
                    ]
                ];
            } else {
                $currentGroup->ids .= ',' . $schedule->id;
                $currentGroup->details[] = (object)[
                    'jam_mulai'   => substr($schedule->start_time, 0, 5),
                    'jam_selesai' => substr($schedule->end_time, 0, 5)
                ];
            }
        }
        
        if ($currentGroup) {
            $jadwalMengajar[] = $currentGroup;
        }

        $monthlyEvents = \App\Models\AcademicCalendar::where('school_partner_id', $schoolId)
            ->whereDate('date', $tanggalDipilih) 
            ->get();

        // ========================================================
        // 1. PENGUMUMAN (HIERARKI KOMUNIKASI)
        // ========================================================
        
        $pengumumanDariSekolah = \Illuminate\Support\Facades\DB::table('announcements')
            ->leftJoin('users', 'announcements.author_id', '=', 'users.id') // Join untuk dapat nama Kepsek
            ->where('announcements.school_partner_id', $schoolId)
            ->whereIn('announcements.author_role', ['Kepala Sekolah', 'Wakil Kepala Sekolah'])
            ->where('announcements.target', 'Guru') 
            ->select('announcements.*', 'users.name as nama_pengirim')
            // 👇 Tambahkan pengecekan apakah Guru ini sudah membacanya
            ->selectRaw('(EXISTS (SELECT 1 FROM announcement_views WHERE announcement_views.announcement_id = announcements.id AND announcement_views.user_id = ?)) as is_read', [$userId])
            ->orderBy('announcements.created_at', 'desc')
            ->take(5)
            ->get();

        // B. Pengumuman Keluar: Dari Guru Ini -> Target: Siswa
        $pengumumanKeSiswa = \Illuminate\Support\Facades\DB::table('announcements')
            ->where('school_partner_id', $schoolId)
            ->where('author_id', $userId)
            ->where('target', 'Siswa') 
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // ========================================================
        // 2. POLLING DARI GURU ITU SENDIRI (TAB: Polling Kelas Saya)
        // ========================================================
        $recentPolls = \App\Models\Poll::where('school_partner_id', $schoolId)
            ->where('author_id', $userId) 
            ->orderBy('created_at', 'desc')
            ->take(4) 
            ->get()
            ->map(function($poll) {
                if ($poll->class_id) {
                    $kelas = \Illuminate\Support\Facades\DB::table('school_classes')->where('id', $poll->class_id)->first();
                    $poll->nama_kelas = $kelas ? $kelas->class_name : 'Kelas Dihapus';
                } else {
                    $poll->nama_kelas = 'Semua Kelas (Global)';
                }
                return $poll;
            });

        // Render data grafik untuk polling buatan sendiri
        foreach ($recentPolls as $poll) {
            $options = \App\Models\PollOption::where('poll_id', $poll->id)->get();
            $labels = [];
            $votes = [];
            
            foreach ($options as $opt) {
                $labels[] = $opt->option_text;
                $count = \Illuminate\Support\Facades\DB::table('poll_votes')
                            ->where('poll_option_id', $opt->id)
                            ->count();
                $votes[] = $count; 
            }
            
            $poll->chart_labels = json_encode($labels);
            $poll->chart_data = json_encode($votes);
        }

        // ========================================================
        // 3. POLLING DARI KEPALA SEKOLAH / WAKIL (TAB: Dari Sekolah)
        // ========================================================
        
        // 👇 A. Ambil dulu daftar ID kelas yang diajar oleh guru ini (dari Jadwal)
        $classIdsYangDiajarGuru = \Illuminate\Support\Facades\DB::table('lesson_schedule_items')
            ->join('lesson_schedules', 'lesson_schedule_items.lesson_schedule_id', '=', 'lesson_schedules.id')
            ->where('lesson_schedules.school_partner_id', $schoolId)
            ->where('lesson_schedule_items.teacher_id', $teacherId)
            ->pluck('lesson_schedules.class_id')
            ->unique()
            ->toArray();

        // 👇 B. Query Polling difilter berdasarkan kelas yang diajar
        $pollingDariSekolah = \App\Models\Poll::with('PollOptions')
            ->where('school_partner_id', $schoolId)
            ->whereIn('author_role', ['Kepala Sekolah', 'Wakil Kepala Sekolah'])
            ->whereIn('target', ['Semua Guru', 'Semua Warga Sekolah', 'Semua'])
            ->where(function($query) use ($classIdsYangDiajarGuru) {
                // Tampilkan jika targetnya Global (Null) ATAU khusus kelas yang diajar guru ini
                $query->whereNull('class_id') 
                      ->orWhereIn('class_id', $classIdsYangDiajarGuru); 
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($poll) use ($userId) {
                if ($poll->class_id) {
                    $kelas = \Illuminate\Support\Facades\DB::table('school_classes')->where('id', $poll->class_id)->first();
                    $poll->nama_kelas = $kelas ? $kelas->class_name : 'Kelas Dihapus';
                } else {
                    $poll->nama_kelas = 'Semua Kelas (Global)';
                }

                $voteRecord = \Illuminate\Support\Facades\DB::table('poll_votes')
                    ->where('poll_id', $poll->id)
                    ->where('user_id', $userId)
                    ->first();
                
                if ($voteRecord) {
                    $poll->has_voted = true;
                    $poll->voted_option_id = $voteRecord->poll_option_id; 
                } else {
                    $poll->has_voted = false;
                    $poll->voted_option_id = null;
                }
                
                return $poll;
            });

        return view('features.lms.teacher.dashboard', compact(
            'role',
            'schoolName', 
            'schoolId', 
            'totalKelas', 
            'totalJadwalHariIni', 
            'jadwalMengajar', 
            'monthlyEvents',
            'recentPolls',
            'pollingDariSekolah',
            'pengumumanDariSekolah', 
            'pengumumanKeSiswa',     
            'hariIni',
            'tanggalDipilih',
            'daftarKelas'
        ));
    }
    public function classDetailView($role, $schoolName, $schoolId, $scheduleId)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user || $user->role !== 'Guru') abort(403, 'Akses Ditolak.');

        $jadwal = \App\Models\LessonSchedule::findOrFail($scheduleId);
        $classId = $jadwal->class_id;

        // 1. Total Siswa di Kelas Ini
        $totalSiswa = \Illuminate\Support\Facades\DB::table('student_school_classes')
            ->where('school_class_id', $classId) 
            ->where('student_class_status', 'active')
            ->count();

        // 2. Pengumuman Terkini (DARI GURU INI -> UNTUK SISWA DI KELAS INI)
        $pengumumanTerkini = \Illuminate\Support\Facades\DB::table('announcements')
            ->where('school_partner_id', $schoolId)
            ->where('author_id', $user->id) // Hanya pengumuman buatan Guru ini
            ->where('target', 'Siswa')    // Targetnya ke Siswa
            ->where(function($query) use ($classId) {
                $query->where('target_class_id', $classId) // Khusus kelas ini
                      ->orWhereNull('target_class_id');    // Atau global untuk semua kelasnya
            })
            ->orderBy('created_at', 'desc')
            ->take(4) 
            ->get();

        // 3. Kehadiran Hari Ini
        $today = date('Y-m-d');
        $attendances = \Illuminate\Support\Facades\DB::table('attendances')
            ->where('schedule_id', $scheduleId)
            ->where('date', $today)
            ->get();

        // =========================================================
        // 4. DATA MATERI (Menggunakan Eloquent ORM & Ambil URL File)
        // =========================================================
        $materiKelasRaw = \App\Models\LmsMeetingContent::with(['LmsContent.LmsContentItem', 'Mapel'])
            ->where('school_class_id', $classId)
            ->where('teacher_id', $user->id)
            ->orderBy('meeting_date', 'desc')
            ->get();

        $materiKelas = collect();
        foreach ($materiKelasRaw as $materi) {
            $judul = 'Materi Pembelajaran';
            $fileUrl = null; 

            if ($materi->LmsContent && $materi->LmsContent->LmsContentItem && $materi->LmsContent->LmsContentItem->count() > 0) {
                $item = $materi->LmsContent->LmsContentItem->first();
                $judul = $item->original_filename ?? substr(strip_tags($item->value_text), 0, 50) ?? 'Materi Pembelajaran';
                
                if (!empty($item->value_file)) {
                    $fileUrl = asset('lms-contents/' . $item->value_file);
                }
            }

            $materiKelas->push((object)[
                'judul'         => $judul,
                'mapel'         => $materi->Mapel->mata_pelajaran ?? 'Mata Pelajaran',
                'tanggal_rilis' => $materi->meeting_date,
                'pertemuan'     => $materi->meeting_number,
                'is_active'     => $materi->is_active,
                'file_url'      => $fileUrl
            ]);
        }

        // =========================================================
        // 5 & 6. DATA TUGAS & UJIAN
        // =========================================================
        $semuaAsesmen = \App\Models\SchoolAssessment::with(['SchoolAssessmentType.AssessmentMode', 'Mapel'])
            ->where('school_class_id', $classId)
            ->where('user_id', $user->id)
            ->get();

        // ---- FILTER TUGAS (Mode: project) ----
        $tugasKelasRaw = $semuaAsesmen->filter(function($item) {
            return $item->SchoolAssessmentType 
                && $item->SchoolAssessmentType->AssessmentMode 
                && $item->SchoolAssessmentType->AssessmentMode->code === 'project';
        })->sortByDesc('end_date');

        $tugasKelas = collect();
        foreach ($tugasKelasRaw as $tugas) {
            $terkumpul = \Illuminate\Support\Facades\DB::table('class_task_submissions')
                ->where('task_id', $tugas->id)
                ->whereNotNull('score')
                ->count(); 
            
            $fileName = $tugas->assessment_value_file;
            $filePath = public_path('assessment/assessment-file/' . $fileName);
            
            if (!empty($fileName) && file_exists($filePath)) {
                $fileUrl = asset('assessment/assessment-file/' . $fileName);
            } else {
                $fileUrl = null; 
            }

            $tugasKelas->push((object)[
                'id'        => $tugas->id,
                'judul'     => $tugas->title,
                'mapel'     => $tugas->Mapel->mata_pelajaran ?? 'Mata Pelajaran',
                'deadline'  => $tugas->end_date,
                'status'    => $tugas->status,
                'terkumpul' => $terkumpul,
                'file_url'  => $fileUrl
            ]);
        }

        $ujianKelasRaw = $semuaAsesmen->filter(function($item) {
            return $item->SchoolAssessmentType 
                && $item->SchoolAssessmentType->AssessmentMode 
                && $item->SchoolAssessmentType->AssessmentMode->code !== 'project';
        })->sortBy('start_date');

        $ujianKelas = collect();
        foreach ($ujianKelasRaw as $ujian) {
            $ujianKelas->push((object)[
                'id'            => $ujian->id,
                'judul'         => $ujian->title,
                'tipe'          => $ujian->SchoolAssessmentType->name ?? 'Ujian',
                'mapel'         => $ujian->Mapel->mata_pelajaran ?? 'Mata Pelajaran',
                'tanggal_ujian' => $ujian->start_date
            ]);
        }

        // =========================================================
        // 7. STATISTIK KELAS
        // =========================================================
        $statistik = (object)[
            'totalSiswa'      => $totalSiswa,
            'totalMateri'     => $materiKelas->count(), 
            'totalAssessment' => $ujianKelas->count(), 
            'totalPr'         => $tugasKelas->count(), 
            'hadir'           => $attendances->where('status', 'hadir')->count(), 
            'izin'            => $attendances->where('status', 'izin')->count(),
            'sakit'           => $attendances->where('status', 'sakit')->count(),
            'alpa'            => $attendances->where('status', 'alpa')->count(),
        ];

        return view('features.lms.teacher.class_detail', compact(
            'role',
            'schoolName', 
            'schoolId', 
            'jadwal',    
            'statistik',
            'pengumumanTerkini',
            'materiKelas',
            'tugasKelas',
            'ujianKelas'
        ));
    }
    public function getStudentsForAttendance($classId)
    {
        try {
            $today = date('Y-m-d');
            $scheduleId = request('schedule_id'); 
            
            $students = \Illuminate\Support\Facades\DB::table('student_school_classes')
                ->join('student_profiles', 'student_school_classes.student_id', '=', 'student_profiles.user_id')
                ->leftJoin('attendances', function($join) use ($today, $scheduleId) {
                    $join->on('student_school_classes.student_id', '=', 'attendances.student_id')
                         ->where('attendances.date', '=', $today)
                         ->where('attendances.schedule_id', '=', $scheduleId);
                })
                ->where('student_school_classes.school_class_id', $classId)
                ->where('student_school_classes.student_class_status', 'active')
                ->select(
                    'student_profiles.user_id as id', 
                    'student_profiles.nama_lengkap as name',
                    'attendances.status' 
                )
                ->orderBy('student_profiles.nama_lengkap', 'asc')
                ->get();

            return response()->json($students);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function saveAttendance(Request $request) 
    {
        try {
            $scheduleId = $request->schedule_id;
            $date = date('Y-m-d');
            
            foreach($request->attendance as $studentId => $status) {
                \Illuminate\Support\Facades\DB::table('attendances')->updateOrInsert(
                    [
                        'schedule_id' => $scheduleId, 
                        'student_id'  => $studentId, 
                        'date'        => $date
                    ],
                    [
                        'status'      => $status, 
                        'updated_at'  => now()
                    ]
                );
            }
            
            return response()->json(['success' => true, 'message' => 'Presensi berhasil disimpan ke database!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }
    public function lmsStudentView($schoolName = null, $schoolId = null)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'Siswa') {
            abort(403, 'Akses Ditolak. Halaman ini khusus untuk Siswa.');
        }
        $school = \App\Models\SchoolPartner::find($schoolId);
        $jadwalSiswa = []; 
        $hariInggris = date('l');
        $mapHari = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
        ];
        $hariIni = $mapHari[$hariInggris] ?? 'Senin';
        
        return view('features.lms.students.dashboard', compact(
            'schoolName', 
            'schoolId', 
            'user',
            'jadwalSiswa',
            'hariIni'
        ));
    }
    // Tambahkan di bagian bawah, sebelum penutup class
    public function storePengumuman(\Illuminate\Http\Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        
        // Validasi
        $request->validate([
            'title'     => 'required|string|max:255',
            'type'      => 'required|in:info,penting',
            'content'   => 'required|string',
            'school_id' => 'required',
            'class_id'  => 'nullable' // 👈 Tangkap class_id dari form
        ]);

        try {

            $classIds = $request->class_id;

            // Jika semua kelas dicentang / global
            if (empty($classIds)) {

                \Illuminate\Support\Facades\DB::table('announcements')->insert([
                    'school_partner_id' => $request->school_id,
                    'target_class_id'   => null,
                    'author_id'         => $user->id,
                    'author_role'       => 'Guru',
                    'target'            => 'Siswa',
                    'title'             => $request->title,
                    'type'              => $request->type,
                    'content'           => $request->input('content'),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

            } else {

                foreach ($classIds as $classId) {

                    \Illuminate\Support\Facades\DB::table('announcements')->insert([
                        'school_partner_id' => $request->school_id,
                        'target_class_id'   => $classId,
                        'author_id'         => $user->id,
                        'author_role'       => 'Guru',
                        'target'            => 'Siswa',
                        'title'             => $request->title,
                        'type'              => $request->type,
                        'content'           => $request->input('content'),
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);

                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Pengumuman berhasil dikirim!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    public function markAnnouncementAsRead(Request $request)
    {
        try {
            $userId = \Illuminate\Support\Facades\Auth::id();
            $announcementId = $request->announcement_id;

            if (!$announcementId) {
                return response()->json(['success' => false, 'message' => 'ID tidak valid.']);
            }

            \Illuminate\Support\Facades\DB::table('announcement_views')->updateOrInsert(
                ['announcement_id' => $announcementId, 'user_id' => $userId],
                ['created_at' => now(), 'updated_at' => now()]
            );

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    public function storeTugas(Request $request)
    {
        try {
            $user = \Illuminate\Support\Facades\Auth::user();
            
            \Illuminate\Support\Facades\DB::table('class_tasks')->insert([
                'school_partner_id' => $request->school_id,
                'class_id'          => $request->class_id,
                'teacher_id'        => $user->id,
                'judul_tugas'       => $request->judul_tugas,
                'deadline'          => $request->deadline,
                'max_score'         => $request->max_score,
                'instructions'      => $request->instructions,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Tugas berhasil dipublikasikan!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }
    public function getTaskSubmissions($taskId)
    {
        try {
            // PERBAIKAN: Harus mencari di tabel school_assessments, BUKAN class_tasks
            $task = \Illuminate\Support\Facades\DB::table('school_assessments')->where('id', $taskId)->first();
            
            if (!$task) {
                return response()->json(['error' => 'Data Tugas tidak ditemukan di database (ID: '.$taskId.').'], 404);
            }

            // PERBAIKAN: Filter siswa murni hanya untuk Kelas di mana tugas ini dibuat
            $students = \Illuminate\Support\Facades\DB::table('student_school_classes')
                ->join('student_profiles', 'student_school_classes.student_id', '=', 'student_profiles.user_id')
                ->leftJoin('class_task_submissions', function($join) use ($taskId) {
                    $join->on('student_school_classes.student_id', '=', 'class_task_submissions.student_id')
                         ->where('class_task_submissions.task_id', '=', $taskId);
                })
                ->where('student_school_classes.school_class_id', $task->school_class_id) // Menggunakan school_class_id yang benar
                ->where('student_school_classes.student_class_status', 'active')
                ->select(
                    'student_profiles.user_id as student_id',
                    'student_profiles.nama_lengkap as name',
                    'class_task_submissions.id as submission_id',
                    'class_task_submissions.score',
                    'class_task_submissions.status'
                )
                ->orderBy('student_profiles.nama_lengkap', 'asc')
                ->get();

            // Default nilai maks
            $task->max_score = 100;

            return response()->json([
                'task' => $task,
                'students' => $students
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan sistem: ' . $e->getMessage()], 500);
        }
    }
    public function saveTaskGrades(Request $request)
    {
        try {
            $taskId = $request->task_id;
            $grades = $request->grades; // Array { student_id: score }

            foreach ($grades as $studentId => $score) {
                // Jika input tidak kosong, masukkan nilainya
                $scoreVal = $score !== '' ? $score : null;

                \Illuminate\Support\Facades\DB::table('class_task_submissions')->updateOrInsert(
                    [
                        'task_id' => $taskId,
                        'student_id' => $studentId
                    ],
                    [
                        'score' => $scoreVal,
                        'status' => $scoreVal !== null ? 'graded' : 'pending',
                        'updated_at' => now()
                    ]
                );
            }

            return response()->json(['success' => true, 'message' => 'Nilai tugas berhasil disimpan!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }
}
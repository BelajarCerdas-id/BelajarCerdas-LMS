<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\SchoolStaffProfile;
use App\Models\AcademicCalendar;
use App\Models\LessonSchedule;
use App\Models\LessonScheduleItem;
use App\Models\SchoolAssessment;
use App\Models\LmsMeetingContent;
use App\Models\LmsQuestionBank;
use App\Models\User;
use Carbon\Carbon;

class HeadmasterController extends Controller
{
    public function index($role, $schoolName, $schoolId)
    {
        $user = Auth::user();

        // Ambil profil staff
        $staffProfile = SchoolStaffProfile::where('user_id', $user->id)->first();

        if ($staffProfile) {
            // Gunakan ID Sekolah dari profil untuk keamanan ekstra
            $schoolId = $staffProfile->school_partner_id;
            
            $school = DB::table('school_partners')->where('id', $schoolId)->first();
            $schoolName = $school ? $school->nama_sekolah : 'Sekolah Mitra';

            // --- MENGHITUNG KEHADIRAN REAL-TIME ---
            $totalAbsensiHariIni = 0;
            $totalHadir = 0;

            try {
                $totalSiswaSatuSekolah = DB::table('student_profiles')
                    ->where('school_partner_id', $schoolId)
                    ->count();

                $totalHadir = DB::table('attendances')
                    ->where('school_partner_id', $schoolId)
                    ->whereDate('date', today()) 
                    ->whereIn('status', ['Hadir', 'hadir']) 
                    ->count();
                    
                $totalAbsensiHariIni = $totalSiswaSatuSekolah; 
            } catch (\Exception $e) {
                // Biarkan 0 jika tabel attendances belum dibuat
            }

            $persentaseHadir = $totalAbsensiHariIni > 0 
                ? round(($totalHadir / $totalAbsensiHariIni) * 100) 
                : 0;

            // --- STATISTIK ---
            $stats = (object) [
                'total_siswa' => DB::table('student_profiles')
                    ->where('school_partner_id', $schoolId)
                    ->count(),

                'total_guru' => SchoolStaffProfile::with('UserAccount')
                    ->where('school_partner_id', $schoolId)
                    ->get()
                    ->filter(function ($staff) {
                        return $staff->UserAccount && $staff->UserAccount->role === 'Guru';
                    })
                    ->count(),

                'total_kelas' => DB::table('school_classes')
                    ->where('school_partner_id', $schoolId)
                    ->where('status_class', 'active') // Change this
                    ->count(),

                'rata_kehadiran' => $persentaseHadir,
            ];

            // --- PENGUMUMAN REAL-TIME ---
            $pengumuman = [];
            try {
                $pengumuman = DB::table('announcements')
                    ->where('school_partner_id', $schoolId)
                    ->select('id', 'title as judul', 'created_at') 
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
            } catch (\Exception $e) {
                $pengumuman = [];
            }

            return view('features.lms.headmaster.dashboard', compact('stats', 'pengumuman', 'schoolName', 'schoolId', 'role'));            
        } else {
            abort(403, 'Profil Kepala Sekolah Anda belum terdaftar.');
        }
    }

    public function aktivitasGuru(Request $request)
    {
        $user = Auth::user();
        $staffProfile = SchoolStaffProfile::where('user_id', $user->id)->first();

        if (!$staffProfile) {
            abort(403, 'Profil Kepala Sekolah Anda belum terdaftar.');
        }

        $schoolId = $staffProfile->school_partner_id;

        // 1. Ambil daftar guru ASLI di sekolah ini untuk opsi Dropdown Filter
        $daftarGuru = SchoolStaffProfile::with('UserAccount')
            ->where('school_partner_id', $schoolId)
            ->get()
            ->filter(function ($staff) {
                return $staff->UserAccount && $staff->UserAccount->role === 'Guru';
            });

        // 2. Tangkap ID Guru dari parameter URL (jika ada)
        $filterGuruId = $request->query('guru_id');
        $guruTerpilih = null;
        $targetUserId = null;

        if ($filterGuruId) {
            $guruTerpilih = $daftarGuru->where('id', $filterGuruId)->first();
            if ($guruTerpilih) {
                $targetUserId = $guruTerpilih->user_id; // Ambil ID User si Guru
            }
        }

        // =================================================================
        // AMBIL DATA REAL DARI DATABASE MENGGUNAKAN ELOQUENT ORM
        // =================================================================
        
        $qAssessment = SchoolAssessment::with(['UserAccount.SchoolStaffProfile', 'SchoolAssessmentType', 'Mapel'])
            ->whereHas('SchoolClass', function($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            });

        $qContent = LmsMeetingContent::with(['UserAccount.SchoolStaffProfile', 'Mapel', 'LmsContent.LmsContentItem'])
            ->whereHas('SchoolClass', function($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            });

        $qQuestion = LmsQuestionBank::with(['UserAccount.SchoolStaffProfile', 'Mapel', 'Bab', 'SubBab'])
            ->where('school_partner_id', $schoolId);

        // Jika filter guru diterapkan
        if ($targetUserId) {
            $qAssessment->where('user_id', $targetUserId);
            $qContent->where('teacher_id', $targetUserId);
            $qQuestion->where('user_id', $targetUserId); 
        }

        $rawAssessments = $qAssessment->orderBy('created_at', 'desc')->get();
        $rawContents = $qContent->orderBy('created_at', 'desc')->get();
        $rawQuestions = $qQuestion->orderBy('created_at', 'desc')->get();

        // Mapping Data Assessment
        $recentAssessments = $rawAssessments->take(20)->map(function($a) {
            return (object)[
                'guru'   => $a->UserAccount->SchoolStaffProfile->nama_lengkap ?? 'Guru Tidak Diketahui',
                'status' => $a->status ?? 'Draft',
                'tipe'   => $a->SchoolAssessmentType->name ?? 'Tugas / Ujian',
                'mapel'  => $a->Mapel->mata_pelajaran ?? 'Umum',
                'waktu'  => Carbon::parse($a->created_at)->diffForHumans(),
            ];
        });

        // Mapping Data Content (Materi)
        $recentContents = $rawContents->take(20)->map(function($c) {
            $formatType = 'Teks/Modul';
            $judul = 'Materi Pembelajaran';

            if ($c->LmsContent && $c->LmsContent->LmsContentItem->count() > 0) {
                $item = $c->LmsContent->LmsContentItem->first();
                $judul = $item->original_filename ?? substr(strip_tags($item->value_text), 0, 50) ?? 'Materi Pembelajaran';
                
                if(!empty($item->value_file)) {
                    $ext = pathinfo($item->value_file, PATHINFO_EXTENSION);
                    $formatType = strtoupper($ext); 
                }
            }

            return (object)[
                'guru'   => $c->UserAccount->SchoolStaffProfile->nama_lengkap ?? 'Guru Tidak Diketahui',
                'format' => $formatType,
                'judul'  => $judul,
                'mapel'  => $c->Mapel->mata_pelajaran ?? 'Umum',
                'waktu'  => Carbon::parse($c->created_at)->diffForHumans(),
            ];
        });

        // Mapping Data Question Bank (Dikelompokkan)
        $recentQuestions = collect();
        $groupedQuestions = $rawQuestions->groupBy(function($item) {
            return $item->user_id . '-' . $item->sub_bab_id; 
        })->take(20);

        foreach ($groupedQuestions as $group) {
            $firstItem = $group->first();
            $recentQuestions->push((object)[
                'guru'        => $firstItem->UserAccount->SchoolStaffProfile->nama_lengkap ?? 'Guru Tidak Diketahui',
                'jumlah_soal' => $group->count(),
                'topik'       => $firstItem->SubBab->nama_sub_bab ?? ($firstItem->Bab->nama_bab ?? 'Topik Umum'),
                'mapel'       => $firstItem->Mapel->mata_pelajaran ?? 'Umum',
                'waktu'       => Carbon::parse($firstItem->created_at)->diffForHumans(),
            ]);
        }

        // =================================================================
        // KALKULASI STATISTIK
        // =================================================================
        
        $aktifAssessments = $rawAssessments->where('created_at', '>=', Carbon::now()->startOfMonth())->pluck('user_id');
        $aktifContents = $rawContents->where('created_at', '>=', Carbon::now()->startOfMonth())->pluck('teacher_id');
        $aktifQuestions = $rawQuestions->where('created_at', '>=', Carbon::now()->startOfMonth())->pluck('user_id');
        
        $guruAktifBulanIni = collect([])->merge($aktifAssessments)->merge($aktifContents)->merge($aktifQuestions)->unique()->count();

        $stats = (object) [
            'total_assessment' => $rawAssessments->count(),
            'total_content'    => $rawContents->count(),
            'total_question'   => $rawQuestions->count(), 
            'guru_aktif'       => $targetUserId ? 1 : $guruAktifBulanIni 
        ];

        return view('features.lms.headmaster.monitoring.aktivitas_guru', compact(
            'stats', 'recentAssessments', 'recentContents', 'recentQuestions', 
            'daftarGuru', 'filterGuruId', 'guruTerpilih'
        ));
    }

   public function laporanAkademik(\Illuminate\Http\Request $request)
    {
        $user = Auth::user();
        $staffProfile = \App\Models\SchoolStaffProfile::where('user_id', $user->id)->first();

        if (!$staffProfile) {
            abort(403, 'Profil Kepala Sekolah Anda belum terdaftar.');
        }

        $schoolId = $staffProfile->school_partner_id;

        // 1. AMBIL DAFTAR TAHUN AJARAN UNTUK FILTER
        $tahunAjaranList = DB::table('school_classes')
            ->where('school_partner_id', $schoolId)
            ->whereNotNull('tahun_ajaran')
            ->distinct()
            ->orderBy('tahun_ajaran', 'desc')
            ->pluck('tahun_ajaran');
            
        $filterTahun = $request->query('tahun_ajaran', $tahunAjaranList->first() ?? null);

        // Inisialisasi Data Chart
        $chartLabelKelas = [];
        $chartDataContent = []; // Persentase Materi Dibaca
        $chartDataAssessment = []; // Persentase Tugas Selesai

        try {
            // Ambil semua kelas di sekolah tersebut
            $kelasQuery = DB::table('school_classes')
                ->where('school_partner_id', $schoolId)
                ->where('status_class', 'active');
            
            if ($filterTahun) {
                $kelasQuery->where('tahun_ajaran', $filterTahun);
            }
            
            $kelasRecords = $kelasQuery->orderBy('class_name', 'asc')->get();

            foreach ($kelasRecords as $kelas) {
                // A. HITUNG JUMLAH SISWA AKTIF DI KELAS INI
                $totalSiswa = DB::table('student_school_classes')
                    ->where('school_class_id', $kelas->id)
                    ->where('student_class_status', 'active')
                    ->count();

                if ($totalSiswa == 0) {
                    $chartLabelKelas[] = 'Kelas ' . $kelas->class_name;
                    $chartDataContent[] = 0;
                    $chartDataAssessment[] = 0;
                    continue;
                }

                // B. ANALISIS AKTIVITAS KONTEN (MATERI LMS)
                // Total materi yang dipublish untuk kelas ini
                $totalMateri = DB::table('lms_meeting_contents')
                    ->where('school_class_id', $kelas->id)
                    ->where('is_active', 1)
                    ->count();

                $ekspektasiBaca = $totalSiswa * $totalMateri;
                
                // Aktual materi yang dibaca (asumsi ada tabel lms_content_reads)
                // Jika kamu belum punya tabel log baca, kita gunakan dummy logic atau hitung manual
                $aktualBaca = DB::table('lms_content_reads') // Pastikan nama tabel log baca materi ini sesuai
                    ->join('lms_meeting_contents', 'lms_content_reads.content_id', '=', 'lms_meeting_contents.id')
                    ->where('lms_meeting_contents.school_class_id', $kelas->id)
                    ->count();

                // C. ANALISIS AKTIVITAS ASESMEN (TUGAS/UJIAN)
                $totalAsesmen = DB::table('school_assessments')
                    ->where('school_class_id', $kelas->id)
                    ->count();

                $ekspektasiSelesai = $totalSiswa * $totalAsesmen;

                // Aktual tugas yang dikumpulkan (submission)
                $aktualSelesai = DB::table('class_task_submissions')
                    ->whereIn('task_id', function($query) use ($kelas) {
                        $query->select('id')->from('school_assessments')->where('school_class_id', $kelas->id);
                    })
                    ->count();

                // D. KALKULASI PERSENTASE
                $persenContent = $ekspektasiBaca > 0 ? round(($aktualBaca / $ekspektasiBaca) * 100) : 0;
                $persenAssessment = $ekspektasiSelesai > 0 ? round(($aktualSelesai / $ekspektasiSelesai) * 100) : 0;

                $chartLabelKelas[] = 'Kelas ' . $kelas->class_name;
                $chartDataContent[] = $persenContent;
                $chartDataAssessment[] = $persenAssessment;
            }

            // HITUNG KPI RINGKASAN UNTUK KOTAK ATAS
            $stats = (object) [
                'total_materi' => DB::table('lms_meeting_contents')->whereIn('school_class_id', $kelasRecords->pluck('id'))->count(),
                'total_tugas'  => DB::table('school_assessments')->whereIn('school_class_id', $kelasRecords->pluck('id'))->count(),
                'avg_keaktifan' => count($chartDataContent) > 0 ? round(array_sum($chartDataContent) / count($chartDataContent)) : 0,
                'siswa_pasif'   => 0 // Bisa dihitung berdasarkan siswa yang 0 submission
            ];

        } catch (\Exception $e) {
            $stats = (object) ['total_materi' => 0, 'total_tugas' => 0, 'avg_keaktifan' => 0, 'siswa_pasif' => 0];
        }

        return view('features.lms.headmaster.monitoring.laporan_akademik', compact(
            'stats', 'tahunAjaranList', 'filterTahun', 
            'chartLabelKelas', 'chartDataContent', 'chartDataAssessment'
        ));
    }

    public function CalendarView($role, $schoolName, $schoolId)
    {
        $eventsFromDb = AcademicCalendar::where('school_partner_id', $schoolId)->get();
        
        $savedEvents = [];
        foreach($eventsFromDb as $ev) {
            $savedEvents[] = [
                'date'   => date('Y-m-d', strtotime($ev->date)), 
                'title'  => $ev->title,
                'type'   => $ev->type,
                'color'  => $ev->color,
                'status' => $ev->status
            ];
        }

        return view('features.lms.headmaster.information.calender', compact('role', 'schoolName', 'schoolId', 'savedEvents'));
    }

    public function saveCalendarData(Request $request, $role, $schoolName, $schoolId)
    {
        try {
            $status = $request->status; 
            $events = $request->events;

            AcademicCalendar::where('school_partner_id', $schoolId)->delete();

            if (!empty($events)) {
                $insertData = [];
                foreach ($events as $event) {
                    $insertData[] = [
                        'school_partner_id' => $schoolId,
                        'date'              => $event['date'],
                        'title'             => $event['title'],
                        'type'              => $event['type'] ?? 'school_event',
                        'color'             => $event['color'] ?? '#F59E0B',
                        'status'            => $status,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ];
                }
                AcademicCalendar::insert($insertData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Kalender berhasil disimpan permanen ke database!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'GAGAL DATABASE: ' . $e->getMessage()
            ], 500);
        }
    }

    // =========================================================
    // MENU PENYUSUNAN JADWAL
    // =========================================================
    public function scheduleView($role, $schoolName, $schoolId)
    {
        $timeSlots = [
            ['start' => '07:00', 'end' => '07:45', 'is_break' => false],
            ['start' => '07:45', 'end' => '08:30', 'is_break' => false],
            ['start' => '08:30', 'end' => '09:15', 'is_break' => false],
            ['start' => '09:15', 'end' => '10:00', 'is_break' => false],
            ['start' => '10:00', 'end' => '10:45', 'is_break' => true],
            ['start' => '10:45', 'end' => '11:30', 'is_break' => false],
            ['start' => '11:30', 'end' => '12:15', 'is_break' => false],
            ['start' => '12:15', 'end' => '13:00', 'is_break' => true],
            ['start' => '13:00', 'end' => '13:45', 'is_break' => false],
            ['start' => '13:45', 'end' => '14:30', 'is_break' => false],
            ['start' => '14:30', 'end' => '15:15', 'is_break' => false],
        ];

        $classes = DB::table('school_classes')
            ->where('school_partner_id', $schoolId)
            ->where('status_class', 'active')
            ->select('id', 'class_name', 'kelas_id') 
            ->orderBy('kelas_id', 'asc')
            ->orderBy('class_name', 'asc')
            ->get();

        // MENGAMBIL SELURUH JADWAL DARI SEMUA KELAS DI SEKOLAH INI
        // UNTUK VALIDASI DRAG & DROP GURU BENTROK DI JAVASCRIPT
        $allSchedules = DB::table('lesson_schedule_items')
            ->join('lesson_schedules', 'lesson_schedule_items.lesson_schedule_id', '=', 'lesson_schedules.id')
            ->where('lesson_schedules.school_partner_id', $schoolId)
            ->select(
                'lesson_schedules.class_id',
                'lesson_schedule_items.day_of_week',
                'lesson_schedule_items.start_time',
                'lesson_schedule_items.mapel_id as subject_id', 
                'lesson_schedule_items.teacher_id'
            )
            ->get();

        return view('features.lms.headmaster.information.schedule', compact(
            'role', 'schoolName', 'schoolId', 'timeSlots', 'classes', 'allSchedules'
        ));
    }

    public function getScheduleDataAjax($schoolId, $classId)
    {
        try {
            $classInfo = DB::table('school_classes')
                ->where('id', $classId)
                ->where('school_partner_id', $schoolId)
                ->first();

            if (!$classInfo) {
                return response()->json(['success' => false, 'message' => 'Kelas tidak ditemukan.']);
            }

            $teachersData = DB::table('teacher_mapels')
                ->join('school_staff_profiles', 'teacher_mapels.user_id', '=', 'school_staff_profiles.user_id')
                ->join('mapels', 'teacher_mapels.mapel_id', '=', 'mapels.id')
                ->join('school_classes', 'teacher_mapels.school_class_id', '=', 'school_classes.id')
                ->where('school_staff_profiles.school_partner_id', $schoolId)
                ->where('school_classes.kelas_id', $classInfo->kelas_id)
                ->where(function($query) use ($classInfo) {
                    if ($classInfo->major_id) {
                        $query->where('school_classes.major_id', $classInfo->major_id)
                              ->orWhereNull('school_classes.major_id');
                    } else {
                        $query->whereNull('school_classes.major_id');
                    }
                })
                ->select(
                    'teacher_mapels.user_id', 
                    'teacher_mapels.mapel_id',
                    'school_staff_profiles.nama_lengkap',
                    'mapels.mata_pelajaran'
                )
                ->distinct() 
                ->get();

            $available_mapels = [];
            $colors = ['#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EF4444', '#06B6D4', '#EAB308'];
            
            foreach ($teachersData as $index => $t) {
                $available_mapels[] = [
                    'id'         => $t->user_id,
                    'name'       => $t->nama_lengkap ?? "Guru " . $t->user_id,
                    'subject_id' => $t->mapel_id,
                    'subject'    => $t->mata_pelajaran, 
                    'color'      => $colors[$index % count($colors)]
                ];
            }

            $parent = LessonSchedule::where('school_partner_id', $schoolId)
                ->where('class_id', $classId)
                ->first();
                
            $formattedSchedules = [];
            if ($parent) {
                $items = LessonScheduleItem::where('lesson_schedule_id', $parent->id)->get();
                foreach ($items as $item) {
                    $formattedSchedules[] = [
                        'day_of_week'  => $item->day_of_week,
                        'start_time'   => substr($item->start_time, 0, 5), 
                        'teacher_id'   => $item->teacher_id,
                        'teacher_name' => $item->teacher_name,
                        'subject_id'   => $item->mapel_id,
                        'subject_name' => $item->subject_name,
                        'color'        => $item->color
                    ];
                }
            }

            return response()->json([
                'success'          => true,
                'available_mapels' => $available_mapels,
                'data'             => $formattedSchedules
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function saveSchedule(Request $request, $role, $schoolName, $schoolId)
    {
        $classId = $request->class_id;
        $className = $request->class_name;
        $status = $request->status ?? 'draft';
        $schedules = $request->schedules;

        if (!$classId) {
            return response()->json(['success' => false, 'message' => 'ID Kelas tidak valid.']);
        }

        DB::beginTransaction();
        try {
            // 1. Cek Bentrok
            if (!empty($schedules)) {
                foreach ($schedules as $s) {
                    $clash = DB::table('lesson_schedule_items')
                        ->join('lesson_schedules', 'lesson_schedule_items.lesson_schedule_id', '=', 'lesson_schedules.id')
                        ->where('lesson_schedules.school_partner_id', $schoolId)
                        ->where('lesson_schedules.class_id', '!=', $classId)
                        ->where('lesson_schedule_items.day_of_week', $s['day'])
                        ->where('lesson_schedule_items.start_time', $s['start_time'])
                        ->where('lesson_schedule_items.teacher_id', $s['teacher_id'])
                        ->select('lesson_schedules.class_name')
                        ->first();

                    if ($clash) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false, 
                            'message' => "BENTROK JADWAL: {$s['teacher_name']} sudah mengajar di kelas {$clash->class_name} pada hari {$s['day']} jam {$s['start_time']}."
                        ]);
                    }
                }
            }

            // 2. Hapus Data Lama
            $existingParent = LessonSchedule::where('school_partner_id', $schoolId)
                ->where('class_id', $classId)
                ->first();

            if ($existingParent) {
                LessonScheduleItem::where('lesson_schedule_id', $existingParent->id)->delete();
                $existingParent->delete();
            }

            $parentData = [
                'school_partner_id' => $schoolId,
                'class_id'          => $classId,
                'class_name'        => $className,
                'status'            => $status,
                'created_at'        => now(),
                'updated_at'        => now(),
            ];

            $legacyColumns = [
                'day_of_week'  => '-',
                'start_time'   => '00:00',
                'end_time'     => '00:00',
                'teacher_id'   => '0',
                'teacher_name' => '-',
                'subject_name' => '-',
                'mapel_id'     => '0',
                'color'        => '-',
            ];

            foreach ($legacyColumns as $col => $dummyValue) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('lesson_schedules', $col)) {
                    $parentData[$col] = $dummyValue;
                }
            }

            $newParentId = DB::table('lesson_schedules')->insertGetId($parentData);

            // 4. Simpan Detail Jadwal (Children)
            if (!empty($schedules)) {
                $items = [];
                $now = now();
                
                foreach ($schedules as $s) {
                    $endTime = date('H:i', strtotime('+45 minutes', strtotime($s['start_time'])));
                    $items[] = [
                        'lesson_schedule_id' => $newParentId, 
                        'teacher_id'         => $s['teacher_id'],
                        'mapel_id'           => $s['subject_id'],
                        'teacher_name'       => $s['teacher_name'],
                        'subject_name'       => $s['subject_name'],
                        'day_of_week'        => $s['day'],
                        'start_time'         => $s['start_time'],
                        'end_time'           => $endTime,
                        'color'              => $s['color'] ?? '#0071BC',
                        'created_at'         => $now,
                        'updated_at'         => $now,
                    ];
                }
                LessonScheduleItem::insert($items);
            }

            DB::commit();
            
            $msgStatus = $status === 'published' ? 'dipublikasikan' : 'disimpan sebagai draft';
            return response()->json([
                'success' => true, 
                'message' => "Jadwal kelas {$className} berhasil {$msgStatus}!"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false, 
                'message' => 'Gagal menyimpan: ' . $e->getMessage()
            ], 500);
        }
    }

   // =================================================================
    // MANAJEMEN POLLING (KEPALA SEKOLAH)
    // =================================================================
    
    public function pollingIndex(Request $request)
    {
        $user = Auth::user();
        $staffProfile = \App\Models\SchoolStaffProfile::where('user_id', $user->id)->first();

        if (!$staffProfile) {
            abort(403, 'Profil tidak ditemukan.');
        }

        $schoolId = $staffProfile->school_partner_id;

        // 1. TANGKAP QUERY FILTER DARI URL
        $filterPembuat = $request->query('pembuat');
        $filterTarget = $request->query('target');

        // Ambil semua kelas untuk pilihan dropdown saat buat polling
        $daftarKelas = DB::table('school_classes')
            ->where('school_partner_id', $schoolId)
            ->where('status_class', 'active')
            ->select('id', 'class_name')
            ->orderBy('class_name', 'asc')
            ->get();

        // 2. BUAT QUERY DASAR
        $pollsQuery = \App\Models\Poll::with('PollOptions')
            ->where('school_partner_id', $schoolId);

        // 3. TERAPKAN FILTER JIKA ADA
        if (!empty($filterPembuat)) {
            $pollsQuery->where('author_role', $filterPembuat);
        }

        if (!empty($filterTarget)) {
            $pollsQuery->where('target', $filterTarget); 
        }

        // 4. EKSEKUSI QUERY
        $polls = $pollsQuery->orderBy('created_at', 'desc')
            ->get()
            ->map(function($poll) {
                // Relasi ke poll_votes tetap aman karena kita menghitung berdasarkan poll_id
                $totalVotes = DB::table('poll_votes')->where('poll_id', $poll->id)->count();
                $poll->total_votes = $totalVotes;

                foreach ($poll->PollOptions as $opt) {
                    $opt->percentage = $totalVotes > 0 ? round(($opt->votes_count / $totalVotes) * 100) : 0;
                }
                
                // Manfaatkan kolom class_name yang baru dibuat di tabel polls agar lebih cepat
                if ($poll->class_id) {
                    $poll->nama_kelas = $poll->class_name ?? 'Kelas Spesifik';
                } else {
                    $poll->nama_kelas = 'Semua Kelas (Global)';
                }

                $poll->author_role = $poll->author_role ?? 'Sistem';

                return $poll;
            });

        return view('features.lms.headmaster.information.polling', compact('polls', 'daftarKelas'));
    }

    public function pollingStore(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'target'   => 'required|in:Semua Warga Sekolah,Semua Guru,Semua Siswa,Semua Orang Tua',
            'options'  => 'required|array|min:2', 
            'options.*'=> 'required|string|max:100',
        ]);

        $user = Auth::user();
        $staffProfile = \App\Models\SchoolStaffProfile::where('user_id', $user->id)->first();

        DB::beginTransaction();
        try {
            // 👇 PEMBERSIHAN CLASS_ID: Mencegah Error Integrity constraint violation 19
           $kelasId = $request->class_id;
            if (empty($kelasId) || $kelasId === '0' || $kelasId === 'null') {
                $kelasId = null;
            }

            // 👇 AMBIL NAMA KELAS UNTUK DISIMPAN DI KOLOM BARU class_name
            $kelasName = null;
            if ($kelasId) {
                $kelasInfo = DB::table('school_classes')->where('id', $kelasId)->first();
                $kelasName = $kelasInfo ? $kelasInfo->class_name : null;
            }

            // 1. Data dasar sesuai dengan migration yang baru
            $pollData = [
                'school_partner_id' => $staffProfile->school_partner_id,
                'class_id'          => $kelasId,
                'target'            => $request->target,
                'question'          => $request->question,
                'author_id'         => $user->id,
                'author_role'       => $user->role,
                'status'            => 'active',
                'created_at'        => now(),
                'updated_at'        => now(),
            ];

            // 2. Masukkan ID Author & Teacher sesuai format tabel baru
            if (\Illuminate\Support\Facades\Schema::hasColumn('polls', 'author_id')) {
                $pollData['author_id'] = $user->id;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('polls', 'teacher_id')) {
                $pollData['teacher_id'] = $user->id;
            }

            // 3. Eksekusi penyimpanan ke database
            $pollId = DB::table('polls')->insertGetId($pollData);

            $optionsData = [];
            foreach ($request->options as $optText) {
                $optionsData[] = [
                    'poll_id'      => $pollId,
                    'option_text'  => $optText,
                    'votes_count'  => 0,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];
            }
            DB::table('poll_options')->insert($optionsData);

            DB::commit();
            return back()->with('success', 'Polling baru berhasil dipublikasikan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat polling: ' . $e->getMessage());
        }
    }

    public function pollingDestroy($id)
    {
        DB::beginTransaction();
        try {
            // Karena migration baru sudah pakai cascadeOnDelete(),
            // Sebenarnya menghapus tabel anaknya (votes dan options) tidak wajib lagi.
            // Namun, menuliskannya di sini tetap AMAN untuk berjaga-jaga (backward compatibility).
            DB::table('poll_votes')->where('poll_id', $id)->delete();
            DB::table('poll_options')->where('poll_id', $id)->delete();
            
            // Hapus polling utama
            DB::table('polls')->where('id', $id)->delete();

            DB::commit();
            return back()->with('success', 'Polling berhasil dihapus sepenuhnya.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus polling.');
        }
    }
    
    /**
     * Mendapatkan detail polling beserta breakdown responden berdasarkan target
     */
    public function getPollingBreakdown($id)
    {
        try {
            $options = \App\Models\PollOption::where('poll_id', $id)->get();
            $votes = \Illuminate\Support\Facades\DB::table('poll_votes')
                ->join('user_accounts', 'poll_votes.user_id', '=', 'user_accounts.id')
                ->where('poll_votes.poll_id', $id)
                ->select('poll_votes.poll_option_id', 'user_accounts.role')
                ->get();

            $labels = [];
            $dataSiswa = [];
            $dataOrtu = [];
            $dataGuru = [];

            foreach ($options as $opt) {
                $labels[] = $opt->option_text;
                
                // Hitung berdasarkan Role
                $dataSiswa[] = $votes->where('poll_option_id', $opt->id)->where('role', 'Siswa')->count();
                $dataOrtu[] = $votes->where('poll_option_id', $opt->id)->where('role', 'Orang Tua')->count();
                // Gabung semua jenis Guru & Manajemen
                $dataGuru[] = $votes->where('poll_option_id', $opt->id)->whereIn('role', ['Guru', 'Kepala Sekolah', 'Wakil Kepala Sekolah', 'Admin'])->count();
            }

            return response()->json([
                'success' => true,
                'labels' => $labels,
                'datasets' => [
                    'Siswa' => $dataSiswa,
                    'Orang Tua' => $dataOrtu,
                    'Guru/Manajemen' => $dataGuru
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
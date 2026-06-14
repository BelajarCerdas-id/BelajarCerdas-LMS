<?php

namespace App\Http\Controllers;

use App\Models\AcademicCalendar;
use App\Models\LessonSchedule;
use App\Models\LessonScheduleItem;
use App\Models\LmsContentRead;
use App\Models\LmsMeetingContent;
use App\Models\LmsQuestionBank;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\SchoolAssessment;
use App\Models\SchoolClass;
use App\Models\SchoolStaffProfile;
use App\Models\StudentAssessmentAnswer;
use App\Models\StudentProfile;
use App\Models\StudentProjectSubmission;
use App\Models\StudentSchoolClass;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

            // --- PENGUMUMAN REAL-TIME (HANYA BUATAN KEPSEK/WAKASEK UNTUK GURU) ---
            $pengumuman = [];
            try {
                $pengumuman = DB::table('announcements')
                    ->where('school_partner_id', $schoolId)
                    // 👇 Ganti filter role menjadi filter ID agar spesifik milik user yang login
                    ->where('author_id', $user->id)
                    ->select('id', 'title as judul', 'created_at')
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
            } catch (\Exception $e) {
                $pengumuman = [];
            }

            $pengumumanYayasan = DB::table('announcements')
                ->leftJoin('announcement_views', function ($join) use ($user) {
                    $join->on('announcement_views.announcement_id', '=', 'announcements.id')
                        ->where('announcement_views.user_id', '=', $user->id);
                })
                ->where('announcements.school_partner_id', $schoolId)
                ->where('announcements.author_role', 'Yayasan')
                ->where('announcements.target', 'Kepala Sekolah')
                ->select(
                    'announcements.id',
                    'announcements.title',
                    'announcements.type',
                    'announcements.content',
                    'announcements.created_at',
                    'announcement_views.created_at as read_at'
                )
                ->orderByDesc('announcements.created_at')
                ->limit(8)
                ->get();

            return view('features.lms.headmaster.dashboard', compact('stats', 'pengumuman', 'pengumumanYayasan', 'schoolName', 'schoolId', 'role'));
        } else {
            abort(403, 'Profil Kepala Sekolah Anda belum terdaftar.');
        }
    }

    public function markYayasanAnnouncementAsRead(Request $request)
    {
        $user = Auth::user();
        $staffProfile = SchoolStaffProfile::where('user_id', $user->id)->first();

        if (! $staffProfile || ! in_array($user->role, ['Kepala Sekolah', 'Wakil Kepala Sekolah'])) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $announcementId = $request->input('announcement_id');
        $announcement = DB::table('announcements')
            ->where('id', $announcementId)
            ->where('school_partner_id', $staffProfile->school_partner_id)
            ->where('author_role', 'Yayasan')
            ->where('target', 'Kepala Sekolah')
            ->first();

        if (! $announcement) {
            return response()->json(['success' => false, 'message' => 'Pengumuman tidak ditemukan.'], 404);
        }

        DB::table('announcement_views')->updateOrInsert(
            ['announcement_id' => $announcement->id, 'user_id' => $user->id],
            ['created_at' => now(), 'updated_at' => now()]
        );

        return response()->json(['success' => true]);
    }

    public function aktivitasGuru(Request $request)
    {
        $user = Auth::user();
        $staffProfile = SchoolStaffProfile::where('user_id', $user->id)->first();

        if (! $staffProfile) {
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
            ->whereHas('SchoolClass', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            });

        $qContent = LmsMeetingContent::with(['UserAccount.SchoolStaffProfile', 'Mapel', 'LmsContent.LmsContentItem'])
            ->whereHas('SchoolClass', function ($q) use ($schoolId) {
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

        // =================================================================
        // KALKULASI RINCIAN JENIS (UNTUK POP-UP)
        // =================================================================
        $breakdownAss = [];
        foreach ($rawAssessments as $a) {
            $tipe = $a->SchoolAssessmentType->name ?? 'Lainnya';
            $breakdownAss[$tipe] = ($breakdownAss[$tipe] ?? 0) + 1;
        }

        $breakdownCont = [];
        foreach ($rawContents as $c) {
            $format = 'Teks/Modul';
            if ($c->LmsContent && $c->LmsContent->LmsContentItem->count() > 0) {
                $file = $c->LmsContent->LmsContentItem->first()->value_file;
                if ($file) {
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    if (in_array($ext, ['mp4', 'mkv', 'youtube'])) {
                        $format = 'Video';
                    } elseif (in_array($ext, ['doc', 'docx', 'pdf', 'ppt', 'pptx'])) {
                        $format = 'Dokumen/PDF';
                    } else {
                        $format = strtoupper($ext);
                    }
                }
            }
            $breakdownCont[$format] = ($breakdownCont[$format] ?? 0) + 1;
        }

        // Soal Dihitung Per-Bank (Grup Upload), BUKAN per butir soal
        $groupedQuestions = $rawQuestions->groupBy(function ($item) {
            return $item->user_id.'-'.$item->sub_bab_id;
        });
        $totalQuestionBanks = $groupedQuestions->count();

        // =================================================================
        // KALKULASI TOTAL JATAH / TARGET GLOBAL
        // =================================================================
        if ($targetUserId) {
            // Target untuk 1 Guru
            $totalMapelCount = DB::table('lesson_schedule_items')
                ->join('lesson_schedules', 'lesson_schedule_items.lesson_schedule_id', '=', 'lesson_schedules.id')
                ->where('lesson_schedules.school_partner_id', $schoolId)
                ->where('lesson_schedule_items.teacher_id', $targetUserId)
                ->distinct('lesson_schedule_items.mapel_id')
                ->count('lesson_schedule_items.mapel_id');
        } else {
            // Target Global Seluruh Sekolah (Total kombinasi Guru & Mapel)
            $totalMapelCount = DB::table('lesson_schedule_items')
                ->join('lesson_schedules', 'lesson_schedule_items.lesson_schedule_id', '=', 'lesson_schedules.id')
                ->where('lesson_schedules.school_partner_id', $schoolId)
                ->select('lesson_schedule_items.teacher_id', 'lesson_schedule_items.mapel_id')
                ->distinct()
                ->get()
                ->count();
        }

        $aktifAssessments = $rawAssessments->where('created_at', '>=', Carbon::now()->startOfMonth())->pluck('user_id');
        $aktifContents = $rawContents->where('created_at', '>=', Carbon::now()->startOfMonth())->pluck('teacher_id');
        $aktifQuestions = $rawQuestions->where('created_at', '>=', Carbon::now()->startOfMonth())->pluck('user_id');
        $guruAktifBulanIni = collect([])->merge($aktifAssessments)->merge($aktifContents)->merge($aktifQuestions)->unique()->count();

        $stats = (object) [
            // Pencapaian vs Target
            'total_assessment' => $rawAssessments->count(),
            'target_assessment' => $totalMapelCount * 6, // 6 Tugas/Ujian per Mapel
            'breakdown_assessment' => $breakdownAss,

            'total_content' => $rawContents->count(),
            'target_content' => $totalMapelCount * 12, // 12 Materi per Mapel
            'breakdown_content' => $breakdownCont,

            'total_question_banks' => $totalQuestionBanks, // Hitung per file/bank
            'target_question' => $totalMapelCount * 4,  // 4 Bank soal per Mapel

            'guru_aktif' => $targetUserId ? 1 : $guruAktifBulanIni,
        ];

        // =================================================================
        // MAPPING DATA UNTUK DAFTAR TERKINI (RECENT)
        // =================================================================

        // Mapping Data Assessment
        $recentAssessments = $rawAssessments->take(20)->map(function ($a) {
            return (object) [
                'guru' => $a->UserAccount->SchoolStaffProfile->nama_lengkap ?? 'Guru Tidak Diketahui',
                'status' => $a->status ?? 'Draft',
                'tipe' => $a->SchoolAssessmentType->name ?? 'Tugas / Ujian',
                'mapel' => $a->Mapel->mata_pelajaran ?? 'Umum',
                'waktu' => Carbon::parse($a->created_at)->diffForHumans(),
            ];
        });

        // Mapping Data Content (Materi)
        $recentContents = $rawContents->take(20)->map(function ($c) {
            $formatType = 'Teks/Modul';
            $judul = 'Materi Pembelajaran';

            if ($c->LmsContent && $c->LmsContent->LmsContentItem->count() > 0) {
                $item = $c->LmsContent->LmsContentItem->first();
                $judul = $item->original_filename ?? substr(strip_tags($item->value_text), 0, 50) ?? 'Materi Pembelajaran';

                if (! empty($item->value_file)) {
                    $ext = strtolower(pathinfo($item->value_file, PATHINFO_EXTENSION));
                    if (in_array($ext, ['mp4', 'mkv', 'youtube'])) {
                        $formatType = 'Video';
                    } elseif (in_array($ext, ['doc', 'docx', 'pdf', 'ppt', 'pptx'])) {
                        $formatType = 'Dokumen/PDF';
                    } else {
                        $formatType = strtoupper($ext);
                    }
                }
            }

            return (object) [
                'guru' => $c->UserAccount->SchoolStaffProfile->nama_lengkap ?? 'Guru Tidak Diketahui',
                'format' => $formatType,
                'judul' => $judul,
                'mapel' => $c->Mapel->mata_pelajaran ?? 'Umum',
                'waktu' => Carbon::parse($c->created_at)->diffForHumans(),
            ];
        });

        // Mapping Data Question Bank
        $recentQuestions = collect();
        $groupedQuestionsToMap = $rawQuestions->groupBy(function ($item) {
            return $item->user_id.'-'.$item->sub_bab_id;
        })->take(20);

        foreach ($groupedQuestionsToMap as $group) {
            $firstItem = $group->first();
            $recentQuestions->push((object) [
                'guru' => $firstItem->UserAccount->SchoolStaffProfile->nama_lengkap ?? 'Guru Tidak Diketahui',
                'jumlah_soal' => $group->count(),
                'topik' => $firstItem->SubBab->nama_sub_bab ?? ($firstItem->Bab->nama_bab ?? 'Topik Umum'),
                'mapel' => $firstItem->Mapel->mata_pelajaran ?? 'Umum',
                'waktu' => Carbon::parse($firstItem->created_at)->diffForHumans(),
            ]);
        }

        // =================================================================
        // MENGHITUNG TARGET JATAH UPLOAD PER MAPEL (JIKA ADA GURU YANG DIPILIH)
        // =================================================================
        $targetUploadGuru = [];

        if ($targetUserId) {
            $TARGET_MATERI_PER_MAPEL = 12;
            $TARGET_ASSESSMENT_PER_MAPEL = 6;

            $mapelDiajar = DB::table('lesson_schedule_items')
                ->join('lesson_schedules', 'lesson_schedule_items.lesson_schedule_id', '=', 'lesson_schedules.id')
                ->where('lesson_schedules.school_partner_id', $schoolId)
                ->where('lesson_schedule_items.teacher_id', $targetUserId)
                ->select('lesson_schedule_items.mapel_id', 'lesson_schedule_items.subject_name')
                ->distinct('lesson_schedule_items.mapel_id')
                ->get();

            foreach ($mapelDiajar as $mapel) {

                // --- PROSES MENGHITUNG MATERI (CONTENT) ---
                $contents = LmsMeetingContent::with('LmsContent.LmsContentItem')
                    ->where('teacher_id', $targetUserId)
                    ->where('subject_id', $mapel->mapel_id)
                    ->get();

                $tercapaiMateri = $contents->count();
                $detailMateri = [];

                foreach ($contents as $c) {
                    $format = 'Teks/Modul';
                    if ($c->LmsContent && $c->LmsContent->LmsContentItem->count() > 0) {
                        $file = $c->LmsContent->LmsContentItem->first()->value_file;
                        if ($file) {
                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            if (in_array($ext, ['mp4', 'mkv', 'youtube'])) {
                                $format = 'Video';
                            } elseif (in_array($ext, ['doc', 'docx', 'pdf', 'ppt', 'pptx'])) {
                                $format = 'Dokumen/PDF';
                            } else {
                                $format = strtoupper($ext);
                            }
                        }
                    }
                    if (! isset($detailMateri[$format])) {
                        $detailMateri[$format] = 0;
                    }
                    $detailMateri[$format]++;
                }

                // --- PROSES MENGHITUNG ASSESSMENT ---
                $assessments = SchoolAssessment::with('SchoolAssessmentType')
                    ->where('user_id', $targetUserId)
                    ->where('subject_id', $mapel->mapel_id)
                    ->get();

                $tercapaiAss = $assessments->count();
                $detailAss = [];

                foreach ($assessments as $a) {
                    $tipe = $a->SchoolAssessmentType->name ?? 'Lainnya';
                    if (! isset($detailAss[$tipe])) {
                        $detailAss[$tipe] = 0;
                    }
                    $detailAss[$tipe]++;
                }

                $targetUploadGuru[] = (object) [
                    'mapel' => $mapel->subject_name,
                    'content' => (object) [
                        'target' => $TARGET_MATERI_PER_MAPEL,
                        'tercapai' => $tercapaiMateri,
                        'detail' => $detailMateri,
                    ],
                    'assessment' => (object) [
                        'target' => $TARGET_ASSESSMENT_PER_MAPEL,
                        'tercapai' => $tercapaiAss,
                        'detail' => $detailAss,
                    ],
                ];
            }
        }

        return view('features.lms.headmaster.monitoring.aktivitas_guru', compact(
            'stats', 'recentAssessments', 'recentContents', 'recentQuestions',
            'daftarGuru', 'filterGuruId', 'guruTerpilih', 'targetUploadGuru'
        ));
    }

    public function laporanAkademik(Request $request)
    {
        $user = Auth::user();
        $staffProfile = SchoolStaffProfile::where('user_id', $user->id)->first();

        if (! $staffProfile) {
            abort(403, 'Profil Kepala/Wakil Sekolah tidak terdaftar.');
        }

        $schoolId = $staffProfile->school_partner_id;

        // 1. FILTER TAHUN AJARAN
        $tahunAjaranList = SchoolClass::where('school_partner_id', $schoolId)
            ->whereNotNull('tahun_ajaran')
            ->distinct()
            ->orderBy('tahun_ajaran', 'desc')
            ->pluck('tahun_ajaran');

        $defaultTahun = $tahunAjaranList->last(); // Ambil yang terbaru sebagai default
        $filterTahun = $request->query('tahun_ajaran', $defaultTahun);

        // 2. INISIALISASI
        $chartLabelKelas = [];
        $chartDataContent = [];
        $chartDataAssessment = [];
        $listSiswaPasif = collect(); // Koleksi untuk menyimpan detail nama & kelas

        $totalMateriGlobal = 0;
        $totalTugasGlobal = 0;
        $totalSiswaPasif = 0;

        try {
            $kelasQuery = SchoolClass::where('school_partner_id', $schoolId)
                ->where('status_class', 'active');

            if ($filterTahun) {
                $kelasQuery->where('tahun_ajaran', $filterTahun);
            }

            $daftarKelas = $kelasQuery->orderBy('kelas_id', 'asc')->orderBy('class_name', 'asc')->get();

            foreach ($daftarKelas as $kelas) {
                // A. Ambil Siswa Aktif di Kelas
                $siswaIds = StudentSchoolClass::where('school_class_id', $kelas->id)
                    ->where('student_class_status', 'active')
                    ->where(function ($q) {
                        $q->whereNull('academic_action')->orWhere('academic_action', '');
                    })
                    ->pluck('student_id');

                $jumlahSiswa = $siswaIds->count();

                if ($jumlahSiswa == 0) {
                    $chartLabelKelas[] = $kelas->class_name;
                    $chartDataContent[] = 0;
                    $chartDataAssessment[] = 0;

                    continue;
                }

                // B. LOGIKA ASESMEN
                $assessments = SchoolAssessment::where('school_class_id', $kelas->id)->get();
                $jumlahTugas = $assessments->count();
                $totalTugasGlobal += $jumlahTugas;

                $targetTugas = $jumlahSiswa * $jumlahTugas;
                $aktualTugas = 0;
                $siswaAktifTugasIds = collect();

                foreach ($assessments as $assessment) {
                    $examStudents = StudentAssessmentAnswer::where('school_assessment_id', $assessment->id)
                        ->where('status_answer', 'submitted')
                        ->distinct()->pluck('student_id');

                    $projectStudents = StudentProjectSubmission::where('school_assessment_id', $assessment->id)
                        ->distinct()->pluck('student_id');

                    $submissionStudents = $examStudents->merge($projectStudents)->unique();
                    $aktualTugas += $submissionStudents->count();
                    $siswaAktifTugasIds = $siswaAktifTugasIds->merge($submissionStudents)->unique();
                }

                $persenTugas = $targetTugas > 0 ? round(($aktualTugas / $targetTugas) * 100) : 0;

                // C. LOGIKA MATERI
                $materiIds = LmsMeetingContent::where('school_class_id', $kelas->id)->where('is_active', 1)->pluck('id');
                $totalMateriGlobal += $materiIds->count();
                $targetBaca = $jumlahSiswa * $materiIds->count();
                $aktualBaca = 0;

                if ($materiIds->isNotEmpty() && class_exists('\App\Models\LmsContentRead')) {
                    $aktualBaca = LmsContentRead::whereIn('content_id', $materiIds)->whereIn('student_id', $siswaIds)->count();
                }
                $persenMateri = $targetBaca > 0 ? round(($aktualBaca / $targetBaca) * 100) : 0;

                // D. LOGIKA SISWA PASIF (FIXED: Tidak Double Hitung)
                $pasifIds = $siswaIds->diff($siswaAktifTugasIds);
                $totalSiswaPasif += $pasifIds->count(); // Tambah sekali saja di sini

                if ($pasifIds->isNotEmpty()) {
                    $detailPasif = StudentProfile::whereIn('user_id', $pasifIds)
                        ->select('nama_lengkap')
                        ->get()
                        ->map(function ($s) use ($kelas) {
                            return [
                                'nama' => $s->nama_lengkap,
                                'kelas' => $kelas->class_name,
                            ];
                        });
                    $listSiswaPasif = $listSiswaPasif->concat($detailPasif);
                }

                // Data untuk Chart
                $chartLabelKelas[] = $kelas->class_name;
                $chartDataContent[] = min($persenMateri, 100);
                $chartDataAssessment[] = min($persenTugas, 100);
            }

            // Hitung Avg Keaktifan Global
            $jumlahData = count($chartDataContent) * 2;
            $avgKeaktifan = $jumlahData > 0 ? round((array_sum($chartDataContent) + array_sum($chartDataAssessment)) / $jumlahData) : 0;

            $stats = (object) [
                'total_materi' => $totalMateriGlobal,
                'total_tugas' => $totalTugasGlobal,
                'avg_keaktifan' => $avgKeaktifan,
                'siswa_pasif' => $totalSiswaPasif,
            ];

        } catch (\Exception $e) {
            $stats = (object) ['total_materi' => 0, 'total_tugas' => 0, 'avg_keaktifan' => 0, 'siswa_pasif' => 0];
        }

        return view('features.lms.headmaster.monitoring.laporan_akademik', compact(
            'stats', 'tahunAjaranList', 'filterTahun', 'chartLabelKelas',
            'chartDataContent', 'chartDataAssessment', 'listSiswaPasif'
        ));
    }

    public function CalendarView($role, $schoolName, $schoolId)
    {
        $eventsFromDb = AcademicCalendar::where('school_partner_id', $schoolId)
            ->orderBy('date', 'asc')
            ->get();

        // 1. Data Kalender untuk FullCalendar (Visual Kalender Bulan)
        $savedEvents = [];
        foreach ($eventsFromDb as $ev) {
            $savedEvents[] = [
                'date' => date('Y-m-d', strtotime($ev->date)),
                'title' => $ev->title,
                'type' => $ev->type,
                'color' => $ev->color,
                'status' => $ev->status,
            ];
        }

        // =========================================================================
        // FITUR A: MENGHITUNG TOTAL KEGIATAN PER HARI (Untuk notifikasi/badge)
        // =========================================================================
        $kegiatanPerHari = $eventsFromDb->groupBy(function ($date) {
            return Carbon::parse($date->date)->format('Y-m-d');
        })->map(function ($items) {
            return $items->count();
        })->toArray();

        // =========================================================================
        // FITUR B: MENGGABUNGKAN KEGIATAN YANG SAMA DI HARI BERURUTAN (Bulk 1-5)
        // =========================================================================
        $groupedAgenda = [];
        foreach ($eventsFromDb as $event) {
            $date = Carbon::parse($event->date)->startOfDay();
            $title = trim($event->title);

            $found = false;

            // Cek apakah kegiatan ini adalah kelanjutan dari kegiatan sebelumnya
            if (count($groupedAgenda) > 0) {
                // Iterasi dari belakang untuk mencari kegiatan yang sama persis
                for ($i = count($groupedAgenda) - 1; $i >= 0; $i--) {
                    if ($groupedAgenda[$i]['title'] === $title) {
                        $lastDateInGroup = Carbon::parse($groupedAgenda[$i]['end_date'])->startOfDay();

                        // Jika selisih harinya TEPAT 1 hari (berurutan)
                        if ($lastDateInGroup->diffInDays($date) == 1 && $lastDateInGroup->lt($date)) {
                            // Perpanjang tanggal akhirnya saja
                            $groupedAgenda[$i]['end_date'] = $event->date;
                            $found = true;
                            break;
                        }
                    }
                }
            }

            // Jika ini kegiatan baru (atau tidak berurutan), buat grup baru
            if (! $found) {
                $groupedAgenda[] = [
                    'id' => $event->id,
                    'title' => $title,
                    'start_date' => $event->date,
                    'end_date' => $event->date,
                    'color' => $event->color ?? '#0071BC',
                ];
            }
        }

        // Format teks tanggalnya agar cantik dibaca di Blade
        $agendaSekolah = collect($groupedAgenda)->map(function ($item) {
            $start = Carbon::parse($item['start_date']);
            $end = Carbon::parse($item['end_date']);

            if ($start->isSameDay($end)) {
                // Tepat 1 hari (Misal: 10 Mei 2026)
                $tanggalTeks = $start->translatedFormat('d M Y');
            } elseif ($start->isSameMonth($end)) {
                // Beda hari tapi 1 bulan (Misal: 1 - 5 Mei 2026)
                $tanggalTeks = $start->format('d').' - '.$end->translatedFormat('d M Y');
            } else {
                // Lintas bulan (Misal: 28 Apr - 2 Mei 2026)
                $tanggalTeks = $start->translatedFormat('d M').' - '.$end->translatedFormat('d M Y');
            }

            return (object) [
                'id' => $item['id'],
                'kegiatan' => $item['title'],
                'tanggal_teks' => $tanggalTeks,
                'color' => $item['color'],
                // Hitung total hari (Jika butuh ditampilkan "Selama 5 Hari")
                'durasi_hari' => $start->diffInDays($end) + 1,
            ];
        });

        return view('features.lms.headmaster.information.calender', compact(
            'role', 'schoolName', 'schoolId', 'savedEvents', 'agendaSekolah', 'kegiatanPerHari'
        ));
    }

    public function saveCalendarData(Request $request, $role, $schoolName, $schoolId)
    {
        try {
            $status = $request->status;
            $events = $request->events;

            AcademicCalendar::where('school_partner_id', $schoolId)->delete();

            if (! empty($events)) {
                $insertData = [];
                foreach ($events as $event) {
                    $insertData[] = [
                        'school_partner_id' => $schoolId,
                        'date' => $event['date'],
                        'title' => $event['title'],
                        'type' => $event['type'] ?? 'school_event',
                        'color' => $event['color'] ?? '#F59E0B',
                        'status' => $status,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                AcademicCalendar::insert($insertData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Kalender berhasil disimpan permanen ke database!',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'GAGAL DATABASE: '.$e->getMessage(),
            ], 500);
        }
    }

    // =========================================================
    // MENU PENYUSUNAN JADWAL
    // =========================================================
    public function scheduleView($role, $schoolName, $schoolId)
    {
        // 1. Ambil daftar kelas
        $classes = DB::table('school_classes')
            ->where('school_partner_id', $schoolId)
            ->where('status_class', 'active')
            ->select('id', 'class_name', 'kelas_id')
            ->orderBy('kelas_id', 'asc')
            ->orderBy('class_name', 'asc')
            ->get();

        // 2. MENGAMBIL SELURUH JADWAL DARI SEMUA KELAS DI SEKOLAH INI
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

        // Variabel $timeSlots SUDAH DIHAPUS karena digenerate otomatis oleh Javascript
        return view('features.lms.headmaster.information.schedule', compact(
            'role', 'schoolName', 'schoolId', 'classes', 'allSchedules'
        ));
    }

    public function getScheduleDataAjax($schoolId, $classId)
    {
        try {
            $classInfo = DB::table('school_classes')
                ->where('id', $classId)
                ->where('school_partner_id', $schoolId)
                ->first();

            if (! $classInfo) {
                return response()->json(['success' => false, 'message' => 'Kelas tidak ditemukan.']);
            }

            $teachersData = DB::table('teacher_mapels')
                ->join('school_staff_profiles', 'teacher_mapels.user_id', '=', 'school_staff_profiles.user_id')
                ->join('mapels', 'teacher_mapels.mapel_id', '=', 'mapels.id')
                ->join('school_classes', 'teacher_mapels.school_class_id', '=', 'school_classes.id')
                ->where('school_staff_profiles.school_partner_id', $schoolId)
                ->where('school_classes.kelas_id', $classInfo->kelas_id)
                ->where(function ($query) use ($classInfo) {
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
                    'id' => $t->user_id,
                    'name' => $t->nama_lengkap ?? 'Guru '.$t->user_id,
                    'subject_id' => $t->mapel_id,
                    'subject' => $t->mata_pelajaran,
                    'color' => $colors[$index % count($colors)],
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
                        'day_of_week' => $item->day_of_week,
                        'start_time' => substr($item->start_time, 0, 5),
                        'teacher_id' => $item->teacher_id,
                        'teacher_name' => $item->teacher_name,
                        'subject_id' => $item->mapel_id,
                        'subject_name' => $item->subject_name,
                        'color' => $item->color,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'available_mapels' => $available_mapels,
                'data' => $formattedSchedules,
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

        if (! $classId) {
            return response()->json(['success' => false, 'message' => 'ID Kelas tidak valid.']);
        }

        DB::beginTransaction();
        try {
            // 1. Cek Bentrok
            if (! empty($schedules)) {
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
                            'message' => "BENTROK JADWAL: {$s['teacher_name']} sudah mengajar di kelas {$clash->class_name} pada hari {$s['day']} jam {$s['start_time']}.",
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
                'class_id' => $classId,
                'class_name' => $className,
                'status' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $legacyColumns = [
                'day_of_week' => '-',
                'start_time' => '00:00',
                'end_time' => '00:00',
                'teacher_id' => '0',
                'teacher_name' => '-',
                'subject_name' => '-',
                'mapel_id' => '0',
                'color' => '-',
            ];

            foreach ($legacyColumns as $col => $dummyValue) {
                if (Schema::hasColumn('lesson_schedules', $col)) {
                    $parentData[$col] = $dummyValue;
                }
            }

            $newParentId = DB::table('lesson_schedules')->insertGetId($parentData);

            // 4. Simpan Detail Jadwal (Children)
            if (! empty($schedules)) {
                $items = [];
                $now = now();

                foreach ($schedules as $s) {
                    $endTime = date('H:i', strtotime('+45 minutes', strtotime($s['start_time'])));
                    $items[] = [
                        'lesson_schedule_id' => $newParentId,
                        'teacher_id' => $s['teacher_id'],
                        'mapel_id' => $s['subject_id'],
                        'teacher_name' => $s['teacher_name'],
                        'subject_name' => $s['subject_name'],
                        'day_of_week' => $s['day'],
                        'start_time' => $s['start_time'],
                        'end_time' => $endTime,
                        'color' => $s['color'] ?? '#0071BC',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                LessonScheduleItem::insert($items);
            }

            DB::commit();

            $msgStatus = $status === 'published' ? 'dipublikasikan' : 'disimpan sebagai draft';

            return response()->json([
                'success' => true,
                'message' => "Jadwal kelas {$className} berhasil {$msgStatus}!",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan: '.$e->getMessage(),
            ], 500);
        }
    }

    // =================================================================
    // MANAJEMEN POLLING (KEPALA SEKOLAH)
    // =================================================================

    public function pollingIndex(Request $request)
    {
        $user = Auth::user();
        $staffProfile = SchoolStaffProfile::where('user_id', $user->id)->first();

        if (! $staffProfile) {
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
        $pollsQuery = Poll::with('PollOptions')
            ->where('school_partner_id', $schoolId);

        // 3. TERAPKAN FILTER JIKA ADA
        if (! empty($filterPembuat)) {
            $pollsQuery->where('author_role', $filterPembuat);
        }

        if (! empty($filterTarget)) {
            $pollsQuery->where('target', $filterTarget);
        }

        // 4. EKSEKUSI QUERY
        $polls = $pollsQuery->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($poll) {
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
            'target' => 'required|in:Semua Warga Sekolah,Semua Guru,Semua Siswa,Semua Orang Tua',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string|max:100',
        ]);

        $user = Auth::user();
        $staffProfile = SchoolStaffProfile::where('user_id', $user->id)->first();

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
                'class_id' => $kelasId,
                'target' => $request->target,
                'question' => $request->question,
                'author_id' => $user->id,
                'author_role' => $user->role,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // 2. Masukkan ID Author & Teacher sesuai format tabel baru
            if (Schema::hasColumn('polls', 'author_id')) {
                $pollData['author_id'] = $user->id;
            }
            if (Schema::hasColumn('polls', 'teacher_id')) {
                $pollData['teacher_id'] = $user->id;
            }

            // 3. Eksekusi penyimpanan ke database
            $pollId = DB::table('polls')->insertGetId($pollData);

            $optionsData = [];
            foreach ($request->options as $optText) {
                $optionsData[] = [
                    'poll_id' => $pollId,
                    'option_text' => $optText,
                    'votes_count' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('poll_options')->insert($optionsData);

            DB::commit();

            return back()->with('success', 'Polling baru berhasil dipublikasikan!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal membuat polling: '.$e->getMessage());
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
            $options = PollOption::where('poll_id', $id)->get();
            $votes = DB::table('poll_votes')
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
                    'Guru/Manajemen' => $dataGuru,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function storePengumuman(Request $request)
    {
        $user = Auth::user();

        $staffProfile = SchoolStaffProfile::where('user_id', $user->id)->first();

        if (! $staffProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Profil staff tidak ditemukan',
            ], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:info,penting',
            'content' => 'required|string',
        ]);

        try {
            DB::table('announcements')->insert([
                // ✅ FIX DI SINI
                'school_partner_id' => $staffProfile->school_partner_id,

                'author_id' => $user->id,
                'author_role' => $user->role,
                'target' => 'Guru',
                'title' => $request->title,
                'type' => $request->type,
                'content' => $request->content,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pengumuman berhasil dikirim ke seluruh Guru!',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: '.$e->getMessage(),
            ], 500);
        }
    }
}

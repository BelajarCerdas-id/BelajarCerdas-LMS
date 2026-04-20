<?php

namespace App\Http\Controllers;

use App\Events\LmsSchoolSubscription;
use App\Models\AcademicCalendar;
use App\Models\LessonSchedule;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\SchoolLmsSubscription;
use App\Models\SchoolPartner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LmsController extends Controller
{
    // function lms school subscription view
    public function lmsSchoolSubscriptionView()
    {
        return view('features.lms.administrator.lms-school-subscription');
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
            'lmsAcademicManagement' => '/lms/school-subscription/:schoolName/:schoolId/academic-management',
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

    public function lmsTeacherView($schoolName, $schoolId)
    {
        $tanggalDipilih = request('date', date('Y-m-d'));
        $user = Auth::user();
    
        if (!$user || $user->role !== 'Guru') {
        abort(403, 'Akses Ditolak.');
        }   

        $teacherId = $user->teacher_id ?? $user->id; 

        $totalKelas = DB::table('lesson_schedule_items')
            ->join('lesson_schedules', 'lesson_schedule_items.lesson_schedule_id', '=', 'lesson_schedules.id')
            ->where('lesson_schedules.school_partner_id', $schoolId)
            ->where('lesson_schedule_items.teacher_id', $teacherId)
            ->distinct('lesson_schedules.class_id')
            ->count('lesson_schedules.class_id');

        $englishDaySekarang = date('l'); 
        $mapHari = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
        ];
        $hariIni = $mapHari[$englishDaySekarang] ?? 'Senin';

        $dbSchedules = DB::table('lesson_schedule_items')
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
        $monthlyEvents = AcademicCalendar::where('school_partner_id', $schoolId)
            ->whereDate('date', $tanggalDipilih) 
            ->get();

        $recentPolls = Poll::where('school_partner_id', $schoolId)
            ->where('teacher_id', $user->id) 
            ->orderBy('created_at', 'desc')
            ->take(4) 
            ->get();

        foreach ($recentPolls as $poll) {
            $options = PollOption::where('poll_id', $poll->id)->get();
            $labels = [];
            $votes = [];
            
            foreach ($options as $opt) {
                $labels[] = $opt->option_text;
                $count = DB::table('poll_votes')
                            ->where('poll_option_id', $opt->id)
                            ->count();
                $votes[] = $count; 
            }
            
            $poll->chart_labels = json_encode($labels);
            $poll->chart_data = json_encode($votes);
        }

        return view('features.lms.teacher.dashboard', compact(
            'schoolName', 
            'schoolId', 
            'totalKelas', 
            'totalJadwalHariIni', 
            'jadwalMengajar', 
            'monthlyEvents',
            'recentPolls',
            'hariIni',
            'tanggalDipilih'
        ));
    }
    public function classDetailView($schoolName, $schoolId, $scheduleId)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'Guru') abort(403, 'Akses Ditolak.');

        $jadwal = LessonSchedule::findOrFail($scheduleId);

        $totalSiswa = DB::table('student_school_classes')
            ->where('school_class_id', $jadwal->class_id) 
            ->count();

        $pengumumanTerkini = DB::table('announcements')
            ->where('school_partner_id', $schoolId)
            ->where(function($query) use ($jadwal) {
                $query->where('target_class_id', $jadwal->class_id)->orWhereNull('target_class_id'); 
            })
            ->orderBy('created_at', 'desc')
            ->take(4) 
            ->get();

        $today = date('Y-m-d');
        $attendances = DB::table('attendances')
            ->where('schedule_id', $scheduleId)
            ->where('date', $today)
            ->get();

        $tugasKelas = DB::table('class_tasks')
            ->where('class_id', $jadwal->class_id)
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($tugasKelas as $tugas) {
            $tugas->terkumpul = DB::table('class_task_submissions')
                ->where('task_id', $tugas->id)
                ->count();
        }

        $statistik = (object)[
            'totalSiswa'      => $totalSiswa,
            'totalMateri'     => 0, 
            'totalAssessment' => 0, 
            
            'totalPr'         => $tugasKelas->count(), 
            
            'hadir'           => $attendances->where('status', 'hadir')->count(), 
            'izin'            => $attendances->where('status', 'izin')->count(),
            'sakit'           => $attendances->where('status', 'sakit')->count(),
            'alpa'            => $attendances->where('status', 'alpa')->count(),
        ];

        return view('features.lms.teacher.class_detail', compact(
            'schoolName', 
            'schoolId', 
            'jadwal',    
            'statistik',
            'pengumumanTerkini',
            'tugasKelas'
        ));
    }
    public function getStudentsForAttendance($classId)
    {
        try {
            $today = date('Y-m-d');
            $scheduleId = request('schedule_id'); 
            
            $students = DB::table('student_school_classes')
                ->join('student_profiles', 'student_school_classes.student_id', '=', 'student_profiles.user_id')
                ->leftJoin('attendances', function($join) use ($today, $scheduleId) {
                    $join->on('student_school_classes.student_id', '=', 'attendances.student_id')->where('attendances.date', '=', $today)->where('attendances.schedule_id', '=', $scheduleId);
                })
                ->where('student_school_classes.school_class_id', $classId)->where('student_school_classes.student_class_status', 'active')->select(
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
                DB::table('attendances')->updateOrInsert(
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
        $school = SchoolPartner::find($schoolId);
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
    public function storePengumuman(Request $request)
    {
        try {
            $user = Auth::user();
            
            $schoolId = $request->school_id ?? $user->school_partner_id ?? 1; 

            DB::table('announcements')->insert([
                'school_partner_id' => $schoolId,
                'teacher_id'        => $user->id,
                'target_class_id'   => $request->class_id, // Bisa null jika global, atau ID kelas jika spesifik
                'title'             => $request->title,
                'content'           => $request->input('content'),
                'type'              => $request->type,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Pengumuman berhasil disiarkan!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function markAsRead(Request $request)
    {
        try {
            $announcementId = $request->id;
            $studentId = Auth::id();
            $alreadyRead = DB::table('announcement_views')
                ->where('announcement_id', $announcementId)
                ->where('student_id', $studentId)
                ->exists();

            if (!$alreadyRead) {
                DB::table('announcement_views')->insert([
                    'announcement_id' => $announcementId,
                    'student_id'      => $studentId,
                    'created_at'      => now(),
                    'updated_at'      => now()
                ]);
                DB::table('announcements')
                    ->where('id', $announcementId)
                    ->increment('views_count');
            }

            return response()->json(['success' => true, 'message' => 'Tracking berhasil']);
        } catch (\Exception $e) {
            // PERUBAHAN DI SINI: Kita tangkap pesan error aslinya!
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    public function storeTugas(Request $request)
    {
        try {
            $user = Auth::user();
            
            DB::table('class_tasks')->insert([
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
            $task = DB::table('class_tasks')->where('id', $taskId)->first();
            if (!$task) return response()->json(['error' => 'Tugas tidak ditemukan'], 404);

            $students = DB::table('student_school_classes')
                ->join('student_profiles', 'student_school_classes.student_id', '=', 'student_profiles.user_id')
                ->leftJoin('class_task_submissions', function($join) use ($taskId) {
                    $join->on('student_school_classes.student_id', '=', 'class_task_submissions.student_id')->where('class_task_submissions.task_id', '=', $taskId);
                })
                ->where('student_school_classes.school_class_id', $task->class_id)->where('student_school_classes.student_class_status', 'active')->select(
                    'student_profiles.user_id as student_id',
                    'student_profiles.nama_lengkap as name',
                    'class_task_submissions.id as submission_id',
                    'class_task_submissions.score',
                    'class_task_submissions.status'
                )
                ->orderBy('student_profiles.nama_lengkap', 'asc')
                ->get();

            return response()->json([
                'task' => $task,
                'students' => $students
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
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

                DB::table('class_task_submissions')->updateOrInsert(
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
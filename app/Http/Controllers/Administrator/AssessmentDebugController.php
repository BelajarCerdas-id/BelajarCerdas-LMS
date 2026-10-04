<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\SchoolAssessment;
use App\Models\SchoolPartner;
use App\Models\StudentAssessmentAnswer;
use App\Models\StudentAssessmentAttempt;
use App\Models\StudentAssessmentSummary;
use App\Models\UserAccount;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Helpers\TimezoneHelper;
use App\Services\LMS\AssessmentSummaryService\AssessmentSummaryService;
use App\Models\SchoolAssessmentQuestion;
use App\Models\LmsQuestionBank;
use App\Models\LmsQuestionOption;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AssessmentDebugController extends Controller
{
    protected AssessmentSummaryService $summaryService;

    public function __construct(AssessmentSummaryService $summaryService)
    {
        $this->summaryService = $summaryService;
    }
    /**
     * Otorisasi ketat: Hanya Administrator utama yang dapat mengakses
     */
    protected function authorizeAdmin(): void
    {
        if (!Auth::check() || !in_array(strtolower((string) Auth::user()->role), ['administrator', 'admin', 'office', 'superadmin', 'super admin', 'kepala sekolah', 'kurikulum'])) {
            abort(403, 'Akses ditolak. Halaman debug ini hanya dapat diakses oleh Administrator.');
        }
    }

    /**
     * Halaman Utama Assessment Debugger
     */
    public function index(Request $request, string $role): View
    {
        $this->authorizeAdmin();

        $schools = SchoolPartner::select('id', 'nama_sekolah', 'npsn', 'jenjang_sekolah')
            ->orderBy('nama_sekolah', 'asc')
            ->get();

        return view('features.lms.administrator.assessment-debug', compact('role', 'schools'));
    }

    /**
     * Data List Attempt Siswa (AJAX)
     */
    public function getAttempts(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        try {
            $search = $request->input('search');
            $schoolId = $request->input('school_id');
            $status = $request->input('status');

            $query = StudentAssessmentAttempt::with([
                'UserAccount.StudentProfile.SchoolPartner',
                'UserAccount.StudentSchoolClass.SchoolClass',
                'SchoolAssessment.SchoolAssessmentType',
                'SchoolAssessment.Mapel',
                'SchoolAssessment.SchoolClass',
                'SchoolAssessment.SchoolPartner'
            ]);

            if (!empty($status) && $status !== 'all') {
                $query->where('status', $status);
            }

            if (!empty($schoolId) && $schoolId !== 'all') {
                $query->where(function ($q) use ($schoolId) {
                    $q->whereHas('SchoolAssessment', function ($sa) use ($schoolId) {
                        $sa->where('school_partner_id', $schoolId);
                    })->orWhereHas('UserAccount.StudentProfile', function ($sp) use ($schoolId) {
                        $sp->where('school_partner_id', $schoolId);
                    });
                });
            }

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('UserAccount.StudentProfile', function ($sp) use ($search) {
                        $sp->where('nama_lengkap', 'like', "%{$search}%")
                            ->orWhere('nisn', 'like', "%{$search}%")
                            ->orWhere('personal_email', 'like', "%{$search}%");
                    })->orWhereHas('UserAccount', function ($u) use ($search) {
                        $u->where('email', 'like', "%{$search}%");
                    })->orWhereHas('SchoolAssessment', function ($sa) use ($search) {
                        $sa->where('title', 'like', "%{$search}%");
                    })->orWhereHas('SchoolAssessment.SchoolAssessmentType', function ($st) use ($search) {
                        $st->where('name', 'like', "%{$search}%");
                    })->orWhereHas('SchoolAssessment.Mapel', function ($m) use ($search) {
                        $m->where('mata_pelajaran', 'like', "%{$search}%");
                    });
                });
            }

            // Summary Counts
            $summaryCounts = [
                'total' => StudentAssessmentAttempt::count(),
                'cheating' => StudentAssessmentAttempt::where('status', 'cheating')->count(),
                'in_progress' => StudentAssessmentAttempt::where('status', 'in_progress')->count(),
                'submitted' => StudentAssessmentAttempt::where('status', 'submitted')->count(),
                'timeout' => StudentAssessmentAttempt::where('status', 'timeout')->count(),
                'total_assessments' => SchoolAssessment::count(),
            ];

            $attempts = $query->latest('updated_at')->paginate(20);

            // Ambil skor & butir terjawab untuk 20 attempts ini secara efisien (1 query batch)
            $studentIds = $attempts->getCollection()->pluck('student_id')->unique();
            $assessmentIds = $attempts->getCollection()->pluck('school_assessment_id')->unique();

            $scoreMap = StudentAssessmentAnswer::whereIn('student_id', $studentIds)
                ->whereIn('school_assessment_id', $assessmentIds)
                ->groupBy('student_id', 'school_assessment_id')
                ->select(
                    'student_id',
                    'school_assessment_id',
                    DB::raw('SUM(question_score) as total_score'),
                    DB::raw("COUNT(CASE WHEN answer_value IS NOT NULL AND answer_value != '' AND answer_value != '[]' THEN 1 END) as answered_count"),
                    DB::raw("COUNT(CASE WHEN answer_value IS NOT NULL AND answer_value != '' AND answer_value != '[]' AND question_score > 0 THEN 1 END) as correct_count"),
                    DB::raw("COUNT(CASE WHEN answer_value IS NOT NULL AND answer_value != '' AND answer_value != '[]' AND (question_score <= 0 OR question_score IS NULL) THEN 1 END) as wrong_count"),
                    DB::raw('COUNT(*) as total_answers')
                )
                ->get()
                ->keyBy(fn($r) => "{$r->student_id}_{$r->school_assessment_id}");

            $items = $attempts->getCollection()->map(function ($item) use ($scoreMap) {
                $key = "{$item->student_id}_{$item->school_assessment_id}";
                $scoreData = $scoreMap->get($key);
                $answered = $scoreData ? (int) $scoreData->answered_count : 0;
                $total = $scoreData ? (int) $scoreData->total_answers : 0;
                $item->current_score = $scoreData ? round((float) $scoreData->total_score, 2) : 0;
                $item->answered_count = $answered;
                $item->total_answers = $total;
                $item->correct_count = $scoreData ? (int) $scoreData->correct_count : 0;
                $item->wrong_count = $scoreData ? (int) $scoreData->wrong_count : 0;
                $item->empty_count = max(0, $total - $answered);
                return $item;
            });

            return response()->json([
                'status' => 'success',
                'data' => $items,
                'pagination' => [
                    'current_page' => $attempts->currentPage(),
                    'last_page' => $attempts->lastPage(),
                    'per_page' => $attempts->perPage(),
                    'total' => $attempts->total(),
                ],
                'summary' => $summaryCounts
            ]);
        } catch (\Throwable $e) {
            \Log::error('AssessmentDebugController@getAttempts error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memuat data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buka Kunci Ujian Siswa (Unlock Test)
     * Mengembalikan status ke 'in_progress', reset tab_switch_count ke 0, perpanjang waktu jika habis,
     * dan bersihkan jawaban kosong hasil auto-submit agar siswa dapat melanjutkan ujian.
     */
    public function unlock(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin();

        $attempt = StudentAssessmentAttempt::with('SchoolAssessment')->findOrFail($id);
        $duration = $attempt->SchoolAssessment->duration ?? 60;
        $extraMinutes = (int) $request->input('extra_minutes', $duration);

        if ($extraMinutes <= 0) {
            $extraMinutes = $duration;
        }

        $now = Carbon::now();
        $expireTime = $now->copy()->addMinutes($extraMinutes);

        $attempt->update([
            'status' => 'in_progress',
            'tab_switch_count' => 0,
            'expire_time' => $expireTime
        ]);

        // Bersihkan jawaban yang kosong (null) akibat auto-submit saat timeout/cheating,
        // sehingga soal-soal tersebut kembali berstatus belum dikerjakan / dapat dijawab siswa
        StudentAssessmentAnswer::where('student_id', $attempt->student_id)
            ->where('school_assessment_id', $attempt->school_assessment_id)
            ->whereNull('answer_value')
            ->delete();

        // Opsi: Jika admin mencentang opsi kembalikan semua jawaban ke draft
        if ($request->boolean('reset_to_draft')) {
            StudentAssessmentAnswer::where('student_id', $attempt->student_id)
                ->where('school_assessment_id', $attempt->school_assessment_id)
                ->update(['status_answer' => 'draft']);
        }

        // Opsi: Jika admin memilih hapus seluruh jawaban untuk mulai bersih
        if ($request->boolean('delete_all_answers')) {
            StudentAssessmentAnswer::where('student_id', $attempt->student_id)
                ->where('school_assessment_id', $attempt->school_assessment_id)
                ->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => "Ujian berhasil dibuka kunci (status: in_progress, pelanggaran: 0, sisa waktu: {$extraMinutes} menit). Soal yang belum terjawab telah dibuka kembali untuk siswa.",
            'data' => $attempt
        ]);
    }

    /**
     * Ubah Status Attempt (in_progress, submitted, cheating, timeout)
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin();

        $request->validate([
            'status' => 'required|in:in_progress,submitted,cheating,timeout'
        ]);

        $attempt = StudentAssessmentAttempt::with('SchoolAssessment')->findOrFail($id);
        $attempt->update([
            'status' => $request->status
        ]);

        if ($request->status === 'in_progress') {
            // Bersihkan jawaban kosong agar siswa dapat melanjutkan soal yang belum diisi
            StudentAssessmentAnswer::where('student_id', $attempt->student_id)
                ->where('school_assessment_id', $attempt->school_assessment_id)
                ->whereNull('answer_value')
                ->delete();

            // Jika expire_time sudah lewat, beri perpanjangan durasi dari sekarang
            if (!$attempt->expire_time || $attempt->expire_time->isPast()) {
                $duration = $attempt->SchoolAssessment->duration ?? 60;
                $attempt->update([
                    'expire_time' => Carbon::now()->addMinutes($duration)
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "Status attempt berhasil diperbarui menjadi '{$request->status}'.",
            'data' => $attempt
        ]);
    }

    /**
     * Ubah Jumlah Pelanggaran / Tab Switch Count
     */
    public function updateTabSwitchCount(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin();

        $request->validate([
            'tab_switch_count' => 'required|integer|min:0'
        ]);

        $attempt = StudentAssessmentAttempt::findOrFail($id);
        $attempt->update([
            'tab_switch_count' => (int) $request->tab_switch_count
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Jumlah pelanggaran berhasil diperbarui menjadi {$request->tab_switch_count}.",
            'data' => $attempt
        ]);
    }

    /**
     * Update Seluruh Atribut Attempt Sekaligus
     */
    public function updateAttempt(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin();

        $request->validate([
            'status' => 'required|in:in_progress,submitted,cheating,timeout',
            'tab_switch_count' => 'required|integer|min:0',
            'add_minutes' => 'nullable|integer|min:0'
        ]);

        $attempt = StudentAssessmentAttempt::with('SchoolAssessment')->findOrFail($id);

        $updateData = [
            'status' => $request->status,
            'tab_switch_count' => (int) $request->tab_switch_count,
        ];

        // Jika menambah durasi menit
        if ($request->filled('add_minutes') && (int) $request->add_minutes > 0) {
            $baseTime = ($attempt->expire_time && $attempt->expire_time->isFuture()) 
                ? $attempt->expire_time 
                : Carbon::now();
            $updateData['expire_time'] = $baseTime->copy()->addMinutes((int) $request->add_minutes);
        } elseif ($request->status === 'in_progress' && (!$attempt->expire_time || $attempt->expire_time->isPast())) {
            // Pastikan jika status in_progress, batas waktu tidak dalam kondisi kadaluarsa
            $duration = $attempt->SchoolAssessment->duration ?? 60;
            $updateData['expire_time'] = Carbon::now()->addMinutes($duration);
        }

        $attempt->update($updateData);

        if ($request->status === 'in_progress') {
            // Bersihkan jawaban kosong auto-submit
            StudentAssessmentAnswer::where('student_id', $attempt->student_id)
                ->where('school_assessment_id', $attempt->school_assessment_id)
                ->whereNull('answer_value')
                ->delete();

            if ($request->boolean('reset_to_draft')) {
                StudentAssessmentAnswer::where('student_id', $attempt->student_id)
                    ->where('school_assessment_id', $attempt->school_assessment_id)
                    ->update(['status_answer' => 'draft']);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "Data attempt berhasil diperbarui.",
            'data' => $attempt
        ]);
    }

    /**
     * Reset Jawaban Siswa untuk Assessment Ini
     */
    public function resetAnswers(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin();

        $attempt = StudentAssessmentAttempt::findOrFail($id);
        $type = $request->input('reset_type', 'empty'); // 'empty', 'all_draft', 'delete_all'

        if ($type === 'delete_all') {
            StudentAssessmentAnswer::where('student_id', $attempt->student_id)
                ->where('school_assessment_id', $attempt->school_assessment_id)
                ->delete();
            $msg = "Seluruh butir jawaban siswa berhasil dihapus bersih.";
        } elseif ($type === 'all_draft') {
            StudentAssessmentAnswer::where('student_id', $attempt->student_id)
                ->where('school_assessment_id', $attempt->school_assessment_id)
                ->update(['status_answer' => 'draft']);
            $msg = "Seluruh jawaban siswa telah diubah statusnya menjadi 'draft'. Siswa dapat mengubah jawaban kembali.";
        } else {
            StudentAssessmentAnswer::where('student_id', $attempt->student_id)
                ->where('school_assessment_id', $attempt->school_assessment_id)
                ->whereNull('answer_value')
                ->delete();
            $msg = "Jawaban kosong / auto-submit berhasil dibersihkan. Siswa dapat melanjutkan soal yang belum dijawab.";
        }

        return response()->json([
            'status' => 'success',
            'message' => $msg
        ]);
    }

    /**
     * Hapus Attempt (Reset Ujian dari Awal termasuk Jawaban)
     */
    public function deleteAttempt(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin();

        $attempt = StudentAssessmentAttempt::findOrFail($id);
        $studentId = $attempt->student_id;
        $assessmentId = $attempt->school_assessment_id;

        // Hapus juga jawaban siswa jika ada
        StudentAssessmentAnswer::where('student_id', $studentId)
            ->where('school_assessment_id', $assessmentId)
            ->delete();

        $attempt->delete();

        return response()->json([
            'status' => 'success',
            'message' => "Attempt dan data jawaban berhasil dihapus. Siswa dapat memulai sesi ujian baru dari awal."
        ]);
    }

    /**
     * Ambil Status RAM Cache Soal Ujian (List Assessment Aktif & Cache State)
     */
    public function getCacheStatus(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $search = $request->input('search');
        $schoolId = $request->input('school_id');

        $query = SchoolAssessment::with([
            'SchoolPartner',
            'SchoolClass',
            'Mapel',
            'SchoolAssessmentType'
        ])
        ->withCount('SchoolAssessmentQuestion')
        ->whereHas('SchoolAssessmentQuestion')
        ->latest('start_date');

        if (!empty($schoolId) && $schoolId !== 'all') {
            $query->where('school_partner_id', $schoolId);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('Mapel', fn($m) => $m->where('mata_pelajaran', 'like', "%{$search}%"))
                  ->orWhereHas('SchoolClass', fn($c) => $c->where('class_name', 'like', "%{$search}%"));
            });
        }

        $assessments = $query->take(60)->get();

        $cachedCount = 0;
        $uncachedCount = 0;

        $items = $assessments->map(function ($assessment) use (&$cachedCount, &$uncachedCount) {
            $sourceId = ($assessment->parent_assessment_id && in_array(strtolower($assessment->assessment_category ?? ''), ['remedial', 'susulan']))
                ? $assessment->parent_assessment_id
                : $assessment->id;

            $cacheKey = "assessment_questions_pool_{$sourceId}";
            $isCached = Cache::has($cacheKey);
            $questionCount = null;

            if ($isCached) {
                $cachedPool = Cache::get($cacheKey);
                $questionCount = is_countable($cachedPool) ? count($cachedPool) : null;
                $cachedCount++;
            } else {
                $uncachedCount++;
            }

            $startDateStr = '-';
            $endDateStr = '-';
            $statusExam = 'active';

            try {
                $timezone = $assessment->SchoolPartner?->timezone ?: config('app.timezone', 'Asia/Jakarta');
                $schoolNow = Carbon::now($timezone);

                $startDate = $assessment->start_date ? Carbon::parse($assessment->start_date)->setTimezone($timezone) : null;
                $endDate = $assessment->end_date ? Carbon::parse($assessment->end_date)->setTimezone($timezone) : null;

                if ($startDate) {
                    $startDateStr = $startDate->format('d M Y H:i');
                }
                if ($endDate) {
                    $endDateStr = $endDate->format('d M Y H:i');
                }

                if ($startDate && $schoolNow->lt($startDate)) {
                    $statusExam = 'upcoming';
                } elseif ($endDate && $schoolNow->gt($endDate)) {
                    $statusExam = 'expired';
                }
            } catch (\Throwable $e) {
                $statusExam = 'active';
            }

            return [
                'id' => $assessment->id,
                'source_id' => $sourceId,
                'title' => (string) ($assessment->title ?: ($assessment->SchoolAssessmentType->name ?? 'Assessment')),
                'type' => (string) ($assessment->SchoolAssessmentType->name ?? '-'),
                'mapel' => (string) ($assessment->Mapel->mata_pelajaran ?? '-'),
                'class_name' => (string) ($assessment->SchoolClass->class_name ?? '-'),
                'school_name' => (string) ($assessment->SchoolPartner->nama_sekolah ?? '-'),
                'start_date' => $startDateStr,
                'end_date' => $endDateStr,
                'status_exam' => $statusExam,
                'is_cached' => $isCached,
                'cache_key' => $cacheKey,
                'cached_questions_count' => $questionCount,
                'total_questions' => (int) ($assessment->school_assessment_question_count ?? 0),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $items,
            'summary' => [
                'total' => $items->count(),
                'cached' => $cachedCount,
                'uncached' => $uncachedCount,
                'cache_driver' => config('cache.default', 'file'),
            ]
        ]);
    }

    /**
     * Muat Soal Assessment ke RAM Cache (Pre-load / Warm-up)
     */
    public function warmCache(Request $request, string $id): JsonResponse
    {
        $this->authorizeAdmin();

        if ($id === 'all') {
            $assessments = SchoolAssessment::where('end_date', '>=', now()->subHours(2))
                ->whereHas('SchoolAssessmentQuestion')
                ->get();

            $warmed = 0;
            foreach ($assessments as $assessment) {
                $sourceId = ($assessment->parent_assessment_id && in_array(strtolower($assessment->assessment_category ?? ''), ['remedial', 'susulan']))
                    ? $assessment->parent_assessment_id
                    : $assessment->id;

                $this->executeWarmAssessmentCache($sourceId);
                $warmed++;
            }

            return response()->json([
                'status' => 'success',
                'message' => "Berhasil memuat {$warmed} assessment ke dalam RAM Cache."
            ]);
        }

        $assessment = SchoolAssessment::findOrFail((int) $id);
        $sourceId = ($assessment->parent_assessment_id && in_array(strtolower($assessment->assessment_category ?? ''), ['remedial', 'susulan']))
            ? $assessment->parent_assessment_id
            : $assessment->id;

        $count = $this->executeWarmAssessmentCache($sourceId);

        return response()->json([
            'status' => 'success',
            'message' => "Berhasil memuat {$count} butir soal ke dalam RAM Cache untuk '{$assessment->title}'. Siswa akan memuat soal secara instan (< 1ms)."
        ]);
    }

    /**
     * Bersihkan RAM Cache Soal Assessment (Invalidate / Evict)
     */
    public function clearCache(Request $request, string $id): JsonResponse
    {
        $this->authorizeAdmin();

        if ($id === 'all') {
            $assessments = SchoolAssessment::whereHas('SchoolAssessmentQuestion')->get();
            $cleared = 0;
            foreach ($assessments as $assessment) {
                $sourceId = ($assessment->parent_assessment_id && in_array(strtolower($assessment->assessment_category ?? ''), ['remedial', 'susulan']))
                    ? $assessment->parent_assessment_id
                    : $assessment->id;

                Cache::forget("assessment_questions_pool_{$sourceId}");
                $cleared++;
            }

            return response()->json([
                'status' => 'success',
                'message' => "Seluruh cache RAM soal ({$cleared} assessment) berhasil dibersihkan."
            ]);
        }

        $assessment = SchoolAssessment::findOrFail((int) $id);
        $sourceId = ($assessment->parent_assessment_id && in_array(strtolower($assessment->assessment_category ?? ''), ['remedial', 'susulan']))
            ? $assessment->parent_assessment_id
            : $assessment->id;

        $cacheKey = "assessment_questions_pool_{$sourceId}";
        Cache::forget($cacheKey);

        return response()->json([
            'status' => 'success',
            'message' => "RAM Cache untuk '{$assessment->title}' berhasil dibersihkan. Request berikutnya akan memuat ulang data terbaru dari database."
        ]);
    }

    /**
     * Helper eksekusi warm cache untuk 1 source assessment id
     */
    private function executeWarmAssessmentCache(int $sourceId): int
    {
        $pool = SchoolAssessmentQuestion::with([
            'LmsQuestionBank',
            'LmsQuestionBank.LmsQuestionOption',
            'LmsQuestionBank.Mapel'
        ])
        ->where('school_assessment_id', $sourceId)
        ->whereHas('LmsQuestionBank', function ($q) {
            $q->where('status_bank_soal', 'Publish');
        })
        ->orderBy('id')
        ->get();

        $cacheKey = "assessment_questions_pool_{$sourceId}";
        Cache::put($cacheKey, $pool, now()->addHours(6));

        // Warm up MariaDB InnoDB Buffer Pool disk pages
        try {
            DB::select("
                SELECT COUNT(*) as total_rows
                FROM school_assessment_questions saq
                JOIN lms_question_banks lqb ON saq.question_bank_id = lqb.id
                LEFT JOIN lms_question_options lqo ON lqb.id = lqo.question_bank_id
                WHERE saq.school_assessment_id = ?
            ", [$sourceId]);
        } catch (\Throwable $e) {}

        return $pool->count();
    }

    /**
     * Ambil Daftar Assessment untuk Assessment Views di Debugger (AJAX)
     */
    public function getAssessments(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        try {
            $search = $request->input('search');
            $schoolId = $request->input('school_id');
            $category = $request->input('category');
            $statusExam = $request->input('status');

            $query = SchoolAssessment::with([
                'SchoolPartner',
                'SchoolClass',
                'Mapel',
                'SchoolAssessmentType'
            ])
            ->withCount('SchoolAssessmentQuestion')
            ->withSum('SchoolAssessmentQuestion as total_weight', 'question_weight')
            ->latest('id');

            if (!empty($schoolId) && $schoolId !== 'all') {
                $query->where('school_partner_id', $schoolId);
            }

            if (!empty($category) && $category !== 'all') {
                $query->where('assessment_category', $category);
            }

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhereHas('Mapel', fn($m) => $m->where('mata_pelajaran', 'like', "%{$search}%"))
                      ->orWhereHas('SchoolClass', fn($c) => $c->where('class_name', 'like', "%{$search}%"))
                      ->orWhereHas('SchoolPartner', fn($p) => $p->where('nama_sekolah', 'like', "%{$search}%"))
                      ->orWhereHas('SchoolAssessmentType', fn($t) => $t->where('name', 'like', "%{$search}%"));
                });
            }

            $totalAssessments = SchoolAssessment::count();
            $remedialCount = SchoolAssessment::whereIn('assessment_category', ['remedial', 'susulan'])->count();

            $paginated = $query->paginate(15);
            $assessmentIds = $paginated->getCollection()->pluck('id')->toArray();

            // Aggregates for attempts & scores
            $attemptStats = StudentAssessmentAttempt::whereIn('school_assessment_id', $assessmentIds)
                ->select(
                    'school_assessment_id',
                    DB::raw('COUNT(*) as total_attempts'),
                    DB::raw('COUNT(CASE WHEN status = "submitted" THEN 1 END) as submitted_count'),
                    DB::raw('COUNT(CASE WHEN status = "cheating" THEN 1 END) as cheating_count'),
                    DB::raw('COUNT(CASE WHEN status = "in_progress" THEN 1 END) as in_progress_count'),
                    DB::raw('COUNT(CASE WHEN status = "timeout" THEN 1 END) as timeout_count')
                )
                ->groupBy('school_assessment_id')
                ->get()
                ->keyBy('school_assessment_id');

            // Answer scores stats per assessment
            $answerStats = StudentAssessmentAnswer::whereIn('school_assessment_id', $assessmentIds)
                ->select(
                    'school_assessment_id',
                    'student_id',
                    DB::raw('SUM(question_score) as student_total_score')
                )
                ->groupBy('school_assessment_id', 'student_id')
                ->get()
                ->groupBy('school_assessment_id')
                ->map(function ($rows) {
                    $scores = $rows->pluck('student_total_score');
                    return [
                        'participants_with_answers' => $scores->count(),
                        'avg_score' => $scores->count() > 0 ? round($scores->avg(), 2) : 0,
                        'max_score' => $scores->count() > 0 ? round($scores->max(), 2) : 0,
                        'min_score' => $scores->count() > 0 ? round($scores->min(), 2) : 0,
                    ];
                });

            $items = $paginated->getCollection()->map(function ($assessment) use ($attemptStats, $answerStats) {
                $timezone = $assessment->SchoolPartner?->timezone ?: config('app.timezone', 'Asia/Jakarta');
                $schoolNow = Carbon::now($timezone);
                $startDate = $assessment->start_date ? Carbon::parse($assessment->start_date)->setTimezone($timezone) : null;
                $endDate = $assessment->end_date ? Carbon::parse($assessment->end_date)->setTimezone($timezone) : null;

                $itemExamStatus = 'active';
                if ($startDate && $schoolNow->lt($startDate)) {
                    $itemExamStatus = 'upcoming';
                } elseif ($endDate && $schoolNow->gt($endDate)) {
                    $itemExamStatus = 'expired';
                }

                $sourceId = ($assessment->parent_assessment_id && in_array(strtolower($assessment->assessment_category ?? ''), ['remedial', 'susulan']))
                    ? $assessment->parent_assessment_id
                    : $assessment->id;
                $isCached = Cache::has("assessment_questions_pool_{$sourceId}");

                $att = $attemptStats->get($assessment->id);
                $ans = $answerStats->get($assessment->id);

                return [
                    'id' => $assessment->id,
                    'parent_assessment_id' => $assessment->parent_assessment_id,
                    'title' => (string) ($assessment->title ?: ($assessment->SchoolAssessmentType->name ?? 'Assessment')),
                    'type' => (string) ($assessment->SchoolAssessmentType->name ?? '-'),
                    'category' => (string) ($assessment->assessment_category ?: 'main'),
                    'mapel' => (string) ($assessment->Mapel->mata_pelajaran ?? '-'),
                    'class_name' => (string) ($assessment->SchoolClass->class_name ?? '-'),
                    'school_name' => (string) ($assessment->SchoolPartner->nama_sekolah ?? '-'),
                    'school_id' => $assessment->school_partner_id,
                    'duration' => (int) ($assessment->duration ?? 0),
                    'start_date' => $startDate ? $startDate->format('d M Y H:i') : '-',
                    'end_date' => $endDate ? $endDate->format('d M Y H:i') : '-',
                    'status_exam' => $itemExamStatus,
                    'is_cached' => $isCached,
                    'total_questions' => (int) ($assessment->school_assessment_question_count ?? 0),
                    'total_weight' => round((float) ($assessment->total_weight ?? 0), 2),
                    'total_attempts' => (int) ($att->total_attempts ?? 0),
                    'submitted_count' => (int) ($att->submitted_count ?? 0),
                    'in_progress_count' => (int) ($att->in_progress_count ?? 0),
                    'cheating_count' => (int) ($att->cheating_count ?? 0),
                    'timeout_count' => (int) ($att->timeout_count ?? 0),
                    'avg_score' => $ans['avg_score'] ?? 0,
                    'max_score' => $ans['max_score'] ?? 0,
                    'min_score' => $ans['min_score'] ?? 0,
                    'participants_count' => max((int)($att->total_attempts ?? 0), (int)($ans['participants_with_answers'] ?? 0)),
                ];
            });

            // Filter status_exam in collection if requested
            if (!empty($statusExam) && $statusExam !== 'all') {
                $items = $items->filter(fn($item) => $item['status_exam'] === $statusExam)->values();
            }

            return response()->json([
                'status' => 'success',
                'data' => $items,
                'pagination' => [
                    'current_page' => $paginated->currentPage(),
                    'last_page' => $paginated->lastPage(),
                    'per_page' => $paginated->perPage(),
                    'total' => $paginated->total(),
                ],
                'summary' => [
                    'total_assessments' => $totalAssessments,
                    'remedial_susulan' => $remedialCount,
                    'total_attempts' => StudentAssessmentAttempt::count(),
                ]
            ]);
        } catch (\Throwable $e) {
            \Log::error('AssessmentDebugController@getAssessments error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memuat daftar assessment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ambil Detail Assessment Lengkap (Info, Daftar Soal & Kunci, Daftar Peserta & Nilai)
     */
    public function getAssessmentDetail(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin();

        try {
            $assessment = SchoolAssessment::with([
                'SchoolPartner',
                'SchoolClass',
                'Mapel',
                'SchoolAssessmentType',
                'UserAccount.studentProfile',
                'parentAssessment'
            ])
            ->findOrFail($id);

            $timezone = $assessment->SchoolPartner?->timezone ?: config('app.timezone', 'Asia/Jakarta');
            $startDate = $assessment->start_date ? Carbon::parse($assessment->start_date)->setTimezone($timezone) : null;
            $endDate = $assessment->end_date ? Carbon::parse($assessment->end_date)->setTimezone($timezone) : null;

            // Load Soal
            $questions = SchoolAssessmentQuestion::with([
                'LmsQuestionBank.LmsQuestionOption'
            ])
            ->where('school_assessment_id', $id)
            ->orderBy('id')
            ->get()
            ->map(function ($sq, $index) {
                $bank = $sq->LmsQuestionBank;
                $options = $bank?->LmsQuestionOption ? $bank->LmsQuestionOption->map(function ($opt) {
                    return [
                        'id' => $opt->id,
                        'key' => (string) $opt->options_key,
                        'value' => (string) strip_tags($opt->options_value ?? ''),
                        'is_correct' => (bool) $opt->is_correct,
                        'extra_data' => $opt->extra_data,
                    ];
                }) : [];

                return [
                    'number' => $index + 1,
                    'id' => $sq->id,
                    'question_bank_id' => $sq->question_bank_id,
                    'weight' => (float) $sq->question_weight,
                    'tipe_soal' => (string) ($bank?->tipe_soal ?? '-'),
                    'difficulty' => (string) ($bank?->difficulty ?? '-'),
                    'question_text' => (string) strip_tags($bank?->questions ?? ''),
                    'options' => $options,
                ];
            });

            // Load Peserta Ujian
            $attempts = StudentAssessmentAttempt::with([
                'UserAccount.studentProfile.SchoolPartner',
                'UserAccount.StudentSchoolClass.SchoolClass'
            ])
            ->where('school_assessment_id', $id)
            ->get()
            ->keyBy('student_id');

            $answersGrouped = StudentAssessmentAnswer::where('school_assessment_id', $id)
                ->select(
                    'student_id',
                    DB::raw('COUNT(*) as total_answers'),
                    DB::raw("COUNT(CASE WHEN answer_value IS NOT NULL AND answer_value != '' AND answer_value != '[]' THEN 1 END) as answered_count"),
                    DB::raw("COUNT(CASE WHEN answer_value IS NOT NULL AND answer_value != '' AND answer_value != '[]' AND question_score > 0 THEN 1 END) as correct_count"),
                    DB::raw("COUNT(CASE WHEN answer_value IS NOT NULL AND answer_value != '' AND answer_value != '[]' AND (question_score <= 0 OR question_score IS NULL) THEN 1 END) as wrong_count"),
                    DB::raw("COUNT(CASE WHEN answer_value IS NULL OR answer_value = '' OR answer_value = '[]' THEN 1 END) as unanswered_count"),
                    DB::raw('SUM(question_score) as total_score')
                )
                ->groupBy('student_id')
                ->get()
                ->keyBy('student_id');

            $allStudentIds = $attempts->keys()->merge($answersGrouped->keys())->unique()->values();

            $students = UserAccount::with([
                'studentProfile',
                'StudentSchoolClass.SchoolClass'
            ])
            ->whereIn('id', $allStudentIds)
            ->get();

            $totalQuestionsCount = $questions->count();

            $participants = $students->map(function ($student) use ($attempts, $answersGrouped, $timezone, $totalQuestionsCount) {
                $att = $attempts->get($student->id);
                $ans = $answersGrouped->get($student->id);

                $startTime = $att?->start_time ? Carbon::parse($att->start_time)->setTimezone($timezone)->format('d M Y H:i') : '-';
                $expireTime = $att?->expire_time ? Carbon::parse($att->expire_time)->setTimezone($timezone)->format('d M Y H:i') : '-';

                $answered = (int) ($ans?->answered_count ?? 0);
                $correct = (int) ($ans?->correct_count ?? 0);
                $wrong = (int) ($ans?->wrong_count ?? 0);
                $empty = $totalQuestionsCount > 0 ? max(0, $totalQuestionsCount - $answered) : (int) ($ans?->unanswered_count ?? 0);

                return [
                    'student_id' => $student->id,
                    'attempt_id' => $att?->id,
                    'name' => $student->studentProfile?->nama_lengkap ?? 'Tanpa Nama',
                    'nis' => $student->studentProfile?->nis ?? $student->studentProfile?->nisn ?? '-',
                    'class_name' => $student->StudentSchoolClass?->first()?->SchoolClass?->class_name ?? '-',
                    'status' => (string) ($att?->status ?? ($ans ? 'submitted' : 'not_started')),
                    'tab_switch_count' => (int) ($att?->tab_switch_count ?? 0),
                    'total_answers' => (int) ($ans?->total_answers ?? 0),
                    'answered_count' => $answered,
                    'correct_count' => $correct,
                    'wrong_count' => $wrong,
                    'empty_count' => $empty,
                    'total_score' => round((float) ($ans?->total_score ?? 0), 2),
                    'start_time' => $startTime,
                    'expire_time' => $expireTime,
                ];
            })->sortByDesc('total_score')->values();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'assessment' => [
                        'id' => $assessment->id,
                        'title' => (string) ($assessment->title ?: ($assessment->SchoolAssessmentType->name ?? 'Assessment')),
                        'type' => (string) ($assessment->SchoolAssessmentType->name ?? '-'),
                        'category' => (string) ($assessment->assessment_category ?: 'main'),
                        'mapel' => (string) ($assessment->Mapel->mata_pelajaran ?? '-'),
                        'class_name' => (string) ($assessment->SchoolClass->class_name ?? '-'),
                        'school_name' => (string) ($assessment->SchoolPartner->nama_sekolah ?? '-'),
                        'duration' => (int) ($assessment->duration ?? 0),
                        'start_date' => $startDate ? $startDate->format('d M Y H:i') : '-',
                        'end_date' => $endDate ? $endDate->format('d M Y H:i') : '-',
                        'parent_title' => $assessment->parentAssessment?->title,
                        'instruction' => (string) strip_tags($assessment->assessment_instruction ?? ''),
                        'shuffle_questions' => (bool) $assessment->shuffle_questions,
                        'shuffle_options' => (bool) $assessment->shuffle_options,
                        'show_score' => (bool) $assessment->show_score,
                    ],
                    'questions' => $questions,
                    'participants' => $participants,
                    'summary' => [
                        'total_questions' => $questions->count(),
                        'total_weight' => round($questions->sum('weight'), 2),
                        'total_participants' => $participants->count(),
                        'avg_score' => $participants->count() > 0 ? round($participants->avg('total_score'), 2) : 0,
                        'highest_score' => $participants->count() > 0 ? round($participants->max('total_score'), 2) : 0,
                        'lowest_score' => $participants->count() > 0 ? round($participants->min('total_score'), 2) : 0,
                    ]
                ]
            ]);
        } catch (\Throwable $e) {
            \Log::error('AssessmentDebugController@getAssessmentDetail error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memuat detail assessment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper Hitung Ulang Seluruh Jawaban Siswa pada Suatu Assessment
     */
    protected function recalculateStudentAnswers(SchoolAssessment $assessment, int $studentId): array
    {
        $schoolQuestions = SchoolAssessmentQuestion::with([
            'LmsQuestionBank.LmsQuestionOption'
        ])
        ->where('school_assessment_id', $assessment->id)
        ->get()
        ->keyBy('id');

        $totalQuestions = $schoolQuestions->count();
        $assessmentCategory = strtolower($assessment->assessment_category ?? '');
        $isRemedial = $assessmentCategory === 'remedial';
        $scorePerQuestion = $totalQuestions > 0 ? (100 / $totalQuestions) : 0;

        if ($isRemedial && $assessment->parent_assessment_id) {
            $previousAssessment = null;
            $relatedAssessments = SchoolAssessment::where(function ($q) use ($assessment) {
                $q->where('id', $assessment->parent_assessment_id)->orWhere('parent_assessment_id', $assessment->parent_assessment_id);
            })->whereHas('StudentAssessmentAnswer', function ($q) use ($studentId) {
                $q->where('student_id', $studentId);
            })->orderBy('start_date')->pluck('id')->toArray();

            $currentIndex = array_search($assessment->id, $relatedAssessments);
            if ($currentIndex !== false && $currentIndex > 0) {
                $prevAssessmentId = $relatedAssessments[$currentIndex - 1];
                $previousAssessment = SchoolAssessment::find($prevAssessmentId);
            }

            $cacheKeyWrong = "assessment-remedial-wrong-{$studentId}-prev-" . ($previousAssessment->id ?? 'none');
            $wrongBankIds = Cache::get($cacheKeyWrong, []);

            if (empty($wrongBankIds)) {
                $totalRemedialQuestions = SchoolAssessmentQuestion::where('school_assessment_id', $assessment->parent_assessment_id)->count();
            } else {
                $totalRemedialQuestions = count($wrongBankIds);
            }
            if ($totalRemedialQuestions > 0) {
                $scorePerQuestion = 100 / $totalRemedialQuestions;
            }
        }

        $answers = StudentAssessmentAnswer::where('student_id', $studentId)
            ->where('school_assessment_id', $assessment->id)
            ->get();

        $oldTotalScore = $answers->sum('question_score');
        $updatedCount = 0;

        foreach ($answers as $answer) {
            $schoolQuestion = $schoolQuestions->get($answer->school_assessment_question_id);
            if (!$schoolQuestion || !$schoolQuestion->LmsQuestionBank) {
                continue;
            }

            $question = $schoolQuestion->LmsQuestionBank;

            // Jika ESSAY dan sudah dinilai oleh guru secara manual (status 'graded'), pertahankan nilai guru
            if ($question->tipe_soal === 'ESSAY' && $answer->grading_status === 'graded') {
                continue;
            }

            $answerData = $answer->answer_value;
            if (is_string($answerData)) {
                $decoded = json_decode($answerData, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $answerData = $decoded;
                }
            }
            if ($answerData === '' || $answerData === [] || $answerData === null) {
                $answerData = null;
            }

            $score = 0;
            if ($answerData !== null) {
                switch ($question->tipe_soal) {
                    case 'MCQ':
                        $correctOption = $question->LmsQuestionOption->firstWhere('is_correct', 1);
                        if ($correctOption && (string)$answerData === (string)$correctOption->options_key) {
                            $score = $isRemedial ? $scorePerQuestion : $schoolQuestion->question_weight;
                        }
                        break;

                    case 'MCMA':
                        if (is_array($answerData)) {
                            $correctOptions = $question->LmsQuestionOption
                                ->where('is_correct', 1)
                                ->pluck('options_key')
                                ->map(fn($val) => trim((string)$val))
                                ->toArray();

                            $totalCorrect = count($correctOptions);
                            if ($totalCorrect > 0) {
                                $normalizedAnswers = array_map(fn($val) => trim((string)$val), $answerData);
                                $maxScore = $isRemedial ? $scorePerQuestion : $schoolQuestion->question_weight;
                                $wrongSelected = count(array_diff($normalizedAnswers, $correctOptions));

                                // Option C: Hanya berikan skor parsial jika TIDAK ADA opsi salah yang dipilih
                                if ($wrongSelected === 0) {
                                    $correctSelected = count(array_intersect($normalizedAnswers, $correctOptions));
                                    $score = round(($correctSelected / $totalCorrect) * $maxScore, 2);
                                } else {
                                    $score = 0;
                                }
                            }
                        }
                        break;

                    case 'MATCHING':
                        if (is_array($answerData)) {
                            $correctPairs = $question->LmsQuestionOption
                                ->filter(function ($opt) {
                                    return isset($opt->extra_data['side']) && $opt->extra_data['side'] === 'left';
                                })
                                ->mapWithKeys(function ($opt) {
                                    return [$opt->options_key => $opt->extra_data['pair_with'] ?? null];
                                })
                                ->toArray();

                            ksort($correctPairs);
                            ksort($answerData);

                            if ($correctPairs === $answerData) {
                                $score = $isRemedial ? $scorePerQuestion : $schoolQuestion->question_weight;
                            }
                        }
                        break;

                    case 'PG_KOMPLEKS':
                        $correctAnswers = $question->LmsQuestionOption
                            ->filter(function ($opt) {
                                return isset($opt->extra_data['side']) && $opt->extra_data['side'] === 'item';
                            })
                            ->mapWithKeys(function ($opt) {
                                return [$opt->options_key => $opt->extra_data['answer']];
                            })
                            ->toArray();

                        ksort($correctAnswers);
                        ksort($answerData);

                        if ($correctAnswers === $answerData) {
                            $score = $isRemedial ? $scorePerQuestion : $schoolQuestion->question_weight;
                        }
                        break;

                    case 'ESSAY':
                        $score = 0;
                        break;
                }
            }

            $roundedScore = round($score, 2);
            $answer->update(['question_score' => $roundedScore]);
            $updatedCount++;
        }

        // Sinkronkan ke StudentAssessmentSummary
        $this->summaryService->updateStudentAssessmentSummary($studentId, $assessment);

        $newTotalScore = StudentAssessmentAnswer::where('student_id', $studentId)
            ->where('school_assessment_id', $assessment->id)
            ->sum('question_score');

        $rootAssessmentId = $assessment->parent_assessment_id ?: $assessment->id;
        $summary = StudentAssessmentSummary::where('student_id', $studentId)
            ->where('root_assessment_id', $rootAssessmentId)
            ->first();

        return [
            'answers_count' => $answers->count(),
            'updated_count' => $updatedCount,
            'old_total_score' => round($oldTotalScore, 2),
            'new_total_score' => round($newTotalScore, 2),
            'summary' => $summary,
        ];
    }

    /**
     * Hitung Ulang Nilai Siswa Berdasarkan Attempt ID (Recount Attempt)
     */
    public function recountAttempt(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin();

        $attempt = StudentAssessmentAttempt::with('SchoolAssessment', 'UserAccount.studentProfile')->findOrFail($id);
        $assessment = $attempt->SchoolAssessment;

        if (!$assessment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Assessment tidak ditemukan untuk attempt ini.'
            ], 404);
        }

        $studentName = $attempt->UserAccount?->studentProfile?->nama_lengkap ?? 'Siswa';
        $result = $this->recalculateStudentAnswers($assessment, $attempt->student_id);

        return response()->json([
            'status' => 'success',
            'message' => "Nilai untuk {$studentName} berhasil dihitung ulang. Skor sebelumnya: {$result['old_total_score']} -> Skor baru: {$result['new_total_score']}.",
            'data' => [
                'attempt_id' => $attempt->id,
                'student_id' => $attempt->student_id,
                'student_name' => $studentName,
                'assessment_id' => $assessment->id,
                'old_total_score' => $result['old_total_score'],
                'new_total_score' => $result['new_total_score'],
                'answers_count' => $result['answers_count'],
                'summary' => $result['summary'],
            ]
        ]);
    }

    /**
     * Hitung Ulang Nilai Siswa Tertentu pada Suatu Assessment (Recount Per User)
     */
    public function recountUser(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $request->validate([
            'assessment_id' => 'required|integer|exists:school_assessments,id',
            'student_id' => 'required|integer|exists:user_accounts,id',
        ]);

        $assessment = SchoolAssessment::findOrFail($request->assessment_id);
        $student = UserAccount::with('studentProfile')->findOrFail($request->student_id);
        $studentName = $student->studentProfile?->nama_lengkap ?? 'Siswa';

        $result = $this->recalculateStudentAnswers($assessment, $request->student_id);

        return response()->json([
            'status' => 'success',
            'message' => "Nilai untuk {$studentName} berhasil dihitung ulang. Skor sebelumnya: {$result['old_total_score']} -> Skor baru: {$result['new_total_score']}.",
            'data' => [
                'student_id' => $request->student_id,
                'student_name' => $studentName,
                'assessment_id' => $assessment->id,
                'old_total_score' => $result['old_total_score'],
                'new_total_score' => $result['new_total_score'],
                'answers_count' => $result['answers_count'],
                'summary' => $result['summary'],
            ]
        ]);
    }

    /**
     * Hitung Ulang Seluruh Nilai Peserta pada Suatu Assessment (Recount Per Assessment)
     */
    public function recountAssessment(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin();

        $assessment = SchoolAssessment::findOrFail($id);

        // Ambil seluruh student_id yang memiliki attempt atau jawaban pada assessment ini
        $studentIdsAttempt = StudentAssessmentAttempt::where('school_assessment_id', $id)->pluck('student_id');
        $studentIdsAnswer = StudentAssessmentAnswer::where('school_assessment_id', $id)->pluck('student_id');
        $studentIds = $studentIdsAttempt->merge($studentIdsAnswer)->unique()->values();

        if ($studentIds->isEmpty()) {
            return response()->json([
                'status' => 'warning',
                'message' => "Tidak ada data peserta atau jawaban yang ditemukan untuk assessment '{$assessment->title}'.",
                'data' => ['recounted_students' => 0]
            ]);
        }

        $recountedStudents = 0;
        $totalAnswersCount = 0;

        foreach ($studentIds as $studentId) {
            $res = $this->recalculateStudentAnswers($assessment, $studentId);
            $totalAnswersCount += $res['answers_count'];
            $recountedStudents++;
        }

        return response()->json([
            'status' => 'success',
            'message' => "Berhasil menghitung ulang nilai untuk seluruh peserta ({$recountedStudents} siswa, {$totalAnswersCount} butir jawaban) pada assessment '{$assessment->title}'.",
            'data' => [
                'assessment_id' => $id,
                'assessment_title' => $assessment->title,
                'recounted_students' => $recountedStudents,
                'total_answers' => $totalAnswersCount,
            ]
        ]);
    }

    /**
     * Ambil Detail Lembar Jawaban Siswa Tertentu (Exam Sheet & Breakdown Butir Soal)
     */
    public function getStudentExamDetail(Request $request, int $assessmentId, int $studentId): JsonResponse
    {
        $this->authorizeAdmin();

        try {
            $assessment = SchoolAssessment::with([
                'SchoolPartner',
                'SchoolClass',
                'Mapel',
                'SchoolAssessmentType'
            ])->findOrFail($assessmentId);

            $student = UserAccount::with([
                'studentProfile',
                'StudentSchoolClass.SchoolClass'
            ])->findOrFail($studentId);

            $attempt = StudentAssessmentAttempt::where('school_assessment_id', $assessmentId)
                ->where('student_id', $studentId)
                ->first();

            $timezone = $assessment->SchoolPartner?->timezone ?: config('app.timezone', 'Asia/Jakarta');

            // Load Butir Soal Ujian
            $schoolQuestions = SchoolAssessmentQuestion::with([
                'LmsQuestionBank.LmsQuestionOption'
            ])
            ->where('school_assessment_id', $assessmentId)
            ->orderBy('id')
            ->get();

            // Load Jawaban Siswa
            $answers = StudentAssessmentAnswer::where('school_assessment_id', $assessmentId)
                ->where('student_id', $studentId)
                ->get()
                ->keyBy('school_assessment_question_id');

            $items = [];
            $correctCount = 0;
            $wrongCount = 0;
            $emptyCount = 0;
            $totalScore = 0;

            foreach ($schoolQuestions as $index => $sq) {
                $bank = $sq->LmsQuestionBank;
                $answer = $answers->get($sq->id);

                $rawAnswer = $answer?->answer_value;
                if (is_string($rawAnswer)) {
                    $decoded = json_decode($rawAnswer, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $rawAnswer = $decoded;
                    }
                }

                $isEmpty = ($rawAnswer === null || $rawAnswer === '' || $rawAnswer === [] || $rawAnswer === '[]');

                $options = $bank?->LmsQuestionOption ? $bank->LmsQuestionOption->map(function ($opt) {
                    return [
                        'id' => $opt->id,
                        'key' => (string) $opt->options_key,
                        'value' => (string) strip_tags($opt->options_value ?? ''),
                        'is_correct' => (bool) $opt->is_correct,
                        'extra_data' => $opt->extra_data,
                    ];
                })->values()->toArray() : [];

                $tipeSoal = strtoupper($bank?->tipe_soal ?? '');
                $weight = (float) $sq->question_weight;
                $score = (float) ($answer?->question_score ?? 0);

                $evalStatus = 'empty';
                if ($isEmpty) {
                    $emptyCount++;
                    $evalStatus = 'empty';
                } elseif ($score <= 0) {
                    $wrongCount++;
                    $evalStatus = 'wrong';
                } else {
                    // Skor > 0
                    if ($tipeSoal === 'MCQ' || $tipeSoal === 'MATCHING' || $tipeSoal === 'PG_KOMPLEKS') {
                        // Tipe soal ini tidak memiliki skor parsial; jawaban benar bernilai penuh
                        $evalStatus = 'correct';
                    } elseif ($tipeSoal === 'MCMA') {
                        $correctKeys = collect($options)->where('is_correct', true)->pluck('key')->map(fn($k) => strtolower(trim((string)$k)))->toArray();
                        $studentSelected = is_array($rawAnswer)
                            ? array_map(fn($v) => strtolower(trim((string)$v)), $rawAnswer)
                            : (is_string($rawAnswer) ? [strtolower(trim($rawAnswer))] : []);

                        $wrongSelected = count(array_diff($studentSelected, $correctKeys));
                        $correctSelected = count(array_intersect($studentSelected, $correctKeys));

                        if ($wrongSelected === 0 && count($correctKeys) > 0 && $correctSelected === count($correctKeys)) {
                            $evalStatus = 'correct';
                        } else {
                            $evalStatus = 'partial';
                        }
                    } else {
                        // ESSAY atau lainnya: toleransi selisih pembulatan tipe kolom MySQL INT (misal 3 vs 3.33)
                        $isFull = ($score >= $weight) || ($weight > 0 && ($score / $weight) >= 0.85) || (abs($score - $weight) <= 0.5);
                        $evalStatus = $isFull ? 'correct' : 'partial';
                    }

                    $correctCount++;
                }

                // Jika status jawaban 'correct' penuh dan selisih score dengan weight disebabkan oleh pembulatan kolom integer MySQL (misal 3 vs 3.33)
                if ($evalStatus === 'correct' && $weight > 0) {
                    if ($score <= 0 || abs($score - $weight) <= 0.5 || $score > $weight) {
                        $score = $weight;
                    }
                }

                $totalScore += $score;

                $items[] = [
                    'number' => $index + 1,
                    'question_id' => $sq->id,
                    'question_bank_id' => $sq->question_bank_id,
                    'tipe_soal' => (string) ($bank?->tipe_soal ?? '-'),
                    'difficulty' => (string) ($bank?->difficulty ?? '-'),
                    'question_text' => (string) strip_tags($bank?->questions ?? ''),
                    'weight' => (float) $sq->question_weight,
                    'student_answer' => $rawAnswer,
                    'score' => round($score, 2),
                    'status_answer' => (string) ($answer?->status_answer ?? 'not_answered'),
                    'grading_status' => (string) ($answer?->grading_status ?? 'not_graded'),
                    'teacher_feedback' => (string) ($answer?->teacher_feedback ?? ''),
                    'eval_status' => $evalStatus,
                    'options' => $options,
                ];
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'student' => [
                        'id' => $student->id,
                        'name' => $student->studentProfile?->nama_lengkap ?? 'Tanpa Nama',
                        'nis' => $student->studentProfile?->nis ?? $student->studentProfile?->nisn ?? '-',
                        'class_name' => $student->StudentSchoolClass?->first()?->SchoolClass?->class_name ?? '-',
                        'school_name' => $assessment->SchoolPartner?->nama_sekolah ?? '-',
                    ],
                    'assessment' => [
                        'id' => $assessment->id,
                        'title' => (string) ($assessment->title ?: ($assessment->SchoolAssessmentType->name ?? 'Assessment')),
                        'mapel' => (string) ($assessment->Mapel->mata_pelajaran ?? '-'),
                        'category' => (string) ($assessment->assessment_category ?: 'main'),
                        'duration' => (int) ($assessment->duration ?? 0),
                    ],
                    'attempt' => [
                        'id' => $attempt?->id,
                        'status' => (string) ($attempt?->status ?? 'submitted'),
                        'tab_switch_count' => (int) ($attempt?->tab_switch_count ?? 0),
                        'start_time' => $attempt?->start_time ? Carbon::parse($attempt->start_time)->setTimezone($timezone)->format('d M Y H:i:s') : '-',
                        'expire_time' => $attempt?->expire_time ? Carbon::parse($attempt->expire_time)->setTimezone($timezone)->format('d M Y H:i:s') : '-',
                    ],
                    'summary' => [
                        'total_questions' => count($items),
                        'correct_count' => $correctCount,
                        'wrong_count' => $wrongCount,
                        'empty_count' => $emptyCount,
                        'total_score' => round($totalScore, 2),
                        'max_score' => round($schoolQuestions->sum('question_weight'), 2),
                    ],
                    'questions' => $items,
                ]
            ]);
        } catch (\Throwable $e) {
            \Log::error('AssessmentDebugController@getStudentExamDetail error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memuat detail lembar jawaban siswa: ' . $e->getMessage()
            ], 500);
        }
    }
}

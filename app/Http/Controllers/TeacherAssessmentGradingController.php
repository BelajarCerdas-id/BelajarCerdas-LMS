<?php

namespace App\Http\Controllers;

use App\Models\SchoolAssessment;
use App\Models\SchoolAssessmentQuestion;
use App\Models\SchoolAssessmentType;
use App\Models\SchoolPartner;
use App\Models\StudentAssessmentAnswer;
use App\Models\StudentAssessmentSummary;
use App\Models\StudentProjectSubmission;
use App\Models\StudentSchoolClass;
use App\Models\SubjectPassingGradeCriteria;
use App\Models\UserAccount;
use App\Services\ClassName\ClassNameService;
use App\Services\LMS\AssessmentSummaryService\AssessmentSummaryService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TeacherAssessmentGradingController extends Controller
{
    // function extract class level
    private function extractClassLevel($className)
    {
        $classNameService = new ClassNameService();
        return $classNameService->extractClassLevel($className);
    }

    private $summaryService;

    public function __construct(AssessmentSummaryService $summaryService)
    {
        $this->summaryService = $summaryService;
    }

    public function assessmentGradingManagement($role, $schoolName, $schoolId)
    {
        return view('features.lms.teacher.assessment-grading.teacher-assessment-grading-management',compact('role', 'schoolName', 'schoolId'));
    }

    public function paginateAssessmentGrading(Request $request, $role, $schoolName, $schoolId)
    {
        $user = Auth::user();
        $schoolPartner = SchoolPartner::findOrFail($schoolId);
        $jenjang = strtoupper($schoolPartner->jenjang_sekolah);

        // DEFAULT LEVEL BERDASARKAN JENJANG
        $startLevelMap = [
            'SD'  => 1,  'MI'  => 1,
            'SMP' => 7,  'MTS' => 7,
            'SMA' => 10, 'SMK' => 10,
            'MA'  => 10, 'MAK' => 10,
        ];

        $defaultLevel = $startLevelMap[$jenjang] ?? 1;

        $query = SchoolAssessment::with(['Mapel', 'SchoolClass', 'SchoolAssessmentType'])->where('user_id', $user->id)->latest();
        
        $assessments = $query->get();

        // TAHUN AJARAN
        $tahunAjaran = $assessments->pluck('SchoolClass.tahun_ajaran')->unique()->sortDesc()->values();

        $searchYear = $request->filled('search_year') ? $request->search_year : ($tahunAjaran->first() ?? null);

        // FILTER BERDASARKAN TAHUN AJARAN
        $schoolClasses = $assessments->where('SchoolClass.tahun_ajaran', $searchYear)->values();

        // LEVEL KELAS UNIK
        $classLevels = $schoolClasses->pluck('SchoolClass.class_name')->map(fn($c) => (int) $this->extractClassLevel($c))->unique()->sort()->values();

        $selectedClass = $request->filled('search_class') ? (int) $request->search_class : ($classLevels->first() ?? $defaultLevel);

        // FILTER ROMBEL SESUAI LEVEL
        $schoolClasses = $schoolClasses->filter(fn($item) => (int)$this->extractClassLevel($item->SchoolClass->class_name) === $selectedClass)->values();

        // Filter berdasarkan level kelas
        if ($selectedClass) {
            $assessments = $assessments->filter(function ($item) use ($selectedClass) {

                if (!$item || !$item->SchoolClass->class_name) {
                    return false;
                }

                return $this->extractClassLevel($item->SchoolClass->class_name) == $selectedClass;
            });
        }

        $schoolAssessmentType = SchoolAssessmentType::where('school_partner_id', $schoolId)->get();

        // FILTER SEARCH ASSESSMENT TYPE
        if ($request->filled('search_assessment_type')) {
            $assessments = $assessments->filter(function ($item) use ($request) {
                return $item->SchoolAssessmentType->id == $request->search_assessment_type;
            })->values();
        }

        // manual pagination karena sudah menjadi collection
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;

        $paginated = new LengthAwarePaginator(
            $assessments->forPage($currentPage, $perPage)->values(),
            $assessments->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url()]
        );

        $paginated->getCollection()->transform(function ($assessment) {

            // total siswa aktif di kelas
            $totalStudents = StudentSchoolClass::where('school_class_id', $assessment->school_class_id)->where('student_class_status', 'active')->where(function ($q) {
                $q->whereNull('academic_action')->orWhere('academic_action', '');
            })->count();

            // TOTAL SUBMISSION
            $examStudents = StudentAssessmentAnswer::where('school_assessment_id', $assessment->id)->where('status_answer', 'submitted')->distinct()->pluck('student_id');

            $projectStudents = StudentProjectSubmission::where('school_assessment_id', $assessment->id)->distinct()->pluck('student_id');

            $submissionStudents = $examStudents->merge($projectStudents)->unique();

            $submissionCount = $submissionStudents->count();

            // TOTAL PENDING GRADING
            $pendingExamStudents = StudentAssessmentAnswer::where('school_assessment_id', $assessment->id)->where('status_answer', 'submitted')->where('grading_status', 'pending')
                ->distinct()->pluck('student_id');

            $pendingProjectStudents = StudentProjectSubmission::where('school_assessment_id', $assessment->id)->where('grading_status', 'pending')->distinct()
                ->pluck('student_id');

            $pendingStudents = $pendingExamStudents->merge($pendingProjectStudents)->unique();

            $pendingCount = $pendingStudents->count();

            // FINAL DATA
            $assessment->total_students = $totalStudents;
            $assessment->submission_count = $submissionCount;
            $assessment->pending_count = $pendingCount;

            $assessment->grading_status = $pendingCount > 0 ? 'pending' : 'completed';

            return $assessment;
        });

        return response()->json([
            'data' => $paginated->items(),
            'links' => (string) $paginated->links(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'tahunAjaran'   => $tahunAjaran,
            'selectedYear'  => $searchYear,
            'selectedClass' => $selectedClass,
            'className'     => $classLevels,
            'schoolAssessmentType' => $schoolAssessmentType,
            'assessmentGradingStudentList' => '/lms/:role/:schoolName/:schoolId/assessment-grading/:assessmentId/mode/:mode/student-list'
        ]);
    }

    public function assessmentGradingStudentList($role, $schoolName, $schoolId, $assessmentId, $mode)
    {
        return view('features.lms.teacher.assessment-grading.teacher-assessment-grading-student-list', compact('role', 'schoolName', 'schoolId', 'assessmentId', 'mode'));
    }

    public function paginateAssessmentGradingStudentList(Request $request, $role, $schoolName, $schoolId, $assessmentId, $mode) 
    {
        $assessment = SchoolAssessment::with(['SchoolClass', 'Mapel', 'SchoolAssessmentType.AssessmentMode'])->findOrFail($assessmentId);

        $rootAssessmentId = $assessment->parent_assessment_id ?? $assessment->id;

        // ambil semua assessment (main + child)
        $allAssessmentIds = SchoolAssessment::where(function ($q) use ($rootAssessmentId) {
            $q->where('id', $rootAssessmentId)
            ->orWhere('parent_assessment_id', $rootAssessmentId);
        })->pluck('id');

        $assessmentMap = SchoolAssessment::whereIn('id', $allAssessmentIds)->get()->keyBy('id');

        // STUDENTS
        $students = StudentSchoolClass::with(['UserAccount.StudentProfile'])->where('student_class_status', 'active')->where(function ($q) {
            $q->whereNull('academic_action')->orWhere('academic_action', '');
        })
            ->where('school_class_id', $assessment->school_class_id)->when($request->filled('search_student'), function ($q) use ($request) {
            $q->whereHas('UserAccount.StudentProfile', function ($sub) use ($request) {
                $sub->where('nama_lengkap', 'LIKE', '%' . $request->search_student . '%');
            });
        })->get()->sortBy(fn($s) => strtolower($s->UserAccount->StudentProfile->nama_lengkap ?? ''))->values();

        $studentIds = $students->pluck('student_id');

        //  SUMMARY
        $summaries = StudentAssessmentSummary::whereIn('student_id', $studentIds)->where('root_assessment_id', $rootAssessmentId)->get()->keyBy('student_id');

        // ANSWERS
        $allAnswers = StudentAssessmentAnswer::whereIn('school_assessment_id', $allAssessmentIds)->whereIn('student_id', $studentIds)->where('status_answer', 'submitted')->get()->groupBy('student_id');

        $allProjects = StudentProjectSubmission::whereIn('school_assessment_id', $allAssessmentIds)->whereIn('student_id', $studentIds)->get()->groupBy('student_id');

        //  KKM
        $schoolYear = $assessment->SchoolClass?->tahun_ajaran;

        $kelasId = $assessment->SchoolClass?->kelas_id;

        $kkm = SubjectPassingGradeCriteria::where('mapel_id', $assessment->mapel_id)->where('kelas_id', $kelasId)->where('school_year', $schoolYear)->latest()->value('kkm_value');

        $type = $assessment->SchoolAssessmentType;
        $isRemedialAllowed = $type->is_remedial_allowed ?? false;
        $maxRemedialAttempt = $type->max_remedial_attempt ?? 0;

        //  MAP DATA
        $students = $students->map(function ($item) use ($allAnswers, $allProjects, $assessmentMap, $assessment, $summaries, $kkm, $isRemedialAllowed, $maxRemedialAttempt) {
            $studentId = $item->student_id;

            $summary = $summaries[$studentId] ?? null;

            $answers = $allAnswers[$studentId] ?? collect();
            $projects = $allProjects[$studentId] ?? collect();

            $submission = ($answers->count() > 0 || $projects->count() > 0) ? 'Submit' : 'Tidak Submit';

            //  HITUNG NILAI PER ASSESSMENT
            $grouped = $answers->groupBy('school_assessment_id');

            $assessmentScores = [];

            foreach ($grouped as $assessmentIdKey => $items) {

                $totalScore = round($items->sum('question_score'), 2);

                if ($assessment->SchoolAssessmentType->AssessmentMode->code === 'project') {
                    $project = $projects->firstWhere('school_assessment_id', $assessmentIdKey);
                    if ($project) {
                        $totalScore += $project->score ?? 0;
                    }
                }

                $assessmentData = $assessmentMap[$assessmentIdKey] ?? null;

                if ($assessmentData) {
                    $assessmentScores[] = [
                        'score' => $totalScore,
                        'assessment_category' => strtolower(trim($assessmentData->assessment_category)),
                        'assessment_id' => $assessmentIdKey
                    ];
                }
            }

            $collection = collect($assessmentScores);

            $remedialScores = $collection->where('assessment_category', 'remedial')->unique('assessment_id')->sortBy('assessment_id')->values();

            //  OUTPUT
            $item->remedial_attempts = $remedialScores->map(fn($r) => [
                'score' => $r['score'],
                'assessment_id' => $r['assessment_id'],
            ])->values();

            //  SOURCE OF TRUTH = SUMMARY
            $item->main_score = $summary->main_score ?? null;
            $item->remedial_score = $summary->last_remedial_score ?? null;
            $item->susulan_score = $summary->susulan_score ?? null;
            $item->pengayaan_score = $summary->pengayaan_score ?? null;

            $item->remedial_count = $summary->remedial_count ?? 0;

            $item->score = $summary->final_score ?? 0;
            $item->score_source = $summary->score_source ?? null;

            //  LOGIC STATUS
            $latestScore = $summary->last_remedial_score ?? $summary->susulan_score ?? $summary->main_score;

            $needRemedial = $isRemedialAllowed && !is_null($latestScore) && $latestScore < $kkm && ($summary->remedial_count ?? 0) < $maxRemedialAttempt;

            $needSusulan = is_null($summary?->main_score) && is_null($summary?->susulan_score);

            $hasPengayaan = !is_null($summary?->pengayaan_score);
            $needPengayaan = ($summary->final_score ?? 0) >= $kkm && !$hasPengayaan;

            // grading status
            $gradingStatus = null;
            if ($answers->count() > 0) {
                $pending = $answers->where('grading_status', 'pending')->count();
                $gradingStatus = $pending > 0 ? 'Sementara' : 'Final';
            }

            // assign
            $item->submission_status = $submission;
            $item->grading_status = $gradingStatus;

            $item->need_remedial = $needRemedial;
            $item->need_susulan = $needSusulan;
            $item->has_pengayaan = $hasPengayaan;
            $item->need_pengayaan = $needPengayaan;

            $item->kkm = $kkm;

            return $item;
        });

        //  FILTER MODE
        if ($mode === 'remedial') {
            $students = $students->filter(fn($s) => $s->need_remedial || $s->remedial_score !== null)->values();
        } elseif ($mode === 'susulan') {
            $students = $students->filter(fn($s) => $s->need_susulan || $s->susulan_score !== null)->values();
        } elseif ($mode === 'pengayaan') {
            $students = $students->filter(fn($s) => $s->need_pengayaan || $s->pengayaan_score !== null)->values();
        }

        return response()->json([
            'data' => $students,
            'assessment' => $assessment,

            'statistics' => [
                'total_students' => $students->count(),
                'submitted' => $students->where('submission_status', 'Submit')->count(),
                'not_submitted' => $students->where('submission_status', 'Tidak Submit')->count(),
                'pending_score' => $students->where('grading_status', 'Sementara')->count(),
                'final_score' => $students->where('grading_status', 'Final')->count(),
            ],

            'global_action' => [
                'can_remedial' => $students->where('need_remedial', true)->count() > 0,
                'can_susulan' => $students->where('need_susulan', true)->count() > 0,
                'can_pengayaan' => $students->where('need_pengayaan', true)->count() > 0,
                'total_pengayaan_students' => $students->where('need_pengayaan', true)->count(),
                'total_remedial_students' => $students->where('need_remedial', true)->count(),
                'total_susulan_students' => $students->where('need_susulan', true)->count(),
            ],

            'assessmentGradingStudentAnswer' =>
                '/lms/:role/:schoolName/:schoolId/assessment-grading/:assessmentId/mode/:mode/student-list/:studentId/scoring'
        ]);
    }

    public function assessmentGradingStudentAnswer($role, $schoolName, $schoolId, $assessmentId, $mode, $studentId)
    {
        $assessment = SchoolAssessment::with(['SchoolAssessmentType.AssessmentMode', 'Mapel'])->findOrFail($assessmentId);

        $rootAssessmentId = $assessment->parent_assessment_id ?? $assessment->id;

        if ($assessment->SchoolAssessmentType->AssessmentMode->code === 'project') {
            return view('features.lms.teacher.assessment-grading.teacher-assessment-grading-student-project-detail', compact('role', 'schoolName', 'schoolId', 'assessmentId', 'studentId'));
        } else {
            return view('features.lms.teacher.assessment-grading.teacher-assessment-grading-student-answer-detail', compact('role', 'schoolName', 'schoolId', 'assessmentId', 'mode', 'studentId',
            'rootAssessmentId'));
        }
    }

    public function paginateAssessmentGradingStudentAnswer($role, $schoolName, $schoolId, $assessmentId, $mode, $studentId)
    {
        $assessment = SchoolAssessment::with(['SchoolClass', 'Mapel'])->where('id', $assessmentId)->where('assessment_category', $mode)->firstOrFail();

        // fallback ke parent jika tidak ada soal
        $realAssessmentId = $assessment->id;

        $hasQuestion = SchoolAssessmentQuestion::where('school_assessment_id', $assessment->id)->exists();

        if (!$hasQuestion && $assessment->parent_assessment_id) {
            $realAssessmentId = $assessment->parent_assessment_id;
        }

        $schoolAssessmentQuestion = SchoolAssessmentQuestion::with([
            'LmsQuestionBank.LmsQuestionOption',
            'StudentAssessmentAnswer' => function ($query) use ($studentId, $assessment) {
                $query->where('student_id', $studentId)
                    ->where('school_assessment_id', $assessment->id);
            }
        ])->where('school_assessment_id', $realAssessmentId)->get();

        if ($assessment->assessment_category === 'remedial') {

            // root id
            $rootAssessmentId = $assessment->parent_assessment_id ?? $assessment->id;

            // semua assessment dalam chain
            $allAssessments = SchoolAssessment::where(function ($q) use ($rootAssessmentId) {
                $q->where('id', $rootAssessmentId)
                ->orWhere('parent_assessment_id', $rootAssessmentId);
            })->orderBy('id')->get();

            // ambil hanya assessment sebelum yang sekarang
            $previousAssessments = $allAssessments->where('id', '<', $assessment->id);

            // ambil semua jawaban siswa
            $allAnswers = StudentAssessmentAnswer::where('student_id', $studentId)
                ->whereIn('school_assessment_id', $allAssessments->pluck('id'))
                ->where('status_answer', 'submitted')
                ->get()
                ->groupBy('school_assessment_id');

            // ambil jawaban terakhir tiap soal
            $latestAnswerPerQuestion = [];

            foreach ($previousAssessments as $prevAssessment) {

                $answers = $allAnswers[$prevAssessment->id] ?? collect();

                foreach ($answers as $ans) {
                    $qId = $ans->school_assessment_question_id;

                    // overwrite = ambil attempt terakhir
                    $latestAnswerPerQuestion[$qId] = $ans;
                }
            }

            // FILTER SOAL
            $schoolAssessmentQuestion = $schoolAssessmentQuestion->filter(function ($q) use ($latestAnswerPerQuestion) {

                $answer = $latestAnswerPerQuestion[$q->id] ?? null;

                // tidak pernah jawab → masuk
                if (!$answer) return true;

                // salah → masuk
                if ($answer->question_score <= 0) return true;

                // sudah benar → skip
                return false;

            })->values();
        }

        // STUDENT ANSWER
        $assessmentIds = [$assessment->id];

        // kalau punya parent → ambil juga
        if ($assessment->parent_assessment_id) {
            $assessmentIds[] = $assessment->parent_assessment_id;
        }

        // kalau mau lebih proper (ambil semua child juga)
        $childIds = SchoolAssessment::where('parent_assessment_id', $assessment->parent_assessment_id ?? $assessment->id)->pluck('id')->toArray();

        $assessmentIds = array_unique(array_merge($assessmentIds, $childIds));

        $questionsAnswer = StudentAssessmentAnswer::where('student_id', $studentId)
            ->whereIn('school_assessment_id', $assessmentIds)
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->school_assessment_id . '_' . $item->school_assessment_question_id => $item
                ];
            });

        $student = UserAccount::with(['studentProfile', 'StudentSchoolClass.SchoolClass'])->findOrFail($studentId);

        $students = StudentSchoolClass::where('school_class_id', $assessment->school_class_id)
            ->orderBy('student_id')
            ->get()
            ->sortBy(function ($item) {
                return strtolower($item->UserAccount->StudentProfile->nama_lengkap ?? '');
            })
            ->pluck('student_id')
            ->values();

        $currentIndex = $students->search($studentId);

        $previousStudent = $students[$currentIndex - 1] ?? null;
        $nextStudent = $students[$currentIndex + 1] ?? null;

        return response()->json([
            'data' => $schoolAssessmentQuestion,
            'questionsAnswer' => $questionsAnswer,
            'assessment' => $assessment,
            'student' => $student,
            'previousStudent' => $previousStudent,
            'nextStudent' => $nextStudent
        ]);
    }

    public function submitAssessmentStudentScore(Request $request, $role, $schoolName, $schoolId, $assessmentId, $studentId, $schoolAssessmentQuestionId)
    {
        $validator = Validator::make(request()->all(), [
            'question_score' => 'required',
        ], [
            'question_score.required' => 'Nilai harus diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $schoolAssessmentQuestion = SchoolAssessmentQuestion::findOrFail($schoolAssessmentQuestionId);

        if ($request->question_score < 0) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'question_score' => ['Nilai tidak dapat kurang dari 0.']
                ],
            ], 422);
        } else if ($request->question_score > $schoolAssessmentQuestion->question_weight) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'question_score' => ['Nilai tidak dapat melebihi nilai bobot soal.']
                ],
            ], 422);
        }

        // REMEDIAL LOGIC
        $assessment = SchoolAssessment::findOrFail($assessmentId);

        $assessmentCategory = strtolower($assessment->assessment_category ?? '');
        $parentAssessmentId = $assessment->parent_assessment_id;

        $finalQuestionId = $schoolAssessmentQuestionId;

        if (in_array($assessmentCategory, ['remedial', 'susulan']) && $parentAssessmentId) {

            $parentQuestion = SchoolAssessmentQuestion::find($schoolAssessmentQuestionId);

            if ($parentQuestion) {

                $childQuestion = SchoolAssessmentQuestion::where('school_assessment_id', $assessmentId)
                    ->where('lms_question_bank_id', $parentQuestion->lms_question_bank_id)
                    ->first();

                if ($childQuestion) {
                    $finalQuestionId = $childQuestion->id;
                }
            }
        }

        $studentAssessmentAnswer = StudentAssessmentAnswer::where('school_assessment_question_id', $finalQuestionId)->where('school_assessment_id', $assessmentId)
        ->where('student_id', $studentId)->first();

        if (!$studentAssessmentAnswer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jawaban siswa tidak ditemukan.'
            ], 404);
        }

        $studentAssessmentAnswer->update([
            'question_score' => $request->question_score,
            'grading_status' => 'graded',
            'teacher_feedback' => $request->teacher_feedback,
        ]);

        $this->summaryService->updateStudentAssessmentSummary($studentId, $assessment);

        return response()->json([
            'status' => 'success',
            'message' => 'Nilai berhasil disimpan.',
        ], 200);
    }

    public function paginateAssessmentGradingStudentProject($role, $schoolName, $schoolId, $assessmentId, $studentId)
    {
        $assessment = SchoolAssessment::with(['SchoolClass','Mapel'])->findOrFail($assessmentId);

        $submission = StudentProjectSubmission::where('school_assessment_id', $assessmentId)->where('student_id', $studentId)->first();

        $student = UserAccount::with(['studentProfile', 'StudentSchoolClass.SchoolClass'])->findOrFail($studentId);

        $students = StudentSchoolClass::where('school_class_id',$assessment->school_class_id)->orderBy('student_id')->pluck('student_id')->values();

        $currentIndex = $students->search($studentId);

        $previousStudent = $students[$currentIndex - 1] ?? null;
        $nextStudent = $students[$currentIndex + 1] ?? null;

        return response()->json([
            'assessment' => $assessment,
            'student' => $student,
            'submission' => $submission,
            'previousStudent' => $previousStudent,
            'nextStudent' => $nextStudent
        ]);
    }

    public function submitAssessmentStudentProjectScore(Request $request, $role, $schoolName, $schoolId, $assessmentId, $studentId, $submissionId)
    {
        $validator = Validator::make(request()->all(), [
            'score' => 'required',
        ], [
            'score.required' => 'Nilai harus diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $submission = StudentProjectSubmission::findOrFail($submissionId);

        if ($request->score < 0) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'score' => ['Nilai tidak dapat kurang dari 0.']
                ],
            ], 422);
        } else if ($request->score > 100) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'score' => ['Nilai tidak dapat lebih dari 100.']
                ],
            ], 422);
        }

        $submission->update([
            'score' => $request->score,
            'grading_status' => 'graded',
            'teacher_feedback' => $request->teacher_feedback,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Nilai berhasil disimpan.',
        ], 200);
    }
}

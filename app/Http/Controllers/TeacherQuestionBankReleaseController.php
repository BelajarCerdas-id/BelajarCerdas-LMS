<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Kurikulum;
use App\Models\LmsQuestionBank;
use App\Models\SchoolAssessment;
use App\Models\SchoolAssessmentQuestion;
use App\Models\SchoolAssessmentType;
use App\Models\SchoolPartner;
use App\Models\StudentAssessmentAnswer;
use App\Models\TeacherMapel;
use App\Services\ClassName\ClassNameService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TeacherQuestionBankReleaseController extends Controller
{
    private function extractClassLevel($className)
    {
        $classNameService = new ClassNameService();
        return $classNameService->extractClassLevel($className);
    }
    private function resolveClassLevel($class): ?int
    {
        $classNameService = new ClassNameService();
        return $classNameService->resolveClassLevel($class);
    }
    
    public function teacherQuestionBankForRelease($role, $schoolName, $schoolId)
    {
        $schoolAssessmentType = SchoolAssessmentType::where('school_partner_id', $schoolId)->get();

        $getCurriculum = Kurikulum::all();

        return view('features.lms.teacher.question-bank-for-release.teacher-question-bank-for-release', compact('role', 'schoolName', 
            'schoolId', 'schoolAssessmentType', 'getCurriculum'));
    }

    public function teacherFormQuestionBankForRelease(Request $request, $role, $schoolName, $schoolId)
    {
        $user = Auth::user();
        $schoolPartner = SchoolPartner::findOrFail($schoolId);
        $jenjang = strtoupper(trim($schoolPartner->jenjang_sekolah));

        $startLevelMap = [
            'SD' => 1,
            'MI' => 1,
            'SMP' => 7,
            'MTS' => 7,
            'SMA' => 10,
            'SMK' => 10,
            'MA' => 10,
            'MAK' => 10,
        ];

        $defaultLevel = $startLevelMap[$jenjang] ?? 1;

        $teacherMapels = TeacherMapel::where('user_id', $user->id)->where('is_active', true)->whereHas('SchoolClass', function ($query) use ($schoolId) {
            $query->where('school_partner_id', $schoolId);
        })->with(['SchoolClass', 'Mapel'])->get();

        $allSchoolAssessments = SchoolAssessment::with(['Mapel', 'SchoolClass', 'SchoolAssessmentType'])->whereNotIn('assessment_category', ['remedial', 'susulan'])
        ->whereHas('SchoolAssessmentType.AssessmentMode', function ($query) {
            $query->whereNot('code', 'project');
        })->where('user_id', $user->id)->where('school_partner_id', $schoolId)->orderByDesc('created_at')->get();

        $tahunAjaran = $teacherMapels->toBase()->pluck('SchoolClass.tahun_ajaran')
            ->concat($allSchoolAssessments->toBase()->pluck('SchoolClass.tahun_ajaran'))
            ->filter()->unique()->sortDesc()->values();

        $searchYear = $request->filled('search_year') ? $request->search_year : ($tahunAjaran->first() ?? null);

        $teacherClasses = $teacherMapels->filter(function ($teacherMapel) use ($searchYear) {
            $schoolClass = $teacherMapel->SchoolClass;
            return $schoolClass && (!$searchYear || $schoolClass->tahun_ajaran === $searchYear);
        })->values();

        $assessmentClassLevels = $allSchoolAssessments->toBase()->filter(function ($assessment) use ($searchYear) {
            return optional($assessment->SchoolClass)->tahun_ajaran === $searchYear;
        })->map(function ($assessment) {
            return (int) $this->extractClassLevel(
                optional($assessment->SchoolClass)->class_name
            );
        })->filter()->unique();

        $teacherClassLevels = $teacherMapels->toBase()->filter(function ($teacherMapel) use ($searchYear) {
            $schoolClass = $teacherMapel->SchoolClass;
            return $schoolClass && (!$searchYear || $schoolClass->tahun_ajaran === $searchYear);
        })->map(function ($teacherMapel) {
            return (int) $this->extractClassLevel(
                optional($teacherMapel->SchoolClass)->class_name
            );
        })->filter()->unique();

        $classLevels = $assessmentClassLevels->concat($teacherClassLevels)->unique()->sort()->values();

        $selectedClass = $request->filled('search_class') ? $this->resolveClassLevel($request->search_class) : ($classLevels->first() ?? $defaultLevel);

        $allAssessmentIds = $allSchoolAssessments->pluck('id')->filter()->map(fn($id) => (int) $id)->values();

        $assessmentAnswerStatus = StudentAssessmentAnswer::whereIn('school_assessment_id', $allAssessmentIds)->select('school_assessment_id')
        ->distinct()->pluck('school_assessment_id')->map(fn($id) => (int) $id)->flip();

        $allAssignments = SchoolAssessmentQuestion::whereIn('school_assessment_id', $allAssessmentIds)->get(['school_assessment_id', 'question_bank_id', 'question_weight'])
        ->groupBy('school_assessment_id');

        $assignedQuestionIds = $allAssignments->flatten()->pluck('question_bank_id')->filter()->map(fn($id) => (int) $id)->unique()->values();

        $assignedQuestionDetails = collect();

        if ($assignedQuestionIds->isNotEmpty()) {
            $assignedQuestionDetails = LmsQuestionBank::with(['UserAccount', 'UserAccount.OfficeProfile', 'UserAccount.SchoolStaffProfile', 'Kurikulum', 
                'Kelas', 'Mapel', 'Bab', 'SubBab', 'SchoolPartner', 'LmsQuestionOption', 'SchoolQuestionBank' => function ($query) use ($schoolId) {
                    $query->where('school_partner_id', $schoolId)->where('is_active', true);
                },
            ])->whereIn('id', $assignedQuestionIds)->get()->map(function ($question) {
                $question->question_source_name = $question->school_partner_id ? (
                    optional($question->SchoolPartner)->name ?? optional($question->SchoolPartner)->school_name ?? optional($question->SchoolPartner)->nama_sekolah ??
                    'Sekolah'
                ) : 'BelajarCerdas.id';

                return $question;
            })
            ->keyBy(fn($question) => (string) $question->id);
        }

        $schoolAssessment = $allSchoolAssessments->filter(function ($assessment) use ($searchYear, $selectedClass) {
            $schoolClass = $assessment->SchoolClass;

            if (!$schoolClass) {
                return false;
            }

            if ($searchYear && $schoolClass->tahun_ajaran !== $searchYear) {
                return false;
            }

            return (int) $this->extractClassLevel($schoolClass->class_name) === (int) $selectedClass;
        })->values();

        if ($request->filled('search_assessment_type')) {
            $schoolAssessment = $schoolAssessment->filter(function ($assessment) use ($request) {
                return (int) optional($assessment->SchoolAssessmentType)->id === (int) $request->search_assessment_type;
            })->values();
        }

        if ($request->filled('search_subject')) {
            $schoolAssessment = $schoolAssessment->filter(function ($assessment) use ($request) {
                return (int) $assessment->mapel_id === (int) $request->search_subject;
            })->values();
        }

        if ($request->filled('search_semester')) {
            $schoolAssessment = $schoolAssessment->filter(function ($assessment) use ($request) {
                return (string) $assessment->semester === (string) $request->search_semester;
            })->values();
        }

        $selectedAssessmentId = $request->input('school_assessment_id') ?? $request->input('assessment_id') ?? $request->input('selected_assessment_id');

        $selectedAssessment = null;
        $selectedAssessmentHasAnswers = false;

        if ($selectedAssessmentId) {
            $selectedAssessment = $allSchoolAssessments->firstWhere('id', (int) $selectedAssessmentId);

            if ($selectedAssessment) {
                $selectedAssessmentHasAnswers = $assessmentAnswerStatus->has(
                    (int) $selectedAssessment->id
                );
            }
        }

        $subjects = $teacherClasses->toBase()->filter(function ($teacherMapel) use ($selectedClass) {
            $schoolClass = $teacherMapel->SchoolClass;

            if (!$schoolClass) {
                return false;
            }

            return (int) $this->extractClassLevel($schoolClass->class_name) === (int) $selectedClass;
        })->filter(fn($item) => !is_null($item->mapel_id))->unique('mapel_id')->map(function ($item) {
            return [
                'id' => $item->mapel_id,
                'name' => optional($item->Mapel)->mata_pelajaran ?? '-',
            ];
        })->values();

        $assessmentSubjects = $schoolAssessment->toBase()->filter(fn($item) => !is_null($item->mapel_id))->unique('mapel_id')->map(function ($item) {
            return [
                'id' => $item->mapel_id,
                'name' => optional($item->Mapel)->mata_pelajaran ?? '-',
            ];
        })->values();

        $subjects = $subjects->concat($assessmentSubjects)->unique('id')->values();

        $schoolAssessmentType = SchoolAssessmentType::where('school_partner_id', $schoolId)->get();

        $schoolAssessment = $schoolAssessment->map(function ($assessment) use ($allAssignments, $assignedQuestionDetails, $assessmentAnswerStatus) {
            $assessment->has_student_answers = $assessmentAnswerStatus->has(
                (int) $assessment->id
            );
    
            $assignments = $allAssignments->get($assessment->id, collect());
    
            $assessment->assigned_question_count = $assignments->count();
    
            $assessment->assigned_total_weight = round($assignments->sum(
                fn($item) => (float) $item->question_weight
            ), 2);
    
            $assessment->assigned_questions = $assignments->map(function ($item) use ($assignedQuestionDetails) {
                $questionId = (string) $item->question_bank_id;
    
                return [
                    'question_id' => (int) $item->question_bank_id,
                    'weight' => (float) $item->question_weight,
                    'question' => $assignedQuestionDetails->get($questionId),
                ];
            })->values()->all();
    
            return $assessment;
        })->values();

        if ($selectedAssessment) {
            $selectedAssessment->has_student_answers = $assessmentAnswerStatus->has((int) $selectedAssessment->id);

            $assignments = $allAssignments->get($selectedAssessment->id, collect());

            $selectedAssessment->assigned_question_count = $assignments->count();

            $selectedAssessment->assigned_total_weight = round($assignments->sum(fn($item) => (float) $item->question_weight), 2);

            $selectedAssessment->assigned_questions = $assignments->map(function ($item) use ($assignedQuestionDetails) {
                $questionId = (string) $item->question_bank_id;

                return [
                    'question_id' => (int) $item->question_bank_id,
                    'weight' => (float) $item->question_weight,
                    'question' => $assignedQuestionDetails->get($questionId),
                ];
            })->values()->all();
        }

        $selectedAssessmentMapelId = $selectedAssessment ? $selectedAssessment->mapel_id: null;

        if ($request->filled('mapel_id')) {
            $mapelIds = collect([(int) $request->mapel_id]);
        } elseif ($selectedAssessmentMapelId) {
            $mapelIds = collect([(int) $selectedAssessmentMapelId]);
        } else {
            $teacherMapelIds = $teacherMapels->toBase()->filter(function ($teacherMapel) use ($searchYear) {
                $schoolClass = $teacherMapel->SchoolClass;

                return $schoolClass && (!$searchYear || $schoolClass->tahun_ajaran === $searchYear);
            })->pluck('mapel_id')->filter()->map(fn($id) => (int) $id)->unique()->values();

            $assessmentMapelIds = $allSchoolAssessments->toBase()->filter(function ($assessment) use ($searchYear) {
                $schoolClass = $assessment->SchoolClass;

                return $schoolClass && (!$searchYear || $schoolClass->tahun_ajaran === $searchYear);
            })->pluck('mapel_id')->filter()->map(fn($id) => (int) $id)->unique()->values();

            $mapelIds = $teacherMapelIds->concat($assessmentMapelIds)->unique()->values();
        }

        $questionQuery = LmsQuestionBank::with(['UserAccount', 'UserAccount.OfficeProfile', 'UserAccount.SchoolStaffProfile', 'Kurikulum', 'Kelas', 'Mapel', 'Bab', 
            'SubBab', 'SchoolPartner', 'LmsQuestionOption', 'SchoolQuestionBank' => function ($query) use ($schoolId) {
                $query->where('school_partner_id', $schoolId)->where('is_active', true);
            },
        ])->where(function ($query) use ($schoolId) {
            $query->where('school_partner_id', $schoolId)->orWhereNull('school_partner_id');
        })->when($mapelIds->isNotEmpty(), function ($query) use ($mapelIds) {
            $query->whereIn('mapel_id', $mapelIds);
        })->orderByDesc('created_at');

        if ($request->filled('search_question')) {
            $questionQuery->where('questions', 'LIKE', '%' . $request->search_question . '%');
        }

        if ($request->filled('kurikulum_id')) {
            $questionQuery->where('kurikulum_id', $request->kurikulum_id);
        }

        if ($request->filled('kelas_id')) {
            $questionQuery->where('kelas_id', $request->kelas_id);
        }

        if ($request->filled('mapel_id')) {
            $questionQuery->where('mapel_id', $request->mapel_id);
        }

        if ($request->filled('bab_id')) {
            $questionQuery->where('bab_id', $request->bab_id);
        }

        if ($request->filled('sub_bab_id')) {
            $questionQuery->where('sub_bab_id', $request->sub_bab_id);
        }

        $getQuestions = $questionQuery->get()->map(function ($question) {
                $question->question_source_name = $question->school_partner_id ? (optional($question->SchoolPartner)->name ?? 
                optional($question->SchoolPartner)->school_name ?? optional($question->SchoolPartner)->nama_sekolah ?? 'Sekolah'
            ) : 'BelajarCerdas.id';

            return $question;
        })->values();

        return response()->json([
            'data' => $schoolAssessment->values(),
            'tahunAjaran' => $tahunAjaran,
            'selectedYear' => $searchYear,
            'selectedClass' => $selectedClass,
            'className' => $classLevels,
            'subject' => $subjects,
            'schoolAssessmentType' => $schoolAssessmentType,
            'questionBank' => $getQuestions,
            'selectedAssessment' => $selectedAssessment,
            'selectedAssessmentHasAnswers' => $selectedAssessmentHasAnswers,
        ]);
    }

    public function teacherQuestionBankForReleaseStore(Request $request, $role, $schoolName, $schoolId)
    {
        $validator = Validator::make($request->all(), [
            'school_assessment_id' => [
                'required',
                'integer',
                'exists:school_assessments,id',
            ],
            'question_id' => [
                'required',
                'array',
                'min:1',
            ],
            'question_id.*' => [
                'required',
                'integer',
                'distinct',
                'exists:lms_question_banks,id',
            ],
            'question_weight' => [
                'required',
                'array',
            ],
            'question_weight.*' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],
            'total_weight' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],
        ], [
            'school_assessment_id.required' => 'Harap pilih asesmen.',
            'school_assessment_id.integer' => 'Asesmen tidak valid.',
            'school_assessment_id.exists' => 'Asesmen tidak ditemukan.',
            'question_id.required' => 'Harap pilih setidaknya 1 soal.',
            'question_id.min' => 'Harap pilih setidaknya 1 soal.',
            'question_id.*.required' => 'Soal tidak valid.',
            'question_id.*.integer' => 'ID soal tidak valid.',
            'question_id.*.distinct' => 'Terdapat soal yang dipilih lebih dari satu kali.',
            'question_id.*.exists' => 'Salah satu soal tidak ditemukan.',
            'question_weight.required' => 'Bobot soal wajib diisi.',
            'question_weight.*.required' => 'Bobot soal wajib diisi.',
            'question_weight.*.numeric' => 'Bobot soal harus berupa angka.',
            'question_weight.*.min' => 'Bobot soal tidak boleh kurang dari 0.',
            'question_weight.*.max' => 'Bobot soal tidak boleh lebih dari 100.',
            'total_weight.required' => 'Total bobot wajib diisi.',
            'total_weight.numeric' => 'Total bobot harus berupa angka.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $assessment = SchoolAssessment::with(['SchoolClass', 'Mapel'])->where('id', $request->school_assessment_id)->where('user_id', Auth::id())
        ->where('school_partner_id', $schoolId)->first();

        if (!$assessment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Asesmen tidak ditemukan atau tidak dapat diakses.',
            ], 403);
        }

        $questionIds = array_values(array_unique(array_map('intval', $request->question_id)));

        $weights = collect($request->question_weight)->mapWithKeys(function ($weight, $questionId) {
            return [(int) $questionId => round((float) $weight, 2)];
        });

        foreach ($questionIds as $questionId) {
            if (!$weights->has($questionId)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bobot untuk salah satu soal belum tersedia.',
                    'errors' => [
                        'question_weight' => [
                            "Bobot soal dengan ID {$questionId} belum tersedia."
                        ],
                    ],
                ], 422);
            }
        }

        $totalWeight = round(collect($questionIds)->sum(fn($questionId) => $weights->get($questionId)), 2);

        if (abs($totalWeight - 100) > 0.01) {
            return response()->json([
                'status' => 'error',
                'message' => 'Total bobot soal harus tepat 100%.',
                'errors' => [
                    'total_weight' => [
                        "Total bobot saat ini {$totalWeight}%. Total bobot harus tepat 100%."
                    ],
                ],
            ], 422);
        }

        $questions = LmsQuestionBank::with(['Kelas', 'Mapel'])->whereIn('id', $questionIds)->get();

        if ($questions->count() !== count($questionIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terdapat soal yang tidak ditemukan.',
            ], 422);
        }

        $assessmentClassLevel = (int) $this->extractClassLevel(
            optional($assessment->SchoolClass)->class_name
        );

        foreach ($questions as $question) {
            $questionClassLevel = (int) $this->extractClassLevel(
                optional($question->Kelas)->kelas
            );

            if ((int) $question->mapel_id !== (int) $assessment->mapel_id || $questionClassLevel !== $assessmentClassLevel) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terdapat soal yang tidak sesuai dengan kelas atau mata pelajaran asesmen.',
                ], 422);
            }

            if ($question->school_partner_id !== null && (int) $question->school_partner_id !== (int) $schoolId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terdapat soal yang tidak berasal dari sekolah yang sesuai.',
                ], 422);
            }
        }

        DB::beginTransaction();

        try {
            SchoolAssessmentQuestion::where('school_assessment_id', $assessment->id)->whereNotIn('question_bank_id', $questionIds)->delete();

            foreach ($questionIds as $questionId) {
                SchoolAssessmentQuestion::updateOrCreate(
                    [
                        'school_assessment_id' => $assessment->id,
                        'question_bank_id' => $questionId,
                    ],
                    [
                        'question_weight' => $weights->get($questionId),
                    ]
                );
            }

            DB::commit();

            $savedQuestions = SchoolAssessmentQuestion::where('school_assessment_id', $assessment->id)->get(['question_bank_id', 'question_weight'])->map(function ($item) {
                return [
                    'question_id' => (int) $item->question_bank_id,
                    'weight' => (float) $item->question_weight,
                ];
            })->values();

            return response()->json([
                'status' => 'success',
                'message' => 'Soal berhasil dirilis ke asesmen.',
                'data' => [
                    'school_assessment_id' => (int) $assessment->id,
                    'question_count' => $savedQuestions->count(),
                    'total_weight' => round($savedQuestions->sum('weight'), 2),
                    'assigned_questions' => $savedQuestions,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menyimpan soal.',
            ], 500);
        }
    }

    public function paginateTeacherQuestionBankForRelease(Request $request, $role, $schoolName, $schoolId)
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

        $query = SchoolAssessmentQuestion::with(['SchoolAssessment', 'SchoolAssessment.SchoolClass', 'SchoolAssessment.Mapel', 'SchoolAssessment.SchoolAssessmentType'
        ])->whereHas('SchoolAssessment', function ($query) use ($user, $schoolId) {
            $query->where('user_id', $user->id)->where('school_partner_id', $schoolId);
        })->orderBy('created_at', 'desc')->get();

        // TAHUN AJARAN
        $tahunAjaran = $query->pluck('SchoolAssessment.SchoolClass.tahun_ajaran')->filter()->unique()->sortDesc()->values();

        $searchYear = $request->filled('search_year') ? $request->search_year : ($tahunAjaran->first() ?? null);

        // FILTER BERDASARKAN TAHUN AJARAN
        $schoolClasses = $query->filter(function ($item) use ($searchYear) {
            return $item->SchoolAssessment?->SchoolClass?->tahun_ajaran === $searchYear;
        })->values();
        
        // LEVEL KELAS UNIK
        $classLevels = $schoolClasses->pluck('SchoolAssessment.SchoolClass.class_name')->map(fn($c) => (int) $this->extractClassLevel($c))->filter()->unique()->sort()->values();

        $selectedClass = $request->filled('search_class') ? $this->resolveClassLevel($request->search_class) : ($classLevels->first() ?? $defaultLevel);

        // FILTER ROMBEL SESUAI LEVEL
        $schoolClasses = $schoolClasses->filter(fn($item) => (int)$this->extractClassLevel($item->SchoolAssessment?->SchoolClass?->class_name) === (int)$selectedClass)->values();

        // Filter berdasarkan level kelas
        if ($selectedClass) {
            $query = $query->filter(function ($item) use ($selectedClass) {

                if (!$item?->SchoolAssessment?->SchoolClass?->class_name) {
                    return false;
                }

                return (int)$this->extractClassLevel($item->SchoolAssessment->SchoolClass->class_name) === (int)$selectedClass;
            });
        }

        $schoolAssessmentType = SchoolAssessmentType::where('school_partner_id', $schoolId)->get();

        // FILTER SEARCH ASSESSMENT TYPE
        if ($request->filled('search_assessment_type')) {
            $query = $query->filter(function ($item) use ($request) {
                return (int)optional($item->SchoolAssessment?->SchoolAssessmentType)->id === (int)$request->search_assessment_type;
            })->values();
        }

        // GROUP BY school_assessment_id
        $schoolAssessmentQuestion = $query->groupBy('school_assessment_id');

        // manual pagination karena sudah menjadi collection
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;

        $paginated = new LengthAwarePaginator(
            $schoolAssessmentQuestion->forPage($currentPage, $perPage)->values(),
            $schoolAssessmentQuestion->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url()]
        );

        return response()->json([
            'data' => $paginated->items(),
            'links' => (string) $paginated->links(),
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
            'tahunAjaran'   => $tahunAjaran,
            'selectedYear'  => $searchYear,
            'selectedClass' => $selectedClass,
            'className'     => $classLevels,
            'schoolAssessmentType' => $schoolAssessmentType,
            'teacherReviewQuestionBankForRelease' => '/lms/:role/:schoolName/:schoolId/teacher-question-bank-for-release/review/:assessmentQuestionId'
        ]);
    }

    // function teacher review question bank for release
    public function teacherReviewQuestionBankForRelease(Request $request, $role, $schoolName, $schoolId, $assessmentQuestionId)
    {
        return view('features.lms.teacher.question-bank-for-release.teacher-review-question-bank-for-release', compact('role', 'schoolName', 'schoolId', 
            'assessmentQuestionId'));
    }

    public function paginateTeacherReviewQuestionBankForRelease(Request $request, $role, $schoolName, $schoolId, $assessmentQuestionId)
    {
        $user = Auth::user();

        $questions = SchoolAssessmentQuestion::with(['LmsQuestionBank', 'LmsQuestionBank.LmsQuestionOption'])->where('school_assessment_id', $assessmentQuestionId)->get();

        $videoIds = $questions->map(function ($q) {
            if (preg_match(
                '/youtu\.be\/([a-zA-Z0-9_-]{11})|youtube\.com\/.*v=([a-zA-Z0-9_-]{11})/',
                $q->explanation,
                $matches
            )) {
                return $matches[1] ?? $matches[2];
            }
            return null;
        });

        $response = [
            'data' => $questions,
            'videoIds' => $videoIds,
        ];

        return response()->json($response);
    }
}

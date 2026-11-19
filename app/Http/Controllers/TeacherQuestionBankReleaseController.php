<?php

namespace App\Http\Controllers;

use App\Models\Kurikulum;
use App\Models\LmsQuestionBank;
use App\Models\SchoolAssessment;
use App\Models\SchoolAssessmentComposition;
use App\Models\SchoolAssessmentQuestion;
use App\Models\SchoolAssessmentType;
use App\Models\SchoolPartner;
use App\Models\StudentAssessmentAnswer;
use App\Models\StudentAssessmentComposition;
use App\Models\StudentSchoolClass;
use App\Models\TeacherMapel;
use App\Services\ClassName\ClassNameService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TeacherQuestionBankReleaseController extends Controller
{
    
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

        $allSchoolAssessments = SchoolAssessment::with(['Mapel', 'SchoolClass', 'SchoolAssessmentType', 'SchoolAssessmentComposition'])
        ->whereNotIn('assessment_category', ['remedial', 'susulan'])->whereHas('SchoolAssessmentType.AssessmentMode', function ($query) {
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
            return (int) app(ClassNameService::class)->extractClassLevel(
                optional($assessment->SchoolClass)->class_name
            );
        })->filter()->unique();

        $teacherClassLevels = $teacherMapels->toBase()->filter(function ($teacherMapel) use ($searchYear) {
            $schoolClass = $teacherMapel->SchoolClass;
            return $schoolClass && (!$searchYear || $schoolClass->tahun_ajaran === $searchYear);
        })->map(function ($teacherMapel) {
            return (int) app(ClassNameService::class)->extractClassLevel(
                optional($teacherMapel->SchoolClass)->class_name
            );
        })->filter()->unique();

        $classLevels = $assessmentClassLevels->concat($teacherClassLevels)->unique()->sort()->values();

        $selectedClass = $request->filled('search_class') ? app(ClassNameService::class)->resolveClassLevel($request->search_class) : ($classLevels->first() ?? $defaultLevel);

        $allAssessmentIds = $allSchoolAssessments->pluck('id')->filter()->map(fn($id) => (int) $id)->values();

        $assessmentAnswerStatus = StudentAssessmentAnswer::whereIn('school_assessment_id', $allAssessmentIds)->select('school_assessment_id')
        ->distinct()->pluck('school_assessment_id')->map(fn($id) => (int) $id)->flip();

        // Ambil ID soal yang sudah pernah dijawab siswa.
        $answeredAssessmentQuestionIds = StudentAssessmentAnswer::whereIn('school_assessment_id', $allAssessmentIds)->whereNotNull('school_assessment_question_id')
        ->distinct()->pluck('school_assessment_question_id')->map(fn($id) => (int) $id)->flip();

        $allAssignments = SchoolAssessmentQuestion::whereIn('school_assessment_id', $allAssessmentIds)->get(['id', 'school_assessment_id', 'sac_id', 'question_bank_id', 
        'question_weight'])->groupBy('school_assessment_id');

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

        $buildAssessmentAssignmentData = function ($assessment) use ($allAssignments, $assignedQuestionDetails, $assessmentAnswerStatus, $answeredAssessmentQuestionIds) {
            $assignments = $allAssignments->get($assessment->id, collect());

            $formatAssignedQuestions = function ($items) use ($assignedQuestionDetails, $answeredAssessmentQuestionIds) {
                return $items->map(function ($item) use ($assignedQuestionDetails, $answeredAssessmentQuestionIds) {
                    $questionId = (string) $item->question_bank_id;

                    return [
                        'assignment_id' => (int) $item->id,
                        'question_id' => (int) $item->question_bank_id,
                        'weight' => (float) $item->question_weight,
                        'has_answers' => $answeredAssessmentQuestionIds->has((int) $item->id),
                        'question' => $assignedQuestionDetails->get($questionId),
                    ];
                })->values()->all();
            };

            // Soal lama yang belum memiliki komposisi.
            $legacyAssignments = $assignments->filter(function ($item) {
                return is_null($item->sac_id);
            })->values();

            // Data setiap komposisi.
            $compositions = $assessment->SchoolAssessmentComposition
                ->map(function ($composition) use ($assignments, $formatAssignedQuestions) {
                    $compositionAssignments = $assignments
                        ->where('sac_id', $composition->id)
                        ->values();

                    return [
                        'id' => (int) $composition->id,
                        'composition_name' => $composition->composition_name,
                        'assigned_question_count' => $compositionAssignments->count(),
                        'assigned_total_weight' => round($compositionAssignments->sum(fn($item) => (float) $item->question_weight), 2),
                        'assigned_questions' => $formatAssignedQuestions($compositionAssignments),
                    ];
                })->values();

            $assessment->has_student_answers = $assessmentAnswerStatus->has(
                (int) $assessment->id
            );

            // Total seluruh soal, termasuk soal lama dan seluruh komposisi.
            $assessment->assigned_question_count = $assignments->count();

            $assessment->assigned_total_weight = round($assignments->sum(fn($item) => (float) $item->question_weight), 2);

            $assessment->assigned_questions = $formatAssignedQuestions($assignments);

            // Komposisi baru.
            $assessment->compositions = $compositions;

            // Soal lama yang belum dikelompokkan ke komposisi.
            $assessment->legacy_assigned_question_count = $legacyAssignments->count();

            $assessment->legacy_assigned_total_weight = round($legacyAssignments->sum(fn($item) => (float) $item->question_weight), 2);

            $assessment->legacy_assigned_questions = $formatAssignedQuestions($legacyAssignments);

            return $assessment;
        };

        $schoolAssessment = $allSchoolAssessments->filter(function ($assessment) use ($searchYear, $selectedClass) {
            $schoolClass = $assessment->SchoolClass;

            if (!$schoolClass) {
                return false;
            }

            if ($searchYear && $schoolClass->tahun_ajaran !== $searchYear) {
                return false;
            }

            return (int) app(ClassNameService::class)->extractClassLevel($schoolClass->class_name) === (int) $selectedClass;
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

            return (int) app(ClassNameService::class)->extractClassLevel($schoolClass->class_name) === (int) $selectedClass;
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

        $schoolAssessment = $schoolAssessment->map($buildAssessmentAssignmentData)->values();

        if ($selectedAssessment) {
            $selectedAssessment = $buildAssessmentAssignmentData($selectedAssessment);
        }

        $students = collect();

        if ($selectedAssessment) {
            $students = StudentSchoolClass::with(['UserAccount.StudentProfile', 'SchoolClass'])->where('school_class_id', $selectedAssessment->school_class_id)
            ->whereHas('SchoolClass', function ($query) use ($schoolId) {
                $query->where('school_partner_id', $schoolId);

            })->get()->filter(function ($studentSchoolClass) {
                return $studentSchoolClass->UserAccount !== null;

            })->map(function ($studentSchoolClass) {
                $user = $studentSchoolClass->UserAccount;
                $profile = $user->StudentProfile;

                return [
                    'id' => (int) $user->id, 
                    'name' => $profile->nama_lengkap ?? 'Nama tidak tersedia',
                    'class_name' => optional($studentSchoolClass->SchoolClass)->class_name ?? '-',
                    'student_class_status' => $studentSchoolClass->student_class_status,
                ];
            })->where('student_class_status', 'active')->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();
        }

        $studentCompositionAssignments = collect();

        if ($selectedAssessment) {

            $studentCompositionAssignments = StudentAssessmentComposition::where(
                'school_assessment_id',
                $selectedAssessment->id
            )->get(['sac_id', 'user_id'])->groupBy('sac_id')->map(function ($items) {
                return $items->pluck('user_id')->map(fn($id) => (int) $id)->values();
            });
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
        })->orderByDesc('created_at')->orderBy('id', 'asc');

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
            'students' => $students,
            'studentCompositionAssignments' => $studentCompositionAssignments,
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
                'min:1',
                'max:100',
            ],
            'total_weight' => [
                'required',
                'numeric',
                'min:1',
                'max:100',
            ],
            'composition_id' => [
                'nullable',
                'integer',
                'required_if:is_legacy,0',
                'exists:school_assessment_compositions,id',
            ],
            'is_legacy' => [
                'required',
                'boolean',
            ],
            'student_ids' => [
                'required',
                'array',
                'min:1',
            ],
            'student_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:user_accounts,id',
            ],
        ], [
            'school_assessment_id.required' => 'Harap pilih assessment.',
            'school_assessment_id.integer' => 'Assessment tidak valid.',
            'school_assessment_id.exists' => 'Assessment tidak ditemukan.',

            'question_id.required' => 'Harap pilih setidaknya 1 soal.',
            'question_id.min' => 'Harap pilih setidaknya 1 soal.',
            'question_id.*.required' => 'Soal tidak valid.',
            'question_id.*.integer' => 'ID soal tidak valid.',
            'question_id.*.distinct' => 'Terdapat soal yang dipilih lebih dari satu kali.',
            'question_id.*.exists' => 'Salah satu soal tidak ditemukan.',

            'question_weight.required' => 'Bobot soal wajib diisi.',
            'question_weight.*.required' => 'Bobot soal wajib diisi.',
            'question_weight.*.numeric' => 'Bobot soal harus berupa angka.',
            'question_weight.*.min' => 'Bobot soal tidak boleh kurang dari 1.',
            'question_weight.*.max' => 'Bobot soal tidak boleh lebih dari 100.',

            'total_weight.required' => 'Total bobot wajib diisi.',
            'total_weight.numeric' => 'Total bobot harus berupa angka.',
            'total_weight.min' => 'Total bobot minimal adalah 1.',
            'total_weight.max' => 'Total bobot tidak boleh lebih dari 100.',

            'composition_id.required_if' => 'Komposisi soal wajib dipilih.',
            'composition_id.integer' => 'ID komposisi tidak valid.',
            'composition_id.exists' => 'Komposisi soal tidak ditemukan.',

            'is_legacy.required' => 'Jenis komposisi wajib ditentukan.',
            'is_legacy.boolean' => 'Jenis komposisi tidak valid.',

            'student_ids.required' => 'Harap pilih setidaknya satu siswa.',
            'student_ids.min' => 'Harap pilih setidaknya satu siswa.',
            'student_ids.*.required' => 'Data siswa tidak valid.',
            'student_ids.*.integer' => 'ID siswa tidak valid.',
            'student_ids.*.distinct' => 'Terdapat siswa yang dipilih lebih dari satu kali.',
            'student_ids.*.exists' => 'Salah satu siswa tidak ditemukan.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Cari assessment dan pastikan dimiliki guru serta sekolah terkait.
        $assessment = SchoolAssessment::with(['SchoolClass', 'Mapel'])->where('id', $request->school_assessment_id)->where('user_id', Auth::id())
        ->where('school_partner_id', $schoolId)->first();

        if (!$assessment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Assessment tidak ditemukan atau tidak dapat diakses.',
            ], 403);
        }

        // Data komposisi.
        $isLegacy = $request->boolean('is_legacy');
        $compositionId = null;

        if (!$isLegacy) {
            $composition = SchoolAssessmentComposition::where('id', $request->composition_id)->where('school_assessment_id', $assessment->id)->first();

            if (!$composition) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Komposisi soal tidak ditemukan atau tidak sesuai dengan assessment.',
                    'errors' => [
                        'is_legacy' => [
                            'Pilihan komposisi soal tidak valid. Silakan pilih ulang komposisi soal.',
                        ],
                    ],
                ], 422);
            }

            $compositionId = $composition->id;
        }

        // Ambil ID soal.
        $questionIds = array_values(array_unique(
            array_map('intval', $request->question_id)
        ));

        // Ambil bobot soal.
        $weights = collect($request->question_weight)->mapWithKeys(
            function ($weight, $questionId) {
                return [
                    (int) $questionId => round((float) $weight, 2),
                ];
            }
        );

        // Pastikan setiap soal memiliki bobot.
        foreach ($questionIds as $questionId) {
            if (!$weights->has($questionId)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bobot untuk salah satu soal belum tersedia.',
                    'errors' => [
                        'question_weight' => [
                            "Bobot soal dengan ID {$questionId} belum tersedia.",
                        ],
                    ],
                ], 422);
            }
        }

        // Hitung total bobot.
        $totalWeight = round(collect($questionIds)->sum(fn($questionId) => $weights->get($questionId)), 2
        );

        if ($totalWeight > 100) {
            return response()->json([
                'status' => 'error',
                'message' => 'Total bobot soal tidak boleh melebihi 100%.',
                'errors' => [
                    'total_weight' => [
                        "Total bobot saat ini {$totalWeight}%. Maksimal total bobot adalah 100%.",
                    ],
                ],
            ], 422);
        }

        // Ambil ID siswa.
        $studentIds = array_values(array_unique(
            array_map('intval', $request->student_ids)
        ));

        // Validasi siswa berasal dari kelas dan sekolah assessment.
        $validStudentIds = StudentSchoolClass::where('school_class_id', $assessment->school_class_id)->whereHas('SchoolClass', function ($query) use ($schoolId) {
            $query->where('school_partner_id', $schoolId);
        })->whereIn('student_id', $studentIds)->pluck('student_id')->map(fn($id) => (int) $id)->all();

        $invalidStudentIds = array_diff(
            $studentIds,
            $validStudentIds
        );

        if (!empty($invalidStudentIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terdapat siswa yang tidak terdaftar di kelas assessment ini.',
                'errors' => [
                    'student_ids' => [
                        'Pastikan siswa yang dipilih berasal dari kelas assessment.',
                    ],
                ],
            ], 422);
        }

        // Ambil soal dari bank soal.
        $questions = LmsQuestionBank::with(['Kelas', 'Mapel'])->whereIn('id', $questionIds)->get();

        if ($questions->count() !== count($questionIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terdapat soal yang tidak ditemukan.',
            ], 422);
        }

        $assessmentClassLevel = (int) app(ClassNameService::class)->extractClassLevel(
            optional($assessment->SchoolClass)->class_name
        );

        foreach ($questions as $question) {
            if ($question->school_partner_id !== null && (int) $question->school_partner_id !== (int) $schoolId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terdapat soal yang tidak berasal dari sekolah yang sesuai.',
                ], 422);
            }
        }

        DB::beginTransaction();

        try {

            // Jika memilih kelompok legacy, konversikan menjadi komposisi biasa.
            if ($isLegacy) {
                $composition = SchoolAssessmentComposition::create([
                    'school_assessment_id' => $assessment->id,
                    'composition_name' => 'Tanpa Nama',
                ]);

                $compositionId = $composition->id;

                // Pindahkan seluruh soal legacy ke komposisi baru.
                SchoolAssessmentQuestion::where('school_assessment_id', $assessment->id)->whereNull('sac_id')->update([
                    'sac_id' => $compositionId,
                ]);
            }

            // HAPUS SOAL YANG TIDAK LAGI DIPILIH DARI KOMPOSISI
            SchoolAssessmentQuestion::where('school_assessment_id', $assessment->id)->where('sac_id', $compositionId)->whereNotIn('question_bank_id', $questionIds)->delete();

            // Simpan soal beserta komposisinya.
            foreach ($questionIds as $questionId) {
                SchoolAssessmentQuestion::updateOrCreate(
                    [
                        'school_assessment_id' => $assessment->id,
                        'sac_id' => $compositionId,
                        'question_bank_id' => $questionId,
                    ],
                    [
                        'question_weight' => $weights->get($questionId),
                    ]
                );
            }

            // hapus siswa yang tidak lagi dipilih dari komposisi tujuan.
            StudentAssessmentComposition::where('school_assessment_id', $assessment->id)->where('sac_id', $compositionId)->whereNotIn('user_id', $studentIds)->delete();

            // Masukkan siswa ke komposisi yang dipilih.
            foreach ($studentIds as $studentId) {
                StudentAssessmentComposition::updateOrCreate(
                    [
                        'school_assessment_id' => $assessment->id,
                        'user_id' => $studentId,
                    ],
                    [
                        'sac_id' => $compositionId,
                    ]
                );
            }

            DB::commit();

            // Ambil soal yang berhasil disimpan.
            $savedQuestions = SchoolAssessmentQuestion::where('school_assessment_id', $assessment->id)->where('sac_id', $compositionId)
            ->get(['question_bank_id', 'question_weight', 'sac_id'])->map(function ($item) {
                return [
                    'question_id' => (int) $item->question_bank_id,
                    'weight' => (float) $item->question_weight,
                ];
            })->values();

            return response()->json([
                'status' => 'success',
                'message' => 'Soal dan siswa berhasil dimasukkan ke komposisi assessment.',
                'data' => [
                    'school_assessment_id' => (int) $assessment->id,
                    'composition_id' => (int) $compositionId,
                    'is_legacy' => false,
                    'question_count' => $savedQuestions->count(),
                    'total_weight' => round($savedQuestions->sum('weight'), 2),
                    'assigned_questions' => $savedQuestions,
                    'student_count' => count($studentIds),
                    'assigned_students' => $studentIds,
                ],
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menyimpan soal dan siswa ke komposisi assessment.',
            ], 500);
        }
    }

    public function storeTeacherQuestionReleaseComposition(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'school_assessment_id' => [
                'required',
            ],
            'composition_name' => [
                'required',
            ],
        ], [
            'school_assessment_id.required' => 'Assessment wajib dipilih.',
            'composition_name.required' => 'Nama komposisi wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Terdapat kesalahan pada input.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $composition = DB::transaction(function () use ($request) {
                $schoolAssessment = SchoolAssessment::findOrFail(
                    $request->school_assessment_id
                );

                return SchoolAssessmentComposition::create([
                    'school_assessment_id' => $schoolAssessment->id,
                    'composition_name' => trim($request->composition_name),
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Komposisi soal berhasil ditambahkan.',
                'composition' => $composition,
            ], 201);

        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan komposisi soal.',
            ], 500);
        }
    }

    public function updateTeacherQuestionReleaseComposition(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'school_assessment_id' => [
                'required',
            ],
            'composition_id' => [
                'nullable',
                'integer',
                'required_if:is_legacy,0',
            ],
            'is_legacy' => [
                'required',
                'boolean',
            ],
            'composition_name' => [
                'required',
            ],
        ], [
            'school_assessment_id.required' => 'Assessment wajib dipilih.',
            'composition_id.required_if' => 'Komposisi soal wajib dipilih.',
            'composition_id.integer' => 'ID komposisi tidak valid.',
            'is_legacy.required' => 'Jenis komposisi wajib ditentukan.',
            'is_legacy.boolean' => 'Jenis komposisi tidak valid.',
            'composition_name.required' => 'Nama komposisi wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Terdapat kesalahan pada input.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = DB::transaction(function () use ($request) {
                $schoolAssessment = SchoolAssessment::findOrFail(
                    $request->school_assessment_id
                );

                $compositionName = trim($request->composition_name);
                $isLegacy = $request->boolean('is_legacy');

                // KONDISI 1: EDIT KOMPOSISI LEGACY
                if ($isLegacy) {
                    $legacyQuestions = SchoolAssessmentQuestion::where(
                        'school_assessment_id',
                        $schoolAssessment->id
                    )
                        ->whereNull('sac_id');

                    $legacyQuestionCount = $legacyQuestions->count();

                    if ($legacyQuestionCount === 0) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'composition_name' => [
                                'Kelompok soal tanpa nama sudah tidak memiliki pertanyaan.',
                            ],
                        ]);
                    }

                    // Buat komposisi baru
                    $composition = SchoolAssessmentComposition::create([
                        'school_assessment_id' => $schoolAssessment->id,
                        'composition_name' => $compositionName,
                    ]);

                    // Pindahkan seluruh pertanyaan legacy ke komposisi baru
                    SchoolAssessmentQuestion::where(
                        'school_assessment_id',
                        $schoolAssessment->id
                    )
                        ->whereNull('sac_id')
                        ->update([
                            'sac_id' => $composition->id,
                        ]);

                } else {
                    // KONDISI 2: EDIT KOMPOSISI YANG SUDAH ADA
                    $composition = SchoolAssessmentComposition::where(
                        'id',
                        $request->composition_id
                    )
                        ->where('school_assessment_id', $schoolAssessment->id)
                        ->firstOrFail();

                    $composition->update([
                        'composition_name' => $compositionName,
                    ]);
                }

                // Ambil informasi komposisi terbaru
                $assignedQuestions = SchoolAssessmentQuestion::where(
                    'school_assessment_id',
                    $schoolAssessment->id
                )
                    ->where('sac_id', $composition->id);

                return [
                    'composition' => [
                        'id' => $composition->id,
                        'school_assessment_id' => $composition->school_assessment_id,
                        'composition_name' => $composition->composition_name,
                        'assigned_question_count' => $assignedQuestions->count(),
                        'assigned_total_weight' => (float) $assignedQuestions->sum('question_weight'),
                    ],
                    'is_legacy_converted' => $isLegacy,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Nama komposisi soal berhasil diperbarui.',
                'composition' => $result['composition'],
                'is_legacy_converted' => $result['is_legacy_converted'],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terdapat kesalahan pada data komposisi.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui nama komposisi soal.',
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
        $classLevels = $schoolClasses->pluck('SchoolAssessment.SchoolClass.class_name')->map(fn($c) => (int) app(ClassNameService::class)->extractClassLevel($c))->filter()->unique()->sort()->values();

        $selectedClass = $request->filled('search_class') ? app(ClassNameService::class)->resolveClassLevel($request->search_class) : ($classLevels->first() ?? $defaultLevel);

        // FILTER ROMBEL SESUAI LEVEL
        $schoolClasses = $schoolClasses->filter(fn($item) => (int)app(ClassNameService::class)->extractClassLevel($item->SchoolAssessment?->SchoolClass?->class_name) === (int)$selectedClass)->values();

        // Filter berdasarkan level kelas
        if ($selectedClass) {
            $query = $query->filter(function ($item) use ($selectedClass) {

                if (!$item?->SchoolAssessment?->SchoolClass?->class_name) {
                    return false;
                }

                return (int)app(ClassNameService::class)->extractClassLevel($item->SchoolAssessment->SchoolClass->class_name) === (int)$selectedClass;
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

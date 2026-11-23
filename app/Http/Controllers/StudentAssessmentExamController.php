<?php

namespace App\Http\Controllers;

use App\Helpers\TimezoneHelper;
use App\Models\SchoolAssessment;
use App\Services\Proctor\ProctorService;
use App\Models\SchoolAssessmentComposition;
use App\Models\SchoolAssessmentQuestion;
use App\Models\SchoolPartner;
use App\Models\StudentAssessmentAnswer;
use App\Models\StudentAssessmentAttempt;
use App\Models\StudentAssessmentComposition;
use App\Models\StudentAssessmentSummary;
use App\Models\StudentProjectSubmission;
use App\Models\StudentSchoolClass;
use App\Models\SubjectPassingGradeCriteria;
use App\Models\UserAccount;
use App\Services\LMS\AssessmentSummaryService\AssessmentSummaryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentAssessmentExamController extends Controller
{
    private $summaryService;

    public function __construct(AssessmentSummaryService $summaryService)
    {
        $this->summaryService = $summaryService;
    }
    
    public function studentAssessmentExam($role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId, $semester, $assessmentId)
    {
        return view('features.lms.student.assessment.student-assessment-test', compact('role', 'schoolName', 'schoolId', 'curriculumId', 
            'mapelId', 'assessmentTypeId', 'semester', 'assessmentId'));
    }

    public function studentAssessmentExamForm($role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId, $semester, $assessmentId) 
    {
        $user = UserAccount::with('StudentProfile')->find(Auth::id());

        $assessment = SchoolAssessment::with(['SchoolAssessmentType', 'SchoolClass'])->where('id', $assessmentId)
            ->whereHas('SchoolClass.StudentSchoolClass', function ($query) use ($user) {
                $query->where('student_id', $user->id)->where('student_class_status', 'active');
            })->where('assessment_type_id', $assessmentTypeId)->where('semester', $semester)->first();

        if (!$assessment) {
            return response()->json(['data' => null]);
        }

        // AMBIL ASSESSMENT
        $schoolAssessment = SchoolAssessment::where('id', $assessmentId)->first();

        // REMEDIAL LOGIC (AMBIL CATEGORY & PARENT)
        $assessmentCategory = strtolower($schoolAssessment->assessment_category ?? '');
        $parentAssessmentId = $schoolAssessment->parent_assessment_id;

        $sourceAssessmentId = $assessmentId;

        if (in_array($assessmentCategory, ['remedial', 'susulan']) && $parentAssessmentId) {
            $sourceAssessmentId = $parentAssessmentId;
        }

        $wrongBankIds = [];

        // KOMPOSISI SOAL SISWA
        $studentComposition = null;

        $hasCompositions = SchoolAssessmentComposition::where('school_assessment_id', $sourceAssessmentId)->exists();

        if ($hasCompositions) {
            $studentComposition = StudentAssessmentComposition::where('school_assessment_id', $sourceAssessmentId)->where('user_id', $user->id)->first();
        }

        if ($assessmentCategory === 'remedial' && $parentAssessmentId) {

            $previousAssessment = $this->remedialPreviousAssessment($schoolAssessment, $user->id);

            // CACHE KEY
            $cacheKeyWrong = "assessment-remedial-wrong-{$user->id}-prev-" . ($previousAssessment->id ?? 'none');

            if (Cache::has($cacheKeyWrong)) {

                $wrongBankIds = Cache::get($cacheKeyWrong);

            } else {

                if ($previousAssessment) {

                    $previousAnswers = StudentAssessmentAnswer::with('SchoolAssessmentQuestion')->where('student_id', $user->id)->where('school_assessment_id', $previousAssessment->id)->get();

                    foreach ($previousAnswers as $ans) {

                        if ($ans->status_answer === 'submitted' && $ans->question_score !== null && $ans->question_score <= 0) {
                            if ($ans->SchoolAssessmentQuestion) {
                                $wrongBankIds[] = $ans->SchoolAssessmentQuestion->question_bank_id;
                            }
                        }
                    }
                }

                $wrongBankIds = array_values(array_unique($wrongBankIds));

                Cache::put($cacheKeyWrong, $wrongBankIds, now()->addHours(3));
            }
        }

        $forceRefresh = request()->boolean('force_refresh') || request()->filled('force_refresh');
        $poolCacheKey = "assessment_questions_pool_{$sourceAssessmentId}";

        if ($forceRefresh) {
            Cache::forget($poolCacheKey);
        }

        // Ambil master pool seluruh soal assessment dari cache.
        $questionPool = Cache::remember($poolCacheKey, now()->addHours(6), function () use ($sourceAssessmentId) {
            return SchoolAssessmentQuestion::with([
                'LmsQuestionBank',
                'LmsQuestionBank.LmsQuestionOption',
                'LmsQuestionBank.Mapel'
            ])
            ->where('school_assessment_id', $sourceAssessmentId)
            ->whereHas('LmsQuestionBank', function ($q) {
                $q->where('status_bank_soal', 'Publish');
            })
            ->orderBy('id')
            ->get();
        });

        // FILTER BERDASARKAN KOMPOSISI SISWA.
        if ($hasCompositions) {

            if ($studentComposition) {
                // Siswa hanya mendapatkan soal dari komposisinya sendiri.
                $questionPool = $questionPool->where('sac_id', $studentComposition->sac_id)->values();
            } else {
                // Assessment sudah menggunakan komposisi, tetapi siswa tidak terdaftar pada komposisi mana pun.
                $questionPool = collect();
            }

        } else {
            // Assessment lama: gunakan soal tanpa komposisi.
            $questionPool = $questionPool->whereNull('sac_id')->values();
        }

        // ID soal dihitung setelah filter komposisi.
        $publishedQuestionIds = $questionPool->pluck('id')->implode(',');

        $shuffleQuestions = $schoolAssessment->shuffle_questions;
        $shuffleOptions = $schoolAssessment->shuffle_options;

        $cacheKey = "assessment-{$user->id}-{$sourceAssessmentId}-{$publishedQuestionIds}-{$semester}-{$shuffleQuestions}-test";

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        // 2. Filter remedial langsung di memori jika ada
        $filteredPool = $questionPool;
        if (!empty($wrongBankIds)) {
            $filteredPool = $filteredPool->whereIn('question_bank_id', $wrongBankIds);
        }

        // 3. Clone collection dan model agar transformasi opsi tiap siswa terisolasi
        $isolatedPool = $filteredPool->map(function ($item) {
            $cloned = clone $item;
            if ($cloned->relationLoaded('LmsQuestionBank')) {
                $qb = clone $cloned->LmsQuestionBank;
                if ($qb->relationLoaded('LmsQuestionOption')) {
                    $qb->setRelation('LmsQuestionOption', $qb->LmsQuestionOption->map(fn($opt) => clone $opt));
                }
                $cloned->setRelation('LmsQuestionBank', $qb);
            }
            return $cloned;
        });

        // 4. Urutan soal per siswa
        if (Cache::has($cacheKey)) {
            $cachedIds = Cache::get($cacheKey);
            $questions = $isolatedPool
                ->whereIn('id', $cachedIds)
                ->sortBy(function ($q) use ($cachedIds) {
                    return array_search($q->id, $cachedIds);
                })
                ->values();
        } else {
            if ($shuffleQuestions) {
                $questions = $isolatedPool->shuffle()->values();
            } else {
                $questions = $isolatedPool->values();
            }

            $cachePayload = $questions->pluck('id')->toArray();
            Cache::put($cacheKey, $cachePayload, now()->addHours(3));
        }

        // SHUFFLE OPTIONS
        $questions->transform(function ($question) use ($user, $assessmentId, $semester, $shuffleOptions, $forceRefresh) {

            $type = strtoupper($question->LmsQuestionBank->tipe_soal ?? '');

            $options = $question->LmsQuestionBank->LmsQuestionOption;

            if (!$options) {
                return $question;
            }

            $publishedOptionIds = $options->sortBy('id')->pluck('id')->implode(',');

            $optionCacheKey = "assessment-option-{$user->id}-{$assessmentId}-{$question->id}-{$publishedOptionIds}-{$semester}-{$shuffleOptions}";

            // MCQ / MCMA
            if (in_array($type, ['MCQ', 'MCMA'])) {

                if (Cache::has($optionCacheKey)) {

                    $cachedIds = Cache::get($optionCacheKey);

                    $sorted = $options
                        ->whereIn('id', $cachedIds)
                        ->sortBy(function ($opt) use ($cachedIds) {
                            return array_search($opt->id, $cachedIds);
                        })
                        ->values();

                } else {

                    if ($shuffleOptions) {
                        $sorted = $options->shuffle()->values();
                    } else {
                        $sorted = $options->sortBy('id')->values();
                    }

                    Cache::put(
                        $optionCacheKey,
                        $sorted->pluck('id')->toArray(),
                        now()->addHours(3)
                    );
                }

                // LABEL TAMPIL TETAP A, B, C, D, E. options_key asli tidak berubah
                $sorted = $sorted->values()->map(function ($option, $index) {
                    $option->setAttribute('display_key', chr(65 + $index));

                    return $option;
                });

                $question->LmsQuestionBank->setRelation('LmsQuestionOption', $sorted);
            }

            // MATCHING
            if ($type === 'MATCHING') {

                // AMBIL LEFT
                $left = $options->filter(function ($opt) {
                    return isset($opt->extra_data['side'])
                        && $opt->extra_data['side'] === 'left';
                })->values();

                // AMBIL RIGHT
                $right = $options->filter(function ($opt) {
                    return isset($opt->extra_data['side'])
                        && $opt->extra_data['side'] === 'right';
                })->values();

                // CACHE LEFT
                $publishedLeftIds = $left->sortBy('id')->pluck('id')->implode(',');
                $leftCacheKey = "assessment-match-left-{$user->id}-{$assessmentId}-{$question->id}-{$publishedLeftIds}-{$semester}-{$shuffleOptions}";

                // CACHE RIGHT
                $publishedRightIds = $right->sortBy('id')->pluck('id')->implode(',');
                $rightCacheKey = "assessment-match-right-{$user->id}-{$assessmentId}-{$question->id}-{$publishedRightIds}-{$semester}-{$shuffleOptions}";

                // FORCE REFRESH KEDUA CACHE
                if ($forceRefresh) {
                    Cache::forget($leftCacheKey);
                    Cache::forget($rightCacheKey);
                }

                // SHUFFLE LEFT
                if (Cache::has($leftCacheKey)) {

                    $cachedLeftIds = Cache::get($leftCacheKey);

                    $left = $left->whereIn('id', $cachedLeftIds)->sortBy(function ($opt) use ($cachedLeftIds) {
                        return array_search($opt->id, $cachedLeftIds);
                    })->values();

                } else {

                    if ($shuffleOptions) {
                        $left = $left->shuffle()->values();
                    }

                    Cache::put($leftCacheKey, $left->pluck('id')->toArray(), now()->addHours(3));
                }

                // SHUFFLE RIGHT
                if (Cache::has($rightCacheKey)) {

                    $cachedRightIds = Cache::get($rightCacheKey);

                    $right = $right->whereIn('id', $cachedRightIds)->sortBy(function ($opt) use ($cachedRightIds) {
                        return array_search($opt->id, $cachedRightIds);
                    })->values();

                } else {

                    if ($shuffleOptions) {
                        $right = $right->shuffle()->values();
                    }

                    Cache::put($rightCacheKey, $right->pluck('id')->toArray(), now()->addHours(3));
                }

                // GABUNGKAN LEFT + RIGHT
                $shuffled = collect();

                foreach ($left as $l) {
                    $shuffled->push($l);
                }

                foreach ($right as $r) {
                    $shuffled->push($r);
                }

                $question->LmsQuestionBank->setRelation(
                    'LmsQuestionOption',
                    $shuffled
                );
            }

            if ($type === 'PG_KOMPLEKS') {

                // AMBIL ITEMS (ROW)
                $items = $options->filter(function ($opt) {
                    return isset($opt->extra_data['side']) 
                        && $opt->extra_data['side'] === 'item';
                })->values();

                $publishedItemIds = $items->sortBy('id')->pluck('id')->implode(',');

                $itemCacheKey = "assessment-pgk-item-{$user->id}-{$assessmentId}-{$question->id}-{$publishedItemIds}-{$semester}-{$shuffleOptions}";

                if (Cache::has($itemCacheKey)) {

                    $cachedIds = Cache::get($itemCacheKey);

                    $items = $items->whereIn('id', $cachedIds)->sortBy(function ($opt) use ($cachedIds) {
                        return array_search($opt->id, $cachedIds);
                    })->values();

                } else {

                    if ($shuffleOptions) {
                        $items = $items->shuffle()->values();
                    } else {
                        $items = $items->values();
                    }

                    Cache::put($itemCacheKey, $items->pluck('id')->toArray(), now()->addHours(3));
                }

                // AMBIL CATEGORY (COLUMN)
                $right = $options->filter(function ($opt) {
                    return isset($opt->extra_data['side']) 
                        && $opt->extra_data['side'] === 'category';
                })->values();

                $publishedRightIds = $right->sortBy('id')->pluck('id')->implode(',');

                $matchingCacheKey = "assessment-pgk-category-{$user->id}-{$assessmentId}-{$question->id}-{$publishedRightIds}-{$semester}-{$shuffleOptions}";

                if (Cache::has($matchingCacheKey)) {

                    $cachedIds = Cache::get($matchingCacheKey);

                    $right = $right->whereIn('id', $cachedIds)->sortBy(function ($opt) use ($cachedIds) {
                        return array_search($opt->id, $cachedIds);
                    })->values();
                } else {

                    if ($shuffleOptions) {
                        $right = $right->shuffle()->values();
                    } else {
                        $right = $right->values();
                    }

                    Cache::put($matchingCacheKey, $right->pluck('id')->toArray(), now()->addHours(3));
                }

                // GABUNGKAN ITEMS + CATEGORY
                $shuffled = collect();

                foreach ($items as $item) {
                    $shuffled->push($item);
                }

                foreach ($right as $cat) {
                    $shuffled->push($cat);
                }

                $question->LmsQuestionBank->setRelation(
                    'LmsQuestionOption',
                    $shuffled
                );
            }

            return $question;
        });

        // STUDENT ANSWER
        $questionsAnswer = StudentAssessmentAnswer::where('student_id', $user->id)
            ->where('school_assessment_id', $assessmentId)
            ->get()
            ->mapWithKeys(function ($item) {

                $data = $item->attributesToArray();

                if (is_string($data['answer_value'])) {
                    $decoded = json_decode($data['answer_value'], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $data['answer_value'] = $decoded;
                    }
                }

                $question = SchoolAssessmentQuestion::with('LmsQuestionBank.LmsQuestionOption')
                    ->find($item->school_assessment_question_id);

                $isCorrect = false;

                if ($question) {

                    $type = $question->LmsQuestionBank->tipe_soal;

                    $correctOptions = $question->LmsQuestionBank->LmsQuestionOption
                        ->where('is_correct', 1)
                        ->pluck('options_key')
                        ->values()
                        ->toArray();

                    $studentAnswer = $data['answer_value'];

                    if ($type === 'MCQ') {
                        $isCorrect = $studentAnswer === ($correctOptions[0] ?? null);
                    }

                    if ($type === 'MCMA') {

                        if (!is_array($studentAnswer)) {
                            $isCorrect = false;
                        } else {

                            sort($correctOptions);
                            sort($studentAnswer);

                            $isCorrect = $studentAnswer === $correctOptions;
                        }
                    }

                    if ($type === 'MATCHING') {

                        if (is_string($studentAnswer)) {
                            $studentAnswer = json_decode($studentAnswer, true);
                        }

                        if (!is_array($studentAnswer)) {
                            $isCorrect = false;
                        } else {

                            $correctPairs = $question->LmsQuestionBank->LmsQuestionOption
                                ->filter(function ($opt) {
                                    return isset($opt->extra_data['side']) 
                                        && $opt->extra_data['side'] === 'left';
                                })
                                ->mapWithKeys(function ($opt) {
                                    return [
                                        trim($opt->options_key) =>
                                        trim($opt->extra_data['pair_with'] ?? '')
                                    ];
                                })
                                ->toArray();

                            $normalizedStudentAnswer = collect($studentAnswer)
                                ->mapWithKeys(function ($value, $key) {
                                    return [trim($key) => trim($value)];
                                })
                                ->toArray();

                            ksort($correctPairs);
                            ksort($normalizedStudentAnswer);

                            $isCorrect = $correctPairs === $normalizedStudentAnswer;
                        }
                    }

                    if ($type === 'PG_KOMPLEKS') {

                        if (is_string($studentAnswer)) {
                            $studentAnswer = json_decode($studentAnswer, true);
                        }

                        if (!is_array($studentAnswer)) {
                            $isCorrect = false;
                        } else {

                            $correctPairs = $question->LmsQuestionBank->LmsQuestionOption
                                ->filter(function ($opt) {
                                    return isset($opt->extra_data['side']) 
                                        && $opt->extra_data['side'] === 'item';
                                })
                                ->mapWithKeys(function ($opt) {
                                    return [
                                        trim($opt->options_key) =>
                                        trim($opt->extra_data['answer'] ?? '')
                                    ];
                                })
                                ->toArray();

                            $normalizedStudentAnswer = collect($studentAnswer)
                                ->mapWithKeys(function ($value, $key) {
                                    return [trim($key) => trim($value)];
                                })
                                ->toArray();

                            ksort($correctPairs);
                            ksort($normalizedStudentAnswer);

                            $isCorrect = $correctPairs === $normalizedStudentAnswer;
                        }
                    }
                }

                $data['is_correct'] = $isCorrect;

                return [
                    $item->school_assessment_question_id => $data
                ];
            });

        $schoolPartner = SchoolPartner::findOrFail($assessment->school_partner_id);

        $timezone = TimezoneHelper::getSchoolTimezone($schoolPartner);
        $timezoneLabel = TimezoneHelper::getTimezoneLabel($timezone);

        $startDate = TimezoneHelper::parse($assessment->start_date, $timezone);
        $endDate = TimezoneHelper::parse($assessment->end_date, $timezone);
        $now = TimezoneHelper::now($timezone);

        $attempt = StudentAssessmentAttempt::where('student_id', $user->id)
            ->where('school_assessment_id', $assessmentId)
            ->first();

        if ($attempt && $attempt->expire_time) {
            $attemptExpire = $attempt->expire_time;
            if ($attemptExpire && (!$endDate || $attemptExpire->gt($endDate))) {
                $endDate = $attemptExpire->copy()->addMinute();
            }
        }

        $status = 'active';

        if ($startDate && $now->lt($startDate)) {
            $status = 'not_started';
        } elseif ($endDate && $now->gt($endDate)) {
            $status = 'expired';
        }

        // kunci jawaban tidak boleh sampai ke browser; 'side' tetap dikirim untuk UI matching
        $questions->each(function ($question) {
            $bank = $question->LmsQuestionBank;

            if ($bank && $bank->LmsQuestionOption) {
                $bank->LmsQuestionOption->each(function ($opt) {
                    unset($opt->is_correct);

                    if (is_array($opt->extra_data)) {
                        $extra = $opt->extra_data;
                        unset($extra['answer'], $extra['pair_with']);
                        $opt->extra_data = $extra;
                    }
                });
            }
        });

        return response()->json([
            'data' => $questions,
            'questionsAnswer' => $questionsAnswer,
            'schoolAssessment' => $schoolAssessment,
            'user' => $user,
            'attempt' => $attempt ? [
                'status' => $attempt->status,
                'tab_switch_count' => $attempt->tab_switch_count,
                'start_time' => $attempt->start_time ? $attempt->start_time->timestamp * 1000 : null,
                'expire_time' => $attempt->expire_time ? $attempt->expire_time->timestamp * 1000 : null,
            ] : null,
            'start_date' => $startDate?->format('Y-m-d H:i:s'),
            'end_date' => $endDate?->format('Y-m-d H:i:s'),
            'start_date_iso' => $startDate?->format('Y-m-d\TH:i:sP'),
            'end_date_iso' => $endDate?->format('Y-m-d\TH:i:sP'),
            'timezone' => $timezone,
            'timezone_label' => $timezoneLabel,
            'status' => $status,
            'server_now' => $now->format('Y-m-d H:i:s'),
            'server_now_iso' => $now->format('Y-m-d\TH:i:sP'),
            'assessment_title' => $assessment->SchoolAssessmentType->name,
            'semester' => $assessment->semester,
            'resultTestHref' => '/lms/:role/:schoolName/:schoolId/curriculum/:curriculumId/subject/:mapelId/learning/assessment/:assessmentTypeId/semester/:semester/assessment/:assessmentId/result-test'
        ]);
    }

    public function startTImer($role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId, $semester, $assessmentId)
    {
        $userId = Auth::id();

        $assessment = SchoolAssessment::findOrFail($assessmentId);

        $schoolPartner = SchoolPartner::findOrFail($assessment->school_partner_id);

        $timezone = TimezoneHelper::getSchoolTimezone($schoolPartner);

        $startDate = TimezoneHelper::parse($assessment->start_date, $timezone);
        $endDate = TimezoneHelper::parse($assessment->end_date, $timezone);
        $schoolNow = TimezoneHelper::now($timezone);

        $attempt = StudentAssessmentAttempt::where('student_id', $userId)
            ->where('school_assessment_id', $assessmentId)
            ->first();

        if ($attempt && $attempt->expire_time) {
            $attemptExpire = $attempt->expire_time;
            if ($attemptExpire && (!$endDate || $attemptExpire->gt($endDate))) {
                $endDate = $attemptExpire->copy()->addMinute();
            }
        }

        if ($startDate && $schoolNow->lt($startDate)) {
            return response()->json([
                'status' => 'not_started',
                'message' => 'Assessment belum dimulai.'
            ], 422);
        }

        if ($endDate && $schoolNow->gt($endDate)) {
            return response()->json([
                'status' => 'expired',
                'message' => 'Assessment sudah berakhir.'
            ], 422);
        }

        if ($assessment->proctor_stopped_at) {
            return response()->json(['status' => 'stopped', 'message' => 'Ujian dihentikan oleh guru.'], 410);
        }
        if ($assessment->proctor_paused_at || $attempt?->proctor_paused_at) {
            return response()->json(['status' => 'paused', 'message' => 'Ujian sedang dijeda guru.'], 202);
        }

        // nobody starts until the teacher admits them; an existing attempt (resume) is never gated
        if (!$attempt) {
            $entry = app(ProctorService::class)->entryFor($assessment, $userId);

            if ($entry->status === 'denied') {
                return response()->json(['status' => 'denied', 'message' => 'Guru tidak mengizinkan kamu mengikuti ujian ini.'], 403);
            }
            if ($entry->status !== 'admitted') {
                return response()->json(['status' => 'waiting_teacher', 'message' => 'Menunggu guru memulai ujian.'], 202);
            }
        }

        $attempt = app(ProctorService::class)->openAttempt($assessment, $userId);

        return response()->json([
            'status' => 'success',
            'start_time' => $attempt->start_time->timestamp * 1000,
            'expire_time' => $attempt->expire_time->timestamp * 1000,
            'duration' => $assessment->duration
        ]);
    }

    
    public function studentAssessmentExamAnswer(Request $request, $role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId, $semester, $assessmentId)
    {
        $userId = Auth::id();

        $assessment = SchoolAssessment::findOrFail($assessmentId);

        $schoolPartner = SchoolPartner::findOrFail($assessment->school_partner_id);

        $timezone = TimezoneHelper::getSchoolTimezone($schoolPartner);

        $endDate = TimezoneHelper::parse($assessment->end_date, $timezone);
        $now = TimezoneHelper::now($timezone);

        $attempt = StudentAssessmentAttempt::where('student_id', $userId)
            ->where('school_assessment_id', $assessmentId)
            ->first();

        if ($attempt && $attempt->expire_time) {
            $attemptExpire = $attempt->expire_time;
            if ($attemptExpire && (!$endDate || $attemptExpire->lt($endDate))) {
                $endDate = $attemptExpire->copy()->addMinute();
            }
        }

        $isExpired = $endDate && $now->greaterThan($endDate);

        if ($assessment->proctor_stopped_at) {
            return response()->json(['status' => 'expired', 'message' => 'Ujian dihentikan oleh guru. Jawaban tidak dapat disimpan.'], 422);
        }
        if ($assessment->proctor_paused_at || $attempt?->proctor_paused_at) {
            return response()->json(['status' => 'paused', 'message' => 'Ujian sedang dijeda guru.'], 423);
        }

        // Anti-cheat guards: only a live, unfinalized attempt may write answers.
        // Final auto-submits go through studentAssessmentExamEnd(), which is
        // allowed once for a cheating/timeout attempt.
        if (!$attempt) {
            return response()->json([
                'status' => 'not_started',
                'message' => 'Assessment belum dimulai. Silakan mulai timer terlebih dahulu.',
            ], 422);
        }

        if ($attempt->finalized_at || $attempt->status !== 'in_progress') {
            return response()->json([
                'status' => 'error',
                'message' => $attempt->status === 'cheating'
                    ? 'Ujian telah dihentikan karena pelanggaran.'
                    : 'Ujian sudah selesai. Jawaban tidak dapat diubah.',
            ], 422);
        }

        if ($isExpired) {
            return response()->json([
                'status' => 'expired',
                'message' => 'Waktu ujian telah berakhir. Jawaban tidak dapat disimpan.',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'school_assessment_question_id' => 'required|exists:school_assessment_questions,id',
            'answer_value' => [
                Rule::requiredIf(!$request->auto_submit)
            ],
            'status_answer' => 'required|in:draft,submitted',
        ], [
            'answer_value.required' => 'Jawaban tidak boleh kosong.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // ambil soal
        $schoolQuestion = SchoolAssessmentQuestion::with('lmsQuestionBank')
            ->where('school_assessment_id', $assessmentId)
            ->findOrFail($request->school_assessment_question_id);

        $question = $schoolQuestion->lmsQuestionBank;

        // cari jawaban siswa
        $answer = StudentAssessmentAnswer::where('student_id', $userId)->where('school_assessment_question_id', $request->school_assessment_question_id)
        ->where('school_assessment_id', $assessmentId)->first();

        // normalisasi answer_value
        $answerData = $request->answer_value;

        // decode JSON jika string
        if (is_string($answerData)) {
            $decoded = json_decode($answerData, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $answerData = $decoded;
            }
        }

        // jika kosong -> simpan NULL
        if ($answerData === '' || $answerData === [] || $answerData === null) {
            $answerData = null;
        }

        $schoolAssessment = $assessment;

        $assessmentCategory = strtolower($schoolAssessment->assessment_category ?? '');
        $isRemedial = $assessmentCategory === 'remedial';

        if ($isRemedial && $assessment->parent_assessment_id) {

            $previousAssessment = $this->remedialPreviousAssessment($assessment, $userId);

            // cache key harus sama dengan form
            $cacheKeyWrong = "assessment-remedial-wrong-{$userId}-prev-" . ($previousAssessment->id ?? 'none');

            $wrongBankIds = Cache::get($cacheKeyWrong, []);

            // fallback kalau cache kosong
            if (empty($wrongBankIds)) {
                $totalQuestions = SchoolAssessmentQuestion::where('school_assessment_id', $assessment->parent_assessment_id)->count();
            } else {
                $totalQuestions = count($wrongBankIds);
            }

        } else {

            $totalQuestions = SchoolAssessmentQuestion::where('school_assessment_id', $assessmentId)->count();
        }

        $scorePerQuestion = (float) $schoolQuestion->question_weight;

        // hitung score
        $score = 0;

        if ($answerData !== null) {

            switch ($question->tipe_soal) {

                // MCQ
                case 'MCQ':

                    $correctOption = $question->lmsQuestionOption()
                        ->where('is_correct', 1)
                        ->first();

                        if ($correctOption && $answerData === $correctOption->options_key) {
                            $score = $scorePerQuestion;
                        }

                break;

                // MCMA
                case 'MCMA':

                    if (is_array($answerData)) {

                        $correctOptions = $question->lmsQuestionOption()
                            ->where('is_correct', 1)
                            ->pluck('options_key')
                            ->map(fn($val) => trim((string)$val))
                            ->toArray();

                        $totalCorrect = count($correctOptions);

                        if ($totalCorrect > 0) {
                            $normalizedAnswers = array_map(fn($val) => trim((string)$val), $answerData);
                            $maxScore = $scorePerQuestion;
                            $wrongSelected = count(array_diff($normalizedAnswers, $correctOptions));

                            if ($wrongSelected === 0) {
                                $correctSelected = count(array_intersect($normalizedAnswers, $correctOptions));
                                $score = round(($correctSelected / $totalCorrect) * $maxScore, 2);
                            } else {
                                $score = 0;
                            }
                        }

                    }

                break;

                // MATCHING
                case 'MATCHING':

                    if (is_array($answerData)) {

                        $correctPairs = $question->lmsQuestionOption()
                            ->get()
                            ->filter(function ($opt) {
                                return isset($opt->extra_data['side'])
                                    && $opt->extra_data['side'] === 'left';
                            })
                            ->mapWithKeys(function ($opt) {
                                return [
                                    $opt->options_key => $opt->extra_data['pair_with'] ?? null
                                ];
                            })
                            ->toArray();

                        ksort($correctPairs);
                        ksort($answerData);

                        if ($correctPairs === $answerData) {
                            $score = $scorePerQuestion;
                        }

                    }

                break;

                case 'PG_KOMPLEKS':

                    $correctAnswers = $question->lmsQuestionOption()->get()->filter(function ($opt) {
                        return isset($opt->extra_data['side']) && $opt->extra_data['side'] === 'item';
                    })
                        ->mapWithKeys(function ($opt) {
                            return [
                                $opt->options_key => $opt->extra_data['answer']
                            ];
                        })->toArray();

                    ksort($correctAnswers);
                    if (is_array($answerData)) {
                        ksort($answerData);
                    }

                    if ($correctAnswers === $answerData) {
                        $score = $scorePerQuestion;
                    }
                break;

                // ESSSAY
                case 'ESSAY':

                    $score = 0;

                break;
            }
        }

        // simpan jawaban
        if ($answer) {

            $updateData = [
                'status_answer' => $request->status_answer,
                'answer_duration' => $request->answer_duration,
            ];

            // hanya update jika dikirim
            if ($request->has('answer_value')) {
                $updateData['answer_value'] = $answerData;
                $updateData['question_score'] = $score;
            }

            if ($request->filled('total_exam_duration')) {
                $updateData['total_exam_duration'] = $request->total_exam_duration;
            }

            $answer->update($updateData);

        } else {

            $data = [
                'student_id' => $userId,
                'school_assessment_id' => $assessmentId,
                'school_assessment_question_id' => $request->school_assessment_question_id,
                'answer_value' => $answerData,
                'question_score' => $score,
                'answer_duration' => $request->answer_duration,
                'status_answer' => $request->status_answer,
                'grading_status' => $question->tipe_soal === 'ESSAY' ? 'pending' : null,
            ];

            if ($request->filled('total_exam_duration')) {
                $data['total_exam_duration'] = $request->total_exam_duration;
            }

            StudentAssessmentAnswer::create($data);
        }

        if ($request->status_answer === 'submitted') {

            $totalSubmitted = StudentAssessmentAnswer::where('student_id', $userId)
                ->where('school_assessment_id', $assessmentId)
                ->where('status_answer', 'submitted')
                ->count();

            if ($totalSubmitted >= $totalQuestions) {
                Cache::lock("exam_summary_lock_{$assessmentId}_{$userId}", 10)->get(function () use ($request, $userId, $assessmentId, $assessment) {
                    if ($request->filled('total_exam_duration')) {
                        StudentAssessmentAnswer::where('student_id', $userId)
                            ->where('school_assessment_id', $assessmentId)
                            ->update([
                                'total_exam_duration' => $request->total_exam_duration
                            ]);
                    }

                    $this->summaryService->updateStudentAssessmentSummary($userId, $assessment);
                });
            }
        }

        $attempt = StudentAssessmentAttempt::where('student_id', Auth::id())->where('school_assessment_id', $assessmentId)->first();

        // answering counts as activity: keeps the proctor's "last active" fresh and
        // stops the heartbeat-gap rule from striking a student busy writing an answer
        if ($attempt && $attempt->status === 'in_progress' && !$attempt->proctor_paused_at) {
            $attempt->update(['last_heartbeat_at' => now()]);
        }

        // Only the explicit auto-submit flows (timer expiry, blocked, manual end)
        // may change the attempt status. Per-question saves embed a hidden
        // status_attempt=submitted field — honoring it here would end the exam
        // on the first draft save and permanently disable the anti-cheat.
        if ($attempt && $attempt->status === 'in_progress' && $request->boolean('auto_submit') && $request->filled('status_attempt')) {

            $attempt->update([
                'status' => $request->status_attempt
            ]);

        }

        return response()->json([
            'status' => 'success',
            'message' => 'Jawaban berhasil disimpan',
        ]);
    }

    public function studentAssessmentExamEnd(Request $request, $role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId, $semester, $assessmentId)
    {
        $userId = Auth::id();

        // Gunakan cache lock per student & assessment agar tidak terjadi double-commit bersamaan
        $lockKey = "exam_end_lock_{$assessmentId}_{$userId}";
        $lock = Cache::lock($lockKey, 15);

        if (!$lock->get()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Proses pengakhiran ujian sedang berlangsung atau telah selesai.'
            ]);
        }

        try {
            $assessment = SchoolAssessment::findOrFail($assessmentId);
            $schoolPartner = SchoolPartner::findOrFail($assessment->school_partner_id);

            $timezone = TimezoneHelper::getSchoolTimezone($schoolPartner);
            $endDate = TimezoneHelper::parse($assessment->end_date, $timezone);
            $now = TimezoneHelper::now($timezone);

            $attempt = StudentAssessmentAttempt::where('student_id', $userId)
                ->where('school_assessment_id', $assessmentId)
                ->first();

            if ($attempt && $attempt->expire_time) {
                $attemptExpire = $attempt->expire_time;
                if ($attemptExpire && (!$endDate || $attemptExpire->lt($endDate))) {
                    $endDate = $attemptExpire->copy()->addMinute();
                }
            }

            // Finalization is one-shot: replaying /end must not rewrite answers.
            if ($attempt && ($attempt->finalized_at || in_array($attempt->status, ['submitted', 'timeout'], true))) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Assessment sudah diselesaikan.',
                    'status_attempt' => $attempt->status,
                ]);
            }

            $isExpired = $endDate && $now->greaterThan($endDate);
            $statusAttempt = $request->input('status_attempt', ($isExpired ? 'timeout' : 'submitted'));
            if (!in_array($statusAttempt, ['timeout', 'submitted', 'cheating'])) {
                $statusAttempt = $isExpired ? 'timeout' : 'submitted';
            }

            $totalExamDuration = $request->input('total_exam_duration');

            DB::transaction(function () use ($userId, $assessmentId, $assessment, $request, $statusAttempt, $totalExamDuration) {
                // 1. Simpan jawaban aktif saat ini jika ada yang dikirim dan belum tersimpan
                $currentQuestionId = $request->input('current_question_id');
                $currentAnswerValue = $request->input('current_answer_value');
                $currentAnswerDuration = $request->input('current_answer_duration', 0);

                if ($currentQuestionId) {
                    $schoolQuestion = SchoolAssessmentQuestion::with('lmsQuestionBank')->find($currentQuestionId);
                    if ($schoolQuestion && $schoolQuestion->lmsQuestionBank) {
                        $question = $schoolQuestion->lmsQuestionBank;

                        $answerData = $currentAnswerValue;
                        if (is_string($answerData)) {
                            $decoded = json_decode($answerData, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $answerData = $decoded;
                            }
                        }
                        if ($answerData === '' || $answerData === [] || $answerData === null) {
                            $answerData = null;
                        }

                        $score = $this->calculateSingleQuestionScore($assessment, $schoolQuestion, $question, $answerData, $userId);

                        $existingCurrentAnswer = StudentAssessmentAnswer::where('student_id', $userId)
                            ->where('school_assessment_id', $assessmentId)
                            ->where('school_assessment_question_id', $currentQuestionId)
                            ->first();

                        if ($existingCurrentAnswer) {
                            $updateData = [
                                'status_answer' => 'submitted',
                                'answer_duration' => $currentAnswerDuration ?: $existingCurrentAnswer->answer_duration,
                            ];
                            if ($answerData !== null) {
                                $updateData['answer_value'] = $answerData;
                                $updateData['question_score'] = $score;
                            }
                            $existingCurrentAnswer->update($updateData);
                        } else {
                            StudentAssessmentAnswer::create([
                                'student_id' => $userId,
                                'school_assessment_id' => $assessmentId,
                                'school_assessment_question_id' => $currentQuestionId,
                                'answer_value' => $answerData,
                                'question_score' => $score,
                                'answer_duration' => $currentAnswerDuration,
                                'status_answer' => 'submitted',
                                'grading_status' => $question->tipe_soal === 'ESSAY' ? 'pending' : null,
                            ]);
                        }
                    }
                }

                // 2. Ubah semua jawaban 'draft' menjadi 'submitted' sekaligus dalam 1 query
                StudentAssessmentAnswer::where('student_id', $userId)
                    ->where('school_assessment_id', $assessmentId)
                    ->where('status_answer', 'draft')
                    ->update([
                        'status_answer' => 'submitted'
                    ]);

                // 3. Update total exam duration pada semua jawaban siswa sekaligus dalam 1 query
                if ($totalExamDuration !== null && $totalExamDuration !== '') {
                    StudentAssessmentAnswer::where('student_id', $userId)
                        ->where('school_assessment_id', $assessmentId)
                        ->update([
                            'total_exam_duration' => (int) $totalExamDuration
                        ]);
                }

                // 4. Update durasi per soal jika dikirim dari frontend
                $durations = $request->input('durations');
                if (is_array($durations)) {
                    foreach ($durations as $qId => $dur) {
                        if ($dur > 0) {
                            StudentAssessmentAnswer::where('student_id', $userId)
                                ->where('school_assessment_id', $assessmentId)
                                ->where('school_assessment_question_id', $qId)
                                ->where(function ($q) {
                                    $q->whereNull('answer_duration')->orWhere('answer_duration', '<=', 0);
                                })
                                ->update(['answer_duration' => (int) $dur]);
                        }
                    }
                }

                // 5. Isi soal yang belum pernah dijawab dengan jawaban kosong (bulk insert)
                $sourceAssessmentId = $assessment->id;
                $assessmentCategory = strtolower($assessment->assessment_category ?? '');
                if (in_array($assessmentCategory, ['remedial', 'susulan']) && $assessment->parent_assessment_id) {
                    $sourceAssessmentId = $assessment->parent_assessment_id;
                }

                $existingQuestionIds = StudentAssessmentAnswer::where('student_id', $userId)
                    ->where('school_assessment_id', $assessmentId)
                    ->pluck('school_assessment_question_id')
                    ->toArray();

                $allQuestionsQuery = SchoolAssessmentQuestion::where('school_assessment_id', $sourceAssessmentId);
                if ($assessmentCategory === 'remedial' && $assessment->parent_assessment_id) {
                    $previousAssessment = $this->remedialPreviousAssessment($assessment, $userId);
                    $cacheKeyWrong = "assessment-remedial-wrong-{$userId}-prev-" . ($previousAssessment->id ?? 'none');
                    $wrongBankIds = Cache::get($cacheKeyWrong, []);
                    if (!empty($wrongBankIds)) {
                        $allQuestionsQuery->whereIn('question_bank_id', $wrongBankIds);
                    }
                }

                $allQuestionIds = $allQuestionsQuery->pluck('id')->toArray();
                $missingQuestionIds = array_diff($allQuestionIds, $existingQuestionIds);

                if (!empty($missingQuestionIds)) {
                    $nowTimestamp = now();
                    $bulkInsert = [];
                    foreach ($missingQuestionIds as $missingQId) {
                        $bulkInsert[] = [
                            'student_id' => $userId,
                            'school_assessment_id' => $assessmentId,
                            'school_assessment_question_id' => $missingQId,
                            'answer_value' => null,
                            'question_score' => 0,
                            'status_answer' => 'submitted',
                            'grading_status' => null,
                            'teacher_feedback' => null,
                            'answer_duration' => 0,
                            'total_exam_duration' => $totalExamDuration ? (int) $totalExamDuration : null,
                            'created_at' => $nowTimestamp,
                            'updated_at' => $nowTimestamp,
                        ];
                    }
                    StudentAssessmentAnswer::insert($bulkInsert);
                }

                // 6. Update status attempt dalam 1 query
                $attempt = StudentAssessmentAttempt::where('student_id', $userId)
                    ->where('school_assessment_id', $assessmentId)
                    ->first();

                if ($attempt && in_array($attempt->status, ['in_progress', 'cheating'])) {
                    $updates = ['finalized_at' => now()];
                    if ($attempt->status !== 'cheating') {
                        $updates['status'] = $statusAttempt;
                    }
                    $attempt->update($updates);
                }

                // 7. Update summary nilai akhir siswa (cukup 1 kali)
                $this->summaryService->updateStudentAssessmentSummary($userId, $assessment);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Assessment berhasil diselesaikan.',
                'status_attempt' => $statusAttempt,
            ]);

        } finally {
            $lock->release();
        }
    }

    /**
     * Assessment before $assessment in its remedial chain (the parent plus remedials
     * the student has answered, counting $assessment itself). Every wrong-answer
     * cache key is built from this so form, answer and end always agree.
     */
    private function remedialPreviousAssessment(SchoolAssessment $assessment, int $userId): ?SchoolAssessment
    {
        $chain = SchoolAssessment::where(function ($q) use ($assessment) {
            $q->where('id', $assessment->parent_assessment_id)->orWhere('parent_assessment_id', $assessment->parent_assessment_id);
        })->where(function ($q) use ($assessment, $userId) {
            $q->where('id', $assessment->id)->orWhereHas('StudentAssessmentAnswer', function ($q) use ($userId) {
                $q->where('student_id', $userId);
            });
        })->orderBy('start_date')->pluck('id')->toArray();

        $currentIndex = array_search($assessment->id, $chain);

        if ($currentIndex === false || $currentIndex === 0) {
            return null;
        }

        return SchoolAssessment::find($chain[$currentIndex - 1]);
    }

    private function calculateSingleQuestionScore($assessment, $schoolQuestion, $question, $answerData, $userId)
    {
        if ($answerData === null) {
            return 0;
        }

        $assessmentCategory = strtolower($assessment->assessment_category ?? '');
        $isRemedial = $assessmentCategory === 'remedial';

        if ($isRemedial && $assessment->parent_assessment_id) {
            $previousAssessment = $this->remedialPreviousAssessment($assessment, $userId);
            $cacheKeyWrong = "assessment-remedial-wrong-{$userId}-prev-" . ($previousAssessment->id ?? 'none');
            $wrongBankIds = Cache::get($cacheKeyWrong, []);

            if (empty($wrongBankIds)) {
                $totalQuestions = SchoolAssessmentQuestion::where('school_assessment_id', $assessment->parent_assessment_id)->count();
            } else {
                $totalQuestions = count($wrongBankIds);
            }
        } else {
            $totalQuestions = SchoolAssessmentQuestion::where('school_assessment_id', $assessment->id)->count();
        }

        $scorePerQuestion = (float) $schoolQuestion->question_weight;
        $score = 0;

        switch ($question->tipe_soal) {
            case 'MCQ':
                $correctOption = $question->lmsQuestionOption()
                    ->where('is_correct', 1)
                    ->first();
                    if ($correctOption && $answerData === $correctOption->options_key) {
                        $score = $scorePerQuestion;
                    }
                break;

            case 'MCMA':
                if (is_array($answerData)) {
                    $correctOptions = $question->lmsQuestionOption()
                        ->where('is_correct', 1)
                        ->pluck('options_key')
                        ->map(fn($val) => trim((string)$val))
                        ->toArray();

                    $totalCorrect = count($correctOptions);

                    if ($totalCorrect > 0) {
                        $normalizedAnswers = array_map(fn($val) => trim((string)$val), $answerData);
                        $maxScore = $scorePerQuestion;
                        $wrongSelected = count(array_diff($normalizedAnswers, $correctOptions));

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
                    $correctPairs = $question->lmsQuestionOption()
                        ->get()
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
                        $score = $scorePerQuestion;
                    }
                }
                break;

            case 'PG_KOMPLEKS':
                $correctAnswers = $question->lmsQuestionOption()->get()->filter(function ($opt) {
                    return isset($opt->extra_data['side']) && $opt->extra_data['side'] === 'item';
                })
                ->mapWithKeys(function ($opt) {
                    return [$opt->options_key => $opt->extra_data['answer']];
                })->toArray();

                ksort($correctAnswers);
                if (is_array($answerData)) {
                    ksort($answerData);
                }

                if ($correctAnswers === $answerData) {
                    $score = $scorePerQuestion;
                }
                break;

            case 'ESSAY':
                $score = 0;
                break;
        }

        return $score;
    }

    // function submit essay image (for ckeditor & direct image attachments)
    public function storeImageEssay(Request $request) {
        $file = $request->file('upload') ?? $request->file('image');

        if (!$file) {
            return response()->json([
                'uploaded' => 0,
                'error' => ['message' => 'Tidak ada file gambar yang diunggah.']
            ], 422);
        }

        $validator = Validator::make(['file' => $file], [
            'file' => 'required|file|mimes:jpeg,jpg,png,gif,webp,heic,heif|max:10240'
        ], [
            'file.mimes' => 'Format file harus berupa gambar (JPG, PNG, GIF, WEBP, HEIC).',
            'file.max' => 'Ukuran gambar maksimal adalah 10 MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'uploaded' => 0,
                'error' => ['message' => $validator->errors()->first('file')],
                'message' => $validator->errors()->first('file')
            ], 422);
        }

        $targetDir = public_path('lms-assessment-submission/assessment-test');
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $originName = $file->getClientOriginalName();
        $rawFileName = pathinfo($originName, PATHINFO_FILENAME);
        $safeName = Str::slug(substr($rawFileName, 0, 50));
        if (empty($safeName)) {
            $safeName = 'answer_img';
        }
        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $finalName = $safeName . '_' . time() . '_' . Str::random(6) . '.' . $extension;

        $file->move($targetDir, $finalName);

        // Give the anti-cheat heartbeat an extended gap window: the native
        // camera/gallery picker suspends page JS for a while.
        AssessmentAntiCheatController::grantFileGrace();

        $url = "/lms-assessment-submission/assessment-test/$finalName";
        return response()->json([
            'fileName' => $finalName,
            'uploaded' => 1,
            'url' => $url,
            'status' => 'success'
        ]);
    }

    // function delete image essay (for ckeditor & attachment removal)
    public function deleteImageEssay(Request $request) {
        $request->validate([
            'imageUrl' => 'required|string',
        ]);

        $url = $request->imageUrl;
        $parsedPath = parse_url($url, PHP_URL_PATH) ?? $url;
        $relativePath = ltrim($parsedPath, '/\\');

        // Security check: restrict deletion only to the assessment-test upload directory
        $allowedPrefix = 'lms-assessment-submission/assessment-test/';
        if (!str_starts_with($relativePath, $allowedPrefix)) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $fullImagePath = public_path($relativePath);

        if (file_exists($fullImagePath) && is_file($fullImagePath)) {
            // Security check: resolve symlinks/.. to block path traversal outside the upload directory
            $realpath = realpath($fullImagePath);
            $allowedRealBase = realpath(public_path('lms-assessment-submission/assessment-test')) . DIRECTORY_SEPARATOR;
            if ($realpath === false || !str_starts_with($realpath, $allowedRealBase)) {
                return response()->json(['message' => 'Akses ditolak.'], 403);
            }

            unlink($fullImagePath);
            return response()->json(['message' => 'Gambar berhasil dihapus']);
        }

        return response()->json(['message' => 'Gambar tidak ditemukan'], 404);
    }

    public function studentProjectSubmission(Request $request, $role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId, $semester, $assessmentId)
    {
        $userId = Auth::id();
        $assessment = SchoolAssessment::findOrFail($assessmentId);

        $schoolPartner = SchoolPartner::findOrFail($assessment->school_partner_id);

        $timezone = TimezoneHelper::getSchoolTimezone($schoolPartner);

        $startDate = TimezoneHelper::parse($assessment->start_date, $timezone);
        $endDate = TimezoneHelper::parse($assessment->end_date, $timezone);
        $now = TimezoneHelper::now($timezone);

        $isExpired = $endDate && $now->greaterThan($endDate);
        $isBeforeStart = $startDate && $now->lessThan($startDate);

        if ($isBeforeStart) {
            return response()->json([
                'status' => 'not_started',
                'message' => 'Assessment belum dimulai.'
            ], 422);
        }

        if ($isExpired) {
            return response()->json([
                'status' => 'expired',
                'message' => 'Assessment sudah berakhir.'
            ], 422);
        }
    
        $validator = Validator::make($request->all(), [

            'submission_type' => [
                'required', 'in:file,text'
            ],

            'project_file' => [
                'nullable', 'required_if:submission_type,file', 'file', 'mimes:pdf', 'max:100000'
            ],

            'project_text' => [
                'nullable', 'required_if:submission_type,text'
            ]

        ], [
            'project_file.required_if' => 'Harap upload file assessment.',
            'project_file.mimes' => 'Format file tidak sesuai.',
            'project_file.max' => 'Ukuran file tidak boleh lebih dari 100 MB.',
            'project_text.required_if' => 'Harap isi deskripsi assessment.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $submissionType = $request->submission_type;

        $data = [
            'student_id' => Auth::id(),
            'school_assessment_id' => $assessmentId,
            'submission_type' => $submissionType
        ];

        if ($submissionType === 'file') {
            $file = $request->file("project_file");

            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assessment/assessment-file-submission'), $filename);

            $data['file_path'] = $filename;
            $data['original_filename'] = $file->getClientOriginalName();
        } else {
            $data['text_answer'] = $request->project_text;
        }

        StudentProjectSubmission::firstOrCreate(
            [
                'student_id' => Auth::id(),
                'school_assessment_id' => $assessmentId
            ],
            $data
        );

        $this->summaryService->updateStudentAssessmentSummary($userId, $assessment);

        return response()->json([
            'status' => 'success',
            'message' => 'Assessment berhasil disubmit.',
        ]);
    }

    public function studentResultAssessment($role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId, $semester, $assessmentId) 
    {
        $user = UserAccount::with(['StudentProfile','StudentSchoolClass'])->findOrFail(Auth::id());
        $classId = $user->StudentSchoolClass[0]->school_class_id;

        // GET ASSESSMENT
        $schoolAssessment = SchoolAssessment::with('SchoolClass')->find($assessmentId);

        if (!$schoolAssessment) {
            abort(404, 'Assessment tidak ditemukan.');
        }

        // Cek jika siswa memiliki attempt yang masih aktif (in_progress) dan waktu belum kadaluarsa
        $attempt = StudentAssessmentAttempt::where('student_id', $user->id)
            ->where('school_assessment_id', $assessmentId)
            ->first();

        $schoolPartner = SchoolPartner::find($schoolAssessment->school_partner_id);
        $timezone = TimezoneHelper::getSchoolTimezone($schoolPartner);
        $endDate = TimezoneHelper::parse($schoolAssessment->end_date, $timezone);
        $now = TimezoneHelper::now($timezone);

        if ($attempt && $attempt->expire_time) {
            $attemptExpire = $attempt->expire_time;
            if ($attemptExpire && (!$endDate || $attemptExpire->gt($endDate))) {
                $endDate = $attemptExpire->copy()->addMinute();
            }
        }

        $isExpired = $endDate && $now->greaterThan($endDate);

        if ($attempt && $attempt->status === 'in_progress' && !$isExpired) {
            return redirect()->route('lms.studentAssessmentExam.view', [
                $role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId, $semester, $assessmentId
            ]);
        }

        // ROOT (untuk summary)
        $rootAssessmentId = $schoolAssessment->parent_assessment_id ?? $schoolAssessment->id;

        // SUMMARY
        $summary = StudentAssessmentSummary::where('student_id', $user->id)->where('root_assessment_id', $rootAssessmentId)->first();

        // DATA JAWABAN USER
        $answers = StudentAssessmentAnswer::where('student_id',$user->id)->where('school_assessment_id', $assessmentId)->get();

        $totalQuestionsExam = $answers->count();

        $totalCorrect = $answers->whereNotNull('answer_value')->where('question_score', '>', 0)->count();
        $totalWrong = $answers->where('question_score', 0)->count();
        $totalUnanswered = $answers->whereNull('answer_value')->count();
        $totalAnswered = $answers->whereNotNull('answer_value')->count();
        $totalPendingEssay = $answers->where('status_answer', 'submitted')->where('grading_status', 'pending')->count();

        // SCORE
        $rawScore = $answers->whereNotNull('question_score')->sum('question_score');

        // FINAL SCORE
        $category = strtolower($schoolAssessment->assessment_category ?? 'main');

        if ($summary) {

            if ($category === 'main') {
                $finalScore = $summary->main_score ?? $rawScore;
            }

            elseif ($category === 'susulan') {
                $finalScore = $summary->susulan_score ?? $rawScore;
            }

            elseif ($category === 'remedial') {

                // kalau kamu pakai JSON remedial_score
                $remedials = $summary->remedial_score ?? [];

                $currentRemedial = collect($remedials)
                    ->firstWhere('assessment_id', $assessmentId);

                $finalScore = $currentRemedial['score'] ?? $rawScore;
            }

            elseif ($category === 'pengayaan') {
                $finalScore = $summary->pengayaan_score ?? $rawScore;
            }

            else {
                $finalScore = $rawScore;
            }

        } else {
            $finalScore = $rawScore;
        }

        $finalScore = round($finalScore, 2);

        // final global score (lulus / tidak)
        $finalScoreGlobal = null;

        if ($summary) {
            if ($category === 'main') {
                $finalScoreGlobal = $summary->main_score ?? $finalScore;
            }

            elseif ($category === 'susulan') {
                $finalScoreGlobal = $summary->susulan_score ?? $finalScore;
            }

            elseif ($category === 'remedial') {
                $finalScoreGlobal = $finalScore;
            }

            elseif ($category === 'pengayaan') {
                $finalScoreGlobal = $summary->pengayaan_score ?? $finalScore;
            }

            else {
                $finalScoreGlobal = $finalScore;
            }
        }

        // fallback kalau semua null
        $finalScoreGlobal = $finalScoreGlobal ?? $finalScore;

        $maxScore = 100;
        $percentage = $maxScore > 0 ? round(($finalScore / $maxScore) * 100, 2) : 0;

        // DURASI USER
        $durations = $answers->where('answer_duration','>',0)->pluck('answer_duration');
        $fastestRaw = $durations->min() ?? 0;
        $slowestRaw = $durations->max() ?? 0;
        $totalDurationRaw = $answers->where('answer_duration','>',0)->pluck('total_exam_duration')->first() ?? 0;

        $formatDuration = function($seconds){
            if($seconds <= 0) return '-';

            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $secondsRemain = $seconds % 60;

            $parts = [];
            if($hours > 0) $parts[] = $hours.' jam';
            if($minutes > 0) $parts[] = $minutes.' menit';
            if($secondsRemain > 0) $parts[] = $secondsRemain.' detik';

            return implode(' ',$parts);
        };

        $fastest = $formatDuration($fastestRaw);
        $slowest = $formatDuration($slowestRaw);
        $totalDuration = $formatDuration($totalDurationRaw);

        // CONFIDENCE SCORE
        $answered = $totalCorrect + $totalWrong;
        $accuracy = $answered > 0 ? $totalCorrect / $answered : 0;
        $completion = $totalQuestionsExam > 0 ? $answered / $totalQuestionsExam : 0;
        $confidence = round(($accuracy * 0.8 + $completion * 0.2) * 100, 2);

        // TOTAL SISWA
        $totalStudents = StudentSchoolClass::where('school_class_id',$classId)->count();

        $participants = StudentAssessmentAnswer::where('school_assessment_id', $assessmentId)
        ->whereIn('student_id', function ($q) use ($classId) {
            $q->select('student_id')->from('student_school_classes')->where('school_class_id', $classId);
        })->distinct('student_id')->count('student_id');

        // RANKING DURASI
        $allDurations = StudentAssessmentAnswer::where('school_assessment_id',$assessmentId)->where('answer_duration','>', 0)->selectRaw('student_id, SUM(answer_duration) as total_duration')
        ->groupBy('student_id')->orderBy('total_duration')->get();

        $uniqueTotalDurations = $allDurations->pluck('total_duration')->unique()->sort()->values();
        $userTotalDuration = $allDurations->firstWhere('student_id',$user->id)->total_duration ?? null;
        $rankDuration = $userTotalDuration !== null ? $uniqueTotalDurations->search($userTotalDuration) + 1 : null;

        $percentileDuration = ($rankDuration !== null) ? ($participants == 1 ? 100 : round((($participants - $rankDuration) / ($participants - 1)) * 100, 2)) : 0;

        // FASTEST
        $fastestQuestion = StudentAssessmentAnswer::where('school_assessment_id',$assessmentId)->where('answer_duration','>', 0)->selectRaw('student_id, MIN(answer_duration) as duration')
        ->groupBy('student_id')->orderBy('duration')->get();

        $uniqueFastestDurations = $fastestQuestion->pluck('duration')->unique()->sort()->values();
        $userFastestDuration = $fastestQuestion->firstWhere('student_id',$user->id)->duration ?? null;
        $rankFastest = $userFastestDuration !== null ? $uniqueFastestDurations->search($userFastestDuration) + 1 : null;

        $percentileFastest = ($rankFastest !== null) ? ($participants == 1 ? 100 : round((($participants - $rankFastest) / ($participants - 1)) * 100, 2)) : 0;

        // SLOWEST
        $slowestQuestion = StudentAssessmentAnswer::where('school_assessment_id',$assessmentId)->where('answer_duration','>',0)->selectRaw('student_id, MAX(answer_duration) as duration')
        ->groupBy('student_id')->orderBy('duration')->get();

        $uniqueSlowestDuration = $slowestQuestion->pluck('duration')->unique()->sortDesc()->values();
        $userSlowestDuration = $slowestQuestion->firstWhere('student_id',$user->id)->duration ?? null;
        $rankSlowest = $userSlowestDuration !== null ? $uniqueSlowestDuration->search($userSlowestDuration) + 1 : null;

        $percentileSlowest = ($rankSlowest !== null) ? ($participants == 1 ? 100 : round((($participants - $rankSlowest) / ($participants - 1)) * 100, 2)) : 0;

        // CONFIDENCE RANK
        $totalQuestions = StudentAssessmentAnswer::where('school_assessment_id',$assessmentId)->distinct('school_assessment_question_id')->count('school_assessment_question_id');

        $allConfidence = StudentAssessmentAnswer::where('school_assessment_id', $assessmentId)->selectRaw('student_id,
            SUM(CASE WHEN question_score > 0 THEN 1 ELSE 0 END) as correct,
            SUM(CASE WHEN status_answer = "submitted" THEN 1 ELSE 0 END) as answered,
            SUM(answer_duration) as total_duration')
        ->groupBy('student_id')->get();

        $durations = $allConfidence->pluck('total_duration');
        $minDuration = $durations->min();
        $maxDuration = $durations->max();

        $allConfidence = $allConfidence->map(function($row) use ($totalQuestions,$minDuration,$maxDuration){

            $duration = $row->total_duration ?? 0;
            $accuracy = $row->answered > 0 ? $row->correct / $row->answered : 0;
            $completion = $totalQuestions > 0 ? min($row->answered / $totalQuestions, 1) : 0;

            $speed = ($maxDuration == $minDuration) ? 1 : ($maxDuration - $duration) / ($maxDuration - $minDuration);
            $speed = max(min($speed,1),0);

            $confidence = min(($accuracy * 0.7 + $completion * 0.2 + $speed * 0.1) * 100, 100);

            return [
                'student_id'=> $row->student_id,
                'confidence'=> round($confidence,2)
            ];
        })->sortByDesc('confidence')->values();

        $uniqueConfidence = $allConfidence->pluck('confidence')->unique()->sortDesc()->values();
        $userConfidenceRow = $allConfidence->firstWhere('student_id',$user->id);
        $confidence = $userConfidenceRow['confidence'] ?? null;

        $rankConfidence = $confidence !== null ? $uniqueConfidence->search($confidence) + 1 : null;

        $percentileConfidence = ($rankConfidence !== null) ? ($participants == 1 ? 100 : round((($participants - $rankConfidence) / ($participants - 1)) * 100, 2)) : 0;

        $isFullyGraded = $totalPendingEssay === 0;

        // KKM
        $kelasId = $schoolAssessment->SchoolClass?->kelas_id;
        $schoolYear = $schoolAssessment->SchoolClass?->tahun_ajaran;

        $kkm = SubjectPassingGradeCriteria::where('mapel_id', $schoolAssessment->mapel_id)->where('kelas_id', $kelasId)->where('school_year', $schoolYear)->latest()->value('kkm_value') ?? 75;

        $hasAttempt = $answers->count() > 0;

        return view('features.lms.student.assessment.student-assessment-result-test', compact('role', 'schoolName', 'schoolId', 'curriculumId', 'mapelId', 'assessmentTypeId', 'semester',
            'assessmentId', 'user', 'totalQuestions', 'totalCorrect', 'totalWrong', 'totalUnanswered', 'totalPendingEssay', 'finalScore', 'percentage', 'isFullyGraded', 'schoolAssessment',
            'fastest', 'slowest', 'totalDuration', 'confidence', 'percentileDuration', 'percentileFastest', 'percentileSlowest', 'percentileConfidence', 'participants','totalStudents','kkm',
            'hasAttempt', 'finalScoreGlobal', 'category'
        ));
    }

    public function studentProjectResult($role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId, $semester, $assessmentId)
    {
        $user = Auth::user();

        $submission = StudentProjectSubmission::where('student_id', $user->id)
            ->where('school_assessment_id', $assessmentId)
            ->first();

        $score = $submission->score ?? 0;

        // cek apakah sudah dinilai
        $isFullyGraded = $submission && $submission->grading_status === 'graded';

        $schoolAssessment = SchoolAssessment::find($assessmentId);

        return view('features.lms.student.assessment.student-project-assessment-result', compact('role', 'schoolName', 'schoolId', 'curriculumId', 
            'mapelId', 'assessmentTypeId', 'semester', 'assessmentId', 'submission', 'score', 'isFullyGraded', 'schoolAssessment'));
    }
}

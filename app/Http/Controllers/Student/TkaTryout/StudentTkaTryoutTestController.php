<?php

namespace App\Http\Controllers\Student\TkaTryout;

use App\Helpers\TimezoneHelper;
use App\Http\Controllers\Controller;
use App\Models\LmsQuestionBank;
use App\Models\Mapel;
use App\Models\SchoolPartner;
use App\Models\StudentTkaAttempt;
use App\Models\TkaTryoutAnswer;
use App\Models\TkaTryoutPeriodSchOverride;
use App\Models\TkaTryoutSession;
use App\Models\TkaTryoutSubject;
use App\Models\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StudentTkaTryoutTestController extends Controller
{
    public function index($role, $schoolName, $schoolId, $periodId, $sessionId, $subjectId)
    {
        return view('features.lms.student.tka-tryout.tka-tryout-test', compact('role', 'schoolName', 'schoolId', 'periodId', 'sessionId', 'subjectId'));
    }

    private function calculateAttemptResult($attempt)
    {
        $answers = TkaTryoutAnswer::where('attempt_id', $attempt->id)->get();

        $questionIds = $attempt->question_order ?? [];

        $questions = LmsQuestionBank::with('LmsQuestionOption')->whereIn('id', $questionIds)->get()->keyBy('id');

        $totalQuestion = (int) $attempt->total_question;

        $submittedAnswers = $answers->filter(function ($answer) {
            return $answer->status_answer === 'submitted';
        });

        $totalAnswered = $submittedAnswers->filter(function ($answer) {
            return $answer->answer_value !== null;
        })->count();

        $totalCorrect = $submittedAnswers->filter(function ($answer) {
            return (float) $answer->question_score > 0;
        })->count();

        $totalWrong = $submittedAnswers->filter(function ($answer) {
            return (float) $answer->question_score <= 0 && $answer->answer_value !== null;
        })->count();

        $totalUnanswered = max($totalQuestion - $totalAnswered, 0);

        $totalScore = $submittedAnswers->sum(function ($answer) {
            return (float) $answer->question_score;
        });

        $totalMaxScore = 0;

        foreach ($questionIds as $questionId) {

            $question = $questions->get($questionId);

            if (!$question) {
                continue;
            }

            $type = strtoupper($question->tipe_soal ?? '');

            if (in_array($type, ['MCQ', 'MCMA'])) {
                $totalMaxScore += 1;
            }

            if ($type === 'PG_KOMPLEKS') {
                $totalMaxScore += 2;
            }

            if ($type === 'MATCHING') {

                $totalPair = $question->LmsQuestionOption->filter(function ($option) {
                    return isset($option->extra_data['side']) && $option->extra_data['side'] === 'left';
                })->count();

                $totalMaxScore += $totalPair === 3 ? 2 : 3;
            }
        }

        $finalScore = $totalMaxScore > 0 ? round(($totalScore / $totalMaxScore) * 100, 2) : 0;

        return [
            'total_question' => $totalQuestion,
            'total_answered' => $totalAnswered,
            'total_correct' => $totalCorrect,
            'total_wrong' => $totalWrong,
            'total_unanswered' => $totalUnanswered,
            'total_score' => $totalScore,
            'total_max_score' => $totalMaxScore,
            'final_score' => $finalScore,
        ];
    }

    public function studentTkaTryoutTestForm($role, $schoolName, $schoolId, $periodId, $sessionId, $subjectId)
    {
        $user = UserAccount::with('StudentProfile')->find(Auth::id());

        $subject = Mapel::findOrFail($subjectId);

        $schoolPartner = SchoolPartner::findOrFail($schoolId);
        $timezone = TimezoneHelper::getSchoolTimezone($schoolPartner);
        $timezoneLabel = TimezoneHelper::getTimezoneLabel($timezone);

        $session = TkaTryoutSession::where('id', $sessionId)->where('tka_tryout_period_id', $periodId)->first();

        $sessionStarted = false;
        $sessionEnded = false;
        $sessionStart = null;
        $sessionEnd = null;
        $now = TimezoneHelper::now($timezone);

        if ($session) {
            $sessionStart = TimezoneHelper::parse($session->session_date->format('Y-m-d') . ' ' . $session->start_time->format('H:i:s'), $timezone);
            $sessionEnd = TimezoneHelper::parse($session->session_date->format('Y-m-d') . ' ' . $session->end_time->format('H:i:s'), $timezone);

            $sessionStarted = $now->greaterThanOrEqualTo($sessionStart);
            $sessionEnded = $now->greaterThan($sessionEnd);
        }

        $override = TkaTryoutPeriodSchOverride::where('tka_tryout_period_id', $periodId)->where('school_partner_id', $schoolId)->first();

        $subjectsQuery = TkaTryoutSubject::query()->where('tka_tryout_period_id', $periodId);

        if ($override) {
            $subjectsQuery->where('tka_tryout_period_sch_override_id', $override->id);
        } else {
            $subjectsQuery->whereNull('tka_tryout_period_sch_override_id');
        }

        $tryoutSubject = $subjectsQuery->where('subject_id', $subjectId)->first();

        $duration = $tryoutSubject ? ((int) $tryoutSubject->duration) * 60 : 0;

        $attempt = StudentTkaAttempt::where('student_id', $user->id)->where('kelas_id', $subject->kelas_id)->where('mapel_id', $subjectId)
        ->where('tka_tryout_session_id', $sessionId)->where('attempt_type', 'tryout')->where('status', 'active')->latest()->first();

        if (!$attempt) {
            return response()->json([
                'has_attempt' => false,
                'attempt' => null,
                'questionsAnswer' => [],
                'data' => [],
                'user' => $user,
                'duration' => $duration,
                'session' => $session ? [
                    'id' => $session->id,
                    'session_date' => $session->session_date?->format('Y-m-d'),
                    'start_time' => $session->start_time?->format('H:i'),
                    'end_time' => $session->end_time?->format('H:i'),
                    'start_datetime' => $sessionStart?->format('Y-m-d\TH:i:sP'),
                    'end_datetime' => $sessionEnd?->format('Y-m-d\TH:i:sP'),
                ] : null,
                'session_started' => $sessionStarted,
                'session_ended' => $sessionEnded,
                'timezone' => $timezone,
                'timezone_label' => $timezoneLabel,
                'server_now' => $now->format('Y-m-d H:i:sP'),
            ]);
        }

        $questionIds = $attempt->question_order ?? [];

        if (empty($questionIds)) {
            return response()->json([
                'message' => 'Question order tidak ditemukan.'
            ], 500);
        }

        $questions = LmsQuestionBank::with(['LmsQuestionOption', 'Mapel'])->whereIn('id', $questionIds)->get()->sortBy(function ($question) use ($questionIds) {
                return array_search($question->id, $questionIds);
        })->values();

        $questions->transform(function ($question) use ($attempt) {

            $type = strtoupper($question->tipe_soal ?? '');

            $options = collect($question->LmsQuestionOption ?? []);

            if ($options->isEmpty()) {
                return $question;
            }

            if (in_array($type, ['MCQ', 'MCMA'])) {

                $sorted = $options->sortBy(function ($option) use ($attempt, $question) {
                    return md5($attempt->id . '_' . $question->id . '_' . $option->id);
                })->values();

                $question->LmsQuestionOption = $sorted;
            }

            if ($type === 'MATCHING') {

                $left = $options->filter(function ($opt) {
                    return isset($opt->extra_data['side']) && $opt->extra_data['side'] === 'left';
                })->values();

                $right = $options->filter(function ($opt) {
                    return isset($opt->extra_data['side']) && $opt->extra_data['side'] === 'right';
                })->sortBy(function ($option) use ($attempt, $question) {
                    return md5($attempt->id . '_' . $question->id . '_' . $option->id);
                })->values();

                $shuffled = collect();

                foreach ($left as $l) {
                    $shuffled->push($l);
                }

                foreach ($right as $r) {
                    $shuffled->push($r);
                }

                $question->LmsQuestionOption = $shuffled;
            }

            if ($type === 'PG_KOMPLEKS') {

                $items = $options->filter(function ($opt) {
                    return isset($opt->extra_data['side']) && $opt->extra_data['side'] === 'item';
                })->sortBy(function ($option) use ($attempt, $question) {
                    return md5($attempt->id . '_' . $question->id . '_' . $option->id);
                })->values();

                $right = $options->filter(function ($opt) {
                    return isset($opt->extra_data['side']) && $opt->extra_data['side'] === 'category';
                })->sortBy(function ($option) use ($attempt, $question) {
                    return md5($attempt->id . '_' . $question->id . '_' . $option->id);
                })->values();

                $shuffled = collect();

                foreach ($items as $item) {
                    $shuffled->push($item);
                }

                foreach ($right as $cat) {
                    $shuffled->push($cat);
                }

                $question->LmsQuestionOption = $shuffled;
            }

            return $question;
        });

        $questionsAnswer = collect();

        if ($attempt) {
            $questionsAnswer = TkaTryoutAnswer::with(['StudentTkaAttempt'])->where('attempt_id', $attempt->id)->get()->mapWithKeys(function ($item) {
                
                $data = $item->attributesToArray();

                if (is_string($data['answer_value'])) {
                    $decoded = json_decode($data['answer_value'], true);

                    if (json_last_error() === JSON_ERROR_NONE) {
                        $data['answer_value'] = $decoded;
                    }
                }

                $question = LmsQuestionBank::with('LmsQuestionOption')->find($item->question_id);

                $isCorrect = false;

                if ($question) {

                    $type = $question->tipe_soal;

                    $correctOptions = $question->LmsQuestionOption->where('is_correct', 1)->pluck('options_key')->values()->toArray();

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

                            $correctPairs = $question->LmsQuestionOption->filter(function ($opt) {
                                return isset($opt->extra_data['side']) && $opt->extra_data['side'] === 'left';
                            })->mapWithKeys(function ($opt) {
                                return [
                                    trim($opt->options_key) => trim($opt->extra_data['pair_with'] ?? '')
                                ];
                            })->toArray();

                            $normalizedStudentAnswer = collect($studentAnswer)->mapWithKeys(function ($value, $key) {
                                return [trim($key) => trim($value)];
                            })->toArray();

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

                            $correctPairs = $question->LmsQuestionOption->filter(function ($opt) {
                                return isset($opt->extra_data['side']) && $opt->extra_data['side'] === 'item';
                            })->mapWithKeys(function ($opt) {
                                return [
                                    trim($opt->options_key) => trim($opt->extra_data['answer'] ?? '')
                                ];
                            })->toArray();

                            $normalizedStudentAnswer = collect($studentAnswer)->mapWithKeys(function ($value, $key) {
                                return [trim($key) => trim($value)];
                            })->toArray();

                            ksort($correctPairs);
                            ksort($normalizedStudentAnswer);

                            $isCorrect = $correctPairs === $normalizedStudentAnswer;
                        }
                    }
                }

                $data['is_correct'] = $isCorrect;

                return [
                    $item->question_id => $data
                ];
            });
        }

        $result = $this->calculateAttemptResult($attempt);

        return response()->json([
            'has_attempt' => true,
            'attempt' => [
                'id' => $attempt->id,
                'status' => $attempt->status,
                'kelas_id' => $attempt->kelas_id,
                'mapel_id' => $attempt->mapel_id,
                'total_question' => $attempt->total_question,
            ],

            'session' => $session ? [
                'id' => $session->id,
                'session_date' => $session->session_date?->format('Y-m-d'),
                'start_time' => $session->start_time?->format('H:i'),
                'end_time' => $session->end_time?->format('H:i'),
                'start_datetime' => $sessionStart?->format('Y-m-d\TH:i:sP'),
                'end_datetime' => $sessionEnd?->format('Y-m-d\TH:i:sP'),
            ] : null,

            'session_started' => $sessionStarted,
            'session_ended' => $sessionEnded,
            'timezone' => $timezone,
            'timezone_label' => $timezoneLabel,
            'server_now' => $now->format('Y-m-d H:i:sP'),
            'questionsAnswer' => $questionsAnswer,
            'data' => $questions,
            'user' => $user,
            'duration' => $duration,
            'result' => $result
        ]);
    }

    private function generateQuestionOrder($role, $schoolName, $schoolId, $periodId, $sessionId, $classId, $subjectId, $totalQuestion = null)
    {
        $baseQuery = LmsQuestionBank::where('kelas_id', $classId)->where('mapel_id', $subjectId)->where('status_bank_soal', 'Publish')
        ->where('question_category', 'TKA')->where('tipe_soal', '!=', 'ESSAY');

        $override = TkaTryoutPeriodSchOverride::where('tka_tryout_period_id', $periodId)->where('school_partner_id', $schoolId)->first();

        $subjectsQuery = TkaTryoutSubject::query()->where('tka_tryout_period_id', $periodId);

        if ($override) {
            $subjectsQuery->where('tka_tryout_period_sch_override_id', $override->id);
        } else {
            $subjectsQuery->whereNull('tka_tryout_period_sch_override_id');
        }

        $totalQuestion = $subjectsQuery->where('subject_id', $subjectId)->value('total_question');

        $totalQuestion = (int) $totalQuestion;

        if ($totalQuestion <= 0) {
            return [];
        }

        $questions = $baseQuery->get();

        if ($questions->isEmpty()) {
            return [];
        }

        // Acak kembali agar soal wajib tidak selalu muncul di awal
        return $questions->shuffle()->take($totalQuestion)->pluck('id')->values()->toArray();
    }

    public function studentTkaTryoutStartTest($role, $schoolName, $schoolId, $periodId, $sessionId, $subjectId)
    {
        $userId = Auth::id();

        $schoolPartner = SchoolPartner::findOrFail($schoolId);
        $timezone = TimezoneHelper::getSchoolTimezone($schoolPartner);

        $session = TkaTryoutSession::where('id', $sessionId)->where('tka_tryout_period_id', $periodId)->firstOrFail();

        $sessionStart = TimezoneHelper::parse($session->session_date->format('Y-m-d') . ' ' . $session->start_time->format('H:i:s'), $timezone);
        $sessionEnd = TimezoneHelper::parse($session->session_date->format('Y-m-d') . ' ' . $session->end_time->format('H:i:s'), $timezone);

        $now = TimezoneHelper::now($timezone);

        if ($now->lt($sessionStart)) {
            return response()->json([
                'status' => 'not_started',
                'message' => 'Sesi Tryout TKA belum dimulai.'
            ], 422);
        }

        if ($now->gt($sessionEnd)) {
            return response()->json([
                'status' => 'expired',
                'message' => 'Sesi Tryout TKA sudah berakhir.'
            ], 422);
        }

        $subject = Mapel::findOrFail($subjectId);

        StudentTkaAttempt::where('student_id', $userId)->where('session_id', $sessionId)->where('kelas_id', $subject->kelas_id)->where('mapel_id', $subjectId)
        ->where('attempt_type', 'tryout')->where('status', 'active')->update([
            'status' => 'inactive'
        ]);

        $questionOrder = $this->generateQuestionOrder($role, $schoolName, $schoolId, $periodId, $sessionId, $subject->kelas_id, $subjectId);

        $attempt = StudentTkaAttempt::create([
            'student_id' => $userId,
            'kelas_id' => $subject->kelas_id,
            'mapel_id' => $subjectId,
            'tka_tryout_session_id' => $sessionId,
            'total_question' => count($questionOrder),
            'question_order' => $questionOrder,
            'status' => 'active',
            'attempt_type' => 'tryout',
        ]);

        return response()->json([
            'status' => 'success',
            'attempt_id' => $attempt->id,
            'timezone' => $timezone,
            'timezone_label' => TimezoneHelper::getTimezoneLabel($timezone),
            'server_now' => $now->format('Y-m-d H:i:sP'),
            'session_start' => $sessionStart->format('Y-m-d H:i:sP'),
            'session_end' => $sessionEnd->format('Y-m-d H:i:sP'),
        ]);
    }

    public function studentTkaTryoutSubmitAnswer(Request $request, $role, $schoolName, $schoolId, $periodId, $sessionId, $subjectId, $attemptId)
    {
        $userId = Auth::id();

        $attempt = StudentTkaAttempt::where('id', $attemptId)->where('student_id', $userId)->where('attempt_type', 'tryout')->where('status', 'active')->firstOrFail();

        $schoolPartner = SchoolPartner::findOrFail($schoolId);
        $timezone = TimezoneHelper::getSchoolTimezone($schoolPartner);

        $session = TkaTryoutSession::where('id', $sessionId)->where('tka_tryout_period_id', $periodId)->firstOrFail();

        $sessionStart = TimezoneHelper::parse($session->session_date->format('Y-m-d') . ' ' . $session->start_time->format('H:i:s'), $timezone);
        $sessionEnd = TimezoneHelper::parse($session->session_date->format('Y-m-d') . ' ' . $session->end_time->format('H:i:s'), $timezone);

        $now = TimezoneHelper::now($timezone);

        if (!$request->auto_submit) {
            if ($now->lt($sessionStart)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Sesi Tryout TKA belum dimulai.'
                ], 422);
            }

            if ($now->gt($sessionEnd)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Sesi Tryout TKA sudah berakhir.'
                ], 422);
            }
        }

        $validator = Validator::make($request->all(), [
            'question_id' => 'required|exists:lms_question_banks,id',
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

        $question = LmsQuestionBank::findOrFail($request->question_id);

        $answer = TkaTryoutAnswer::where('attempt_id', $attemptId)->where('question_id', $request->question_id)->first();

        $answerData = $request->answer_value;

        if (is_string($answerData)) {
            $decoded = json_decode($answerData, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $answerData = $decoded;
            }
        }

        if ($answerData === '' || $answerData === [] || $answerData === null) {
            $answerData = null;
        }

        $isCorrect = false;
        $score = 0;

        if ($answerData !== null) {
            switch ($question->tipe_soal) {
                case 'MCQ':

                    $correctOption = $question->lmsQuestionOption()->where('is_correct', 1)->first();

                    $isCorrect = $correctOption && $correctOption->options_key == $answerData;

                    $score = $isCorrect ? 1 : 0;

                break;

                case 'MCMA':

                    if (is_array($answerData)) {

                        $correctOptions = $question->lmsQuestionOption()->where('is_correct', 1)->pluck('options_key')->toArray();

                        sort($correctOptions);
                        sort($answerData);

                        $isCorrect = ($correctOptions == $answerData);

                        $score = $isCorrect ? 1 : 0;
                    }

                break;

                case 'MATCHING':

                    if (is_array($answerData)) {

                        $correctPairs = $question->lmsQuestionOption()->get()->filter(function ($opt) {
                            return isset($opt->extra_data['side']) && $opt->extra_data['side'] === 'left';
                        })->mapWithKeys(function ($opt) {
                            return [
                                trim($opt->options_key) => trim($opt->extra_data['pair_with'] ?? '')
                            ];
                        })->toArray();

                        $normalizedAnswer = collect($answerData)->mapWithKeys(function ($value, $key) {
                            return [
                                trim($key) => trim($value)
                            ];
                        })->toArray();

                        $totalPair = count($correctPairs);

                        $correctCount = 0;

                        foreach ($correctPairs as $key => $correctValue) {

                            if (
                                array_key_exists($key, $normalizedAnswer) &&
                                $normalizedAnswer[$key] === $correctValue
                            ) {
                                $correctCount++;
                            }
                        }

                        $isCorrect = $correctCount === $totalPair;

                        if ($isCorrect) {

                            $score = $totalPair === 3 ? 2 : 3;

                        } else {

                            $minimumPartial = ceil($totalPair / 2);

                            $score = $correctCount >= $minimumPartial ? 1 : 0;
                        }
                    }

                break;

                case 'PG_KOMPLEKS':

                    if (is_array($answerData)) {

                        $correctAnswers = $question->lmsQuestionOption()->get()->filter(function ($opt) {
                            return isset($opt->extra_data['side']) && $opt->extra_data['side'] === 'item';
                        })->mapWithKeys(function ($opt) {
                            return [
                                trim($opt->options_key) => trim($opt->extra_data['answer'] ?? '')
                            ];
                        })->toArray();

                        $normalizedAnswer = collect($answerData)->mapWithKeys(function ($value, $key) {
                            return [
                                trim($key) => trim($value)
                            ];
                        })->toArray();

                        $totalItem = count($correctAnswers);

                        $correctCount = 0;

                        foreach ($correctAnswers as $key => $correctValue) {

                            if (
                                array_key_exists($key, $normalizedAnswer) &&
                                $normalizedAnswer[$key] === $correctValue
                            ) {
                                $correctCount++;
                            }
                        }

                        $isCorrect = $correctCount === $totalItem;

                        if ($isCorrect) {

                            $score = 2;

                        } else {

                            $minimumPartial = ceil($totalItem / 2);

                            $score = $correctCount >= $minimumPartial ? 1 : 0;
                        }
                    }

                break;

                case 'ESSAY':

                    $score = 0;

                break;
            }
        }

        $answer = TkaTryoutAnswer::where('attempt_id', $attemptId)->where('question_id', $request->question_id)->first();

        if ($answer) {

            $updateData = [
                'attempt_id' => $attemptId,
                'status_answer' => $request->status_answer,
                'answer_value' => $answerData,
                'question_score' => $score,
            ];

            $answer->update($updateData);

        } else {

            TkaTryoutAnswer::create([
                'attempt_id' => $attemptId,
                'question_id' => $request->question_id,
                'answer_value' => $answerData,
                'question_score' => $score,
                'status_answer' => $request->status_answer,
            ]);

        }

        $submittedCount = TkaTryoutAnswer::where('attempt_id', $attemptId)->where('status_answer', 'submitted')->count();

        $isFinished = $submittedCount >= $attempt->total_question;

        return response()->json([
            'status' => 'success',
            'message' => 'Jawaban berhasil disimpan',
            'is_finished' => $isFinished
        ]);
    }
}
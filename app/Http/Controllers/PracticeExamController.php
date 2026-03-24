<?php

namespace App\Http\Controllers;

use App\Models\PracticeExam;
use App\Models\PracticeExamAttempt;
use App\Models\PracticeExamQuestion;
use App\Models\PracticeExamAnswer;
use App\Models\Kurikulum;
use App\Models\Kelas;
use App\Models\Mapel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PracticeExamController extends Controller
{
    /**
     * Display a listing of practice exams for students
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $student = $user->StudentProfile;
        
        $examType = $request->get('type', 'all'); // all, daily_practice, chapter_test, midterm, final, school_exam
        $kelasId = $request->get('kelas_id');
        $mapelId = $request->get('mapel_id');
        $search = $request->get('search');
        
        $query = PracticeExam::published()
            ->with(['Kurikulum', 'Kelas', 'Mapel', 'UserAccount']);
        
        // Filter by type
        if ($examType !== 'all') {
            $query->where('exam_type', $examType);
        }
        
        // Filter by kelas
        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }
        
        // Filter by mapel
        if ($mapelId) {
            $query->where('mapel_id', $mapelId);
        }
        
        // Search
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }
        
        $exams = $query->latest()->paginate(12);
        
        // Get user's attempts for each exam
        foreach ($exams as $exam) {
            $exam->best_attempt = $student ? $exam->studentAttempt($student->id) : null;
        }
        
        $kurikulums = Kurikulum::all();
        $kelasList = Kelas::all();
        $mapels = Mapel::all();
        $examTypes = [
            'daily_practice' => 'Latihan Harian',
            'chapter_test' => 'Ujian Bab',
            'midterm' => 'UTS',
            'final' => 'UAS',
            'school_exam' => 'Ujian Sekolah',
        ];
        
        return view('practice-exams.index', compact('exams', 'examType', 'kelasId', 'mapelId', 'search', 'kurikulums', 'kelasList', 'mapels', 'examTypes'));
    }

    /**
     * Show practice exam details
     */
    public function show($id)
    {
        $user = Auth::user();
        $student = $user->StudentProfile;
        
        $exam = PracticeExam::published()
            ->with(['Kurikulum', 'Kelas', 'Mapel', 'Bab', 'SubBab', 'Questions.QuestionBank', 'UserAccount'])
            ->findOrFail($id);
        
        // Get student attempts
        $attempts = $student ? PracticeExamAttempt::where('practice_exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('is_completed', true)
            ->orderBy('score', 'desc')
            ->limit(5)
            ->get() : [];
        
        $bestAttempt = $attempts->first();
        
        return view('practice-exams.show', compact('exam', 'attempts', 'bestAttempt'));
    }

    /**
     * Start practice exam
     */
    public function start($id)
    {
        $user = Auth::user();
        $student = $user->StudentProfile;
        
        if (!$student) {
            return redirect()->route('practice-exams.index')
                ->with('error', 'You need to have a student profile to take exams');
        }
        
        $exam = PracticeExam::published()->findOrFail($id);
        
        // Check if already has an in-progress attempt
        $existingAttempt = PracticeExamAttempt::where('practice_exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->first();
        
        if ($existingAttempt) {
            return redirect()->route('practice-exams.take', $existingAttempt->id);
        }
        
        // Create new attempt
        DB::beginTransaction();
        try {
            $attemptNumber = PracticeExamAttempt::where('practice_exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->count() + 1;
            
            $attempt = PracticeExamAttempt::create([
                'practice_exam_id' => $exam->id,
                'student_id' => $student->id,
                'user_id' => $user->id,
                'started_at' => now(),
                'attempt_number' => $attemptNumber,
                'status' => 'in_progress',
            ]);
            
            // Get questions and randomize if needed
            $questions = $exam->Questions()->orderBy('question_number')->get();
            
            if ($exam->randomize_questions) {
                $questions = $questions->shuffle();
            }
            
            // Create answer records for each question
            foreach ($questions as $index => $question) {
                PracticeExamAnswer::create([
                    'practice_exam_attempt_id' => $attempt->id,
                    'practice_exam_question_id' => $question->id,
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('practice-exams.take', $attempt->id);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('practice-exams.show', $exam->id)
                ->with('error', 'Failed to start exam. Please try again.');
        }
    }

    /**
     * Take practice exam
     */
    public function take($attemptId)
    {
        $user = Auth::user();
        $student = $user->StudentProfile;
        
        $attempt = PracticeExamAttempt::with(['PracticeExam.Questions.QuestionBank', 'Answers'])
            ->findOrFail($attemptId);
        
        // Check authorization
        if ($attempt->student_id !== $student->id) {
            abort(403, 'Unauthorized access');
        }
        
        // Check if already completed
        if ($attempt->is_completed) {
            return redirect()->route('practice-exams.result', $attempt->id);
        }
        
        // Check if expired
        $exam = $attempt->PracticeExam;
        if ($exam->duration_minutes) {
            $elapsedSeconds = now()->diffInSeconds($attempt->started_at);
            $durationSeconds = $exam->duration_minutes * 60;
            
            if ($elapsedSeconds >= $durationSeconds) {
                $this->submitExam($attemptId);
                return redirect()->route('practice-exams.result', $attemptId)
                    ->with('info', 'Exam time expired. Your answers have been submitted automatically.');
            }
            
            $remainingSeconds = $durationSeconds - $elapsedSeconds;
        } else {
            $remainingSeconds = null;
        }
        
        return view('practice-exams.take', compact('attempt', 'exam', 'remainingSeconds'));
    }

    /**
     * Save answer for practice exam
     */
    public function saveAnswer(Request $request, $attemptId)
    {
        $user = Auth::user();
        $student = $user->StudentProfile;
        
        $attempt = PracticeExamAttempt::findOrFail($attemptId);
        
        // Check authorization
        if ($attempt->student_id !== $student->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $questionId = $request->input('question_id');
        $answer = $request->input('answer');
        
        $examAnswer = PracticeExamAnswer::where('practice_exam_attempt_id', $attemptId)
            ->whereHas('ExamQuestion', function($q) use ($questionId) {
                $q->where('id', $questionId);
            })
            ->firstOrFail();
        
        $examAnswer->update([
            'student_answer' => $answer,
            'time_spent_seconds' => now()->diffInSeconds($attempt->started_at),
        ]);
        
        return response()->json(['success' => true]);
    }

    /**
     * Submit practice exam
     */
    public function submit($attemptId)
    {
        $user = Auth::user();
        $student = $user->StudentProfile;
        
        $attempt = PracticeExamAttempt::findOrFail($attemptId);
        
        // Check authorization
        if ($attempt->student_id !== $student->id) {
            abort(403, 'Unauthorized access');
        }
        
        return $this->submitExam($attemptId);
    }

    /**
     * Submit exam logic
     */
    private function submitExam($attemptId)
    {
        $attempt = PracticeExamAttempt::with(['PracticeExam.Questions.QuestionBank', 'Answers.ExamQuestion.QuestionBank'])->findOrFail($attemptId);
        
        DB::beginTransaction();
        try {
            $correctAnswers = 0;
            $wrongAnswers = 0;
            $unanswered = 0;
            $totalPoints = 0;
            $earnedPoints = 0;
            
            foreach ($attempt->Answers as $answer) {
                $question = $answer->ExamQuestion->QuestionBank;
                $totalPoints += $answer->ExamQuestion->points;
                
                if (empty($answer->student_answer)) {
                    $unanswered++;
                } elseif ($answer->student_answer == $question->correct_answer) {
                    $correctAnswers++;
                    $earnedPoints += $answer->ExamQuestion->points;
                    $answer->update(['is_correct' => true, 'points_earned' => $answer->ExamQuestion->points]);
                } else {
                    $wrongAnswers++;
                }
            }
            
            // Calculate score (0-100)
            $score = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0;
            $passed = $attempt->PracticeExam->passing_score ? $score >= $attempt->PracticeExam->passing_score : true;
            
            $attempt->update([
                'submitted_at' => now(),
                'time_spent_seconds' => now()->diffInSeconds($attempt->started_at),
                'correct_answers' => $correctAnswers,
                'wrong_answers' => $wrongAnswers,
                'unanswered' => $unanswered,
                'score' => round($score, 2),
                'passed' => $passed,
                'is_completed' => true,
                'status' => 'completed',
            ]);
            
            DB::commit();
            
            return redirect()->route('practice-exams.result', $attempt->id);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('practice-exams.take', $attemptId)
                ->with('error', 'Failed to submit exam. Please try again.');
        }
    }

    /**
     * Show practice exam results
     */
    public function result($attemptId)
    {
        $user = Auth::user();
        $student = $user->StudentProfile;
        
        $attempt = PracticeExamAttempt::with(['PracticeExam', 'Answers.ExamQuestion.QuestionBank'])
            ->findOrFail($attemptId);
        
        // Check authorization
        if ($attempt->student_id !== $student->id) {
            abort(403, 'Unauthorized access');
        }
        
        return view('practice-exams.result', compact('attempt'));
    }

    /**
     * View explanation for a question
     */
    public function viewExplanation($attemptId, $questionId)
    {
        $user = Auth::user();
        $student = $user->StudentProfile;
        
        $answer = PracticeExamAnswer::where('practice_exam_attempt_id', $attemptId)
            ->whereHas('ExamQuestion', function($q) use ($questionId) {
                $q->where('id', $questionId);
            })
            ->with('ExamQuestion.QuestionBank')
            ->firstOrFail();
        
        // Check authorization
        if ($answer->ExamAttempt->student_id !== $student->id) {
            abort(403, 'Unauthorized access');
        }
        
        // Mark as viewed
        $answer->update(['viewed_explanation' => true]);
        
        return response()->json([
            'success' => true,
            'explanation' => $answer->ExamQuestion->QuestionBank->explanation,
        ]);
    }

    /**
     * Manage practice exams (Admin only)
     */
    public function manage(Request $request)
    {
        $this->ensureAdministrator();

        $search = $request->get('search');
        $examType = $request->get('type');

        $query = PracticeExam::with(['Kurikulum', 'Kelas', 'Mapel', 'UserAccount']);

        if ($examType) {
            $query->where('exam_type', $examType);
        }

        if ($search) {
            $query->where('title', 'LIKE', "%{$search}%");
        }

        $exams = $query->latest()->paginate(20);

        $examTypes = [
            'daily_practice' => 'Latihan Harian',
            'chapter_test' => 'Ujian Bab',
            'midterm' => 'UTS',
            'final' => 'UAS',
            'school_exam' => 'Ujian Sekolah'
        ];

        return view('practice-exams.manage', compact('exams', 'examTypes', 'search', 'examType'));
    }

    /**
     * Show form to create new practice exam
     */
    public function create()
    {
        $this->ensureAdministrator();

        $kurikulums = Kurikulum::all();
        $kelasList = Kelas::all();
        $mapels = Mapel::all();

        return view('practice-exams.create', compact('kurikulums', 'kelasList', 'mapels'));
    }

    /**
     * Store new practice exam
     */
    public function store(Request $request)
    {
        $this->ensureAdministrator();

        $validated = $request->validate([
            'exam_type' => 'required|in:daily_practice,chapter_test,midterm,final,school_exam',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'kurikulum_id' => 'required|exists:kurikulums,id',
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mapels,id',
            'difficulty' => 'required|in:easy,medium,hard',
            'duration_minutes' => 'required|integer|min:10|max:180',
            'total_questions' => 'required|integer|min:5|max:100',
            'randomize_questions' => 'boolean',
        ]);

        PracticeExam::create([
            'user_id' => Auth::id(),
            'school_partner_id' => null,
            'exam_type' => $validated['exam_type'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'kurikulum_id' => $validated['kurikulum_id'],
            'kelas_id' => $validated['kelas_id'],
            'mapel_id' => $validated['mapel_id'],
            'difficulty' => $validated['difficulty'],
            'duration_minutes' => $validated['duration_minutes'],
            'total_questions' => $validated['total_questions'],
            'randomize_questions' => $validated['randomize_questions'] ?? false,
            'status' => 'published',
            'is_active' => true,
        ]);

        return redirect()->route('practice-exams.manage')
            ->with('success', 'Latihan Soal berhasil ditambahkan!');
    }

    /**
     * Show form to edit practice exam
     */
    public function edit($id)
    {
        $this->ensureAdministrator();

        $exam = PracticeExam::findOrFail($id);
        $kurikulums = Kurikulum::all();
        $kelasList = Kelas::all();
        $mapels = Mapel::all();

        return view('practice-exams.edit', compact('exam', 'kurikulums', 'kelasList', 'mapels'));
    }

    /**
     * Update practice exam
     */
    public function update(Request $request, $id)
    {
        $this->ensureAdministrator();

        $exam = PracticeExam::findOrFail($id);

        $validated = $request->validate([
            'exam_type' => 'required|in:daily_practice,chapter_test,midterm,final,school_exam',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'kurikulum_id' => 'required|exists:kurikulums,id',
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mapels,id',
            'difficulty' => 'required|in:easy,medium,hard',
            'duration_minutes' => 'required|integer|min:10|max:180',
            'total_questions' => 'required|integer|min:5|max:100',
            'randomize_questions' => 'boolean',
        ]);

        $exam->update([
            'exam_type' => $validated['exam_type'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'kurikulum_id' => $validated['kurikulum_id'],
            'kelas_id' => $validated['kelas_id'],
            'mapel_id' => $validated['mapel_id'],
            'difficulty' => $validated['difficulty'],
            'duration_minutes' => $validated['duration_minutes'],
            'total_questions' => $validated['total_questions'],
            'randomize_questions' => $validated['randomize_questions'] ?? false,
        ]);

        return redirect()->route('practice-exams.manage')
            ->with('success', 'Latihan Soal berhasil diupdate!');
    }

    /**
     * Delete practice exam
     */
    public function destroy($id)
    {
        $this->ensureAdministrator();

        $exam = PracticeExam::findOrFail($id);
        $exam->delete();

        return redirect()->route('practice-exams.manage')
            ->with('success', 'Latihan Soal berhasil dihapus!');
    }

    private function ensureAdministrator()
    {
        if (!Auth::check() || Auth::user()->role !== 'Administrator') {
            abort(403, 'Unauthorized action.');
        }
    }
}

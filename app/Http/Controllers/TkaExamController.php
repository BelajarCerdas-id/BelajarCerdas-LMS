<?php

namespace App\Http\Controllers;

use App\Models\TkaExam;
use App\Models\TkaExamAttempt;
use App\Models\TkaExamQuestion;
use App\Models\TkaExamAnswer;
use App\Models\LmsQuestionBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TkaExamController extends Controller
{
    /**
     * Display a listing of TKA exams for students
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $student = $user->StudentProfile;

        $search = $request->get('search');
        $subject = $request->get('subject');

        $query = TkaExam::published()->with(['UserAccount', 'SchoolPartner']);

        // Search
        if ($search) {
            $query->where('title', 'LIKE', "%{$search}%");
        }

        // Filter by subject
        if ($subject) {
            $query->whereJsonContains('subjects', $subject);
        }

        $exams = $query->latest()->paginate(12);

        // Get user's attempts for each exam
        foreach ($exams as $exam) {
            $exam->user_attempt = $student ? $exam->studentAttempt($student->id) : null;
        }

        $subjects = ['Matematika', 'IPA', 'Bahasa Indonesia', 'Bahasa Inggris', 'IPS'];

        return view('tka-exams.index', compact('exams', 'subjects', 'search', 'subject'));
    }

    /**
     * Manage TKA exams (Admin only)
     */
    public function manage(Request $request)
    {
        $this->ensureAdministrator();

        $search = $request->get('search');
        $subject = $request->get('subject');

        $query = TkaExam::with(['UserAccount', 'SchoolPartner']);

        if ($search) {
            $query->where('title', 'LIKE', "%{$search}%");
        }

        if ($subject) {
            $query->whereJsonContains('subjects', $subject);
        }

        $exams = $query->latest()->paginate(20);
        $subjects = ['Matematika', 'IPA', 'Bahasa Indonesia', 'Bahasa Inggris', 'Fisika', 'Kimia', 'Biologi', 'Ekonomi', 'Geografi', 'Sejarah'];

        return view('tka-exams.manage', compact('exams', 'subjects', 'search', 'subject'));
    }

    /**
     * Show form to create new TKA exam
     */
    public function create()
    {
        $this->ensureAdministrator();
        return view('tka-exams.create');
    }

    /**
     * Store new TKA exam
     */
    public function store(Request $request)
    {
        $this->ensureAdministrator();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'subjects' => 'required|array|min:1',
            'difficulty' => 'required|in:easy,medium,hard,mixed',
            'duration_minutes' => 'required|integer|min:30|max:180',
            'passing_score' => 'required|integer|min:50|max:100',
            'total_questions' => 'required|integer|min:10|max:100',
            'randomize_questions' => 'boolean',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        TkaExam::create([
            'user_id' => Auth::id(),
            'school_partner_id' => null,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'subjects' => json_encode($validated['subjects']),
            'difficulty' => $validated['difficulty'],
            'passing_score' => $validated['passing_score'],
            'duration_minutes' => $validated['duration_minutes'],
            'total_questions' => $validated['total_questions'],
            'randomize_questions' => $validated['randomize_questions'] ?? false,
            'show_results_immediately' => true,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => 'published',
            'is_active' => true,
        ]);

        return redirect()->route('tka-exams.manage')
            ->with('success', 'TKA Exam berhasil ditambahkan!');
    }

    /**
     * Show form to edit TKA exam
     */
    public function edit($id)
    {
        $this->ensureAdministrator();
        $exam = TkaExam::findOrFail($id);
        return view('tka-exams.edit', compact('exam'));
    }

    /**
     * Update TKA exam
     */
    public function update(Request $request, $id)
    {
        $this->ensureAdministrator();

        $exam = TkaExam::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'subjects' => 'required|array|min:1',
            'difficulty' => 'required|in:easy,medium,hard,mixed',
            'duration_minutes' => 'required|integer|min:30|max:180',
            'passing_score' => 'required|integer|min:50|max:100',
            'total_questions' => 'required|integer|min:10|max:100',
            'randomize_questions' => 'boolean',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $exam->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'subjects' => json_encode($validated['subjects']),
            'difficulty' => $validated['difficulty'],
            'passing_score' => $validated['passing_score'],
            'duration_minutes' => $validated['duration_minutes'],
            'total_questions' => $validated['total_questions'],
            'randomize_questions' => $validated['randomize_questions'] ?? false,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
        ]);

        return redirect()->route('tka-exams.manage')
            ->with('success', 'TKA Exam berhasil diupdate!');
    }

    /**
     * Delete TKA exam
     */
    public function destroy($id)
    {
        $this->ensureAdministrator();

        $exam = TkaExam::findOrFail($id);
        $exam->delete();

        return redirect()->route('tka-exams.manage')
            ->with('success', 'TKA Exam berhasil dihapus!');
    }

    private function ensureAdministrator()
    {
        if (!Auth::check() || Auth::user()->role !== 'Administrator') {
            abort(403, 'Unauthorized action.');
        }
    }

    /**
     * Show TKA exam details
     */
    public function show($id)
    {
        $user = Auth::user();
        $student = $user->StudentProfile;
        
        $exam = TkaExam::published()->with(['Questions.QuestionBank', 'UserAccount'])->findOrFail($id);
        
        // Check if exam is available
        if (!$exam->isAvailable()) {
            return redirect()->route('tka-exams.index')
                ->with('error', 'Exam is not available at this time');
        }
        
        // Get or create student attempt
        $attempt = null;
        if ($student) {
            $attempt = TkaExamAttempt::where('tka_exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->where('status', 'in_progress')
                ->first();
        }
        
        $previousAttempts = $student ? TkaExamAttempt::where('tka_exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('is_completed', true)
            ->orderBy('score', 'desc')
            ->limit(5)
            ->get() : collect([]);
        
        return view('tka-exams.show', compact('exam', 'attempt', 'previousAttempts'));
    }

    /**
     * Start TKA exam
     */
    public function start($id)
    {
        $user = Auth::user();
        $student = $user->StudentProfile;
        
        if (!$student) {
            return redirect()->route('tka-exams.index')
                ->with('error', 'You need to have a student profile to take exams');
        }
        
        $exam = TkaExam::published()->findOrFail($id);
        
        // Check if exam is available
        if (!$exam->isAvailable()) {
            return redirect()->route('tka-exams.index')
                ->with('error', 'Exam is not available at this time');
        }
        
        // Check if already has an in-progress attempt
        $existingAttempt = TkaExamAttempt::where('tka_exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->first();
        
        if ($existingAttempt) {
            return redirect()->route('tka-exams.take', $existingAttempt->id);
        }
        
        // Create new attempt
        DB::beginTransaction();
        try {
            $attempt = TkaExamAttempt::create([
                'tka_exam_id' => $exam->id,
                'student_id' => $student->id,
                'user_id' => $user->id,
                'started_at' => now(),
                'status' => 'in_progress',
            ]);
            
            // Get questions and randomize if needed
            $questions = $exam->Questions()->orderBy('question_number')->get();
            
            if ($exam->randomize_questions) {
                $questions = $questions->shuffle();
            }
            
            // Create answer records for each question
            foreach ($questions as $index => $question) {
                TkaExamAnswer::create([
                    'tka_exam_attempt_id' => $attempt->id,
                    'tka_exam_question_id' => $question->id,
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('tka-exams.take', $attempt->id);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('tka-exams.show', $exam->id)
                ->with('error', 'Failed to start exam. Please try again.');
        }
    }

    /**
     * Take TKA exam
     */
    public function take($attemptId)
    {
        $user = Auth::user();
        $student = $user->StudentProfile;
        
        $attempt = TkaExamAttempt::with(['TkaExam.Questions.QuestionBank', 'Answers'])
            ->findOrFail($attemptId);
        
        // Check authorization
        if ($attempt->student_id !== $student->id) {
            abort(403, 'Unauthorized access');
        }
        
        // Check if already completed
        if ($attempt->is_completed) {
            return redirect()->route('tka-exams.result', $attempt->id);
        }
        
        // Check if expired
        $exam = $attempt->TkaExam;
        $elapsedSeconds = now()->diffInSeconds($attempt->started_at);
        $durationSeconds = $exam->duration_minutes * 60;
        
        if ($elapsedSeconds >= $durationSeconds) {
            $this->submitExam($attemptId);
            return redirect()->route('tka-exams.result', $attemptId)
                ->with('info', 'Exam time expired. Your answers have been submitted automatically.');
        }
        
        $remainingSeconds = $durationSeconds - $elapsedSeconds;
        
        return view('tka-exams.take', compact('attempt', 'exam', 'remainingSeconds'));
    }

    /**
     * Save answer for TKA exam
     */
    public function saveAnswer(Request $request, $attemptId)
    {
        $user = Auth::user();
        $student = $user->StudentProfile;
        
        $attempt = TkaExamAttempt::findOrFail($attemptId);
        
        // Check authorization
        if ($attempt->student_id !== $student->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $questionId = $request->input('question_id');
        $answer = $request->input('answer');
        
        $examAnswer = TkaExamAnswer::where('tka_exam_attempt_id', $attemptId)
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
     * Submit TKA exam
     */
    public function submit($attemptId)
    {
        $user = Auth::user();
        $student = $user->StudentProfile;
        
        $attempt = TkaExamAttempt::findOrFail($attemptId);
        
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
        $attempt = TkaExamAttempt::with(['TkaExam.Questions.QuestionBank', 'Answers.ExamQuestion.QuestionBank'])->findOrFail($attemptId);
        
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
            
            $attempt->update([
                'submitted_at' => now(),
                'time_spent_seconds' => now()->diffInSeconds($attempt->started_at),
                'correct_answers' => $correctAnswers,
                'wrong_answers' => $wrongAnswers,
                'unanswered' => $unanswered,
                'score' => round($score, 2),
                'is_completed' => true,
                'status' => 'completed',
            ]);
            
            DB::commit();
            
            return redirect()->route('tka-exams.result', $attempt->id);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('tka-exams.take', $attemptId)
                ->with('error', 'Failed to submit exam. Please try again.');
        }
    }

    /**
     * Show TKA exam results
     */
    public function result($attemptId)
    {
        $user = Auth::user();
        $student = $user->StudentProfile;
        
        $attempt = TkaExamAttempt::with(['TkaExam', 'Answers.ExamQuestion.QuestionBank'])
            ->findOrFail($attemptId);
        
        // Check authorization
        if ($attempt->student_id !== $student->id) {
            abort(403, 'Unauthorized access');
        }
        
        return view('tka-exams.result', compact('attempt'));
    }
}

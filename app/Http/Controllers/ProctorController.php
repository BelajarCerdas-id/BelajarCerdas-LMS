<?php

namespace App\Http\Controllers;

use App\Models\ProctorEntryRequest;
use App\Models\ProctorUnlockRequest;
use App\Models\SchoolAssessment;
use App\Models\SchoolClass;
use App\Models\StudentAssessmentAttempt;
use App\Models\StudentProfile;
use App\Models\StudentSchoolClass;
use App\Models\UserAccount;
use App\Services\Proctor\ProctorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProctorController extends Controller
{
    private const SNAPSHOT_LIMIT = 2000;

    public function __construct(private ProctorService $proctor) {}

    public function index($role, $schoolName, $schoolId): View|JsonResponse
    {
        if (!$this->canView()) {
            abort(403);
        }

        return view('features.lms.teacher.proctor.proctor', [
            'role' => $role,
            'schoolName' => $schoolName,
            'schoolId' => $schoolId,
            'canApprove' => (bool) Auth::user()->can_approve_unlock,
            'isAdmin' => ProctorService::hasRole(Auth::user(), ProctorService::APPROVER_ADMIN_ROLES),
        ]);
    }

    /**
     * One row per student per exam: the class roster, plus anyone who has an attempt or an entry request.
     * Live changes arrive over WebSocket; this is the resync source.
     */
    public function snapshot($role, $schoolName, $schoolId): JsonResponse
    {
        if (!$this->canView()) {
            abort(403);
        }

        $assessments = SchoolAssessment::whereIn('id', $this->proctor->heldTodayAssessmentIds((int) $schoolId))
            ->orderBy('start_date')
            ->get(['id', 'title', 'school_class_id', 'start_date', 'end_date', 'proctor_started_at', 'proctor_paused_at', 'proctor_stopped_at']);
        $ids = $assessments->pluck('id');

        $roster = StudentSchoolClass::whereIn('school_class_id', $assessments->pluck('school_class_id')->filter())
            ->where('student_class_status', 'active')
            ->get(['student_id', 'school_class_id']);

        $attempts = StudentAssessmentAttempt::whereIn('school_assessment_id', $ids)
            ->limit(self::SNAPSHOT_LIMIT)
            ->get(['id', 'student_id', 'school_assessment_id', 'status', 'tab_switch_count', 'strike_limit', 'last_heartbeat_at', 'proctor_paused_at'])
            ->keyBy(fn ($t) => $t->school_assessment_id.':'.$t->student_id);

        $entries = ProctorEntryRequest::whereIn('school_assessment_id', $ids)
            ->whereIn('status', ['pending', 'denied'])
            ->get(['id', 'school_assessment_id', 'student_id', 'status'])
            ->keyBy(fn ($e) => $e->school_assessment_id.':'.$e->student_id);

        $unlocks = ProctorUnlockRequest::whereIn('student_assessment_attempt_id', $attempts->pluck('id'))
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->get()
            ->keyBy('student_assessment_attempt_id');

        $names = StudentProfile::whereIn('user_id', $roster->pluck('student_id')->merge($attempts->pluck('student_id'))->merge($entries->pluck('student_id'))->unique())
            ->pluck('nama_lengkap', 'user_id');

        $students = collect();
        foreach ($assessments as $assessment) {
            $studentIds = $roster->where('school_class_id', $assessment->school_class_id)->pluck('student_id')
                ->merge($attempts->where('school_assessment_id', $assessment->id)->pluck('student_id'))
                ->merge($entries->where('school_assessment_id', $assessment->id)->pluck('student_id'))
                ->unique();

            foreach ($studentIds as $studentId) {
                $key = $assessment->id.':'.$studentId;
                $attempt = $attempts->get($key);
                $entry = $entries->get($key);
                $unlock = $attempt ? $unlocks->get($attempt->id) : null;

                $students->push([
                    'key' => $key,
                    'assessment_id' => $assessment->id,
                    'assessment_title' => $assessment->title,
                    'user_id' => $studentId,
                    'student_name' => $names[$studentId] ?? 'Siswa',
                    'status' => $attempt?->status ?? match ($entry?->status) {
                        'pending' => 'waiting',
                        'denied' => 'denied',
                        default => 'not_joined',
                    },
                    'attempt_id' => $attempt?->id,
                    'entry_request_id' => $entry?->status === 'pending' ? $entry->id : null,
                    'unlock_request_id' => $unlock?->id,
                    'unlock_expires_at' => $unlock?->expires_at?->toIso8601String(),
                    'count' => (int) ($attempt?->tab_switch_count ?? 0),
                    'limit' => AssessmentAntiCheatController::strikeLimitFor($attempt),
                    'attempt_paused' => (bool) $attempt?->proctor_paused_at,
                    'last_heartbeat_at' => $attempt?->last_heartbeat_at?->toIso8601String(),
                ]);
            }
        }

        $assessmentCards = $assessments->map(fn (SchoolAssessment $a) => [
            'assessment_id' => $a->id,
            'assessment_title' => $a->title,
            'time' => $a->start_date->timezone('Asia/Jakarta')->format('H:i').'–'.$a->end_date->timezone('Asia/Jakarta')->format('H:i'),
            'state' => ProctorService::sessionState($a),
        ])->values();

        return response()->json([
            'assessments' => $assessmentCards,
            'students' => $students->take(5000)->values(),
            'canApprove' => (bool) Auth::user()->can_approve_unlock,
        ]);
    }

    /**
     * PIC page: exam overview with pending unlock requests, approves/denies them.
     * The per-class detail (student table, suspend/terminate) stays on the proctor dashboard.
     */
    public function picView($role, $schoolName, $schoolId): View
    {
        if (!$this->canView() || !Auth::user()->can_approve_unlock) {
            abort(403);
        }

        return view('features.lms.teacher.proctor.proctor-pic', [
            'role' => $role,
            'schoolName' => $schoolName,
            'schoolId' => $schoolId,
        ]);
    }

    public function picData($role, $schoolName, $schoolId): JsonResponse
    {
        if (!$this->canView() || !Auth::user()->can_approve_unlock) {
            abort(403);
        }

        $assessments = SchoolAssessment::whereIn('id', $this->proctor->heldTodayAssessmentIds((int) $schoolId))
            ->orderBy('start_date')
            ->get(['id', 'title', 'school_class_id', 'start_date', 'end_date', 'proctor_started_at', 'proctor_paused_at', 'proctor_stopped_at'])
            ->keyBy('id');
        $ids = $assessments->keys();

        $classNames = SchoolClass::whereIn('id', $assessments->pluck('school_class_id')->filter())
            ->pluck('class_name', 'id');

        $attempts = StudentAssessmentAttempt::whereIn('school_assessment_id', $ids)
            ->get(['id', 'student_id', 'school_assessment_id', 'status', 'tab_switch_count', 'strike_limit'])
            ->keyBy('id');

        $requests = ProctorUnlockRequest::whereIn('student_assessment_attempt_id', $attempts->pluck('id'))
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->orderBy('expires_at')
            ->get();

        $names = StudentProfile::whereIn('user_id', $attempts->pluck('student_id')->unique())
            ->pluck('nama_lengkap', 'user_id');

        $requestsPayload = $requests->map(fn (ProctorUnlockRequest $r) => [
            'request_id' => $r->id,
            'attempt_id' => $r->student_assessment_attempt_id,
            'assessment_id' => $attempts[$r->student_assessment_attempt_id]?->school_assessment_id,
            'student_name' => $names[$attempts[$r->student_assessment_attempt_id]?->student_id] ?? 'Siswa',
            'exam_title' => $assessments[$attempts[$r->student_assessment_attempt_id]?->school_assessment_id]?->title,
            'class_name' => $classNames[$assessments[$attempts[$r->student_assessment_attempt_id]?->school_assessment_id]?->school_class_id] ?? '-',
            'count' => (int) ($attempts[$r->student_assessment_attempt_id]?->tab_switch_count ?? 0),
            'limit' => AssessmentAntiCheatController::strikeLimitFor($attempts[$r->student_assessment_attempt_id] ?? null),
            'expires_in' => $r->expires_at->diffInSeconds(now()),
            'expires_at' => $r->expires_at->toIso8601String(),
        ])->values();

        $exams = $assessments->map(fn (SchoolAssessment $a) => [
            'assessment_id' => $a->id,
            'assessment_title' => $a->title,
            'class_name' => $classNames[$a->school_class_id] ?? '-',
            'time' => $a->start_date->timezone('Asia/Jakarta')->format('H:i').'–'.$a->end_date->timezone('Asia/Jakarta')->format('H:i'),
            'state' => ProctorService::sessionState($a),
            'pending_unlocks' => $requestsPayload->where('assessment_id', $a->id)->count(),
        ])->values();

        return response()->json([
            'exams' => $exams,
            'requests' => $requestsPayload,
        ]);
    }

    /**
     * Short-lived WebSocket token scoped to today's exams of this school.
     */
    public function token($role, $schoolName, $schoolId): JsonResponse
    {
        if (!$this->canView()) {
            abort(403);
        }

        $assessmentIds = $this->proctor->heldTodayAssessmentIds((int) $schoolId)->all();

        return response()->json([
            'url' => config('proctor.ws_url'),
            'token' => $this->proctor->token(Auth::id(), null, $assessmentIds, schoolId: (int) $schoolId),
        ]);
    }

    /**
     * Student token scoped to their own attempt (unlocks) or to their user id (entry decisions while waiting).
     */
    public function studentToken(Request $request): JsonResponse
    {
        $attempt = StudentAssessmentAttempt::where('student_id', Auth::id())
            ->where('school_assessment_id', $request->integer('assessment_id'))
            ->first();

        $examId = $request->integer('assessment_id');
        $waiting = ProctorEntryRequest::where('student_id', Auth::id())->where('school_assessment_id', $examId)->exists();
        if (!$attempt && !$waiting) {
            abort(403);
        }

        // no attempt yet: a waiting student gets a user-only token, which receives just their own entry decision
        return response()->json([
            'url' => config('proctor.ws_url'),
            'token' => $this->proctor->token(Auth::id(), $attempt?->id, [], $examId),
        ]);
    }

    /**
     * Admin page: assign the unlock-approver flag (PIC).
     */
    public function approversPage(): View
    {
        $this->ensureAdmin();

        return view('features.lms.administrator.proctor-approvers');
    }

    public function approversData(Request $request): JsonResponse
    {
        $this->ensureAdmin();

        $query = UserAccount::whereIn('role', ProctorService::VIEW_ROLES);
        $term = trim((string) $request->query('q'));
        if ($term !== '') {
            $query->where('email', 'like', "%{$term}%");
        }

        return response()->json([
            'users' => $query->orderByDesc('can_approve_unlock')->orderBy('email')
                ->limit(50)
                ->get(['id', 'email', 'role', 'can_approve_unlock']),
        ]);
    }

    public function approversToggle(Request $request, int $userId): JsonResponse
    {
        $this->ensureAdmin();

        $validated = $request->validate(['enabled' => 'required|boolean']);

        $user = UserAccount::whereIn('role', ProctorService::VIEW_ROLES)->findOrFail($userId);
        $user->update(['can_approve_unlock' => $validated['enabled']]);
        $this->proctor->audit($user->can_approve_unlock ? 'pic_granted' : 'pic_revoked', [
            'school_partner_id' => $user->SchoolStaffProfile?->school_partner_id,
            'student_id' => null,
            'detail' => ['target_user_id' => $user->id, 'target_email' => $user->email],
        ]);

        return response()->json(['status' => 'ok', 'can_approve_unlock' => $user->can_approve_unlock]);
    }

    public function approverSearch(Request $request): JsonResponse
    {
        $this->ensureAdmin();

        $term = trim((string) $request->query('q'));
        if (mb_strlen($term) < 3) {
            return response()->json(['users' => []]);
        }

        return $this->approversData($request);
    }

    public function approverToggle(Request $request, $role, $schoolName, $schoolId, int $userId): JsonResponse
    {
        return $this->approversToggle($request, $userId);
    }

    private function ensureAdmin(): void
    {
        if (!ProctorService::hasRole(Auth::user(), ProctorService::APPROVER_ADMIN_ROLES)) {
            abort(403);
        }
    }

    private function canView(): bool
    {
        return ProctorService::hasRole(Auth::user(), ProctorService::VIEW_ROLES);
    }
}

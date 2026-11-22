<?php

namespace App\Http\Controllers;

use App\Models\ProctorUnlockRequest;
use App\Models\SchoolAssessment;
use App\Models\StudentAssessmentAttempt;
use App\Services\LMS\AssessmentSummaryService\AssessmentSummaryService;
use App\Services\Proctor\ProctorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Unlock only: reopens a locked attempt so the student can continue.
 * Never touches answers, the timer (expire_time) or the grades beyond the summary recount.
 */
class ProctorUnlockController extends Controller
{
    public function __construct(private ProctorService $proctor) {}

    public function store($role, $schoolName, $schoolId, int $attemptId): JsonResponse
    {
        if (!ProctorService::hasRole(Auth::user(), ['guru'])) {
            return response()->json(['message' => 'Hanya guru yang dapat meminta buka kunci.'], 403);
        }

        $window = (int) config('proctor.unlock_window');

        $result = DB::transaction(function () use ($schoolId, $attemptId, $window) {
            $attempt = StudentAssessmentAttempt::with('SchoolAssessment')->lockForUpdate()->find($attemptId);
            if (!$attempt || (int) $attempt->SchoolAssessment?->school_partner_id !== (int) $schoolId) {
                return ['error' => 'Ujian siswa tidak ditemukan.', 'code' => 404];
            }
            if (!$this->proctor->heldTodayAssessmentIds((int) $schoolId)->contains($attempt->school_assessment_id)) {
                return ['error' => 'Ujian ini tidak dijadwalkan hari ini.', 'code' => 422];
            }
            if ($attempt->status !== 'cheating') {
                return ['error' => 'Siswa ini tidak sedang terkunci.', 'code' => 422];
            }

            $pending = ProctorUnlockRequest::where('student_assessment_attempt_id', $attempt->id)
                ->where('status', 'pending')
                ->where('expires_at', '>', now())
                ->exists();
            if ($pending) {
                return ['error' => 'Permintaan buka kunci untuk siswa ini masih menunggu.', 'code' => 422];
            }

            $req = ProctorUnlockRequest::create([
                'student_assessment_attempt_id' => $attempt->id,
                'requested_by' => Auth::id(),
                'status' => 'pending',
                'expires_at' => now()->addSeconds($window),
            ]);
            $this->proctor->audit('unlock_requested', $this->context($schoolId, $attempt, $req));

            return ['request' => $req, 'attempt' => $attempt];
        });

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['code']);
        }

        /** @var ProctorUnlockRequest $req */
        $req = $result['request'];
        $attempt = $result['attempt'];
        $this->proctor->publish([
            'type' => 'unlock_requested',
            'attempt_id' => $attempt->id,
            'assessment_id' => $attempt->school_assessment_id,
            'request_id' => $req->id,
            'expires_at' => $req->expires_at->toIso8601String(),
        ]);

        return response()->json(['status' => 'pending', 'request_id' => $req->id, 'expires_at' => $req->expires_at->toIso8601String()]);
    }

    public function approve($role, $schoolName, $schoolId, int $requestId): JsonResponse
    {
        return $this->decide((int) $schoolId, $requestId, true);
    }

    public function deny($role, $schoolName, $schoolId, int $requestId): JsonResponse
    {
        return $this->decide((int) $schoolId, $requestId, false);
    }

    private function decide(int $schoolId, int $requestId, bool $approve): JsonResponse
    {
        $user = Auth::user();
        if (!$user->can_approve_unlock) {
            $this->proctor->audit('unlock_forbidden', ['school_partner_id' => $schoolId, 'detail' => ['request_id' => $requestId, 'approve' => $approve]]);
            return response()->json(['message' => 'Anda tidak memiliki izin menyetujui buka kunci.'], 403);
        }

        $result = DB::transaction(function () use ($user, $schoolId, $requestId, $approve) {
            $req = ProctorUnlockRequest::lockForUpdate()->find($requestId);
            if (!$req) {
                return ['error' => 'Permintaan tidak ditemukan.', 'code' => 404];
            }

            $attempt = StudentAssessmentAttempt::with('SchoolAssessment')->lockForUpdate()->find($req->student_assessment_attempt_id);
            if (!$attempt || (int) $attempt->SchoolAssessment?->school_partner_id !== $schoolId) {
                return ['error' => 'Permintaan tidak ditemukan.', 'code' => 404];
            }
            if ($req->status !== 'pending') {
                return ['error' => 'Permintaan sudah diproses.', 'code' => 422];
            }

            // Default deny: an answer after the window closes is recorded as expired, not approved.
            if ($req->expires_at->lte(now())) {
                $req->update(['status' => 'expired', 'decided_at' => now()]);
                $this->proctor->audit('unlock_expired', $this->context($schoolId, $attempt, $req));
                return ['error' => 'Waktu persetujuan habis, permintaan ditolak otomatis.', 'code' => 422, 'status' => 'expired'];
            }
            if ((int) $req->requested_by === (int) $user->id) {
                $this->proctor->audit('unlock_self_refused', $this->context($schoolId, $attempt, $req));
                return ['error' => 'Permintaan sendiri tidak dapat disetujui.', 'code' => 403];
            }

            $canReopen = $approve
                && $attempt->status === 'cheating'
                && !($attempt->expire_time && now()->gt($attempt->expire_time));

            if (!$canReopen) {
                $req->update(['status' => 'denied', 'decided_by' => $user->id, 'decided_at' => now()]);
                $this->proctor->audit('unlock_denied', $this->context($schoolId, $attempt, $req));
                $message = $approve ? 'Ujian sudah tidak dapat dibuka kembali.' : null;
                return ['status' => 'denied', 'error' => $message, 'code' => 422, 'attempt' => $attempt, 'request' => $req];
            }

            // A fresh window of strikes from the current count; the student's answers and timer are untouched.
            $attempt->update([
                'status' => 'in_progress',
                'finalized_at' => null,
                'strike_limit' => $attempt->tab_switch_count + AssessmentAntiCheatController::MAX_STRIKES,
                'last_heartbeat_at' => now(),
            ]);
            $req->update(['status' => 'approved', 'decided_by' => $user->id, 'decided_at' => now()]);
            $this->proctor->audit('unlock_approved', $this->context($schoolId, $attempt, $req));

            return ['status' => 'approved', 'attempt' => $attempt, 'request' => $req];
        });

        $attempt = $result['attempt'] ?? null;
        $req = $result['request'] ?? null;

        if ($attempt && $req && in_array($result['status'], ['approved', 'denied'], true)) {
            if ($result['status'] === 'approved') {
                app(AssessmentSummaryService::class)->updateStudentAssessmentSummary($attempt->student_id, $attempt->SchoolAssessment);
            }
            $this->proctor->publish([
                'type' => 'unlock_resolved',
                'attempt_id' => $attempt->id,
                'assessment_id' => $attempt->school_assessment_id,
                'request_id' => $req->id,
                'status' => $result['status'],
            ]);
        }

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error'], 'status' => $result['status'] ?? null], $result['code']);
        }

        return response()->json(['status' => $result['status']]);
    }

    private function context(int $schoolId, StudentAssessmentAttempt $attempt, ProctorUnlockRequest $req): array
    {
        return [
            'school_partner_id' => $schoolId,
            'school_assessment_id' => $attempt->school_assessment_id,
            'student_id' => $attempt->student_id,
            'attempt_id' => $attempt->id,
            'detail' => ['request_id' => $req->id],
        ];
    }
}

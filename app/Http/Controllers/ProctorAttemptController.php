<?php

namespace App\Http\Controllers;

use App\Models\StudentAssessmentAnswer;
use App\Models\StudentAssessmentAttempt;
use App\Services\Proctor\ProctorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Per-student controls for one attempt: suspend, resume, terminate.
 * Mirrors ProctorSessionController but scoped to a single student.
 * Unlock is separate (ProctorUnlockController): a request that only the PIC can approve.
 *
 * Invariant: expire_time is never rewritten here. It is only ever
 * start_time + duration (ProctorService::openAttempt) plus pause shifts on
 * resume. Terminate finalizes the attempt through the normal end steps
 * instead of corrupting the deadline.
 */
class ProctorAttemptController extends Controller
{
    public function __construct(private ProctorService $proctor) {}

    public function control($role, $schoolName, $schoolId, int $attemptId, string $action): JsonResponse
    {
        if (!ProctorService::hasRole(Auth::user(), ProctorService::VIEW_ROLES)) {
            abort(403);
        }

        $result = DB::transaction(function () use ($schoolId, $attemptId, $action) {
            $attempt = StudentAssessmentAttempt::with('SchoolAssessment')->lockForUpdate()->find($attemptId);
            if (!$attempt || (int) $attempt->SchoolAssessment?->school_partner_id !== (int) $schoolId) {
                return ['error' => 'Ujian siswa tidak ditemukan.', 'code' => 404];
            }
            if (!$this->proctor->heldTodayAssessmentIds((int) $schoolId)->contains($attempt->school_assessment_id)) {
                return ['error' => 'Ujian ini tidak dijadwalkan hari ini.', 'code' => 422];
            }

            $context = [
                'school_partner_id' => (int) $schoolId,
                'school_assessment_id' => $attempt->school_assessment_id,
                'student_id' => $attempt->student_id,
                'attempt_id' => $attempt->id,
            ];
            $now = now();

            if ($action === 'suspend') {
                if ($attempt->status !== 'in_progress' || $attempt->finalized_at || $attempt->proctor_paused_at) {
                    return ['error' => 'Siswa ini tidak sedang mengerjakan.', 'code' => 422];
                }
                // the pause does not consume time: resume pushes expire_time out by the pause length
                $attempt->update(['proctor_paused_at' => $now, 'last_heartbeat_at' => $now]);
                $this->proctor->audit('attempt_suspended', $context);
            } elseif ($action === 'resume') {
                if (!$attempt->proctor_paused_at) {
                    return ['error' => 'Siswa ini tidak sedang dijeda.', 'code' => 422];
                }
                $seconds = max(0, $now->timestamp - $attempt->proctor_paused_at->timestamp);
                $attempt->update([
                    'expire_time' => $attempt->expire_time?->addSeconds($seconds) ?? $now,
                    'last_heartbeat_at' => $now,
                    'proctor_paused_at' => null,
                ]);
                $this->proctor->audit('attempt_resumed', $context + ['detail' => ['paused_seconds' => $seconds]]);
            } else {
                // terminate: same intent as the exam-wide stop, but for one student the
                // teacher wants the exam closed NOW, even if that student's client is
                // gone. Finalize through the same steps the student's own /end would
                // run: drafts become submitted, the attempt is finalized, the summary
                // is recomputed. expire_time is NEVER rewritten: it stays the real
                // start_time + duration deadline for the audit trail.
                if ($attempt->status !== 'in_progress' || $attempt->finalized_at) {
                    return ['error' => 'Siswa ini tidak sedang mengerjakan.', 'code' => 422];
                }

                StudentAssessmentAnswer::where('student_id', $attempt->student_id)
                    ->where('school_assessment_id', $attempt->school_assessment_id)
                    ->where('status_answer', 'draft')
                    ->update(['status_answer' => 'submitted']);

                $attempt->update([
                    'status' => $attempt->expire_time && $attempt->expire_time->isPast() ? 'timeout' : 'submitted',
                    'finalized_at' => $now,
                    'proctor_paused_at' => null,
                    'last_heartbeat_at' => $now,
                ]);

                app(\App\Services\LMS\AssessmentSummaryService\AssessmentSummaryService::class)
                    ->updateStudentAssessmentSummary($attempt->student_id, $attempt->SchoolAssessment);

                $this->proctor->audit('attempt_terminated', $context);
            }

            return ['attempt' => $attempt->fresh()];
        });

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['code']);
        }

        $attempt = $result['attempt'];
        // attempt_id targeting only (no audience): the student receives it via their
        // attempt claim; proctor staff via the assessment claim. An audience:'students'
        // would deliver the event to every student of the exam.
        $this->proctor->publish([
            'type' => ['suspend' => 'exam_paused', 'resume' => 'exam_resumed', 'terminate' => 'exam_stopped'][$action],
            'assessment_id' => $attempt->school_assessment_id,
            'attempt_id' => $attempt->id,
        ]);

        return response()->json(['status' => 'ok']);
    }
}

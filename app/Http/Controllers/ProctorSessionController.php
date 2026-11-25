<?php

namespace App\Http\Controllers;

use App\Models\SchoolAssessment;
use App\Models\StudentAssessmentAttempt;
use App\Services\Proctor\ProctorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Teacher controls for one exam: start, pause, resume, stop.
 *
 * Invariant: expire_time is only ever written as start_time + duration
 * (ProctorService::openAttempt) and shifted forward by the exact pause
 * length on resume. A stop NEVER touches attempts: every live client
 * receives the exam_stopped event and finalizes itself through the normal
 * /end path (which also saves the answer the student was typing when the
 * stop hit). Rewriting expire_time to the stop instant corrupted
 * in_progress attempts whose client was offline (stuck rows with a fake
 * deadline) and destroyed the audit trail of the real deadline.
 */
class ProctorSessionController extends Controller
{
    private const ALLOWED_FROM = [
        'start' => ['not_started'],
        'pause' => ['running'],
        'resume' => ['paused'],
        'stop' => ['running', 'paused'],
    ];

    private const EVENTS = [
        'start' => 'session_started',
        'pause' => 'exam_paused',
        'resume' => 'exam_resumed',
        'stop' => 'exam_stopped',
    ];

    public function __construct(private ProctorService $proctor) {}

    public function control($role, $schoolName, $schoolId, int $assessmentId, string $action): JsonResponse
    {
        if (!ProctorService::hasRole(Auth::user(), ProctorService::VIEW_ROLES)) {
            abort(403);
        }

        $held = $this->proctor->heldTodayAssessmentIds((int) $schoolId);
        if (!SchoolAssessment::where('school_partner_id', $schoolId)->whereIn('id', $held)->whereKey($assessmentId)->exists()) {
            return response()->json(['message' => 'Ujian ini tidak dijadwalkan hari ini.'], 404);
        }

        $result = DB::transaction(function () use ($schoolId, $assessmentId, $action) {
            $assessment = SchoolAssessment::lockForUpdate()->findOrFail($assessmentId);
            // effective state: proctor_auto_start schools are 'running' while the
            // schedule window is open, so a teacher can pause or stop the virtual
            // session; a teacher pressing start on it is a no-op 422 (the window
            // is already open). Real rows always win: stopped/paused stay so.
            $state = ProctorService::effectiveSessionState($assessment);

            if (!in_array($state, self::ALLOWED_FROM[$action], true)) {
                return ['error' => "Aksi tidak tersedia saat ujian {$state}.", 'code' => 422];
            }

            // the action targets a schedule-opened (virtual) session when the
            // teacher rows say nobody ever started it
            $virtual = $state === 'running' && $assessment->proctor_started_at === null;

            $context = ['school_partner_id' => $schoolId, 'school_assessment_id' => $assessment->id];
            $now = now();

            if ($action === 'start') {
                $assessment->update(['proctor_started_at' => $now]);
                $this->proctor->audit('session_started', $context);
            } elseif ($action === 'pause') {
                $assessment->update(['proctor_paused_at' => $now]);
                // no audit row exists for the virtual open itself; the first
                // teacher action against it records that it was schedule-driven
                $this->proctor->audit('session_paused', $context + ($virtual ? ['detail' => ['auto_start' => true]] : []));
            } elseif ($action === 'resume') {
                $seconds = max(0, $now->timestamp - $assessment->proctor_paused_at->timestamp);
                $extended = $this->extendRunningAttempts($assessment->id, $seconds, $now);
                $assessment->update(['proctor_paused_at' => null]);
                $this->proctor->audit('session_resumed', $context + ['detail' => ['paused_seconds' => $seconds, 'attempts' => $extended]]);
            } else {
                // attempts are finalized by their own clients through /end (triggered by
                // the exam_stopped event); the server must not rewrite their deadline
                $running = $this->runningAttempts($assessment->id)->count();
                $assessment->update(['proctor_stopped_at' => $now, 'proctor_paused_at' => null]);
                $this->proctor->audit('session_stopped', $context + ['detail' => ['attempts' => $running] + ($virtual ? ['auto_start' => true] : [])]);
            }

            return ['state' => ProctorService::effectiveSessionState($assessment->fresh())];
        });

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['code']);
        }

        $assessmentId = (int) $assessmentId;
        $event = ['type' => self::EVENTS[$action], 'assessment_id' => $assessmentId];
        if ($action !== 'start') {
            $event['audience'] = 'students';
        }
        $this->proctor->publish($event);

        $response = ['status' => 'ok', 'state' => $result['state']];
        if ($action === 'start') {
            $response['pending'] = $this->proctor->pendingEntries(collect([$assessmentId]));
        }

        return response()->json($response);
    }

    private function extendRunningAttempts(int $assessmentId, int $seconds, $now): int
    {
        $count = 0;

        $this->runningAttempts($assessmentId)->chunkById(500, function ($attempts) use ($seconds, $now, &$count) {
            foreach ($attempts as $attempt) {
                // the pause is not the student's fault: reset the heartbeat gap check too
                $attempt->update([
                    'expire_time' => $attempt->expire_time->addSeconds($seconds),
                    'last_heartbeat_at' => $now,
                ]);
                $count++;
            }
        });

        return $count;
    }

    private function runningAttempts(int $assessmentId)
    {
        return StudentAssessmentAttempt::where('school_assessment_id', $assessmentId)
            ->where('status', 'in_progress')
            ->whereNull('finalized_at');
    }
}

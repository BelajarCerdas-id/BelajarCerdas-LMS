<?php

namespace App\Services\Proctor;

use App\Models\ProctorAuditLog;
use App\Models\ProctorEntryRequest;
use App\Models\SchoolAssessment;
use App\Models\StudentAssessmentAttempt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Collection;

class ProctorService
{
    public const CHANNEL = 'proctor:events';

    /** Roles that may open the proctor dashboard (the page is also school-scoped by middleware). */
    public const VIEW_ROLES = ['guru', 'administrator', 'admin sekolah', 'superadmin', 'super admin', 'kurikulum', 'office'];

    /** Roles that may assign the unlock-approver flag. */
    public const APPROVER_ADMIN_ROLES = ['administrator', 'superadmin', 'super admin'];

    /** Role names are stored with mixed case across the app, so compare case-insensitively. */
    public static function hasRole($user, array $roles): bool
    {
        return in_array(strtolower((string) $user?->role), array_map('strtolower', $roles), true);
    }

    /**
     * Fire-and-forget: the exam must never wait on the proctor. Failures are logged only.
     * Tags the event with its school so teacher tokens issued before the exam was created still match.
     */
    public function publish(array $event): void
    {
        try {
            if (!isset($event['school_id']) && isset($event['assessment_id'])) {
                $event['school_id'] = $this->schoolOf((int) $event['assessment_id']);
            }
            Redis::connection('proctor')->publish(self::CHANNEL, json_encode($event));
        } catch (\Throwable $e) {
            Log::warning('proctor publish failed', ['type' => $event['type'] ?? null, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Token format is verified by proctor-service/src/token.rs: base64url(json) . hex(hmac_sha256).
     * $examId scopes a student to exam-wide control events (pause, resume, stop) of that exam only.
     */
    public function token(int $userId, ?int $attemptId, array $assessmentIds, ?int $examId = null, ?int $schoolId = null): string
    {
        $claims = ['sub' => $userId, 'exp' => now()->addSeconds(config('proctor.token_ttl'))->timestamp];
        if ($schoolId !== null) {
            $claims['sch'] = $schoolId;
        }
        if ($attemptId !== null) {
            $claims['att'] = $attemptId;
        }
        if ($examId !== null) {
            $claims['ass'] = $examId;
        }
        if ($assessmentIds) {
            $claims['asm'] = array_values($assessmentIds);
        }

        $payload = rtrim(strtr(base64_encode(json_encode($claims)), '+/', '-_'), '=');

        return $payload.'.'.hash_hmac('sha256', $payload, (string) config('proctor.secret'));
    }

    /**
     * Teacher control state of one exam. Stopped wins over paused, paused over running.
     */
    public static function sessionState(SchoolAssessment $assessment): string
    {
        if ($assessment->proctor_stopped_at) {
            return 'stopped';
        }
        if ($assessment->proctor_paused_at) {
            return 'paused';
        }

        return $assessment->proctor_started_at ? 'running' : 'not_started';
    }

    /**
     * Audit trail for every proctor and approver action. Written in the caller's transaction when there is one.
     */
    public function audit(string $action, array $context = []): void
    {
        ProctorAuditLog::create([
            'actor_id' => Auth::id(),
            'actor_role' => Auth::user()?->role,
            'action' => $action,
            'school_partner_id' => $context['school_partner_id'] ?? null,
            'school_assessment_id' => $context['school_assessment_id'] ?? null,
            'student_id' => $context['student_id'] ?? null,
            'attempt_id' => $context['attempt_id'] ?? null,
            'detail' => $context['detail'] ?? null,
            'ip' => request()->ip(),
            'created_at' => now(),
        ]);
    }

    /**
     * Published assessments of this school whose window overlaps today (Asia/Jakarta).
     *
     * @return Collection<int, int>
     */
    public function heldTodayAssessmentIds(int $schoolPartnerId): Collection
    {
        $today = now('Asia/Jakarta');

        return SchoolAssessment::where('school_partner_id', $schoolPartnerId)
            ->where('status', 'published')
            ->where('start_date', '<=', $today->copy()->endOfDay())
            ->where('end_date', '>=', $today->copy()->startOfDay())
            ->pluck('id');
    }

    /** assessment_id -> school_partner_id; assessments never move between schools. */
    private function schoolOf(int $assessmentId): ?int
    {
        static $cache = [];

        return $cache[$assessmentId] ??= SchoolAssessment::whereKey($assessmentId)->value('school_partner_id');
    }

    /**
     * The attempt a student gets when they are admitted (or find the exam already open for them).
     */
    public function openAttempt(SchoolAssessment $assessment, int $userId): StudentAssessmentAttempt
    {
        $now = now();

        return StudentAssessmentAttempt::firstOrCreate(
            ['student_id' => $userId, 'school_assessment_id' => $assessment->id],
            [
                'start_time' => $now,
                'expire_time' => $now->copy()->addMinutes($assessment->duration),
                'last_heartbeat_at' => $now,
                'status' => 'in_progress',
            ]
        );
    }

    /**
     * A student who tries to start waits in the queue until the teacher admits them.
     * The teacher is only pinged for new requests; repeats return the existing row.
     */
    public function entryFor(SchoolAssessment $assessment, int $userId): ProctorEntryRequest
    {
        $entry = ProctorEntryRequest::query()->createOrFirst(
            ['school_assessment_id' => $assessment->id, 'student_id' => $userId],
            ['status' => 'pending']
        );

        if ($entry->wasRecentlyCreated) {
            $entry->load('student.studentProfile');
            $this->publish([
                'type' => 'entry_requested',
                'assessment_id' => $assessment->id,
                'request_id' => $entry->id,
                'user_id' => $userId,
                'student_name' => $entry->student?->studentProfile?->nama_lengkap ?? 'Siswa',
                'started' => $assessment->proctor_started_at !== null,
            ]);
        }

        return $entry;
    }

    /**
     * @return Collection<int, array>
     */
    public function pendingEntries(Collection $assessmentIds): Collection
    {
        return ProctorEntryRequest::with('student.studentProfile')
            ->whereIn('school_assessment_id', $assessmentIds)
            ->where('status', 'pending')
            ->orderBy('id')
            ->limit(2000)
            ->get()
            ->map(fn (ProctorEntryRequest $entry) => [
                'request_id' => $entry->id,
                'assessment_id' => $entry->school_assessment_id,
                'user_id' => $entry->student_id,
                'student_name' => $entry->student?->studentProfile?->nama_lengkap ?? 'Siswa',
            ])
            ->values();
    }
}

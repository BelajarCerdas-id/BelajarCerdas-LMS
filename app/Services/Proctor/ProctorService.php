<?php

namespace App\Services\Proctor;

use App\Models\ProctorAuditLog;
use App\Models\ProctorEntryRequest;
use App\Models\SchoolAssessment;
use App\Models\SchoolPartner;
use App\Models\StudentAssessmentAttempt;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProctorService
{
    public const CHANNEL = 'proctor:events';

    /** Roles that may open the proctor dashboard (the page is also school-scoped by middleware). */
    public const VIEW_ROLES = ['guru', 'administrator', 'admin sekolah', 'superadmin', 'super admin', 'kurikulum', 'office', 'kepala sekolah', 'wakil kepala sekolah', 'wakil kesiswaan', 'yayasan'];

    /** Roles that may assign the unlock-approver flag. */
    public const APPROVER_ADMIN_ROLES = ['administrator', 'superadmin', 'super admin'];

    /** Role names are stored with mixed case across the app, so compare case-insensitively. */
    public static function hasRole($user, array $roles): bool
    {
        return in_array(strtolower((string) $user?->role), array_map('strtolower', $roles), true);
    }

    /**
     * The schoolId=0 proctor views (snapshot, timeline) show every school:
     * platform admins only; school-scoped roles keep their single-school view.
     */
    public static function canCrossSchool($user, int $schoolId): bool
    {
        return $schoolId !== 0 || self::hasRole($user, self::APPROVER_ADMIN_ROLES);
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
     * Per-school proctor auto-start (school_partners.proctor_auto_start): while
     * a school has it enabled, a published assessment whose schedule window is
     * open (start_date <= now <= end_date, school timezone) counts as RUNNING
     * even though no teacher ever pressed 'Mulai' — the schedule is the actor.
     *
     * This is a VIRTUAL session: proctor_started_at is never written and no
     * session_started audit row exists for the open itself. Real teacher
     * decisions always win afterwards, because sessionState() short-circuits
     * first: proctor_stopped_at set -> stopped (a real stop overrides the
     * schedule permanently), proctor_paused_at set -> paused.
     *
     * No deadline may ever derive from this: a student's expire_time stays
     * their own attempt start + duration (openAttempt), computed only when
     * they open the exam.
     */
    public static function effectiveSessionState(SchoolAssessment $assessment, ?SchoolPartner $school = null): string
    {
        $state = self::sessionState($assessment);

        if ($state !== 'not_started') {
            return $state;
        }

        $school ??= SchoolPartner::find($assessment->school_partner_id);

        if (!$school?->proctor_auto_start || $assessment->status !== 'published') {
            return $state;
        }

        // same window semantics as the student start-timer gate: dates are
        // naive strings interpreted in the school's timezone
        $timezone = \App\Helpers\TimezoneHelper::getSchoolTimezone($school);
        $now = \App\Helpers\TimezoneHelper::now($timezone);
        $start = \App\Helpers\TimezoneHelper::parse($assessment->start_date, $timezone);
        $end = \App\Helpers\TimezoneHelper::parse($assessment->end_date, $timezone);

        if (($start && $now->lt($start)) || ($end && $now->gt($end))) {
            return $state;
        }

        return 'running';
    }

    /**
     * True when the running state came from the schedule (proctor_auto_start),
     * not from a teacher pressing start. The dashboard badges this so a
     * schedule-opened exam is never confused with a teacher-started one.
     */
    public static function virtualRunning(SchoolAssessment $assessment, ?SchoolPartner $school = null): bool
    {
        return self::sessionState($assessment) === 'not_started'
            && self::effectiveSessionState($assessment, $school) === 'running';
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
     * Published assessments of this school whose window overlaps the given day
     * (default today, Asia/Jakarta). The optional $day lets the admin snapshot
     * and timeline review past sessions; every other caller keeps the default.
     * schoolId 0 stays empty on purpose: the action endpoints must not treat a
     * bogus school as "every school".
     *
     * @return Collection<int, int>
     */
    public function heldTodayAssessmentIds(int $schoolPartnerId, ?CarbonInterface $day = null): Collection
    {
        return $this->heldOn($day, SchoolAssessment::where('school_partner_id', $schoolPartnerId))->pluck('id');
    }

    /**
     * Cross-school form for the platform-admin dashboard (schoolId=0): every
     * partner school's published assessments held on the day. Only the read
     * paths may use this — the action endpoints stay per-school so the
     * {schoolId} scoping keeps its teeth.
     *
     * @return Collection<int, int>
     */
    public function heldAssessmentIdsAllSchools(?CarbonInterface $day = null): Collection
    {
        return $this->heldOn($day, SchoolAssessment::query())->pluck('id');
    }

    /** Window overlap: the schedule intersects the day (Asia/Jakarta wall time, like every other proctor day bound). */
    private function heldOn(?CarbonInterface $day, $query)
    {
        $day ??= now('Asia/Jakarta');

        return $query->where('status', 'published')
            ->where('start_date', '<=', $day->copy()->endOfDay())
            ->where('end_date', '>=', $day->copy()->startOfDay());
    }

    /**
     * The proctor review day from ?date=YYYY-MM-DD (Asia/Jakarta). Missing or
     * empty means today; anything that is not a real calendar date in that
     * exact format returns null and the caller answers 422.
     */
    public static function reviewDay(?string $raw): ?CarbonInterface
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return now('Asia/Jakarta')->startOfDay();
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw) !== 1) {
            return null;
        }

        // '2026-02-30' rolls over instead of failing, so the value must
        // round-trip to be accepted as a real calendar date
        $parsed = \DateTime::createFromFormat('!Y-m-d', $raw, new \DateTimeZone('Asia/Jakarta'));
        if ($parsed === false || $parsed->format('Y-m-d') !== $raw) {
            return null;
        }

        return \Carbon\Carbon::instance($parsed);
    }

    /**
     * Review day straight from the request; garbage aborts with 422. Shared by
     * the snapshot and the timeline so the admin date filter behaves the same
     * everywhere.
     */
    public static function reviewDayFrom(Request $request): CarbonInterface
    {
        $raw = $request->query('date');
        if ($raw !== null && !is_string($raw)) {
            abort(422, 'Parameter date harus berformat YYYY-MM-DD.');
        }

        return self::reviewDay($raw) ?? abort(422, 'Parameter date harus berformat YYYY-MM-DD.');
    }

    /** assessment_id -> school_partner_id; assessments never move between schools. */
    private function schoolOf(int $assessmentId): ?int
    {
        static $cache = [];

        return $cache[$assessmentId] ??= SchoolAssessment::whereKey($assessmentId)->value('school_partner_id');
    }

    /**
     * The attempt a student gets when they are admitted (or find the exam already open for them).
     *
     * THE expire_time INVARIANT: an attempt's deadline is always the student's own
     * start_time + duration, computed here and nowhere else. The only legal
     * adjustments afterwards are pause shifts (resume adds the exact paused
     * seconds, because a pause must not consume exam time). Proctor stop /
     * terminate paths must never rewrite it: production data from 2026-11-25
     * showed stop-rewritten deadlines (expire == proctor_stopped_at) mistaken
     * for a second 'proctor_started_at + duration' computation; no such second
     * computation exists or is allowed.
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
     * Per-school auto-admit (school_partners.proctor_auto_admit): while a school
     * has it enabled, a student hitting the entry gate is admitted without the
     * teacher clicking admit on each student. Semantics: it only applies while
     * the proctor session is effectively RUNNING — either the teacher pressed
     * 'start', or the school's proctor_auto_start opened the window on
     * schedule (effectiveSessionState) — and it never overrides a teacher
     * decision: an explicitly denied entry stays denied (403) until a
     * PIC-approved revocation puts it back in the queue.
     *
     * Mirrors ProctorEntryController::decideOne so the dashboard, timeline and
     * the student's WebSocket all see the admission exactly like a manual
     * admit: the entry row becomes admitted (decided_by null = system actor),
     * the same entry_resolved event is published (with an extra `auto` marker)
     * and an audit row is written with the system as the actor.
     */
    public function autoAdmitEntry(SchoolAssessment $assessment, ProctorEntryRequest $entry, SchoolPartner $school): bool
    {
        if (!$school->proctor_auto_admit || self::effectiveSessionState($assessment, $school) !== 'running') {
            return false;
        }

        $admitted = DB::transaction(function () use ($assessment, $entry, $school): bool {
            // serialize with session stop/pause (ProctorSessionController locks this
            // same row) and re-check the effective state the way
            // ProctorEntryController::decideOne does: a stop landing between the
            // caller's checks and this lock must not be admitted on the caller's
            // stale in-memory copy
            $freshAssessment = SchoolAssessment::lockForUpdate()->find($assessment->id);
            if (!$freshAssessment || self::effectiveSessionState($freshAssessment, $school) !== 'running') {
                return false;
            }

            $locked = ProctorEntryRequest::lockForUpdate()->find($entry->id);

            // a teacher decision (admit or deny) always wins over auto-admit; this
            // also keeps an explicit denial from being overridden on a retry
            if (!$locked || $locked->status !== 'pending') {
                return false;
            }

            $locked->update(['status' => 'admitted', 'decided_by' => null, 'decided_at' => now()]);

            return true;
        });

        if (!$admitted) {
            return false;
        }

        // same audit trail the manual decide path writes, with the system as the actor
        ProctorAuditLog::create([
            'actor_id' => null,
            'actor_role' => 'auto-admit',
            'action' => 'entry_auto_admitted',
            'school_partner_id' => $school->id,
            'school_assessment_id' => $assessment->id,
            'student_id' => $entry->student_id,
            'attempt_id' => null,
            'detail' => ['request_id' => $entry->id],
            'ip' => request()->ip(),
            'created_at' => now(),
        ]);

        // the same event the manual decide path publishes, so dashboards and the
        // student's own socket resolve the entry identically
        $this->publish([
            'type' => 'entry_resolved',
            'request_id' => $entry->id,
            'assessment_id' => $assessment->id,
            'user_id' => $entry->student_id,
            'status' => 'approved',
            'auto' => true,
        ]);

        return true;
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

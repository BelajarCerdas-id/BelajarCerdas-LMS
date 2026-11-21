<?php

namespace App\Http\Controllers;

use App\Models\SchoolAssessment;
use App\Models\StudentAssessmentAnticheatViolation;
use App\Models\StudentAssessmentAttempt;
use App\Services\LMS\AssessmentSummaryService\AssessmentSummaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Anti-cheat endpoints for the school assessment exam.
 * Same JSON contract as the previous implementation
 * (status: ok|warning|blocked|ignored, count, max_count), but every
 * rule is now server-verified: no client-controlled grace periods,
 * atomic cooldown, and a grace window for file pickers only when the
 * server actually saw an essay image upload recently.
 */
class AssessmentAntiCheatController extends Controller
{
    public const MAX_STRIKES = 5;
    public const COOLDOWN_SECONDS = 10;
    public const HEARTBEAT_MAX_GAP = 35;
    public const FILE_GRACE_GAP = 180;
    public const FILE_GRACE_TTL = 180;
    public const STARTUP_GRACE = 60;

    public function reportTabSwitch(Request $request, $role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId, $semester, $assessmentId): JsonResponse
    {
        $attempt = $this->activeAttempt($assessmentId);

        if (!$attempt) {
            return response()->json(['status' => 'ignored']);
        }

        // Warnings (reload, alt-tab while typing) are logged only: no strike, no cooldown, no modal
        if ($request->boolean('warning')) {
            $this->recordViolation($attempt, $request, $request->input('reason', 'unspecified'), 'warning');
            return response()->json(['status' => 'ok']);
        }

        // Server-side cooldown dedupes cascading duplicate triggers within 10s
        // (visibilitychange + blur + pagehide firing together). Forced reports
        // (fullscreen exits) bypass it so every exit counts.
        $cooldownKey = $this->cooldownKey($attempt->id);
        $isForced = $request->boolean('force');
        if (!$isForced && !Cache::add($cooldownKey, true, self::COOLDOWN_SECONDS)) {
            return response()->json(['status' => 'ok']);
        }

        if ($isForced) {
            // Event-storm guard: Chrome can fire several fullscreenchange exit
            // events during rapid toggle / hold-Esc retry cycles. One counted
            // strike per reason per 5s is plenty for a real exit sequence.
            $forcedKey = "anticheat_forced_{$attempt->id}_{$request->input('reason', 'unspecified')}";
            if (!Cache::add($forcedKey, true, 5)) {
                return response()->json(['status' => 'ok']);
            }
            // still claim the general cooldown so a cascade of duplicate
            // fullscreen events from a single exit doesn't multiply the strike
            Cache::put($cooldownKey, true, self::COOLDOWN_SECONDS);
        }

        $attempt->increment('tab_switch_count');
        $attempt->refresh();

        $this->recordViolation($attempt, $request, $request->input('reason', 'unspecified'));

        return $this->settleStrike($attempt, $assessmentId);
    }

    public function checkAttemptStatus($role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId, $semester, $assessmentId): JsonResponse
    {
        $attempt = StudentAssessmentAttempt::where('student_id', Auth::id())
            ->where('school_assessment_id', $assessmentId)
            ->first();

        if (!$attempt) {
            return response()->json(['status' => 'error']);
        }

        if ($attempt->status === 'cheating' || $attempt->tab_switch_count >= self::MAX_STRIKES) {
            return response()->json([
                'status' => 'blocked',
                'count' => $attempt->tab_switch_count,
                'max_count' => self::MAX_STRIKES,
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'count' => $attempt->tab_switch_count,
            'max_count' => self::MAX_STRIKES,
        ]);
    }

    public function heartbeat(Request $request, $role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId, $semester, $assessmentId): JsonResponse
    {
        $attempt = $this->activeAttempt($assessmentId);

        if (!$attempt) {
            return response()->json(['status' => 'ignored']);
        }

        $now = now();
        $lastHeartbeat = $attempt->last_heartbeat_at;

        // Carbon 3's diffInSeconds is signed; gaps must be measured in absolute seconds
        $startupGrace = $attempt->start_time && abs($now->diffInSeconds($attempt->start_time)) < self::STARTUP_GRACE;

        // Extended gap window ONLY when the server saw a recent essay image
        // upload from this user (native camera/gallery picker suspends JS).
        // The client-sent is_selecting_file flag is never trusted on its own.
        $maxGap = self::HEARTBEAT_MAX_GAP;
        if (Cache::has($this->fileGraceKey())) {
            $maxGap = self::FILE_GRACE_GAP;
        }

        $cooldownKey = $this->cooldownKey($attempt->id);
        $gap = $lastHeartbeat ? abs($now->diffInSeconds($lastHeartbeat)) : 0;

        if ($lastHeartbeat && !$startupGrace && !Cache::has($cooldownKey) && $gap > $maxGap) {
            if (!Cache::add($cooldownKey, true, self::COOLDOWN_SECONDS)) {
                return response()->json(['status' => 'ok']);
            }

            // A client report (visibility/blur) already struck this absence; don't strike it twice.
            // The 2s slack covers second-precision DB timestamps.
            $struckDuringGap = StudentAssessmentAnticheatViolation::where('student_assessment_attempt_id', $attempt->id)
                ->where('severity', 'strike')
                ->where('created_at', '>=', $now->copy()->subSeconds($gap + 2))
                ->exists();
            if ($struckDuringGap) {
                $attempt->update(['last_heartbeat_at' => $now]);
                return response()->json(['status' => 'ok']);
            }

            $attempt->increment('tab_switch_count');
            $attempt->refresh();

            $this->recordViolation($attempt, $request, 'heartbeat_gap');

            return $this->settleStrike($attempt, $assessmentId);
        }

        // Normal heartbeat: throttle DB writes to once every 15 seconds
        if (!$lastHeartbeat || $gap >= 15) {
            $attempt->update(['last_heartbeat_at' => $now]);
        }

        return response()->json(['status' => 'ok']);
    }

    private function activeAttempt(int $assessmentId): ?StudentAssessmentAttempt
    {
        $attempt = StudentAssessmentAttempt::where('student_id', Auth::id())
            ->where('school_assessment_id', $assessmentId)
            ->first();

        if (!$attempt || $attempt->status !== 'in_progress') {
            return null;
        }

        if ($attempt->expire_time && now()->gt($attempt->expire_time)) {
            return null;
        }

        return $attempt;
    }

    private function settleStrike(StudentAssessmentAttempt $attempt, int $assessmentId): JsonResponse
    {
        if ($attempt->tab_switch_count >= self::MAX_STRIKES) {
            $attempt->update([
                'status' => 'cheating',
                'last_heartbeat_at' => now(),
            ]);

            $assessment = SchoolAssessment::find($assessmentId);
            if ($assessment) {
                app(AssessmentSummaryService::class)->updateStudentAssessmentSummary(Auth::id(), $assessment);
            }

            return response()->json(['status' => 'blocked']);
        }

        $attempt->update(['last_heartbeat_at' => now()]);

        return $this->warningResponse($attempt);
    }

    private function warningResponse(StudentAssessmentAttempt $attempt): JsonResponse
    {
        return response()->json([
            'status' => 'warning',
            'count' => $attempt->tab_switch_count,
            'max_count' => self::MAX_STRIKES,
        ]);
    }

    private function cooldownKey(int $attemptId): string
    {
        return "tab_switch_cooldown_{$attemptId}";
    }

    /**
     * Store one violation record with client context: ip, browser, device.
     */
    private function recordViolation(StudentAssessmentAttempt $attempt, Request $request, string $reason, string $severity = 'strike'): void
    {
        try {
            $ua = (string) $request->userAgent();
            $parsed = StudentAssessmentAnticheatViolation::parseUserAgent($ua);

            // behind Cloudflare the direct peer is the edge; prefer the real client IP
            $ip = $request->header('CF-Connecting-IP') ?: $request->ip();

            StudentAssessmentAnticheatViolation::create([
                'student_assessment_attempt_id' => $attempt->id,
                'student_id' => $attempt->student_id,
                'school_assessment_id' => $attempt->school_assessment_id,
                'reason' => substr($reason, 0, 50) ?: 'unspecified',
                'severity' => $severity,
                'ip' => $ip,
                'browser' => $parsed['browser'],
                'browser_version' => $parsed['version'],
                'device_type' => $parsed['deviceType'],
                'platform' => $parsed['platform'],
                'user_agent' => $ua,
            ]);
        } catch (\Throwable $e) {
            // never let violation logging break the exam flow
        }
    }

    public static function fileGraceKey(): string
    {
        return 'anticheat_file_grace_' . Auth::id();
    }

    /**
     * Called by StudentAssessmentExamController::storeImageEssay after a
     * successful essay image upload so the next heartbeat gap gets the
     * extended camera/gallery grace window.
     */
    public static function grantFileGrace(): void
    {
        Cache::put(self::fileGraceKey(), true, self::FILE_GRACE_TTL);
    }
}

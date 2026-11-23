<?php

namespace App\Http\Controllers;

use App\Models\ProctorEntryRequest;
use App\Models\SchoolAssessment;
use App\Services\Proctor\ProctorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Teacher gate: nobody starts an exam until the teacher starts the session and admits them.
 */
class ProctorEntryController extends Controller
{
    public function __construct(private ProctorService $proctor) {}

    public function decide(Request $request, $role, $schoolName, $schoolId): JsonResponse
    {
        if (!ProctorService::hasRole(Auth::user(), ProctorService::VIEW_ROLES)) {
            abort(403);
        }

        $validated = $request->validate([
            'decisions' => 'required|array|min:1|max:500',
            'decisions.*.request_id' => 'required|integer',
            'decisions.*.approve' => 'required|boolean',
        ]);

        $heldIds = $this->proctor->heldTodayAssessmentIds((int) $schoolId);
        $decided = 0;
        $skipped = 0;

        foreach ($validated['decisions'] as $decision) {
            $result = $this->decideOne((int) $schoolId, $heldIds, (int) $decision['request_id'], (bool) $decision['approve']);

            if ($result === null) {
                $skipped++;
                continue;
            }

            $decided++;
            $this->proctor->publish(['type' => 'entry_resolved', ...$result]);
        }

        return response()->json(['status' => 'ok', 'decided' => $decided, 'skipped' => $skipped]);
    }

    /**
     * A teacher asks the PIC to revoke a denied entry. Mirrors the unlock flow:
     * request first, PIC approves, the student re-enters through the normal admit queue.
     */
    public function requestRevoke($role, $schoolName, $schoolId, int $entryId): JsonResponse
    {
        if (!ProctorService::hasRole(Auth::user(), ['guru'])) {
            return response()->json(['message' => 'Hanya guru yang dapat meminta pembatalan tolakan.'], 403);
        }

        $window = (int) config('proctor.unlock_window');

        $result = DB::transaction(function () use ($schoolId, $entryId, $window) {
            $entry = ProctorEntryRequest::lockForUpdate()->find($entryId);
            if (!$entry || (int) $entry->assessment?->school_partner_id !== (int) $schoolId) {
                return ['error' => 'Permintaan masuk tidak ditemukan.', 'code' => 404];
            }
            if (!$this->proctor->heldTodayAssessmentIds((int) $schoolId)->contains($entry->school_assessment_id)) {
                return ['error' => 'Ujian ini tidak dijadwalkan hari ini.', 'code' => 422];
            }
            if ($entry->status !== 'denied') {
                return ['error' => 'Hanya siswa yang ditolak yang dapat diajukan pembatalan.', 'code' => 422];
            }
            if ($entry->revoke_status === 'pending' && $entry->revoke_expires_at?->isFuture()) {
                return ['error' => 'Pembatalan tolakan untuk siswa ini masih menunggu persetujuan PIC.', 'code' => 422];
            }

            $entry->update([
                'revoke_status' => 'pending',
                'revoke_requested_by' => Auth::id(),
                'revoke_expires_at' => now()->addSeconds($window),
                'revoke_decided_by' => null,
                'revoke_decided_at' => null,
            ]);
            $this->proctor->audit('entry_revocation_requested', [
                'school_partner_id' => (int) $schoolId,
                'school_assessment_id' => $entry->school_assessment_id,
                'student_id' => $entry->student_id,
                'detail' => ['request_id' => $entry->id],
            ]);

            return $entry;
        });

        if (is_array($result)) {
            return response()->json(['message' => $result['error']], $result['code']);
        }

        $this->proctor->publish([
            'type' => 'entry_revoke_requested',
            'assessment_id' => $result->school_assessment_id,
            'user_id' => $result->student_id,
            'request_id' => $result->id,
        ]);

        return response()->json(['status' => 'pending', 'expires_at' => $result->revoke_expires_at->toIso8601String()]);
    }

    /** PIC decision on a revocation request. */
    public function decideRevoke($role, $schoolName, $schoolId, int $entryId, string $decision): JsonResponse
    {
        $approve = $decision === 'approve';
        if (!$approve && $decision !== 'deny') {
            abort(404);
        }

        $user = Auth::user();
        if (!$user->can_approve_unlock) {
            $this->proctor->audit('entry_revocation_forbidden', ['school_partner_id' => (int) $schoolId, 'detail' => ['request_id' => $entryId, 'approve' => $approve]]);
            return response()->json(['message' => 'Hanya PIC yang dapat menyetujui pembatalan tolakan.'], 403);
        }

        $result = DB::transaction(function () use ($user, $schoolId, $entryId, $approve) {
            $entry = ProctorEntryRequest::with('assessment')->lockForUpdate()->find($entryId);
            if (!$entry || (int) $entry->assessment?->school_partner_id !== (int) $schoolId) {
                return ['error' => 'Permintaan masuk tidak ditemukan.', 'code' => 404];
            }
            if ($entry->revoke_status !== 'pending') {
                return ['error' => 'Permintaan pembatalan sudah diproses.', 'code' => 422];
            }
            if ((int) $entry->revoke_requested_by === (int) $user->id) {
                $this->proctor->audit('entry_revocation_self_refused', ['school_partner_id' => (int) $schoolId, 'detail' => ['request_id' => $entry->id]]);
                return ['error' => 'Permintaan sendiri tidak dapat disetujui.', 'code' => 403];
            }

            // same default-deny as unlock: past the window the request is expired, not approved
            if ($entry->revoke_expires_at?->isPast()) {
                $entry->update(['revoke_status' => 'expired', 'revoke_decided_by' => $user->id, 'revoke_decided_at' => now()]);
                $this->proctor->audit('entry_revocation_expired', ['school_partner_id' => (int) $schoolId, 'detail' => ['request_id' => $entry->id]]);
                return ['error' => 'Waktu persetujuan habis, permintaan ditolak otomatis.', 'code' => 422];
            }

            if ($approve && ProctorService::sessionState($entry->assessment) !== 'running') {
                $entry->update(['revoke_status' => 'denied', 'revoke_decided_by' => $user->id, 'revoke_decided_at' => now()]);
                $this->proctor->audit('entry_revocation_denied', ['school_partner_id' => (int) $schoolId, 'detail' => ['request_id' => $entry->id]]);
                return ['error' => 'Ujian sudah tidak menerima siswa.', 'code' => 422];
            }

            if ($approve) {
                // back into the normal admit queue; the student re-opens the exam and waits again
                $entry->update([
                    'status' => 'pending',
                    'revoke_status' => 'approved',
                    'revoke_decided_by' => $user->id,
                    'revoke_decided_at' => now(),
                ]);
                $this->proctor->audit('entry_revocation_approved', [
                    'school_partner_id' => (int) $schoolId,
                    'school_assessment_id' => $entry->school_assessment_id,
                    'student_id' => $entry->student_id,
                    'detail' => ['request_id' => $entry->id],
                ]);
            } else {
                $entry->update(['revoke_status' => 'denied', 'revoke_decided_by' => $user->id, 'revoke_decided_at' => now()]);
                $this->proctor->audit('entry_revocation_denied', ['school_partner_id' => (int) $schoolId, 'detail' => ['request_id' => $entry->id]]);
            }

            return $entry;
        });

        if (is_array($result)) {
            return response()->json(['message' => $result['error']], $result['code']);
        }

        $this->proctor->publish([
            'type' => 'entry_revoke_resolved',
            'assessment_id' => $result->school_assessment_id,
            'user_id' => $result->student_id,
            'request_id' => $result->id,
            'status' => $approve ? 'approved' : 'denied',
        ]);

        return response()->json(['status' => 'ok']);
    }

    /**
     * @return array|null null when the request is not pending, not in this school, or the exam is not running for approvals.
     */
    private function decideOne(int $schoolId, $heldIds, int $requestId, bool $approve): ?array
    {
        return DB::transaction(function () use ($schoolId, $heldIds, $requestId, $approve) {
            $entry = ProctorEntryRequest::with('assessment')->lockForUpdate()->find($requestId);
            if (!$entry || $entry->status !== 'pending' || !$heldIds->contains($entry->school_assessment_id)) {
                return null;
            }

            $assessment = $entry->assessment;
            if ($approve && (ProctorService::sessionState($assessment) !== 'running' || $assessment->end_date?->isPast())) {
                return null;
            }

            $context = [
                'school_partner_id' => $schoolId,
                'school_assessment_id' => $assessment->id,
                'student_id' => $entry->student_id,
                'detail' => ['request_id' => $entry->id],
            ];

            if ($approve) {
                $attempt = $this->proctor->openAttempt($assessment, $entry->student_id);
                $context['attempt_id'] = $attempt->id;
            }
            $entry->update(['status' => $approve ? 'admitted' : 'denied', 'decided_by' => Auth::id(), 'decided_at' => now()]);
            $this->proctor->audit($approve ? 'entry_admitted' : 'entry_denied', $context);

            return [
                'request_id' => $entry->id,
                'assessment_id' => $assessment->id,
                'user_id' => $entry->student_id,
                'status' => $approve ? 'approved' : 'denied',
            ];
        });
    }
}

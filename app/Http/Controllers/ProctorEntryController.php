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

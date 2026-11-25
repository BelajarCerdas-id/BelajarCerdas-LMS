<?php

namespace App\Http\Controllers;

use App\Models\ProctorAuditLog;
use App\Models\SchoolAssessment;
use App\Models\SchoolPartner;
use App\Models\StudentAssessmentAnticheatViolation;
use App\Models\StudentProfile;
use App\Models\UserAccount;
use App\Services\Proctor\ProctorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Timeline of proctor, approver and administrator actions, plus student strikes, for one school.
 * Scopes: all exams, one exam, or one student (across the school's exams).
 */
class ProctorTimelineController extends Controller
{
    private const LIMIT = 300;

    private const LABELS = [
        'session_started' => 'Ujian dimulai',
        'session_paused' => 'Ujian dijeda',
        'session_resumed' => 'Ujian dilanjutkan',
        'session_stopped' => 'Ujian dihentikan',
        'entry_admitted' => 'Siswa diizinkan masuk',
        'entry_auto_admitted' => 'Siswa diizinkan masuk (otomatis)',
        'entry_denied' => 'Siswa ditolak masuk',
        'unlock_requested' => 'Permintaan buka kunci',
        'unlock_approved' => 'Buka kunci disetujui',
        'unlock_denied' => 'Buka kunci ditolak',
        'unlock_expired' => 'Buka kunci kedaluwarsa (ditolak otomatis)',
        'unlock_self_refused' => 'Persetujuan sendiri ditolak sistem',
        'unlock_forbidden' => 'Percobaan setujui tanpa izin PIC',
        'pic_granted' => 'Izin PIC diberikan',
        'pic_revoked' => 'Izin PIC dicabut',
    ];

    public function index($role, $schoolName, $schoolId): View
    {
        $this->ensureViewer();
        $this->ensureSchoolScoped((int) $schoolId);

        // schoolId=0 (platform admin): the exam dropdown spans every school
        $assessments = SchoolAssessment::when((int) $schoolId !== 0, fn ($q) => $q->where('school_partner_id', $schoolId))
            ->orderByDesc('start_date')
            ->limit(200)
            ->get(['id', 'title', 'start_date']);

        return view('features.lms.teacher.proctor.proctor-timeline', [
            'role' => $role,
            'schoolName' => $schoolName,
            'schoolId' => $schoolId,
            'assessments' => $assessments,
        ]);
    }

    public function data(Request $request, $role, $schoolName, $schoolId): JsonResponse
    {
        $this->ensureViewer();
        $this->ensureSchoolScoped((int) $schoolId);

        // review any day, not just today: ?date=YYYY-MM-DD (admins reviewing yesterday's sessions)
        $day = ProctorService::reviewDayFrom($request);
        $dayStart = $day->copy()->startOfDay();
        $dayEnd = $day->copy()->endOfDay();

        $crossSchool = (int) $schoolId === 0;
        $assessmentId = $request->integer('assessment_id') ?: null;
        $studentId = $request->integer('student_id') ?: null;

        // schoolId=0 (platform admin): no school scope — events from every school
        $examIds = $crossSchool
            ? SchoolAssessment::pluck('id')
            : SchoolAssessment::where('school_partner_id', $schoolId)->pluck('id');

        if ($assessmentId && !$examIds->contains($assessmentId)) {
            abort(404);
        }

        $audit = ($crossSchool ? ProctorAuditLog::query() : ProctorAuditLog::where('school_partner_id', $schoolId))
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->when($assessmentId, fn ($q) => $q->where('school_assessment_id', $assessmentId))
            ->when($studentId, fn ($q) => $q->where('student_id', $studentId))
            ->orderByDesc('created_at')->orderByDesc('id')
            ->limit(self::LIMIT)
            ->get();

        $violations = ($crossSchool
            ? StudentAssessmentAnticheatViolation::query()
            : StudentAssessmentAnticheatViolation::whereIn('school_assessment_id', $examIds))
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->when($assessmentId, fn ($q) => $q->where('school_assessment_id', $assessmentId))
            ->when($studentId, fn ($q) => $q->where('student_id', $studentId))
            ->orderByDesc('created_at')->orderByDesc('id')
            ->limit(self::LIMIT)
            ->get();

        $names = $this->names($audit->pluck('student_id')->merge($violations->pluck('student_id')));
        $emails = $this->emails($audit->pluck('actor_id'));

        $titleRows = SchoolAssessment::whereIn('id', $examIds)->get($crossSchool ? ['id', 'title', 'school_partner_id'] : ['id', 'title']);
        $titles = $titleRows->pluck('title', 'id');
        // cross-school extras: each exam's school and the school names for the event rows
        $examSchool = $crossSchool ? $titleRows->pluck('school_partner_id', 'id') : collect();
        $schoolNames = $crossSchool
            ? SchoolPartner::whereIn('id', $audit->pluck('school_partner_id')->merge($examSchool->values())->filter()->unique())->pluck('nama_sekolah', 'id')
            : collect();

        $events = $audit->map(function (ProctorAuditLog $a) use ($names, $emails, $titles, $schoolNames, $crossSchool) {
            $row = [
                'at' => $a->created_at->timezone('Asia/Jakarta')->format('d M Y H:i:s'),
                'ts' => $a->created_at->timestamp,
                'source' => 'proctor',
                'label' => self::LABELS[$a->action] ?? $a->action,
                'actor' => $a->actor_id ? ($emails[$a->actor_id] ?? 'Pengguna') : 'Sistem',
                'actor_role' => $a->actor_role,
                'student' => $a->student_id ? ($names[$a->student_id] ?? 'Siswa') : null,
                'exam' => $a->school_assessment_id ? ($titles[$a->school_assessment_id] ?? null) : null,
                'detail' => $this->detail($a),
            ];
            if ($crossSchool) {
                $row['school_name'] = $schoolNames[$a->school_partner_id] ?? '-';
            }

            return $row;
        })->concat($violations->map(function (StudentAssessmentAnticheatViolation $v) use ($names, $titles, $examSchool, $schoolNames, $crossSchool) {
            $row = [
                'at' => $v->created_at->timezone('Asia/Jakarta')->format('d M Y H:i:s'),
                'ts' => $v->created_at->timestamp,
                'source' => 'siswa',
                'label' => ($v->severity === 'warning' ? 'Peringatan: ' : 'Pelanggaran: ').$v->reason,
                'actor' => null,
                'actor_role' => null,
                'student' => $names[$v->student_id] ?? 'Siswa',
                'exam' => $titles[$v->school_assessment_id] ?? null,
                'detail' => null,
            ];
            if ($crossSchool) {
                $row['school_name'] = $schoolNames[$examSchool[$v->school_assessment_id] ?? null] ?? '-';
            }

            return $row;
        }))->sortByDesc('ts')->values();

        return response()->json([
            'events' => $events->take(self::LIMIT)->values(),
            'truncated' => $events->count() > self::LIMIT,
        ]);
    }

    public function students(Request $request, $role, $schoolName, $schoolId): JsonResponse
    {
        $this->ensureViewer();
        $this->ensureSchoolScoped((int) $schoolId);

        $term = trim((string) $request->query('q'));
        if (mb_strlen($term) < 2) {
            return response()->json(['students' => []]);
        }

        // schoolId=0 (platform admin): the student search spans every school
        $students = StudentProfile::when((int) $schoolId !== 0, fn ($q) => $q->where('school_partner_id', $schoolId))
            ->where('nama_lengkap', 'like', "%{$term}%")
            ->orderBy('nama_lengkap')
            ->limit(30)
            ->get(['user_id', 'nama_lengkap']);

        return response()->json([
            'students' => $students->map(fn ($s) => ['id' => $s->user_id, 'name' => $s->nama_lengkap])->values(),
        ]);
    }

    private function detail(ProctorAuditLog $a): ?string
    {
        $d = $a->detail ?? [];

        return match ($a->action) {
            'session_stopped' => isset($d['attempts']) ? "{$d['attempts']} siswa dihentikan" : null,
            'session_resumed' => isset($d['paused_seconds'])
                ? "dijeda {$d['paused_seconds']} detik, {$d['attempts']} siswa diperpanjang" : null,
            'pic_granted', 'pic_revoked' => $d['target_email'] ?? null,
            default => null,
        };
    }

    private function names($ids): array
    {
        $ids = collect($ids)->filter()->unique()->values();

        return StudentProfile::whereIn('user_id', $ids)->pluck('nama_lengkap', 'user_id')->all();
    }

    private function emails($ids): array
    {
        $ids = collect($ids)->filter()->unique()->values();

        return UserAccount::whereIn('id', $ids)->pluck('email', 'id')->all();
    }

    private function ensureViewer(): void
    {
        if (!ProctorService::hasRole(Auth::user(), ProctorService::VIEW_ROLES)) {
            abort(403);
        }
    }

    /** schoolId=0 (all schools) is a platform-admin-only view, like the snapshot. */
    private function ensureSchoolScoped(int $schoolId): void
    {
        if (!ProctorService::canCrossSchool(Auth::user(), $schoolId)) {
            abort(403);
        }
    }
}

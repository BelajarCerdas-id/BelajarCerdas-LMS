<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\SchoolAssessment;
use App\Models\SchoolPartner;
use App\Models\StudentAssessmentAnswer;
use App\Models\StudentAssessmentAttempt;
use App\Models\StudentAssessmentSummary;
use App\Models\UserAccount;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AssessmentDebugController extends Controller
{
    /**
     * Otorisasi ketat: Hanya Administrator utama yang dapat mengakses
     */
    protected function authorizeAdmin(): void
    {
        if (!Auth::check() || !in_array(strtolower((string) Auth::user()->role), ['administrator', 'admin'])) {
            abort(403, 'Akses ditolak. Halaman debug ini hanya dapat diakses oleh Administrator.');
        }
    }

    /**
     * Halaman Utama Assessment Debugger
     */
    public function index(Request $request, string $role): View
    {
        $this->authorizeAdmin();

        $schools = SchoolPartner::select('id', 'nama_sekolah', 'npsn', 'jenjang_sekolah')
            ->orderBy('nama_sekolah', 'asc')
            ->get();

        return view('features.lms.administrator.assessment-debug', compact('role', 'schools'));
    }

    /**
     * Data List Attempt Siswa (AJAX)
     */
    public function getAttempts(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $search = $request->input('search');
        $schoolId = $request->input('school_id');
        $status = $request->input('status');

        $query = StudentAssessmentAttempt::with([
            'UserAccount.StudentProfile.SchoolPartner',
            'UserAccount.StudentSchoolClass.SchoolClass',
            'SchoolAssessment.SchoolAssessmentType',
            'SchoolAssessment.Mapel',
            'SchoolAssessment.SchoolClass',
            'SchoolAssessment.SchoolPartner'
        ]);

        if (!empty($status) && $status !== 'all') {
            $query->where('status', $status);
        }

        if (!empty($schoolId) && $schoolId !== 'all') {
            $query->where(function ($q) use ($schoolId) {
                $q->whereHas('SchoolAssessment', function ($sa) use ($schoolId) {
                    $sa->where('school_partner_id', $schoolId);
                })->orWhereHas('UserAccount.StudentProfile', function ($sp) use ($schoolId) {
                    $sp->where('school_partner_id', $schoolId);
                });
            });
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('UserAccount.StudentProfile', function ($sp) use ($search) {
                    $sp->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%");
                })->orWhereHas('SchoolAssessment.SchoolAssessmentType', function ($st) use ($search) {
                    $st->where('name', 'like', "%{$search}%");
                })->orWhereHas('SchoolAssessment.Mapel', function ($m) use ($search) {
                    $m->where('mata_pelajaran', 'like', "%{$search}%");
                });
            });
        }

        // Summary Counts
        $summaryCounts = [
            'total' => StudentAssessmentAttempt::count(),
            'cheating' => StudentAssessmentAttempt::where('status', 'cheating')->count(),
            'in_progress' => StudentAssessmentAttempt::where('status', 'in_progress')->count(),
            'submitted' => StudentAssessmentAttempt::where('status', 'submitted')->count(),
            'timeout' => StudentAssessmentAttempt::where('status', 'timeout')->count(),
        ];

        $attempts = $query->latest('updated_at')->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $attempts->items(),
            'pagination' => [
                'current_page' => $attempts->currentPage(),
                'last_page' => $attempts->lastPage(),
                'per_page' => $attempts->perPage(),
                'total' => $attempts->total(),
            ],
            'summary' => $summaryCounts
        ]);
    }

    /**
     * Buka Kunci Ujian Siswa (Unlock Test)
     * Mengembalikan status ke 'in_progress', reset tab_switch_count ke 0, perpanjang waktu jika habis,
     * dan bersihkan jawaban kosong hasil auto-submit agar siswa dapat melanjutkan ujian.
     */
    public function unlock(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin();

        $attempt = StudentAssessmentAttempt::with('SchoolAssessment')->findOrFail($id);
        $duration = $attempt->SchoolAssessment->duration ?? 60;
        $extraMinutes = (int) $request->input('extra_minutes', $duration);

        if ($extraMinutes <= 0) {
            $extraMinutes = $duration;
        }

        $now = Carbon::now();
        $expireTime = $now->copy()->addMinutes($extraMinutes);

        $attempt->update([
            'status' => 'in_progress',
            'tab_switch_count' => 0,
            'expire_time' => $expireTime
        ]);

        // Bersihkan jawaban yang kosong (null) akibat auto-submit saat timeout/cheating,
        // sehingga soal-soal tersebut kembali berstatus belum dikerjakan / dapat dijawab siswa
        StudentAssessmentAnswer::where('student_id', $attempt->student_id)
            ->where('school_assessment_id', $attempt->school_assessment_id)
            ->whereNull('answer_value')
            ->delete();

        // Opsi: Jika admin mencentang opsi kembalikan semua jawaban ke draft
        if ($request->boolean('reset_to_draft')) {
            StudentAssessmentAnswer::where('student_id', $attempt->student_id)
                ->where('school_assessment_id', $attempt->school_assessment_id)
                ->update(['status_answer' => 'draft']);
        }

        // Opsi: Jika admin memilih hapus seluruh jawaban untuk mulai bersih
        if ($request->boolean('delete_all_answers')) {
            StudentAssessmentAnswer::where('student_id', $attempt->student_id)
                ->where('school_assessment_id', $attempt->school_assessment_id)
                ->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => "Ujian berhasil dibuka kunci (status: in_progress, pelanggaran: 0, sisa waktu: {$extraMinutes} menit). Soal yang belum terjawab telah dibuka kembali untuk siswa.",
            'data' => $attempt
        ]);
    }

    /**
     * Ubah Status Attempt (in_progress, submitted, cheating, timeout)
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin();

        $request->validate([
            'status' => 'required|in:in_progress,submitted,cheating,timeout'
        ]);

        $attempt = StudentAssessmentAttempt::with('SchoolAssessment')->findOrFail($id);
        $attempt->update([
            'status' => $request->status
        ]);

        if ($request->status === 'in_progress') {
            // Bersihkan jawaban kosong agar siswa dapat melanjutkan soal yang belum diisi
            StudentAssessmentAnswer::where('student_id', $attempt->student_id)
                ->where('school_assessment_id', $attempt->school_assessment_id)
                ->whereNull('answer_value')
                ->delete();

            // Jika expire_time sudah lewat, beri perpanjangan durasi dari sekarang
            if (!$attempt->expire_time || $attempt->expire_time->isPast()) {
                $duration = $attempt->SchoolAssessment->duration ?? 60;
                $attempt->update([
                    'expire_time' => Carbon::now()->addMinutes($duration)
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "Status attempt berhasil diperbarui menjadi '{$request->status}'.",
            'data' => $attempt
        ]);
    }

    /**
     * Ubah Jumlah Pelanggaran / Tab Switch Count
     */
    public function updateTabSwitchCount(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin();

        $request->validate([
            'tab_switch_count' => 'required|integer|min:0'
        ]);

        $attempt = StudentAssessmentAttempt::findOrFail($id);
        $attempt->update([
            'tab_switch_count' => (int) $request->tab_switch_count
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Jumlah pelanggaran berhasil diperbarui menjadi {$request->tab_switch_count}.",
            'data' => $attempt
        ]);
    }

    /**
     * Update Seluruh Atribut Attempt Sekaligus
     */
    public function updateAttempt(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin();

        $request->validate([
            'status' => 'required|in:in_progress,submitted,cheating,timeout',
            'tab_switch_count' => 'required|integer|min:0',
            'add_minutes' => 'nullable|integer|min:0'
        ]);

        $attempt = StudentAssessmentAttempt::with('SchoolAssessment')->findOrFail($id);

        $updateData = [
            'status' => $request->status,
            'tab_switch_count' => (int) $request->tab_switch_count,
        ];

        // Jika menambah durasi menit
        if ($request->filled('add_minutes') && (int) $request->add_minutes > 0) {
            $baseTime = ($attempt->expire_time && $attempt->expire_time->isFuture()) 
                ? $attempt->expire_time 
                : Carbon::now();
            $updateData['expire_time'] = $baseTime->copy()->addMinutes((int) $request->add_minutes);
        } elseif ($request->status === 'in_progress' && (!$attempt->expire_time || $attempt->expire_time->isPast())) {
            // Pastikan jika status in_progress, batas waktu tidak dalam kondisi kadaluarsa
            $duration = $attempt->SchoolAssessment->duration ?? 60;
            $updateData['expire_time'] = Carbon::now()->addMinutes($duration);
        }

        $attempt->update($updateData);

        if ($request->status === 'in_progress') {
            // Bersihkan jawaban kosong auto-submit
            StudentAssessmentAnswer::where('student_id', $attempt->student_id)
                ->where('school_assessment_id', $attempt->school_assessment_id)
                ->whereNull('answer_value')
                ->delete();

            if ($request->boolean('reset_to_draft')) {
                StudentAssessmentAnswer::where('student_id', $attempt->student_id)
                    ->where('school_assessment_id', $attempt->school_assessment_id)
                    ->update(['status_answer' => 'draft']);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "Data attempt berhasil diperbarui.",
            'data' => $attempt
        ]);
    }

    /**
     * Reset Jawaban Siswa untuk Assessment Ini
     */
    public function resetAnswers(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin();

        $attempt = StudentAssessmentAttempt::findOrFail($id);
        $type = $request->input('reset_type', 'empty'); // 'empty', 'all_draft', 'delete_all'

        if ($type === 'delete_all') {
            StudentAssessmentAnswer::where('student_id', $attempt->student_id)
                ->where('school_assessment_id', $attempt->school_assessment_id)
                ->delete();
            $msg = "Seluruh butir jawaban siswa berhasil dihapus bersih.";
        } elseif ($type === 'all_draft') {
            StudentAssessmentAnswer::where('student_id', $attempt->student_id)
                ->where('school_assessment_id', $attempt->school_assessment_id)
                ->update(['status_answer' => 'draft']);
            $msg = "Seluruh jawaban siswa telah diubah statusnya menjadi 'draft'. Siswa dapat mengubah jawaban kembali.";
        } else {
            StudentAssessmentAnswer::where('student_id', $attempt->student_id)
                ->where('school_assessment_id', $attempt->school_assessment_id)
                ->whereNull('answer_value')
                ->delete();
            $msg = "Jawaban kosong / auto-submit berhasil dibersihkan. Siswa dapat melanjutkan soal yang belum dijawab.";
        }

        return response()->json([
            'status' => 'success',
            'message' => $msg
        ]);
    }

    /**
     * Hapus Attempt (Reset Ujian dari Awal termasuk Jawaban)
     */
    public function deleteAttempt(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin();

        $attempt = StudentAssessmentAttempt::findOrFail($id);
        $studentId = $attempt->student_id;
        $assessmentId = $attempt->school_assessment_id;

        // Hapus juga jawaban siswa jika ada
        StudentAssessmentAnswer::where('student_id', $studentId)
            ->where('school_assessment_id', $assessmentId)
            ->delete();

        $attempt->delete();

        return response()->json([
            'status' => 'success',
            'message' => "Attempt dan data jawaban berhasil dihapus. Siswa dapat memulai sesi ujian baru dari awal."
        ]);
    }
}

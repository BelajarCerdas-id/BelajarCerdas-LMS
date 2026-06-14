<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\PollOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherInformationController extends Controller
{
    // 👇 PERBAIKAN: Tambahkan Request $request di parameter pertama
    public function teacherPollingView(Request $request, $role, $schoolName, $schoolId)
    {
        $user = Auth::user();
        $userId = $user->id;

        // 1. TANGKAP FILTER TAHUN AJARAN DARI URL
        $filterTahun = $request->query('tahun_ajaran');

        // 2. QUERY KELAS YANG DIAJAR GURU BERDASARKAN JADWAL (lesson_schedule_items)
        $kelasQuery = DB::table('lesson_schedule_items')
            ->join('lesson_schedules', 'lesson_schedule_items.lesson_schedule_id', '=', 'lesson_schedules.id')
            ->join('school_classes', 'lesson_schedules.class_id', '=', 'school_classes.id')
            ->where('lesson_schedule_items.teacher_id', $userId)
            ->where('school_classes.school_partner_id', $schoolId)
            ->where('school_classes.status_class', 'active');

        // Ambil daftar Tahun Ajaran unik dari kelas yang ada di jadwal guru ini
        $tahunAjaranList = (clone $kelasQuery)
            ->whereNotNull('school_classes.tahun_ajaran')
            ->select('school_classes.tahun_ajaran')
            ->distinct()
            ->orderBy('school_classes.tahun_ajaran', 'desc')
            ->pluck('tahun_ajaran');

        // Jika tidak ada filter yang dipilih, gunakan tahun ajaran terbaru
        if (empty($filterTahun) && $tahunAjaranList->count() > 0) {
            $filterTahun = $tahunAjaranList->first();
        }

        // Terapkan filter tahun ajaran ke query kelas
        if ($filterTahun) {
            $kelasQuery->where('school_classes.tahun_ajaran', $filterTahun);
        }

        // Eksekusi pencarian kelas (Pastikan distinct agar nama kelas tidak ganda)
        $classes = $kelasQuery->select('school_classes.id as class_id', 'school_classes.class_name')
            ->distinct()
            ->orderBy('school_classes.class_name', 'asc')
            ->get();

        $classIds = $classes->pluck('class_id')->toArray();

        // 3. Ambil Polling Buatan Guru Sendiri (Berdasarkan Tahun Ajaran/Kelas yang diajar)
        $polls = Poll::with('PollOptions')
            ->where('school_partner_id', $schoolId)
            ->where('author_id', $userId)
            ->when($filterTahun, function ($q) use ($classIds) {
                // Tampilkan polling yang mengarah ke kelas di tahun ajaran ini, ATAU polling global (semua kelas)
                $q->where(function ($subQuery) use ($classIds) {
                    $subQuery->whereIn('class_id', $classIds)
                        ->orWhereNull('class_id');
                });
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($poll) {
                // Menambahkan Nama Kelas Secara Dinamis
                if ($poll->class_id) {
                    $kelas = DB::table('school_classes')->where('id', $poll->class_id)->first();
                    $poll->nama_kelas = $kelas ? $kelas->class_name : 'Kelas Dihapus';
                } else {
                    $poll->nama_kelas = 'Semua Kelas (Yang Saya Ajar)';
                }

                return $poll;
            });

        // 4. Ambil Polling Buatan Kepsek & Wakasek (Untuk Tab "Dari Sekolah")
        $pollingDariSekolah = Poll::with('PollOptions')
            ->where('school_partner_id', $schoolId)
            ->whereIn('author_role', ['Kepala Sekolah', 'Wakil Kepala Sekolah'])
            ->whereIn('target', ['Semua Guru', 'Semua Warga Sekolah', 'Semua'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($poll) use ($userId) {
                // Ambil record vote-nya langsung
                $voteRecord = DB::table('poll_votes')
                    ->where('poll_id', $poll->id)
                    ->where('user_id', $userId)
                    ->first();

                if ($voteRecord) {
                    $poll->has_voted = true;
                    $poll->voted_option_id = $voteRecord->poll_option_id;
                } else {
                    $poll->has_voted = false;
                    $poll->voted_option_id = null;
                }

                return $poll;
            });

        return view('features.lms.teacher.information.polling', compact(
            'role', 'schoolName', 'schoolId', 'polls', 'pollingDariSekolah',
            'classes', 'tahunAjaranList', 'filterTahun'
        ));
    }

    public function submitVote(Request $request, $role, $schoolName, $schoolId)
    {
        $userId = Auth::id();

        $request->validate([
            'poll_id' => 'required|exists:polls,id',
            'option_id' => 'required|exists:poll_options,id',
        ]);

        // Cek apakah sudah pernah vote
        $alreadyVoted = DB::table('poll_votes')
            ->where('poll_id', $request->poll_id)
            ->where('user_id', $userId)
            ->exists();

        if ($alreadyVoted) {
            return response()->json(['success' => false, 'message' => 'Anda sudah memberikan suara.']);
        }

        DB::beginTransaction();
        try {
            // 1. Simpan ke riwayat suara
            DB::table('poll_votes')->insert([
                'poll_id' => $request->poll_id,
                'poll_option_id' => $request->option_id,
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Update counter di tabel options
            DB::table('poll_options')->where('id', $request->option_id)->increment('votes_count');

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Terima kasih, suara Anda telah direkam!']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => 'Gagal menyimpan suara.']);
        }
    }

    public function savePollingData(Request $request, $role, $schoolName, $schoolId)
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'Guru') {
            abort(403);
        }

        // 1. Validasi data yang masuk dari AJAX
        $request->validate([
            'question' => 'required|string',
            'options' => 'required|array|min:2',
        ]);

        try {
            DB::beginTransaction();

            $kelasId = $request->class_id;
            if (empty($kelasId) || $kelasId === '0' || $kelasId === 'null') {
                $kelasId = null;
            }

            $targetAudiens = $request->target ?? $request->target_role ?? 'Semua Warga Sekolah';

            // 2. Simpan pertanyaan utama beserta Target & Author
            $pollId = DB::table('polls')->insertGetId([
                'school_partner_id' => $schoolId,
                'class_id' => $kelasId,
                'question' => $request->question,
                'target' => $targetAudiens,
                'author_id' => $user->id,
                'author_role' => 'Guru',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. Simpan pilihan jawabannya
            $optionsData = [];
            foreach ($request->options as $opt) {
                $optionsData[] = [
                    'poll_id' => $pollId,
                    'option_text' => $opt,
                    'votes_count' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('poll_options')->insert($optionsData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Polling berhasil dibuat dan dikirim ke '.$targetAudiens,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    public function deletePoll($role, $schoolName, $schoolId, $id)
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'Guru') {
            abort(403);
        }

        try {
            $pollExists = DB::table('polls')
                ->where('id', $id)
                ->where('author_id', $user->id) // PASTIKAN GURU HANYA BISA MENGHAPUS POLLING MILIKNYA
                ->exists();

            if (! $pollExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Polling tidak ditemukan atau Anda tidak memiliki akses menghapusnya.',
                ], 404);
            }

            // 1. Hapus data yang berelasi
            DB::table('poll_votes')->where('poll_id', $id)->delete();
            DB::table('poll_options')->where('poll_id', $id)->delete();

            // 2. Hapus polling utama
            DB::table('polls')->where('id', $id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Polling berhasil dihapus secara permanen!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mendapatkan detail polling beserta breakdown responden untuk grafik
     */
    public function getPollingBreakdown($role, $schoolName, $schoolId, $id)
    {
        try {
            $options = PollOption::where('poll_id', $id)->get();
            $votes = DB::table('poll_votes')
                ->join('user_accounts', 'poll_votes.user_id', '=', 'user_accounts.id')
                ->where('poll_votes.poll_id', $id)
                ->select('poll_votes.poll_option_id', 'user_accounts.role')
                ->get();

            $labels = [];
            $dataSiswa = [];
            $dataOrtu = [];
            $dataGuru = [];

            foreach ($options as $opt) {
                $labels[] = $opt->option_text;

                // Hitung berdasarkan Role
                $dataSiswa[] = $votes->where('poll_option_id', $opt->id)->where('role', 'Siswa')->count();
                $dataOrtu[] = $votes->where('poll_option_id', $opt->id)->where('role', 'Orang Tua')->count();
                // Guru/Manajemen
                $dataGuru[] = $votes->where('poll_option_id', $opt->id)->whereIn('role', ['Guru', 'Kepala Sekolah', 'Wakil Kepala Sekolah', 'Admin'])->count();
            }

            return response()->json([
                'success' => true,
                'labels' => $labels,
                'datasets' => [
                    'Siswa' => $dataSiswa,
                    'Orang Tua' => $dataOrtu,
                    'Guru/Manajemen' => $dataGuru,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}

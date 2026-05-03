<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Poll;
use App\Models\PollOption;

class TeacherInformationController extends Controller
{
    public function teacherPollingView($role, $schoolName, $schoolId)
    {
        $user = Auth::user();
        $userId = $user->id;

        // 1. AMBIL SEMUA DAFTAR KELAS DI SEKOLAH INI
        $classes = DB::table('school_classes')
            ->where('school_partner_id', $schoolId)
            ->select('id as class_id', 'class_name')
            ->orderBy('class_name', 'asc')
            ->get();

        // 2. Ambil Polling Buatan Guru Sendiri
        $polls = \App\Models\Poll::with('PollOptions')
            ->where('school_partner_id', $schoolId)
            ->where('author_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($poll) {
                // 👇 Menambahkan Nama Kelas Secara Dinamis (karena kolom class_name sudah dihapus)
                if ($poll->class_id) {
                    $kelas = DB::table('school_classes')->where('id', $poll->class_id)->first();
                    $poll->nama_kelas = $kelas ? $kelas->class_name : 'Kelas Dihapus';
                } else {
                    $poll->nama_kelas = 'Semua Kelas (Global)';
                }
                return $poll;
            });

        // 3. Ambil Polling Buatan Kepsek & Wakasek (Untuk Tab "Dari Sekolah")
        $pollingDariSekolah = \App\Models\Poll::with('PollOptions')
            ->where('school_partner_id', $schoolId)
            ->whereIn('author_role', ['Kepala Sekolah', 'Wakil Kepala Sekolah'])
            // 👇 PERBAIKAN: Mengubah target_role menjadi target
            ->whereIn('target', ['Semua Guru', 'Semua Warga Sekolah', 'Semua'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($poll) use ($userId) {
                // Ambil record vote-nya langsung (Aman karena poll_votes sekarang pakai user_id)
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

        return view('features.lms.teacher.information.polling', compact('role', 'schoolName', 'schoolId', 'polls', 'pollingDariSekolah', 'classes'));
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
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user || $user->role !== 'Guru') abort(403);

        // 1. Validasi data yang masuk dari AJAX
        $request->validate([
            // Target dan Target Role divalidasi dua-duanya untuk berjaga-jaga dari HTML lama
            'question'    => 'required|string',
            'options'     => 'required|array|min:2',
        ]);

        try {
            DB::beginTransaction();

            // 👇 PEMBERSIHAN CLASS_ID: Mencegah Error Integrity constraint violation 19
            $kelasId = $request->class_id;
            if (empty($kelasId) || $kelasId === '0' || $kelasId === 'null') {
                $kelasId = null;
            }

            // 👇 TANGKAP NILAI TARGET DARI HTML (Mengakomodir 'target' atau 'target_role')
            $targetAudiens = $request->target ?? $request->target_role ?? 'Semua Warga Sekolah';

            // 2. Simpan pertanyaan utama beserta Target & Author
            $pollId = DB::table('polls')->insertGetId([
                'school_partner_id' => $schoolId,
                'class_id'          => $kelasId,
                // class_name DIHAPUS agar sesuai tabel baru
                'question'          => $request->question,
                'target'            => $targetAudiens, 
                'author_id'         => $user->id,            
                'author_role'       => 'Guru',               
                'status'            => 'active',
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            // 3. Simpan pilihan jawabannya
            $optionsData = [];
            foreach ($request->options as $opt) {
                $optionsData[] = [
                    'poll_id'     => $pollId,
                    'option_text' => $opt,
                    'votes_count' => 0,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
            DB::table('poll_options')->insert($optionsData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Polling berhasil dibuat dan dikirim ke ' . $targetAudiens
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deletePoll($role, $schoolName, $schoolId, $id) 
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user || $user->role !== 'Guru') abort(403);

        try {
            $pollExists = \Illuminate\Support\Facades\DB::table('polls')->where('id', $id)->exists();
            if (!$pollExists) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Polling tidak ditemukan di database.'
                ], 404);
            }

            // 1. Hapus data yang berelasi terlebih dahulu (Aman untuk backward compatibility)
            \Illuminate\Support\Facades\DB::table('poll_votes')->where('poll_id', $id)->delete();
            \Illuminate\Support\Facades\DB::table('poll_options')->where('poll_id', $id)->delete();
            
            // 2. Hapus polling utama
            \Illuminate\Support\Facades\DB::table('polls')->where('id', $id)->delete();

            return response()->json([
                'success' => true, 
                'message' => 'Polling berhasil dihapus secara permanen!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }
}
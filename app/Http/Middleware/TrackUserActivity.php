<?php

namespace App\Http\Middleware;

use App\Models\UserActivity;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    /**
     * Handle an incoming request and track module usage.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Hanya track request yang berhasil (status < 400) dan user sudah login
        if (Auth::check() && $response->getStatusCode() < 400) {
            $this->logActivity($request);
        }

        return $response;
    }

    /**
     * Catat aktivitas pengguna secara aman tanpa mengganggu alur aplikasi
     */
    protected function logActivity(Request $request): void
    {
        try {
            $path = $request->path();

            // Abaikan rute internal, assets, live polling, dan analytics itu sendiri
            if ($this->shouldIgnorePath($path, $request)) {
                return;
            }

            $user = Auth::user();
            $role = $user->role ?? 'Unknown';

            // Resolusi modul dan sub-modul
            [$module, $subModule] = $this->resolveModuleAndSubModule($request);

            // Tentukan tipe aksi
            $action = $this->resolveAction($request);

            // Debounce / throttle: cegah duplikasi aktivitas identik dalam 45 detik untuk user yang sama
            $debounceKey = "user_act_{$user->id}_{$module}_{$subModule}_{$action}";
            if (Cache::has($debounceKey)) {
                return;
            }
            Cache::put($debounceKey, true, now()->addSeconds(45));

            // Dapatkan school_partner_id jika ada
            $schoolPartnerId = $user->school_partner_id ?? null;

            UserActivity::create([
                'user_id'           => $user->id,
                'school_partner_id' => $schoolPartnerId,
                'role'              => $role,
                'module'            => $module,
                'sub_module'        => $subModule,
                'action'            => $action,
                'url'               => '/' . ltrim($path, '/'),
                'route_name'        => $request->route()?->getName(),
                'ip_address'        => $request->ip(),
                'user_agent'        => substr((string) $request->userAgent(), 0, 500),
                'created_at'        => now(),
            ]);

        } catch (\Throwable $e) {
            // Jangan pernah biarkan tracking menyebabkan error pada aplikasi
            Log::warning('Failed to log user activity: ' . $e->getMessage());
        }
    }

    /**
     * Cek apakah rute harus diabaikan dari tracking
     */
    protected function shouldIgnorePath(string $path, Request $request): bool
    {
        // Abaikan endpoint analytics sendiri agar tidak looping
        if (str_contains($path, 'analytics') || str_contains($path, 'debugbar')) {
            return true;
        }

        // Abaikan assets dan file statis
        if (preg_match('/\.(js|css|map|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)$/i', $path)) {
            return true;
        }

        // Abaikan polling background / upload chunk status
        if (str_contains($path, 'video/status') || str_contains($path, 'upload-status')) {
            return true;
        }

        // Abaikan auth/logout
        if ($path === 'logout' || $path === 'login') {
            return true;
        }

        return false;
    }

    /**
     * Resolusi Modul dan Sub-modul dari route dan URL
     */
    protected function resolveModuleAndSubModule(Request $request): array
    {
        $routeName = (string) ($request->route()?->getName() ?? '');
        $path      = strtolower($request->path());

        // 1. LIBRARY
        if (str_contains($routeName, 'library') || str_contains($path, 'library')) {
            if (str_contains($path, 'ppt') || str_contains($routeName, 'ppt')) {
                return ['Library', 'Power Point'];
            }
            if (str_contains($path, 'lks') || str_contains($routeName, 'lks')) {
                return ['Library', 'LKPD (Lembar Kerja)'];
            }
            if (str_contains($path, 'video') || str_contains($routeName, 'video')) {
                return ['Library', 'Video Pembelajaran'];
            }
            if (str_contains($path, 'topik') || str_contains($routeName, 'topik')) {
                return ['Library', 'Topik Management'];
            }
            if (str_contains($path, 'read') || str_contains($routeName, 'read')) {
                return ['Library', 'Baca Materi / Buku'];
            }
            return ['Library', 'Katalog Materi'];
        }

        // 2. ASSESSMENT & UJIAN
        if (str_contains($routeName, 'assessment') || str_contains($path, 'assessment') || str_contains($path, 'exam')) {
            if (str_contains($path, 'exam') || str_contains($routeName, 'exam')) {
                return ['Assessment & Ujian', 'Pelaksanaan Ujian'];
            }
            if (str_contains($path, 'grading') || str_contains($routeName, 'grading')) {
                return ['Assessment & Ujian', 'Penilaian & Koreksi'];
            }
            if (str_contains($path, 'remedial')) {
                return ['Assessment & Ujian', 'Remedial & Susulan'];
            }
            if (str_contains($path, 'weight') || str_contains($path, 'type')) {
                return ['Assessment & Ujian', 'Bobot & Tipe Assessment'];
            }
            return ['Assessment & Ujian', 'Kelola Assessment'];
        }

        // 3. PRESENSI & KEHADIRAN
        if (str_contains($routeName, 'attendance') || str_contains($path, 'attendance') || str_contains($path, 'presensi')) {
            if (str_contains($path, 'meeting')) {
                return ['Presensi & Kehadiran', 'Presensi Pertemuan'];
            }
            return ['Presensi & Kehadiran', 'Rekap Kehadiran'];
        }

        // 4. AGENDA HARIAN GURU
        if (str_contains($routeName, 'agenda') || str_contains($path, 'agenda')) {
            if (str_contains($path, 'history') || str_contains($path, 'riwayat')) {
                return ['Agenda Guru', 'Riwayat Agenda'];
            }
            if (str_contains($path, 'form') || str_contains($path, 'create')) {
                return ['Agenda Guru', 'Pengisian Agenda'];
            }
            return ['Agenda Guru', 'Monitoring Agenda'];
        }

        // 5. BUKU NILAI & LEGER
        if (str_contains($routeName, 'gradebook') || str_contains($path, 'gradebook') || str_contains($path, 'grade-ledger') || str_contains($path, 'transcript')) {
            if (str_contains($path, 'ledger')) {
                return ['Buku Nilai', 'Leger Nilai'];
            }
            if (str_contains($path, 'transcript')) {
                return ['Buku Nilai', 'Transkrip Nilai Siswa'];
            }
            return ['Buku Nilai', 'Buku Nilai Siswa'];
        }

        // 6. BANK SOAL
        if (str_contains($routeName, 'question-bank') || str_contains($path, 'question-bank')) {
            if (str_contains($path, 'release')) {
                return ['Bank Soal', 'Rilis Soal ke Siswa'];
            }
            return ['Bank Soal', 'Kelola Bank Soal'];
        }

        // 7. BANK KONTEN / MATERI
        if (str_contains($routeName, 'content') || str_contains($path, 'content') || str_contains($path, 'materi')) {
            if (str_contains($path, 'release')) {
                return ['Bank Materi', 'Rilis Materi Pembelajaran'];
            }
            return ['Bank Materi', 'Kelola Bank Materi'];
        }

        // 8. SIMULASI TKA
        if (str_contains($routeName, 'tka') || str_contains($path, 'tka')) {
            return ['Simulasi TKA', 'Latihan & Simulasi TKA'];
        }

        // 9. KURIKULUM & SILABUS
        if (str_contains($path, 'syllabus') || str_contains($path, 'kurikulum') || str_contains($path, 'curriculum') || str_contains($path, 'fase') || str_contains($path, 'kelas') || str_contains($path, 'mapel') || str_contains($path, 'bab')) {
            if (str_contains($path, 'fase')) return ['Kurikulum & Silabus', 'Kelola Fase'];
            if (str_contains($path, 'kelas')) return ['Kurikulum & Silabus', 'Kelola Kelas / Rombel'];
            if (str_contains($path, 'mapel')) return ['Kurikulum & Silabus', 'Mata Pelajaran'];
            if (str_contains($path, 'bab') || str_contains($path, 'sub-bab')) return ['Kurikulum & Silabus', 'Bab & Sub-Bab'];
            return ['Kurikulum & Silabus', 'Struktur Kurikulum'];
        }

        // 10. MANAJEMEN AKUN & USER
        if (str_contains($path, 'manage-user') || str_contains($path, 'office-management') || str_contains($path, 'management-role-account') || str_contains($path, 'management-accounts')) {
            if (str_contains($path, 'office')) return ['Manajemen Pengguna', 'Akun Internal Office'];
            return ['Manajemen Pengguna', 'Akun Civitas Sekolah'];
        }

        // 11. MITRA SEKOLAH & LANGGANAN
        if (str_contains($path, 'school-partner') || str_contains($path, 'school-subscription')) {
            return ['Mitra Sekolah', 'Kelola Sekolah & Langganan'];
        }

        // 12. YAYASAN
        if (str_contains($path, 'foundation') || str_contains($path, 'yayasan')) {
            return ['Manajemen Yayasan', 'Portal & Akses Yayasan'];
        }

        // 13. KEUANGAN (FINANCE)
        if (str_contains($path, 'finance') || str_contains($path, 'contract') || str_contains($path, 'revenue')) {
            if (str_contains($path, 'contract')) return ['Keuangan', 'Kontrak Sekolah'];
            if (str_contains($path, 'revenue')) return ['Keuangan', 'Pendapatan'];
            return ['Keuangan', 'Dashboard Finance'];
        }

        // 14. PROFIL AKUN
        if (str_contains($path, 'profile-account') || str_contains($path, 'reset-password')) {
            return ['Pengaturan Akun', 'Profil & Keamanan Akun'];
        }

        // 15. DASHBOARD & BERANDA
        if (str_contains($routeName, 'beranda') || str_contains($path, 'dashboard') || str_contains($path, 'beranda')) {
            return ['Dashboard', 'Beranda Utama'];
        }

        // Fallback berdasarkan prefix
        if (str_contains($path, 'lms')) {
            return ['LMS Pembelajaran', 'Aktivitas LMS'];
        }

        return ['Sistem', 'Halaman Umum'];
    }

    /**
     * Resolusi jenis aksi (Lihat, Simpan, Ubah, Hapus)
     */
    protected function resolveAction(Request $request): string
    {
        $method = strtoupper($request->method());

        return match ($method) {
            'GET'    => 'Lihat / Akses',
            'POST'   => 'Simpan / Tambah',
            'PUT', 'PATCH' => 'Perbarui / Ubah',
            'DELETE' => 'Hapus',
            default  => $method,
        };
    }
}

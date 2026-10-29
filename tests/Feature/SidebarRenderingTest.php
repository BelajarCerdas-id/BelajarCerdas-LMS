<?php

namespace Tests\Feature;

use App\Models\UserAccount;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class SidebarRenderingTest extends TestCase
{
    /**
     * Test access denied rendering when user is unauthenticated or role is unknown.
     */
    public function test_sidebar_renders_access_denied_for_unknown_role(): void
    {
        $rendered = View::make('components.sidebar-beranda', [
            'headerSideNav' => 'Test Dashboard',
        ])->render();

        $this->assertStringContainsString('Akses Ditolak', $rendered);
        $this->assertStringContainsString('Kembali & Keluar', $rendered);
    }

    /**
     * Test all role partials exist in the components/sidebar/roles directory.
     */
    public function test_all_sidebar_role_partials_exist(): void
    {
        $expectedRoles = [
            'siswa',
            'administrator',
            'finance',
            'guru',
            'admin-sekolah',
            'kepala-sekolah',
            'wakil-kesiswaan',
            'orang-tua',
            'yayasan',
        ];

        foreach ($expectedRoles as $role) {
            $viewName = "components.sidebar.roles.{$role}";
            $this->assertTrue(
                View::exists($viewName),
                "View partial [{$viewName}] must exist."
            );
        }

        $this->assertTrue(
            View::exists('components.sidebar.access-denied'),
            "View partial [components.sidebar.access-denied] must exist."
        );
    }

    /**
     * Test that each role can be rendered properly without exceptions.
     */
    public function test_all_roles_render_their_respective_sidebars(): void
    {
        $roleExpectations = [
            'Siswa' => 'sidebar-beranda-student',
            'Administrator' => 'sidebar-beranda-administrator',
            'admin' => 'sidebar-beranda-administrator',
            'Finance' => 'sidebar-beranda-administrator',
            'Guru' => 'sidebar-beranda-student',
            'Admin Sekolah' => 'sidebar-beranda-school-admin',
            'Kepala Sekolah' => 'sidebar-beranda-student',
            'Wakil Kepala Sekolah' => 'sidebar-beranda-student',
            'Wakil Kesiswaan' => 'sidebar-beranda-student',
            'Orang Tua' => 'sidebar-beranda-student',
            'Yayasan' => 'sidebar-beranda-student',
        ];

        foreach ($roleExpectations as $role => $expectedClass) {
            $user = new UserAccount();
            $user->role = $role;
            $this->actingAs($user);

            $rendered = View::make('components.sidebar-beranda', [
                'headerSideNav' => "Dashboard {$role}",
                'backButton' => '<i class="fa fa-arrow-left"></i>',
                'linkBackButton' => '/home',
            ])->render();

            $this->assertStringContainsString(
                $expectedClass,
                $rendered,
                "Expected class [{$expectedClass}] when role is [{$role}]."
            );
            $this->assertStringContainsString("Dashboard {$role}", $rendered);
            $this->assertStringNotContainsString('Akses Ditolak', $rendered);
        }
    }

    /**
     * Test that the new Teacher Dashboard renders successfully with DPAI elements.
     */
    public function test_teacher_dashboard_renders_with_dpai_elements(): void
    {
        $user = new UserAccount();
        $user->role = 'Guru';
        $user->name = 'Bu Ratna Puspita';
        $this->actingAs($user);

        $view = View::make('features.lms.teacher.dashboard', [
            'role' => 'Guru',
            'schoolName' => 'Sekolah Masa',
            'schoolId' => 1,
            'hariIni' => 'Rabu',
            'tanggalDipilih' => date('Y-m-d'),
            'totalKelas' => 6,
            'totalJadwalHariIni' => 4,
            'jadwalMengajar' => [
                (object)[
                    'mapel' => 'Matematika — SPLDV',
                    'kelas' => 'Kelas X-2',
                    'ruangan' => 'Ruang R-104',
                    'subject_teacher_id' => 1,
                    'details' => [
                        (object)[
                            'jam_mulai' => '07.45',
                            'jam_selesai' => '09.15'
                        ]
                    ]
                ]
            ],
            'daftarKelas' => collect(),
            'recentPolls' => collect(),
            'pollingDariSekolah' => collect(),
            'pengumumanDariSekolah' => collect(),
            'pengumumanKeSiswa' => collect(),
        ]);

        $rendered = $view->render();

        $this->assertStringContainsString('Presensi cepat', $rendered);
        $this->assertStringContainsString('Jadwal mengajar hari ini', $rendered);
        $this->assertStringContainsString('Total kelas diampu', $rendered);
        $this->assertStringContainsString('Matematika — SPLDV', $rendered);
        $this->assertStringContainsString('var(--color-accent)', $rendered);
    }

    /**
     * Test that Administrator sidebar renders with centralized DPAI tokens and proper copywriting.
     */
    public function test_administrator_sidebar_renders_with_dpai_tokens_and_correct_copywriting(): void
    {
        $user = new UserAccount();
        $user->role = 'Administrator';
        $user->name = 'Super Admin';
        $this->actingAs($user);

        $rendered = View::make('components.sidebar.roles.administrator')->render();

        $this->assertStringContainsString('sidebar-beranda-administrator', $rendered);
        $this->assertStringContainsString('var(--color-surface)', $rendered);
        $this->assertStringContainsString('var(--color-border)', $rendered);
        $this->assertStringContainsString('var(--color-text-primary)', $rendered);
        $this->assertStringContainsString('var(--radius-nav-item)', $rendered);
        $this->assertStringContainsString('var(--radius-tile)', $rendered);
        $this->assertStringContainsString('Manajemen Kurikulum', $rendered);
        $this->assertStringNotContainsString('Management Curriculum', $rendered);
        $this->assertStringContainsString('Site Analytics', $rendered);
        $this->assertStringContainsString('Admin Console', $rendered);
    }

    /**
     * Test that Teacher sidebar renders dedicated Jadwal & Kalender link.
     */
    public function test_teacher_sidebar_renders_schedule_calendar_link(): void
    {
        $user = new UserAccount();
        $user->role = 'Guru';
        $user->name = 'Bu Ratna Puspita';
        $this->actingAs($user);

        $rendered = View::make('components.sidebar.roles.guru', [
            'schoolName' => 'SMA Belajar Cerdas',
            'schoolId' => 1,
        ])->render();

        $this->assertStringContainsString('Jadwal & Kalender', $rendered);
        $this->assertStringContainsString('teacher-schedule-calendar', $rendered);
        $this->assertStringContainsString('fa-calendar-days', $rendered);
    }

    /**
     * Test that Teacher Schedule & Calendar view renders with full DPAI components.
     */
    public function test_teacher_schedule_calendar_renders_with_dpai_elements(): void
    {
        $user = new UserAccount();
        $user->role = 'Guru';
        $user->name = 'Bu Ratna Puspita';
        $this->actingAs($user);

        $view = View::make('features.lms.teacher.information.schedule_calendar', [
            'role' => 'Guru',
            'schoolName' => 'SMA Belajar Cerdas',
            'schoolId' => 1,
            'totalSesi' => 14,
            'totalJP' => 22,
            'totalKelas' => 4,
            'jadwalPerHari' => [
                'Senin' => collect([
                    (object)[
                        'mapel' => 'Matematika — SPLDV',
                        'kelas' => 'X-1',
                        'ruangan' => 'R-101',
                        'jam_mulai' => '07.00',
                        'jam_selesai' => '08.30',
                        'topik' => 'Penyelesaian SPLDV',
                    ]
                ]),
            ],
            'kalenderData' => collect([
                (object)[
                    'tgl' => '17',
                    'bulan' => 'Agu',
                    'hari' => 'Sabtu',
                    'judul' => 'Upacara HUT Kemerdekaan RI',
                    'kategori' => 'Hari Libur',
                    'color' => '#1E9E5A',
                    'bg' => '#E4F5EB',
                    'jam' => '07.00 - 09.30 WIB',
                ]
            ]),
        ]);

        $rendered = $view->render();

        $this->assertStringContainsString('Jadwal & Kalender Akademik', $rendered);
        $this->assertStringContainsString('Jadwal Mengajar', $rendered);
        $this->assertStringContainsString('Kalender Akademik', $rendered);
        $this->assertStringContainsString('Total Sesi Mengajar', $rendered);
        $this->assertStringContainsString('Jam Pelajaran', $rendered);
        $this->assertStringContainsString('JP per pekan', $rendered);
        $this->assertStringContainsString('Kelas Diampu', $rendered);
        $this->assertStringContainsString('Agenda Bulan Ini', $rendered);
        $this->assertStringContainsString('var(--color-accent)', $rendered);
        $this->assertStringContainsString('var(--radius-card)', $rendered);
        $this->assertStringContainsString('var(--radius-btn)', $rendered);
    }
}


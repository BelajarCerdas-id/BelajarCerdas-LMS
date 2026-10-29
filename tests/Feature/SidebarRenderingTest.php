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
}

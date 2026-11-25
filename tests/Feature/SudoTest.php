<?php

namespace Tests\Feature;

use App\Http\Controllers\SudoController;
use App\Models\UserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class SudoTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $role = 'administrator'): UserAccount
    {
        return UserAccount::create(['email' => "sudo-$role@test.id", 'password' => 'x', 'no_hp' => '0811111101', 'role' => $role]);
    }

    private function start(UserAccount $admin, UserAccount $target)
    {
        $this->actingAs($admin);

        return app(SudoController::class)->start(
            Request::create('/x', 'GET', []), 'Administrator', $target->id
        );
    }

    public function test_admin_can_sudo_into_any_role(): void
    {
        $admin = $this->admin();
        $student = UserAccount::create(['email' => 'student@test.id', 'password' => 'x', 'no_hp' => '0811111102', 'role' => 'Siswa']);

        $res = $this->start($admin, $student);

        $this->assertStringEndsWith('/lms/Siswa', $res->getTargetUrl());
        $this->assertAuthenticatedAs($student);
        $this->assertEquals($admin->id, session(SudoController::SESSION_KEY));
    }

    public function test_sudo_can_be_left_and_returns_the_admin(): void
    {
        $admin = $this->admin();
        $teacher = UserAccount::create(['email' => 'teacher@test.id', 'password' => 'x', 'no_hp' => '0811111103', 'role' => 'guru']);
        $this->start($admin, $teacher);

        $res = app(SudoController::class)->leave();

        $this->assertAuthenticatedAs($admin);
        $this->assertNull(session(SudoController::SESSION_KEY));
        $this->assertStringEndsWith('/lms/administrator', $res->getTargetUrl());
    }

    public function test_teacher_cannot_sudo(): void
    {
        $admin = $this->admin('guru');
        $student = UserAccount::create(['email' => 'student2@test.id', 'password' => 'x', 'no_hp' => '0811111104', 'role' => 'Siswa']);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->start($admin, $student);
    }

    public function test_admin_cannot_sudo_into_themselves(): void
    {
        $admin = $this->admin();

        $res = $this->start($admin, $admin);

        $this->assertAuthenticatedAs($admin);
        $this->assertNull(session(SudoController::SESSION_KEY), 'no sudo state is opened for the account you already use');
    }

    public function test_sudo_into_another_platform_admin_is_allowed_and_pinned(): void
    {
        $first = $this->admin();
        $second = UserAccount::create(['email' => 'sudo-superadmin@test.id', 'password' => 'x', 'no_hp' => '0811111105', 'role' => 'superadmin']);

        // current behavior, deliberately kept: an admin may sudo another admin.
        // This test pins it so any future change is a conscious one.
        $res = $this->start($first, $second);

        $this->assertStringEndsWith('/lms/superadmin', $res->getTargetUrl());
        $this->assertAuthenticatedAs($second);
        $this->assertEquals($first->id, session(SudoController::SESSION_KEY));
    }

    public function test_sudo_cannot_stack_a_second_level(): void
    {
        $first = $this->admin();
        $second = UserAccount::create(['email' => 'sudo-superadmin2@test.id', 'password' => 'x', 'no_hp' => '0811111106', 'role' => 'superadmin']);
        $student = UserAccount::create(['email' => 'student3@test.id', 'password' => 'x', 'no_hp' => '0811111107', 'role' => 'Siswa']);
        $this->start($first, $second);

        // the inner superadmin would pass the role gate; only the already-open
        // sudo session stops them — this is what makes sudo one level deep
        $res = $this->start($second, $student);

        // still the first target, never a deeper one
        $this->assertAuthenticatedAs($second);
        $this->assertEquals($first->id, session(SudoController::SESSION_KEY), 'the original admin is still the only return address');
    }

    public function test_sudoed_student_sees_the_real_student_dashboard(): void
    {
        $admin = $this->admin();
        $partner = \App\Models\SchoolPartner::create(['nama_sekolah' => 'SMA Sudo', 'npsn' => '99999999', 'kepsek_id' => $admin->id, 'jenjang_sekolah' => 'SMA']);
        $student = UserAccount::create(['email' => 'siswa-sudo@test.id', 'password' => 'x', 'no_hp' => '0811111108', 'role' => 'Siswa']);
        \App\Models\StudentProfile::create([
            'user_id' => $student->id,
            'nama_lengkap' => 'Andi Sudoin',
            'personal_email' => 'andi@test.id',
            'enrollment_type' => 'B2B',
            'school_partner_id' => $partner->id,
        ]);

        $this->actingAs($admin);
        $this->withoutMiddleware(\App\Http\Middleware\CheckSchoolContract::class);

        // sudo start redirects to /lms/Siswa; beranda then redirects to the
        // student's real dashboard — the whole chain is the production flow
        $res = $this->followingRedirects()->get('/lms/Administrator/sudo/'.$student->id);

        $res->assertOk();
        $res->assertSee('Andi Sudoin', false);
        $this->assertAuthenticatedAs($student);
        $this->assertEquals($admin->id, session(SudoController::SESSION_KEY));
    }

    public function test_sudoed_teacher_sees_the_real_teacher_dashboard(): void
    {
        $admin = $this->admin();
        $partner = \App\Models\SchoolPartner::create(['nama_sekolah' => 'SMA Sudo', 'npsn' => '99999998', 'kepsek_id' => $admin->id, 'jenjang_sekolah' => 'SMA']);
        $teacher = UserAccount::create(['email' => 'guru-sudo@test.id', 'password' => 'x', 'no_hp' => '0811111109', 'role' => 'Guru']);
        \App\Models\SchoolStaffProfile::create([
            'user_id' => $teacher->id,
            'school_partner_id' => $partner->id,
            'enrollment_type' => 'B2B',
            'nama_lengkap' => 'Bu Sudoin',
            'nik' => '1234567890123456',
            'personal_email' => 'bu@test.id',
        ]);

        $this->actingAs($admin);
        $this->withoutMiddleware(\App\Http\Middleware\CheckSchoolContract::class);

        $res = $this->followingRedirects()->get('/lms/Administrator/sudo/'.$teacher->id);

        $res->assertOk();
        $this->assertAuthenticatedAs($teacher);
        $this->assertEquals($admin->id, session(SudoController::SESSION_KEY));
    }
}

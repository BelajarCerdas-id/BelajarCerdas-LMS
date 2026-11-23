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
}

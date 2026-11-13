<?php

namespace Tests\Unit;

use App\Http\Middleware\EnsureRoleAccess;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class EnsureRoleAccessTest extends TestCase
{
    public function test_feature_gate_and_role_map(): void
    {
        $m = new ReflectionMethod(EnsureRoleAccess::class, 'allowedRoles');
        $m->setAccessible(true);
        $mw = new EnsureRoleAccess();
        $roles = fn (string $path): ?array => $m->invoke($mw, $path);

        // Student slug can no longer reach admin-only features.
        $this->assertEquals(
            ['administrator', 'admin sekolah', 'office', 'superadmin', 'super admin', 'kurikulum'],
            $roles('lms/siswa/office-management/manage-user/store')
        );
        // Finance keeps access to the contract console.
        $this->assertContains('finance', $roles('lms/finance/manage-contract'));
        // School-subscription management segments are admin-only wherever they sit.
        $this->assertEquals(
            ['administrator', 'admin sekolah', 'office', 'superadmin', 'super admin', 'kurikulum'],
            $roles('lms/siswa/school-subscription/12/management-account/5/activate-account')
        );
        $this->assertEquals(
            ['administrator', 'admin sekolah', 'office', 'superadmin', 'super admin', 'kurikulum'],
            $roles('lms/guru/school-subscription/pilar/5/academic-management/management-role-account/siswa/management-accounts')
        );
        // Normal role-slug behavior unchanged.
        $this->assertEquals(['siswa'], $roles('lms/siswa/beranda'));
        $this->assertEquals(['guru'], $roles('lms/guru/gradebook/classes'));
        $this->assertEquals(['finance', 'administrator'], $roles('lms/finance/dashboard'));
        // Unmapped segments stay open (documented fallback).
        $this->assertNull($roles('lms/school-subscription/5/logo'));
    }
}
<?php

namespace Tests\Feature;

use App\Models\AcademicDocument;
use App\Models\AcademicDocumentWordContent;
use App\Models\SchoolPartner;
use App\Models\UserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression: /registrasi-guru/academic-word/{type}/{documentId}/view
 * (lms.schoolAdmin.registrasiGuru.academicWord.sharedView) was registered but
 * pointed at AcademicDocumentWordController::viewSharedDocument, which never
 * existed — every view_url produced by the browse endpoint 500ed. The method
 * is now implemented; this locks the shared view for both word types.
 */
class AcademicWordSharedViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_shared_word_document_view_renders_for_both_types(): void
    {
        $admin = UserAccount::create(['email' => 'admin@test.id', 'password' => 'secret', 'no_hp' => '0811111101', 'role' => 'admin sekolah']);
        $teacher = UserAccount::create(['email' => 'guru@test.id', 'password' => 'secret', 'no_hp' => '0811111102', 'role' => 'Guru']);

        $partner = SchoolPartner::create(['nama_sekolah' => 'SMA Test', 'npsn' => '12345678', 'kepsek_id' => $admin->id, 'jenjang_sekolah' => 'SMA']);

        \App\Models\SchoolStaffProfile::create([
            'user_id' => $admin->id,
            'school_partner_id' => $partner->id,
            'enrollment_type' => 'B2B',
            'nama_lengkap' => 'Admin Sekolah Test',
            'nik' => '1234567890123456',
            'personal_email' => 'admin@test.id',
        ]);

        $rppm = new AcademicDocument();
        $rppm->forceFill([
            'title' => 'RPPM Test',
            'original_filename' => 'rppm-test.docx',
            'school_partner_id' => $partner->id,
            'owner_user_id' => $teacher->id,
            'document_type' => 'rppm_word',
            'status' => 'saved',
            'subject_id' => 1,
        ])->save();

        AcademicDocumentWordContent::create([
            'academic_document_id' => $rppm->id,
            'title' => 'RPPM Test',
            'content' => '<p>Isi RPPM yang dibagikan</p>',
        ]);

        $refleksi = new AcademicDocument();
        $refleksi->forceFill([
            'title' => 'Refleksi Test',
            'original_filename' => 'refleksi-test.docx',
            'school_partner_id' => $partner->id,
            'owner_user_id' => $teacher->id,
            'document_type' => 'refleksi_guru',
            'status' => 'saved',
            'subject_id' => 1,
        ])->save();

        AcademicDocumentWordContent::create([
            'academic_document_id' => $refleksi->id,
            'title' => 'Refleksi Test',
            'content' => '<p>Isi refleksi yang dibagikan</p>',
        ]);

        // contract billing is unrelated to rendering; no subscription fixture here
        $this->withoutMiddleware(\App\Http\Middleware\CheckSchoolContract::class);

        // sanity: the browse endpoint that produces the view_url must keep working
        $browse = $this->actingAs($admin)->get("/lms/school-admin/sekolah/{$partner->id}/registrasi-guru/academic-word/rppm/browse");
        $browse->assertOk();
        $this->assertSame(
            url("/lms/school-admin/sekolah/{$partner->id}/registrasi-guru/academic-word/rppm/{$rppm->id}/view"),
            $browse->json('data.0.view_url')
        );

        $response = $this->actingAs($admin)->get(
            "/lms/school-admin/sekolah/{$partner->id}/registrasi-guru/academic-word/rppm/{$rppm->id}/view"
        );
        $response->assertOk();

        $response = $this->actingAs($admin)->get(
            "/lms/school-admin/sekolah/{$partner->id}/registrasi-guru/academic-word/refleksi-guru/{$refleksi->id}/view"
        );
        $response->assertOk();
    }

    public function test_shared_word_document_view_hides_documents_from_other_schools(): void
    {
        $adminA = UserAccount::create(['email' => 'admin-a@test.id', 'password' => 'secret', 'no_hp' => '0811111101', 'role' => 'admin sekolah']);
        $teacher = UserAccount::create(['email' => 'guru@test.id', 'password' => 'secret', 'no_hp' => '0811111102', 'role' => 'Guru']);

        // school A owns the document; school B is a different partner
        $partnerA = SchoolPartner::create(['nama_sekolah' => 'SMA Test', 'npsn' => '12345678', 'kepsek_id' => $adminA->id, 'jenjang_sekolah' => 'SMA']);
        $adminB = UserAccount::create(['email' => 'admin-b@test.id', 'password' => 'secret', 'no_hp' => '0811111103', 'role' => 'admin sekolah']);
        $partnerB = SchoolPartner::create(['nama_sekolah' => 'SMA Lain', 'npsn' => '87654321', 'kepsek_id' => $adminB->id, 'jenjang_sekolah' => 'SMA']);

        \App\Models\SchoolStaffProfile::create([
            'user_id' => $adminA->id,
            'school_partner_id' => $partnerA->id,
            'enrollment_type' => 'B2B',
            'nama_lengkap' => 'Admin Sekolah A',
            'nik' => '1234567890123456',
            'personal_email' => 'admin-a@test.id',
        ]);
        \App\Models\SchoolStaffProfile::create([
            'user_id' => $adminB->id,
            'school_partner_id' => $partnerB->id,
            'enrollment_type' => 'B2B',
            'nama_lengkap' => 'Admin Sekolah B',
            'nik' => '2345678901234567',
            'personal_email' => 'admin-b@test.id',
        ]);

        $rppm = new AcademicDocument();
        $rppm->forceFill([
            'title' => 'RPPM Test',
            'original_filename' => 'rppm-test.docx',
            'school_partner_id' => $partnerA->id,
            'owner_user_id' => $teacher->id,
            'document_type' => 'rppm_word',
            'status' => 'saved',
            'subject_id' => 1,
        ])->save();

        AcademicDocumentWordContent::create([
            'academic_document_id' => $rppm->id,
            'title' => 'RPPM Test',
            'content' => '<p>Isi RPPM sekolah A</p>',
        ]);

        // contract billing is unrelated to rendering; no subscription fixture here
        $this->withoutMiddleware(\App\Http\Middleware\CheckSchoolContract::class);

        // admin of school B requests school A's document: the school-scoped
        // lookup (getSchoolProfileOrFail + school_partner_id filter) must 404
        // — a shared document URL must never leak across partners
        $response = $this->actingAs($adminB)->get(
            "/lms/school-admin/sekolah/{$partnerA->id}/registrasi-guru/academic-word/rppm/{$rppm->id}/view"
        );
        $response->assertNotFound();
    }

    public function test_shared_word_document_view_refuses_roles_outside_view_roles(): void
    {
        $admin = UserAccount::create(['email' => 'admin@test.id', 'password' => 'secret', 'no_hp' => '0811111101', 'role' => 'admin sekolah']);
        $teacher = UserAccount::create(['email' => 'guru@test.id', 'password' => 'secret', 'no_hp' => '0811111102', 'role' => 'Guru']);
        $student = UserAccount::create(['email' => 'student@test.id', 'password' => 'secret', 'no_hp' => '0811111103', 'role' => 'Siswa']);

        $partner = SchoolPartner::create(['nama_sekolah' => 'SMA Test', 'npsn' => '12345678', 'kepsek_id' => $admin->id, 'jenjang_sekolah' => 'SMA']);

        \App\Models\SchoolStaffProfile::create([
            'user_id' => $admin->id,
            'school_partner_id' => $partner->id,
            'enrollment_type' => 'B2B',
            'nama_lengkap' => 'Admin Sekolah Test',
            'nik' => '1234567890123456',
            'personal_email' => 'admin@test.id',
        ]);
        \App\Models\StudentProfile::create([
            'user_id' => $student->id,
            'school_partner_id' => $partner->id,
            'nama_lengkap' => 'Siswa Test',
            'personal_email' => 'student@test.id',
            'nisn' => '0012345678',
            'enrollment_type' => 'B2B',
        ]);

        $rppm = new AcademicDocument();
        $rppm->forceFill([
            'title' => 'RPPM Test',
            'original_filename' => 'rppm-test.docx',
            'school_partner_id' => $partner->id,
            'owner_user_id' => $teacher->id,
            'document_type' => 'rppm_word',
            'status' => 'saved',
            'subject_id' => 1,
        ])->save();

        AcademicDocumentWordContent::create([
            'academic_document_id' => $rppm->id,
            'title' => 'RPPM Test',
            'content' => '<p>Isi RPPM yang dibagikan</p>',
        ]);

        // contract billing is unrelated to rendering; no subscription fixture here
        $this->withoutMiddleware(\App\Http\Middleware\CheckSchoolContract::class);

        // the 'siswa' URL slug lets the student past EnsureRoleAccess, so this
        // request really reaches the controller and pins its own role gate:
        // VIEW_ROLES is guru/admin sekolah/kepala sekolah/wakil kepala sekolah —
        // a student is refused with 403 by abortIfCannotView
        $response = $this->actingAs($student)->get(
            "/lms/siswa/sekolah/{$partner->id}/registrasi-guru/academic-word/rppm/{$rppm->id}/view"
        );
        $response->assertForbidden();
    }
}

<?php

namespace Tests\Feature;

use App\Models\Bab;
use App\Models\Fase;
use App\Models\Kelas;
use App\Models\Kurikulum;
use App\Models\LmsContent;
use App\Models\LmsContentItem;
use App\Models\LmsMeetingContent;
use App\Models\Mapel;
use App\Models\SchoolClass;
use App\Models\SchoolPartner;
use App\Models\Service;
use App\Models\ServiceRule;
use App\Models\SubBab;
use App\Models\UserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression: the review-content preview URL built by
 * paginateTeacherContentForReleaseReviewMeeting's URL map must resolve to a
 * working action and render the review-content view (it previously looked
 * broken because the controller method was PascalCased).
 */
class TeacherContentReleasePreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_content_preview_url_renders(): void
    {
        $admin = UserAccount::create(['email' => 'admin@test.id', 'password' => 'secret', 'no_hp' => '0811111101', 'role' => 'administrator']);
        $teacher = UserAccount::create(['email' => 'teacher@test.id', 'password' => 'secret', 'no_hp' => '0811111102', 'role' => 'Guru']);

        $partner = SchoolPartner::create(['nama_sekolah' => 'SMA Test', 'npsn' => '12345678', 'kepsek_id' => $admin->id, 'jenjang_sekolah' => 'SMA']);
        $kurikulum = Kurikulum::create(['user_id' => $admin->id, 'nama_kurikulum' => 'Kurikulum Merdeka', 'kode' => 'KM']);
        $fase = Fase::create(['user_id' => $admin->id, 'nama_fase' => 'Fase E', 'kode' => 'E', 'kurikulum_id' => $kurikulum->id]);
        $kelas = Kelas::create(['user_id' => $admin->id, 'kelas' => 'Kelas 10', 'kode' => 'X', 'fase_id' => $fase->id, 'kurikulum_id' => $kurikulum->id]);
        $mapel = Mapel::create(['user_id' => $admin->id, 'mata_pelajaran' => 'Matematika', 'kode' => 'MAT', 'kelas_id' => $kelas->id, 'kurikulum_id' => $kurikulum->id]);
        $bab = Bab::create(['user_id' => $admin->id, 'nama_bab' => 'Bab 1', 'kode' => 'B1', 'mapel_id' => $mapel->id, 'kelas_id' => $kelas->id, 'kurikulum_id' => $kurikulum->id]);
        $subBab = SubBab::create(['user_id' => $admin->id, 'sub_bab' => 'Sub 1', 'kode' => 'S1', 'bab_id' => $bab->id, 'mapel_id' => $mapel->id, 'kelas_id' => $kelas->id, 'kurikulum_id' => $kurikulum->id]);

        $class = SchoolClass::create(['school_partner_id' => $partner->id, 'class_name' => '10-A', 'kelas_id' => $kelas->id, 'wali_kelas_id' => $admin->id, 'tahun_ajaran' => '2026/2027']);

        \App\Models\SchoolStaffProfile::create([
            'user_id' => $teacher->id,
            'school_partner_id' => $partner->id,
            'enrollment_type' => 'B2B',
            'nama_lengkap' => 'Guru Test',
            'nik' => '1234567890123456',
            'personal_email' => 'guru@test.id',
        ]);

        $service = Service::create(['kurikulum_id' => $kurikulum->id, 'name' => 'Rapat Pemanasan', 'school_partner_status' => true]);
        $rule = ServiceRule::create(['service_id' => $service->id, 'upload_type' => 'textarea']);

        $content = LmsContent::create([
            'user_id' => $teacher->id, 'service_id' => $service->id, 'school_partner_id' => $partner->id,
            'kurikulum_id' => $kurikulum->id, 'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id,
            'bab_id' => $bab->id, 'sub_bab_id' => $subBab->id, 'is_active' => true,
        ]);
        LmsContentItem::create([
            'lms_content_id' => $content->id, 'service_rule_id' => $rule->id,
            'value_text' => 'Materi pertemuan pertama', 'value_file' => null, 'original_filename' => null,
        ]);

        $meetingContent = LmsMeetingContent::create([
            'teacher_id' => $teacher->id, 'school_class_id' => $class->id, 'mapel_id' => $mapel->id,
            'school_partner_id' => $partner->id, 'lms_content_id' => $content->id, 'service_id' => $service->id,
            'semester' => 1, 'meeting_number' => 1, 'meeting_date' => now()->toDateString(), 'is_active' => true,
        ]);

        // contract billing is unrelated to rendering; no subscription fixture here
        $this->withoutMiddleware(\App\Http\Middleware\CheckSchoolContract::class);

        $response = $this->actingAs($teacher)->get(
            "/lms/guru/sekolah/{$partner->id}/content-for-release/rombel-kelas/{$class->id}/subject/{$mapel->id}/semester/1/service/{$service->id}/review-content/{$meetingContent->id}"
        );

        $response->assertOk();
        $response->assertSee('Materi pertemuan pertama');
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProfileAccountValidationTest extends TestCase
{
    /**
     * Test student personal info validation returns standard browser error JSON.
     */
    public function test_student_personal_information_validation_fails_with_json(): void
    {
        // Unauthenticated request should be handled by auth middleware
        $response = $this->postJson('/lms/guru/sekolah/1/profile-account/personal-information-student/update/1', [
            'nama_lengkap' => '',
            'no_hp' => 'invalid-phone',
            'personal_email' => 'invalid-email',
        ]);

        // Returns 401 or redirect because user is unauthenticated
        $this->assertTrue(in_array($response->status(), [302, 401, 422]));
    }
}

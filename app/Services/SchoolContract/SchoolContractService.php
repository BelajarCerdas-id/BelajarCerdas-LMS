<?php

namespace App\Services\SchoolContract;

use App\Models\SchContract;
use App\Models\SchTermStudent;
use App\Models\UserAccount;

class SchoolContractService
{
    /**
     *
     *
     * @param UserAccount $user
     * @return array
     */

    private function getSchoolPartnerId(UserAccount $user): ?int
    {
        return match ($user->role) {

            'Siswa' => optional($user->StudentProfile)->school_partner_id,

            'Orang Tua' => optional($user->ParentProfile)->school_partner_id,

            default => optional($user->SchoolStaffProfile)->school_partner_id,
        };
    }

    private function officeProfile(UserAccount $user): ?object
    {
        return $user->OfficeProfile;
    }

    private function schoolFoundationProfile(UserAccount $user): ?object
    {
        return $user->SchoolFoundationProfile;
    }

    public function validate(UserAccount $user): array
    {
        $schoolPartnerId = $this->getSchoolPartnerId($user);

        $officeProfile = $this->officeProfile($user);

        $schoolFoundationProfile = $this->schoolFoundationProfile($user);

        // office profile tidak di cek
        if ($officeProfile) {
            return [
                'success' => true,
                'message' => null,
            ];
        }

        // foundation profile tidak di cek
        if ($schoolFoundationProfile) {
            return [
                'success' => true,
                'message' => null,
            ];
        }

        // Jika sekolah tidak ditemukan
        if (!$schoolPartnerId) {
            return [
                'success' => false,
                'message' => 'Sekolah tidak ditemukan.',
            ];
        }

        // Cari kontrak aktif
        $contract = SchContract::where('school_partner_id', $schoolPartnerId)->where('status', 'active')->whereDate('start_contract', '<=', today())
            ->whereDate('end_contract', '>=', today())->latest('end_contract')->first();

        if (!$contract) {
            return [
                'success' => false,
                'message' => 'Tidak ada kontrak sekolah yang aktif.',
            ];
        }

        // Jika bukan siswa maka hanya cek status kontrak saja
        if ($user->role !== 'Siswa') {
            return [
                'success' => true,
                'message' => null,
            ];
        }

        // Cari term aktif
        $term = $contract->SchContractTerm()->whereDate('period_start', '<=', today())->whereDate('period_end', '>=', today())->first();

        if (!$term) {
            return [
                'success' => false,
                'message' => 'Periode kontrak tidak aktif.',
            ];
        }

        // Cek apakah siswa terdaftar pada term
        $registered = SchTermStudent::where('term_id', $term->id)->where('student_id', $user->id)->where('status', 'active')->exists();

        if (!$registered) {
            return [
                'success' => false,
                'message' => 'Akun siswa tidak terdaftar pada periode kontrak yang sedang berjalan.',
            ];
        }

        return [
            'success' => true,
            'message' => null,
        ];
    }
}
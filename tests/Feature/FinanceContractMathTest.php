<?php

namespace Tests\Feature;

use App\Models\Feature;
use App\Models\FeaturePrice;
use App\Models\SchContract;
use App\Models\SchContractTerm;
use App\Models\SchTermStudent;
use App\Models\SchoolPartner;
use App\Models\UserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

// uang math: students × price × months per term (FinanceContractController::paginateManageContract)
class FinanceContractMathTest extends TestCase
{
    use RefreshDatabase;

    public function test_kpi_math_uses_active_students_times_price_times_months_per_term(): void
    {
        $creator = UserAccount::create([
            'email' => 'office@test.id',
            'password' => 'secret',
            'no_hp' => '0811111111',
            'role' => 'administrator',
        ]);

        $studentA = UserAccount::create([
            'email' => 'a@test.id',
            'password' => 'secret',
            'no_hp' => '0811111112',
            'role' => 'Siswa',
        ]);

        $studentB = UserAccount::create([
            'email' => 'b@test.id',
            'password' => 'secret',
            'no_hp' => '0811111113',
            'role' => 'Siswa',
        ]);

        $partner = SchoolPartner::create([
            'nama_sekolah' => 'SMA Test',
            'npsn' => '12345678',
            'kepsek_id' => $creator->id,
            'jenjang_sekolah' => 'SMA',
        ]);

        $feature = Feature::create([
            'nama_fitur' => 'LMS',
        ]);

        $featurePrice = FeaturePrice::create([
            'feature_id' => $feature->id,
            'variant_name' => 'Full',
            'variant_type' => 'B2B',
            'duration' => '12 bulan',
            'price' => 1000,
        ]);

        $contract = SchContract::create([
            'user_id' => $creator->id,
            'school_partner_id' => $partner->id,
            'feature_id' => $feature->id,
            'feature_price_id' => $featurePrice->id,
            'contract_number' => 'CTR-001',
            'start_contract' => '2026-01-01',
            'end_contract' => '2026-07-01',
            'init_student_count' => 2,
            'price_per_student' => 1000,
            'total_term' => 2,
            'status' => 'active',
        ]);

        // 6 bulan kontrak / 2 term = 3 bulan per term
        $term1 = SchContractTerm::create([
            'contract_id' => $contract->id,
            'term_number' => 1,
            'period_start' => '2026-01-01',
            'period_end' => '2026-04-01',
            'status' => 'paid',
        ]);

        $term2 = SchContractTerm::create([
            'contract_id' => $contract->id,
            'term_number' => 2,
            'period_start' => '2026-04-01',
            'period_end' => '2026-07-01',
            'status' => 'unpaid',
        ]);

        foreach ([$studentA, $studentB] as $student) {
            SchTermStudent::create([
                'office_id' => $creator->id,
                'term_id' => $term1->id,
                'student_id' => $student->id,
                'status' => 'active',
            ]);
        }

        SchTermStudent::create([
            'office_id' => $creator->id,
            'term_id' => $term2->id,
            'student_id' => $studentA->id,
            'status' => 'active',
        ]);

        SchTermStudent::create([
            'office_id' => $creator->id,
            'term_id' => $term2->id,
            'student_id' => $studentB->id,
            'status' => 'inactive', // tidak dihitung
        ]);

        $response = app(\App\Http\Controllers\Finance\FinanceContractController::class)
            ->paginateManageContract(new Request(), 'finance');

        $kpi = $response->getData(true)['kpi'];

        // term1 paid: 2 siswa aktif × 1000 × 3 = 6000
        $this->assertSame(6000, $kpi['total_paid']);
        // term2 unpaid: 1 siswa aktif × 1000 × 3 = 3000
        $this->assertSame(3000, $kpi['total_outstanding']);
        // 2 siswa × 1000 × 6 bulan = 12000
        $this->assertSame(12000, $kpi['total_contract_value']);
        // 6000 / 9000 = 67%
        $this->assertSame(67, $kpi['collection_rate']);
    }
}

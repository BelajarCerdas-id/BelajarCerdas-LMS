<?php

namespace App\Http\Controllers;

use App\Events\LmsSchoolSubscription;
use App\Events\LmsManagementAccount;
use App\Events\LmsManagementClass;
use App\Events\LmsManagementMajors;
use App\Events\LmsManagementStudentInClass;
use App\Models\Fase;
use App\Models\SchoolClass;
use App\Models\SchoolLmsSubscription;
use App\Models\SchoolMajor;
use App\Models\SchoolPartner;
use App\Models\SchoolStaffProfile;
use App\Models\StudentSchoolClass;
use App\Models\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LmsController extends Controller
{
    // function lms school subscription view
    public function lmsSchoolSubscriptionView()
    {
        return view('features.lms.administrator.lms-school-subscription');
    }

    // function pagiante lms school subscription
    public function paginateLmsSchoolSubscription(Request $request)
    {
        $today = now()->format('Y-m-d');

        $lmsSchoolSubscription = SchoolLmsSubscription::whereHas('Transaction', function ($query) {
            $query->where('transaction_status', 'Berhasil');
        })->where('end_date', '<', $today)->get();

        if ($lmsSchoolSubscription) {
            foreach ($lmsSchoolSubscription as $history) {
                $history->update([
                    'subscription_status' => 'tidak_aktif'
                ]);
            }
        }

        $lmsSchoolSubscription = SchoolPartner::with(['UserAccount.SchoolStaffProfile', 'SchoolLmsSubscription' => function ($query) {
            $query->whereHas('transaction', function ($q) {
                $q->where('transaction_status', 'Berhasil');
            })->orderByDesc('start_date')->limit(1); // ambil subscription terbaru
        }
        ])->orderBy('updated_at', 'desc');


        // Filter school
        if ($request->filled('search_school')) {
            $search = $request->search_school;
            $lmsSchoolSubscription->where('nama_sekolah', 'LIKE', "%{$search}%");
        }

        $paginated = $lmsSchoolSubscription->paginate(20);

        return response()->json([
            'data' => $paginated->values(), // flat array, bukan nested
            'links' => (string) $paginated->links(),
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
            'lmsManagementUsers' => '/lms/school-subscription/:schoolName/:schoolId/management-role-account',
        ]);
    }

    // function activate lms school subscription
    public function lmsSchoolSubscriptionActivate(Request $request, $subscriptionId)
    {
        $subscription = SchoolLmsSubscription::findOrFail($subscriptionId);

        $subscription->update([
            'subscription_status' => $request->subscription_status,
        ]);

        broadcast(new LmsSchoolSubscription($subscription))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Subscription status updated successfully',
            'subscription' => $subscription
        ]);
    }
}
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LmsController;
use App\Http\Controllers\MasterAcademicController;
use App\Http\Controllers\SchoolPartnerController;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ROUTE FALLBACK
Route::fallback(function () {
    // Sudah login → arahkan ke dashboard
    if (Auth::check()) {
        return redirect()->route('beranda');
    }

    // Belum login → arahkan ke login
    return redirect()->route('login');
});

Route::get('/', fn () => redirect('/login'));

// middleware redirect if authenticated
Route::middleware([RedirectIfAuthenticated::class])->group(function () {
    Route::get('/login', [AuthController::class, 'loginView'])->name('login');
});

// routes auth login & logout
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ROUTES DROPDOWN KELAS, DLL
Route::get('/kelas/{id}', [MasterAcademicController::class, 'getKelas']); // kelas by fase

// MIDDLEWARE LOGIN
Route::middleware([AuthMiddleware::class])->group(function () {
    // DASHBOARD
    Route::get('/beranda', [DashboardController::class, 'index'])->name('beranda');

    // ROUTES LMS FEATURE
    // views (administrator)
    Route::get('/lms/school-subscription', [LmsController::class, 'lmsSchoolSubscriptionView'])->name('lms.schoolSubscription.view');

    // route management role account
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account', [LmsController::class, 'lmsManagementRolesView'])->name('lms.managementRoles.view');

    // route management account
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-accounts', [LmsController::class, 'lmsManagementAccountView'])->name('lms.managementAccount.view');

    // route management majors
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-majors', [LmsController::class, 'lmsManagementMajorsView'])->name('lms.managementMajors.view');

    // CRUD
    // routes crud majors
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-majors/create', [LmsController::class, 'lmsManagementCreateMajor'])->name('lms.managementCreateMajor.store');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-majors/{majorId}/edit', [LmsController::class, 'lmsManagementEditMajor'])->name('lms.managementEditMajor.store');

    // routes activate school subscription, account, major
    Route::put('/lms/school-subscription/{subscriptionId}/activate', [LmsController::class, 'lmsSchoolSubscriptionActivate'])->name('lms.schoolSubscription.activate');
    Route::put('/lms/school-subscription/{schoolId}/management-account/{id}/activate-account', [LmsController::class, 'lmsActivateAccount'])->name('lms.account.activate');
    Route::put('/lms/school-subscription/management-class/{id}/activate-major', [LmsController::class, 'lmsActivateMajor'])->name('lms.major.activate');

    // paginate
    Route::get('/lms/school-subscription/paginate', [LmsController::class, 'paginateLmsSchoolSubscription'])->name('lms.schoolSubscription.paginate');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/role-account/paginate', [LmsController::class, 'paginateLmsSchoolSubscriptionRoleAccount'])->name('lms.SchoolSubscriptionRoleAccount.paginate');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-accounts/paginate', [LmsController::class, 'paginateLmsSchoolAccount'])->name('lms.SchoolSubscriptionAccount.paginate');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/role-account/{role}/management-majors/paginate', [LmsController::class, 'paginateLmsSchoolSubscriptionMajors'])->name('lms.SchoolSubscriptionMajors.paginate');
});

// ROUTES SCHOOL PARTNER
Route::post('/school-subcsription/store', [SchoolPartnerController::class, 'bulkUploadSchoolPartner'])->name('bulkUploadSchoolPartner.store');
Route::post('/school-subscription/add-users/store', [SchoolPartnerController::class, 'bulkUploadAddUsers'])->name('bulkUploadAddUsers.store');
<?php

use App\Http\Controllers\LmsController;
use App\Http\Controllers\SchoolAdminDashboardController;
use App\Http\Controllers\SchoolPartnerController;
use Illuminate\Support\Facades\Route;

// ROUTES SCHOOL PARTNER
// validate
Route::post('/school-subscription/validate', [SchoolPartnerController::class, 'bulkUploadSchoolPartnerValidate'])->name('bulk_upload_school_partner.validate');

// store
Route::post('/school-subscription/store', [SchoolPartnerController::class, 'bulkUploadSchoolPartner'])->name('bulk_upload_school_partner.store');

// validate
Route::post('/school-subscription/add-users/validate', [SchoolPartnerController::class, 'bulkUploadAddUsersValidate'])->name('bulk_upload_add_users.validate');

// store
Route::post('/school-subscription/add-users/store', [SchoolPartnerController::class, 'bulkUploadAddUsers'])->name('bulk_upload_add_users.store');
Route::post('/lms/teacher/tugas/store', [LmsController::class, 'storeTugas'])->name('lms.teacher.tugas.store');

// ROUTES SCHOOL ADMIN
// dashboard
Route::get('/lms/{role}/{schoolName}/{schoolId}/admin/dashboard', [SchoolAdminDashboardController::class, 'index'])->name('lms.school_admin.dashboard.view');

// edit school logo
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/edit-school-logo', [LmsController::class, 'editSchoolLogo'])->name('lms.edit_logo');

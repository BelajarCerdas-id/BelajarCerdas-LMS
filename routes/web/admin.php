<?php

use App\Http\Controllers\LmsController;
use App\Http\Controllers\SchoolAdminDashboardController;
use App\Http\Controllers\SchoolPartnerController;
use Illuminate\Support\Facades\Route;

// ROUTES SCHOOL PARTNER
// validate
Route::post('/school-subscription/validate', [SchoolPartnerController::class, 'bulkUploadSchoolPartnerValidate'])->name('bulkUploadSchoolPartner.validate');

// store
Route::post('/school-subscription/store', [SchoolPartnerController::class, 'bulkUploadSchoolPartner'])->name('bulkUploadSchoolPartner.store');

// validate
Route::post('/school-subscription/add-users/validate', [SchoolPartnerController::class, 'bulkUploadAddUsersValidate'])->name('bulkUploadAddUsers.validate');

// store
Route::post('/school-subscription/add-users/store', [SchoolPartnerController::class, 'bulkUploadAddUsers'])->name('bulkUploadAddUsers.store');
Route::post('/lms/teacher/tugas/store', [LmsController::class, 'storeTugas'])->name('lms.teacher.tugas.store');

// ROUTES SCHOOL ADMIN
// dashboard
Route::get('/lms/{role}/{schoolName}/{schoolId}/admin/dashboard', [SchoolAdminDashboardController::class, 'index'])->name('lms.schoolAdmin.dashboard.view');

// edit school logo
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/edit-school-logo', [LmsController::class, 'editSchoolLogo'])->name('lms.editLogo');

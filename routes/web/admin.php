<?php

use App\Http\Controllers\LmsController;
use App\Http\Controllers\SchoolAdminDashboardController;
use App\Http\Controllers\SchoolPartnerController;
use Illuminate\Support\Facades\Route;

// ROUTES SCHOOL PARTNER
// validate
Route::post('/school-subscription/validate', [SchoolPartnerController::class, 'bulkUploadSchoolPartnerValidate'])->name('BulkUploadSchoolPartner.Validate');

// store
Route::post('/school-subscription/store', [SchoolPartnerController::class, 'bulkUploadSchoolPartner'])->name('BulkUploadSchoolPartner.Store');

// validate
Route::post('/school-subscription/add-users/validate', [SchoolPartnerController::class, 'bulkUploadAddUsersValidate'])->name('BulkUploadAddUsers.Validate');

// store
Route::post('/school-subscription/add-users/store', [SchoolPartnerController::class, 'bulkUploadAddUsers'])->name('BulkUploadAddUsers.Store');
Route::post('/lms/teacher/tugas/store', [LmsController::class, 'storeTugas'])->name('Lms.Teacher.Tugas.Store');

// ROUTES SCHOOL ADMIN
// dashboard
Route::get('/lms/{role}/{schoolName}/{schoolId}/admin/dashboard', [SchoolAdminDashboardController::class, 'index'])->name('Lms.SchoolAdmin.Dashboard.View');

// edit school logo
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/edit-school-logo', [LmsController::class, 'editSchoolLogo'])->name('Lms.EditLogo');

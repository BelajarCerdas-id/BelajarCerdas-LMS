<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileAccountController;
use Illuminate\Support\Facades\Route;

// DASHBOARD
Route::get('/lms/{role}', [DashboardController::class, 'index'])->name('beranda');

// PROFILE USER
// school partner & non school partner views
Route::get('/lms/{role}/{schoolName}/{schoolId}/profile-account', [ProfileAccountController::class, 'index'])->name('profile-account-school-partner.view');
Route::get('/lms/{role}/profile-account', [ProfileAccountController::class, 'index'])->name('profile-account-non-school-partner.view');

// update personal information
Route::post('/lms/{role}/{schoolName}/{schoolId}/profile-account/personal-information-student/update/{userId}', [ProfileAccountController::class, 'updatePersonalInformationStudent'])->name('profile-account-school-partner.personal-information-student.update');
Route::post('/lms/{role}/profile-account/personal-information-office/update/{userId}', [ProfileAccountController::class, 'updatePersonalInformationOffice'])->name('profile-account-school-partner.personal-information-office.update');
Route::post('/lms/{role}/{schoolName}/{schoolId}/profile-account/personal-information-school-staff/update/{userId}', [ProfileAccountController::class, 'updatePersonalInformationSchoolStaff'])->name('profile-account-school-partner.personal-information-schoolStaff.update');
Route::post('/lms/{role}/{schoolName}/{schoolId}/profile-account/personal-information-parent/update/{userId}', [ProfileAccountController::class, 'updatePersonalInformationParent'])->name('profile-account-school-partner.personal-information-parent.update');
Route::post('/lms/{role}/profile-account/personal-information-scholl-foundation/update/{userId}', [ProfileAccountController::class, 'updatePersonalInformationSchoolFoundation'])->name('profile-account-school-partner.personal-information-school-foundation.update');

// school partner & non school partner reset password views
Route::get('/lms/{role}/{schoolName}/{schoolId}/profile-account/reset-password', [ProfileAccountController::class, 'resetPasswordView'])->name('profile-account-school-partner.reset-password.view');
Route::get('/lms/{role}/profile-account/reset-password', [ProfileAccountController::class, 'resetPasswordView'])->name('profile-account-non-school-partner.reset-password.view');

// reset password update
Route::post('/lms/{role}/{schoolName}/{schoolId}/profile-account/reset-password/update', [ProfileAccountController::class, 'resetPasswordUpdate'])->name('profile-account-school-partner.reset-password.update');
Route::post('/lms/{role}/profile-account/reset-password/update', [ProfileAccountController::class, 'resetPasswordUpdate'])->name('profile-account-non-school-partner.reset-password.update');

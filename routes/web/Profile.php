<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileAccountController;
use Illuminate\Support\Facades\Route;

// DASHBOARD
Route::get('/lms/{role}', [DashboardController::class, 'index'])->name('Beranda');

// PROFILE USER
// school partner & non school partner views
Route::get('/lms/{role}/{schoolName}/{schoolId}/profile-account', [ProfileAccountController::class, 'index'])->name('profile_account_school_partner.view');
Route::get('/lms/{role}/profile-account', [ProfileAccountController::class, 'index'])->name('profile_account_non_school_partner.view');

// update personal information
Route::post('/lms/{role}/{schoolName}/{schoolId}/profile-account/personal-information-student/update/{userId}', [ProfileAccountController::class, 'updatePersonalInformationStudent'])->name('profile_account_school_partner.personal_information_student.update');
Route::post('/lms/{role}/profile-account/personal-information-office/update/{userId}', [ProfileAccountController::class, 'updatePersonalInformationOffice'])->name('profile_account_school_partner.personal_information_office.update');
Route::post('/lms/{role}/{schoolName}/{schoolId}/profile-account/personal-information-school-staff/update/{userId}', [ProfileAccountController::class, 'updatePersonalInformationSchoolStaff'])->name('profile_account_school_partner.personal_information_school_staff.update');
Route::post('/lms/{role}/{schoolName}/{schoolId}/profile-account/personal-information-parent/update/{userId}', [ProfileAccountController::class, 'updatePersonalInformationParent'])->name('profile_account_school_partner.personal_information_parent.update');
Route::post('/lms/{role}/profile-account/personal-information-scholl-foundation/update/{userId}', [ProfileAccountController::class, 'updatePersonalInformationSchoolFoundation'])->name('profile_account_school_partner.personal_information_school_foundation.update');

// school partner & non school partner reset password views
Route::get('/lms/{role}/{schoolName}/{schoolId}/profile-account/reset-password', [ProfileAccountController::class, 'resetPasswordView'])->name('profile_account_school_partner.reset_password.view');
Route::get('/lms/{role}/profile-account/reset-password', [ProfileAccountController::class, 'resetPasswordView'])->name('profile_account_non_school_partner.reset_password.view');

// reset password update
Route::post('/lms/{role}/{schoolName}/{schoolId}/profile-account/reset-password/update', [ProfileAccountController::class, 'resetPasswordUpdate'])->name('profile_account_school_partner.reset_password.update');
Route::post('/lms/{role}/profile-account/reset-password/update', [ProfileAccountController::class, 'resetPasswordUpdate'])->name('profile_account_non_school_partner.reset_password.update');

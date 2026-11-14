<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileAccountController;
use Illuminate\Support\Facades\Route;

// DASHBOARD
Route::get('/lms/{role}', [DashboardController::class, 'index'])->name('Beranda');

// PROFILE USER
// school partner & non school partner views
Route::get('/lms/{role}/{schoolName}/{schoolId}/profile-account', [ProfileAccountController::class, 'index'])->name('ProfileAccountSchoolPartner.View');
Route::get('/lms/{role}/profile-account', [ProfileAccountController::class, 'index'])->name('ProfileAccountNonSchoolPartner.View');

// update personal information
Route::post('/lms/{role}/{schoolName}/{schoolId}/profile-account/personal-information-student/update/{userId}', [ProfileAccountController::class, 'updatePersonalInformationStudent'])->name('ProfileAccountSchoolPartner.PersonalInformationStudent.Update');
Route::post('/lms/{role}/profile-account/personal-information-office/update/{userId}', [ProfileAccountController::class, 'updatePersonalInformationOffice'])->name('ProfileAccountSchoolPartner.PersonalInformationOffice.Update');
Route::post('/lms/{role}/{schoolName}/{schoolId}/profile-account/personal-information-school-staff/update/{userId}', [ProfileAccountController::class, 'updatePersonalInformationSchoolStaff'])->name('ProfileAccountSchoolPartner.PersonalInformationSchoolStaff.Update');
Route::post('/lms/{role}/{schoolName}/{schoolId}/profile-account/personal-information-parent/update/{userId}', [ProfileAccountController::class, 'updatePersonalInformationParent'])->name('ProfileAccountSchoolPartner.PersonalInformationParent.Update');
Route::post('/lms/{role}/profile-account/personal-information-scholl-foundation/update/{userId}', [ProfileAccountController::class, 'updatePersonalInformationSchoolFoundation'])->name('ProfileAccountSchoolPartner.PersonalInformationSchoolFoundation.Update');

// school partner & non school partner reset password views
Route::get('/lms/{role}/{schoolName}/{schoolId}/profile-account/reset-password', [ProfileAccountController::class, 'resetPasswordView'])->name('ProfileAccountSchoolPartner.ResetPassword.View');
Route::get('/lms/{role}/profile-account/reset-password', [ProfileAccountController::class, 'resetPasswordView'])->name('ProfileAccountNonSchoolPartner.ResetPassword.View');

// reset password update
Route::post('/lms/{role}/{schoolName}/{schoolId}/profile-account/reset-password/update', [ProfileAccountController::class, 'resetPasswordUpdate'])->name('ProfileAccountSchoolPartner.ResetPassword.Update');
Route::post('/lms/{role}/profile-account/reset-password/update', [ProfileAccountController::class, 'resetPasswordUpdate'])->name('ProfileAccountNonSchoolPartner.ResetPassword.Update');

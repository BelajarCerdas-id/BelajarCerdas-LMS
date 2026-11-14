<?php

use App\Http\Controllers\Lms\MajorSubject\MajorSubjectController;
use App\Http\Controllers\Lms\QuestionBank\QuestionBankController;
use App\Http\Controllers\Lms\StudentSubjectSelection\StudentSubjectSelectionController;
use Illuminate\Support\Facades\Route;

// ROUTES MAJOR SUBJECT MANAGEMENT
// views
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/major-subject-management', [MajorSubjectController::class, 'index'])->name('lms.major_subject_management.view.school_partner');

// load data form
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/major-subject-management/load-data-form', [MajorSubjectController::class, 'loadMajorSubjectForm'])->name('lms.major_subject_management.load_data_form');

// crud
Route::post('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/major-subject-management/submit-form', [MajorSubjectController::class, 'majorSubjectSubmitForm'])->name('lms.major_subject_management.submit_form');

// ROUTES STUDENT SUBJECT SELECTION
// views
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/student-subject-selection', [StudentSubjectSelectionController::class, 'index'])->name('lms.student_subject_selection.view.school_partner');

// load data form
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/student-subject-selection/load-data-form', [StudentSubjectSelectionController::class, 'loadStudentSubjectSelectionForm'])->name('lms.student_subject_selection.load_data_form');

// crud
Route::post('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/student-subject-selection/submit-form', [StudentSubjectSelectionController::class, 'studentSubjectSelectionSubmitForm'])->name('lms.student_subject_selection.submit_form');

// =========================================================
// ROUTES QUESTION BANK MANAGEMENT
// view
// question bank management no school partner & school partner
Route::get('/lms/{role}/question-bank-management', [QuestionBankController::class, 'lmsQuestionBankManagementView'])->name('lms.question_bank_management.view.no_school_partner');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/question-bank-management', [QuestionBankController::class, 'lmsQuestionBankManagementView'])->name('lms.question_bank_management.view.school_partner');

// edit question bank no school partner & school partner
Route::get('/lms/{role}/question-bank-management/kurikulum/{kurikulumId}/kelas/{kelasId}/mapel/{mapelId}/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/{questionId}/edit/{babId?}/{subBabId?}', [QuestionBankController::class, 'lmsDefaultQuestionBankManagementEditView'])->name('lms.question_bank_management_edit.view.no_school_partner');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/question-bank-management/kurikulum/{kurikulumId}/kelas/{kelasId}/mapel/{mapelId}/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/{questionId}/edit/{babId?}/{subBabId?}', [QuestionBankController::class, 'lmsSchoolQuestionBankManagementEditView'])->name('lms.question_bank_management_edit.view.school_partner');

// review question bank no school partner & school partner
Route::get('/lms/{role}/question-bank-management/kurikulum/{kurikulumId}/kelas/{kelasId}/mapel/{mapelId}/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/{babId?}/{subBabId?}', [QuestionBankController::class, 'lmsDefaultQuestionBankManagementDetailView'])->name('lms.question_bank_management_detail.view.no_school_partner');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/question-bank-management/kurikulum/{kurikulumId}/kelas/{kelasId}/mapel/{mapelId}/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/{babId?}/{subBabId?}', [QuestionBankController::class, 'lmsSchoolQuestionBankManagementDetailView'])->name('lms.question_bank_management_detail.view.school_partner');

// form question bank edit no school partner & school partner
Route::get('/lms/question-bank-management/bank-soal/form/source/{source}/review/question-type/{questionType}/{questionId}/edit/{subBabId?}', [QuestionBankController::class, 'formEditQuestion'])->name('lms.bank_soal.form.edit.question.no_school_partner');
Route::get('/lms/school-subscription/question-bank-management/bank-soal/form/source/{source}/review/question-type/{questionType}/{questionId}/{schoolName}/{schoolId}/edit/{subBabId?}', [QuestionBankController::class, 'formEditQuestion'])->name('lms.bank_soal.form.edit.question.school_partner');

// crud bank soal
// validate bank soal no school partner & school partner
Route::post('/lms/question-bank-management/validate', [QuestionBankController::class, 'lmsQuestionBankManagementValidate'])->name('lms.question_bank_management.validate.no_school_partner');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/question-bank-management/validate', [QuestionBankController::class, 'lmsQuestionBankManagementValidate'])->name('lms.question_bank_management.validate.school_partner');

// upload bank soal no school partner & school partner
Route::post('/lms/question-bank-management/store', [QuestionBankController::class, 'lmsQuestionBankManagementStore'])->name('lms.question_bank_management.store.no_school_partner');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/question-bank-management/store', [QuestionBankController::class, 'lmsQuestionBankManagementStore'])->name('lms.question_bank_management.store.school_partner');

// edit & delete image bank soal with ckeditor
Route::post('/lms/bank-soal/edit-image', [QuestionBankController::class, 'editImageBankSoal'])->name('lms.edit_image');
Route::post('/lms/bank-soal/delete-image/endpoint', [QuestionBankController::class, 'deleteImageBankSoal'])->name('lms.delete_image');

// activate question bank no school partner with sub bab & without sub bab
Route::put('/lms/question-bank-management/source/{source}/question-type/{questionType}/question-category/{questionCategory}/{subBabId}/activate', [QuestionBankController::class, 'lmsActivateQuestionBank'])->name('lms.question_bank.activate.global.with_sub_bab');
Route::put('/lms/question-bank-management/source/{source}/question-type/{questionType}/question-category/{questionCategory}/activate', [QuestionBankController::class, 'lmsActivateQuestionBankNoSubBab'])->name('lms.question_bank.activate.global.no_sub_bab');

// activate question bank school partner with sub bab & without sub bab
Route::put('/lms/school-subscription/question-bank-management/source/{source}/question-type/{questionType}/question-category/{questionCategory}/{subBabId}/{schoolName}/{schoolId}/activate', [QuestionBankController::class, 'lmsActivateQuestionBank'])->name('lms.question_bank.activate.school.with_sub_bab');
Route::put('/lms/school-subscription/question-bank-management/source/{source}/question-type/{questionType}/question-category/{questionCategory}/{schoolName}/{schoolId}/activate', [QuestionBankController::class, 'lmsActivateQuestionBankNoSubBab'])->name('lms.question_bank.activate.school.no_sub_bab');

// edit bank soal no school partner & school partner submit form
Route::post('/lms/question-bank-management/{questionId}/edit', [QuestionBankController::class, 'lmsQuestionBankManagementEdit'])->name('lms.question_bank_management.edit');

// paginate
// question bank management no school partner & school partner
Route::get('/lms/question-bank-management/paginate', [QuestionBankController::class, 'paginateLmsQuestionBankManagement'])->name('lms.question_bank_management.paginate.no_school_partner');
Route::get('/lms/school-subscription/{schoolName}/{schoolId}/question-bank-management/paginate', [QuestionBankController::class, 'paginateLmsQuestionBankManagement'])->name('lms.question_bank_management.paginate.school_partner');

// paginate review question bank no school partner & school partner

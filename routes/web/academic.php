<?php

use App\Http\Controllers\Lms\MajorSubject\MajorSubjectController;
use App\Http\Controllers\Lms\QuestionBank\QuestionBankController;
use App\Http\Controllers\Lms\StudentSubjectSelection\StudentSubjectSelectionController;
use Illuminate\Support\Facades\Route;

// ROUTES MAJOR SUBJECT MANAGEMENT
// views
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/major-subject-management', [MajorSubjectController::class, 'index'])->name('lms.majorSubjectManagement.view.schoolPartner');

// load data form
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/major-subject-management/load-data-form', [MajorSubjectController::class, 'loadMajorSubjectForm'])->name('lms.majorSubjectManagement.loadDataForm');

// crud
Route::post('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/major-subject-management/submit-form', [MajorSubjectController::class, 'majorSubjectSubmitForm'])->name('lms.majorSubjectManagement.submitForm');

// ROUTES STUDENT SUBJECT SELECTION
// views
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/student-subject-selection', [StudentSubjectSelectionController::class, 'index'])->name('lms.studentSubjectSelection.view.schoolPartner');

// load data form
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/student-subject-selection/load-data-form', [StudentSubjectSelectionController::class, 'loadStudentSubjectSelectionForm'])->name('lms.studentSubjectSelection.loadDataForm');

// crud
Route::post('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/student-subject-selection/submit-form', [StudentSubjectSelectionController::class, 'studentSubjectSelectionSubmitForm'])->name('lms.studentSubjectSelection.submitForm');

// =========================================================
// ROUTES QUESTION BANK MANAGEMENT
// view
// question bank management no school partner & school partner
Route::get('/lms/{role}/question-bank-management', [QuestionBankController::class, 'lmsQuestionBankManagementView'])->name('lms.questionBankManagement.view.noSchoolPartner');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/question-bank-management', [QuestionBankController::class, 'lmsQuestionBankManagementView'])->name('lms.questionBankManagement.view.schoolPartner');

// edit question bank no school partner & school partner
Route::get('/lms/{role}/question-bank-management/kurikulum/{kurikulumId}/kelas/{kelasId}/mapel/{mapelId}/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/{questionId}/edit/{babId?}/{subBabId?}', [QuestionBankController::class, 'lmsDefaultQuestionBankManagementEditView'])->name('lms.questionBankManagementEdit.view.noSchoolPartner');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/question-bank-management/kurikulum/{kurikulumId}/kelas/{kelasId}/mapel/{mapelId}/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/{questionId}/edit/{babId?}/{subBabId?}', [QuestionBankController::class, 'lmsSchoolQuestionBankManagementEditView'])->name('lms.questionBankManagementEdit.view.schoolPartner');

// review question bank no school partner & school partner
Route::get('/lms/{role}/question-bank-management/kurikulum/{kurikulumId}/kelas/{kelasId}/mapel/{mapelId}/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/{babId?}/{subBabId?}', [QuestionBankController::class, 'lmsDefaultQuestionBankManagementDetailView'])->name('lms.questionBankManagementDetail.view.noSchoolPartner');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/question-bank-management/kurikulum/{kurikulumId}/kelas/{kelasId}/mapel/{mapelId}/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/{babId?}/{subBabId?}', [QuestionBankController::class, 'lmsSchoolQuestionBankManagementDetailView'])->name('lms.questionBankManagementDetail.view.schoolPartner');

// form question bank edit no school partner & school partner
Route::get('/lms/question-bank-management/bank-soal/form/source/{source}/review/question-type/{questionType}/{questionId}/edit/{subBabId?}', [QuestionBankController::class, 'formEditQuestion'])->name('lms.bankSoal.form.edit.question.noSchoolPartner');
Route::get('/lms/school-subscription/question-bank-management/bank-soal/form/source/{source}/review/question-type/{questionType}/{questionId}/{schoolName}/{schoolId}/edit/{subBabId?}', [QuestionBankController::class, 'formEditQuestion'])->name('lms.bankSoal.form.edit.question.schoolPartner');

// crud bank soal
// validate bank soal no school partner & school partner
Route::post('/lms/question-bank-management/validate', [QuestionBankController::class, 'lmsQuestionBankManagementValidate'])->name('lms.questionBankManagement.validate.noSchoolPartner');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/question-bank-management/validate', [QuestionBankController::class, 'lmsQuestionBankManagementValidate'])->name('lms.questionBankManagement.validate.schoolPartner');

// upload bank soal no school partner & school partner
Route::post('/lms/question-bank-management/store', [QuestionBankController::class, 'lmsQuestionBankManagementStore'])->name('lms.questionBankManagement.store.noSchoolPartner');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/question-bank-management/store', [QuestionBankController::class, 'lmsQuestionBankManagementStore'])->name('lms.questionBankManagement.store.schoolPartner');

// edit & delete image bank soal with ckeditor
Route::post('/lms/bank-soal/edit-image', [QuestionBankController::class, 'editImageBankSoal'])->name('lms.editImage');
Route::post('/lms/bank-soal/delete-image/endpoint', [QuestionBankController::class, 'deleteImageBankSoal'])->name('lms.deleteImage');

// activate question bank no school partner with sub bab & without sub bab
Route::put('/lms/question-bank-management/source/{source}/question-type/{questionType}/question-category/{questionCategory}/{subBabId}/activate', [QuestionBankController::class, 'lmsActivateQuestionBank'])->name('lms.questionBank.activate.global.withSubBab');
Route::put('/lms/question-bank-management/source/{source}/question-type/{questionType}/question-category/{questionCategory}/activate', [QuestionBankController::class, 'lmsActivateQuestionBankNoSubBab'])->name('lms.questionBank.activate.global.noSubBab');

// activate question bank school partner with sub bab & without sub bab
Route::put('/lms/school-subscription/question-bank-management/source/{source}/question-type/{questionType}/question-category/{questionCategory}/{subBabId}/{schoolName}/{schoolId}/activate', [QuestionBankController::class, 'lmsActivateQuestionBank'])->name('lms.questionBank.activate.school.withSubBab');
Route::put('/lms/school-subscription/question-bank-management/source/{source}/question-type/{questionType}/question-category/{questionCategory}/{schoolName}/{schoolId}/activate', [QuestionBankController::class, 'lmsActivateQuestionBankNoSubBab'])->name('lms.questionBank.activate.school.noSubBab');

// edit bank soal no school partner & school partner submit form
Route::post('/lms/question-bank-management/{questionId}/edit', [QuestionBankController::class, 'lmsQuestionBankManagementEdit'])->name('lms.questionBankManagement.edit');

// paginate
// question bank management no school partner & school partner
Route::get('/lms/question-bank-management/paginate', [QuestionBankController::class, 'paginateLmsQuestionBankManagement'])->name('lms.questionBankManagement.paginate.noSchoolPartner');
Route::get('/lms/school-subscription/{schoolName}/{schoolId}/question-bank-management/paginate', [QuestionBankController::class, 'paginateLmsQuestionBankManagement'])->name('lms.questionBankManagement.paginate.schoolPartner');

// paginate review question bank no school partner & school partner

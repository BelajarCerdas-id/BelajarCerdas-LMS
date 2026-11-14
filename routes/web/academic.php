<?php

use App\Http\Controllers\Lms\MajorSubject\MajorSubjectController;
use App\Http\Controllers\Lms\QuestionBank\QuestionBankController;
use App\Http\Controllers\Lms\StudentSubjectSelection\StudentSubjectSelectionController;
use Illuminate\Support\Facades\Route;

// ROUTES MAJOR SUBJECT MANAGEMENT
// views
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/major-subject-management', [MajorSubjectController::class, 'index'])->name('Lms.MajorSubjectManagement.View.SchoolPartner');

// load data form
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/major-subject-management/load-data-form', [MajorSubjectController::class, 'loadMajorSubjectForm'])->name('Lms.MajorSubjectManagement.LoadDataForm');

// crud
Route::post('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/major-subject-management/submit-form', [MajorSubjectController::class, 'majorSubjectSubmitForm'])->name('Lms.MajorSubjectManagement.SubmitForm');

// ROUTES STUDENT SUBJECT SELECTION
// views
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/student-subject-selection', [StudentSubjectSelectionController::class, 'index'])->name('Lms.StudentSubjectSelection.View.SchoolPartner');

// load data form
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/student-subject-selection/load-data-form', [StudentSubjectSelectionController::class, 'loadStudentSubjectSelectionForm'])->name('Lms.StudentSubjectSelection.LoadDataForm');

// crud
Route::post('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/student-subject-selection/submit-form', [StudentSubjectSelectionController::class, 'studentSubjectSelectionSubmitForm'])->name('Lms.StudentSubjectSelection.SubmitForm');

// =========================================================
// ROUTES QUESTION BANK MANAGEMENT
// view
// question bank management no school partner & school partner
Route::get('/lms/{role}/question-bank-management', [QuestionBankController::class, 'lmsQuestionBankManagementView'])->name('Lms.QuestionBankManagement.View.NoSchoolPartner');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/question-bank-management', [QuestionBankController::class, 'lmsQuestionBankManagementView'])->name('Lms.QuestionBankManagement.View.SchoolPartner');

// edit question bank no school partner & school partner
Route::get('/lms/{role}/question-bank-management/kurikulum/{kurikulumId}/kelas/{kelasId}/mapel/{mapelId}/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/{questionId}/edit/{babId?}/{subBabId?}', [QuestionBankController::class, 'lmsDefaultQuestionBankManagementEditView'])->name('Lms.QuestionBankManagementEdit.View.NoSchoolPartner');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/question-bank-management/kurikulum/{kurikulumId}/kelas/{kelasId}/mapel/{mapelId}/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/{questionId}/edit/{babId?}/{subBabId?}', [QuestionBankController::class, 'lmsSchoolQuestionBankManagementEditView'])->name('Lms.QuestionBankManagementEdit.View.SchoolPartner');

// review question bank no school partner & school partner
Route::get('/lms/{role}/question-bank-management/kurikulum/{kurikulumId}/kelas/{kelasId}/mapel/{mapelId}/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/{babId?}/{subBabId?}', [QuestionBankController::class, 'lmsDefaultQuestionBankManagementDetailView'])->name('Lms.QuestionBankManagementDetail.View.NoSchoolPartner');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/question-bank-management/kurikulum/{kurikulumId}/kelas/{kelasId}/mapel/{mapelId}/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/{babId?}/{subBabId?}', [QuestionBankController::class, 'lmsSchoolQuestionBankManagementDetailView'])->name('Lms.QuestionBankManagementDetail.View.SchoolPartner');

// form question bank edit no school partner & school partner
Route::get('/lms/question-bank-management/bank-soal/form/source/{source}/review/question-type/{questionType}/{questionId}/edit/{subBabId?}', [QuestionBankController::class, 'formEditQuestion'])->name('Lms.BankSoal.Form.Edit.Question.NoSchoolPartner');
Route::get('/lms/school-subscription/question-bank-management/bank-soal/form/source/{source}/review/question-type/{questionType}/{questionId}/{schoolName}/{schoolId}/edit/{subBabId?}', [QuestionBankController::class, 'formEditQuestion'])->name('Lms.BankSoal.Form.Edit.Question.SchoolPartner');

// crud bank soal
// validate bank soal no school partner & school partner
Route::post('/lms/question-bank-management/validate', [QuestionBankController::class, 'lmsQuestionBankManagementValidate'])->name('Lms.QuestionBankManagement.Validate.NoSchoolPartner');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/question-bank-management/validate', [QuestionBankController::class, 'lmsQuestionBankManagementValidate'])->name('Lms.QuestionBankManagement.Validate.SchoolPartner');

// upload bank soal no school partner & school partner
Route::post('/lms/question-bank-management/store', [QuestionBankController::class, 'lmsQuestionBankManagementStore'])->name('Lms.QuestionBankManagement.Store.NoSchoolPartner');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/question-bank-management/store', [QuestionBankController::class, 'lmsQuestionBankManagementStore'])->name('Lms.QuestionBankManagement.Store.SchoolPartner');

// edit & delete image bank soal with ckeditor
Route::post('/lms/bank-soal/edit-image', [QuestionBankController::class, 'editImageBankSoal'])->name('Lms.EditImage');
Route::post('/lms/bank-soal/delete-image/endpoint', [QuestionBankController::class, 'deleteImageBankSoal'])->name('Lms.DeleteImage');

// activate question bank no school partner with sub bab & without sub bab
Route::put('/lms/question-bank-management/source/{source}/question-type/{questionType}/question-category/{questionCategory}/{subBabId}/activate', [QuestionBankController::class, 'lmsActivateQuestionBank'])->name('Lms.QuestionBank.Activate.Global.WithSubBab');
Route::put('/lms/question-bank-management/source/{source}/question-type/{questionType}/question-category/{questionCategory}/activate', [QuestionBankController::class, 'lmsActivateQuestionBankNoSubBab'])->name('Lms.QuestionBank.Activate.Global.NoSubBab');

// activate question bank school partner with sub bab & without sub bab
Route::put('/lms/school-subscription/question-bank-management/source/{source}/question-type/{questionType}/question-category/{questionCategory}/{subBabId}/{schoolName}/{schoolId}/activate', [QuestionBankController::class, 'lmsActivateQuestionBank'])->name('Lms.QuestionBank.Activate.School.WithSubBab');
Route::put('/lms/school-subscription/question-bank-management/source/{source}/question-type/{questionType}/question-category/{questionCategory}/{schoolName}/{schoolId}/activate', [QuestionBankController::class, 'lmsActivateQuestionBankNoSubBab'])->name('Lms.QuestionBank.Activate.School.NoSubBab');

// edit bank soal no school partner & school partner submit form
Route::post('/lms/question-bank-management/{questionId}/edit', [QuestionBankController::class, 'lmsQuestionBankManagementEdit'])->name('Lms.QuestionBankManagement.Edit');

// paginate
// question bank management no school partner & school partner
Route::get('/lms/question-bank-management/paginate', [QuestionBankController::class, 'paginateLmsQuestionBankManagement'])->name('Lms.QuestionBankManagement.Paginate.NoSchoolPartner');
Route::get('/lms/school-subscription/{schoolName}/{schoolId}/question-bank-management/paginate', [QuestionBankController::class, 'paginateLmsQuestionBankManagement'])->name('Lms.QuestionBankManagement.Paginate.SchoolPartner');

// paginate review question bank no school partner & school partner

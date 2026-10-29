<?php

use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\CheckSchoolContract;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| BelajarCerdas-LMS Modular Routing Architecture
|--------------------------------------------------------------------------
| Each domain maintains its own routes under routes/web/*.php.
| High-traffic, authenticated routes are wrapped with Auth & Contract guards.
|
*/

// 1. Public & Authentication Routes
require __DIR__.'/web/auth.php';

// 2. Authenticated LMS Domain Routes
Route::middleware([AuthMiddleware::class, CheckSchoolContract::class])->group(function () {
    require __DIR__.'/web/profile.php';
    require __DIR__.'/web/library.php';
    require __DIR__.'/web/foundation.php';
    require __DIR__.'/web/syllabus.php';
    require __DIR__.'/web/academic.php';
    require __DIR__.'/web/assessment.php';
    require __DIR__.'/web/finance.php';
    require __DIR__.'/web/student.php';
    require __DIR__.'/web/teacher.php';
    require __DIR__.'/web/headmaster.php';
    require __DIR__.'/web/student_vice_principal.php';
    require __DIR__.'/web/parent.php';
    require __DIR__.'/web/admin.php';
    require __DIR__.'/web/academic_documents.php';
});

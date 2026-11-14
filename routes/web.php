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
require __DIR__.'/web/Auth.php';

// 2. Authenticated LMS Domain Routes
Route::middleware([AuthMiddleware::class, CheckSchoolContract::class])->group(function () {
    require __DIR__.'/web/Profile.php';
    require __DIR__.'/web/Library.php';
    require __DIR__.'/web/Foundation.php';
    require __DIR__.'/web/Syllabus.php';
    require __DIR__.'/web/Academic.php';
    require __DIR__.'/web/Assessment.php';
    require __DIR__.'/web/Finance.php';
    require __DIR__.'/web/Student.php';
    require __DIR__.'/web/Teacher.php';
    require __DIR__.'/web/Headmaster.php';
    require __DIR__.'/web/StudentVicePrincipal.php';
    require __DIR__.'/web/Parent.php';
    require __DIR__.'/web/Admin.php';
    require __DIR__.'/web/AcademicDocuments.php';
});

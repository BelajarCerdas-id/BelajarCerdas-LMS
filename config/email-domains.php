<?php

/*
|--------------------------------------------------------------------------
| Allowed e-mail domains per validation context
|--------------------------------------------------------------------------
|
| Each context encodes EXACTLY the allowed set its controller used to
| hardcode inline (local part character class + accepted domains), so the
| lists can be tuned without touching validation code. local_part is a
| regex character-class body; every domain is matched literally. Do not
| tighten these lists casually: they are live account-validation rules.
|
*/

return [
    'contexts' => [
        // Login + general account creation (AuthController, TeacherSubjectController, LmsUsersHandler, LmsHandler)
        'login' => [
            'local_part' => 'A-Za-z0-9._-',
            'domains' => ['belajarcerdas.id', 'belajar.id', 'pilar.sch.id', 'gmail.com'],
        ],

        // App\Http\Controllers\Lms\Academic\ClassController — akun_wali_kelas (store + edit)
        'class_wali_kelas' => [
            'local_part' => 'A-Za-z0-9._%+-',
            'domains' => ['belajarcerdas.id'],
        ],

        // App\Http\Controllers\Administrator\FoundationManagementController — account email
        'foundation_management' => [
            'local_part' => 'A-Za-z0-9._-',
            'domains' => ['belajar.id', 'belajar.com', 'belajarcerdas.id', 'belajarcerdas.com', 'gmail.id', 'gmail.com'],
        ],

        // App\Http\Controllers\OfficeManagementController — account email (store + edit)
        'office_management' => [
            'local_part' => 'A-Za-z0-9._-',
            'domains' => ['belajar.id', 'belajar.com', 'belajarcerdas.id', 'belajarcerdas.com', 'gmail.id', 'gmail.com'],
        ],
    ],
];

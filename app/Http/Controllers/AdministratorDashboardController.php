<?php

namespace App\Http\Controllers;

class AdministratorDashboardController extends Controller
{
    public function index($role)
    {
        return view('Features.Lms.Administrator.Dashboard');     
    }
}

<?php

namespace App\Http\Controllers;

class AdministratorDashboardController extends Controller
{
    public function index($role)
    {
        return view('features.lms.administrator.dashboard');     
    }
}

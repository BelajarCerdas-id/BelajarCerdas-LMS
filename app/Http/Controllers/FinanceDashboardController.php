<?php

namespace App\Http\Controllers;

class FinanceDashboardController extends Controller
{
    public function index($role)
    {
        return view('features.lms.finance.dashboard');
    }
}

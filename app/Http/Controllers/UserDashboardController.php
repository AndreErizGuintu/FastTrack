<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserDashboardController extends Controller
{
    /**
     * Display the user dashboard.
     */
    public function dashboard()
    {
        return view('user.dashboard');
    }
}

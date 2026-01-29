<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CourierController extends Controller
{
    /**
     * Display the courier dashboard.
     */
    public function dashboard()
    {
        return view('courier.dashboard');
    }
}

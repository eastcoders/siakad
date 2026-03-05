<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Display the dashboard for the Pegawai role.
     */
    public function index()
    {
        return view('pegawai.dashboard');
    }
}

<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard untuk member.
     */
    public function index()
    {
        // Variabel untuk Breadcrumb Navbar
        $pageTitle = 'Dashboard Member';

        // Logika lain khusus Member bisa ditambahkan di sini (misalnya data langganan)

        // Tampilkan view dashboard member
        return view('member.dashboard.index', compact('pageTitle'));
    }
}

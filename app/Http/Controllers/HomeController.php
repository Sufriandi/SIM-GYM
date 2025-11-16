<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Ganti 'welcome' dengan nama view yang kamu inginkan untuk halaman depan.
        return view('welcome');
    }
}

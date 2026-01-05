<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use Illuminate\Http\Request;

class CoachController extends Controller
{
    public function index()
    {
        $coaches = Coach::all();

        return response()->json([
            'status' => 'success',
            'data'   => $coaches,
        ], 200);
    }
}

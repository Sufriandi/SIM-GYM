<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $schedule = [
            'Senin' => [
                ['time' => '18:30', 'name' => 'HIIT Cardio', 'coach' => 'Coach'],
                ['time' => '20:00', 'name' => 'Heavy Lifting', 'coach' => 'Coach'],
            ],
            'Rabu' => [
                ['time' => '19:00', 'name' => 'Power Yoga', 'coach' => 'Coach'],
            ],
            'Jumat' => [
                ['time' => '17:30', 'name' => 'Strength Basics', 'coach' => 'Coach'],
                ['time' => '19:30', 'name' => 'Conditioning', 'coach' => 'Coach'],
            ],
        ];

        return view('schedule', [
            'pageTitle' => 'Jadwal Kelas',
            'schedule' => $schedule,
        ]);
    }
}

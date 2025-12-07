<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mode Periode Absensi
    |--------------------------------------------------------------------------
    | Nilai: 'harian', 'mingguan', 'bulanan'
    | Bisa di-override dari .env via ABSENSI_MODE
    */

    'mode' => env('ABSENSI_MODE', 'harian'),

];

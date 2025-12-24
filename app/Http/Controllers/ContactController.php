<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $waNumber = env('WHATSAPP_NUMBER', '6280000000000');
        $text = rawurlencode('Halo BETA GYM, saya ingin tanya membership.');
        $whatsappUrl = "https://wa.me/{$waNumber}?text={$text}";

        return view('contact', [
            'pageTitle' => 'Kontak',
            'whatsappUrl' => $whatsappUrl,
        ]);
    }
}

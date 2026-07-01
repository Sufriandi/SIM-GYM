<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akses Member | Elite Fitness</title>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">

    m,l;l;Oswald:wght@400;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* Terapkan font-family ke tag dasar */
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Oswald', sans-serif;
            text-transform: uppercase;
        }
        body {
            font-family: 'Roboto', sans-serif;
        }
        /* CSS untuk memastikan container utama mengisi viewport */
        .full-screen-center {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>

<body class="bg-dark-background text-white font-body">
    
    {{-- Container utama yang memposisikan konten di tengah --}}
    <div class="full-screen-center p-6">
        
        {{-- Slot untuk konten utama (akan diisi oleh login.blade.php) --}}
        <main class="w-full max-w-6xl"> {{-- Ganti max-w-md menjadi max-w-6xl untuk menampung dua kolom --}}
            @yield('content') 
        </main>

    </div>
    
    

</body>
</html>
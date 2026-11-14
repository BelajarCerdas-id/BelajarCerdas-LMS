<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Halaman Tidak Ditemukan</title>
    <link rel="icon" href="{{ asset('assets/images/logo-bc/main-logo-bc.svg') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center px-4">
    <div class="text-center max-w-md">
        <img src="{{ asset('assets/images/logo-bc/main-logo-bc.svg') }}" alt="BelajarCerdas" class="h-14 mx-auto mb-6">
        <p class="text-[#0071BC] font-bold text-sm tracking-widest uppercase mb-2">Error 404</p>
        <h1 class="text-4xl font-extrabold text-slate-800 mb-3">Halaman Tidak Ditemukan</h1>
        <p class="text-slate-500 text-sm mb-8">
            Halaman atau data yang kamu cari tidak tersedia, sudah dipindahkan, atau tautannya salah.
        </p>
        <a href="{{ url('/') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#0071BC] hover:bg-blue-600 transition-colors shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
            Kembali ke Beranda
        </a>
    </div>
</body>
</html>

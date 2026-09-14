<!DOCTYPE html>
<html lang="en" data-theme="light" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        document.documentElement.setAttribute('data-theme', 'light');
        document.documentElement.classList.remove('dark');
        document.documentElement.classList.add('light');
    </script>

    <title id="title-icon-web" data-school-name="{{ Auth::check() ? (Auth::user()->StudentProfile?->SchoolPartner?->nama_sekolah ?? Auth::user()->SchoolStaffProfile?->SchoolPartner?->nama_sekolah) 
        : null }}"></title>
    <link rel="shortcut icon" type="image/svg" href="{{ asset('assets/images/favicon/favicon.svg') }}">

    <!-- Your compiled app.css (includes Tailwind, DaisyUI if configured) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Your custom BelajarCerdas.css (consider merging into app.css if possible) -->
    <link rel="stylesheet" href="{{ asset('assets/css/BelajarCerdas.css') }}">

    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Asynchronously load Font Awesome (or self-host) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    </noscript>

    <style>
        :root, html, body {
            color-scheme: light !important;
            background-color: #ffffff !important;
            min-height: 100vh;
        }
        @media (prefers-color-scheme: dark) {
            :root, html, body {
                color-scheme: light !important;
                background-color: #ffffff !important;
                color: #1e293b !important;
            }
        }

        /* Global Select Dropdown Chevron Styling */
        select:not([multiple]):not([size]):not(.appearance-none):not(.no-custom-chevron),
        select:not([multiple])[size="1"]:not(.appearance-none):not(.no-custom-chevron) {
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            appearance: none !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.75' d='m6 8 4 4 4-4'/%3E%3C/svg%3E") !important;
            background-position: right 1rem center !important;
            background-repeat: no-repeat !important;
            background-size: 1.15rem 1.15rem !important;
            padding-right: 2.75rem !important;
        }
        select:not([multiple]):not([size]):not(.appearance-none):not(.no-custom-chevron):focus,
        select:not([multiple])[size="1"]:not(.appearance-none):not(.no-custom-chevron):focus {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%230071BC' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m6 8 4 4 4-4'/%3E%3C/svg%3E") !important;
        }
        select:not([multiple]):not([size]):not(.appearance-none):not(.no-custom-chevron):disabled,
        select:not([multiple])[size="1"]:not(.appearance-none):not(.no-custom-chevron):disabled {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3E%3C/svg%3E") !important;
            cursor: not-allowed;
        }
        select.no-custom-chevron,
        .has-custom-chevron select,
        .no-custom-chevron select {
            background-image: none !important;
        }
    </style>
</head>
<body>
    
</body>
</html>

<script>
    const titleIconWeb = document.getElementById('title-icon-web');
    const schoolPartnerName = titleIconWeb.getAttribute('data-school-name');

    if (schoolPartnerName) {
        document.title = `LMS - ${schoolPartnerName}`
    } else {
        document.title = 'LMS - BelajarCerdas'
    }
</script>
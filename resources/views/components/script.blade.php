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
        try {
            var savedSidebarWidth = localStorage.getItem('bc_sidebar_width');
            if (savedSidebarWidth) {
                document.documentElement.style.setProperty('--sidebar-width', savedSidebarWidth + 'px');
            }
        } catch (e) {}
    </script>

    <title id="title-icon-web" data-school-name="{{ Auth::check() ? (Auth::user()->StudentProfile?->SchoolPartner?->nama_sekolah ?? Auth::user()->SchoolStaffProfile?->SchoolPartner?->nama_sekolah) 
        : null }}"></title>
    <link rel="shortcut icon" type="image/svg" href="{{ asset('assets/images/favicon/favicon.svg') }}">

    <!-- Your compiled app.css (includes Tailwind, DaisyUI if configured) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Your custom BelajarCerdas.css (consider merging into app.css if possible) -->
    <link rel="stylesheet" href="{{ asset('assets/css/BelajarCerdas.css') }}?v={{ file_exists(public_path('assets/css/BelajarCerdas.css')) ? filemtime(public_path('assets/css/BelajarCerdas.css')) : time() }}">

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
        select:not([multiple])[size="1"]:not(.appearance-none):not(.no-custom-chevron),
        #dropdown-tahun-ajaran,
        #dropdown-semester,
        #dropdown-filter-class,
        #dropdown-school-class,
        #dropdown-filter-tahun-ajaran,
        #dropdown-filter-mapel,
        #semester {
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
        select:not([multiple])[size="1"]:not(.appearance-none):not(.no-custom-chevron):focus,
        #dropdown-tahun-ajaran:focus,
        #dropdown-semester:focus,
        #dropdown-filter-class:focus,
        #dropdown-school-class:focus,
        #dropdown-filter-tahun-ajaran:focus,
        #dropdown-filter-mapel:focus,
        #semester:focus {
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

        /* Login Page Styling (All Screens & Responsive Constraints) */
        .login-box {
            width: 100% !important;
            max-width: 420px !important;
            margin-left: auto !important;
            margin-right: auto !important;
            box-sizing: border-box !important;
        }

        .login-logo {
            display: block !important;
            margin-left: auto !important;
            margin-right: auto !important;
            object-fit: contain !important;
            height: 3.75rem !important;
            max-height: 64px !important;
        }

        .toggle-password-btn {
            position: absolute !important;
            right: 12px !important;
            top: 0 !important;
            bottom: 0 !important;
            margin-top: auto !important;
            margin-bottom: auto !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 24px !important;
            height: 24px !important;
            padding: 0 !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            border: none !important;
            background: transparent !important;
            color: #94a3b8 !important;
            cursor: pointer !important;
            z-index: 10 !important;
            outline: none !important;
            transform: none !important;
            translate: none !important;
            transition: color 0.15s ease-in-out !important;
        }

        .toggle-password-btn:hover {
            color: #475569 !important;
        }

        /* 1366x768 & Compact Laptop Screen Optimizations for Login */
        @media screen and (max-width: 1400px) and (max-height: 850px),
               screen and (max-width: 1366px) and (min-width: 1024px),
               screen and (max-height: 768px) {
            .login-box {
                max-width: 380px !important;
                padding: 1.5rem !important;
            }

            .login-logo {
                height: 3.25rem !important;
                max-height: 52px !important;
                margin-bottom: 0.875rem !important;
            }
        }

        @media screen and (max-height: 700px) {
            .login-box {
                max-width: 350px !important;
                padding: 1.25rem !important;
            }

            .login-logo {
                height: 2.75rem !important;
                max-height: 44px !important;
                margin-bottom: 0.625rem !important;
            }
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
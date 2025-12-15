{{-- resources/views/layouts/andon.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Shipping System') - STEP</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Google Fonts - Montserrat -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="icon" type="image/png" href="{{ asset('images/logostep.png') }}">

    <style>
        /* Base styles */
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #000000;
            color: #ffffff;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Header Navigation */
        .navbar {
            background-color: #000000;
            border: 3px solid #ffffff;
            padding: 0;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
        }

        /* Header Table Structure */
        .table-container {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .table-row {
            display: table-row;
        }

        .table-cell {
            display: table-cell;
            vertical-align: middle;
            padding: 10px;
            text-align: center;
        }

        .table-cell:not(:last-child) {
            border-right: 3px solid white;
        }

        .logo-cell {
            width: 150px;
        }

        .title-cell {
            width: auto;
        }

        .user-cell {
            width: 200px;
        }

        .logo {
            display: block;
            margin: 0 auto;
            width: 120px;
            height: auto;
            cursor: pointer;
        }

        .page-title {
            font-weight: bold;
            font-size: 55px;
            text-align: center;
            margin: 0;
            color: #ffffff;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: white;
            font-size: 14px;
        }

        .user-name {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 5px;
        }

        .user-role {
            font-size: 12px;
            padding: 2px 10px;
            border-radius: 12px;
            background: #fbbf24;
            color: #000;
            font-weight: 600;
        }

        /* Main Content */
        .main-content {
            padding: 0rem;
            min-height: calc(100vh - 120px);
            background-color: #000000;
        }

        body.preparation-page {
            margin: 0 !important;
            padding: 0 !important;
        }

        body.preparation-page .main-content {
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        body.preparation-page .container,
        body.preparation-page .container-fluid {
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
            width: 100% !important;
        }

        body.preparation-page .table-responsive {
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
            
        }

        /* ========================================
           Compact Table Styling untuk Preparation
           ======================================== */
        .table-compact {
            border-collapse: collapse !important;
            background-color: #000000;
            color: #ffffff;
            border: 2px solid #ffffff;
        }

        .table-compact thead th {
            font-weight: 700;
            white-space: nowrap;
            text-align: center;
            vertical-align: middle;
            background-color: #1a1a1a;
            color: #ffffff;
            border: 1px solid #ffffff !important;
            letter-spacing: 1px;
        }

        .table-compact tbody td {
            padding: 2px 0px !important;
            white-space: nowrap;
            text-align: center;
            vertical-align: middle;
            background-color: #0a0a0a;
            color: #ffffff;
            border: 1px solid #ffffff !important;
            font-weight: 500;
        }

        .table-compact tbody tr:nth-child(even) td {
            background-color: #0f0f0f;
        }

        .table-compact tbody tr:hover td {
            background-color: #505050;
            transition: background 0.2s ease;
        }

        .table-compact tbody td strong {
            font-weight: 700;
        }

        /* Pagination Wrapper untuk Preparation */
        .pagination-wrapper {
            margin: 0 !important;
            padding: 15px !important;
            background-color: #000000;
            padding-bottom: 80px !important;
        }

        /* Prevent horizontal scrollbar dengan word wrap */
        .table-responsive {
            overflow-x: auto;
        }

        #preparationsTable {
            width: 100%;
        }

        #preparationsTable th,
        #preparationsTable td {
            white-space: normal;
            word-wrap: break-word;
            word-break: break-word;
            vertical-align: middle;
        }

        /* Kolom actions tetap nowrap biar button ga kebawah */
        #preparationsTable th:nth-child(12),
        #preparationsTable td:nth-child(12) {
            white-space: nowrap;
        }
        
        .badge-delay {
            animation: hard-blink 0.8s infinite;
        }

        @keyframes hard-blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0.3; }
        }

        /* Menu styles */
        .menu-container {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-top: -1px;
        }

        .fixed-running-text {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            z-index: 9999;
            padding: 8px 0;
            box-shadow: 0 -3px 5px rgba(0,0,0,0.2);
        }

        .running-text-container {
            overflow: hidden;
            white-space: nowrap;
        }

        .running-text-inner {
            display: inline-block;
            padding-left: 100%;
        }

        .speed-normal {
            animation: scroll-left 15s linear infinite;
        }
        .speed-fast {
            animation: scroll-left 8s linear infinite;
        }
        .speed-slow {
            animation: scroll-left 25s linear infinite;
        }

        @keyframes scroll-left {
            from { transform: translateX(0); }
            to { transform: translateX(-100%); }
        }

        @keyframes blink-animation {
            0%, 49% { opacity: 1; }
            50%, 99% { opacity: 0.2; }
            100% { opacity: 1; }
        }
        
        
        .blink-badge {
            animation: blink-animation 1s steps(1) infinite;
        }
    </style>

    @stack('styles')
</head>

<body class="@yield('body-class')">
    <!-- Header Navigation -->
    <nav class="navbar navbar-dark">
        <div class="table-container">
            <div class="table-row">
                <!-- Logo -->
                <div class="table-cell logo-cell">
                    {{-- <a href="{{ route('dashboard') }}"> --}}
                        <img src="{{ asset('images/logostep.png') }}" alt="Logo" class="logo">
                    </a>
                </div>
                <!-- Title -->
                <div class="table-cell title-cell">
                    <span class="page-title">
                        @yield('page-title', 'SHIPPING SYSTEM V2')
                    </span>
                </div>
                <!-- User Info -->
                <div class="table-cell user-cell">
                    <div id="clock" style="margin-top: 1px; font-size: 20px; font-weight: 600; color: #ffffff;">
                        <div style="font-size: 20px; margin-top: 2px;">00/00/0000</div>
                        <div style="font-size: 16px;">00:00:00</div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>

    

    @php
        $runningText = \App\Models\RunningText::getActive();
    @endphp

    @if($runningText && $runningText->is_active)
        <div class="running-text-wrapper fixed-running-text fw-bold"
            style="background-color: {{ $runningText->background_color ?? '#1a1a1a' }};">
            <div class="running-text-container">
                <div class="running-text-track">
                    <div class="running-text-inner speed-{{ $runningText->speed ?? 'normal' }}">
                        <span style="color: {{ $runningText->text_color ?? '#fbbf24' }};">
                            {{ $runningText->content }}
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        </span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Include Running Text Modal (hanya untuk superadmin) -->
    @if(auth()->check() && auth()->user()->role === 'superadmin')
        @include('runningtext.index')
    @endif

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <!-- Clock Script -->
    <script>
        function updateClock() {
            const now = new Date();
            
            // Format waktu: HH:MM:SS
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const timeString = `${hours}:${minutes}:${seconds}`;
            
            // Format tanggal: DD/MM/YYYY
            const day = String(now.getDate()).padStart(2, '0');
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const year = now.getFullYear();
            const dateString = `${day}/${month}/${year}`;
            
            // Update DOM
            const clockDiv = document.getElementById('clock');
            if (clockDiv) {
                clockDiv.innerHTML = `
                    <div style="font-size: 25px; font-weight: bold; margin-top: 2px;">${dateString}</div>
                    <div style="font-size: 25px; font-weight: bold;">${timeString}</div>
                `;
            }
        }
        
        // Update setiap detik
        setInterval(updateClock, 1000);
        
        // Jalankan pertama kali
        updateClock();
    </script>

    <!-- SweetAlert Session Handler -->
    <script>
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                confirmButtonColor: '#10b981',
                timer: 3000,
                timerProgressBar: true
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '{{ session('error') }}',
                confirmButtonColor: '#dc2626'
            });
        @endif

        function confirmLogout(event) {
            event.preventDefault();
            
            Swal.fire({
                title: 'Konfirmasi Logout',
                text: 'Apakah Anda yakin ingin keluar?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Logout',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            });
        }

        const btn = document.getElementById('settingsBtn');
        const submenu = document.getElementById('submenu');

        if (btn && submenu) {
            btn.addEventListener('click', function (e) {
                e.preventDefault(); 
                submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';
            });

            // Tutup jika klik di luar menu
            document.addEventListener('click', function(e) {
                const settingsMenu = document.getElementById('settingsMenu');
                if (settingsMenu && !settingsMenu.contains(e.target)) {
                    submenu.style.display = 'none';
                }
            });

            // Prevent submenu links from closing immediately
            submenu.addEventListener('click', function(e) {
                if (e.target.tagName === 'A' && !e.target.hasAttribute('data-bs-toggle')) {
                    // Allow navigation for normal links
                } else {
                    e.stopPropagation();
                }
            });
        }
    </script>
    @include('partials.ad-player')
    @stack('scripts')
</body>
</html>
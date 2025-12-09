{{-- resources/views/layouts/app.blade.php --}}
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

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

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
        }

        /* Custom CSS untuk nested dropdown */
        .dropdown-menu .dropend .dropdown-toggle::after {
            display: inline-block;
            margin-left: 0.5em;
            vertical-align: 0.255em;
            content: "";
            border-top: 0.3em solid transparent;
            border-right: 0;
            border-bottom: 0.3em solid transparent;
            border-left: 0.3em solid;
        }

        .dropdown-menu .dropend > .dropdown-menu {
            top: 0;
            left: 100%;
            margin-top: -1px;
        }

        .dropend:hover > .dropdown-menu {
            display: block;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
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

        .menu-item {
            display: table-cell;
            text-align: center;
            padding: 10px;
            border-top: 3px solid #fff;
            border-left: 3px solid #fff;
            background-color: #000000;
            transition: background-color 0.3s;
            position: relative;
        }
        
        .menu-item:first-child {
            border-left: none;
        }

        .menu-item:hover {
            background-color: #555;
        }

        .menu-item a {
            color: #fff;
            font-weight: 500;
            font-size: 18px;
            text-decoration: none;
        }

        .menu-item a:hover {
            color: #dfe6e9;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: #fff;
            border: 1px solid #ddd;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            padding: 0.5rem 1rem;
            min-width: 150px;
        }

        .dropdown-menu a {
            display: block;
            padding: 0.5rem 0;
            text-decoration: none;
            color: #333;
        }

        .dropdown-menu a:hover {
            background-color: #f1f1f1;
        }

        .menu-item:hover .dropdown-menu {
            display: block;
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
                    <a href="{{ route('dashboard') }}">
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

        <!-- Menu below Navbar -->
            <div class="menu-container">
                <div class="menu-item">
                    <a href="{{ route('preparations.index') }}">Preparation</a>
                </div>
                <div class="menu-item">
                    <a href="{{ route('shippings.index') }}">Shipping</a>
                </div>
                <div class="menu-item">
                    <a href="#">Delivery</a>
                </div>
                <div class="menu-item">
                    <a href="#">Receipt DN</a>
                </div>
                <div class="menu-item">
                    <a href="#">Kanban</a>
                </div>
                <div class="menu-item">
                    <a href="#">Settings</a>
                </div>
                <div class="menu-item">
                    <a href="#" onclick="confirmLogout(event);">Logout</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>

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
    </script>

    @stack('scripts')
</body>
</html>
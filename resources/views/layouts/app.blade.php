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
            font-size: 48px;
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
            padding: 2px 20px !important;
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
            background-color: #1a1a1a;
            transition: background 0.2s ease;
        }

        .table-compact tbody td strong {
            font-weight: 700;
        }

        .action-buttons-compact {
            display: flex;
            gap: 4px;
            justify-content: center;
            align-items: center;
        }

        /* Pagination Wrapper untuk Preparation */
        .pagination-wrapper {
            margin: 0 !important;
            padding: 15px !important;
            background-color: #000000;
        }

        @keyframes blink-hard {
            0%, 50%, 100% { opacity: 1; }
            25%, 75% { opacity: 0; }
        }

        
        
        /* Pulse animation untuk status delay - lebih cepat */
        @keyframes pulse-red {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
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
    </script>

    @stack('scripts')
</body>
</html>
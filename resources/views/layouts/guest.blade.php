<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        
        <!-- Bootstrap 5 -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- SweetAlert2 CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

        <!-- SweetAlert2 JS -->
        
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .system-expiry-badge {
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: rgba(0, 0, 0, 0.7);
                backdrop-filter: blur(10px);
                color: white;
                padding: 12px 20px;
                border-radius: 8px;
                font-size: 0.875rem;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
                z-index: 1000;
                cursor: pointer;
                transition: all 0.3s ease;
            }

            .system-expiry-badge:hover {
                background: rgba(0, 0, 0, 0.85);
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(0, 0, 0, 0.4);
            }

            .system-expiry-badge.expired {
                background: rgba(220, 53, 69, 0.9);
                animation: pulse 2s infinite;
            }

            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.7; }
            }

            .expiry-label {
                font-size: 0.75rem;
                opacity: 0.8;
                margin-bottom: 4px;
            }

            .expiry-date {
                font-weight: 600;
                font-size: 1rem;
            }
        </style>
    </head>
    <body 
        class="font-sans text-gray-900 antialiased"
        style="background-image: url('{{ asset('images/bglogin.png') }}'); background-size: cover; background-position: center;"
    >
        <div class="min-h-screen d-flex justify-content-center align-items-center p-3">
            {{ $slot }}
        </div>

        <!-- System Expiry Badge -->
        <div class="system-expiry-badge" id="systemExpiryBadge" onclick="showActivationModal()">
            <div class="expiry-label">Masa Berlaku Sistem</div>
            <div class="expiry-date" id="systemExpiryDate">Loading...</div>
        </div>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        
        <script>
            // Check system expiry on page load
            document.addEventListener('DOMContentLoaded', function() {
                checkSystemExpiry();
            });

            async function checkSystemExpiry() {
                try {
                    const response = await fetch('/api/token/expiry');
                    const expiryDate = await response.text();
                    
                    document.getElementById('systemExpiryDate').textContent = expiryDate;

                    // Check if expired
                    const expiredResponse = await fetch('/api/token/is-expired');
                    const isExpired = await expiredResponse.json();

                    const badge = document.getElementById('systemExpiryBadge');
                    if (isExpired) {
                        badge.classList.add('expired');
                        badge.title = 'Klik untuk aktivasi';
                    } else {
                        badge.classList.remove('expired');
                        badge.title = 'Sistem aktif';
                    }

                } catch (error) {
                    console.error('Error checking system expiry:', error);
                    document.getElementById('systemExpiryDate').textContent = 'Error';
                }
            }

            // Function untuk show modal aktivasi (akan dipanggil dari login.blade.php)
            function showActivationModal() {
                // Trigger modal jika ada
                if (typeof window.showActivationModalFromLogin === 'function') {
                    window.showActivationModalFromLogin();
                }
            }
        </script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </body>
</html>
@extends('layouts.app')

@section('title', 'Checking LP')
@section('page-title', 'CHECKING LP')
@section('body-class', 'preparation-page')

@push('styles')
<style>
    .scan-container {
        background: #ffffff;
        border-radius: 10px;
        padding: 20px 30px;
        max-width: 600px;
        margin: 30px auto;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .scan-title {
        font-size: 1.5rem;
        font-weight: 700;
        text-align: center;
        color: #333;
        margin-bottom: 20px;
        margin-left: 40px;
    }

    .scan-input-group {
        display: flex;
        gap: 10px;
    }

    .scan-input {
        flex: 1;
        padding: 12px 15px;
        font-size: 1rem;
        border: 2px solid #ddd;
        border-radius: 8px;
        transition: border-color 0.3s;
    }

    .scan-input:focus {
        outline: none;
        border-color: #0d6efd;
    }

    .scan-btn {
        padding: 12px 25px;
        font-size: 1rem;
        font-weight: 600;
        border-radius: 8px;
    }

    .scan-hint {
        text-align: center;
        color: #666;
        font-size: 0.9rem;
        margin-top: 15px;
    }

    .route-list-title {
        color: #aaa;
        font-size: 0.9rem;
        margin-bottom: 15px;
        margin-top: 30px;
    }

    .shipping-table {
        width: 100%;
        border-collapse: collapse;
        background: #000;
        color: #fff;
    }

    .shipping-table th {
        background: #000000;
        padding: 5px 10px;
        text-align: center;
        font-weight: 700;
        border: 2px solid #ffffff;
        
    }

    .shipping-table td {
        padding: 5px 10px;
        text-align: center;
        border: 2px solid #ffffff;
        
    }

    .shipping-table tbody tr:nth-child(even) {
        background: #0a0a0a;
    }

    .shipping-table tbody tr:hover {
        background: #000000;
    }

    .arrival-pending {
        color: #ffc107;
        font-weight: 600;
    }

    .arrival-done {
        color: #28a745;
        font-weight: 600;
    }

    .btn-logout {
        background: #f8f9fa;
        color: #333;
        border: 1px solid #ddd;
        padding: 8px 20px;
        border-radius: 5px;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.3s;
    }

    .btn-logout:hover {
        background: #e9ecef;
    }

    .header-box {
        display: flex;
        
        align-items: center;
        padding: 10px 0;
    }

    .footer-text {
        text-align: center;
        color: #666;
        font-size: 0.85rem;
        margin-top: 30px;
        padding-bottom: 20px;
    }

    /* Status badges */
    .status-advance { background: #17a2b8 !important; }
    .status-normal { background: #28a745 !important; }
    .status-delay { background: #dc3545 !important; }
    .status-on_loading { background: #0d6efd !important; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="scan-container">
        <div class="header-box d-flex align-items-center justify-content-between">
            <!-- Tombol Logout -->
            <button type="button" class="btn btn-danger btn-sm fw-bold mb-4"
                    onclick="confirmLogout(event)">
                Logout
            </button>


            <span class="scan-title">SCAN ROUTE LP</span>

            <div style="width: 100px;"></div>
        </div>

        <div class="scan-input-group text-dark fw-bold">
            <input type="text" id="routeInput" class="scan-input" placeholder="Masukkan Route (misal: RC12)" autofocus>
            <button type="button" id="scanButton" class="btn btn-dark scan-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 
                    1.415-1.414l-3.85-3.85zm-5.242 1.156a5 5 0 1 1 0-10 5 5 0 0 1 0 10z"/>
                </svg>
            </button>
        </div>

            <p class="scan-hint">Scan route untuk mengisi data arrival pada shipping</p>
        </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>

    <!-- Available Routes Info -->
    <p class="route-list-title ms-3 text-white">Data Route & Cycle yang Tersedia:</p>

    <!-- Shipping Table -->
    <div class="table-responsive">
        <table class="shipping-table" id="checkingTable">
            <thead>
                <tr class="fs-5">
                    <th>Route</th>
                    <th>LP</th>
                    <th>Cycle</th>
                    <th>Dock</th>
                    <th>Customer</th>
                    <th>Delv Date</th>
                    <th>Delv Time</th>
                    <th>Arrival</th>
                </tr>
            </thead>
            <tbody class="fs-5">
                @forelse($shippings as $ship)
                    <tr data-route="{{ $ship->route }}">
                        <td><strong>{{ $ship->route }}</strong></td>
                        <td>{{ $ship->logistic_partners }}</td>
                        <td><strong>{{ $ship->cycle }}</strong></td>
                        <td>{{ $ship->dock }}</td>
                        <td>{{ $ship->customers }}</td>
                        <td>{{ $ship->delivery_date->format('d-m-y') }}</td>
                        <td>{{ date('H:i:s', strtotime($ship->delivery_time)) }}</td>
                        <td>
                            @if($ship->arrival)
                                <span class="arrival-done">{{ $ship->arrival->format('d-m-y H:i') }}</span>
                            @else
                                <span class="arrival-pending">Belum Scan</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <span class="text-muted">Tidak ada data shipping yang perlu di-scan</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <p class="footer-text">&copy; {{ date('Y') }} STEP. All rights reserved.</p>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Focus on input
    $('#routeInput').focus();

    // Handle scan button click
    $('#scanButton').on('click', function() {
        checkRoute();
    });

    // Handle enter key
    $('#routeInput').on('keypress', function(e) {
        if (e.which === 13) {
            checkRoute();
        }
    });

    // Step 1: Check route untuk lihat ada berapa cycle
    function checkRoute() {
        const route = $('#routeInput').val().trim().toUpperCase();
        
        if (!route) {
            Swal.fire({
                title: 'Error!',
                text: 'Masukkan route terlebih dahulu',
                icon: 'error',
                confirmButtonColor: '#dc2626'
            });
            $('#routeInput').focus();
            return;
        }

        // Show loading
        Swal.fire({
            title: 'Memeriksa...',
            text: 'Checking route ' + route,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Check route first
        $.ajax({
            url: '{{ route("shippings.checkRoute") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                route: route
            },
            success: function(response) {
                Swal.close();
                
                if (response.has_multiple_cycles) {
                    // Show cycle selection dialog
                    showCycleSelection(route, response.cycles);
                } else {
                    // Langsung scan tanpa pilih cycle
                    processScan(route, null);
                }
            },
            error: function(xhr) {
                let errorMessage = xhr.responseJSON?.message || 'Terjadi kesalahan saat check route';
                
                Swal.fire({
                    title: 'Tidak Ditemukan!',
                    text: errorMessage,
                    icon: 'warning',
                    confirmButtonColor: '#f59e0b'
                }).then(() => {
                    $('#routeInput').val('').focus();
                });
            }
        });
    }

    // Step 2: Show cycle selection jika ada multiple cycles
    function showCycleSelection(route, cycles) {
        let cycleButtons = '';
        
        cycles.forEach(function(cycle) {
            cycleButtons += `<button type="button" class="btn btn-outline-primary btn-lg m-1 cycle-select-btn" data-cycle="${cycle}" style="min-width: 80px;">Cycle ${cycle}</button>`;
        });
        
        // Tambahkan opsi "Scan Semua"
        cycleButtons += `<button type="button" class="btn btn-success btn-lg m-1 cycle-select-btn" data-cycle="all" style="min-width: 120px;"><i class="bi bi-check-all me-1"></i>Scan Semua</button>`;

        Swal.fire({
            title: `Route: ${route}`,
            html: `
                <p class="text-muted mb-3">Pilih Cycle yang akan di-scan:</p>
                <div class="d-flex flex-wrap justify-content-center">
                    ${cycleButtons}
                </div>
            `,
            showConfirmButton: false,
            showCancelButton: true,
            cancelButtonText: 'Batal',
            cancelButtonColor: '#6c757d',
            width: 500,
            didOpen: () => {
                // Handle cycle button click
                document.querySelectorAll('.cycle-select-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const cycle = this.getAttribute('data-cycle');
                        Swal.close();
                        
                        if (cycle === 'all') {
                            processScan(route, null); // null = scan semua cycle
                        } else {
                            processScan(route, parseInt(cycle));
                        }
                    });
                });
            }
        });
    }

    // Step 3: Process scan
    function processScan(route, cycle) {
        // Show loading
        const cycleText = cycle ? ` Cycle ${cycle}` : ' (Semua Cycle)';
        Swal.fire({
            title: 'Memproses...',
            text: `Scanning route ${route}${cycleText}`,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Prepare data
        let postData = {
            _token: '{{ csrf_token() }}',
            route: route
        };
        
        if (cycle !== null) {
            postData.cycle = cycle;
        }

        // Send scan request
        $.ajax({
            url: '{{ route("shippings.scanRoute") }}',
            type: 'POST',
            data: postData,
            success: function(response) {
                Swal.fire({
                    title: 'Berhasil!',
                    html: `
                        <div class="text-center">
                            <p>${response.message}</p>
                            <p class="fw-bold text-success fs-5">Arrival: ${response.arrival_time}</p>
                        </div>
                    `,
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 1500,
                }).then(() => {
                    // Clear input and reload
                    $('#routeInput').val('');
                    window.location.reload();
                });
            },
            error: function(xhr) {
                let errorMessage = xhr.responseJSON?.message || 'Terjadi kesalahan saat scan route';
                
                Swal.fire({
                    title: 'Gagal!',
                    text: errorMessage,
                    icon: 'error',
                    showConfirmButton: false,
                    timer: 1500,
                }).then(() => {
                    $('#routeInput').val('').focus();
                });
            }
        });
    }

    // Highlight rows when typing route
    $('#routeInput').on('input', function() {
        const route = $(this).val().trim().toUpperCase();
        
        // Reset all highlights
        $('#checkingTable tbody tr').css('background', '');
        
        if (route) {
            // Highlight matching routes
            $('#checkingTable tbody tr').each(function() {
                const rowRoute = $(this).data('route');
                if (rowRoute && rowRoute.toUpperCase().includes(route)) {
                    $(this).css('background', '#1a3a1a');
                }
            });
        }
    });
});
</script>
@endpush
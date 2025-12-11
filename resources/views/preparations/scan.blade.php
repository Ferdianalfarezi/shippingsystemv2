@extends('layouts.mobile')

@section('title', 'Scan to Shipping')
@section('page-title', 'SCAN TO SHIPPING')
@section('body-class', 'scan-mobile-page')

@section('content')
<div class="container-fluid px-3 py-3">
    
    <!-- Scan Input Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="text-center mb-3">
                <i class="bi bi-qr-code-scan text-dark" style="font-size: 3rem;"></i>
                <h4 class="mt-2  fw-bold">Scan DN Barcode</h4>
                <small class="text-muted">Scan atau ketik nomor DN untuk memindahkan data ke Shipping</small>
            </div>
            
            <div class="input-group input-group-lg">
                <span class="input-group-text bg-dark text-white">
                    <i class="bi bi-upc-scan"></i>
                </span>
                <input type="text" 
                       class="form-control form-control-lg text-center fw-bold" 
                       id="scanDnInput" 
                       placeholder="Scan DN disini..." 
                       autocomplete="off"
                       autofocus
                       style="font-size: 1.5rem; letter-spacing: 1px;">
            </div>
            
            <!-- Status Indicator -->
            <div id="scanStatus" class="text-center mt-3 d-none">
                <div class="spinner-border spinner-border-sm text-dark me-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span class="text-muted">Mencari DN...</span>
            </div>
        </div>
    </div>


    <!-- Recent Scans -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <i class="bi bi-clock-history me-2"></i>
                    <strong>Data scan terakhir</strong>
                </div>
                <span class="badge bg-light text-dark">{{ $recentScans->count() }} terakhir</span>
            </div>
        </div>
        <div class="card-body p-0">
            @forelse($recentScans as $scan)
                <div class="border-bottom p-3 {{ $loop->first ? 'bg-light' : '' }}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="mb-1 fw-bold text-dark">{{ $scan->no_dn }}</h5>
                            <div class="text-muted small">
                                <span class="me-2"><i class="bi bi-signpost-split me-1"></i>{{ $scan->route }}</span>
                                <span class="me-2"><i class="bi bi-building me-1"></i>{{ $scan->dock }}</span>
                                <span><i class="bi bi-arrow-repeat me-1"></i>Cycle {{ $scan->cycle }}</span>
                            </div>
                            <div class="text-muted small mt-1">
                                <i class="bi bi-geo-alt me-1"></i>{{ $scan->address }}
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">{{ $scan->scan_to_shipping->format('H:i') }}</div>
                            <small class="text-muted">{{ $scan->scan_to_shipping->format('d/m/Y') }}</small>
                            <div class="small text-muted mt-1">
                                <i class="bi bi-person-fill"></i> {{ $scan->moved_by ?? 'System' }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                    <p class="mt-2 mb-0">Belum ada scan hari ini</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Logout Button -->
    <div class="mt-4 mb-3">
        <form id="logoutForm" action="{{ route('logout') }}" method="POST">
            @csrf
        </form>
        <button type="button" class="btn btn-danger w-100 py-3" id="btnLogout">
            <i class="bi bi-box-arrow-right me-2"></i>
            <strong>Logout</strong>
        </button>
    </div>

</div>

<!-- Address Selection Modal (Full Screen for Mobile) -->
<div class="modal fade" id="addressModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">
                    <i class="bi bi-geo-alt-fill me-2"></i>Pilih Lokasi Shipping
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- DN Info -->
                <div class="alert alert-light border text-center mb-4">
                    <small class="text-muted d-block">No DN</small>
                    <h4 class="mb-1 fw-bold text-dark" id="modalDnNumber">-</h4>
                    <small id="modalDnInfo" class="text-muted">-</small>
                </div>
                
                <!-- Address Buttons -->
                <div class="row g-2 mb-3">
                    <div class="col-4"><button type="button" class="btn btn-outline-dark w-100 py-3 address-btn" data-address="Shipping 1"><strong>1</strong></button></div>
                    <div class="col-4"><button type="button" class="btn btn-outline-dark w-100 py-3 address-btn" data-address="Shipping 2"><strong>2</strong></button></div>
                    <div class="col-4"><button type="button" class="btn btn-outline-dark w-100 py-3 address-btn" data-address="Shipping 3"><strong>3</strong></button></div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-4"><button type="button" class="btn btn-outline-dark w-100 py-3 address-btn" data-address="Shipping 4"><strong>4</strong></button></div>
                    <div class="col-4"><button type="button" class="btn btn-outline-dark w-100 py-3 address-btn" data-address="Shipping 5"><strong>5</strong></button></div>
                    <div class="col-4"><button type="button" class="btn btn-outline-dark w-100 py-3 address-btn" data-address="Shipping 6"><strong>6</strong></button></div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-4"><button type="button" class="btn btn-outline-dark w-100 py-3 address-btn" data-address="Shipping 7"><strong>7</strong></button></div>
                    <div class="col-4"><button type="button" class="btn btn-outline-dark w-100 py-3 address-btn" data-address="Shipping 8"><strong>8</strong></button></div>
                    <div class="col-4"><button type="button" class="btn btn-outline-dark w-100 py-3 address-btn" data-address="Shipping 9"><strong>9</strong></button></div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-12"><button type="button" class="btn btn-outline-dark w-100 py-3 address-btn" data-address="Shipping 10"><strong>10</strong></button></div>
                </div>
                
                <hr>
                <p class="text-center text-muted small mb-2">Export Area</p>
                
                <div class="row g-2">
                    <div class="col"><button type="button" class="btn btn-outline-secondary w-100 py-3 address-btn" data-address="Shipping Ex 1"><strong>Ex 1</strong></button></div>
                    <div class="col"><button type="button" class="btn btn-outline-secondary w-100 py-3 address-btn" data-address="Shipping Ex 2"><strong>Ex 2</strong></button></div>
                    <div class="col"><button type="button" class="btn btn-outline-secondary w-100 py-3 address-btn" data-address="Shipping Ex 3"><strong>Ex 3</strong></button></div>
                </div>
                <div class="row g-2 mt-1">
                    <div class="col"><button type="button" class="btn btn-outline-secondary w-100 py-3 address-btn" data-address="Shipping Ex 4"><strong>Ex 4</strong></button></div>
                    <div class="col"><button type="button" class="btn btn-outline-secondary w-100 py-3 address-btn" data-address="Shipping Ex 5"><strong>Ex 5</strong></button></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary w-100 py-2" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-2"></i>Batal
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Mobile optimized styles */
    .scan-mobile-page {
        background-color: #f5f5f5;
    }
    
    #scanDnInput {
        height: 60px;
        border: 2px solid #dee2e6;
        transition: all 0.3s ease;
    }
    
    #scanDnInput:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    }
    
    .address-btn {
        font-size: 1.2rem;
        transition: all 0.2s ease;
    }
    
    .address-btn:active {
        transform: scale(0.95);
    }
    
    .address-btn:hover {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: white;
    }
    
    /* Success animation */
    @keyframes successPulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    .scan-success {
        animation: successPulse 0.3s ease;
    }
    
    /* Recent scan highlight */
    .recent-highlight {
        border-left: 4px solid #000000 !important;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    
    let scanTimeout;
    let currentPreparation = null;
    const addressModal = new bootstrap.Modal(document.getElementById('addressModal'));
    
    // Auto focus on load
    $('#scanDnInput').focus();
    
    // Re-focus after modal closes
    $('#addressModal').on('hidden.bs.modal', function() {
        $('#scanDnInput').val('').focus();
        currentPreparation = null;
    });
    
    // Handle scan input
    $('#scanDnInput').on('input', function() {
        clearTimeout(scanTimeout);
        const noDn = $(this).val().trim();
        
        if (noDn.length > 0) {
            // Delay 500ms untuk menunggu scanner selesai input
            scanTimeout = setTimeout(function() {
                processScanDn(noDn);
            }, 500);
        }
    });
    
    // Handle Enter key
    $('#scanDnInput').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            clearTimeout(scanTimeout);
            const noDn = $(this).val().trim();
            if (noDn.length > 0) {
                processScanDn(noDn);
            }
        }
    });
    
    // Process scan DN
    function processScanDn(noDn) {
        // Show loading
        $('#scanStatus').removeClass('d-none');
        $('#scanDnInput').prop('disabled', true);
        
        $.ajax({
            url: '{{ route("preparations.findByDn") }}',
            type: 'GET',
            data: { no_dn: noDn },
            success: function(response) {
                $('#scanStatus').addClass('d-none');
                $('#scanDnInput').prop('disabled', false);
                
                if (response.success && response.data) {
                    currentPreparation = response.data;
                    showAddressModal(response.data);
                } else {
                    // DN tidak ditemukan
                    showError('DN Tidak Ditemukan', `No DN <strong>${noDn}</strong> tidak ada di preparation`);
                    $('#scanDnInput').val('').focus();
                }
            },
            error: function(xhr) {
                $('#scanStatus').addClass('d-none');
                $('#scanDnInput').prop('disabled', false);
                
                showError('Error', xhr.responseJSON?.message || 'Terjadi kesalahan');
                $('#scanDnInput').val('').focus();
            }
        });
    }
    
    // Show address selection modal
    function showAddressModal(preparation) {
        $('#modalDnNumber').text(preparation.no_dn);
        $('#modalDnInfo').html(`
            <span class="me-2"><strong>Route:</strong> ${preparation.route}</span>
            <span class="me-2"><strong>Dock:</strong> ${preparation.dock}</span>
            <span><strong>Cycle:</strong> ${preparation.cycle}</span>
        `);
        addressModal.show();
    }
    
    // Handle address button click
    $('.address-btn').on('click', function() {
        if (!currentPreparation) return;
        
        const address = $(this).data('address');
        const btn = $(this);
        
        // Disable all buttons
        $('.address-btn').prop('disabled', true);
        btn.html('<span class="spinner-border spinner-border-sm"></span>');
        
        // Execute move
        $.ajax({
            url: '{{ route("shippings.moveFromPreparation") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                preparation_id: currentPreparation.id,
                address: address
            },
            success: function(response) {
                addressModal.hide();
                showSuccess(currentPreparation.no_dn, address);
            },
            error: function(xhr) {
                $('.address-btn').prop('disabled', false);
                btn.html(`<strong>${address.replace('Shipping ', '').replace('Ex ', 'Ex ')}</strong>`);
                
                showError('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan');
            }
        });
    });
    
    // Show success message
    function showSuccess(noDn, address) {
        Swal.fire({
            title: 'Berhasil!',
            html: `<div class="text-center">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                <h4 class="mt-3 mb-1">${noDn}</h4>
                <p class="text-muted mb-0">Dipindahkan ke <strong>${address}</strong></p>
            </div>`,
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true
        }).then(() => {
            window.location.reload();
        });
    }
    
    // Show error message
    function showError(title, message) {
        Swal.fire({
            title: title,
            html: message,
            icon: 'error',
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'OK'
        }).then(() => {
            $('#scanDnInput').focus();
        });
    }
    
    // Prevent screen sleep on mobile (if supported)
    if ('wakeLock' in navigator) {
        navigator.wakeLock.request('screen').catch(err => {
            console.log('Wake Lock error:', err);
        });
    }
    
    // Logout confirmation
    $('#btnLogout').on('click', function() {
        Swal.fire({
            title: 'Konfirmasi Logout',
            text: 'Apakah Anda yakin ingin keluar?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Logout',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#logoutForm').submit();
            }
        });
    });
});
</script>
@endpush
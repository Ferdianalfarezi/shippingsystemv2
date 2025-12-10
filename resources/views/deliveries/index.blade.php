@extends('layouts.app')

@section('title', 'Data Delivery')
@section('page-title', 'DELIVERIES MONITORING')
@section('body-class', 'delivery-page')

@section('content')
    <!-- Stats Badges dan Dropdown di kanan -->
    <div class="d-flex justify-content-end align-items-center gap-2 mb-3 mt-3">
        
        <!-- Toggle View Button -->
        <div class="card border-0 shadow-sm p-1 bg-warning">
            <a href="{{ route('deliveries.indexReverse') }}" class="btn btn-warning text-dark" title="Switch to Reverse View">
                <i class="bi bi-box-seam"></i>
            </a>
        </div>
        
        <!-- Delete All Button -->
        <div class="card border-4 bg-danger">
            <button type="button" class="btn btn-danger" id="deleteAllButton" title="Hapus Semua Data">
                <i class="bi bi-trash-fill"></i>
            </button>
        </div>
        
        <!-- Show By Dropdown -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-1">
                <select class="form-select form-select-sm border-0" id="perPageSelect" style="width: auto;">
                    <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500</option>
                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
                </select>
            </div>
        </div>

        <!-- Status Filter -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-1">
                <select class="form-select form-select-sm border-0" id="statusFilter" style="width: auto;">
                    <option value="all" {{ request('status') == 'all' || !request('status') ? 'selected' : '' }}>All Status</option>
                    <option value="normal" {{ request('status') == 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="delay" {{ request('status') == 'delay' ? 'selected' : '' }}>Delay</option>
                </select>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="input-group" style="width: 300px;">
            <input type="text" class="form-control" id="searchInput" placeholder="Cari Route, LP, DN, Customer..." value="{{ request('search') }}">
            <button class="btn btn-secondary" type="button" id="searchButton">
                <i class="bi bi-search"></i>
            </button>
        </div>

        <!-- Badges -->
        <div class="d-flex align-items-center gap-2">
            <!-- Normal Badge -->
            <div class="bg-success card border-0 shadow-sm">
                <div class="card-body p-1">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-10 p-2 rounded me-2">
                            <i class="bi bi-check-circle text-white fs-5"></i>
                        </div>
                        <div>
                            <small class="text-white d-block fw-bold me-3" style="font-size: 0.7rem;">Normal</small>
                            <h5 class="mb-0 fw-bold text-white">{{ $totalNormal }}</h5>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Delay Badge -->
            <div class="bg-danger card border-0 shadow-sm me-3">
                <div class="card-body p-1">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-10 p-2 rounded me-2">
                            <i class="bi bi-exclamation-triangle text-white fs-5"></i>
                        </div>
                        <div>
                            <small class="text-white d-block fw-bold me-3" style="font-size: 0.7rem;">Delay</small>
                            <h5 class="mb-0 fw-bold text-white">{{ $totalDelay }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>

    <div class="table-responsive p-0 mt-0">
        <table class="table table-compact w-100 mt-1" id="deliveriesTable">
            <thead>
                <tr class="fs-5">
                    <th>Route</th>
                    <th>LP</th>
                    <th>No DN</th>
                    <th>Cust</th>
                    <th>Dock</th>
                    <th>Scan to Delv</th>
                    <th>Cyc</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deliveries as $index => $delivery)
                    <tr class="fs-4 {{ $delivery->status === 'delay' ? 'table-danger-subtle' : '' }}">
                        <td><strong>{{ $delivery->route }}</strong></td>
                        <td>{{ $delivery->logistic_partners }}</td>
                        <td>{{ $delivery->no_dn }}</td>
                        <td>{{ $delivery->customers }}</td>
                        <td><strong>{{ $delivery->dock }}</strong></td>
                        <td>{{ $delivery->formatted_scan_time }}</td>
                        
                        <td><strong>{{ $delivery->cycle }}</strong></td>
                        <td>{{ $delivery->address }}</td>
                        <td>
                           <span class="badge {{ $delivery->status_badge }} fw-bold px-3 py-2 mb-1 mt-1
                                {{ $delivery->status === 'delay' ? 'badge-delay' : '' }}"
                                title="{{ $delivery->status === 'delay' ? 'Delay ' . $delivery->delay_duration : 'Normal (<48h business hours)' }}">

                            {{ $delivery->status_label }}

                            @if($delivery->status === 'delay' && $delivery->delay_duration)
                                <small class="ms-0" style="font-size: 1rem;">+ {{ $delivery->delay_duration }}</small>
                            @endif
                        </span>
                        </td>
                        <td>
                            <div class="d-flex justify-content-center" style="gap: 0;">
                                <button onclick="openEditModal({{ $delivery->id }})" class="btn btn-warning btn-sm btn-action-square" style="border-radius: 6px 0 0 6px; margin: 0;" title="Edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                
                                <form action="{{ route('deliveries.destroy', $delivery->id) }}" method="POST" class="d-inline delete-form" style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm btn-action-square" style="border-radius: 0; margin: 0;" title="Hapus">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>

                                <a href="javascript:void(0)" onclick="showQrCode('{{ $delivery->no_dn }}')" class="btn btn-secondary btn-sm btn-action-square" style="border-radius: 0 6px 6px 0; margin: 0;" title="QR Code">
                                    <i class="bi bi-qr-code"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="mt-3">
                        <td colspan="11" class="text-center py-4">
                            <div class="text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">Belum ada data delivery</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-wrapper">
        {{ $deliveries->links() }}
    </div>

    @include('deliveries.edit')
    
@endsection

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

@push('scripts')
<script>
    $(document).ready(function() {
        // Delete confirmation dengan SweetAlert
        $('.delete-form').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const url = form.attr('action');
            
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data ini akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menghapus...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: response.message || 'Data berhasil dihapus',
                                icon: 'success',
                                confirmButtonColor: '#059669'
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: 'Gagal!',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus data',
                                icon: 'error',
                                confirmButtonColor: '#dc2626'
                            });
                        }
                    });
                }
            });
        });

        // Delete All Button
        $('#deleteAllButton').on('click', function() {
            Swal.fire({
                title: 'PERINGATAN!',
                text: "Apakah Anda yakin ingin menghapus SEMUA data delivery? Tindakan ini tidak dapat dibatalkan!",
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus Semua!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                input: 'text',
                inputPlaceholder: 'Ketik "HAPUS SEMUA" untuk konfirmasi',
                inputValidator: (value) => {
                    if (value !== 'HAPUS SEMUA') {
                        return 'Anda harus mengetik "HAPUS SEMUA" untuk melanjutkan!'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menghapus Semua Data...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    $.ajax({
                        url: '{{ route("deliveries.deleteAll") }}',
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonColor: '#059669'
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: 'Gagal!',
                                text: 'Terjadi kesalahan saat menghapus data',
                                icon: 'error',
                                confirmButtonColor: '#dc2626'
                            });
                        }
                    });
                }
            });
        });

        // Handle Search
        $('#searchButton').on('click', function() {
            performSearch();
        });

        $('#searchInput').on('keypress', function(e) {
            if (e.which === 13) {
                performSearch();
            }
        });

        // Handle Per Page Change
        $('#perPageSelect').on('change', function() {
            updateUrl();
        });

        // Handle Status Filter Change
        $('#statusFilter').on('change', function() {
            updateUrl();
        });

        function performSearch() {
            updateUrl();
        }

        function updateUrl() {
            const url = new URL(window.location.href);
            const perPage = $('#perPageSelect').val();
            const search = $('#searchInput').val();
            const status = $('#statusFilter').val();
            
            if (perPage && perPage !== '50') {
                url.searchParams.set('per_page', perPage);
            } else {
                url.searchParams.delete('per_page');
            }
            
            if (search && search.trim() !== '') {
                url.searchParams.set('search', search);
            } else {
                url.searchParams.delete('search');
            }

            if (status && status !== 'all') {
                url.searchParams.set('status', status);
            } else {
                url.searchParams.delete('status');
            }
            
            window.location.href = url.toString();
        }
    });

    // QR Code Generator
    function showQrCode(noDn) {
        Swal.fire({
            title: 'QR Code DN',
            html: `
                <div class="text-center">
                    <div id="qrcode" class="d-flex justify-content-center mb-3"></div>
                    <p class="fw-bold fs-4 mb-0 mt-3">${noDn}</p>
                </div>
            `,
            showCloseButton: true,
            showConfirmButton: true,
            confirmButtonText: '<i class="bi bi-printer"></i> Print',
            confirmButtonColor: '#6c757d',
            width: 350,
            didOpen: () => {
                new QRCode(document.getElementById("qrcode"), {
                    text: noDn,
                    width: 200,
                    height: 200,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const printWindow = window.open('', '_blank');
                const qrImage = document.querySelector('#qrcode img').src;
                printWindow.document.write(`
                    <html>
                    <head><title>QR Code - ${noDn}</title></head>
                    <body style="text-align: center; padding: 20px;">
                        <img src="${qrImage}" style="width: 200px; height: 200px;">
                        <p style="font-size: 24px; font-weight: bold; margin-top: 15px;">${noDn}</p>
                    </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.print();
            }
        });
    }

    // Edit Modal Function
    function openEditModal(id) {
        $.ajax({
            url: `/deliveries/${id}/edit`,
            type: 'GET',
            success: function(data) {
                $('#edit_delivery_id').val(data.id);
                $('#edit_route').val(data.route);
                $('#edit_logistic_partners').val(data.logistic_partners);
                $('#edit_no_dn').val(data.no_dn);
                $('#edit_customers').val(data.customers);
                $('#edit_dock').val(data.dock);
                $('#edit_cycle').val(data.cycle);
                $('#edit_address').val(data.address);
                
                $('#editDeliveryForm').attr('action', `/deliveries/${data.id}`);
                $('#editDeliveryModal').modal('show');
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Gagal memuat data delivery',
                    icon: 'error',
                    confirmButtonColor: '#dc2626'
                });
            }
        });
    }
</script>
@endpush
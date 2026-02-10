@extends('layouts.app')

@section('title', 'Data Shipping')
@section('page-title', 'SHIPPINGS MONITORING')
@section('body-class', 'preparation-page')

@section('content')
    <!-- Stats Badges dan Dropdown di kanan -->
    <div class="d-flex justify-content-end align-items-center gap-2 mb-3 mt-3">
        
        <!-- Toggle View Button -->
        <div class="card border-0 shadow-sm p-1 bg-warning">
            <a href="{{ route('shippings.indexReverse') }}" class="btn btn-warning" title="Switch to Reverse View">
                <i class="fa-solid fa-truck"></i>
            </a>
        </div>  
        
        <!-- Scan DN Input (langsung tanpa modal) -->
        <div class="input-group" style="width: 280px;">
            <span class="input-group-text bg-white text-dark">
                <i class="bi bi-qr-code-scan"></i>
            </span>
            <input type="text" class="form-control" id="scanDnInput" placeholder="Scan DN to Delivery..." autofocus>
        </div>
        
        @if(auth()->user()->role === 'superadmin')
            <!-- Delete All Button -->
            <div class="card border-0 shadow-sm p-1 bg-danger">
                <button type="button" class="btn btn-danger" id="deleteAllButton" title="Hapus Semua Data">
                    <i class="bi bi-trash-fill"></i>
                </button>
            </div>
        @endif
        
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

        <!-- Status Filter - Advance, Normal, Delay, On Loading -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-1">
                <select class="form-select form-select-sm border-0" id="statusFilter" style="width: auto;">
                    <option value="all" {{ request('status') == 'all' || !request('status') ? 'selected' : '' }}>All Status</option>
                    <option value="advance" {{ request('status') == 'advance' ? 'selected' : '' }}>Advance</option>
                    <option value="normal" {{ request('status') == 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="delay" {{ request('status') == 'delay' ? 'selected' : '' }}>Delay</option>
                    <option value="on_loading" {{ request('status') == 'on_loading' ? 'selected' : '' }}>On Loading</option>
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

        <!-- Menu & Badges -->
        <div class="d-flex align-items-center gap-2">
            <!-- Dropdown Menu Button -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-2">
                    <div class="dropdown">
                        <button class="btn btn-link text-dark p-0 m-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false" style="text-decoration: none;">
                            <i class="bi bi-three-dots-vertical fs-4"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="dropdownMenuButton">
                            <li>
                                <a class="dropdown-item" href="{{ route('shippings.checkingLp') }}">
                                    <i class="bi bi-qr-code-scan me-2"></i> Checking LP
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Advance Badge -->
            <div class="bg-warning card border-0 shadow-sm">
                <div class="card-body p-1">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-10 p-2 rounded me-2">
                            <i class="bi bi-clock text-dark fs-5"></i>
                        </div>
                        <div>
                            <small class="text-dark d-block fw-bold me-3" style="font-size: 0.7rem;">Advance</small>
                            <h5 class="mb-0 fw-bold text-dark">{{ $totalAdvance }}</h5>
                        </div>
                    </div>
                </div>
            </div>

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
            <div class="bg-danger card border-0 shadow-sm">
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

            <!-- On Loading Badge -->
            <div class="bg-primary card border-0 shadow-sm me-3">
                <div class="card-body p-1">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-10 p-2 rounded me-2">
                            <i class="bi bi-truck text-white fs-5"></i>
                        </div>
                        <div>
                            <small class="text-white d-block fw-bold me-3" style="font-size: 0.7rem;">On Loading</small>
                            <h5 class="mb-0 fw-bold text-white">{{ $totalOnLoading }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>

    @if($recentScan)
    <div class="card border-0 shadow-sm mb-3 ms-3 me-3"
     style="border-radius:0; background-color:#000000; outline:2px solid #ffffff;">

        <div class="card-body py-1 px-4">
            <div class="d-flex align-items-center justify-content-center gap-3">

                <!-- Icon & Label -->
                <div class="d-flex align-items-center gap-2">
                    <div>
                        <i class="bi bi-arrow-right-circle-fill text-white fs-6"></i>
                    </div>
                    <small class="text-white fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.5px;">RECENT SCAN</small>
                </div>

                <!-- Vertical Divider -->
                <div class="vr" style="height: 30px; opacity: 0.2;"></div>

                <!-- No DN -->
                <div class="d-flex align-items-center gap-2">
                    <small class="text-white" style="font-size: 1rem;">No DN:</small>
                    <strong class="text-white">{{ $recentScan->no_dn }}</strong>
                </div>

                <!-- Vertical Divider -->
                <div class="vr" style="height: 30px; opacity: 0.2;"></div>

                <!-- Route -->
                <div class="d-flex align-items-center gap-2">
                    <small class="text-white" style="font-size: 1rem;">Route:</small>
                    <span class="fw-semibold text-white">{{ $recentScan->route }}</span>
                </div>

                <!-- Vertical Divider -->
                <div class="vr" style="height: 30px; opacity: 0.2;"></div>

                <!-- Dock -->
                <div class="d-flex align-items-center gap-2">
                    <small class="text-white" style="font-size: 1rem;">Dock:</small>
                    <span class="fw-semibold text-white">{{ $recentScan->dock }}</span>
                </div>

                <!-- Vertical Divider -->
                <div class="vr" style="height: 30px; opacity: 0.2;"></div>

                <!-- Cycle -->
                <div class="d-flex align-items-center gap-2">
                    <small class="text-white" style="font-size: 1rem;">Cycle:</small>
                    <span class="fw-semibold text-white">{{ $recentScan->cycle }}</span>
                </div>

                <!-- Vertical Divider -->
                <div class="vr" style="height: 30px; opacity: 0.2;"></div>

                <!-- Customer -->
                <div class="d-flex align-items-center gap-2">
                    <small class="text-white" style="font-size: 1rem;">Customer:</small>
                    <span class="fw-semibold text-white">{{ $recentScan->customers }}</span>
                </div>

                <!-- Vertical Divider -->
                <div class="vr" style="height: 30px; opacity: 0.2;"></div>

                <!-- Moved By -->
                <div class="d-flex align-items-center gap-2">
                    <small class="text-white" style="font-size: 1rem;">Scan by:</small>
                    <span class="fw-semibold text-white">
                        <i class="bi bi-person-fill"></i> {{ $recentScan->moved_by ?? 'System' }}
                    </span>
                </div>

                <!-- Timestamp -->
                <div class="d-flex align-items-center gap-2">
                    <span class="text-white fw-bold">
                        <i class="bi bi-clock-fill"></i> {{ $recentScan->scan_to_delivery->format('H:i:s') }}
                    </span>
                    <span class="text-white fw-bold">{{ $recentScan->scan_to_delivery->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="table-responsive p-0 mt-0">
        <table class="table table-compact w-100 mt-1" id="shippingsTable">
            <thead>
                <tr class="fs-5">
                    <th>Route</th>
                    <th>LP</th>
                    <th>No DN</th>
                    <th>Cust</th>
                    <th>Dock</th>
                    <th>Delv Date</th>
                    <th>Delv Time</th>
                    <th>Arrival</th>
                    <th>Cyc</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shippings as $index => $ship)
                    @php
                        $currentStatus = $ship->calculateStatus();
                    @endphp
                    <tr class="fs-4 {{ $currentStatus === 'delay' ? 'table-danger-subtle' : '' }}">
                        <td><strong>{{ $ship->route }}</strong></td>
                        <td>{{ $ship->logistic_partners }}</td>
                        <td>{{ $ship->no_dn }}</td>
                        <td>{{ $ship->customers }}</td>
                        <td><strong>{{ $ship->dock }}</strong></td>
                        <td>{{ $ship->delivery_date->format('d-m-y') }}</td>
                        <td>{{ date('H:i:s', strtotime($ship->delivery_time)) }}</td>
                        <td>
                            @if($ship->arrival)
                                <span>{{ $ship->arrival->format('d-m-y H:i') }}</span>
                            @else
                                <span>-</span>
                            @endif
                        </td>
                        <td><strong>{{ $ship->cycle }}</strong></td>
                        <td>{{ $ship->address }}</td>
                        <td>
                            <span class="badge {{ $ship->status_badge }} fw-bold px-3 py-2 mb-1 mt-1 
                                {{ $currentStatus === 'delay' ? 'badge-delay' : '' }}"
                                title="{{ $ship->time_info }}">
                                {{ $ship->status_label }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex justify-content-center" style="gap: 0;">
                                @if(auth()->user()->role === 'superadmin')
                                    <button onclick="openEditModal({{ $ship->id }})" class="btn btn-warning btn-sm btn-action-square" style="border-radius: 6px 0 0 6px; margin: 0;" title="Edit">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    
                                    <form action="{{ route('shippings.destroy', $ship->id) }}" method="POST" class="d-inline delete-form" style="margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm btn-action-square" style="border-radius: 0; margin: 0;" title="Hapus">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                @endif

                                <a href="javascript:void(0)" onclick="showQrCode('{{ $ship->no_dn }}')" class="btn btn-secondary btn-sm btn-action-square" style="border-radius: 0; margin: 0;" title="QR Code">
                                    <i class="bi bi-qr-code"></i>
                                </a>
                                
                                <button onclick="moveToDelivery({{ $ship->id }}, '{{ $ship->no_dn }}')" class="btn btn-primary btn-sm btn-action-square" style="border-radius: 0 6px 6px 0; margin: 0;" title="Move to Delivery">
                                    <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="mt-3">
                        <td colspan="12" class="text-center py-4">
                            <div class="text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">Belum ada data shipping</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-wrapper">
        {{ $shippings->links() }}
    </div>

    @include('shippings.edit')
    
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
                text: "Apakah Anda yakin ingin menghapus SEMUA data shipping? Tindakan ini tidak dapat dibatalkan!",
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
                        url: '{{ route("shippings.deleteAll") }}',
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

        // Handle scan input langsung (tanpa modal)
        $('#scanDnInput').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                const noDn = $(this).val().trim();
                if (noDn) {
                    processScanToDelivery(noDn);
                }
            }
        });
    });

    // Process scan to delivery
    function processScanToDelivery(noDn) {
        $.ajax({
            url: '{{ route("shippings.scanToDelivery") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                no_dn: noDn
            },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    html: `<strong>${response.data.no_dn}</strong> dipindahkan ke Delivery<br><small class="text-muted">${response.data.route} - ${response.data.customers}</small>`,
                    confirmButtonColor: '#059669'
                }).then(() => {
                    $('#scanDnInput').val('').focus();
                    window.location.reload();
                });
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan',
                    confirmButtonColor: '#dc2626'
                }).then(() => {
                    $('#scanDnInput').val('').focus();
                });
            }
        });
    }

    // Move to Delivery (single item via button)
    function moveToDelivery(shippingId, noDn) {
        Swal.fire({
            title: 'Pindahkan ke Delivery?',
            html: `Data <strong>${noDn}</strong> akan dipindahkan dari Shipping ke Delivery`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0dcaf0',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-arrow-right"></i> Ya, Pindahkan!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                
                $.ajax({
                    url: '{{ route("shippings.moveToDelivery") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        shipping_id: shippingId
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.message,
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1500,
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Gagal!',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan',
                            icon: 'error',
                            confirmButtonColor: '#dc2626'
                        });
                    }
                });
            }
        });
    }

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
            url: `/shippings/${id}/edit`,
            type: 'GET',
            success: function(data) {
                $('#edit_shipping_id').val(data.id);
                $('#edit_route').val(data.route);
                $('#edit_logistic_partners').val(data.logistic_partners);
                $('#edit_no_dn').val(data.no_dn);
                $('#edit_customers').val(data.customers);
                $('#edit_dock').val(data.dock);
                $('#edit_delivery_date').val(data.delivery_date);
                $('#edit_delivery_time').val(data.delivery_time);
                $('#edit_cycle').val(data.cycle);
                $('#edit_address').val(data.address);
                
                $('#editShippingForm').attr('action', `/shippings/${data.id}`);
                $('#editShippingModal').modal('show');
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Gagal memuat data shipping',
                    icon: 'error',
                    confirmButtonColor: '#dc2626'
                });
            }
        });
    }
</script>
@endpush
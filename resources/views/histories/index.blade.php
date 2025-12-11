@extends('layouts.app')

@section('title', 'Data History')
@section('page-title', 'RECEIPT DN HISTORY')
@section('body-class', 'history-page')

@section('content')
    <!-- Stats Badges dan Dropdown di kanan -->
    <div class="d-flex justify-content-end align-items-center gap-2 mb-3 mt-3">
        
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

        <!-- Date Range Filter -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-1 d-flex align-items-center gap-1">
                <input type="date" class="form-control form-control-sm border-0" id="dateFrom" 
                       value="{{ request('date_from') }}" placeholder="From" style="width: 130px;">
                <span class="text-muted">-</span>
                <input type="date" class="form-control form-control-sm border-0" id="dateTo" 
                       value="{{ request('date_to') }}" placeholder="To" style="width: 130px;">
            </div>
        </div>

        <!-- Search Bar -->
        <div class="input-group" style="width: 300px;">
            <input type="text" class="form-control" id="searchInput" placeholder="Cari Route, LP, DN, Customer..." value="{{ request('search') }}">
            <button class="btn btn-secondary" type="button" id="searchButton">
                <i class="bi bi-search"></i>
            </button>
        </div>

        <!-- Total Badge -->
        <div class="bg-secondary card border-0 shadow-sm me-3">
            <div class="card-body p-1">
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-10 p-2 rounded me-2">
                        <i class="bi bi-archive text-white fs-5"></i>
                    </div>
                    <div>
                        <small class="text-white d-block fw-bold me-3" style="font-size: 0.7rem;">Total</small>
                        <h5 class="mb-0 fw-bold text-white">{{ $totalAll }}</h5>
                    </div>
                </div>
            </div>
        </div>
        
    </div>

    <div class="table-responsive p-0 mt-0">
        <table class="table table-compact w-100 mt-1" id="historiesTable">
            <thead>
                <tr class="fs-5">
                    <th>Route</th>
                    <th>LP</th>
                    <th>No DN</th>
                    <th>Cust</th>
                    <th>Dock</th>
                    <th>Cyc</th>
                    <th>Address</th>
                    <th>Completed</th> 
                    <th>Detail</th>
                </tr>
            </thead>
            <tbody>
                @forelse($histories as $index => $history)
                    <tr class="fs-4">
                        <td><strong>{{ $history->route }}</strong></td>
                        <td>{{ $history->logistic_partners }}</td>
                        <td>{{ $history->no_dn }}</td>
                        <td>{{ $history->customers }}</td>
                        <td><strong>{{ $history->dock }}</strong></td>
                        <td><strong>{{ $history->cycle }}</strong></td>
                        <td>{{ $history->address }}</td>
                        <td>{{ $history->formatted_completed_at }}</td>
                        <td>
                            <div class="d-flex justify-content-center" style="gap: 6px;">
                                <!-- Tombol Detail -->
                                <button onclick="openDetailModal({{ $history->id }})" 
                                        class="btn btn-warning btn-sm btn-action-square" 
                                        style="border-radius: 6px;" 
                                        title="Detail">
                                    <i class="bi bi-eye-fill"></i>
                                </button>

                                @if(auth()->user()->role === 'superadmin')
                                <!-- Tombol Delete -->
                                <form action="{{ route('histories.destroy', $history->id) }}" 
                                    method="POST" 
                                    class="d-inline delete-form" 
                                    style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="btn btn-danger btn-sm btn-action-square" 
                                            style="border-radius: 6px;" 
                                            title="Hapus">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>

                    </tr>
                @empty
                    <tr class="mt-3">
                        <td colspan="10" class="text-center py-4">
                            <div class="text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">Belum ada data history</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-wrapper">
        {{ $histories->links() }}
    </div>

    <!-- Include Detail Modal -->
    @include('histories.detail')
    
@endsection

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

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
                text: "Apakah Anda yakin ingin menghapus SEMUA data history? Tindakan ini tidak dapat dibatalkan!",
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
                        url: '{{ route("histories.deleteAll") }}',
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
            updateUrl();
        });

        $('#searchInput').on('keypress', function(e) {
            if (e.which === 13) {
                updateUrl();
            }
        });

        // Handle Per Page Change
        $('#perPageSelect').on('change', function() {
            updateUrl();
        });

        // Handle Date Filter Change
        $('#dateFrom, #dateTo').on('change', function() {
            updateUrl();
        });

        function updateUrl() {
            const url = new URL(window.location.href);
            const perPage = $('#perPageSelect').val();
            const search = $('#searchInput').val();
            const dateFrom = $('#dateFrom').val();
            const dateTo = $('#dateTo').val();
            
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

            if (dateFrom) {
                url.searchParams.set('date_from', dateFrom);
            } else {
                url.searchParams.delete('date_from');
            }

            if (dateTo) {
                url.searchParams.set('date_to', dateTo);
            } else {
                url.searchParams.delete('date_to');
            }
            
            window.location.href = url.toString();
        }
    });

    // Detail Modal Function
    function openDetailModal(id) {
        $.ajax({
            url: `/histories/${id}`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    
                    // Basic Info
                    $('#detail_no_dn').text(data.no_dn);
                    $('#detail_route').text(data.route);
                    $('#detail_logistic_partners').text(data.logistic_partners);
                    $('#detail_customers').text(data.customers);
                    $('#detail_dock').text(data.dock);
                    $('#detail_cycle').text(data.cycle);
                    $('#detail_address').text(data.address);
                    
                    // Timeline
                    $('#detail_pulling_datetime').text(data.pulling_datetime || '-');
                    $('#detail_delivery_datetime').text(data.delivery_datetime || '-');
                    $('#detail_scan_to_shipping').text(data.scan_to_shipping || '-');
                    $('#detail_arrival').text(data.arrival || '-');
                    $('#detail_scan_to_delivery').text(data.scan_to_delivery || '-');
                    $('#detail_completed_at').text(data.completed_at || '-');
                    
                    // Durations
                    $('#detail_shipping_duration').text(data.shipping_duration);
                    $('#detail_loading_duration').text(data.loading_duration);
                    $('#detail_delivery_duration').text(data.delivery_duration);
                    $('#detail_total_duration').text(data.total_journey_duration);
                    $('#detail_business_hours').text(data.total_business_hours);
                    
                    // User
                    $('#detail_moved_by').text(data.moved_by || '-');
                    
                    $('#detailHistoryModal').modal('show');
                }
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Gagal memuat data history',
                    icon: 'error',
                    confirmButtonColor: '#dc2626'
                });
            }
        });
    }
</script>
@endpush
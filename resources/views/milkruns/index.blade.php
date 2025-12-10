@extends('layouts.app')

@section('title', 'Data Milkrun')
@section('page-title', 'MILKRUN MONITORING')
@section('body-class', 'milkrun-page')

@push('styles')
<style>
    /* Animasi kedip untuk badge delay */
    @keyframes blink-delay {
        0%, 50% {
            opacity: 1;
        }
        51%, 100% {
            opacity: 0.4;
        }
    }
    
    .badge-delay-blink {
        animation: blink-delay 1s ease-in-out infinite;
    }
    
    /* Row delay juga kedip subtle */
    .row-delay-blink {
        animation: blink-row 1.5s ease-in-out infinite;
    }
    
    @keyframes blink-row {
        0%, 50% {
            background-color: rgba(220, 53, 69, 0.15);
        }
        51%, 100% {
            background-color: rgba(220, 53, 69, 0.05);
        }
    }
</style>
@endpush

@section('content')
    <!-- Stats Badges dan Dropdown di kanan -->
    <div class="d-flex justify-content-end align-items-center gap-2 mb-3 mt-3">
        
        <!-- Delete All Button -->
        <div class="card border-4 bg-danger">
            <button type="button" class="btn btn-danger" id="deleteAllButton" title="Hapus Semua Data">
                <i class="bi bi-trash-fill"></i>
            </button>
        </div>
        
        <!-- Date Filter -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-1 d-flex align-items-center gap-1">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="prevDateBtn" title="Hari Sebelumnya">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <input type="date" class="form-control form-control-sm border-0" id="dateFilter" 
                       value="{{ $dateFilter }}" style="width: 110px;">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="nextDateBtn" title="Hari Berikutnya">
                    <i class="bi bi-chevron-right"></i>
                </button>
                <button type="button" class="btn btn-sm btn-dark" id="todayBtn" title="Hari Ini">
                    Today
                </button>
            </div>
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
                    <option value="advance" {{ request('status') == 'advance' ? 'selected' : '' }}>Advance</option>
                    <option value="on_time" {{ request('status') == 'on_time' ? 'selected' : '' }}>On Time</option>
                    <option value="delay" {{ request('status') == 'delay' ? 'selected' : '' }}>Delay</option>
                </select>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="input-group" style="width: 300px;">
            <input type="text" class="form-control" id="searchInput" placeholder="Cari Route, LP, Customer..." value="{{ request('search') }}">
            <button class="btn btn-secondary" type="button" id="searchButton">
                <i class="bi bi-search"></i>
            </button>
        </div>

        <!-- Badges -->
        <div class="d-flex align-items-center gap-2">

            <!-- Advance Badge -->
            <div class="bg-warning card border-0 shadow-sm">
                <div class="card-body p-1">
                    <div class="d-flex align-items-center ">
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

            <!-- On Time Badge -->
            <div class="bg-success card border-0 shadow-sm">
                <div class="card-body p-1">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-10 p-2 rounded me-2">
                            <i class="bi bi-check-circle text-white fs-5"></i>
                        </div>
                        <div>
                            <small class="text-white d-block fw-bold me-3" style="font-size: 0.7rem;">On Time</small>
                            <h5 class="mb-0 fw-bold text-white">{{ $totalOnTime }}</h5>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Delay Badge -->
            <div class="bg-danger card border-0 shadow-sm me-3 {{ $totalDelay > 0 ? 'badge-delay-blink' : '' }}">
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
        <table class="table table-compact w-100 mt-1" id="milkrunsTable">
            <thead>
                <tr class="fs-5">
                    <th>Cust</th>
                    <th>Route</th>
                    <th>LP</th>
                    <th>Cyc</th>
                    <th>Dock</th>
                    <th>Del Date</th>
                    <th>Del Time</th>
                    <th>Arrival</th>
                    <th>Departure</th>
                    <th>Status</th>
                    <th>DN</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($milkruns as $milkrun)
                    @php
                        $currentStatus = $milkrun->arrival ? $milkrun->calculateStatus() : 'pending';
                    @endphp

                    <tr class="fs-4 {{ $currentStatus === 'delay' ? 'row-delay-blink' : '' }}">
                        <td>{{ $milkrun->customers }}</td>
                        <td><strong>{{ $milkrun->route }}</strong></td>
                        <td>{{ $milkrun->logistic_partners }}</td>
                        <td><strong>{{ $milkrun->cycle }}</strong></td>
                        <td><strong>{{ $milkrun->dock }}</strong></td>
                        <td>{{ $milkrun->delivery_date->format('d-m-y') }}</td>
                        <td>{{ date('H:i:s', strtotime($milkrun->delivery_time)) }}</td>

                        {{-- ARRIVAL --}}
                        <td>
                            @if($milkrun->arrival)
                                {{ $milkrun->arrival->format('d-m-y H:i') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- DEPARTURE --}}
                        <td>
                            @if($milkrun->departure)
                                {{ $milkrun->departure->format('d-m-y H:i') }}
                            @else
                                <button class="btn btn-sm btn-outline-success"
                                        onclick="setDeparture({{ $milkrun->id }})"
                                        title="Set Departure">
                                    <i class="bi bi-box-arrow-right"></i> Set
                                </button>
                            @endif
                        </td>

                        {{-- STATUS --}}
                        <td>
                            <span class="badge {{ $milkrun->status_badge }} fw-bold px-3 py-2 mt-1 mb-1 {{ $currentStatus === 'delay' ? 'badge-delay-blink' : '' }}"
                                title="{{ $milkrun->time_diff_info }}">
                                {{ $milkrun->status_label }}
                            </span>
                        </td>

                        {{-- DN --}}
                        <td>
                            <button class="btn btn-sm btn-info"
                                    onclick="showDnList({{ $milkrun->id }})"
                                    title="Lihat DN">
                                <i class="bi bi-list-ul"></i> {{ $milkrun->dn_count }}
                            </button>
                        </td>

                        {{-- ACTION --}}
                        <td>
                            <div class="btn-group" role="group">
                                <button onclick="openEditModal({{ $milkrun->id }})"
                                        class="btn btn-warning btn-sm"
                                        style="border-radius: 6px 0 0 6px; border-right: none;"
                                        title="Edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <form action="{{ route('milkruns.destroy', $milkrun->id) }}"
                                    method="POST"
                                    class="d-inline m-0 p-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-danger btn-sm"
                                            style="border-radius: 0 6px 6px 0;"
                                            title="Hapus">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="12" class="text-center py-4">
                            <div class="text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">Belum ada data milkrun untuk tanggal {{ \Carbon\Carbon::parse($dateFilter)->format('d M Y') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>


    <!-- Pagination -->
    <div class="pagination-wrapper">
        {{ $milkruns->links() }}
    </div>

    <!-- Edit Modal -->
    @include('milkruns.edit')
    
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
                text: "Apakah Anda yakin ingin menghapus SEMUA data milkrun? Tindakan ini tidak dapat dibatalkan!",
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
                        url: '{{ route("milkruns.deleteAll") }}',
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

        // Handle Date Filter Change
        $('#dateFilter').on('change', function() {
            updateUrl();
        });

        // Previous Date Button
        $('#prevDateBtn').on('click', function() {
            const currentDate = new Date($('#dateFilter').val());
            currentDate.setDate(currentDate.getDate() - 1);
            $('#dateFilter').val(formatDate(currentDate));
            updateUrl();
        });

        // Next Date Button
        $('#nextDateBtn').on('click', function() {
            const currentDate = new Date($('#dateFilter').val());
            currentDate.setDate(currentDate.getDate() + 1);
            $('#dateFilter').val(formatDate(currentDate));
            updateUrl();
        });

        // Today Button
        $('#todayBtn').on('click', function() {
            const today = new Date();
            $('#dateFilter').val(formatDate(today));
            updateUrl();
        });

        function formatDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function performSearch() {
            updateUrl();
        }

        function updateUrl() {
            const url = new URL(window.location.href);
            const perPage = $('#perPageSelect').val();
            const search = $('#searchInput').val();
            const status = $('#statusFilter').val();
            const date = $('#dateFilter').val();
            const today = formatDate(new Date());
            
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

            // Hanya set date jika bukan hari ini
            if (date && date !== today) {
                url.searchParams.set('date', date);
            } else {
                url.searchParams.delete('date');
            }
            
            window.location.href = url.toString();
        }

    });

    // Set Departure manually
    function setDeparture(milkrunId) {
        Swal.fire({
            title: 'Set Departure Time',
            html: `
                <input type="datetime-local" id="departureInput" class="form-control" value="${new Date().toISOString().slice(0, 16)}">
            `,
            showCancelButton: true,
            confirmButtonText: 'Set Departure',
            confirmButtonColor: '#198754',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                const departure = document.getElementById('departureInput').value;
                if (!departure) {
                    Swal.showValidationMessage('Pilih waktu departure');
                    return false;
                }
                return departure;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/milkruns/${milkrunId}/departure`,
                    type: 'PATCH',
                    data: {
                        _token: '{{ csrf_token() }}',
                        departure: result.value
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Departure time berhasil diset',
                            confirmButtonColor: '#059669'
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan',
                            confirmButtonColor: '#dc2626'
                        });
                    }
                });
            }
        });
    }

    // Show DN List dengan SweetAlert dan Better Error Handling
function showDnList(milkrunId) {
    // Show loading
    Swal.fire({
        title: 'Memuat data...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: `/milkruns/${milkrunId}/dns`,
        type: 'GET',
        success: function(response) {
            console.log('Response:', response); // Debug
            
            if (!response.success) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: response.message || 'Gagal memuat daftar DN',
                    confirmButtonColor: '#dc2626'
                });
                return;
            }
            
            // Check if deliveries exist
            if (!response.data.deliveries || response.data.deliveries.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Data DN Kosong',
                    html: `
                        <div style="text-align: left; margin-bottom: 15px; padding: 15px; background: #f3f4f6; border-radius: 10px;">
                            <div style="margin-bottom: 8px;">
                                <strong>Route:</strong> ${response.data.milkrun.route}
                            </div>
                            <div style="margin-bottom: 8px;">
                                <strong>Cycle:</strong> ${response.data.milkrun.cycle}
                            </div>
                            <div>
                                <strong>Total DN:</strong> ${response.data.milkrun.dn_count}
                            </div>
                        </div>
                        <p class="text-muted">Tidak ada data DN yang ditemukan di tabel Delivery.</p>
                    `,
                    confirmButtonColor: '#6366f1'
                });
                return;
            }
            
            // Build DN list HTML dengan styling yang lebih menarik
            let dnListHtml = '';
            response.data.deliveries.forEach(function(d, index) {
                dnListHtml += `
                    <div style="padding: 12px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; transition: background 0.2s;">
                        <div style="flex: 1;">
                            <strong style="color: #1f2937; font-size: 0.95rem;">${index + 1}. ${d.no_dn}</strong>
                        </div>
                    </div>
                `;
            });
            
            Swal.fire({
                title: `Daftar DN`,
                html: `
                    <div style="
                        text-align: left; 
                        margin-bottom: 12px;
                        padding: 8px 0;
                        border-bottom: 1px solid #e5e7eb;
                    ">
                        <div style="display: flex; justify-content: space-between; gap: 16px;">
                            
                            <div style="flex: 1;">
                                <div style="font-size: 0.7rem; color: #6b7280; margin-bottom: 2px;">Route</div>
                                <div style="font-size: 0.95rem; font-weight: 600; color: #111827;">
                                    ${response.data.milkrun.route}
                                </div>
                            </div>

                            <div style="flex: 1; text-align: center;">
                                <div style="font-size: 0.7rem; color: #6b7280; margin-bottom: 2px;">Cycle</div>
                                <div style="font-size: 0.95rem; font-weight: 600; color: #111827;">
                                    ${response.data.milkrun.cycle}
                                </div>
                            </div>

                            <div style="flex: 1; text-align: right;">
                                <div style="font-size: 0.7rem; color: #6b7280; margin-bottom: 2px;">Total DN</div>
                                <div style="font-size: 0.95rem; font-weight: 700; color: #111827;">
                                    ${response.data.milkrun.dn_count}
                                </div>
                            </div>

                        </div>
                    </div>

                    <div style="
                        max-height: 380px; 
                        overflow-y: auto; 
                        border: 1px solid #e5e7eb; 
                        border-radius: 8px; 
                        background: #fafafa;
                        padding: 8px;
                    ">
                        ${dnListHtml}
                    </div>
                `,
                width: "580px",
                confirmButtonText: "Tutup",
                confirmButtonColor: "#4f46e5",
                background: "#ffffff",
                customClass: {
                    popup: 'swal-compact'
                }
            });
        },
        error: function(xhr) {
            console.error('Error:', xhr); // Debug
            
            let errorMessage = 'Gagal memuat daftar DN';
            let debugInfo = '';
            
            if (xhr.responseJSON) {
                errorMessage = xhr.responseJSON.message || errorMessage;
                
                if (xhr.responseJSON.debug) {
                    debugInfo = `
                        <hr>
                        <div style="text-align: left; font-size: 0.85rem; color: #6b7280;">
                            <strong>Debug Info:</strong>
                            <pre style="background: #f3f4f6; padding: 10px; border-radius: 5px; margin-top: 5px; text-align: left; overflow-x: auto;">${JSON.stringify(xhr.responseJSON.debug, null, 2)}</pre>
                        </div>
                    `;
                }
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                html: `
                    <p>${errorMessage}</p>
                    ${debugInfo}
                `,
                confirmButtonColor: '#dc2626',
                width: '600px'
            });
        }
    });
}

    // Tambahkan CSS untuk styling yang lebih baik
    const style = document.createElement('style');
    style.textContent = `
        .dn-list-modal .swal2-html-container {
            overflow: visible !important;
            padding: 0 !important;
        }
        
        .dn-list-popup {
            padding: 20px !important;
        }
        
        .swal2-title {
            padding-bottom: 10px !important;
        }
        
        /* Custom scrollbar for DN list */
        .dn-list-modal .swal2-html-container > div:last-child::-webkit-scrollbar {
            width: 8px;
        }
        
        .dn-list-modal .swal2-html-container > div:last-child::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .dn-list-modal .swal2-html-container > div:last-child::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        
        .dn-list-modal .swal2-html-container > div:last-child::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        
        /* Hover effect on DN items */
        .dn-list-modal .swal2-html-container > div:last-child > div:hover {
            background: #ffffff !important;
        }
    `;
    document.head.appendChild(style);

    // Edit Modal Function
    function openEditModal(id) {
        $.ajax({
            url: `/milkruns/${id}/edit`,
            type: 'GET',
            success: function(data) {
                $('#edit_milkrun_id').val(data.id);
                $('#edit_customers').val(data.customers);
                $('#edit_route').val(data.route);
                $('#edit_logistic_partners').val(data.logistic_partners);
                $('#edit_cycle').val(data.cycle);
                $('#edit_dock').val(data.dock);
                $('#edit_delivery_date').val(data.delivery_date);
                $('#edit_delivery_time').val(data.delivery_time);
                $('#edit_arrival').val(data.arrival);
                $('#edit_departure').val(data.departure);
                $('#edit_address').val(data.address);
                
                $('#editMilkrunForm').attr('action', `/milkruns/${data.id}`);
                $('#editMilkrunModal').modal('show');
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Gagal memuat data milkrun',
                    icon: 'error',
                    confirmButtonColor: '#dc2626'
                });
            }
        });
    }
</script>
@endpush
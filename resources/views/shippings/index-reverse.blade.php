@extends('layouts.app')

@section('title', 'Shipping Reverse View')
@section('page-title', 'SHIPPINGS MONITORING')
@section('body-class', 'preparation-page')

@section('content')
    <!-- Stats Badges dan Controls -->
    <div class="d-flex justify-content-end align-items-center gap-2 mb-3 mt-3">
        
        <!-- Toggle View Button with Spin Animation -->
        <div class="card border-0 shadow-sm p-1 bg-warning">
            <a href="{{ route('shippings.index') }}" class="btn btn-warning" title="Switch to Normal View">
                <i class="bi bi-list-ul spin-on-hover"></i>
            </a>
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

        <!-- Search Bar -->
        <div class="input-group" style="width: 300px;">
            <input type="text" class="form-control" id="searchInput" placeholder="Cari Route, LP, Customer..." value="{{ request('search') }}">
            <button class="btn btn-secondary" type="button" id="searchButton">
                <i class="bi bi-search"></i>
            </button>
        </div>

        <!-- Statistics Badges - SAMA SEPERTI INDEX BIASA -->
        <div class="d-flex align-items-center gap-2">
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

    <div class="table-responsive p-0 mt-0">
        <table class="table table-compact w-100 mt-1" id="shippingsReverseTable">
            <thead>
                <tr class="fs-5">
                    <th>Route</th>
                    <th>LP</th>
                    <th>Customer</th>
                    <th>Dock</th>
                    <th>Delv Date</th>
                    <th>Delv Time</th>
                    <th>Arrival</th>
                    <th>Cycle</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($groupedShippings as $index => $group)
                    <tr class="fs-4 {{ !$group['has_arrival'] && $group['status_label'] === 'Delay' ? 'table-danger-subtle' : '' }}">
                        <td><strong>{{ $group['route'] }}</strong></td>
                        <td>{{ $group['logistic_partners'] }}</td>
                        <td>{{ Str::limit($group['customers'], 30) }}</td>
                        <td><strong>{{ $group['dock'] }}</strong></td> 
                        <td>{{ $group['delivery_date'] }}</td>
                        <td>{{ $group['delivery_time'] }}</td>
                        <td>
                            @if($group['arrival'])
                                <span>{{ $group['arrival'] }}</span>
                            @else
                                <span>-</span>
                            @endif
                        </td>
                        <td><strong>{{ $group['cycle'] }}</strong></td>
                        
                        <td>
                            @php
                                $addresses = explode(',', $group['address']);
                                $numbers = array_map(function($addr) {
                                    return trim(str_replace('Shipping', '', $addr));
                                }, $addresses);
                                $formattedAddress = 'S- ' . implode(',', $numbers);
                            @endphp

                            {{ $formattedAddress }}
                        </td>

                       <td>
                            <span class="badge {{ $group['status_badge'] }} fw-bold px-3 py-2 text-uppercase mt-1 mb-1
                                {{ strtolower($group['status_label']) === 'delay' ? 'blink-badge' : '' }}">
                                
                                {{ $group['status_label'] }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <button 
                                    onclick="showDnList('{{ $group['route'] }}', {{ $group['cycle'] }}, {{ json_encode($group['no_dns']) }})" 
                                    class="btn btn-info btn-sm" 
                                    title="Lihat Daftar DN">
                                    <i class="bi bi-list-ul"></i> {{ $group['dn_count'] }}
                                </button>
                                <button 
                                    onclick="moveGroupToDelivery('{{ $group['route'] }}', {{ $group['cycle'] }}, {{ $group['dn_count'] }})" 
                                    class="btn btn-primary btn-sm btn-action-square" 
                                    title="Move All to Delivery">
                                    <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="mt-3">
                        <td colspan="11" class="text-center py-4">
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
        {{ $groupedShippings->links() }}
    </div>
    
@endsection

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

@push('styles')
<style>
    /* Spin animation on hover */
    .spin-on-hover {
        transition: transform 0.3s ease;
    }
    
    .btn:hover .spin-on-hover {
        transform: rotate(180deg);
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
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

        function performSearch() {
            updateUrl();
        }

        function updateUrl() {
            const url = new URL(window.location.href);
            const perPage = $('#perPageSelect').val();
            const search = $('#searchInput').val();
            
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
            
            window.location.href = url.toString();
        }
    });

    // Show DN List Function
    function showDnList(route, cycle, dnList) {
        let dnListHtml = '<ul class="list-group list-group-flush text-start">';
        dnList.forEach((dn, index) => {
            dnListHtml += `<li class="list-group-item"><strong>${index + 1}.</strong> ${dn}</li>`;
        });
        dnListHtml += '</ul>';

        Swal.fire({
            title: `<strong>Daftar DN</strong>`,
            html: `
                <div class="mb-3">
                    <p class="mb-2"><strong>Route:</strong> ${route}</p>
                    <p class="mb-3"><strong>Cycle:</strong> ${cycle}</p>
                </div>
                ${dnListHtml}
            `,
            width: 600,
            showCloseButton: true,
            showConfirmButton: false,
            customClass: {
                popup: 'text-start'
            }
        });
    }

    // Move Group to Delivery Function
    function moveGroupToDelivery(route, cycle, dnCount) {
        Swal.fire({
            title: 'Pindahkan ke Delivery?',
            html: `
                <div class="text-start">
                    <p><strong>Route:</strong> ${route}</p>
                    <p><strong>Cycle:</strong> ${cycle}</p>
                    <p class="mb-0"><strong>Total DN:</strong> <span class="badge bg-info">${dnCount} item</span></p>
                </div>
                <hr>
                <p class="text-muted mb-0">Semua data DN dalam group ini akan dipindahkan ke Delivery</p>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-arrow-right-circle-fill"></i> Ya, Pindahkan Semua!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Memproses...',
                    html: `Memindahkan ${dnCount} data ke Delivery`,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.ajax({
                    url: '{{ route("shippings.moveToDeliveryByRoute") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        route: route,
                        cycle: cycle
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Berhasil!',
                            html: `
                                <div class="text-start">
                                    <p>${response.message}</p>
                                    <hr>
                                    <p class="mb-1"><strong>Dipindahkan:</strong> <span class="badge bg-success">${response.moved_count} item</span></p>
                                    ${response.skipped_count > 0 ? `<p class="mb-0"><strong>Dilewati:</strong> <span class="badge bg-warning">${response.skipped_count} item</span></p>` : ''}
                                </div>
                            `,
                            icon: 'success',
                            confirmButtonColor: '#198754'
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Gagal!',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan saat memindahkan data',
                            icon: 'error',
                            confirmButtonColor: '#dc2626'
                        });
                    }
                });
            }
        });
    }
</script>
@endpush
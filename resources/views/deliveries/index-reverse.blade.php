@extends('layouts.app')

@section('title', 'Delivery Reverse View')
@section('page-title', 'DELIVERIES MONITORING')
@section('body-class', 'delivery-page')

@section('content')
    <!-- Stats Badges dan Controls -->
    <div class="d-flex justify-content-end align-items-center gap-2 mb-3 mt-3">
        
        <!-- Toggle View Button with Spin Animation -->
        <div class="card border-0 shadow-sm p-1 bg-warning">
            <a href="{{ route('deliveries.index') }}" class="btn btn-warning text-dark" title="Switch to Normal View">
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

        <!-- Statistics Badges -->
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
        <table class="table table-compact w-100 mt-1" id="deliveriesReverseTable">
            <thead>
                <tr class="fs-5">
                    <th>Route</th>
                    <th>LP</th>
                    <th>Customer</th>
                    <th>Dock</th>
                    <th>Scan to Delv</th>
                    <th>Cycle</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th>Detail</th>
                </tr>
            </thead>
            <tbody>
                @forelse($groupedDeliveries as $index => $group)
                    <tr class="fs-4 {{ $group['status'] === 'delay' ? 'table-danger-subtle' : '' }}">
                        <td><strong>{{ $group['route'] }}</strong></td>
                        <td>{{ $group['logistic_partners'] }}</td>
                        <td>{{ Str::limit($group['customers'], 30) }}</td>
                        <td><strong>{{ $group['dock'] }}</strong></td> 
                        <td>{{ $group['scan_to_delivery'] ?? '-' }}</td>
                        <td><strong>{{ $group['cycle'] }}</strong></td>
                        
                        <td>
                            @php
                                $addresses = explode(',', $group['address']);
                                $numbers = array_map(function($addr) {
                                    return trim(str_replace('Shipping', '', $addr));
                                }, $addresses);
                                $formattedAddress = 'D- ' . implode(',', $numbers);
                            @endphp

                            {{ $formattedAddress }}
                        </td>

                        <td>
                            <span class="badge {{ $group['status_badge'] }} fw-bold px-3 py-2 text-uppercase mt-1 mb-1
                                {{ $group['status'] === 'delay' ? 'badge-delay' : '' }}">
                                {{ $group['status_label'] }}
                            </span>
                        </td>

                        <td>
                            <button 
                                onclick="showDnList('{{ $group['route'] }}', {{ $group['cycle'] }}, {{ json_encode($group['no_dns']) }})" 
                                class="btn btn-info btn-sm" 
                                title="Lihat Daftar DN">
                                <i class="bi bi-list-ul"></i> {{ $group['dn_count'] }} DN
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr class="mt-3">
                        <td colspan="9" class="text-center py-4">
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
        {{ $groupedDeliveries->links() }}
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

    /* Delay row highlight */
    .table-danger-subtle {
        background-color: rgba(220, 53, 69, 0.1) !important;
    }

    /* Delay badge animation */
    .badge-delay {
        animation: pulse-delay 1.5s infinite;
    }

    @keyframes pulse-delay {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
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
</script>
@endpush
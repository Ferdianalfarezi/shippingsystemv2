@extends('layouts.andon')

@section('title', 'Andon - Milkrun Monitoring')
@section('page-title', 'MILKRUN MONITORING')
@section('body-class', 'andon-page')

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
    <!-- Stats Badges -->
    <div class="d-flex justify-content-end align-items-center gap-2 mb-3 mt-3">
        
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

        <!-- Advance Badge -->
        <div class="bg-warning card border-0 shadow-sm" id="badgeAdvanceBox">
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

        <!-- On Time Badge -->
        <div class="bg-success card border-0 shadow-sm" id="badgeOnTimeBox">
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
        <div class="bg-danger card border-0 shadow-sm me-3" id="badgeDelayBox">
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

    <!-- Table -->
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
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- STATUS --}}
                        <td>
                            <span class="badge {{ $milkrun->status_badge }} fw-bold px-3 py-2 mt-1 mb-1 {{ $currentStatus === 'delay' ? 'badge-delay-blink' : '' }}"
                                title="{{ $milkrun->time_diff_info }}">
                                {{ $milkrun->status_label }}
                            </span>
                        </td>

                        {{-- DN COUNT --}}
                        <td>
                            <span class="badge bg-info text-dark fw-bold px-3 py-2">
                                {{ $milkrun->dn_count }} DN
                            </span>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="11" class="text-center py-4">
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
@endsection


@push('scripts')
<script>
$(document).ready(function () {

    const REFRESH_INTERVAL = 3000; // 3 detik
    let countdownSeconds = REFRESH_INTERVAL / 1000;

    function startCountdown() {
        countdownSeconds = REFRESH_INTERVAL / 1000;
        $('#countdown').text(countdownSeconds);

        const timer = setInterval(() => {
            countdownSeconds--;
            $('#countdown').text(countdownSeconds);

            if (countdownSeconds <= 0 || document.hidden) {
                clearInterval(timer);
            }
        }, 1000);
    }

    startCountdown();

    // Ajax refresh table only
    setInterval(() => {

        if (!document.hidden) {

            // Refresh TABLE and BADGES
            $("#milkrunsTable").load(location.href + " #milkrunsTable>*");
            $("#badgeAdvanceBox").load(location.href + " #badgeAdvanceBox>*");
            $("#badgeOnTimeBox").load(location.href + " #badgeOnTimeBox>*");
            $("#badgeDelayBox").load(location.href + " #badgeDelayBox>*");

        }

        startCountdown();

    }, REFRESH_INTERVAL);

    // Date Navigation
    $('#prevDateBtn').on('click', function() {
        const currentDate = new Date($('#dateFilter').val());
        currentDate.setDate(currentDate.getDate() - 1);
        $('#dateFilter').val(formatDate(currentDate));
        updateUrl();
    });

    $('#nextDateBtn').on('click', function() {
        const currentDate = new Date($('#dateFilter').val());
        currentDate.setDate(currentDate.getDate() + 1);
        $('#dateFilter').val(formatDate(currentDate));
        updateUrl();
    });

    $('#todayBtn').on('click', function() {
        const today = new Date();
        $('#dateFilter').val(formatDate(today));
        updateUrl();
    });

    $('#dateFilter').on('change', function() {
        updateUrl();
    });

    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function updateUrl() {
        const url = new URL(window.location.href);
        const date = $('#dateFilter').val();
        const today = formatDate(new Date());
        
        // Hanya set date jika bukan hari ini
        if (date && date !== today) {
            url.searchParams.set('date', date);
        } else {
            url.searchParams.delete('date');
        }
        
        window.location.href = url.toString();
    }

});
</script>
@endpush
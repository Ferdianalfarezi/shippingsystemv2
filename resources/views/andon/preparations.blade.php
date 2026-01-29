@extends('layouts.andon')

@section('title', 'Andon - Preparations Monitoring')
@section('page-title', 'PREPARATIONS MONITORING')
@section('body-class', 'andon-page')

@section('content')
    <!-- Stats Badges -->
    <div class="d-flex justify-content-end align-items-center gap-2 mb-3 mt-3">

        {{-- <!-- Auto Refresh Indicator -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="spinner-grow spinner-grow-sm text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <small class="text-muted"> <span id="countdown">3</span>s</small>
                </div>
            </div>
        </div> --}}
        
        <!-- On Time Badge -->
        <div class="bg-success card border-0 shadow-sm" id="badgenormalBox">
            <div class="card-body p-1">
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-10 p-2 rounded me-2">
                        <i class="bi bi-check-circle text-white fs-5"></i>
                    </div>
                    <div>
                        <small class="text-white d-block fw-bold me-3" style="font-size: 0.7rem;">Open</small>
                        <h5 class="mb-0 fw-bold text-white">{{ $totalOnTime }}</h5>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Delay Badge -->
        <div class="bg-danger card border-0 shadow-sm me-3" id="badgedelayBox">
            <div class="card-body p-1">
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-10 p-2 rounded me-2">
                        <i class="bi bi-exclamation-triangle text-white fs-5"></i>
                    </div>
                    <div>
                        <small class="text-white d-block fw-bold me-3" style="font-size: 0.7rem;">Delay</small>
                        <h5 class="mb-0 fw-bold text-white">{{ $totalDelay }}</h5>
                    </div>q 
                </div>
            </div>
        </div>
        
    </div>


    <!-- Recent Scan Display -->
    @if($recentScan)
    <div id="recentScanBox">
        <div class="card border-0 shadow-sm mb-3 ms-3 me-3"
            style="border-radius:0; background-color:#000000; outline:2px solid #ffffff;">
            <div class="card-body py-1 px-4">
                <div class="d-flex align-items-center justify-content-center gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <div>
                            <i class="bi bi-arrow-right-circle-fill text-white fs-6"></i>
                        </div>
                        <small class="text-white fw-semibold" style="font-size: 0.7rem;">RECENT SCAN</small>
                    </div>

                    <div class="vr" style="height: 30px; opacity: 0.2;"></div>

                    <div class="d-flex align-items-center gap-2">
                        <small class="text-white" style="font-size: 1rem;">No DN:</small>
                        <strong class="text-white">{{ $recentScan->no_dn }}</strong>
                    </div>

                    <div class="vr" style="height: 30px; opacity: 0.2;"></div>

                    <div class="d-flex align-items-center gap-2">
                        <small class="text-white" style="font-size: 1rem;">Route:</small>
                        <span class="fw-semibold text-white">{{ $recentScan->route }}</span>
                    </div>

                    <div class="vr" style="height: 30px; opacity: 0.2;"></div>

                    <div class="d-flex align-items-center gap-2">
                        <small class="text-white" style="font-size: 1rem;">Dock:</small>
                        <span class="fw-semibold text-white">{{ $recentScan->dock }}</span>
                    </div>

                    <div class="vr" style="height: 30px; opacity: 0.2;"></div>

                    <div class="d-flex align-items-center gap-2">
                        <small class="text-white" style="font-size: 1rem;">Cycle:</small>
                        <span class="fw-semibold text-white">{{ $recentScan->cycle }}</span>
                    </div>

                    <div class="vr" style="height: 30px; opacity: 0.2;"></div>

                    <div class="d-flex align-items-center gap-2">
                        <small class="text-white" style="font-size: 1rem;">Customer:</small>
                        <span class="fw-semibold text-white">{{ $recentScan->customers }}</span>
                    </div>

                    <div class="vr" style="height: 30px; opacity: 0.2;"></div>

                    <div class="d-flex align-items-center gap-2">
                        <small class="text-white" style="font-size: 1rem;">Scan by:</small>
                        <span class="fw-semibold text-white">
                            <i class="bi bi-person-fill"></i> {{ $recentScan->moved_by ?? 'System' }}
                        </span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="text-white fw-bold">
                            <i class="bi bi-clock-fill"></i> {{ $recentScan->scan_to_shipping->format('H:i:s') }}
                        </span>
                        <span class="text-white fw-bold">{{ $recentScan->scan_to_shipping->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Table -->
    <div class="table-responsive p-0 mt-0">
        <table class="table table-compact w-100 mt-1" id="preparationsTable">
            <thead>
                <tr class="fs-5">
                    <th>Route</th>
                    <th>LP</th>
                    <th>No DN</th>
                    <th>Cust</th>
                    <th>Dock</th>
                    <th>Delv Date</th>
                    <th>Delv Time</th>
                    <th>Cyc</th>
                    <th>Pull Date</th>
                    <th>Finish Pulling</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($preparations as $prep)
                    <tr class="fs-4 {{ $prep->status === 'delay' ? 'table-danger-subtle' : '' }}">
                        <td><strong>{{ $prep->route }}</strong></td>
                        <td>{{ $prep->logistic_partners }}</td>
                        <td>{{ $prep->no_dn }}</td>
                        <td>{{ $prep->customers }}</td>
                        <td><strong>{{ $prep->dock }}</strong></td>
                        <td>{{ $prep->delivery_date->format('d-m-y') }}</td>
                        <td>{{ date('H:i:s', strtotime($prep->delivery_time)) }}</td>
                        <td><strong>{{ $prep->cycle }}</strong></td>
                        <td>{{ $prep->pulling_date->format('d-m-y') }}</td>
                        <td>{{ date('H:i:s', strtotime($prep->pulling_time)) }}</td>
                        <td>
                            <span class="badge {{ $prep->status_badge }} fw-bold px-3 py-2
                                {{ $prep->status === 'delay' ? 'badge-delay' : '' }}"
                                title="{{ $prep->status === 'delay' ? 'Terlambat ' . $prep->delay_duration : 'On Time' }}">
                                {{ $prep->status_label }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr class="mt-3">
                        <td colspan="11" class="text-center py-4">
                            <div class="text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">Belum ada data preparation</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-wrapper">
        {{ $preparations->links() }}
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

            // Refresh TABLE
            $("#preparationsTable").load(location.href + " #preparationsTable>*");
            $("#recentScanBox").load(location.href + " #recentScanBox>*");
            $("#badgenormalBox").load(location.href + " #badgenormalBox>*");
            $("#badgedelayBox").load(location.href + " #badgedelayBox>*");

        }

        startCountdown();

    }, REFRESH_INTERVAL);   

});
</script>
@endpush

@extends('layouts.andon')

@section('title', 'Andon - Delivery Monitoring')
@section('page-title', 'DELIVERY MONITORING')
@section('body-class', 'andon-page')

@section('content')
    <!-- Stats Badges -->
    <div class="d-flex justify-content-end align-items-center gap-2 mb-3 mt-3">

        <!-- Toggle View Button -->
        <div class="card border-0 shadow-sm p-0 bg-warning pt-1 pb-1">
            <a href="{{ route('andon.deliveries.group') }}" class="btn btn-warning" title="Switch to Reverse View">
                <i class="fa-solid fa-box-open fs-4"></i>
            </a>
        </div>  
        
        <!-- Normal Badge -->
        <div class="bg-success card border-0 shadow-sm" id="badgeNormalBox">
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
                            <i class="bi bi-clock-fill"></i> {{ $recentScan->completed_at->format('H:i:s') }}
                        </span>
                        <span class="text-white fw-bold">{{ $recentScan->completed_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Table -->
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
                </tr>
            </thead>
            <tbody>
                @forelse($deliveries as $delivery)
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
                            <span class="badge {{ $delivery->status_badge }} fw-bold px-3 py-2
                                {{ strtolower($delivery->status) === 'delay' ? 'blink-badge' : '' }}">
                                {{ strtoupper($delivery->status_label) }}
                                @if(strtolower($delivery->status) === 'delay' && $delivery->delay_duration)
                                    <small class="ms-0" style="font-size: 1rem;">+ {{ $delivery->delay_duration }}</small>
                                @endif
                            </span>
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
        {{ $deliveries->links() }}
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
            $("#deliveriesTable").load(location.href + " #deliveriesTable>*");
            $("#recentScanBox").load(location.href + " #recentScanBox>*");
            $("#badgeNormalBox").load(location.href + " #badgeNormalBox>*");
            $("#badgeDelayBox").load(location.href + " #badgeDelayBox>*");

        }

        startCountdown();

    }, REFRESH_INTERVAL);   

});
</script>
@endpush
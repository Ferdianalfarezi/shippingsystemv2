@extends('layouts.andon')

@section('title', 'Andon - Shipping Monitoring')
@section('page-title', 'SHIPPING MONITORING')
@section('body-class', 'andon-page')

@section('content')
    <!-- Stats Badges -->
    <div class="d-flex justify-content-end align-items-center gap-2 mb-3 mt-3">

        <!-- Toggle View Button -->
        <div class="card border-0 shadow-sm p-0 bg-warning pt-1 pb-1">
            <a href="{{ route('andon.shippings.group') }}" class="btn btn-warning" title="Switch to Reverse View">
                <i class="fa-solid fa-truck fs-4"></i>
            </a>
        </div>  
        
        <!-- Advance Badge -->
        <div class="bg-warning card border-0 shadow-sm" id="badgeAdvanceBox">
            <div class="card-body p-1">
                <div class="d-flex align-items-center">
                    <div class="bg-dark bg-opacity-10 p-2 rounded me-2">
                        <i class="bi bi-clock-history text-dark fs-5"></i>
                    </div>
                    <div>
                        <small class="text-dark d-block fw-bold me-3" style="font-size: 0.7rem;">Advance</small>
                        <h5 class="mb-0 fw-bold text-dark">{{ $totalAdvance }}</h5>
                    </div>
                </div>
            </div>
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
        <div class="bg-danger card border-0 shadow-sm" id="badgeDelayBox">
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
        <div class="bg-primary card border-0 shadow-sm me-3" id="badgeLoadingBox">
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
                        <small class="text-white fw-semibold" style="font-size: 0.7rem;">RECENT SCAN TO DELIVERY</small>
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
                            <i class="bi bi-clock-fill"></i> {{ $recentScan->scan_to_delivery->format('H:i:s') }}
                        </span>
                        <span class="text-white fw-bold">{{ $recentScan->scan_to_delivery->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Table -->
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
                    <th>Cyc</th>
                    <th>Address</th>
                    <th>Arrival</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shippings as $ship)
                    <tr class="fs-4 {{ $ship->status === 'delay' ? 'table-danger-subtle' : '' }}">
                        <td><strong>{{ $ship->route }}</strong></td>
                        <td>{{ $ship->logistic_partners }}</td>
                        <td>{{ $ship->no_dn }}</td>
                        <td>{{ $ship->customers }}</td>
                        <td><strong>{{ $ship->dock }}</strong></td>
                        <td>{{ $ship->delivery_date->format('d-m-y') }}</td>
                        <td>{{ date('H:i:s', strtotime($ship->delivery_time)) }}</td>
                        <td><strong>{{ $ship->cycle }}</strong></td>
                        <td>{{ $ship->address }}</td>
                        <td>
                            @if($ship->arrival)
                                <span>
                                    {{ $ship->arrival->format('d-m-y H:i') }}
                                </span>
                            @else
                                <span class="text-white">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $ship->status_badge }} fw-bold px-3 py-2
                                {{ in_array($ship->status_label, ['DELAY', 'WARNING']) ? 'blink-badge' : '' }}">
                                {{ $ship->status_label }}
                            </span>

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
        {{ $shippings->links() }}
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
            $("#shippingsTable").load(location.href + " #shippingsTable>*");
            $("#recentScanBox").load(location.href + " #recentScanBox>*");
            $("#badgeAdvanceBox").load(location.href + " #badgeAdvanceBox>*");
            $("#badgeNormalBox").load(location.href + " #badgeNormalBox>*");
            $("#badgeDelayBox").load(location.href + " #badgeDelayBox>*");
            $("#badgeLoadingBox").load(location.href + " #badgeLoadingBox>*");

        }

        startCountdown();

    }, REFRESH_INTERVAL);   

});
</script>
@endpush
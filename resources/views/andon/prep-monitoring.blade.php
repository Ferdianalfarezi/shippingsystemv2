@extends('layouts.andon')

@section('title', 'Andon - Shipping Area Monitoring')
@section('page-title', 'SHIPPING AREA MONITORING')
@section('body-class', 'andon-page')

@section('content')
    <!-- Stats Badges -->
    <div class="d-flex justify-content-end align-items-center gap-2 mb-3 mt-3">

        <!-- On Progress Badge -->
        <div class="bg-warning card border-0 shadow-sm" id="badgeOnProgressBox">
            <div class="card-body p-1">
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-10 p-2 rounded me-2">
                        <i class="bi bi-arrow-repeat text-dark fs-5"></i>
                    </div>
                    <div>
                        <small class="text-dark d-block fw-bold me-3" style="font-size: 0.7rem;">On Progress</small>
                        <h5 class="mb-0 fw-bold text-dark">{{ $totalOnProgress }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Closed Badge -->
        <div class="bg-success card border-0 shadow-sm me-3" id="badgeClosedBox">
            <div class="card-body p-1">
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-10 p-2 rounded me-2">
                        <i class="bi bi-check-circle text-white fs-5"></i>
                    </div>
                    <div>
                        <small class="text-white d-block fw-bold me-3" style="font-size: 0.7rem;">Closed</small>
                        <h5 class="mb-0 fw-bold text-white">{{ $totalClosed }}</h5>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Recent Completed Display -->
    @if($recentCompleted)
    <div id="recentCompletedBox">
        <div class="card border-0 shadow-sm mb-3 ms-3 me-3"
            style="border-radius:0; background-color:#000000; outline:2px solid #ffffff;">
            <div class="card-body py-1 px-4">
                <div class="d-flex align-items-center justify-content-center gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <div>
                            <i class="bi bi-check-circle-fill text-white fs-6"></i>
                        </div>
                        <small class="text-white fw-semibold" style="font-size: 0.7rem;">RECENT COMPLETED</small>
                    </div>

                    <div class="vr" style="height: 30px; opacity: 0.2;"></div>

                    <div class="d-flex align-items-center gap-2">
                        <small class="text-white" style="font-size: 1rem;">Customer:</small>
                        <strong class="text-white">{{ $recentCompleted->customers }}</strong>
                    </div>

                    <div class="vr" style="height: 30px; opacity: 0.2;"></div>

                    <div class="d-flex align-items-center gap-2">
                        <small class="text-white" style="font-size: 1rem;">Cycle:</small>
                        <span class="fw-semibold text-white">{{ $recentCompleted->cycle }}</span>
                    </div>

                    <div class="vr" style="height: 30px; opacity: 0.2;"></div>

                    <div class="d-flex align-items-center gap-2">
                        <small class="text-white" style="font-size: 1rem;">Dock:</small>
                        <span class="fw-semibold text-white">{{ $recentCompleted->dock }}</span>
                    </div>

                    <div class="vr" style="height: 30px; opacity: 0.2;"></div>

                    <div class="d-flex align-items-center gap-2">
                        <small class="text-white" style="font-size: 1rem;">Route:</small>
                        <span class="fw-semibold text-white">{{ $recentCompleted->route }}</span>
                    </div>

                    <div class="vr" style="height: 30px; opacity: 0.2;"></div>

                    <div class="d-flex align-items-center gap-2">
                        <small class="text-white" style="font-size: 1rem;">PR:</small>
                        <span class="fw-semibold text-white">{{ $recentCompleted->pr_number }} ({{ $recentCompleted->skid }}/{{ $recentCompleted->skid }})</span>
                    </div>

                    <div class="vr" style="height: 30px; opacity: 0.2;"></div>

                    <div class="d-flex align-items-center gap-2">
                        <small class="text-white" style="font-size: 1rem;">Ship To:</small>
                        <span class="fw-semibold text-white">{{ $recentCompleted->shipping_address }}</span>
                    </div>

                    @if($recentCompleted->start_ship_at)
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-white fw-bold">
                            <i class="bi bi-clock-fill"></i> {{ $recentCompleted->start_ship_at->format('H:i:s') }}
                        </span>
                        <span class="text-white fw-bold">{{ $recentCompleted->start_ship_at->format('d/m/Y') }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Table -->
    <div class="table-responsive p-0 mt-0">
        <table class="table table-bordered table-compact w-100 mt-1 text-center align-middle" id="monitoringTable">
            <thead>
                <tr class="fs-6">
                    <th rowspan="2">Customer</th>
                    <th rowspan="2">Cycle</th>
                    <th rowspan="2">Dock</th>
                    <th rowspan="2">Route</th>
                    <th colspan="2">QTY</th>
                    <th colspan="3">Time</th>
                    <th colspan="15">Status</th>
                </tr>
                <tr class="fs-6">
                    <th>Kbn</th>
                    <th>Skid</th>
                    <th>Start Prep</th>
                    <th>Start Ship</th>
                    <th>ETD</th>
                    <th>Pr 1</th>
                    <th>Pr 2</th>
                    <th>Pr 3</th>
                    <th style="border-right: 5px solid #ff0000 !important;">Pr 4</th>
                    <th style="border-left: 5px solid #ff0000 !important;">S-1</th>
                    <th>S-2</th>
                    <th>S-3</th>
                    <th>S-4</th>
                    <th>S-5</th>
                    <th>S-6</th>
                    <th>S-7</th>
                    <th>S-8</th>
                    <th>S-9</th>
                    <th>S-10</th>
                    <th>Delivery</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    @php
                        // Kolom S-1..S-10 cuma keisi teksnya kalau shipping_address-nya persis "Shipping N" (1-10).
                        $shipIndex = null;
                        if (preg_match('/^Shipping (\d+)$/i', trim($row->shipping_address), $m)) {
                            $n = (int) $m[1];
                            if ($n >= 1 && $n <= 10) $shipIndex = $n;
                        }
                        $progressText = $row->status === 'ok' ? 'OK' : ($row->pr_count . ' / ' . $row->skid);

                        // Section Pr: hijau kalau OK, kuning (teks hitam) kalau masih progress
                        $prClass = $row->status === 'ok'
                            ? 'bg-success text-white fw-bold'
                            : 'bg-warning text-dark fw-bold';

                        // Section Shipping: MERAH kalau full/OK, kuning (teks hitam) kalau masih progress
                        $shipClass = $row->status === 'ok'
                            ? 'bg-danger text-white fw-bold'
                            : 'bg-warning text-dark fw-bold';
                    @endphp
                    <tr class="fs-6">
                        <td>{{ $row->customers }}</td>
                        <td>{{ $row->cycle }}</td>
                        <td>{{ $row->dock }}</td>
                        <td>{{ $row->route }}</td>
                        <td>{{ $row->kbn ?? '-' }}</td>
                        <td>{{ $row->skid }}</td>
                        <td>{{ $row->start_prep_at ? $row->start_prep_at->format('H:i') : '-' }}</td>
                        <td>{{ $row->start_ship_at ? $row->start_ship_at->format('H:i') : '-' }}</td>
                        <td>{{ $row->etd_time ? date('H:i', strtotime($row->etd_time)) : '-' }}</td>

                        {{-- Seluruh section Pr diwarnai sama (bukan cuma kolom aktif) --}}
                        @for($i = 1; $i <= 4; $i++)
                            <td class="{{ $prClass }}" @if($i === 4) style="border-right: 4px solid #ff0000 !important;" @endif>
                                {{ $row->pr_number == $i ? $progressText : '-' }}
                            </td>
                        @endfor

                        {{-- Seluruh section Shipping diwarnai sama (bukan cuma kolom aktif) --}}
                        @for($i = 1; $i <= 10; $i++)
                            <td class="{{ $shipClass }}" @if($i === 1) style="border-left: 4px solid #000 !important;" @endif>
                                {{ $shipIndex === $i ? $progressText : '-' }}
                            </td>
                        @endfor

                        <td class="{{ $row->milkrun ? $row->milkrun->status_badge . ' fw-bold' : '' }}">
                            {{ $row->milkrun ? $row->milkrun->status_label : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="21" class="text-center py-4">
                            <div class="text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">Belum ada data monitoring</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
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

    // Ajax refresh table & badges only
    setInterval(() => {

        if (!document.hidden) {

            $("#monitoringTable").load(location.href + " #monitoringTable>*");
            $("#recentCompletedBox").load(location.href + " #recentCompletedBox>*");
            $("#badgeOnProgressBox").load(location.href + " #badgeOnProgressBox>*");
            $("#badgeClosedBox").load(location.href + " #badgeClosedBox>*");

        }

        startCountdown();

    }, REFRESH_INTERVAL);

});
</script>
@endpush
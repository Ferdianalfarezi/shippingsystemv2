@extends('layouts.app')

@section('title', 'Monitoring Preparation & Delivery')
@section('page-title', 'SHIPPING AREA MONITORING')
@section('body-class', 'prep-monitoring-page')

@section('content')
    <style>
        .pm-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: .75rem;
            margin: 1rem 0;
        }

        .pm-filter-group {
            display: flex;
            align-items: center;
            gap: .75rem;
            background: #14171c;
            border: 1px solid #262b33;
            border-radius: 10px;
            padding: .4rem .5rem;
        }

        .pm-date-input {
            background: #1b1f26;
            border: 1px solid #2f3540;
            color: #e8eaed;
            border-radius: 7px;
            padding: .35rem .6rem;
            font-size: .85rem;
            width: 150px;
            color-scheme: dark;
        }

        .pm-date-input:focus {
            outline: none;
            border-color: #5b8cff;
            box-shadow: 0 0 0 3px rgba(91, 140, 255, .18);
        }

        .pm-sep {
            color: #6b7280;
            font-size: .75rem;
        }

        .pm-btn-filter {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: #3b6df0;
            border: none;
            color: #fff;
            font-weight: 600;
            font-size: .85rem;
            padding: .4rem .9rem;
            border-radius: 7px;
            white-space: nowrap;
        }

        .pm-btn-filter:hover {
            background: #345fd8;
            color: #fff;
        }

        .pm-btn-reset {
            display: inline-flex;
            align-items: center;
            background: transparent;
            border: 1px solid #2f3540;
            color: #9aa2b1;
            font-size: .8rem;
            padding: .38rem .8rem;
            border-radius: 7px;
            text-decoration: none;
            white-space: nowrap;
        }

        .pm-btn-reset:hover {
            border-color: #5b8cff;
            color: #dfe4ee;
        }

        .pm-date-badge {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            background: #131c16;
            border: 1px solid #2a4a33;
            color: #8fd39a;
            font-size: .82rem;
            font-weight: 600;
            padding: .4rem .8rem;
            border-radius: 20px;
            white-space: nowrap;
        }

        .pm-actions {
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-wrap: wrap;
        }
    </style>

    <div class="pm-toolbar">

        <!-- Filter Tanggal -->
        <div class="d-flex align-items-center flex-wrap gap-2 ms-3">
            <div class="pm-filter-group">
                <form method="GET" action="{{ route('prep-monitoring.index') }}" class="d-flex align-items-center gap-2">
                    <input type="date" name="date_from" class="pm-date-input" value="{{ $dateFrom }}">
                    <span class="pm-sep">s/d</span>
                    <input type="date" name="date_to" class="pm-date-input" value="{{ $dateTo }}">
                    <button type="submit" class="pm-btn-filter">
                        <i class="bi bi-funnel-fill"></i> Tampilkan
                    </button>
                </form>

                <span class="pm-date-badge">
                    @if($dateFrom === $dateTo)
                        <i class="bi bi-calendar-event"></i> {{ \Carbon\Carbon::parse($dateFrom)->translatedFormat('d F Y') }}
                    @else
                        <i class="bi bi-calendar-range"></i> {{ \Carbon\Carbon::parse($dateFrom)->translatedFormat('d M Y') }} &ndash; {{ \Carbon\Carbon::parse($dateTo)->translatedFormat('d M Y') }}
                    @endif
                </span>
            </div>

            @if($dateFrom !== \Carbon\Carbon::today()->format('Y-m-d') || $dateTo !== \Carbon\Carbon::today()->format('Y-m-d'))
                <a href="{{ route('prep-monitoring.index') }}" class="pm-btn-reset">
                    Hari Ini
                </a>
            @endif
        </div>

        <!-- Scan + Badge -->
        <div class="pm-actions">

            <!-- Scan DN Input (dibiarin default/putih) -->
            <div class="input-group" style="width: 280px;">
                <span class="input-group-text bg-white text-white">
                    <i class="bi bi-qr-code-scan text-dark"></i>
                </span>
                <input type="text" class="form-control" id="monitoringScanInput" placeholder="Scan DN..." autofocus>
            </div>

            @if(auth()->user()->role === 'superadmin')
                <div class="card border-0 shadow-sm p-1 bg-danger">
                    <button type="button" class="btn btn-danger" id="deleteAllMonitoringButton" title="Hapus Semua Data">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </div>
            @endif

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
    </div>

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
                                <p class="mt-2">Belum ada data monitoring untuk periode ini</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Modal Pilih PR (cuma muncul buat kombinasi baru) -->
    <div class="modal fade" id="selectPrModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Pilih Preparation (PR)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="selectPrInfo" class="mb-3 small text-muted"></div>
                    <div class="row g-2">
                        <div class="col-3"><button type="button" class="btn btn-outline-primary w-100 pr-select-btn" data-pr="1">PR 1</button></div>
                        <div class="col-3"><button type="button" class="btn btn-outline-primary w-100 pr-select-btn" data-pr="2">PR 2</button></div>
                        <div class="col-3"><button type="button" class="btn btn-outline-primary w-100 pr-select-btn" data-pr="3">PR 3</button></div>
                        <div class="col-3"><button type="button" class="btn btn-outline-primary w-100 pr-select-btn" data-pr="4">PR 4</button></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {

    let pendingNoDn = null;
    let scanTimeout;

    $('#monitoringScanInput').on('input', function () {
        clearTimeout(scanTimeout);
        const noDn = $(this).val().trim();
        if (noDn.length > 0) {
            scanTimeout = setTimeout(function () { checkDn(noDn); }, 500);
        }
    });

    $('#monitoringScanInput').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            clearTimeout(scanTimeout);
            const noDn = $(this).val().trim();
            if (noDn.length > 0) checkDn(noDn);
        }
    });

    function checkDn(noDn) {
        $.ajax({
            url: '{{ route("prep-monitoring.check-dn") }}',
            type: 'GET',
            data: { no_dn: noDn },
            success: function (response) {
                $('#monitoringScanInput').val('');

                if (!response.success) {
                    Swal.fire({
                        title: 'Gagal!',
                        text: response.message,
                        icon: 'error',
                        confirmButtonColor: '#dc2626',
                        timer: 2500,
                        timerProgressBar: true,
                    });
                    $('#monitoringScanInput').focus();
                    return;
                }

                if (response.needs_pr_selection) {
                    pendingNoDn = noDn;
                    const d = response.data;
                    $('#selectPrInfo').html(`
                        <strong>DN:</strong> ${d.no_dn}<br>
                        <strong>Customer:</strong> ${d.customers} | <strong>Cycle:</strong> ${d.cycle} | <strong>Dock:</strong> ${d.dock}<br>
                        <strong>Kbn:</strong> ${d.kbn} &nbsp;|&nbsp; <strong>Skid (target):</strong> ${d.skid} &nbsp;|&nbsp; <strong>Shipping Address:</strong> ${d.shipping_address}
                    `);
                    new bootstrap.Modal(document.getElementById('selectPrModal')).show();
                } else {
                    executeScan(noDn, null);
                }
            },
            error: function (xhr) {
                Swal.fire({
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan',
                    icon: 'error',
                    confirmButtonColor: '#dc2626',
                });
                $('#monitoringScanInput').focus();
            }
        });
    }

    $('.pr-select-btn').on('click', function () {
        const prNumber = $(this).data('pr');
        bootstrap.Modal.getInstance(document.getElementById('selectPrModal')).hide();
        executeScan(pendingNoDn, prNumber);
        pendingNoDn = null;
    });

    function executeScan(noDn, prNumber) {
        $.ajax({
            url: '{{ route("prep-monitoring.scan") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                no_dn: noDn,
                pr_number: prNumber,
            },
            success: function (response) {
                Swal.fire({
                    title: 'Berhasil!',
                    text: response.message,
                    icon: 'success',
                    confirmButtonColor: '#059669',
                    timer: 2000,
                    timerProgressBar: true,
                }).then(() => window.location.reload());
            },
            error: function (xhr) {
                Swal.fire({
                    title: 'Gagal!',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan saat memproses scan',
                    icon: 'error',
                    confirmButtonColor: '#dc2626',
                });
                $('#monitoringScanInput').focus();
            }
        });
    }

    $('#deleteAllMonitoringButton').on('click', function () {
        Swal.fire({
            title: 'PERINGATAN!',
            text: 'Hapus SEMUA data monitoring? Tindakan ini tidak dapat dibatalkan!',
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus Semua!',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("prep-monitoring.delete-all") }}',
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (response) {
                        Swal.fire({ title: 'Berhasil!', text: response.message, icon: 'success' })
                            .then(() => window.location.reload());
                    },
                    error: function () {
                        Swal.fire({ title: 'Gagal!', text: 'Terjadi kesalahan', icon: 'error' });
                    }
                });
            }
        });
    });

});
</script>
@endpush
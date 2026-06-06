@extends('layouts.app')

@section('title', 'Data Kanban HPM')
@section('page-title', 'KANBAN HPM')
@section('body-class', 'kanbanhpms-page')

@section('content')

    <!-- Top Bar -->
    <div class="d-flex justify-content-between align-items-center gap-2 mb-3 mt-3">

        <!-- Left: Last Upload Info -->
        <div class="d-flex align-items-center gap-2 ms-3">
            @if($latestUploadInfo)
                <strong>Last Upload:</strong> {{ $latestUploadInfo->last_upload_at }}
                by <strong>{{ $latestUploadInfo->uploaded_by }}</strong>
                @if($latestDate)
                    &nbsp;|&nbsp; <span class="badge bg-primary">Tanggal: {{ $latestDate }}</span>
                @endif
            @else
                <span class="text-muted">Belum ada data diimport</span>
            @endif
        </div>

        <!-- Right: Controls -->
        <div class="d-flex align-items-center gap-2">

            <a href="{{ route('sliphpms.index') }}" class="btn btn-success">
                <i class="bi bi-file-earmark-text me-1"></i> Slip
            </a>

            <button type="button" class="btn btn-primary"
                    data-bs-toggle="modal" data-bs-target="#printFilterModal">
                <i class="bi bi-printer me-1"></i> Print
            </button>

            <!-- Search Bar -->
            <div class="input-group" style="width: 280px;">
                <input type="text" class="form-control" id="searchInput" placeholder="Cari Part No, DI No...">
                <button class="btn btn-secondary" type="button" id="searchBtn">
                    <i class="bi bi-search"></i>
                </button>
            </div>

            <!-- Dropdown Menu -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-2">
                    <div class="dropdown">
                        <button class="btn btn-link text-dark p-0 m-0" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false"
                                style="text-decoration: none;">
                            <i class="bi bi-three-dots-vertical fs-4"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li>
                                <a class="dropdown-item text-primary" href="#"
                                   data-bs-toggle="modal" data-bs-target="#importTxtModal">
                                    <i class="bi bi-file-earmark-text text-primary me-2"></i> Import TXT
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-success" href="#"
                                   data-bs-toggle="modal" data-bs-target="#adjustWeeklyModal">
                                    <i class="bi bi-calendar-check text-success me-2"></i> Adjust Weekly
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Total Badge -->
            <div class="bg-primary card border-0 shadow-sm me-2">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-10 p-2 rounded me-2">
                            <i class="bi bi-box-seam text-white fs-5"></i>
                        </div>
                        <div>
                            <small class="text-white d-block fw-bold me-3" style="font-size: 0.7rem;">Total</small>
                            <h5 class="mb-0 fw-bold text-white">{{ $totalAll }}</h5>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Table -->
    <div class="table-responsive p-0 mt-0">
        <table class="table table-compact w-100 mt-1" id="kanbanhpmsTable">
            <thead>
                <tr class="fs-6">
                    <th>DI No</th>
                    <th>Seq</th>
                    <th>Part No</th>
                    <th>Part Name</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Inv Cty</th>
                    <th>PS Code</th>
                    <th>Seq No</th>
                    <th>KD Lot No</th>
                    <th>Datetime</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kanbanhpms as $item)
                    <tr class="fs-6">
                        <td><strong>{{ $item->di_no }}</strong></td>
                        <td>{{ $item->item_seq }}</td>
                        <td>{{ $item->part_no }}</td>
                        <td>{{ Str::limit($item->part_name, 30) }}</td>
                        <td>{{ $item->from }}</td>
                        <td>{{ $item->to }}</td>
                        <td>{{ $item->inventory_category }}</td>
                        <td>{{ $item->ps_code }}</td>
                        <td>{{ $item->seq_no }}</td>
                        <td>{{ $item->kd_lot_no }}</td>
                        <td>{{ $item->datetime }}</td>
                        <td>
                            <form action="{{ route('kanbanhpms.destroy', $item->id) }}"
                                  method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="btn btn-danger btn-sm"
                                        style="border-radius: 6px;"
                                        title="Hapus">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center py-4">
                            <div class="text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">Belum ada data kanban HPM. Silakan import file TXT.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Server-side Pagination -->
    <div class="d-flex justify-content-end mt-3 me-3">
        {{ $kanbanhpms->links('pagination::bootstrap-5') }}
    </div>


    {{-- ==================== MODAL: Import TXT ==================== --}}
    <div class="modal fade" id="importTxtModal" tabindex="-1" aria-labelledby="importTxtModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-dark" id="importTxtModalLabel">
                        <i class="bi bi-file-earmark-text me-2 text-dark"></i>Import TXT File (HPM)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('kanbanhpms.import') }}" method="POST"
                      enctype="multipart/form-data" id="importTxtForm">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Perhatian!</strong> Jangan Import data yang sama 2x. jika dilakukan maka kanban yang di cetak akan duplikat
                        </div>
                        <div class="mb-3">
                            <label for="txtFile" class="form-label text-dark">Pilih File TXT</label>
                            <input type="file" class="form-control" id="txtFile"
                                   name="file" accept=".txt" required>
                            <div class="form-text">Format: .txt HPM fixed-width (max 5MB)</div>
                        </div>
                        <div class="progress d-none" id="importProgress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                 role="progressbar" style="width: 100%"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="importButton">
                            <i class="bi bi-upload me-2"></i>Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- ==================== MODAL: Adjust Weekly ==================== --}}
    <div class="modal fade" id="adjustWeeklyModal" tabindex="-1"
         aria-labelledby="adjustWeeklyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-dark" id="adjustWeeklyModalLabel">
                        <i class="bi bi-calendar-check me-2 text-success"></i>Adjust Weekly
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('kanbanhpms.adjustWeekly') }}" method="POST"
                      enctype="multipart/form-data" id="adjustWeeklyForm">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info d-flex align-items-start gap-2 mb-4">
                            <i class="bi bi-info-circle-fill mt-1 flex-shrink-0"></i>
                            <div>
                                <strong>Adjust Weekly</strong> akan mengupdate kolom <strong>Datetime</strong>
                                berdasarkan kolom <em>Adjustment Delivery Schedule Ship Date</em> dan
                                <em>Adjustment Delivery Schedule Ship Time</em> dari file Excel, dicocokkan lewat
                                <strong>KD Lot Number</strong>.<br>
                                <small class="text-muted">
                                    Jika kolom Adjustment kosong di Excel, datetime tidak akan diubah (tetap dari TXT).
                                </small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">
                                <i class="bi bi-file-earmark-excel text-success me-1"></i>
                                File Weekly
                            </label>
                            <input type="file" class="form-control" id="fileWeekly"
                                   name="file_weekly" accept=".xlsx,.xls" required>
                            <div class="form-text">
                                Format: 1 file Excel &mdash; kolom <strong>KD Lot Number</strong>,
                                <strong>Adjustment Delivery Schedule Ship Date</strong> &amp;
                                <strong>Adjustment Delivery Schedule Ship Time</strong>
                            </div>
                            <div id="fileWeeklyPreview" class="mt-2 d-none">
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="bi bi-check-circle me-1"></i>
                                    <span id="fileWeeklyName"></span>
                                </span>
                            </div>
                        </div>
                        <div class="progress d-none mt-3" id="adjustWeeklyProgress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                 role="progressbar" style="width: 100%"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg me-1"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-success" id="adjustWeeklyButton">
                            <i class="bi bi-calendar-check me-2"></i>Proses Adjust
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- ==================== MODAL: Print Filter ==================== --}}
    <div class="modal fade" id="printFilterModal" tabindex="-1"
         aria-labelledby="printFilterModalLabel" aria-hidden="true">
        <div class="modal-dialog" style="max-width: 1000px; width: 95vw;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-dark" id="printFilterModalLabel">
                        <i class="bi bi-printer me-2 text-primary"></i>Print Kanban HPM
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-dark p-0">
                    <div class="d-flex" style="min-height: 75vh;">

                        {{-- ── LEFT: Filter Panel ── --}}
                        <div class="flex-shrink-0 border-end p-3" style="width: 240px; background: #f8f9fa;">

                            {{-- Loading filter --}}
                            <div id="filterLoadingSpinner" class="text-center py-4">
                                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                <p class="small text-muted mt-2">Memuat filter...</p>
                            </div>

                            <div id="filterContent" class="d-none">

                                {{-- Filter Tanggal --}}
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-calendar3 text-primary me-1"></i>Tanggal
                                    </label>
                                    <div class="border rounded bg-white p-2" style="max-height: 180px; overflow-y: auto;">
                                        <div class="form-check mb-1">
                                            <input class="form-check-input" type="checkbox"
                                                   id="checkAllDates" onchange="toggleAllDates(this)">
                                            <label class="form-check-label fw-semibold small" for="checkAllDates">
                                                Semua Tanggal
                                            </label>
                                        </div>
                                        <hr class="my-1">
                                        <div id="dateCheckList"></div>
                                    </div>
                                </div>

                                <hr>

                                {{-- Filter Dock --}}
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-signpost-split text-success me-1"></i>Dock
                                    </label>
                                    <div class="border rounded bg-white p-2" style="max-height: 180px; overflow-y: auto;">
                                        <div class="form-check mb-1">
                                            <input class="form-check-input" type="checkbox"
                                                   id="checkAllDocks" onchange="toggleAllDocks(this)">
                                            <label class="form-check-label fw-semibold small" for="checkAllDocks">
                                                Semua Dock
                                            </label>
                                        </div>
                                        <hr class="my-1">
                                        <div id="dockCheckList"></div>
                                    </div>
                                </div>

                                <hr>

                                {{-- Preview Count --}}
                                <div class="p-2 bg-white border rounded text-center">
                                    <div class="text-muted small">Akan diprint</div>
                                    <div class="fw-bold fs-4 text-primary" id="printPreviewCount">0</div>
                                    <div class="text-muted small">kanban</div>
                                </div>

                            </div>
                        </div>

                        {{-- ── RIGHT: Preview Iframe ── --}}
                        <div class="flex-grow-1 p-0">
                            <div style="background: #e9ecef; height: 75vh; overflow: hidden; position: relative;">

                                <div id="hpmPreviewLoading" class="text-center py-5 d-none"
                                     style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="mt-3 text-dark">Memuat preview...</p>
                                </div>

                                <div id="hpmPreviewEmpty"
                                     class="text-center text-muted py-5"
                                     style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                                    <i class="bi bi-funnel" style="font-size: 4rem; opacity: 0.4;"></i>
                                    <p class="mt-3">Pilih filter untuk melihat preview</p>
                                </div>

                                <iframe id="hpmPrintPreviewIframe"
                                        style="width: 100%; height: 100%; border: none; display: none;"></iframe>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Tutup
                    </button>
                    <button type="button" class="btn btn-primary" id="hpmDoPrintBtn" disabled
                            onclick="triggerHpmPrint()">
                        <i class="bi bi-printer-fill me-2"></i>Print
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

@push('scripts')
<script>
$(document).ready(function () {

    // ===================== DELETE =====================
    $(document).on('submit', '.delete-form', function (e) {
        e.preventDefault();
        const form = $(this);
        const url  = form.attr('action');

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: 'Data ini akan dihapus permanen!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menghapus...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading(),
                });

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                    success: function (res) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: res.message || 'Data berhasil dihapus',
                            icon: 'success',
                            confirmButtonColor: '#059669',
                        }).then(() => window.location.reload());
                    },
                    error: function (xhr) {
                        Swal.fire({
                            title: 'Gagal!',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan',
                            icon: 'error',
                            confirmButtonColor: '#dc2626',
                        });
                    },
                });
            }
        });
    });

    // ===================== IMPORT TXT =====================
    $('#importTxtForm').on('submit', function (e) {
        e.preventDefault();
        const fileInput = $('#txtFile')[0];

        if (!fileInput.files.length) {
            Swal.fire({ title: 'Error!', text: 'Pilih file TXT terlebih dahulu', icon: 'error' });
            return;
        }

        Swal.fire({
            title: 'Import Data?',
            text: 'Semua data yang ada akan dihapus dan diganti dengan data baru!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Import!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                $('#importProgress').removeClass('d-none');
                $('#importButton').prop('disabled', true);

                const formData = new FormData($('#importTxtForm')[0]);
                $.ajax({
                    url: '{{ route("kanbanhpms.import") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function () {
                        $('#importProgress').addClass('d-none');
                        $('#importButton').prop('disabled', false);
                        $('#importTxtModal').modal('hide');
                        Swal.fire({
                            title: 'Berhasil!',
                            text: 'Data berhasil diimport.',
                            icon: 'success',
                            confirmButtonColor: '#3085d6',
                        }).then(() => window.location.reload());
                    },
                    error: function (xhr) {
                        $('#importProgress').addClass('d-none');
                        $('#importButton').prop('disabled', false);
                        Swal.fire({
                            title: 'Gagal!',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan saat import',
                            icon: 'error',
                        });
                    },
                });
            }
        });
    });

    $('#importTxtModal').on('hidden.bs.modal', function () {
        $('#txtFile').val('');
        $('#importProgress').addClass('d-none');
        $('#importButton').prop('disabled', false);
    });

    // ===================== ADJUST WEEKLY =====================
    $('#fileWeekly').on('change', function () {
        const file = this.files[0];
        if (file) {
            $('#fileWeeklyName').text(file.name);
            $('#fileWeeklyPreview').removeClass('d-none');
        } else {
            $('#fileWeeklyPreview').addClass('d-none');
        }
    });

    $('#adjustWeeklyModal').on('hidden.bs.modal', function () {
        $('#fileWeekly').val('');
        $('#fileWeeklyPreview').addClass('d-none');
        $('#adjustWeeklyProgress').addClass('d-none');
        $('#adjustWeeklyButton').prop('disabled', false);
    });

    $('#adjustWeeklyForm').on('submit', function (e) {
        e.preventDefault();

        if ($('#fileWeekly')[0].files.length === 0) {
            Swal.fire({
                title: 'Perhatian!',
                text: 'Pilih file Excel Weekly terlebih dahulu.',
                icon: 'warning',
            });
            return;
        }

        Swal.fire({
            title: 'Proses Adjust Weekly?',
            html: 'Datetime pada data yang match dengan KD Lot Number di Excel akan diupdate.<br>' +
                  '<small class="text-muted">Data yang tidak ada di Excel tidak akan berubah.</small>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-calendar-check me-1"></i> Ya, Proses!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                $('#adjustWeeklyProgress').removeClass('d-none');
                $('#adjustWeeklyButton').prop('disabled', true);
                $('#adjustWeeklyForm')[0].submit();
            }
        });
    });

    // ===================== SWEET ALERT SESSION =====================
    @if(session('sweet_alert'))
        Swal.fire({
            icon: '{{ session("sweet_alert.type") }}',
            title: '{{ session("sweet_alert.title") }}',
            text: '{{ session("sweet_alert.text") }}',
            showConfirmButton: {{ session("sweet_alert.showConfirmButton") ? 'true' : 'false' }},
            timer: {{ session("sweet_alert.timer") ?? 'null' }},
        });
    @endif

});


// ===================== PRINT FILTER =====================

let hpmFilterData   = { dates: [], docks: [] };
let hpmPreviewDebounce = null;

// Buka modal → fetch filter options via AJAX
document.getElementById('printFilterModal').addEventListener('show.bs.modal', function () {
    // Reset state
    document.getElementById('filterLoadingSpinner').classList.remove('d-none');
    document.getElementById('filterContent').classList.add('d-none');
    document.getElementById('hpmPreviewLoading').classList.add('d-none');
    document.getElementById('hpmPreviewEmpty').classList.remove('d-none');
    document.getElementById('hpmPrintPreviewIframe').style.display = 'none';
    document.getElementById('hpmPrintPreviewIframe').src = 'about:blank';
    document.getElementById('hpmDoPrintBtn').disabled = true;
    document.getElementById('printPreviewCount').textContent = '0';

    // Fetch filter options
    fetch('{{ route("kanbanhpms.filterOptions") }}')
        .then(r => r.json())
        .then(data => {
            hpmFilterData = data;
            renderDateChecks(data.dates);
            renderDockChecks(data.docks);

            document.getElementById('filterLoadingSpinner').classList.add('d-none');
            document.getElementById('filterContent').classList.remove('d-none');
        })
        .catch(() => {
            document.getElementById('filterLoadingSpinner').innerHTML =
                '<p class="text-danger small">Gagal memuat filter.</p>';
        });
});

// Tutup modal → reset iframe
document.getElementById('printFilterModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('hpmPrintPreviewIframe').src = 'about:blank';
});

function renderDateChecks(dates) {
    const container = document.getElementById('dateCheckList');
    if (!dates.length) {
        container.innerHTML = '<span class="text-muted small">Tidak ada tanggal</span>';
        return;
    }
    container.innerHTML = dates.map((d, i) => `
        <div class="form-check">
            <input class="form-check-input date-check" type="checkbox"
                   value="${d}" id="date_${i}" onchange="onHpmFilterChange()">
            <label class="form-check-label small" for="date_${i}">${d}</label>
        </div>
    `).join('');
}

function renderDockChecks(docks) {
    const container = document.getElementById('dockCheckList');
    if (!docks.length) {
        container.innerHTML = '<span class="text-muted small">Tidak ada dock</span>';
        return;
    }
    container.innerHTML = docks.map((d, i) => `
        <div class="form-check">
            <input class="form-check-input dock-check" type="checkbox"
                   value="${d}" id="dock_${i}" onchange="onHpmFilterChange()">
            <label class="form-check-label small" for="dock_${i}">Dock ${d}</label>
        </div>
    `).join('');
}

function toggleAllDates(cb) {
    document.querySelectorAll('.date-check').forEach(c => c.checked = cb.checked);
    onHpmFilterChange();
}
function toggleAllDocks(cb) {
    document.querySelectorAll('.dock-check').forEach(c => c.checked = cb.checked);
    onHpmFilterChange();
}

function getSelectedDates() {
    return [...document.querySelectorAll('.date-check:checked')].map(c => c.value);
}
function getSelectedDocks() {
    return [...document.querySelectorAll('.dock-check:checked')].map(c => c.value);
}

function onHpmFilterChange() {
    const selDates = getSelectedDates();
    const selDocks = getSelectedDocks();

    // Sync "semua" checkboxes
    const allDates    = document.querySelectorAll('.date-check');
    const allDocks    = document.querySelectorAll('.dock-check');
    document.getElementById('checkAllDates').checked = allDates.length > 0 && allDates.length === selDates.length;
    document.getElementById('checkAllDocks').checked = allDocks.length > 0 && allDocks.length === selDocks.length;

    // Hitung preview count dari data yg di-fetch
    // (estimasi client-side berdasarkan filter date+dock)
    // Karena filterOptions hanya return dates & docks (bukan per-item),
    // kita tampilkan "?" dan biarkan server yang hitung via iframe
    const hasFilter = selDates.length > 0 || selDocks.length > 0;
    document.getElementById('printPreviewCount').textContent = hasFilter ? '...' : '0';

    if (!hasFilter) {
        document.getElementById('hpmPreviewLoading').classList.add('d-none');
        document.getElementById('hpmPreviewEmpty').classList.remove('d-none');
        document.getElementById('hpmPrintPreviewIframe').style.display = 'none';
        document.getElementById('hpmDoPrintBtn').disabled = true;
        return;
    }

    // Debounce load preview
    clearTimeout(hpmPreviewDebounce);
    hpmPreviewDebounce = setTimeout(loadHpmPreview, 500);
}

function loadHpmPreview() {
    const selDates = getSelectedDates();
    const selDocks = getSelectedDocks();

    document.getElementById('hpmPreviewLoading').classList.remove('d-none');
    document.getElementById('hpmPreviewEmpty').classList.add('d-none');
    document.getElementById('hpmPrintPreviewIframe').style.display = 'none';
    document.getElementById('hpmDoPrintBtn').disabled = true;

    // Build GET params
    const params = new URLSearchParams();
    selDates.forEach(d => params.append('dates[]', d));
    selDocks.forEach(d => params.append('docks[]', d));
    params.append('_token', '{{ csrf_token() }}');

    const url = '{{ route("kanbanhpms.printFiltered") }}?' + params.toString();
    const iframe = document.getElementById('hpmPrintPreviewIframe');

    iframe.onload = function () {
        document.getElementById('hpmPreviewLoading').classList.add('d-none');
        iframe.style.display = 'block';
        document.getElementById('hpmDoPrintBtn').disabled = false;

        // Hitung jumlah kanban dari iframe (frame-1 = 1 kanban)
        try {
            const count = iframe.contentDocument.querySelectorAll('.frame-1').length;
            document.getElementById('printPreviewCount').textContent = count;
        } catch(e) {
            document.getElementById('printPreviewCount').textContent = '✓';
        }
    };
    iframe.src = url;
}

function triggerHpmPrint() {
    const iframe = document.getElementById('hpmPrintPreviewIframe');
    if (iframe && iframe.contentWindow) {
        iframe.contentWindow.print();
    }
}
</script>
@endpush
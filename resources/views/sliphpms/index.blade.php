@extends('layouts.app')

@section('title', 'Data Slip HPM')
@section('page-title', 'SLIP HPM')
@section('body-class', 'sliphpms-page')

@section('content')

    <!-- Top Bar -->
    <div class="d-flex justify-content-between align-items-center gap-2 mb-3 mt-3">

        <!-- Left: Last Upload Info -->
        <div class="d-flex align-items-center gap-2 ms-3">
            @if($latestUploadInfo)
                <strong>Last Upload:</strong> {{ $latestUploadInfo->last_upload_at }}
                by <strong>{{ $latestUploadInfo->uploaded_by }}</strong>
            @else
                <span class="text-muted">Belum ada data diimport</span>
            @endif
        </div>

        <!-- Right: Controls -->
        <div class="d-flex align-items-center gap-2">

            <a href="{{ route('kanbanhpms.index') }}" class="btn btn-success">
                <i class="bi bi-file-earmark-text me-1"></i> Kanban
            </a>

            <button type="button" class="btn btn-primary"
                    data-bs-toggle="modal" data-bs-target="#printFilterModal">
                <i class="bi bi-printer me-1"></i> Print
            </button>

            <!-- Supply Address Filter -->
            <select class="form-select" id="supplyFilterSelect" style="width: 150px;">
                <option value="all">All Supply</option>
                @foreach($sliphpms->pluck('supply_address')->unique()->filter()->sort() as $sa)
                    <option value="{{ $sa }}">{{ $sa }}</option>
                @endforeach
            </select>

            <!-- Search -->
            <div class="input-group" style="width: 280px;">
                <input type="text" class="form-control" id="searchInput" placeholder="Cari Part No, DI No...">
                <button class="btn btn-secondary" type="button" id="searchButton">
                    <i class="bi bi-search"></i>
                </button>
            </div>

            <!-- Per Page -->
            <select class="form-select" id="perPageSelect" style="width: 85px;">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="all">All</option>
            </select>

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
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Total Badge -->
            <div class="bg-primary card border-0 shadow-sm me-2">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-10 p-2 rounded me-2">
                            <i class="bi bi-file-earmark-text text-white fs-5"></i>
                        </div>
                        <div>
                            <small class="text-white d-block fw-bold me-3" style="font-size: 0.7rem;">Total</small>
                            <h5 class="mb-0 fw-bold text-white">{{ count($sliphpms) }}</h5>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Table -->
    <div class="table-responsive p-0 mt-0">
        <table class="table table-compact w-100 mt-1" id="sliphpmsTable">
            <thead>
                <tr class="fs-6">
                    <th style="width: 40px;"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th>DI No</th>
                    <th>Part No</th>
                    <th>Part Name</th>
                    <th>From</th>
                    <th>To</th>
                    <th>PS Code</th>
                    <th>Supply Adr</th>
                    <th>KD Lot No</th>
                   
                    <th>Ms ID</th>
                    <th>Qty</th>
                    <th>Datetime</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @forelse($sliphpms as $item)
                    <tr class="fs-6 slip-row"
                        data-supply="{{ $item->supply_address }}"
                        data-id="{{ $item->id }}">
                        <td><input type="checkbox" class="form-check-input row-select" value="{{ $item->id }}"></td>
                        <td><strong>{{ $item->di_no }}</strong></td>
                        <td>{{ $item->part_no }}</td>
                        <td>{{ Str::limit($item->part_name, 30) }}</td>
                        <td>{{ $item->from }}</td>
                        <td>{{ $item->to }}</td>
                        <td>{{ $item->ps_code }}</td>
                        <td>{{ $item->supply_address }}</td>
                        <td>{{ $item->kd_lot_no }}</td>
                        <td>{{ $item->ms_id}}</td>
                        <td>{{ $item->qty }}</td>
                        <td>{{ $item->datetime }}</td>
                        <td>
                            <form action="{{ route('sliphpms.destroy', $item->id) }}"
                                  method="POST" class="d-inline delete-form">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"
                                        style="border-radius: 6px;" title="Hapus">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="13" class="text-center py-4">
                            <div class="text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">Belum ada data Slip HPM. Silakan import file TXT.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-3 me-3" id="paginationContainer">
        <div class="text-muted" id="paginationInfo">
            Showing <span id="showingFrom">1</span> to
            <span id="showingTo">10</span> of
            <span id="totalFiltered">{{ count($sliphpms) }}</span> entries
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination mb-0" id="paginationNav"></ul>
        </nav>
    </div>


    {{-- ==================== MODAL: Import TXT ==================== --}}
    <div class="modal fade" id="importTxtModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-file-earmark-text me-2 text-dark"></i>Import TXT File (Slip HPM)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('sliphpms.import') }}" method="POST"
                      enctype="multipart/form-data" id="importTxtForm">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Perhatian!</strong> Import akan menghapus semua data yang ada
                            dan menggantinya dengan data baru dari file TXT.
                        </div>
                        <div class="mb-3">
                            <label for="txtFile" class="form-label text-dark">Pilih File TXT (Slip HPM)</label>
                            <input type="file" class="form-control" id="txtFile"
                                   name="file" accept=".txt" required>
                            <div class="form-text">Format: .txt Slip HPM fixed-width (max 10MB)</div>
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


    {{-- ==================== MODAL: Print Filter ==================== --}}
    <div class="modal fade" id="printFilterModal" tabindex="-2" aria-hidden="true">
        <div class="modal-dialog" style="max-width: 11000px; width: 95vw;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-printer me-2 text-primary"></i>Print Slip HPM
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-dark p-0">
                    <div class="d-flex" style="min-height: 75vh;">

                        {{-- ── LEFT: Filter Panel ── --}}
                        <div class="flex-shrink-0 border-end p-3" style="width: 240px; background: #f8f9fa;">

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
                                    @php
                                        $uniqueDates = $sliphpms
                                            ->filter(fn($i) => !empty($i->datetime))
                                            ->map(fn($i) => explode(' ', trim($i->datetime))[0])
                                            ->unique()->sort()->values();
                                    @endphp
                                    @forelse($uniqueDates as $date)
                                        <div class="form-check">
                                            <input class="form-check-input date-check" type="checkbox"
                                                   value="{{ $date }}" id="date_{{ $loop->index }}">
                                            <label class="form-check-label small" for="date_{{ $loop->index }}">
                                                {{ $date }}
                                            </label>
                                        </div>
                                    @empty
                                        <span class="text-muted small">Tidak ada tanggal</span>
                                    @endforelse
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
                                    @php
                                        $uniqueDocks = $sliphpms
                                            ->filter(fn($i) => !empty($i->supply_address))
                                            ->pluck('supply_address')
                                            ->unique()->sort()->values();
                                    @endphp
                                    @forelse($uniqueDocks as $dock)
                                        <div class="form-check">
                                            <input class="form-check-input dock-check" type="checkbox"
                                                   value="{{ $dock }}" id="dock_{{ $loop->index }}">
                                            <label class="form-check-label small" for="dock_{{ $loop->index }}">
                                                {{ $dock }}
                                            </label>
                                        </div>
                                    @empty
                                        <span class="text-muted small">Tidak ada dock</span>
                                    @endforelse
                                </div>
                            </div>

                            <hr>

                            {{-- Preview Count --}}
                            <div class="p-2 bg-white border rounded text-center">
                                <div class="text-muted small">Akan diprint</div>
                                <div class="fw-bold fs-4 text-primary" id="printPreviewCount">{{ count($sliphpms) }}</div>
                                <div class="text-muted small">slip</div>
                            </div>

                        </div>

                        {{-- ── RIGHT: Preview Iframe ── --}}
                        <div class="flex-grow-1 p-0">
                            <div style="background: #e9ecef; height: 75vh; overflow: hidden; position: relative;">
                                <div id="slipPreviewLoading" class="text-center py-5 d-none"
                                     style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="mt-3 text-dark">Memuat preview...</p>
                                </div>
                                <div id="slipPreviewEmpty"
                                     class="text-center text-muted py-5"
                                     style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                                    <i class="bi bi-funnel" style="font-size: 4rem; opacity: 0.4;"></i>
                                    <p class="mt-3">Pilih filter untuk melihat preview</p>
                                </div>
                                <iframe id="slipPrintPreviewIframe"
                                        style="width: 100%; height: 100%; border: none; display: none;"></iframe>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Tutup
                    </button>
                    <button type="button" class="btn btn-primary" id="slipDoPrintBtn" disabled
                            onclick="triggerSlipPrint()">
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

    // ===================== PAGINATION =====================
    let currentPage = 1;
    let perPage = 10;
    let allRows = [];
    let filteredRows = [];

    function initRows() {
        allRows = $('.slip-row').not('.empty-row').toArray();
        filteredRows = [...allRows];
    }
    initRows();

    function applyPagination() {
        $(allRows).hide();
        if (perPage === 'all') {
            $(filteredRows).show();
            updatePaginationInfo(1, filteredRows.length, filteredRows.length);
            renderPagination(1, 1);
        } else {
            const totalPages = Math.ceil(filteredRows.length / perPage);
            if (currentPage > totalPages) currentPage = totalPages || 1;
            const start = (currentPage - 1) * perPage;
            const end   = start + perPage;
            filteredRows.slice(start, end).forEach(row => $(row).show());
            updatePaginationInfo(start + 1, Math.min(end, filteredRows.length), filteredRows.length);
            renderPagination(currentPage, totalPages);
        }
    }

    function updatePaginationInfo(from, to, total) {
        if (total === 0) {
            $('#paginationInfo').html('No entries found');
        } else {
            $('#showingFrom').text(from);
            $('#showingTo').text(to);
            $('#totalFiltered').text(total);
        }
    }

    function renderPagination(current, total) {
        if (total <= 1) { $('#paginationNav').html(''); return; }
        let html = `<li class="page-item ${current === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${current - 1}">«</a></li>`;
        let start = Math.max(1, current - 2);
        let end   = Math.min(total, current + 2);
        if (start > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
            if (start > 2) html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
        }
        for (let i = start; i <= end; i++) {
            html += `<li class="page-item ${i === current ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
        }
        if (end < total) {
            if (end < total - 1) html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
            html += `<li class="page-item"><a class="page-link" href="#" data-page="${total}">${total}</a></li>`;
        }
        html += `<li class="page-item ${current === total ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${current + 1}">»</a></li>`;
        $('#paginationNav').html(html);
    }

    $(document).on('click', '#paginationNav .page-link', function (e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page && !$(this).parent().hasClass('disabled')) {
            currentPage = page; applyPagination();
        }
    });

    $('#perPageSelect').on('change', function () {
        const val = $(this).val();
        perPage = val === 'all' ? 'all' : parseInt(val);
        currentPage = 1; applyPagination();
    });

    // ===================== FILTER =====================
    function filterTable() {
        const search       = $('#searchInput').val().toLowerCase();
        const supplyFilter = $('#supplyFilterSelect').val();
        filteredRows = allRows.filter(function (row) {
            const $row   = $(row);
            const supply = String($row.data('supply'));
            const text   = $row.text().toLowerCase();
            return (search === '' || text.includes(search)) &&
                   (supplyFilter === 'all' || supply === supplyFilter);
        });
        currentPage = 1; applyPagination();
        $('#selectAll').prop('checked', false);
    }

    $('#searchButton').on('click', filterTable);
    $('#searchInput').on('input', filterTable);
    $('#searchInput').on('keypress', function (e) { if (e.which === 13) filterTable(); });
    $('#supplyFilterSelect').on('change', filterTable);
    applyPagination();

    // ===================== SELECT ALL =====================
    $('#selectAll').on('change', function () {
        const checked = $(this).is(':checked');
        $('.slip-row:visible .row-select').prop('checked', checked);
    });
    $(document).on('change', '.row-select', function () {
        const totalVisible   = $('.slip-row:visible .row-select').length;
        const checkedVisible = $('.slip-row:visible .row-select:checked').length;
        $('#selectAll').prop('checked', totalVisible > 0 && totalVisible === checkedVisible);
    });

    // ===================== DELETE =====================
    $(document).on('submit', '.delete-form', function (e) {
        e.preventDefault();
        const form = $(this);
        const url  = form.attr('action');
        Swal.fire({
            title: 'Hapus data ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                    success: function (res) {
                        Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success',
                            confirmButtonColor: '#059669' })
                            .then(() => window.location.reload());
                    },
                    error: function (xhr) {
                        Swal.fire({ title: 'Gagal!', text: xhr.responseJSON?.message || 'Error', icon: 'error' });
                    },
                });
            }
        });
    });

    // ===================== IMPORT TXT =====================
    $('#importTxtForm').on('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        if (!$('#txtFile')[0].files.length) {
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
                $.ajax({
                    url: '{{ route("sliphpms.import") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function () {
                        $('#importProgress').addClass('d-none');
                        $('#importButton').prop('disabled', false);
                        $('#importTxtModal').modal('hide');
                        Swal.fire({ title: 'Berhasil!', text: 'Data berhasil diimport.',
                            icon: 'success', confirmButtonColor: '#3085d6' })
                            .then(() => window.location.reload());
                    },
                    error: function (xhr) {
                        $('#importProgress').addClass('d-none');
                        $('#importButton').prop('disabled', false);
                        Swal.fire({ title: 'Gagal!',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan saat import',
                            icon: 'error', confirmButtonColor: '#dc2626' });
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

    // ===================== SESSION ALERT =====================
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
const slipData = @json($slipPrintData);
let slipPreviewDebounce = null;

function toggleAllDates(cb) {
    document.querySelectorAll('.date-check').forEach(c => c.checked = cb.checked);
    onSlipFilterChange();
}
function toggleAllDocks(cb) {
    document.querySelectorAll('.dock-check').forEach(c => c.checked = cb.checked);
    onSlipFilterChange();
}

function getSelectedDates() { return [...document.querySelectorAll('.date-check:checked')].map(c => c.value); }
function getSelectedDocks() { return [...document.querySelectorAll('.dock-check:checked')].map(c => c.value); }

function updateSlipPreviewCount() {
    const selectedDates = getSelectedDates();
    const selectedDocks = getSelectedDocks();
    const count = slipData.filter(item => {
        const dateOk = selectedDates.length === 0 || selectedDates.includes(item.date);
        const dockOk = selectedDocks.length === 0 || selectedDocks.includes(item.supply);
        return dateOk && dockOk;
    }).length;
    document.getElementById('printPreviewCount').textContent = count;
    return count;
}

function loadSlipPreview() {
    const selectedDates = getSelectedDates();
    const selectedDocks = getSelectedDocks();
    const count = updateSlipPreviewCount();

    if (count === 0) {
        document.getElementById('slipPreviewLoading').classList.add('d-none');
        document.getElementById('slipPreviewEmpty').classList.remove('d-none');
        document.getElementById('slipPrintPreviewIframe').style.display = 'none';
        document.getElementById('slipDoPrintBtn').disabled = true;
        return;
    }

    document.getElementById('slipPreviewLoading').classList.remove('d-none');
    document.getElementById('slipPreviewEmpty').classList.add('d-none');
    document.getElementById('slipPrintPreviewIframe').style.display = 'none';
    document.getElementById('slipDoPrintBtn').disabled = true;

    let params = new URLSearchParams();
    selectedDates.forEach(d => params.append('dates[]', d));
    selectedDocks.forEach(d => params.append('docks[]', d));

    const url = '{{ route("sliphpms.printFiltered") }}?' + params.toString();
    const iframe = document.getElementById('slipPrintPreviewIframe');
    iframe.onload = function () {
        document.getElementById('slipPreviewLoading').classList.add('d-none');
        document.getElementById('slipPrintPreviewIframe').style.display = 'block';
        document.getElementById('slipDoPrintBtn').disabled = false;
    };
    iframe.src = url;
}

function onSlipFilterChange() {
    updateSlipPreviewCount();
    const allDates = document.querySelectorAll('.date-check');
    const checkedDates = document.querySelectorAll('.date-check:checked');
    document.getElementById('checkAllDates').checked =
        allDates.length > 0 && allDates.length === checkedDates.length;

    const allDocks = document.querySelectorAll('.dock-check');
    const checkedDocks = document.querySelectorAll('.dock-check:checked');
    document.getElementById('checkAllDocks').checked =
        allDocks.length > 0 && allDocks.length === checkedDocks.length;

    clearTimeout(slipPreviewDebounce);
    slipPreviewDebounce = setTimeout(loadSlipPreview, 400);
}

document.addEventListener('change', function (e) {
    if (e.target.classList.contains('date-check') || e.target.classList.contains('dock-check')) {
        onSlipFilterChange();
    }
});

document.getElementById('printFilterModal').addEventListener('show.bs.modal', function () {
    document.querySelectorAll('.date-check, .dock-check').forEach(c => c.checked = false);
    document.getElementById('checkAllDates').checked = false;
    document.getElementById('checkAllDocks').checked = false;
    document.getElementById('printPreviewCount').textContent = slipData.length;
    document.getElementById('slipPreviewLoading').classList.add('d-none');
    document.getElementById('slipPreviewEmpty').classList.remove('d-none');
    document.getElementById('slipPrintPreviewIframe').style.display = 'none';
    document.getElementById('slipPrintPreviewIframe').src = 'about:blank';
    document.getElementById('slipDoPrintBtn').disabled = true;
});

document.getElementById('printFilterModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('slipPrintPreviewIframe').src = 'about:blank';
});

function triggerSlipPrint() {
    const iframe = document.getElementById('slipPrintPreviewIframe');
    if (iframe && iframe.contentWindow) iframe.contentWindow.print();
}
</script>
@endpush
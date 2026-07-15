@extends('layouts.app')

@section('title', 'Data Kanban ADM')
@section('page-title', 'KANBAN ADM')
@section('body-class', 'kanbanadms-page')

@section('content')

    <!-- Top Bar -->
    <div class="d-flex justify-content-between align-items-center gap-2 mb-3 mt-3">

        <!-- Left: Upload Info -->
        <div class="d-flex align-items-center gap-2 ms-3">
            @if($latestUpload)
                <strong>Last Upload:</strong> {{ $latestUpload->uploaded_at->format('d M Y H:i:s') }}
                by <strong>{{ $latestUpload->uploaded_by }}</strong>
            @else
                <span class="text-muted">Belum ada data yang diupload</span>
            @endif
        </div>

        <!-- Right: Controls -->
        <div class="d-flex align-items-center gap-2">

            <!-- Print Button -->
            <button class="btn btn-primary" id="printBtn">
                <i class="bi bi-printer me-1"></i>
                <span id="printBtnText">Print All</span>
            </button>

            <!-- Search Bar -->
            <div class="input-group" style="width: 280px;">
                <input type="text" class="form-control" id="searchInput" placeholder="Cari Part No, Order No, Vendor...">
                <button class="btn btn-secondary" type="button" id="searchButton">
                    <i class="bi bi-search"></i>
                </button>
            </div>

            <!-- Shop Filter -->
            <select class="form-select" id="shopFilterSelect" style="width: 140px;">
                <option value="all">All Shop</option>
                @foreach($uniqueShops as $shop)
                    <option value="{{ $shop }}">{{ $shop }}</option>
                @endforeach
            </select>

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
                        <button class="btn btn-link text-dark p-0 m-0" type="button" data-bs-toggle="dropdown" style="text-decoration: none;">
                            <i class="bi bi-three-dots-vertical fs-4"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li>
                                <a class="dropdown-item text-success" href="#" data-bs-toggle="modal" data-bs-target="#importExcelModal">
                                    <i class="bi bi-file-earmark-excel text-success me-2"></i> Import Excel
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
                            <h5 class="mb-0 fw-bold text-white">{{ count($kanbanadms) }}</h5>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Table -->
    <div class="table-responsive p-0 mt-0">
        <table class="table table-compact w-100 mt-1" id="kanbanadmsTable">
            <thead>
                <tr class="fs-6">
                    <th style="width: 40px;"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th>Plant</th>
                    <th>Shop</th>
                    <th>Part Category</th>
                    <th>Route</th>
                    <th>Vendor</th>
                    <th>Order No</th>
                    <th>Part No</th>
                    <th>Part Name</th>
                    <th>Del. Date</th>
                    <th>Del. Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @forelse($kanbanadms as $item)
                    <tr class="fs-6 kanban-row" data-shop="{{ $item->shop_code }}" data-id="{{ $item->id }}">
                        <td><input type="checkbox" class="form-check-input row-select" value="{{ $item->id }}" data-shop="{{ $item->shop_code }}"></td>
                        <td>{{ $item->plant_code }}</td>
                        <td><strong>{{ $item->shop_code }}</strong></td>
                        <td>{{ $item->part_category }}</td>
                        <td>{{ $item->route }}</td>
                        <td>{{ $item->vendor_alias }}</td>
                        <td><strong>{{ $item->order_no }}</strong></td>
                        <td>{{ $item->part_no }}</td>
                        <td>{{ Str::limit($item->part_name, 35) }}</td>
                        <td>{{ $item->del_date }}</td>
                        <td>{{ $item->del_time }}</td>
                        <td>
                            <form action="{{ route('kanbanadms.destroy', $item->id) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" style="border-radius: 6px;" title="Hapus">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="12" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">Belum ada data kanban ADM. Silakan import file Excel.</p>
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
            Showing <span id="showingFrom">1</span> to <span id="showingTo">10</span> of <span id="totalFiltered">{{ count($kanbanadms) }}</span> entries
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination mb-0" id="paginationNav"></ul>
        </nav>
    </div>

    <!-- ==================== MODAL: Import Excel ==================== -->
    <div class="modal fade" id="importExcelModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-file-earmark-excel me-2 text-success"></i>Import Excel — Kanban ADM
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="importExcelForm">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Perhatian!</strong> Import akan <strong>menghapus semua data lama</strong> dan menggantinya dengan data dari file baru.
                        </div>
                        <div class="alert alert-secondary py-2" style="font-size: 0.82rem;">
                            <strong>Format file:</strong> Header di baris ke-4 dengan kolom:<br>
                            <code>Plant Code, Shop Code, Part Category, Route, LP, Trip, Vendor Code, ..., Part No, Part Name, Job No, Lane, Qty/Kbn, ...</code>
                        </div>
                        <div class="mb-3">
                            <label for="excelFile" class="form-label fw-bold text-dark">Pilih File Excel</label>
                            <input type="file" class="form-control" id="excelFile" name="file" accept=".xlsx,.xls" required>
                            <div class="form-text">Format: .xlsx / .xls — Max 10MB</div>
                        </div>
                        <div id="filePreviewInfo" class="d-none">
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-file-earmark-check me-2"></i>
                                File dipilih: <strong id="selectedFileName"></strong>
                                <span class="ms-2 text-muted" id="selectedFileSize"></span>
                            </div>
                        </div>
                        <div class="progress d-none mt-3" id="importProgress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 100%"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success" id="importButton">
                            <i class="bi bi-upload me-2"></i>Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ==================== MODAL: Print All ==================== -->
    <div class="modal fade" id="printAllModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-printer me-2"></i>Print All</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-dark p-0">
                    <div class="row g-0">

                        <!-- Sidebar Filter -->
                        <div class="col-md-3 border-end p-3" style="background: #f8f9fa;">

                            <!-- Filter Plant -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Filter Plant:</label>
                                <div class="d-flex flex-column gap-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="plantFilter" id="plantAll" value="all">
                                        <label class="form-check-label" for="plantAll">
                                            Semua Plant
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="plantFilter" id="plant1" value="plant1">
                                        <label class="form-check-label" for="plant1">
                                            <span class="badge bg-secondary me-1">P1</span> Plant 1
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="plantFilter" id="plant2" value="plant2">
                                        <label class="form-check-label" for="plant2">
                                            <span class="badge bg-success me-1">P2</span> Plant 2 
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-2">

                            <!-- Filter Shop -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Pilih Shop:</label>
                                <div id="printAllDockSelection" class="d-flex flex-column gap-1">
                                    @foreach($uniqueShops as $shop)
                                    <div class="form-check d-flex align-items-center justify-content-between pe-1">
                                        <div>
                                            <input class="form-check-input print-all-dock-check" type="checkbox" value="{{ $shop }}" id="printAll_{{ $shop }}">
                                            <label class="form-check-label" for="printAll_{{ $shop }}">{{ $shop }}</label>
                                        </div>
                                        <span class="badge bg-primary ms-2">
                                            {{ $kanbanadms->where('shop_code', $shop)->count() }}
                                        </span>
                                    </div>
                                    @endforeach
                                </div>
                                <div class="mt-2 d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary" id="selectAllDocks">All</button>
                                    <button class="btn btn-sm btn-outline-secondary" id="deselectAllDocks">None</button>
                                </div>
                                <div class="mt-2 p-2 rounded" style="background: #e9ecef; font-size: 0.82rem;">
                                    Total dipilih: <strong id="totalSelectedCount">{{ count($kanbanadms) }}</strong> data
                                </div>
                            </div>

                        </div>

                        <!-- Preview Area -->
                        <div class="col-md-9 p-0">
                            <div style="background: #fff; height: 65vh; overflow: hidden; position: relative;">
                                <div id="previewLoading" class="text-center py-5 d-none" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%);">
                                    <div class="spinner-border text-dark" role="status"></div>
                                    <p class="mt-3 text-dark">Memuat preview...</p>
                                </div>
                                <div id="previewEmpty" class="text-center text-muted py-5" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%);">
                                    <i class="bi bi-file-earmark-text" style="font-size: 4rem; opacity: 0.5;"></i>
                                    <p class="mt-3">Pilih shop untuk melihat preview</p>
                                </div>
                                <iframe id="printPreviewIframe" style="width:100%; height:100%; border:none; display:none;"></iframe>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-success" id="printFromModalBtn" disabled>
                        <i class="bi bi-printer-fill me-2"></i>Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== MODAL: Print Selected ==================== -->
    <div class="modal fade" id="printSelectedModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-printer me-2"></i>Print Selected (<span id="selectedCountModal">0</span> items)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-dark p-0">
                    <div id="previewContainerSelected" style="background: #fff; height: 70vh; overflow: hidden; position: relative;">
                        <div id="previewLoadingSelected" class="text-center py-5 d-none" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%);">
                            <div class="spinner-border text-dark" role="status"></div>
                            <p class="mt-3 text-dark">Memuat preview...</p>
                        </div>
                        <div id="previewEmptySelected" class="text-center text-muted py-5" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%);">
                            <i class="bi bi-file-earmark-text" style="font-size: 4rem; opacity: 0.5;"></i>
                            <p class="mt-3">Loading preview...</p>
                        </div>
                        <iframe id="printPreviewIframeSelected" style="width:100%; height:100%; border:none; display:none;"></iframe>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-success" id="printFromModalBtnSelected" disabled>
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

    let currentPage = 1;
    let perPage = 10;
    let allRows = [];
    let filteredRows = [];
    let previewDebounceTimer = null;
    let selectedIdsForPrint = [];

    function initRows() {
        allRows = $('.kanban-row').not('.empty-row').toArray();
        filteredRows = [...allRows];
    }
    initRows();

    function applyPagination() {
        $(allRows).hide();
        if (perPage === 'all') {
            $(filteredRows).show();
            updatePaginationInfo(filteredRows.length > 0 ? 1 : 0, filteredRows.length, filteredRows.length);
            renderPagination(1, 1);
        } else {
            const totalPages = Math.ceil(filteredRows.length / perPage);
            if (currentPage > totalPages) currentPage = totalPages || 1;
            const start = (currentPage - 1) * perPage;
            const end = start + perPage;
            filteredRows.slice(start, end).forEach(row => $(row).show());
            updatePaginationInfo(
                filteredRows.length > 0 ? start + 1 : 0,
                Math.min(end, filteredRows.length),
                filteredRows.length
            );
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
        let html = '';
        html += `<li class="page-item ${current === 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${current - 1}">«</a></li>`;
        let startPage = Math.max(1, current - 2);
        let endPage = Math.min(total, current + 2);
        if (startPage > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
            if (startPage > 2) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
        for (let i = startPage; i <= endPage; i++) {
            html += `<li class="page-item ${i === current ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
        }
        if (endPage < total) {
            if (endPage < total - 1) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            html += `<li class="page-item"><a class="page-link" href="#" data-page="${total}">${total}</a></li>`;
        }
        html += `<li class="page-item ${current === total ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${current + 1}">»</a></li>`;
        $('#paginationNav').html(html);
    }

    $(document).on('click', '#paginationNav .page-link', function (e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page && !$(this).parent().hasClass('disabled')) {
            currentPage = page;
            applyPagination();
        }
    });

    $('#perPageSelect').on('change', function () {
        perPage = $(this).val() === 'all' ? 'all' : parseInt($(this).val());
        currentPage = 1;
        applyPagination();
    });

    function filterTable() {
        const search = $('#searchInput').val().toLowerCase();
        const selectedShop = $('#shopFilterSelect').val();
        filteredRows = allRows.filter(function (row) {
            const $row = $(row);
            const shop = String($row.data('shop'));
            const text = $row.text().toLowerCase();
            return (search === '' || text.includes(search)) &&
                   (selectedShop === 'all' || shop === selectedShop);
        });
        currentPage = 1;
        applyPagination();
        $('#selectAll').prop('checked', false);
    }

    $('#searchButton').on('click', filterTable);
    $('#searchInput').on('input', filterTable);
    $('#searchInput').on('keypress', function (e) { if (e.which === 13) filterTable(); });
    $('#shopFilterSelect').on('change', filterTable);

    applyPagination();

    // ==================== Print Button ====================
    function updatePrintButton() {
        const count = $('.row-select:checked').length;
        if (count > 0) {
            $('#printBtn').removeClass('btn-primary').addClass('btn-success');
            $('#printBtnText').text('Print Selected (' + count + ')');
            $('#printBtn').data('mode', 'selected');
        } else {
            $('#printBtn').removeClass('btn-success').addClass('btn-primary');
            $('#printBtnText').text('Print All');
            $('#printBtn').data('mode', 'all');
        }
    }

    $('#selectAll').on('change', function () {
        $('.kanban-row:visible .row-select').prop('checked', $(this).is(':checked'));
        updatePrintButton();
    });

    $(document).on('change', '.row-select', function () {
        updatePrintButton();
        const totalVisible = $('.kanban-row:visible .row-select').length;
        const checkedVisible = $('.kanban-row:visible .row-select:checked').length;
        $('#selectAll').prop('checked', totalVisible > 0 && totalVisible === checkedVisible);
    });

    $('#printBtn').on('click', function () {
        const mode = $(this).data('mode') || 'all';
        if (mode === 'selected') {
            selectedIdsForPrint = $('.row-select:checked').map(function () { return $(this).val(); }).get();
            if (selectedIdsForPrint.length === 0) {
                Swal.fire({ title: 'Error!', text: 'Tidak ada data yang dipilih', icon: 'error' });
                return;
            }
            $('#selectedCountModal').text(selectedIdsForPrint.length);
            $('#printSelectedModal').modal('show');
        } else {
            $('#printAllModal').modal('show');
        }
    });

    // ==================== Print All ====================
    function buildPrintAllUrl() {
        const selectedShops = $('.print-all-dock-check:checked').map(function () { return $(this).val(); }).get();
        const plant = $('input[name="plantFilter"]:checked').val();

        let url = '{{ route("kanbanadms.printall") }}';
        const params = [];

        if (selectedShops.length > 0) {
            params.push('shops=' + selectedShops.join(','));
        }
        if (plant && plant !== 'all') {
            params.push('plant=' + plant);
        }
        if (params.length) {
            url += '?' + params.join('&');
        }
        return { url, hasShops: selectedShops.length > 0 };
    }

    function loadPreviewAll() {
        const { url, hasShops } = buildPrintAllUrl();

        if (!hasShops) {
            $('#previewLoading').addClass('d-none');
            $('#previewEmpty').removeClass('d-none');
            $('#printPreviewIframe').hide();
            $('#printFromModalBtn').prop('disabled', true);
            return;
        }

        $('#previewLoading').removeClass('d-none');
        $('#previewEmpty').addClass('d-none');
        $('#printPreviewIframe').hide();
        $('#printFromModalBtn').prop('disabled', true);

        const iframe = document.getElementById('printPreviewIframe');
        iframe.onload = function () {
            $('#previewLoading').addClass('d-none');
            $('#printPreviewIframe').show();
            $('#printFromModalBtn').prop('disabled', false);
        };
        iframe.src = url;
    }

    function updateTotalSelectedCount() {
        let total = 0;
        $('.print-all-dock-check:checked').each(function () {
            const badge = $(this).closest('.form-check').find('.badge');
            total += parseInt(badge.text()) || 0;
        });
        $('#totalSelectedCount').text(total);
    }

    // ==================== FIX: Sync filter tabel → checkbox modal ====================
    // Jalankan SEBELUM modal keliatan (show, bukan shown) biar checkbox
    // sudah benar sebelum shown.bs.modal trigger loadPreviewAll
    $('#printAllModal').on('show.bs.modal', function () {
        const activeShop = $('#shopFilterSelect').val();

        if (activeShop !== 'all') {
            // Uncheck semua, centang HANYA shop yang aktif di filter tabel
            $('.print-all-dock-check').prop('checked', false);
            $('.print-all-dock-check[value="' + activeShop + '"]').prop('checked', true);
        } else {
            // Filter tabel "All" → centang semua shop di modal
            $('.print-all-dock-check').prop('checked', true);
        }

        // Reset plant ke "Semua Plant"
        $('#plantAll').prop('checked', true);
    });

    // Trigger preview setelah modal selesai muncul
    $('#printAllModal').on('shown.bs.modal', function () {
        updateTotalSelectedCount();
        clearTimeout(previewDebounceTimer);
        previewDebounceTimer = setTimeout(loadPreviewAll, 200);
    });

    // Trigger saat shop checkbox berubah manual
    $(document).on('change', '.print-all-dock-check', function () {
        updateTotalSelectedCount();
        clearTimeout(previewDebounceTimer);
        previewDebounceTimer = setTimeout(loadPreviewAll, 300);
    });

    // Trigger saat filter plant berubah
    $('input[name="plantFilter"]').on('change', function () {
        clearTimeout(previewDebounceTimer);
        previewDebounceTimer = setTimeout(loadPreviewAll, 300);
    });

    $('#selectAllDocks').on('click', function () {
        $('.print-all-dock-check').prop('checked', true);
        updateTotalSelectedCount();
        clearTimeout(previewDebounceTimer);
        previewDebounceTimer = setTimeout(loadPreviewAll, 300);
    });

    $('#deselectAllDocks').on('click', function () {
        $('.print-all-dock-check').prop('checked', false);
        updateTotalSelectedCount();
        clearTimeout(previewDebounceTimer);
        previewDebounceTimer = setTimeout(loadPreviewAll, 300);
    });

    $('#printFromModalBtn').on('click', function () {
        const iframe = document.getElementById('printPreviewIframe');
        if (iframe && iframe.contentWindow) iframe.contentWindow.print();
    });

    $('#printAllModal').on('hidden.bs.modal', function () {
        $('#printPreviewIframe').attr('src', 'about:blank').hide();
        $('#previewEmpty').removeClass('d-none');
        $('#previewLoading').addClass('d-none');
        $('#printFromModalBtn').prop('disabled', true);
    });

    // ==================== Print Selected ====================
    function loadPreviewSelected() {
        if (selectedIdsForPrint.length === 0) return;
        $('#previewLoadingSelected').removeClass('d-none');
        $('#previewEmptySelected').addClass('d-none');
        $('#printPreviewIframeSelected').hide();
        $('#printFromModalBtnSelected').prop('disabled', true);
        const url = '{{ route("kanbanadms.printselected") }}?ids=' + selectedIdsForPrint.join(',');
        const iframe = document.getElementById('printPreviewIframeSelected');
        iframe.onload = function () {
            $('#previewLoadingSelected').addClass('d-none');
            $('#printPreviewIframeSelected').show();
            $('#printFromModalBtnSelected').prop('disabled', false);
        };
        iframe.src = url;
    }

    $('#printSelectedModal').on('shown.bs.modal', function () { loadPreviewSelected(); });

    $('#printFromModalBtnSelected').on('click', function () {
        const iframe = document.getElementById('printPreviewIframeSelected');
        if (iframe && iframe.contentWindow) iframe.contentWindow.print();
    });

    $('#printSelectedModal').on('hidden.bs.modal', function () {
        $('#printPreviewIframeSelected').attr('src', 'about:blank').hide();
        $('#previewEmptySelected').removeClass('d-none');
        $('#previewLoadingSelected').addClass('d-none');
        $('#printFromModalBtnSelected').prop('disabled', true);
    });

    // ==================== Import Excel ====================
    $('#excelFile').on('change', function () {
        const file = this.files[0];
        if (file) {
            $('#selectedFileName').text(file.name);
            $('#selectedFileSize').text('(' + (file.size / 1024).toFixed(1) + ' KB)');
            $('#filePreviewInfo').removeClass('d-none');
        } else {
            $('#filePreviewInfo').addClass('d-none');
        }
    });

    $('#importExcelForm').on('submit', function (e) {
        e.preventDefault();
        const fileInput = $('#excelFile')[0];
        if (!fileInput.files.length) {
            Swal.fire({ title: 'Error!', text: 'Silakan pilih file Excel terlebih dahulu', icon: 'error' });
            return;
        }
        Swal.fire({
            title: 'Import Data?',
            html: 'Semua data kanban ADM yang ada akan <strong>dihapus</strong> dan diganti dengan data baru dari file ini.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Import!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('file', fileInput.files[0]);
                formData.append('_token', '{{ csrf_token() }}');
                $('#importProgress').removeClass('d-none');
                $('#importButton').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Mengimport...');
                $.ajax({
                    url: '{{ route("kanbanadms.import") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        $('#importProgress').addClass('d-none');
                        $('#importButton').prop('disabled', false).html('<i class="bi bi-upload me-2"></i>Import');
                        $('#importExcelModal').modal('hide');
                        Swal.fire({ title: 'Berhasil!', text: response.message, icon: 'success', confirmButtonColor: '#198754' })
                            .then(() => window.location.reload());
                    },
                    error: function (xhr) {
                        $('#importProgress').addClass('d-none');
                        $('#importButton').prop('disabled', false).html('<i class="bi bi-upload me-2"></i>Import');
                        Swal.fire({ title: 'Gagal!', text: xhr.responseJSON?.message || 'Terjadi kesalahan saat import', icon: 'error' });
                    }
                });
            }
        });
    });

    $('#importExcelModal').on('hidden.bs.modal', function () {
        $('#importExcelForm')[0].reset();
        $('#filePreviewInfo').addClass('d-none');
        $('#importProgress').addClass('d-none');
        $('#importButton').prop('disabled', false).html('<i class="bi bi-upload me-2"></i>Import');
    });

    // ==================== Delete ====================
    $('.delete-form').on('submit', function (e) {
        e.preventDefault();
        const url = $(this).attr('action');
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
                Swal.fire({ title: 'Menghapus...', allowOutsideClick: false, allowEscapeKey: false, didOpen: () => Swal.showLoading() });
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                    success: function (response) {
                        Swal.fire({ title: 'Berhasil!', text: response.message, icon: 'success', confirmButtonColor: '#059669' })
                            .then(() => window.location.reload());
                    },
                    error: function (xhr) {
                        Swal.fire({ title: 'Gagal!', text: xhr.responseJSON?.message || 'Terjadi kesalahan', icon: 'error' });
                    }
                });
            }
        });
    });

});
</script>
@endpush
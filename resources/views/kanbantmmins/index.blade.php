@extends('layouts.app')

@section('title', 'Data Kanban TMMIN')
@section('page-title', 'KANBAN TMMIN')
@section('body-class', 'kanbantmmins-page')

@section('content')
    @php
        $uniqueDockCodes = $kanbantmmins->pluck('dock_code')->unique()->filter()->sort();
    @endphp

    <div class="d-flex justify-content-between align-items-center gap-2 mb-3 mt-3">
        <div class="d-flex align-items-center gap-2 ms-3">
            <strong>Last Upload:</strong> 
            @if($latestUploadInfo)
                {{ $latestUploadInfo->last_upload_at->format('d M Y H:i:s') }} 
                by <strong>{{ $latestUploadInfo->uploaded_by }}</strong>
            @else
                -
            @endif
        </div>

        <div class="d-flex align-items-center gap-2">
            <div class="btn-group" role="group" aria-label="View toggle">
                <a href="{{ route('kanbantmmins.index') }}" class="btn btn-secondary active" title="View All Items">
                    <i class="bi bi-list-ul"></i> All Items
                </a>
                <a href="{{ route('kanbantmmins.indexByDn') }}" class="btn btn-outline-secondary" title="View By Delivery Note">
                    <i class="bi bi-collection"></i> By DN
                </a>
            </div>

            <button class="btn btn-success d-none" id="printSelectedBtn">
                <i class="bi bi-printer-fill me-1"></i> Print Selected (<span id="selectedCount">0</span>)
            </button>
            
            <button class="btn btn-primary" id="printAllBtn">
                <i class="bi bi-printer me-1"></i> Print All
            </button>

            <select class="form-select" id="dockFilterSelect" style="width: 130px;">
                <option value="all">All Dock</option>
                @foreach($uniqueDockCodes as $dockCode)
                    <option value="{{ $dockCode }}">{{ $dockCode }}</option>
                @endforeach
            </select>

            <div class="input-group" style="width: 280px;">
                <input type="text" class="form-control" id="searchInput" placeholder="Cari Part No, Manifest..." value="">
                <button class="btn btn-secondary" type="button" id="searchButton">
                    <i class="bi bi-search"></i>
                </button>
            </div>

            <select class="form-select" id="perPageSelect" style="width: 85px;">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="all">All</option>
            </select>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-2">
                    <div class="dropdown">
                        <button class="btn btn-link text-dark p-0 m-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false" style="text-decoration: none;">
                            <i class="bi bi-three-dots-vertical fs-4"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="dropdownMenuButton">
                            <li>
                                <a class="dropdown-item text-primary" href="#" data-bs-toggle="modal" data-bs-target="#importTxtModal">
                                    <i class="bi bi-file-earmark-text text-primary me-2"></i> Import TXT
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="bg-primary card border-0 shadow-sm me-2">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-10 p-2 rounded me-2">
                            <i class="bi bi-box-seam text-white fs-5"></i>
                        </div>
                        <div>
                            <small class="text-white d-block fw-bold me-3" style="font-size: 0.7rem;">Total</small>
                            <h5 class="mb-0 fw-bold text-white">{{ count($kanbantmmins) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive p-0 mt-0">
        <table class="table table-compact w-100 mt-1" id="kanbantmminsTable">
            <thead>
                <tr class="fs-6">
                    <th style="width: 40px;"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th>Manifest No</th>
                    <th>Part No</th>
                    <th>Part Name</th>
                    <th>Dock</th>
                    <th>Address</th>
                    <th>PCS</th>
                    <th>Route</th>
                    <th>Supplier</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @forelse($kanbantmmins as $index => $item)
                    <tr class="fs-6 kanban-row" data-dock="{{ $item->dock_code }}" data-id="{{ $item->id }}">
                        <td><input type="checkbox" class="form-check-input row-select" value="{{ $item->id }}" data-dock="{{ $item->dock_code }}"></td>
                        <td><strong>{{ $item->manifest_no }}</strong></td>
                        <td>{{ $item->part_no }}</td>
                        <td>{{ Str::limit($item->part_name, 30) }}</td>
                        <td><span class="badge bg-white text-dark fs-6">{{ $item->dock_code }}</span></td>
                        <td><strong>{{ $item->address }}</strong></td>
                        <td>{{ $item->pcs }}</td>
                        <td>{{ $item->route }}</td>
                        <td>{{ Str::limit($item->supplier, 20) }}</td>
                        <td>
                            <div class="d-flex justify-content-center p-1" style="gap: 0;">
                                <a href="{{ route('kanbantmmins.print', $item->id) }}" class="btn btn-success btn-sm btn-action-square btn-print-individual" style="border-radius: 6px 0 0 6px; margin: 0;" title="Print">
                                    <i class="bi bi-printer-fill"></i>
                                </a>
                                <form action="{{ route('kanbantmmins.destroy', $item->id) }}" method="POST" class="d-inline delete-form" style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm btn-action-square" style="border-radius: 0 6px 6px 0; margin: 0;" title="Hapus">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="mt-3 empty-row">
                        <td colspan="10" class="text-center py-4">
                            <div class="text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">Belum ada data kanban</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3 me-3" id="paginationContainer">
        <div class="text-muted" id="paginationInfo">
            Showing <span id="showingFrom">1</span> to <span id="showingTo">10</span> of <span id="totalFiltered">{{ count($kanbantmmins) }}</span> entries
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination mb-0" id="paginationNav"></ul>
        </nav>
    </div>

    <!-- Import TXT Modal -->
    <div class="modal fade" id="importTxtModal" tabindex="-1" aria-labelledby="importTxtModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-dark" id="importTxtModalLabel">
                        <i class="bi bi-file-earmark-text me-2 text-dark"></i>Import TXT File
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('kanbantmmins.import') }}" method="POST" enctype="multipart/form-data" id="importTxtForm">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Perhatian!</strong> Import akan menghapus semua data yang ada dan menggantinya dengan data baru. 
                        </div>
                        <div class="mb-3">
                            <label for="txtFile" class="form-label text-dark">Pilih File TXT</label>
                            <input type="file" class="form-control" id="txtFile" name="file" accept=".txt" required>
                            <div class="form-text">Format file: .txt (max 5MB) & Hanya support Dock 4</div>
                        </div>
                        <div class="progress d-none" id="importProgress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
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

    <!-- Print All Modal -->
    <div class="modal fade" id="printAllModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-printer me-2 text-dark"></i>Print All - Select Options</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-dark">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Pilih Plant:</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="plantOptionAll" id="plantAll" value="all" checked>
                                <label class="form-check-label" for="plantAll">
                                    All Plant <span class="badge bg-secondary" id="badgeAllPlant">0</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="plantOptionAll" id="plant1Only" value="1">
                                <label class="form-check-label" for="plant1Only">
                                    Plant 1 <span class="badge bg-primary" id="badgePlant1">0</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="plantOptionAll" id="plant2Only" value="2">
                                <label class="form-check-label" for="plant2Only">
                                    Plant 2 <span class="badge bg-success" id="badgePlant2">0</span>
                                </label>
                            </div>
                        </div>
                        <small class="text-muted">Plant 2 = Address diawali huruf "K"</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Dock Code:</label>
                        <div id="printAllDockSelection" class="d-flex flex-wrap gap-2">
                            @foreach($uniqueDockCodes as $dockCode)
                            <div class="form-check">
                                <input class="form-check-input print-all-dock-check" type="checkbox" value="{{ $dockCode }}" id="printAll_{{ $dockCode }}" checked>
                                <label class="form-check-label" for="printAll_{{ $dockCode }}">{{ $dockCode }}</label>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-2">
                            <button class="btn btn-sm btn-primary" id="selectAllPrintDocks">Select All</button>
                            <button class="btn btn-sm btn-secondary" id="deselectAllPrintDocks">Deselect All</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="confirmPrintAllBtn">
                        <i class="bi bi-printer me-2"></i>Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Selected Modal -->
    <div class="modal fade" id="printSelectedModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-printer me-2 text-dark"></i>Print Selected - Select Plant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-dark">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Plant:</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="plantOptionSelected" id="plantAllSelected" value="all" checked>
                                <label class="form-check-label" for="plantAllSelected">
                                    All Plant <span class="badge bg-secondary" id="badgeAllPlantSelected">0</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="plantOptionSelected" id="plant1OnlySelected" value="1">
                                <label class="form-check-label" for="plant1OnlySelected">
                                    Plant 1 <span class="badge bg-primary" id="badgePlant1Selected">0</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="plantOptionSelected" id="plant2OnlySelected" value="2">
                                <label class="form-check-label" for="plant2OnlySelected">
                                    Plant 2 <span class="badge bg-success" id="badgePlant2Selected">0</span>
                                </label>
                            </div>
                        </div>
                        <small class="text-muted">Plant 2 = Address diawali huruf "K"</small>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <span id="selectedItemsInfo">0 item terpilih</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="confirmPrintSelectedBtn">
                        <i class="bi bi-printer me-2"></i>Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Preview Modal -->
    <div class="modal fade" id="printPreviewModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 50vw;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-printer me-2"></i>Print Preview - <span id="previewPlantLabel">Plant 1</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" style="height: 70vh;">
                    <iframe id="printPreviewFrame" style="width: 100%; height: 100%; border: none;"></iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmPrintBtn">
                        <i class="bi bi-printer-fill me-1"></i>Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Counter -->
    <div id="printCounter" style="display: none; position: fixed; bottom: 20px; right: 20px; background: rgba(0,0,0,0.85); color: white; padding: 15px 25px; border-radius: 10px; font-size: 14px; z-index: 99999; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">
        <div id="counterLabel" style="font-size: 12px; opacity: 0.8;">Plant 1</div>
        <div id="counterValue" style="font-size: 24px; font-weight: bold;">0/0s</div>
    </div>
@endsection

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

@push('scripts')
<script>
$(document).ready(function() {
    // ========== VARIABLES ==========
    let currentPage = 1;
    let perPage = 10;
    let allRows = [];
    let filteredRows = [];
    
    // Plant print variables
    let printMode = null;
    let printPlantOption = 'all';
    let selectedDockCodes = [];
    let selectedIds = [];
    let plant1Count = 0;
    let plant2Count = 0;
    let isSequentialPrint = false;
    let currentPrintingPlant = null;
    
    function initRows() {
        allRows = $('.kanban-row').not('.empty-row').toArray();
        filteredRows = [...allRows];
    }
    initRows();
    
    // ========== PAGINATION ==========
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
            const end = start + perPage;
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
        let html = '';
        if (total <= 1) { $('#paginationNav').html(''); return; }
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
    
    $(document).on('click', '#paginationNav .page-link', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page && !$(this).parent().hasClass('disabled')) {
            currentPage = page;
            applyPagination();
        }
    });
    
    $('#perPageSelect').on('change', function() {
        const val = $(this).val();
        perPage = val === 'all' ? 'all' : parseInt(val);
        currentPage = 1;
        applyPagination();
    });
    
    // ========== FILTER ==========
    function filterTable() {
        const search = $('#searchInput').val().toLowerCase();
        const selectedDock = $('#dockFilterSelect').val();
        filteredRows = allRows.filter(function(row) {
            const $row = $(row);
            const dock = String($row.data('dock'));
            const text = $row.text().toLowerCase();
            const matchesSearch = search === '' || text.includes(search);
            const matchesDock = selectedDock === 'all' || dock === selectedDock;
            return matchesSearch && matchesDock;
        });
        currentPage = 1;
        applyPagination();
        $('#selectAll').prop('checked', false);
        updateSelectedCount();
    }
    
    $('#searchButton').on('click', filterTable);
    $('#searchInput').on('keypress', function(e) { if (e.which === 13) filterTable(); });
    $('#searchInput').on('input', filterTable);
    $('#dockFilterSelect').on('change', filterTable);
    applyPagination();
    
    // ========== CHECKBOX ==========
    function updateSelectedCount() {
        const count = $('.row-select:checked').length;
        $('#selectedCount').text(count);
        if (count > 0) {
            $('#printSelectedBtn').removeClass('d-none');
        } else {
            $('#printSelectedBtn').addClass('d-none');
        }
    }
    
    $('#selectAll').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.kanban-row:visible .row-select').prop('checked', isChecked);
        updateSelectedCount();
    });
    
    $(document).on('change', '.row-select', function() {
        updateSelectedCount();
        const totalVisible = $('.kanban-row:visible .row-select').length;
        const checkedVisible = $('.kanban-row:visible .row-select:checked').length;
        $('#selectAll').prop('checked', totalVisible > 0 && totalVisible === checkedVisible);
    });
    
    // ========== PLANT COUNTS ==========
    function fetchPlantCounts(dockCodes, ids, callback) {
        $.ajax({
            url: '{{ route("kanbantmmins.getPlantCounts") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                dock_codes: dockCodes || [],
                ids: ids || []
            },
            success: function(response) {
                plant1Count = response.plant1 || 0;
                plant2Count = response.plant2 || 0;
                if (callback) callback(plant1Count, plant2Count);
            },
            error: function() {
                plant1Count = 0;
                plant2Count = 0;
                if (callback) callback(0, 0);
            }
        });
    }
    
    // ========== PRINT PREVIEW ==========
    function showPrintPreview(url, isSequential, plantLabel) {
        Swal.fire({
            title: 'Loading Preview...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => { Swal.showLoading(); }
        });
        
        isSequentialPrint = isSequential;
        currentPrintingPlant = plantLabel || 'Data';
        $('#previewPlantLabel').text(currentPrintingPlant);
        
        const iframe = $('#printPreviewFrame');
        iframe.attr('src', 'about:blank');
        iframe.attr('src', url);
        
        iframe.off('load').on('load', function() {
            Swal.close();
            $('#printPreviewModal').modal('show');
        });
        
        setTimeout(function() {
            Swal.close();
            $('#printPreviewModal').modal('show');
        }, 3000);
    }
    
    // ========== COUNTER ==========
    function showCounter(label, current, total) {
        $('#counterLabel').text(label);
        $('#counterValue').text(current + '/' + total + 's');
        $('#printCounter').fadeIn();
    }
    
    function updateCounter(current, total) {
        $('#counterValue').text(current + '/' + total + 's');
    }
    
    function hideCounter() {
        $('#printCounter').fadeOut();
    }
    
    // ========== SEQUENTIAL PRINT ==========
    function startSequentialPrint() {
        // Check if both plants have data
        if (plant1Count === 0 && plant2Count === 0) {
            Swal.fire({ title: 'Error!', text: 'Tidak ada data untuk diprint', icon: 'error' });
            return;
        }
        
        if (plant1Count === 0) {
            // Only Plant 2 has data, print directly
            printPlant2();
            return;
        }
        
        if (plant2Count === 0) {
            // Only Plant 1 has data, print directly
            let url;
            if (printMode === 'all') {
                url = '{{ route("kanbantmmins.printall") }}?dock_codes=' + selectedDockCodes.join(',') + '&plant=1';
            } else {
                url = '{{ route("kanbantmmins.printselected") }}?ids=' + selectedIds.join(',') + '&plant=1';
            }
            showPrintPreview(url, false, 'Plant 1');
            return;
        }
        
        // Both plants have data, start with Plant 1
        let url;
        if (printMode === 'all') {
            url = '{{ route("kanbantmmins.printall") }}?dock_codes=' + selectedDockCodes.join(',') + '&plant=1';
        } else {
            url = '{{ route("kanbantmmins.printselected") }}?ids=' + selectedIds.join(',') + '&plant=1';
        }
        showPrintPreview(url, true, 'Plant 1');
    }
    
    function printPlant2() {
    let url;
    if (printMode === 'all') {
        url = '{{ route("kanbantmmins.printall") }}?dock_codes=' + selectedDockCodes.join(',') + '&plant=2';
    } else {
        url = '{{ route("kanbantmmins.printselected") }}?ids=' + selectedIds.join(',') + '&plant=2';
    }
    
    // Buat hidden iframe untuk print langsung
    let iframe = document.getElementById('print-frame');
    if (!iframe) {
        iframe = document.createElement('iframe');
        iframe.id = 'print-frame';
        iframe.style.display = 'none';
        document.body.appendChild(iframe);
    }
    
    iframe.src = url;
    iframe.onload = function() {
        iframe.contentWindow.print();
    };
}
    
    function startPrintTimer(itemCount, onComplete) {
        const totalSeconds = itemCount;
        let currentSecond = 0;
        
        showCounter(currentPrintingPlant, currentSecond, totalSeconds);
        
        const timerInterval = setInterval(function() {
            currentSecond++;
            updateCounter(currentSecond, totalSeconds);
            
            if (currentSecond >= totalSeconds) {
                clearInterval(timerInterval);
                hideCounter();
                if (onComplete) onComplete();
            }
        }, 1000);
    }
    
    function showPlant2Confirmation() {
        let timerInterval;
        let timeLeft = 20;
        
        Swal.fire({
            title: '<i class="bi bi-check-circle-fill text-success"></i> Plant 1 Selesai!',
            html: `
                <p>Lanjut print Plant 2?</p>
                <p class="text-muted">Auto lanjut dalam <strong id="swal-timer">${timeLeft}</strong> detik</p>
            `,
            icon: null,
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-printer me-1"></i> Lanjut',
            cancelButtonText: 'Batal',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                timerInterval = setInterval(() => {
                    timeLeft--;
                    const timerEl = document.getElementById('swal-timer');
                    if (timerEl) timerEl.textContent = timeLeft;
                    if (timeLeft <= 0) {
                        clearInterval(timerInterval);
                        Swal.close();
                        printPlant2();
                    }
                }, 1000);
            },
            willClose: () => {
                clearInterval(timerInterval);
            }
        }).then((result) => {
            if (result.isConfirmed) {
                printPlant2();
            }
        });
    }
    
    // ========== CONFIRM PRINT BUTTON ==========
    $('#confirmPrintBtn').on('click', function() {
        const iframe = document.getElementById('printPreviewFrame');
        if (iframe && iframe.contentWindow) {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
            
            // Close modal after triggering print
            $('#printPreviewModal').modal('hide');
            
            // If sequential print, start timer for Plant 1
            if (isSequentialPrint && currentPrintingPlant === 'Plant 1') {
                startPrintTimer(plant1Count, function() {
                    showPlant2Confirmation();
                });
            }
        }
    });
    
    // ========== PRINT ALL ==========
    $('#printAllBtn').on('click', function() {
        const docks = $('.print-all-dock-check:checked').map(function() { return $(this).val(); }).get();
        fetchPlantCounts(docks, null, function(p1, p2) {
            $('#badgePlant1').text(p1);
            $('#badgePlant2').text(p2);
            $('#badgeAllPlant').text(p1 + p2);
        });
        $('#printAllModal').modal('show');
    });
    
    $(document).on('change', '.print-all-dock-check', function() {
        const docks = $('.print-all-dock-check:checked').map(function() { return $(this).val(); }).get();
        fetchPlantCounts(docks, null, function(p1, p2) {
            $('#badgePlant1').text(p1);
            $('#badgePlant2').text(p2);
            $('#badgeAllPlant').text(p1 + p2);
        });
    });
    
    $('#selectAllPrintDocks').on('click', function() {
        $('.print-all-dock-check').prop('checked', true).trigger('change');
    });
    
    $('#deselectAllPrintDocks').on('click', function() {
        $('.print-all-dock-check').prop('checked', false);
        $('#badgePlant1').text(0);
        $('#badgePlant2').text(0);
        $('#badgeAllPlant').text(0);
    });
    
    $('#confirmPrintAllBtn').on('click', function() {
        selectedDockCodes = $('.print-all-dock-check:checked').map(function() { return $(this).val(); }).get();
        if (selectedDockCodes.length === 0) {
            Swal.fire({ title: 'Error!', text: 'Pilih minimal satu dock code', icon: 'error' });
            return;
        }
        printPlantOption = $('input[name="plantOptionAll"]:checked').val();
        printMode = 'all';
        $('#printAllModal').modal('hide');
        
        if (printPlantOption === 'all') {
            startSequentialPrint();
        } else {
            const url = '{{ route("kanbantmmins.printall") }}?dock_codes=' + selectedDockCodes.join(',') + '&plant=' + printPlantOption;
            showPrintPreview(url, false, 'Plant ' + printPlantOption);
        }
    });
    
    // ========== PRINT SELECTED ==========
    $('#printSelectedBtn').on('click', function() {
        selectedIds = $('.row-select:checked').map(function() { return $(this).val(); }).get();
        if (selectedIds.length === 0) {
            Swal.fire({ title: 'Error!', text: 'Tidak ada data yang dipilih', icon: 'error' });
            return;
        }
        fetchPlantCounts(null, selectedIds, function(p1, p2) {
            $('#badgePlant1Selected').text(p1);
            $('#badgePlant2Selected').text(p2);
            $('#badgeAllPlantSelected').text(p1 + p2);
            $('#selectedItemsInfo').text((p1 + p2) + ' item terpilih');
        });
        $('#printSelectedModal').modal('show');
    });
    
    $('#confirmPrintSelectedBtn').on('click', function() {
        printPlantOption = $('input[name="plantOptionSelected"]:checked').val();
        printMode = 'selected';
        $('#printSelectedModal').modal('hide');
        
        if (printPlantOption === 'all') {
            startSequentialPrint();
        } else {
            const url = '{{ route("kanbantmmins.printselected") }}?ids=' + selectedIds.join(',') + '&plant=' + printPlantOption;
            showPrintPreview(url, false, 'Plant ' + printPlantOption);
        }
    });
    
    // ========== PRINT INDIVIDUAL ==========
    $(document).on('click', '.btn-print-individual', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        showPrintPreview(url, false, 'Single Item');
    });
    
    // ========== DELETE ==========
    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const url = form.attr('action');
        
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data ini akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menghapus...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                    success: function(response) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.message || 'Data berhasil dihapus',
                            icon: 'success',
                            confirmButtonColor: '#059669'
                        }).then(() => { window.location.reload(); });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Gagal!',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan',
                            icon: 'error',
                            confirmButtonColor: '#dc2626'
                        });
                    }
                });
            }
        });
    });

    // ========== IMPORT ==========
    $('#importTxtForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const fileInput = $('#txtFile')[0];
        
        if (!fileInput.files.length) {
            Swal.fire({ title: 'Error!', text: 'Silakan pilih file TXT terlebih dahulu', icon: 'error', confirmButtonColor: '#dc2626' });
            return;
        }
        
        Swal.fire({
            title: 'Import Data?',
            text: 'Semua data yang ada akan dihapus dan diganti dengan data baru!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Import!'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#importProgress').removeClass('d-none');
                $('#importButton').prop('disabled', true);
                
                $.ajax({
                    url: '{{ route("kanbantmmins.import") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#importProgress').addClass('d-none');
                        $('#importButton').prop('disabled', false);
                        $('#importTxtModal').modal('hide');
                        Swal.fire({
                            title: 'Berhasil!',
                            text: 'Data berhasil diimport.',
                            icon: 'success',
                            confirmButtonColor: '#3085d6'
                        }).then(() => { window.location.reload(); });
                    },
                    error: function(xhr) {
                        $('#importProgress').addClass('d-none');
                        $('#importButton').prop('disabled', false);
                        Swal.fire({
                            title: 'Gagal!',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan saat import',
                            icon: 'error',
                            confirmButtonColor: '#dc2626'
                        });
                    }
                });
            }
        });
    });

    @if(session('sweet_alert'))
        Swal.fire({
            icon: '{{ session("sweet_alert.type") }}',
            title: '{{ session("sweet_alert.title") }}',
            text: '{{ session("sweet_alert.text") }}',
            showConfirmButton: {{ session("sweet_alert.showConfirmButton") ? 'true' : 'false' }},
            timer: {{ session("sweet_alert.timer") ?? 'null' }}
        });
    @endif
});
</script>
@endpush
@extends('layouts.app')

@section('title', 'Data Kanban TMMIN')
@section('page-title', 'KANBAN TMMIN')
@section('body-class', 'kanbantmmins-page')

@section('content')
    @php
        $uniqueDockCodes = $kanbantmmins->pluck('dock_code')->unique()->filter()->sort();
    @endphp

    <!-- Stats Badges dan Dropdown di kanan -->
    <div class="d-flex justify-content-between align-items-center gap-2 mb-3 mt-3">
        
        <!-- Left Side - Print Buttons -->
        <div class="d-flex align-items-center gap-2 ms-3">
            <strong>Last Upload:</strong> {{ $latestUploadInfo->last_upload_at->format('d M Y H:i:s') }} 
            by <strong>{{ $latestUploadInfo->uploaded_by }}</strong>
        </div>

        <!-- Right Side - Search & Menu -->
        <div class="d-flex align-items-center gap-2">

            <!-- Print Selected Button (Hidden by default) -->
            <button class="btn btn-success d-none" id="printSelectedBtn">
                <i class="bi bi-printer-fill me-1"></i> Print Selected (<span id="selectedCount">0</span>)
            </button>
            
            <!-- Print All Button -->
            <button class="btn btn-primary" id="printAllBtn">
                <i class="bi bi-printer me-1"></i> Print All
            </button>
            <!-- Dock Filter Dropdown -->
            <select class="form-select" id="dockFilterSelect" style="width: 130px;">
                <option value="all">All Dock</option>
                @foreach($uniqueDockCodes as $dockCode)
                    <option value="{{ $dockCode }}">{{ $dockCode }}</option>
                @endforeach
            </select>

            <!-- Search Bar -->
            <div class="input-group" style="width: 280px;">
                <input type="text" class="form-control" id="searchInput" placeholder="Cari Part No, Manifest..." value="">
                <button class="btn btn-secondary" type="button" id="searchButton">
                    <i class="bi bi-search"></i>
                </button>
            </div>

            <!-- Per Page Selector -->
            <select class="form-select" id="perPageSelect" style="width: 85px;">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="all">All</option>
            </select>

            <!-- Dropdown Menu Button -->
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

            <!-- Total Data Badge -->
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
                                <a href="{{ route('kanbantmmins.print', $item->id) }}" class="btn btn-success btn-sm btn-action-square" style="border-radius: 6px 0 0 6px; margin: 0;" title="Print" target="_blank">
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

    <!-- Pagination Info & Controls -->
    <div class="d-flex justify-content-between align-items-center mt-3 me-3" id="paginationContainer">
        <div class="text-muted" id="paginationInfo">
            Showing <span id="showingFrom">1</span> to <span id="showingTo">10</span> of <span id="totalFiltered">{{ count($kanbantmmins) }}</span> entries
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination mb-0" id="paginationNav">
                <!-- Pagination will be generated by JS -->
            </ul>
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
                    <h5 class="modal-title text-dark"><i class="bi bi-printer me-2 text-dark"></i>Print All - Select Dock Codes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-dark">
                    <p>Pilih dock code yang ingin di-print:</p>
                    <div id="printAllDockSelection" class="d-flex flex-wrap gap-2">
                        @foreach($uniqueDockCodes as $dockCode)
                        <div class="form-check">
                            <input class="form-check-input print-all-dock-check" type="checkbox" value="{{ $dockCode }}" id="printAll_{{ $dockCode }}" checked>
                            <label class="form-check-label" for="printAll_{{ $dockCode }}">{{ $dockCode }}</label>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-sm btn-primary" id="selectAllPrintDocks">Select All</button>
                        <button class="btn btn-sm btn-secondary" id="deselectAllPrintDocks">Deselect All</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="confirmPrintAllBtn">
                        <i class="bi bi-printer me-2"></i>Print All
                    </button>
                </div>
            </div>
        </div>
    </div>
    
@endsection

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

@push('scripts')
<script>
    $(document).ready(function() {
        // Pagination variables
        let currentPage = 1;
        let perPage = 10;
        let allRows = [];
        let filteredRows = [];
        
        // Initialize rows
        function initRows() {
            allRows = $('.kanban-row').not('.empty-row').toArray();
            filteredRows = [...allRows];
        }
        
        initRows();
        
        // Apply pagination
        function applyPagination() {
            // Hide all rows first
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
        
        // Update pagination info text
        function updatePaginationInfo(from, to, total) {
            if (total === 0) {
                $('#paginationInfo').html('No entries found');
            } else {
                $('#showingFrom').text(from);
                $('#showingTo').text(to);
                $('#totalFiltered').text(total);
            }
        }
        
        // Render pagination buttons
        function renderPagination(current, total) {
            let html = '';
            
            if (total <= 1) {
                $('#paginationNav').html('');
                return;
            }
            
            // Previous button
            html += `<li class="page-item ${current === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${current - 1}">«</a>
            </li>`;
            
            // Page numbers
            let startPage = Math.max(1, current - 2);
            let endPage = Math.min(total, current + 2);
            
            if (startPage > 1) {
                html += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
                if (startPage > 2) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }
            
            for (let i = startPage; i <= endPage; i++) {
                html += `<li class="page-item ${i === current ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>`;
            }
            
            if (endPage < total) {
                if (endPage < total - 1) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
                html += `<li class="page-item"><a class="page-link" href="#" data-page="${total}">${total}</a></li>`;
            }
            
            // Next button
            html += `<li class="page-item ${current === total ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${current + 1}">»</a>
            </li>`;
            
            $('#paginationNav').html(html);
        }
        
        // Pagination click handler
        $(document).on('click', '#paginationNav .page-link', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            if (page && !$(this).parent().hasClass('disabled')) {
                currentPage = page;
                applyPagination();
            }
        });
        
        // Per page change
        $('#perPageSelect').on('change', function() {
            const val = $(this).val();
            perPage = val === 'all' ? 'all' : parseInt(val);
            currentPage = 1;
            applyPagination();
        });
        
        // Filter function
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
            
            // Reset checkbox states when filter changes
            $('#selectAll').prop('checked', false);
            updateSelectedCount();
        }
        
        // Search functionality
        $('#searchButton').on('click', filterTable);
        $('#searchInput').on('keypress', function(e) {
            if (e.which === 13) filterTable();
        });
        $('#searchInput').on('input', filterTable);
        
        // Dock filter dropdown
        $('#dockFilterSelect').on('change', filterTable);
        
        // Initial pagination
        applyPagination();
        
        // ========== CHECKBOX & PRINT SELECTED ==========
        
        // Update selected count and show/hide print button
        function updateSelectedCount() {
            const count = $('.row-select:checked').length;
            $('#selectedCount').text(count);
            
            if (count > 0) {
                $('#printSelectedBtn').removeClass('d-none');
            } else {
                $('#printSelectedBtn').addClass('d-none');
            }
        }
        
        // Select all checkbox
        $('#selectAll').on('change', function() {
            const isChecked = $(this).is(':checked');
            // Only select visible rows
            $('.kanban-row:visible .row-select').prop('checked', isChecked);
            updateSelectedCount();
        });
        
        // Individual row checkbox
        $(document).on('change', '.row-select', function() {
            updateSelectedCount();
            
            // Update select all checkbox state
            const totalVisible = $('.kanban-row:visible .row-select').length;
            const checkedVisible = $('.kanban-row:visible .row-select:checked').length;
            $('#selectAll').prop('checked', totalVisible > 0 && totalVisible === checkedVisible);
        });
        
        // Print Selected Button
        $('#printSelectedBtn').on('click', function() {
            const selectedIds = $('.row-select:checked').map(function() {
                return $(this).val();
            }).get();
            
            if (selectedIds.length === 0) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Tidak ada data yang dipilih',
                    icon: 'error'
                });
                return;
            }
            
            // Open print page with selected IDs
            const url = '{{ route("kanbantmmins.printselected") }}?ids=' + selectedIds.join(',');
            window.open(url, '_blank');
        });
        
        // ========== PRINT ALL ==========
        
        $('#printAllBtn').on('click', function() {
            $('#printAllModal').modal('show');
        });
        
        $('#selectAllPrintDocks').on('click', function() {
            $('.print-all-dock-check').prop('checked', true);
        });
        
        $('#deselectAllPrintDocks').on('click', function() {
            $('.print-all-dock-check').prop('checked', false);
        });
        
        $('#confirmPrintAllBtn').on('click', function() {
            const selectedDocks = $('.print-all-dock-check:checked').map(function() {
                return $(this).val();
            }).get();
            
            if (selectedDocks.length === 0) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Pilih minimal satu dock code',
                    icon: 'error'
                });
                return;
            }
            
            const url = '{{ route("kanbantmmins.printall") }}?dock_codes=' + selectedDocks.join(',');
            window.open(url, '_blank');
            $('#printAllModal').modal('hide');
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
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: response.message || 'Data berhasil dihapus',
                                icon: 'success',
                                confirmButtonColor: '#059669'
                            }).then(() => {
                                window.location.reload();
                            });
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
                Swal.fire({
                    title: 'Error!',
                    text: 'Silakan pilih file TXT terlebih dahulu',
                    icon: 'error',
                    confirmButtonColor: '#dc2626'
                });
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
                            }).then(() => {
                                window.location.reload();
                            });
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

        // Sweet Alert for flash messages
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
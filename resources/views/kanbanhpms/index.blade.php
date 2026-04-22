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
            @else
                <span class="text-muted">Belum ada data diimport</span>
            @endif
        </div>

        <!-- Right: Controls -->
        <div class="d-flex align-items-center gap-2">

            <div class="btn-group" role="group">
                <a href="{{ route('kanbanhpms.printall') }}"
                   class="btn btn-primary" target="_blank">
                    <i class="bi bi-printer me-1"></i> Print All
                </a>
            </div>

            <!-- Supply Address Filter -->
            <select class="form-select" id="supplyFilterSelect" style="width: 140px;">
                <option value="all">All Supply</option>
                @foreach($kanbanhpms->pluck('supply_address')->unique()->filter()->sort() as $sa)
                    <option value="{{ $sa }}">{{ $sa }}</option>
                @endforeach
            </select>

            <!-- Search Bar -->
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
                            <h5 class="mb-0 fw-bold text-white">{{ count($kanbanhpms) }}</h5>
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
                    <th style="width: 40px;"><input type="checkbox" id="selectAll" class="form-check-input"></th>
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
            <tbody id="tableBody">
                @forelse($kanbanhpms as $item)
                    <tr class="fs-6 hpm-row"
                        data-supply="{{ $item->supply_address }}"
                        data-id="{{ $item->id }}">
                        <td><input type="checkbox" class="form-check-input row-select" value="{{ $item->id }}"></td>
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
                    <tr class="empty-row">
                        <td colspan="13" class="text-center py-4">
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

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-3 me-3" id="paginationContainer">
        <div class="text-muted" id="paginationInfo">
            Showing <span id="showingFrom">1</span> to
            <span id="showingTo">10</span> of
            <span id="totalFiltered">{{ count($kanbanhpms) }}</span> entries
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination mb-0" id="paginationNav"></ul>
        </nav>
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
                            <strong>Perhatian!</strong> Import akan menghapus semua data yang ada
                            dan menggantinya dengan data baru dari file TXT.
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

                        {{-- Info --}}
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

                        {{-- File Weekly --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">
                                <i class="bi bi-file-earmark-excel text-success me-1"></i>
                                File Weekly
                            </label>
                            <input type="file" class="form-control" id="fileWeekly"
                                   name="file_weekly" accept=".xlsx,.xls" required>
                            <div class="form-text">
                                Format: 1 file Excel dengan 1 sheet &mdash; kolom <strong>KD Lot Number</strong>,
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

                        {{-- Progress --}}
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
        allRows = $('.hpm-row').not('.empty-row').toArray();
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
            currentPage = page;
            applyPagination();
        }
    });

    $('#perPageSelect').on('change', function () {
        const val = $(this).val();
        perPage = val === 'all' ? 'all' : parseInt(val);
        currentPage = 1;
        applyPagination();
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

        currentPage = 1;
        applyPagination();
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
        $('.hpm-row:visible .row-select').prop('checked', checked);
    });

    $(document).on('change', '.row-select', function () {
        const totalVisible   = $('.hpm-row:visible .row-select').length;
        const checkedVisible = $('.hpm-row:visible .row-select:checked').length;
        $('#selectAll').prop('checked', totalVisible > 0 && totalVisible === checkedVisible);
    });

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
        const formData  = new FormData(this);
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
                            confirmButtonColor: '#dc2626',
                        });
                    },
                });
            }
        });
    });

    // Reset modal import saat ditutup
    $('#importTxtModal').on('hidden.bs.modal', function () {
        $('#txtFile').val('');
        $('#importProgress').addClass('d-none');
        $('#importButton').prop('disabled', false);
    });

    // ===================== ADJUST WEEKLY =====================

    // File preview
    $('#fileWeekly').on('change', function () {
        const file = this.files[0];
        if (file) {
            $('#fileWeeklyName').text(file.name);
            $('#fileWeeklyPreview').removeClass('d-none');
        } else {
            $('#fileWeeklyPreview').addClass('d-none');
        }
    });

    // Reset modal saat ditutup
    $('#adjustWeeklyModal').on('hidden.bs.modal', function () {
        $('#fileWeekly').val('');
        $('#fileWeeklyPreview').addClass('d-none');
        $('#adjustWeeklyProgress').addClass('d-none');
        $('#adjustWeeklyButton').prop('disabled', false);
    });

    // Submit
    $('#adjustWeeklyForm').on('submit', function (e) {
        e.preventDefault();

        if ($('#fileWeekly')[0].files.length === 0) {
            Swal.fire({
                title: 'Perhatian!',
                text: 'Pilih file Excel Weekly terlebih dahulu.',
                icon: 'warning',
                confirmButtonColor: '#3085d6',
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
</script>
@endpush
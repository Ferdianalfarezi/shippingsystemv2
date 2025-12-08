@extends('layouts.app')

@section('title', 'Data Preparation')
@section('page-title', 'PREPARATIONS MONITORING')
@section('body-class', 'preparation-page')

@section('content')
    <!-- Stats Badges dan Dropdown di kanan -->
    <div class="d-flex justify-content-end align-items-center gap-2 mb-3 mt-3">
        
        <!-- Delete All Button -->
        <div class="card border-4 bg-danger">
            <button type="button" class="btn btn-danger" id="deleteAllButton" title="Hapus Semua Data">
                <i class="bi bi-trash-fill"></i>
            </button>
        </div>
        
        <!-- Show By Dropdown -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-1">
                <select class="form-select form-select-sm border-0" id="perPageSelect" style="width: auto;">
                    <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500</option>
                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
                </select>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="input-group" style="width: 300px;">
            <input type="text" class="form-control" id="searchInput" placeholder="Cari Route, LP, DN, Customer..." value="{{ request('search') }}">
            <button class="btn btn-secondary" type="button" id="searchButton">
                <i class="bi bi-search"></i>
            </button>
        </div>

        <!-- Menu & Badges -->
        <div class="d-flex align-items-center gap-2">
            <!-- Dropdown Menu Button -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-2">
                    <div class="dropdown">
                        <button class="btn btn-link text-dark p-0 m-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false" style="text-decoration: none;">
                            <i class="bi bi-three-dots-vertical fs-4"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="dropdownMenuButton">
                            <li>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#createPreparationModal">
                                    <i class="bi bi-plus-circle me-2"></i> Tambah Data
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#lpConfigModal">
                                    <i class="bi bi-gear-fill me-2"></i> Konfigurasi LP
                                </a>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#admLeadTimeModal">
                                    <i class="bi bi-clock-history me-2"></i> Konfigurasi Lead Time ADM
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item">
                                        <i class="bi bi-upload me-2"></i> Import Options :
                                    </a>
                                    <a class="dropdown-item text-success" href="#" data-bs-toggle="modal" data-bs-target="#importExcelModal">
                                        <i class="bi bi-file-earmark-excel text-success me-1 ms-4"></i> Excel
                                    </a>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#importTmminModal">
                                        <i class="bi bi-file-earmark-text text-dark me-1 ms-4"></i> TMMIN
                                    </a>
                                    <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#importAdmModal">
                                        <i class="bi bi-file-earmark-spreadsheet text-danger me-1 ms-4"></i> ADM
                                    </a>
                                </li>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- On Time Badge -->
            <div class="bg-success card border-0 shadow-sm">
                <div class="card-body p-1">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-10 p-2 rounded me-2">
                            <i class="bi bi-check-circle text-white  fs-5"></i>
                        </div>
                        <div>
                            <small class="text-white d-block fw-bold me-3" style="font-size: 0.7rem;">Open</small>
                            <h5 class="mb-0 fw-bold text-white">{{ $totalOnTime }}</h5>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Delay Badge -->
            <div class="bg-danger card border-0 shadow-sm me-3">
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
        
    </div>

    <div class="table-responsive p-0 mt-0">
        <table class="table table-compact w-100 mt-1" id="preparationsTable">
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
                    <th>Pull Date</th>
                    <th>Finish Pulling</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($preparations as $index => $prep)
                    <tr class="fs-4 {{ $prep->status === 'delay' ? 'table-danger-subtle' : '' }}">
                        <td><strong>{{ $prep->route }}</strong></td>
                        <td>{{ $prep->logistic_partners }}</td>
                        <td>{{ $prep->no_dn }}</td>
                        <td>{{ $prep->customers }}</td>
                        <td><strong>{{ $prep->dock }}</strong></td>
                        <td>{{ $prep->delivery_date->format('d-m-y') }}</td>
                        <td>{{ date('H:i:s', strtotime($prep->delivery_time)) }}</td>
                        <td><strong>{{ $prep->cycle }}</strong></td>
                        <td>{{ $prep->pulling_date->format('d-m-y') }}</td>
                        <td>{{ date('H:i:s', strtotime($prep->pulling_time)) }}</td>
                        <td>
                            <span class="badge {{ $prep->status_badge }} fw-bold px-3 py-2 mb-1 mt-1 
                                {{ $prep->status === 'delay' ? 'badge-delay' : '' }}"
                                title="{{ $prep->status === 'delay' ? 'Terlambat ' . $prep->delay_duration : 'On Time' }}">
                                {{ $prep->status_label }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex justify-content-center" style="gap: 0;">
                                <button onclick="openEditModal({{ $prep->id }})" class="btn btn-warning btn-sm btn-action-square" style="border-radius: 6px 0 0 6px; margin: 0;" title="Edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                
                                <form action="{{ route('preparations.destroy', $prep->id) }}" method="POST" class="d-inline delete-form" style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm btn-action-square" style="border-radius: 0; margin: 0;" title="Hapus">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>

                                <a href="javascript:void(0)" onclick="showQrCode('{{ $prep->no_dn }}')" class="btn btn-secondary btn-sm btn-action-square" style="border-radius: 0; margin: 0;" title="QR Code">
                                    <i class="bi bi-qr-code"></i>
                                </a>
                                
                                <a href="#" class="btn btn-primary btn-sm btn-action-square" style="border-radius: 0 6px 6px 0; margin: 0;" title="Next">
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="mt-3">
                        <td colspan="12" class="text-center py-4">
                            <div class="text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">Belum ada data preparation</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-wrapper">
        {{ $preparations->links() }}
    </div>

    @include('preparations.create')
    @include('preparations.edit')
    @include('preparations.import')
    @include('preparations.import-tmmin')
    @include('preparations.lp-config')
    @include('preparations.import-adm')
    @include('preparations.adm-lead-time-config')

    
@endsection

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
@push('scripts')
<script>
    $(document).ready(function() {
        
        // Handle nested dropdown for Import Options
        $('.dropend').on('mouseenter', function() {
            $(this).find('.dropdown-menu').addClass('show');
        }).on('mouseleave', function() {
            $(this).find('.dropdown-menu').removeClass('show');
        });

        // Handle click on Import Options
        $('#importOptionsDropdown').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).next('.dropdown-menu').toggleClass('show');
        });

        // Prevent parent dropdown from closing when clicking submenu
        $('.dropend .dropdown-menu').on('click', function(e) {
            e.stopPropagation();
        });

        // Delete confirmation dengan SweetAlert
$('.delete-form').on('submit', function(e) {
    e.preventDefault();
    
    const form = $(this);
    const url = form.attr('action');
    
    console.log('Form URL:', url); // DEBUG
    console.log('CSRF Token:', '{{ csrf_token() }}'); // DEBUG
    
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
            // Tampilkan loading
            Swal.fire({
                title: 'Menghapus...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            console.log('Sending DELETE request...'); // DEBUG
            
            // AJAX Request untuk delete
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'DELETE'
                },
                success: function(response) {
                    console.log('Success response:', response); // DEBUG
                    Swal.fire({
                        title: 'Berhasil!',
                        text: response.message || 'Data berhasil dihapus',
                        icon: 'success',
                        confirmButtonColor: '#059669'
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr, status, error) {
                    console.log('Error status:', status); // DEBUG
                    console.log('Error detail:', error); // DEBUG
                    console.log('XHR response:', xhr.responseText); // DEBUG
                    console.log('XHR status code:', xhr.status); // DEBUG
                    
                    Swal.fire({
                        title: 'Gagal!',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus data',
                        icon: 'error',
                        confirmButtonColor: '#dc2626'
                    });
                }
            });
        }
    });
});

        // Delete All Button
        $('#deleteAllButton').on('click', function() {
            Swal.fire({
                title: 'PERINGATAN!',
                text: "Apakah Anda yakin ingin menghapus SEMUA data preparation? Tindakan ini tidak dapat dibatalkan!",
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus Semua!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                input: 'text',
                inputPlaceholder: 'Ketik "HAPUS SEMUA" untuk konfirmasi',
                inputValidator: (value) => {
                    if (value !== 'HAPUS SEMUA') {
                        return 'Anda harus mengetik "HAPUS SEMUA" untuk melanjutkan!'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Tampilkan loading
                    Swal.fire({
                        title: 'Menghapus Semua Data...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Ajax request untuk delete all
                    $.ajax({
                        url: '{{ route("preparations.deleteAll") }}',
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonColor: '#059669'
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: 'Gagal!',
                                text: 'Terjadi kesalahan saat menghapus data',
                                icon: 'error',
                                confirmButtonColor: '#dc2626'
                            });
                        }
                    });
                }
            });
        });

        // Handle Import Excel Form
        $('#importExcelForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const fileInput = $('#excelFile')[0];
            
            if (!fileInput.files.length) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Silakan pilih file Excel terlebih dahulu',
                    icon: 'error',
                    confirmButtonColor: '#dc2626'
                });
                return;
            }
            
            // Show progress
            $('#importProgress').removeClass('d-none');
            $('#importButton').prop('disabled', true);
            
            $.ajax({
                url: '{{ route("preparations.import") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#importProgress').addClass('d-none');
                    $('#importButton').prop('disabled', false);
                    
                    if (response.status === 'duplicates_found') {
                        // Tampilkan konfirmasi untuk duplikat
                        let duplicateList = '<ul class="text-start">';
                        response.duplicates.forEach(function(dup) {
                            duplicateList += `<li>${dup.table}: ${dup.count} data</li>`;
                        });
                        duplicateList += '</ul>';
                        
                        Swal.fire({
                            title: 'Data Duplikat Ditemukan!',
                            html: response.message + duplicateList + '<br><strong>Apakah Anda ingin melanjutkan import tanpa data duplikat?</strong>',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#059669',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Ya, Lanjutkan!',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Import ulang dengan force_import flag
                                formData.append('force_import', '1');
                                
                                $('#importProgress').removeClass('d-none');
                                $('#importButton').prop('disabled', true);
                                
                                $.ajax({
                                    url: '{{ route("preparations.import") }}',
                                    type: 'POST',
                                    data: formData,
                                    processData: false,
                                    contentType: false,
                                    success: function(response) {
                                        $('#importProgress').addClass('d-none');
                                        $('#importButton').prop('disabled', false);
                                        
                                        if (response.status === 'success') {
                                            Swal.fire({
                                                title: 'Berhasil!',
                                                text: response.message,
                                                icon: 'success',
                                                confirmButtonColor: '#059669'
                                            }).then(() => {
                                                $('#importExcelModal').modal('hide');
                                                window.location.reload();
                                            });
                                        }
                                    },
                                    error: function(xhr) {
                                        $('#importProgress').addClass('d-none');
                                        $('#importButton').prop('disabled', false);
                                        
                                        Swal.fire({
                                            title: 'Gagal!',
                                            text: xhr.responseJSON?.message || 'Terjadi kesalahan saat mengimpor data',
                                            icon: 'error',
                                            confirmButtonColor: '#dc2626'
                                        });
                                    }
                                });
                            }
                        });
                    } else if (response.status === 'success') {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonColor: '#059669'
                        }).then(() => {
                            $('#importExcelModal').modal('hide');
                            window.location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    $('#importProgress').addClass('d-none');
                    $('#importButton').prop('disabled', false);
                    
                    Swal.fire({
                        title: 'Gagal!',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan saat mengimpor data',
                        icon: 'error',
                        confirmButtonColor: '#dc2626'
                    });
                }
            });
        });

        // Reset form when modal is closed
        $('#importExcelModal').on('hidden.bs.modal', function () {
            $('#importExcelForm')[0].reset();
            $('#importProgress').addClass('d-none');
            $('#importButton').prop('disabled', false);
        });

        // Handle Search Button Click
        $('#searchButton').on('click', function() {
            performSearch();
        });

        // Handle Enter key on search input
        $('#searchInput').on('keypress', function(e) {
            if (e.which === 13) {
                performSearch();
            }
        });

        // Handle Per Page Change
        $('#perPageSelect').on('change', function() {
            const perPage = $(this).val();
            const search = $('#searchInput').val();
            updateUrl(perPage, search);
        });

        // Function to perform search
        function performSearch() {
            const search = $('#searchInput').val();
            const perPage = $('#perPageSelect').val();
            updateUrl(perPage, search);
        }

        // Function to update URL with parameters
        function updateUrl(perPage, search) {
            const url = new URL(window.location.href);
            
            if (perPage && perPage !== '50') {
                url.searchParams.set('per_page', perPage);
            } else {
                url.searchParams.delete('per_page');
            }
            
            if (search && search.trim() !== '') {
                url.searchParams.set('search', search);
            } else {
                url.searchParams.delete('search');
            }
            
            window.location.href = url.toString();
        }
    });

    // Handle Import TMMIN TXT Form
        $('#importTmminForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const fileInput = $('#tmminFile')[0];
            
            if (!fileInput.files.length) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Silakan pilih file TXT terlebih dahulu',
                    icon: 'error',
                    confirmButtonColor: '#dc2626'
                });
                return;
            }
            
            // Show progress
            $('#importTmminProgress').removeClass('d-none');
            $('#importTmminButton').prop('disabled', true);
            
            $.ajax({
                url: '{{ route("preparations.import-tmmin") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#importTmminProgress').addClass('d-none');
                    $('#importTmminButton').prop('disabled', false);
                    
                    if (response.status === 'duplicates_found') {
                        // Tampilkan konfirmasi untuk duplikat
                        let duplicateList = '<ul class="text-start">';
                        response.duplicates.forEach(function(dup) {
                            duplicateList += `<li>${dup.table}: ${dup.count} data</li>`;
                        });
                        duplicateList += '</ul>';
                        
                        Swal.fire({
                            title: 'Data Duplikat Ditemukan!',
                            html: response.message + duplicateList + '<br><strong>Apakah Anda ingin melanjutkan import tanpa data duplikat?</strong>',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#059669',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Ya, Lanjutkan!',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Import ulang dengan force_import flag
                                formData.append('force_import', '1');
                                
                                $('#importTmminProgress').removeClass('d-none');
                                $('#importTmminButton').prop('disabled', true);
                                
                                $.ajax({
                                    url: '{{ route("preparations.import-tmmin") }}',
                                    type: 'POST',
                                    data: formData,
                                    processData: false,
                                    contentType: false,
                                    success: function(response) {
                                        $('#importTmminProgress').addClass('d-none');
                                        $('#importTmminButton').prop('disabled', false);
                                        
                                        if (response.status === 'success') {
                                            Swal.fire({
                                                title: 'Berhasil!',
                                                text: response.message,
                                                icon: 'success',
                                                confirmButtonColor: '#059669'
                                            }).then(() => {
                                                $('#importTmminModal').modal('hide');
                                                window.location.reload();
                                            });
                                        }
                                    },
                                    error: function(xhr) {
                                        $('#importTmminProgress').addClass('d-none');
                                        $('#importTmminButton').prop('disabled', false);
                                        
                                        Swal.fire({
                                            title: 'Gagal!',
                                            text: xhr.responseJSON?.message || 'Terjadi kesalahan saat mengimpor data',
                                            icon: 'error',
                                            confirmButtonColor: '#dc2626'
                                        });
                                    }
                                });
                            }
                        });
                    } else if (response.status === 'success') {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonColor: '#059669'
                        }).then(() => {
                            $('#importTmminModal').modal('hide');
                            window.location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    $('#importTmminProgress').addClass('d-none');
                    $('#importTmminButton').prop('disabled', false);
                    
                    Swal.fire({
                        title: 'Gagal!',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan saat mengimpor data',
                        icon: 'error',
                        confirmButtonColor: '#dc2626'
                    });
                }
            });
        });

        // Reset form when modal is closed
        $('#importTmminModal').on('hidden.bs.modal', function () {
            $('#importTmminForm')[0].reset();
            $('#importTmminProgress').addClass('d-none');
            $('#importTmminButton').prop('disabled', false);
        });


        $('#importAdmForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const fileInput = $('#admFile')[0];
        
        if (!fileInput.files.length) {
            Swal.fire({
                title: 'Error!',
                text: 'Silakan pilih file Excel terlebih dahulu',
                icon: 'error',
                confirmButtonColor: '#dc2626'
            });
            return;
        }
        
        // Show progress
        $('#importAdmProgress').removeClass('d-none');
        $('#importAdmButton').prop('disabled', true);
        
        $.ajax({
            url: '{{ route("preparations.import-adm") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#importAdmProgress').addClass('d-none');
                $('#importAdmButton').prop('disabled', false);
                
                if (response.status === 'duplicates_found') {
                    // Tampilkan konfirmasi untuk duplikat
                    let duplicateList = '<ul class="text-start">';
                    response.duplicates.forEach(function(dup) {
                        
                    });
                    duplicateList += '</ul>';
                    
                    Swal.fire({
                        title: 'Data Duplikat Ditemukan!',
                        html: response.message + duplicateList + '<br><strong>Jika lanjut data yang duplikat akan di abaikan</strong>',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#059669',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Lanjutkan!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Import ulang dengan force_import flag
                            formData.append('force_import', '1');
                            
                            $('#importAdmProgress').removeClass('d-none');
                            $('#importAdmButton').prop('disabled', true);
                            
                            $.ajax({
                                url: '{{ route("preparations.import-adm") }}',
                                type: 'POST',
                                data: formData,
                                processData: false,
                                contentType: false,
                                success: function(response) {
                                    $('#importAdmProgress').addClass('d-none');
                                    $('#importAdmButton').prop('disabled', false);
                                    
                                    if (response.status === 'success') {
                                        Swal.fire({
                                            title: 'Berhasil!',
                                            text: response.message,
                                            icon: 'success',
                                            confirmButtonColor: '#059669'
                                        }).then(() => {
                                            $('#importAdmModal').modal('hide');
                                            window.location.reload();
                                        });
                                    }
                                },
                                error: function(xhr) {
                                    $('#importAdmProgress').addClass('d-none');
                                    $('#importAdmButton').prop('disabled', false);
                                    
                                    Swal.fire({
                                        title: 'Gagal!',
                                        text: xhr.responseJSON?.message || 'Terjadi kesalahan saat mengimpor data',
                                        icon: 'error',
                                        confirmButtonColor: '#dc2626'
                                    });
                                }
                            });
                        }
                    });
                } else if (response.status === 'success') {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#059669'
                    }).then(() => {
                        $('#importAdmModal').modal('hide');
                        window.location.reload();
                    });
                }
            },
            error: function(xhr) {
                $('#importAdmProgress').addClass('d-none');
                $('#importAdmButton').prop('disabled', false);
                
                Swal.fire({
                    title: 'Gagal!',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan saat mengimpor data',
                    icon: 'error',
                    confirmButtonColor: '#dc2626'
                });
            }
        });
    });

    // Reset form when modal is closed
    $('#importAdmModal').on('hidden.bs.modal', function () {
        $('#importAdmForm')[0].reset();
        $('#importAdmProgress').addClass('d-none');
        $('#importAdmButton').prop('disabled', false);
    });

    // QR Code Generator
    function showQrCode(noDn) {
        Swal.fire({
            title: 'QR Code DN',
            html: `
                <div class="text-center">
                    <div id="qrcode" class="d-flex justify-content-center mb-3"></div>
                    <p class="fw-bold fs-4 mb-0 mt-3">${noDn}</p>
                </div>
            `,
            showCloseButton: true,
            showConfirmButton: true,
            confirmButtonText: '<i class="bi bi-printer"></i> Print',
            confirmButtonColor: '#6c757d',
            width: 350,
            didOpen: () => {
                new QRCode(document.getElementById("qrcode"), {
                    text: noDn,
                    width: 200,
                    height: 200,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Print QR Code
                const printWindow = window.open('', '_blank');
                const qrImage = document.querySelector('#qrcode img').src;
                printWindow.document.write(`
                    <html>
                    <head><title>QR Code - ${noDn}</title></head>
                    <body style="text-align: center; padding: 20px;">
                        <img src="${qrImage}" style="width: 200px; height: 200px;">
                        <p style="font-size: 24px; font-weight: bold; margin-top: 15px;">${noDn}</p>
                    </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.print();
            }
        });
    }
</script>
@endpush
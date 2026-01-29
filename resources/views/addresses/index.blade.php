@extends('layouts.app')

@section('title', 'Data Address')
@section('page-title', 'TMMIN ADDRESS')
@section('body-class', 'address-page')

@section('content')
    <!-- Stats Badges dan Dropdown di kanan -->
    <div class="d-flex justify-content-end align-items-center gap-2 mb-3 mt-3">
        
        
        
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
            <input type="text" class="form-control" id="searchInput" placeholder="Cari Part No, Customer, Model..." value="{{ request('search') }}">
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
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#createAddressModal">
                                    <i class="bi bi-plus-circle me-2"></i> Tambah Data
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-success" href="#" data-bs-toggle="modal" data-bs-target="#importExcelModal">
                                    <i class="bi bi-file-earmark-excel text-success me-2"></i> Import Excel
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item text-info" href="#" data-bs-toggle="modal" data-bs-target="#importRackModal">
                                    <i class="bi bi-box-seam text-info me-2"></i> Update Rack (Excel)
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Total Data Badge -->
            <div class="bg-primary card border-0 shadow-sm me-3">
                <div class="card-body p-1">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-10 p-2 rounded me-2">
                            <i class="bi bi-box-seam text-white fs-5"></i>
                        </div>
                        <div>
                            <small class="text-white d-block fw-bold me-3" style="font-size: 0.7rem;">Total</small>
                            <h5 class="mb-0 fw-bold text-white">{{ $addresses->total() }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>  

    <div class="table-responsive p-0 mt-0">
        <table class="table table-compact w-100 mt-1" id="addressesTable">
            <thead>
                <tr class="fs-6">
                    <th>Part No</th>
                    <th>Customer Code</th>
                    <th>Model</th>
                    <th>Part Name</th>
                    <th>Qty</th>
                    <th>Line</th>
                    <th>Rack No</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($addresses as $index => $address)
                    <tr class="fs-5">
                        <td><strong>{{ $address->part_no }}</strong></td>
                        <td>{{ $address->customer_code }}</td>
                        <td>{{ $address->model }}</td>
                        <td>{{ $address->part_name }}</td>
                        <td>{{ $address->qty_kbn }}</td>
                        <td><strong>{{ $address->line }}</strong></td>
                        <td><strong>{{ $address->rack_no }}</strong></td>
                        <td>
                            <div class="d-flex justify-content-center p-1" style="gap: 0;">
                                <button onclick="openEditModal({{ $address->id }})" class="btn btn-warning btn-sm btn-action-square" style="border-radius: 6px 0 0 6px; margin: 0;" title="Edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                
                                <form action="{{ route('addresses.destroy', $address->id) }}" method="POST" class="d-inline delete-form" style="margin: 0;">
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
                    <tr class="mt-3">
                        <td colspan="8" class="text-center py-4">
                            <div class="text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">Belum ada data address</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-wrapper">
        {{ $addresses->links() }}
    </div>

    @include('addresses.create')
    @include('addresses.edit')
    @include('addresses.import')
    @include('addresses.rack-import')
    
@endsection

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

@push('scripts')
<script>
    $(document).ready(function() {
        
        // Delete confirmation dengan SweetAlert
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
                    
                    // AJAX Request untuk delete
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
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus data',
                                icon: 'error',
                                confirmButtonColor: '#dc2626'
                            });
                        }
                    });
                }
            });
        });

        // Handle Edit Form Submit
        $('#editAddressForm').on('submit', function(e) {
            e.preventDefault();
            
            const id = $('#edit_id').val();
            const formData = $(this).serialize();
            
            $.ajax({
                url: `/addresses/${id}`,
                type: 'PUT',
                data: formData,
                success: function(response) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Data address berhasil diupdate',
                        icon: 'success',
                        confirmButtonColor: '#059669'
                    }).then(() => {
                        $('#editAddressModal').modal('hide');
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Gagal!',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan saat mengupdate data',
                        icon: 'error',
                        confirmButtonColor: '#dc2626'
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
            
            $('#importProgress').removeClass('d-none');
            $('#importButton').prop('disabled', true);
            
            $.ajax({
                url: '{{ route("addresses.import") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#importProgress').addClass('d-none');
                    $('#importButton').prop('disabled', false);
                    
                    Swal.fire({
                        title: 'Berhasil!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#059669'
                    }).then(() => {
                        $('#importExcelModal').modal('hide');
                        window.location.reload();
                    });
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

        $('#importExcelModal').on('hidden.bs.modal', function () {
            $('#importExcelForm')[0].reset();
            $('#importProgress').addClass('d-none');
            $('#importButton').prop('disabled', false);
        });

        // Handle Search Button Click
        $('#searchButton').on('click', function() {
            performSearch();
        });

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

        function performSearch() {
            const search = $('#searchInput').val();
            const perPage = $('#perPageSelect').val();
            updateUrl(perPage, search);
        }

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

    // Open Edit Modal
    function openEditModal(id) {
        $.ajax({
            url: `/addresses/${id}/edit`,
            type: 'GET',
            success: function(data) {
                $('#edit_id').val(data.id);
                $('#edit_part_no').val(data.part_no);
                $('#edit_customer_code').val(data.customer_code);
                $('#edit_model').val(data.model);
                $('#edit_part_name').val(data.part_name);
                $('#edit_qty_kbn').val(data.qty_kbn);
                $('#edit_line').val(data.line);
                $('#edit_rack_no').val(data.rack_no);
                
                $('#editAddressModal').modal('show');
            },
            error: function() {
                Swal.fire({
                    title: 'Error!',
                    text: 'Gagal mengambil data address',
                    icon: 'error',
                    confirmButtonColor: '#dc2626'
                });
            }
        });
    }
</script>
@endpush
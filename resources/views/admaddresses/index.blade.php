@extends('layouts.app')

@section('title', 'ADM Address')
@section('page-title', 'ADM ADDRESS')
@section('body-class', 'admaddress-page')

@section('content')

    <div class="d-flex justify-content-end align-items-center gap-2 mb-3 mt-3">

        <div class="card border-0 shadow-sm">
            <div class="card-body p-1">
                <select class="form-select form-select-sm border-0" id="perPageSelect" style="width: auto;">
                    <option value="50"  {{ request('per_page', 50) == 50  ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500</option>
                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
                </select>
            </div>
        </div>

        <div class="input-group" style="width: 300px;">
            <input type="text" class="form-control" id="searchInput"
                   placeholder="Cari Part No, Customer, Model..."
                   value="{{ request('search') }}">
            <button class="btn btn-secondary" type="button" id="searchButton">
                <i class="bi bi-search"></i>
            </button>
        </div>

        <div class="d-flex align-items-center gap-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-2">
                    <div class="dropdown">
                        <button class="btn btn-link text-dark p-0 m-0" type="button"
                                data-bs-toggle="dropdown" style="text-decoration: none;">
                            <i class="bi bi-three-dots-vertical fs-4"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li>
                                <a class="dropdown-item" href="#"
                                   data-bs-toggle="modal" data-bs-target="#createAdmaddressModal">
                                    <i class="bi bi-plus-circle me-2"></i> Tambah Data
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-success" href="#"
                                   data-bs-toggle="modal" data-bs-target="#importExcelModal">
                                    <i class="bi bi-file-earmark-excel text-success me-2"></i> Import Excel
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="#" id="deleteAllBtn">
                                    <i class="bi bi-trash3 text-danger me-2"></i> Hapus Semua Data
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="bg-primary card border-0 shadow-sm me-3">
                <div class="card-body p-1">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-10 p-2 rounded me-2">
                            <i class="bi bi-box-seam text-white fs-5"></i>
                        </div>
                        <div>
                            <small class="text-white d-block fw-bold me-3" style="font-size: 0.7rem;">Total</small>
                            <h5 class="mb-0 fw-bold text-white">{{ $admaddresses->total() }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="table-responsive p-0 mt-0">
        <table class="table table-compact w-100 mt-1">
            <thead>
                <tr class="fs-6">
                    <th>Part No</th>
                    <th>Customer Code</th>
                    <th>Model</th>
                    <th>Part Name</th>
                    <th>Qty Kbn</th>
                    <th>Line</th>
                    <th>Rack No</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($admaddresses as $admaddress)
                    <tr class="fs-5">
                        <td><strong>{{ $admaddress->part_no }}</strong></td>
                        <td>{{ $admaddress->customer_code }}</td>
                        <td>{{ $admaddress->model }}</td>
                        <td>{{ $admaddress->part_name }}</td>
                        <td>{{ $admaddress->qty_kbn }}</td>
                        <td><strong>{{ $admaddress->line }}</strong></td>
                        <td><strong>{{ $admaddress->rack_no }}</strong></td>
                        <td>
                            <div class="d-flex justify-content-center p-1" style="gap: 0;">
                                <button onclick="openEditModal({{ $admaddress->id }})"
                                        class="btn btn-warning btn-sm btn-action-square"
                                        style="border-radius: 6px 0 0 6px; margin: 0;" title="Edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <form action="{{ route('admaddresses.destroy', $admaddress->id) }}"
                                      method="POST" class="d-inline delete-form" style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm btn-action-square"
                                            style="border-radius: 0 6px 6px 0; margin: 0;" title="Hapus">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <div class="text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">Belum ada data ADM Address</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        {{ $admaddresses->links() }}
    </div>

    @include('admaddresses.create')
    @include('admaddresses.edit')
    @include('admaddresses.import')

@endsection

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

@push('scripts')
<script>
$(document).ready(function () {

    // Delete single
    $('.delete-form').on('submit', function (e) {
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
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Menghapus...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                $.ajax({
                    url, type: 'POST',
                    data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                    success: function (res) {
                        Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', confirmButtonColor: '#059669' })
                            .then(() => window.location.reload());
                    },
                    error: function (xhr) {
                        Swal.fire({ title: 'Gagal!', text: xhr.responseJSON?.message || 'Terjadi kesalahan', icon: 'error', confirmButtonColor: '#dc2626' });
                    }
                });
            }
        });
    });

    // Delete All
    $('#deleteAllBtn').on('click', function (e) {
        e.preventDefault();
        Swal.fire({
            title: 'Hapus Semua Data?',
            text: 'Seluruh data ADM Address akan dihapus permanen!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus Semua!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Menghapus...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                $.ajax({
                    url: '{{ route("admaddresses.deleteAll") }}',
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', confirmButtonColor: '#059669' })
                            .then(() => window.location.reload());
                    },
                    error: function (xhr) {
                        Swal.fire({ title: 'Gagal!', text: xhr.responseJSON?.message || 'Terjadi kesalahan', icon: 'error', confirmButtonColor: '#dc2626' });
                    }
                });
            }
        });
    });

    // Edit form submit
    $('#editAdmaddressForm').on('submit', function (e) {
        e.preventDefault();
        const id = $('#edit_id').val();
        $.ajax({
            url: `/admaddresses/${id}`,
            type: 'POST',
            data: $(this).serialize() + '&_method=PUT',
            success: function (res) {
                Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', confirmButtonColor: '#059669' })
                    .then(() => { $('#editAdmaddressModal').modal('hide'); window.location.reload(); });
            },
            error: function (xhr) {
                Swal.fire({ title: 'Gagal!', text: xhr.responseJSON?.message || 'Terjadi kesalahan', icon: 'error', confirmButtonColor: '#dc2626' });
            }
        });
    });

    // Import Excel
    $('#importExcelForm').on('submit', function (e) {
        e.preventDefault();
        if (!$('#excelFile')[0].files.length) {
            Swal.fire({ title: 'Error!', text: 'Pilih file Excel terlebih dahulu', icon: 'error', confirmButtonColor: '#dc2626' });
            return;
        }
        $('#importProgress').removeClass('d-none');
        $('#importButton').prop('disabled', true);

        $.ajax({
            url: '{{ route("admaddresses.import") }}',
            type: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function (res) {
                $('#importProgress').addClass('d-none');
                $('#importButton').prop('disabled', false);
                Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', confirmButtonColor: '#059669' })
                    .then(() => { $('#importExcelModal').modal('hide'); window.location.reload(); });
            },
            error: function (xhr) {
                $('#importProgress').addClass('d-none');
                $('#importButton').prop('disabled', false);
                Swal.fire({ title: 'Gagal!', text: xhr.responseJSON?.message || 'Terjadi kesalahan', icon: 'error', confirmButtonColor: '#dc2626' });
            }
        });
    });

    $('#importExcelModal').on('hidden.bs.modal', function () {
        $('#importExcelForm')[0].reset();
        $('#importProgress').addClass('d-none');
        $('#importButton').prop('disabled', false);
    });

    // Search
    $('#searchButton').on('click', performSearch);
    $('#searchInput').on('keypress', function (e) { if (e.which === 13) performSearch(); });
    $('#perPageSelect').on('change', function () { updateUrl($(this).val(), $('#searchInput').val()); });

    function performSearch() { updateUrl($('#perPageSelect').val(), $('#searchInput').val()); }

    function updateUrl(perPage, search) {
        const url = new URL(window.location.href);
        perPage && perPage !== '50' ? url.searchParams.set('per_page', perPage) : url.searchParams.delete('per_page');
        search && search.trim() ? url.searchParams.set('search', search) : url.searchParams.delete('search');
        window.location.href = url.toString();
    }
});

function openEditModal(id) {
    $.ajax({
        url: `/admaddresses/${id}/edit`,
        type: 'GET',
        success: function (data) {
            $('#edit_id').val(data.id);
            $('#edit_part_no').val(data.part_no);
            $('#edit_customer_code').val(data.customer_code);
            $('#edit_model').val(data.model);
            $('#edit_part_name').val(data.part_name);
            $('#edit_qty_kbn').val(data.qty_kbn);
            $('#edit_line').val(data.line);
            $('#edit_rack_no').val(data.rack_no);
            $('#editAdmaddressModal').modal('show');
        },
        error: function () {
            Swal.fire({ title: 'Error!', text: 'Gagal mengambil data', icon: 'error', confirmButtonColor: '#dc2626' });
        }
    });
}
</script>
@endpush
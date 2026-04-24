@extends('layouts.app')

@section('title', 'HPM Address')
@section('page-title', 'HPM ADDRESS')

@section('content')

    <div class="d-flex justify-content-end align-items-center gap-2 mb-3 mt-3">

        <!-- Per Page Dropdown -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-1">
                <select class="form-select form-select-sm border-0" id="perPageSelect" style="width: auto;">
                    <option value="50"  {{ request('per_page', 50) == 50    ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100       ? 'selected' : '' }}>100</option>
                    <option value="500" {{ request('per_page') == 500       ? 'selected' : '' }}>500</option>
                    <option value="all" {{ request('per_page') == 'all'     ? 'selected' : '' }}>All</option>
                </select>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="input-group" style="width: 300px;">
            <input type="text" class="form-control" id="searchInput"
                   placeholder="Cari Part No, Part Name, Rack No..."
                   value="{{ request('search') }}">
            <button class="btn btn-secondary" type="button" id="searchButton">
                <i class="bi bi-search"></i>
            </button>
        </div>

        <!-- Menu & Badge -->
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
                                   data-bs-toggle="modal" data-bs-target="#createHpmAddressModal">
                                    <i class="bi bi-plus-circle me-2"></i> Tambah Data
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-success" href="#"
                                   data-bs-toggle="modal" data-bs-target="#importHpmModal">
                                    <i class="bi bi-file-earmark-excel text-success me-2"></i> Import Excel
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Total Badge -->
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
        <table class="table table-compact w-100 mt-1" id="hpmAddressesTable">
            <thead>
                <tr class="fs-6">
                    <th>#</th>
                    <th>Part No</th>
                    <th>Part Name</th>
                    <th>Rack No</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($addresses as $index => $address)
                    <tr class="fs-5">
                        <td>{{ $addresses->firstItem() + $index }}</td>
                        <td><strong>{{ $address->part_no }}</strong></td>
                        <td>{{ $address->part_name }}</td>
                        <td><strong>{{ $address->rack_no }}</strong></td>
                        <td>
                            <div class="d-flex justify-content-center p-1" style="gap: 0;">
                                <button onclick="openHpmEditModal({{ $address->id }})"
                                        class="btn btn-warning btn-sm btn-action-square"
                                        style="border-radius: 6px 0 0 6px; margin: 0;" title="Edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <form action="{{ route('hpm-addresses.destroy', $address->id) }}"
                                      method="POST" class="d-inline hpm-delete-form" style="margin: 0;">
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
                        <td colspan="5" class="text-center py-4">
                            <div class="text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">Belum ada data HPM Address</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        {{ $addresses->links() }}
    </div>

    @include('hpm-addresses.create')
    @include('hpm-addresses.edit')
    @include('hpm-addresses.import')

@endsection

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

@push('scripts')
<script>
$(document).ready(function () {

    // ── Delete ──────────────────────────────────────────────
    $('.hpm-delete-form').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
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
        }).then(result => {
            if (!result.isConfirmed) return;
            Swal.fire({ title: 'Menghapus...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                success: res => Swal.fire('Berhasil!', res.message || 'Data berhasil dihapus', 'success').then(() => location.reload()),
                error: xhr => Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error')
            });
        });
    });

    // ── Edit Submit ──────────────────────────────────────────
    $('#editHpmAddressForm').on('submit', function (e) {
        e.preventDefault();
        const id = $('#hpm_edit_id').val();
        $.ajax({
            url: `/hpm-addresses/${id}`,
            type: 'PUT',
            data: $(this).serialize(),
            success: res => {
                Swal.fire('Berhasil!', 'Data berhasil diupdate', 'success').then(() => {
                    $('#editHpmAddressModal').modal('hide');
                    location.reload();
                });
            },
            error: xhr => Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error')
        });
    });

    // ── Create Submit ────────────────────────────────────────
    $('#createHpmAddressForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: '{{ route("hpm-addresses.store") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: res => {
                Swal.fire('Berhasil!', res.message, 'success').then(() => {
                    $('#createHpmAddressModal').modal('hide');
                    location.reload();
                });
            },
            error: xhr => Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error')
        });
    });

    // ── Import Submit ────────────────────────────────────────
    $('#importHpmForm').on('submit', function (e) {
        e.preventDefault();
        const fileInput = $('#hpmExcelFile')[0];
        if (!fileInput.files.length) {
            Swal.fire('Error!', 'Silakan pilih file Excel terlebih dahulu', 'error');
            return;
        }
        const formData = new FormData(this);
        $('#hpmImportProgress').removeClass('d-none');
        $('#hpmImportButton').prop('disabled', true);

        $.ajax({
            url: '{{ route("hpm-addresses.import") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: res => {
                $('#hpmImportProgress').addClass('d-none');
                $('#hpmImportButton').prop('disabled', false);
                Swal.fire('Berhasil!', res.message, 'success').then(() => {
                    $('#importHpmModal').modal('hide');
                    location.reload();
                });
            },
            error: xhr => {
                $('#hpmImportProgress').addClass('d-none');
                $('#hpmImportButton').prop('disabled', false);
                Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan saat import', 'error');
            }
        });
    });

    $('#importHpmModal').on('hidden.bs.modal', function () {
        $('#importHpmForm')[0].reset();
        $('#hpmImportProgress').addClass('d-none');
        $('#hpmImportButton').prop('disabled', false);
    });

    // ── Search & Per Page ────────────────────────────────────
    $('#searchButton').on('click', () => performSearch());
    $('#searchInput').on('keypress', e => { if (e.which === 13) performSearch(); });
    $('#perPageSelect').on('change', function () { updateUrl($(this).val(), $('#searchInput').val()); });

    function performSearch() { updateUrl($('#perPageSelect').val(), $('#searchInput').val()); }

    function updateUrl(perPage, search) {
        const url = new URL(window.location.href);
        perPage && perPage !== '50' ? url.searchParams.set('per_page', perPage) : url.searchParams.delete('per_page');
        search && search.trim() ? url.searchParams.set('search', search) : url.searchParams.delete('search');
        window.location.href = url.toString();
    }
});

// ── Open Edit Modal ──────────────────────────────────────────
function openHpmEditModal(id) {
    $.ajax({
        url: `/hpm-addresses/${id}/edit`,
        type: 'GET',
        success: data => {
            $('#hpm_edit_id').val(data.id);
            $('#hpm_edit_part_no').val(data.part_no);
            $('#hpm_edit_part_name').val(data.part_name);
            $('#hpm_edit_rack_no').val(data.rack_no);
            $('#editHpmAddressModal').modal('show');
        },
        error: () => Swal.fire('Error!', 'Gagal mengambil data', 'error')
    });
}
</script>
@endpush
@extends('layouts.app')

@section('title', 'Bank Kanban TGI')
@section('page-title', 'BANK KANBAN TGI')
@section('body-class', 'kanbanbanktgi-page')

@section('content')

    <div class="d-flex justify-content-end align-items-center gap-2 mb-3 mt-3">

        <div class="input-group" style="width: 260px;">
            <input type="text" class="form-control" id="searchInput"
                   placeholder="Cari Part No..."
                   value="{{ request('search') }}">
            <button class="btn btn-secondary" type="button" id="searchButton">
                <i class="bi bi-search"></i>
            </button>
        </div>

        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#uploadBankModal">
            <i class="bi bi-file-earmark-plus me-1"></i> Upload PDF ke Bank
        </button>

        <div class="bg-primary card border-0 shadow-sm">
            <div class="card-body p-1">
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-10 p-2 rounded me-2">
                        <i class="bi bi-archive text-white fs-5"></i>
                    </div>
                    <div>
                        <small class="text-white d-block fw-bold me-3" style="font-size: 0.7rem;">Total</small>
                        <h5 class="mb-0 fw-bold text-white">{{ $items->total() }}</h5>
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
                    <th>Nama File Asli</th>
                    <th>Jumlah KBN</th>
                    <th>Kanban/Halaman</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr class="fs-5">
                        <td><strong>{{ $item->part_no }}</strong></td>
                        <td>{{ $item->original_filename }}</td>
                        <td>{{ $item->jumlah_kbn ?? '-' }}</td>
                        <td>{{ $item->labels_per_page }}</td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm delete-bank-btn" data-id="{{ $item->id }}">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <div class="text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">Bank kanban TGI masih kosong</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        {{ $items->links() }}
    </div>

    <!-- Modal Upload -->
    <div class="modal fade" id="uploadBankModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="uploadBankForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold">Upload PDF ke Bank Kanban TGI</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small text-secondary">File PDF (bisa pilih banyak sekaligus)</label>
                            <input type="file" name="pdf[]" accept="application/pdf" required multiple class="form-control">
                            <div class="form-text">Nama file bebas — part no otomatis diambil dari isi PDF-nya.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-secondary">Jumlah kanban per halaman (di dalam PDF bank)</label>
                            <input type="number" name="labels_per_page" value="3" min="1" max="10" class="form-control">
                        </div>
                        <div id="uploadBankProgress" class="d-none">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                            <span class="small text-muted ms-1">Memproses upload...</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" id="uploadBankButton" class="btn btn-success">Upload</button>
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

    $('#uploadBankForm').on('submit', function (e) {
        e.preventDefault();
        const fileInput = this.querySelector('input[name="pdf[]"]');
        if (!fileInput.files.length) {
            Swal.fire({ title: 'Error!', text: 'Pilih minimal 1 file PDF', icon: 'error', confirmButtonColor: '#dc2626' });
            return;
        }
        $('#uploadBankProgress').removeClass('d-none');
        $('#uploadBankButton').prop('disabled', true);

        $.ajax({
            url: '{{ route("kanbanbanktgi.upload") }}',
            type: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function (res) {
                $('#uploadBankProgress').addClass('d-none');
                $('#uploadBankButton').prop('disabled', false);

                let text = res.message;
                if (res.failed && res.failed.length) {
                    text += '\n\nGagal:\n' + res.failed.map(f => `${f.filename}: ${f.reason}`).join('\n');
                }

                Swal.fire({ title: 'Selesai!', text: text, icon: res.uploaded > 0 ? 'success' : 'warning', confirmButtonColor: '#059669' })
                    .then(() => { $('#uploadBankModal').modal('hide'); window.location.reload(); });
            },
            error: function (xhr) {
                $('#uploadBankProgress').addClass('d-none');
                $('#uploadBankButton').prop('disabled', false);
                Swal.fire({ title: 'Gagal!', text: xhr.responseJSON?.message || 'Terjadi kesalahan', icon: 'error', confirmButtonColor: '#dc2626' });
            }
        });
    });

    $('#uploadBankModal').on('hidden.bs.modal', function () {
        $('#uploadBankForm')[0].reset();
        $('#uploadBankProgress').addClass('d-none');
        $('#uploadBankButton').prop('disabled', false);
    });

    $('.delete-bank-btn').on('click', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Hapus data ini?',
            text: 'File PDF di bank akan dihapus permanen!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/kanban-bank-tgi/${id}`,
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

    $('#searchButton').on('click', function () {
        const url = new URL(window.location.href);
        const search = $('#searchInput').val();
        search && search.trim() ? url.searchParams.set('search', search) : url.searchParams.delete('search');
        window.location.href = url.toString();
    });
    $('#searchInput').on('keypress', function (e) { if (e.which === 13) $('#searchButton').click(); });
});
</script>
@endpush
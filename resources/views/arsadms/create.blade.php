{{-- resources/views/arsadms/create.blade.php --}}
<div class="modal fade" id="createArsAdmModal" tabindex="-1" aria-labelledby="createArsAdmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="createArsAdmModalLabel">
                    <i class="bi bi-plus-circle me-2"></i> Tambah Data ARS ADM
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="createArsAdmForm" action="{{ route('arsadms.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Part No <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="part_no" required placeholder="e.g. 67054-BZ040">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Min</label>
                            <input type="number" step="0.01" class="form-control" name="min" value="0" placeholder="0">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Max</label>
                            <input type="text" class="form-control" name="max" value="0" placeholder="0">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Part Cat</label>
                            <input type="text" class="form-control" name="part_cat" placeholder="e.g. P1-BIG">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Packing Type</label>
                            <input type="text" class="form-control" name="packing_type" placeholder="e.g. TP-362">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Area Code</label>
                            <input type="text" class="form-control" name="area_code" placeholder="e.g. A">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Part Type</label>
                            <input type="text" class="form-control" name="part_type" placeholder="e.g. PRODUCTION">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">WH Zone</label>
                            <input type="text" class="form-control" name="wh_zone" placeholder="e.g. DOOR ASSY PL1">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Rack No</label>
                            <input type="text" class="form-control" name="rack_no" placeholder="e.g. A14">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Rack Layer</label>
                            <input type="text" class="form-control" name="rack_layer" placeholder="e.g. 1">
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

@push('scripts')
<script>
$('#createArsAdmForm').on('submit', function (e) {
    e.preventDefault();
    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: $(this).serialize(),
        success: function (res) {
            Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', confirmButtonColor: '#059669' })
                .then(() => { $('#createArsAdmModal').modal('hide'); window.location.reload(); });
        },
        error: function (xhr) {
            const errors = xhr.responseJSON?.errors;
            const msg = errors ? Object.values(errors).flat().join('\n') : (xhr.responseJSON?.message || 'Terjadi kesalahan');
            Swal.fire({ title: 'Gagal!', text: msg, icon: 'error', confirmButtonColor: '#dc2626' });
        }
    });
});

$('#createArsAdmModal').on('hidden.bs.modal', function () {
    $('#createArsAdmForm')[0].reset();
});
</script>
@endpush

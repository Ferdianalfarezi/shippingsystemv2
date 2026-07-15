{{-- resources/views/arsadms/edit.blade.php --}}
<div class="modal fade" id="editArsAdmModal" tabindex="-1" aria-labelledby="editArsAdmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="editArsAdmModalLabel">
                    <i class="bi bi-pencil-fill me-2"></i> Edit Data ARS ADM
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="editArsAdmForm">
                @csrf
                <input type="hidden" id="edit_id" name="id">
                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Part No <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_part_no" name="part_no" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Min</label>
                            <input type="number" step="0.01" class="form-control" id="edit_min" name="min">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Max</label>
                            <input type="text" class="form-control" id="edit_max" name="max">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Part Cat</label>
                            <input type="text" class="form-control" id="edit_part_cat" name="part_cat">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Packing Type</label>
                            <input type="text" class="form-control" id="edit_packing_type" name="packing_type">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Area Code</label>
                            <input type="text" class="form-control" id="edit_area_code" name="area_code">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Part Type</label>
                            <input type="text" class="form-control" id="edit_part_type" name="part_type">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">WH Zone</label>
                            <input type="text" class="form-control" id="edit_wh_zone" name="wh_zone">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Rack No</label>
                            <input type="text" class="form-control" id="edit_rack_no" name="rack_no">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Rack Layer</label>
                            <input type="text" class="form-control" id="edit_rack_layer" name="rack_layer">
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-save me-1"></i> Update
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

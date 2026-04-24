<!-- Modal Edit HPM Address -->
<div class="modal fade" id="editHpmAddressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-fill me-2"></i>Edit HPM Address
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editHpmAddressForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="hpm_edit_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Part No <span class="text-danger">*</span></label>
                        <input type="text" name="part_no" id="hpm_edit_part_no" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Part Name</label>
                        <input type="text" name="part_name" id="hpm_edit_part_name" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Rack No</label>
                        <input type="text" name="rack_no" id="hpm_edit_rack_no" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-save me-1"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
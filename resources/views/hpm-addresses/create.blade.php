<!-- Modal Create HPM Address -->
<div class="modal fade" id="createHpmAddressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>Tambah HPM Address
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createHpmAddressForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Part No <span class="text-danger">*</span></label>
                        <input type="text" name="part_no" id="create_part_no" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Part Name</label>
                        <input type="text" name="part_name" id="create_part_name" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Rack No</label>
                        <input type="text" name="rack_no" id="create_rack_no" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
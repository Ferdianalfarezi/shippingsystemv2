<!-- Modal Edit Address -->
<div class="modal fade" id="editAddressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square me-2"></i>Edit Data Address
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="editAddressForm" method="POST">
                @csrf
                <input type="hidden" id="edit_id" name="id">

                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-dark">Part No <span class="text-danger">*</span></label>
                            <input type="text" id="edit_part_no" name="part_no" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-dark">Customer Code</label>
                            <input type="text" id="edit_customer_code" name="customer_code" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-dark">Model</label>
                            <input type="text" id="edit_model" name="model" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-dark">Part Name</label>
                            <input type="text" id="edit_part_name" name="part_name" class="form-control">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold text-dark">Qty Kbn</label>
                            <input type="text" id="edit_qty_kbn" name="qty_kbn" class="form-control">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold text-dark">Line</label>
                            <input type="text" id="edit_line" name="line" class="form-control">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold text-dark">Rack No</label>
                            <input type="text" id="edit_rack_no" name="rack_no" class="form-control">
                        </div>

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

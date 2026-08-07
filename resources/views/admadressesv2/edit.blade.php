<div class="modal fade" id="editAdmAddressV2Modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="bi bi-pencil-fill me-2"></i>Edit Data ADM Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editAdmAddressV2Form">
                @csrf
                <input type="hidden" id="edit_id_v2" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-dark">Part No <span class="text-danger">*</span></label>
                            <input type="text" name="part_no" id="edit_part_no_v2" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-dark">Customer Code</label>
                            <input type="text" name="customer_code" id="edit_customer_code_v2" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-dark">Part Name</label>
                            <input type="text" name="part_name" id="edit_part_name_v2" class="form-control">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold text-dark">Qty Kbn</label>
                            <input type="text" name="qty_kbn" id="edit_qty_kbn_v2" class="form-control">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold text-dark">Rack No</label>
                            <input type="text" name="rack_no" id="edit_rack_no_v2" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-save me-1"></i>Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
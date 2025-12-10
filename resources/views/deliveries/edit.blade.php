<!-- Modal Edit Delivery -->
<div class="modal fade" id="editDeliveryModal" tabindex="-1" aria-labelledby="editDeliveryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editDeliveryModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit Data Delivery
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editDeliveryForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" id="edit_delivery_id" name="delivery_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_route" class="form-label">Route <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_route" name="route" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="edit_logistic_partners" class="form-label">Logistic Partners <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_logistic_partners" name="logistic_partners" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_no_dn" class="form-label">No DN <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_no_dn" name="no_dn" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="edit_customers" class="form-label">Customer <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_customers" name="customers" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_dock" class="form-label">Dock <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_dock" name="dock" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="edit_cycle" class="form-label">Cycle <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit_cycle" name="cycle" min="1" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="edit_address" class="form-label">Address <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_address" name="address" required>
                                <option value="">Pilih Address</option>
                                <option value="Shipping 1">Shipping 1</option>
                                <option value="Shipping 2">Shipping 2</option>
                                <option value="Shipping 3">Shipping 3</option>
                                <option value="Shipping 4">Shipping 4</option>
                                <option value="Shipping 5">Shipping 5</option>
                                <option value="Shipping 6">Shipping 6</option>
                                <option value="Shipping 7">Shipping 7</option>
                                <option value="Shipping 8">Shipping 8</option>
                                <option value="Shipping 9">Shipping 9</option>
                                <option value="Shipping 10">Shipping 10</option>
                                <option value="Shipping Ex 1">Shipping Ex 1</option>
                                <option value="Shipping Ex 2">Shipping Ex 2</option>
                                <option value="Shipping Ex 3">Shipping Ex 3</option>
                                <option value="Shipping Ex 4">Shipping Ex 4</option>
                                <option value="Shipping Ex 5">Shipping Ex 5</option>
                            </select>
                        </div>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Note:</strong> Status dihitung otomatis berdasarkan waktu scan.
                        <br>
                        <small class="text-muted">Normal: &lt; 48 jam business hours | Delay: ≥ 48 jam business hours (exclude Sabtu-Minggu)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning" id="updateDeliveryBtn">
                        <i class="bi bi-save me-1"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Handle Edit Form Submit
    $('#editDeliveryForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const url = form.attr('action');
        const formData = form.serialize();
        
        // Show loading
        $('#updateDeliveryBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(response) {
                $('#editDeliveryModal').modal('hide');
                
                Swal.fire({
                    title: 'Berhasil!',
                    text: response.message || 'Data berhasil diupdate',
                    icon: 'success',
                    confirmButtonColor: '#059669'
                }).then(() => {
                    window.location.reload();
                });
            },
            error: function(xhr) {
                $('#updateDeliveryBtn').prop('disabled', false).html('<i class="bi bi-save me-1"></i> Update');
                
                let errorMessage = 'Terjadi kesalahan saat mengupdate data';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage = errors.join('<br>');
                }
                
                Swal.fire({
                    title: 'Gagal!',
                    html: errorMessage,
                    icon: 'error',
                    confirmButtonColor: '#dc2626'
                });
            }
        });
    });

    // Reset form when modal is closed
    $('#editDeliveryModal').on('hidden.bs.modal', function () {
        $('#editDeliveryForm')[0].reset();
        $('#updateDeliveryBtn').prop('disabled', false).html('<i class="bi bi-save me-1"></i> Update');
    });
</script>
@endpush
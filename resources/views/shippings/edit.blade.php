<!-- Modal Edit Shipping -->
<div class="modal fade" id="editShippingModal" tabindex="-1" aria-labelledby="editShippingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold" id="editShippingModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit Data Shipping
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editShippingForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_shipping_id" name="id">
                
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Route -->
                        <div class="col-md-4">
                            <label for="edit_route" class="form-label fw-semibold text-dark">Route <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_route" name="route" required>
                        </div>
                        
                        <!-- Logistic Partners -->
                        <div class="col-md-4">
                            <label for="edit_logistic_partners" class="form-label fw-semibold text-dark">Logistic Partners <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_logistic_partners" name="logistic_partners" required>
                        </div>
                        
                        <!-- No DN -->
                        <div class="col-md-4">
                            <label for="edit_no_dn" class="form-label fw-semibold text-dark">No DN <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_no_dn" name="no_dn" required>
                        </div>
                        
                        <!-- Customers -->
                        <div class="col-md-4">
                            <label for="edit_customers" class="form-label fw-semibold text-dark">Customers <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_customers" name="customers" required>
                        </div>
                        
                        <!-- Dock -->
                        <div class="col-md-4">
                            <label for="edit_dock" class="form-label fw-semibold text-dark">Dock <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_dock" name="dock" required>
                        </div>
                        
                        <!-- Cycle -->
                        <div class="col-md-4">
                            <label for="edit_cycle" class="form-label fw-semibold text-dark">Cycle <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit_cycle" name="cycle" min="1" required>
                        </div>
                        
                        <!-- Delivery Date -->
                        <div class="col-md-4">
                            <label for="edit_delivery_date" class="form-label fw-semibold text-dark">Delivery Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="edit_delivery_date" name="delivery_date" required>
                        </div>
                        
                        <!-- Delivery Time -->
                        <div class="col-md-4">
                            <label for="edit_delivery_time" class="form-label fw-semibold text-dark">Delivery Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="edit_delivery_time" name="delivery_time" required>
                        </div>
                        
                        <!-- Address -->
                        <div class="col-md-4">
                            <label for="edit_address" class="form-label fw-semibold text-dark">Address <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_address" name="address" required>
                                <option value="">Pilih Address...</option>
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
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-circle me-1"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Handle Edit Form Submit
    $('#editShippingForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const url = form.attr('action');
        const formData = form.serialize();
        
        Swal.fire({
            title: 'Mengupdate...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(response) {
                Swal.fire({
                    title: 'Berhasil!',
                    text: response.message || 'Data berhasil diupdate',
                    icon: 'success',
                    confirmButtonColor: '#059669'
                }).then(() => {
                    $('#editShippingModal').modal('hide');
                    window.location.reload();
                });
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan saat mengupdate data';
                
                if (xhr.responseJSON?.errors) {
                    const errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join('<br>');
                } else if (xhr.responseJSON?.message) {
                    errorMessage = xhr.responseJSON.message;
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
    $('#editShippingModal').on('hidden.bs.modal', function () {
        $('#editShippingForm')[0].reset();
    });
</script>
@endpush
<!-- Edit Milkrun Modal -->
<div class="modal fade" id="editMilkrunModal" tabindex="-1" aria-labelledby="editMilkrunModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editMilkrunModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit Data Milkrun
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editMilkrunForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" id="edit_milkrun_id" name="milkrun_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_customers" class="form-label fw-bold">Customers <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_customers" name="customers" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_route" class="form-label fw-bold">Route <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_route" name="route" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_logistic_partners" class="form-label fw-bold">Logistic Partners</label>
                            <input type="text" class="form-control" id="edit_logistic_partners" name="logistic_partners">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="edit_cycle" class="form-label fw-bold">Cycle <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit_cycle" name="cycle" min="1" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="edit_dock" class="form-label fw-bold">Dock</label>
                            <input type="text" class="form-control" id="edit_dock" name="dock">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_delivery_date" class="form-label fw-bold">Delivery Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="edit_delivery_date" name="delivery_date" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_delivery_time" class="form-label fw-bold">Delivery Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="edit_delivery_time" name="delivery_time" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_address" class="form-label fw-bold">Address</label>
                            <input type="text" class="form-control" id="edit_address" name="address" maxlength="50">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_arrival" class="form-label fw-bold">Arrival</label>
                            <input type="datetime-local" class="form-control" id="edit_arrival" name="arrival">
                            <small class="text-muted">Kosongkan jika belum arrival</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_departure" class="form-label fw-bold">Departure</label>
                            <input type="datetime-local" class="form-control" id="edit_departure" name="departure">
                            <small class="text-muted">Kosongkan jika belum departure</small>
                        </div>
                    </div>
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-lg me-1"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Handle edit form submission
    $('#editMilkrunForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const url = form.attr('action');
        
        Swal.fire({
            title: 'Menyimpan...',
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
            data: form.serialize(),
            success: function(response) {
                Swal.fire({
                    title: 'Berhasil!',
                    text: response.message || 'Data berhasil diupdate',
                    icon: 'success',
                    confirmButtonColor: '#059669'
                }).then(() => {
                    $('#editMilkrunModal').modal('hide');
                    window.location.reload();
                });
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan saat mengupdate data';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join('<br>');
                    } else if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
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
</script>
@endpush
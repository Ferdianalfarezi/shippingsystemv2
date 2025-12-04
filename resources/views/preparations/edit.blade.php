<!-- Modal Edit Preparation -->
<div class="modal fade" id="editPreparationModal" tabindex="-1" aria-labelledby="editPreparationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editPreparationModalLabel">
                    <i class="bi bi-pencil-square"></i> Edit Data Preparation
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editPreparationForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <!-- Column 1 -->
                        <div class="col-md-6">
                            <!-- Route -->
                            <div class="mb-3">
                                <label for="edit_route" class="form-label text-dark">Route <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="edit_route" 
                                       name="route" 
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Logistic Partners -->
                            <div class="mb-3">
                                <label for="edit_logistic_partners" class="form-label text-dark">Logistic Partners <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="edit_logistic_partners" 
                                       name="logistic_partners" 
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- No DN -->
                            <div class="mb-3">
                                <label for="edit_no_dn" class="form-label text-dark">No DN <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="edit_no_dn" 
                                       name="no_dn" 
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Customers -->
                            <div class="mb-3">
                                <label for="edit_customers" class="form-label text-dark">Customers <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="edit_customers" 
                                       name="customers" 
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Dock -->
                            <div class="mb-3">
                                <label for="edit_dock" class="form-label text-dark">Dock <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="edit_dock" 
                                       name="dock" 
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <!-- Column 2 -->
                        <div class="col-md-6">
                            <!-- Delivery Date -->
                            <div class="mb-3">
                                <label for="edit_delivery_date" class="form-label text-dark">Delivery Date <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control" 
                                       id="edit_delivery_date" 
                                       name="delivery_date" 
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Delivery Time -->
                            <div class="mb-3">
                                <label for="edit_delivery_time" class="form-label text-dark">Delivery Time <span class="text-danger">*</span></label>
                                <input type="time" 
                                       class="form-control" 
                                       id="edit_delivery_time" 
                                       name="delivery_time" 
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Cycle -->
                            <div class="mb-3">
                                <label for="edit_cycle" class="form-label text-dark">Cycle <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control" 
                                       id="edit_cycle" 
                                       name="cycle" 
                                       min="1"
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Pulling Date -->
                            <div class="mb-3">
                                <label for="edit_pulling_date" class="form-label text-dark">Pulling Date <span class="text-danger">*</span></label>
                                <input type="date"  
                                       class="form-control" 
                                       id="edit_pulling_date" 
                                       name="pulling_date" 
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Pulling Time -->
                            <div class="mb-3">
                                <label for="edit_pulling_time" class="form-label text-dark">Pulling Time <span class="text-danger">*</span></label>
                                <input type="time" 
                                       class="form-control" 
                                       id="edit_pulling_time" 
                                       name="pulling_time" 
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-save"></i> Update Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Function to open edit modal
function openEditModal(preparationId) {
    
    // Fetch preparation data
    $.ajax({
        url: `/preparations/${preparationId}/edit`,
        method: 'GET',
        success: function(response) {
            Swal.close();
            
            // Fill form with data
            $('#edit_route').val(response.route);
            $('#edit_logistic_partners').val(response.logistic_partners);
            $('#edit_no_dn').val(response.no_dn);
            $('#edit_customers').val(response.customers);
            $('#edit_dock').val(response.dock);
            $('#edit_delivery_date').val(response.delivery_date);
            $('#edit_delivery_time').val(response.delivery_time);
            $('#edit_cycle').val(response.cycle);
            $('#edit_pulling_date').val(response.pulling_date);
            $('#edit_pulling_time').val(response.pulling_time);
            
            // Set form action
            $('#editPreparationForm').attr('action', `/preparations/${preparationId}`);
            
            // Update modal title
            $('#editPreparationModalLabel').html(`<i class="bi bi-pencil-square"></i> Edit Data Preparation - ${response.no_dn}`);
            
            // Show modal
            $('#editPreparationModal').modal('show');
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Gagal memuat data preparation',
            });
        }
    });
}

$(document).ready(function() {
    // Handle form submission
    $('#editPreparationForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const formData = form.serialize();
        const url = form.attr('action');
        
        // Reset previous errors
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.invalid-feedback').text('');
        
        // Show loading
        Swal.fire({
            title: 'Mengupdate...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Submit via AJAX
        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Data preparation berhasil diupdate',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    // Close modal
                    $('#editPreparationModal').modal('hide');
                    // Reload page or update table
                    location.reload();
                });
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Validation errors
                    const errors = xhr.responseJSON.errors;
                    
                    // Show validation errors
                    $.each(errors, function(key, value) {
                        const input = form.find(`[name="${key}"]`);
                        input.addClass('is-invalid');
                        input.next('.invalid-feedback').text(value[0]);
                    });
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        text: 'Mohon periksa kembali form Anda',
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Terjadi kesalahan saat mengupdate data',
                    });
                }
            }
        });
    });
    
    // Reset form when modal is closed
    $('#editPreparationModal').on('hidden.bs.modal', function() {
        $('#editPreparationForm')[0].reset();
        $('#editPreparationForm').find('.is-invalid').removeClass('is-invalid');
        $('#editPreparationForm').find('.invalid-feedback').text('');
    });
});
</script>
@endpush
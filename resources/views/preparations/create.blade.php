<!-- Modal Create Preparation -->
<div class="modal fade" id="createPreparationModal" tabindex="-1" aria-labelledby="createPreparationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="createPreparationModalLabel">
                    <i class="bi bi-plus-circle"></i> Tambah Data Preparation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('preparations.store') }}" method="POST" id="createPreparationForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <!-- Column 1 -->
                        <div class="col-md-6">
                            <!-- Route -->
                            <div class="mb-3">
                                <label for="route" class="form-label text-dark">Route <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="route" 
                                       name="route" 
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Logistic Partners -->
                            <div class="mb-3">
                                <label for="logistic_partners" class="form-label text-dark">Logistic Partners <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="logistic_partners" 
                                       name="logistic_partners" 
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- No DN -->
                            <div class="mb-3">
                                <label for="no_dn" class="form-label text-dark">No DN <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="no_dn" 
                                       name="no_dn" 
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Customers -->
                            <div class="mb-3">
                                <label for="customers" class="form-label text-dark">Customers <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="customers" 
                                       name="customers" 
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Dock -->
                            <div class="mb-3">
                                <label for="dock" class="form-label text-dark">Dock <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="dock" 
                                       name="dock" 
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <!-- Column 2 -->
                        <div class="col-md-6">
                            <!-- Delivery Date -->
                            <div class="mb-3">
                                <label for="delivery_date" class="form-label text-dark">Delivery Date <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control" 
                                       id="delivery_date" 
                                       name="delivery_date" 
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Delivery Time -->
                            <div class="mb-3">
                                <label for="delivery_time" class="form-label text-dark">Delivery Time <span class="text-danger">*</span></label>
                                <input type="time" 
                                       class="form-control" 
                                       id="delivery_time" 
                                       name="delivery_time" 
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Cycle -->
                            <div class="mb-3">
                                <label for="cycle" class="form-label text-dark">Cycle <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control" 
                                       id="cycle" 
                                       name="cycle" 
                                       min="1"
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Pulling Date -->
                            <div class="mb-3">
                                <label for="pulling_date" class="form-label text-dark">Pulling Date <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control" 
                                       id="pulling_date" 
                                       name="pulling_date" 
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Pulling Time -->
                            <div class="mb-3">
                                <label for="pulling_time" class="form-label text-dark">Pulling Time <span class="text-danger">*</span></label>
                                <input type="time" 
                                       class="form-control" 
                                       id="pulling_time" 
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
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Handle form submission
    $('#createPreparationForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const formData = form.serialize();
        const url = form.attr('action');
        
        // Reset previous errors
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.invalid-feedback').text('');
        
        // Show loading
        Swal.fire({
            title: 'Menyimpan...',
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
                    text: 'Data preparation berhasil ditambahkan',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    // Close modal
                    $('#createPreparationModal').modal('hide');
                    // Reset form
                    form[0].reset();
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
                        text: 'Terjadi kesalahan saat menyimpan data',
                    });
                }
            }
        });
    });
    
    // Reset form when modal is closed
    $('#createPreparationModal').on('hidden.bs.modal', function() {
        $('#createPreparationForm')[0].reset();
        $('#createPreparationForm').find('.is-invalid').removeClass('is-invalid');
        $('#createPreparationForm').find('.invalid-feedback').text('');
    });
});
</script>
@endpush
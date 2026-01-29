<!-- Modal Import Rack Excel -->
<div class="modal fade" id="importRackModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bi bi-box-seam me-2"></i>Update Rack dari Excel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="importRackForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Info:</strong> Import ini akan mengupdate <code>rack_no</code> berdasarkan <code>part_no</code> yang ada di database.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-info">Pilih File Excel</label>
                        <input type="file" name="file" id="rackExcelFile" class="form-control" accept=".xlsx,.xls" required>
                        <small class="text-muted">Format: .xlsx atau .xls | Kolom: PART_NO, RACK_NO</small>
                    </div>
                    
                    <div id="rackImportProgress" class="d-none">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" role="progressbar" style="width: 100%"></div>
                        </div>
                        <small class="text-muted">Sedang mengupdate data rack...</small>
                    </div>

                    <!-- Result Info -->
                    <div id="rackImportResult" class="d-none mt-3">
                        <div class="card">
                            <div class="card-body p-2">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="text-success">
                                            <i class="bi bi-check-circle fs-4"></i>
                                            <div class="fw-bold" id="updatedCount">0</div>
                                            <small>Updated</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-warning">
                                            <i class="bi bi-exclamation-triangle fs-4"></i>
                                            <div class="fw-bold" id="notFoundCount">0</div>
                                            <small>Not Found</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" id="importRackButton" class="btn btn-info text-white">
                        <i class="bi bi-arrow-repeat me-1"></i>Update Rack
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Handle Import Rack Form
    $('#importRackForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const fileInput = $('#rackExcelFile')[0];
        
        if (!fileInput.files.length) {
            Swal.fire({
                title: 'Error!',
                text: 'Silakan pilih file Excel terlebih dahulu',
                icon: 'error',
                confirmButtonColor: '#dc2626'
            });
            return;
        }
        
        $('#rackImportProgress').removeClass('d-none');
        $('#rackImportResult').addClass('d-none');
        $('#importRackButton').prop('disabled', true);
        
        $.ajax({
            url: '{{ route("addresses.import-rack") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#rackImportProgress').addClass('d-none');
                $('#importRackButton').prop('disabled', false);
                
                // Show result
                $('#updatedCount').text(response.updated || 0);
                $('#notFoundCount').text(response.not_found || 0);
                $('#rackImportResult').removeClass('d-none');
                
                let message = `${response.updated} data berhasil diupdate`;
                if (response.not_found > 0) {
                    message += `, ${response.not_found} part tidak ditemukan`;
                }
                
                Swal.fire({
                    title: 'Berhasil!',
                    text: message,
                    icon: 'success',
                    confirmButtonColor: '#059669'
                }).then(() => {
                    if (response.updated > 0) {
                        window.location.reload();
                    }
                });
            },
            error: function(xhr) {
                $('#rackImportProgress').addClass('d-none');
                $('#importRackButton').prop('disabled', false);
                
                Swal.fire({
                    title: 'Gagal!',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan saat mengupdate data',
                    icon: 'error',
                    confirmButtonColor: '#dc2626'
                });
            }
        });
    });

    // Reset form when modal closed
    $('#importRackModal').on('hidden.bs.modal', function () {
        $('#importRackForm')[0].reset();
        $('#rackImportProgress').addClass('d-none');
        $('#rackImportResult').addClass('d-none');
        $('#importRackButton').prop('disabled', false);
    });
});
</script>
@endpush
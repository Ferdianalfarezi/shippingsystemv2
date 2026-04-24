<!-- Modal Import Excel HPM Address -->
<div class="modal fade" id="importHpmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark-excel me-2"></i>Import Excel HPM Address
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="importHpmForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Info:</strong> Kolom yang dibaca: <code>PART_NO</code>, <code>PART_NAME</code>, <code>RACK_NO</code>.
                        Data yang sudah ada akan di-update, data baru akan ditambahkan.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-success">Pilih File Excel</label>
                        <input type="file" name="file" id="hpmExcelFile" class="form-control"
                               accept=".xlsx,.xls" required>
                        <small class="text-muted">Format: .xlsx atau .xls | Baris header ke-2 (baris 1 = judul)</small>
                    </div>

                    <div id="hpmImportProgress" class="d-none mt-2">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                 role="progressbar" style="width: 100%"></div>
                        </div>
                        <small class="text-muted">Sedang mengimport data...</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" id="hpmImportButton" class="btn btn-success">
                        <i class="bi bi-upload me-1"></i>Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
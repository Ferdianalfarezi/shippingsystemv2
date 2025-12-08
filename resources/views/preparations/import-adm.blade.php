<!-- Modal Import ADM Excel -->
<div class="modal fade" id="importAdmModal" tabindex="-1" aria-labelledby="importAdmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="importAdmModalLabel">
                    <i class="bi bi-file-earmark-excel me-2"></i>Import Data ADM (Excel)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="importAdmForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="admFile" class="form-label text-dark ms-1">Pilih File Excel ADM</label>
                        <input type="file" class="form-control" id="admFile" name="file" accept=".xlsx,.xls" required>
                        <div class="form-text ms-1">Format yang didukung: .xlsx, .xls (Max: 2MB)</div>
                    </div>

                    <div class="alert alert-danger mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Perhatian :</strong>
                        <ul class="mb-0 mt-2">
                            <li>Hanya Support data ADM SAP dan KAP</li>
                            <li>Logistic Partner otomatis diisi berdasarkan konfigurasi route</li>
                        </ul>
                    </div>

                    <!-- Progress bar -->
                    <div id="importAdmProgress" class="d-none">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                 role="progressbar" 
                                 style="width: 100%">
                                Mengimpor data ADM...
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger" id="importAdmButton">
                        <i class="bi bi-upload me-2"></i>Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
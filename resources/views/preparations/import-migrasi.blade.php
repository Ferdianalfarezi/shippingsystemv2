<!-- Modal Import Migrasi Excel -->
<div class="modal fade" id="importMigrasiModal" tabindex="-1" aria-labelledby="importMigrasiModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="importMigrasiModalLabel">
                    <i class="bi bi-database-fill-up me-2"></i>Import Data Migrasi (Excel)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="importMigrasiForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="migrasiFile" class="form-label text-dark ms-1">Pilih File Excel Migrasi</label>
                        <input type="file" class="form-control" id="migrasiFile" name="excel_file" accept=".xlsx,.xls" required>
                        <div class="form-text ms-1">Format yang didukung: .xlsx, .xls (Max: 20MB)</div>
                    </div>

                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Format File:</strong>
                        <ul class="mb-0 mt-2">
                            <li>File Excel harus memiliki header kolom</li>
                            <li>Kolom yang diperlukan: <code>order_no</code>, <code>customer</code>, <code>dock</code>, <code>delivery_date</code>, <code>delivery_time</code>, <code>cycle</code>, <code>route</code>, <code>logistic_partner</code>, <code>pulling_time</code>, <code>pulling_date</code></li>
                            <li>Data duplikat (berdasarkan order_no/DN) akan dilewati</li>
                        </ul>
                    </div>

                    <!-- Progress bar -->
                    <div id="importMigrasiProgress" class="d-none">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" 
                                 role="progressbar" 
                                 style="width: 100%">
                                Mengimpor data migrasi...
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info text-white" id="importMigrasiButton">
                        <i class="bi bi-upload me-2"></i>Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal Import Migrasi History Excel -->
<div class="modal fade" id="importMigrasiHistoryModal" tabindex="-1" aria-labelledby="importMigrasiHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="importMigrasiHistoryModalLabel">
                    <i class="bi bi-database-fill-up me-2"></i>Import Data Migrasi History (Excel)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="importMigrasiHistoryForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="migrasiHistoryFile" class="form-label text-dark ms-1">Pilih File Excel Migrasi</label>
                        <input type="file" class="form-control" id="migrasiHistoryFile" name="excel_file" accept=".xlsx,.xls" required>
                        <div class="form-text ms-1">Format yang didukung: .xlsx, .xls (Max: 50MB)</div>
                    </div>

                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Format File:</strong>
                        <ul class="mb-0 mt-2">
                            <li>File Excel harus memiliki header kolom</li>
                            <li>Kolom: <code>order_no</code>, <code>customer</code>, <code>dock</code>, <code>delivery_date</code>, <code>delivery_time</code>, <code>cycle</code>, <code>route</code>, <code>logistic_partner</code>, <code>address</code>, <code>arrival</code>, <code>scan_to_shipping</code>, <code>scan_to_delivery</code>, <code>scan_to_history</code></li>
                            <li>Data duplikat (berdasarkan order_no/DN) akan dilewati</li>
                        </ul>
                    </div>

                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Perhatian:</strong> File history bisa sangat besar. Proses import mungkin memakan waktu beberapa menit.
                    </div>

                    <!-- Progress bar -->
                    <div id="importMigrasiHistoryProgress" class="d-none">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" 
                                 role="progressbar" 
                                 style="width: 100%">
                                Mengimpor data migrasi history...
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info text-white" id="importMigrasiHistoryButton">
                        <i class="bi bi-upload me-2"></i>Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
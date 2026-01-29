<!-- Modal Import Migrasi Milkrun Excel -->
<div class="modal fade" id="importMigrasiMilkrunModal" tabindex="-1" aria-labelledby="importMigrasiMilkrunModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="importMigrasiMilkrunModalLabel">
                    <i class="bi bi-database-fill-up me-2"></i>Import Data Migrasi Milkrun (Excel)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="importMigrasiMilkrunForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="migrasiMilkrunFile" class="form-label text-dark ms-1">Pilih File Excel Migrasi</label>
                        <input type="file" class="form-control" id="migrasiMilkrunFile" name="excel_file" accept=".xlsx,.xls" required>
                        <div class="form-text ms-1">Format yang didukung: .xlsx, .xls (Max: 50MB)</div>
                    </div>

                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Format File:</strong>
                        <ul class="mb-0 mt-2">
                            <li>File Excel harus memiliki header kolom</li>
                            <li>Kolom wajib: <code>route</code>, <code>cycle</code>, <code>delivery_date</code>, <code>delivery_time</code></li>
                            <li>Kolom opsional: <code>order_no</code>, <code>customer</code>, <code>dock</code>, <code>logistic_partner</code>, <code>arrival</code>, <code>address</code></li>
                            <li>Data akan di-group berdasarkan Route + Cycle + Delivery Date/Time</li>
                            <li>Data duplikat akan dilewati</li>
                        </ul>
                    </div>

                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Perhatian:</strong> Data dari Excel akan di-group menjadi 1 milkrun per kombinasi Route+Cycle+Delivery. Semua <code>order_no</code> dalam group yang sama akan digabung ke dalam array <code>no_dns</code>.
                    </div>

                    <!-- Progress bar -->
                    <div id="importMigrasiMilkrunProgress" class="d-none">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" 
                                 role="progressbar" 
                                 style="width: 100%">
                                Mengimpor data migrasi milkrun...
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info text-white" id="importMigrasiMilkrunButton">
                        <i class="bi bi-upload me-2"></i>Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
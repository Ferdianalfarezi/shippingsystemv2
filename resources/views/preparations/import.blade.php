<!-- Modal Import Excel -->
<div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="importExcelModalLabel">
                    <i class="bi bi-file-earmark-excel me-2"></i>Import Data dari Excel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="importExcelForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="excelFile" class="form-label text-success ms-1">Pilih File Excel</label>
                        <input type="file" class="form-control" id="excelFile" name="file" accept=".xlsx,.xls" required>
                        <div class="form-text ms-1">Format yang didukung: .xlsx, .xls (Max: 2MB)</div>
                    </div>

                   <div class="alert alert-success mb-3">
                        <i></i>
                        Belum punya template?<br>

                        <a href="{{ route('import-excel.download-template') }}" class="alert-link">
                            <i class="bi bi-download me-2"></i>Download Template Excel
                        </a>
                    </div> 

                    <!-- Progress bar -->
                    <div id="importProgress" class="d-none">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 style="width: 100%">
                                Mengimpor data...
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" id="importButton">
                        <i class="bi bi-upload me-2"></i>Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
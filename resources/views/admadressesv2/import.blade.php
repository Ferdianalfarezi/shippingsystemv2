<div class="modal fade" id="importExcelV2Modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-file-earmark-excel me-2"></i>Import Excel</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="importExcelV2Form">
                @csrf
                <div class="modal-body">
                    <label class="form-label fw-bold text-dark">File Excel (.xlsx, .xls, .csv)</label>
                    <input type="file" name="file" id="excelFileV2" accept=".xlsx,.xls,.csv" class="form-control" required>
                    <div id="importProgressV2" class="d-none mt-3">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                        </div>
                        <small class="text-muted">Mengimpor data, mohon tunggu...</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="importButtonV2" class="btn btn-success"><i class="bi bi-upload me-1"></i>Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
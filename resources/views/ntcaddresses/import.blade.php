<div class="modal fade" id="importNtcModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="importNtcForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title fw-bold">Import Excel NTC Address</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label small text-secondary">File Excel (.xlsx)</label>
                    <input type="file" name="excel" id="excelNtcFile" accept=".xlsx,.xls" required class="form-control">
                    <div id="importNtcProgress" class="d-none mt-2">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                        <span class="small text-muted ms-1">Memproses import...</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="importNtcButton" class="btn btn-success">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
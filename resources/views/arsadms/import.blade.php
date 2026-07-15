{{-- resources/views/arsadms/import.blade.php --}}
<div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="importExcelModalLabel">
                    <i class="bi bi-file-earmark-excel text-success me-2"></i> Import Excel — ARS ADM
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="importExcelForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">

                    {{-- Info upsert --}}
                    <div class="alert alert-info d-flex gap-2 py-2 mb-3">
                        <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
                        <div style="font-size: 0.85rem;">
                            <strong>Catatan Import:</strong><br>
                            Jika <strong>Part No</strong> sudah ada, data atribut lainnya akan <strong>diperbarui</strong> sesuai file baru.
                            Jika belum ada, data akan <strong>ditambahkan</strong> sebagai baru.
                        </div>
                    </div>

                    {{-- Format kolom --}}
                    <div class="alert alert-secondary py-2 mb-3" style="font-size: 0.82rem;">
                        <strong>Format kolom Excel:</strong><br>
                        <code>Part No | Min | Max | Part Cat | Packing Type | Area Code | Part Type | WH Zone | Rack No | Rack Layer</code>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pilih File Excel</label>
                        <input type="file" class="form-control" id="excelFile" name="excel_file"
                               accept=".xlsx,.xls,.csv">
                        <div class="form-text">Format yang didukung: .xlsx, .xls, .csv</div>
                    </div>

                    <div id="importProgress" class="d-none">
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success w-100"></div>
                        </div>
                        <small class="text-muted mt-1 d-block">Sedang mengimpor data...</small>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" id="importButton">
                        <i class="bi bi-upload me-1"></i> Import
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

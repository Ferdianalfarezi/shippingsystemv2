<!-- Modal Import TMMIN TXT -->
<div class="modal fade" id="importTmminModal" tabindex="-1" aria-labelledby="importTmminModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="importTmminModalLabel">
                    <i class="bi bi-file-earmark-text me-2"></i>Import Data TMMIN (TXT)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="importTmminForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="tmminFile" class="form-label text-dark ms-1">Pilih File TXT TMMIN</label>
                        <input type="file" class="form-control" id="tmminFile" name="txt_file" accept=".txt" required>
                        <div class="form-text ms-1">Format yang didukung: .txt (Max: 20MB)</div>
                    </div>

                    <div class="alert alert-secondary mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Format File:</strong>
                        <ul class="mb-0 mt-2">
                            <li>File harus dalam format tab-separated (.txt)</li>
                            <li>Harus mengandung baris D1 dan D2</li>
                            <li>Logistic Partner akan otomatis diisi berdasarkan konfigurasi route</li>
                        </ul>
                    </div>

                    <!-- Progress bar -->
                    <div id="importTmminProgress" class="d-none">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                 role="progressbar" 
                                 style="width: 100%">
                                Mengimpor data TMMIN...
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-dark" id="importTmminButton">
                        <i class="bi bi-upload me-2"></i>Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal Konfigurasi Matrix Pulling -->
<div class="modal fade" id="pullingMatrixModal" tabindex="-1" aria-labelledby="pullingMatrixModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" style="margin-top: 3rem;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="pullingMatrixModalLabel">
                    <i class="bi bi-table"></i> Konfigurasi Matrix Pulling
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                <form id="pullingMatrixForm">
                    @csrf

                    <!-- Header kolom -->
                    <div class="row fw-bold mb-2 px-2 text-dark">
                        <div class="col-3">Route</div>
                        <div class="col-3">Dock</div>
                        <div class="col-2">Cycle</div>
                        <div class="col-3">Finish Pulling</div>
                        <div class="col-1 text-center"></div>
                    </div>

                    <div id="pullingMatrixContainer">
                        <!-- Diisi via AJAX -->
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol tambah baris -->
                    <div class="mt-3">
                        <button type="button" class="btn btn-primary" id="addPullingMatrixBtn">
                            <i class="bi bi-plus-circle"></i> Tambah Konfigurasi
                        </button>
                    </div>
                </form>

                <div class="alert alert-primary small mt-3 mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    <strong>Catatan :</strong> Finish Pulling adalah jam absolut saat barang harus mulai ditarik,
                    berlaku untuk semua customer. Jika kombinasi Route, Dock, dan Cycle tidak ada di daftar,
                    sistem akan menggunakan perhitungan default (Delivery Time dikurangi lead time).
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Tutup
                </button>
                <button type="button" class="btn btn-primary" id="savePullingMatrix">
                    <i class="bi bi-save"></i> Simpan Semua
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function () {

    /* ============================================================
       STATE
    ============================================================ */
    let pullingMatrices  = [];
    let originalMatrices = [];
    let deletedIds       = [];
    let newRowIndex      = 0;

    /* ============================================================
       LOAD DATA saat modal dibuka
    ============================================================ */
    $('#pullingMatrixModal').on('shown.bs.modal', function () {
        loadPullingMatrices();
    });

    function loadPullingMatrices() {
        $('#pullingMatrixContainer').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);

        $.ajax({
            url: '{{ route("pulling-matrix.index") }}',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                pullingMatrices  = response.data || [];
                originalMatrices = JSON.parse(JSON.stringify(pullingMatrices));
                deletedIds       = [];
                renderMatrices();
            },
            error: function () {
                $('#pullingMatrixContainer').html(`
                    <div class="alert alert-danger text-center">
                        <i class="bi bi-exclamation-triangle"></i> Gagal memuat data
                    </div>
                `);
            }
        });
    }

    /* ============================================================
       RENDER
    ============================================================ */
    function renderMatrices() {
        if (pullingMatrices.length === 0) {
            $('#pullingMatrixContainer').html(`
                <div class="text-center text-muted py-4">
                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                    <p class="mt-2">Belum ada konfigurasi Matrix Pulling</p>
                </div>
            `);
            return;
        }

        let html = '';
        pullingMatrices.forEach(function (m) {
            html += buildRow(m);
        });
        $('#pullingMatrixContainer').html(html);
    }

    function buildRow(matrix) {
        const rowId = matrix.id ?? '';
        // Potong ke HH:MM untuk input[type=time]
        let timeVal = matrix.pulling_time || '07:00';
        if (timeVal.length > 5) timeVal = timeVal.substring(0, 5);

        return `
            <div class="row mb-2 align-items-center pulling-matrix-row" data-row-id="${rowId}">
                <div class="col-3">
                    <input type="text"
                           class="form-control text-uppercase"
                           value="${matrix.route || ''}"
                           placeholder="MR4-CJ5-A"
                           data-field="route">
                </div>
                <div class="col-3">
                    <input type="text"
                           class="form-control text-uppercase"
                           value="${matrix.dock || ''}"
                           placeholder="ASSY 2"
                           data-field="dock">
                </div>
                <div class="col-2">
                    <input type="text"
                           class="form-control"
                           value="${matrix.cycle || ''}"
                           placeholder="1"
                           data-field="cycle">
                </div>
                <div class="col-3">
                    <div class="input-group">
                        <input type="time"
                               class="form-control"
                               value="${timeVal}"
                               data-field="pulling_time">
                        <span class="input-group-text"><i class="bi bi-clock-fill"></i></span>
                    </div>
                </div>
                <div class="col-1 text-center">
                    <button type="button"
                            class="btn btn-danger btn-sm delete-matrix-row-btn"
                            data-row-id="${rowId}"
                            title="Hapus baris ini">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </div>
            </div>
        `;
    }

    /* ============================================================
       TAMBAH BARIS BARU
    ============================================================ */
    $('#addPullingMatrixBtn').on('click', function () {
        newRowIndex++;
        const newRow = {
            id:           'new_' + newRowIndex,
            route:        '',
            dock:         '',
            cycle:        '',
            pulling_time: '07:00',
        };
        pullingMatrices.push(newRow);
        renderMatrices();

        // Scroll ke bawah container supaya baris baru keliatan
        const container = document.getElementById('pullingMatrixContainer');
        container.scrollTop = container.scrollHeight;
    });

    /* ============================================================
       HAPUS BARIS
    ============================================================ */
    $(document).on('click', '.delete-matrix-row-btn', function () {
        const rowId = $(this).data('row-id');

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: 'Konfigurasi ini akan dihapus!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                const idStr = String(rowId);
                // Kalau bukan baris baru, tandai untuk dihapus di server
                if (idStr && !idStr.startsWith('new_')) {
                    deletedIds.push(rowId);
                }
                pullingMatrices = pullingMatrices.filter(m => String(m.id) !== idStr);
                renderMatrices();

                Swal.fire({
                    title: 'Dihapus!',
                    text: 'Konfigurasi telah dihapus dari daftar.',
                    icon: 'success',
                    timer: 1200,
                    showConfirmButton: false,
                });
            }
        });
    });

    /* ============================================================
       SYNC INPUT → STATE
    ============================================================ */
    $(document).on('input', '.pulling-matrix-row input', function () {
        const row   = $(this).closest('.pulling-matrix-row');
        const rowId = row.data('row-id');
        const field = $(this).data('field');
        const val   = $(this).val();

        const matrix = pullingMatrices.find(m => String(m.id) === String(rowId));
        if (matrix) {
            matrix[field] = (field === 'route' || field === 'dock')
                ? val.toUpperCase()
                : val;
        }
    });

    /* ============================================================
       CEK ADA PERUBAHAN
    ============================================================ */
    function hasChanged(matrix) {
        const idStr = String(matrix.id ?? '');
        if (!idStr || idStr.startsWith('new_')) return true;

        const orig = originalMatrices.find(m => String(m.id) === idStr);
        if (!orig) return true;

        let mTime = matrix.pulling_time || '';
        let oTime = orig.pulling_time   || '';
        if (mTime.length === 5) mTime += ':00';
        if (oTime.length === 5) oTime += ':00';

        return orig.route  !== matrix.route  ||
               orig.dock   !== matrix.dock   ||
               orig.cycle  !== matrix.cycle  ||
               oTime       !== mTime;
    }

    /* ============================================================
       SIMPAN SEMUA
    ============================================================ */
    $('#savePullingMatrix').on('click', function () {

        // Validasi semua field terisi
        let hasError = false;
        $('.pulling-matrix-row').each(function () {
            const route = $(this).find('[data-field="route"]').val().trim();
            const dock  = $(this).find('[data-field="dock"]').val().trim();
            const cycle = $(this).find('[data-field="cycle"]').val().trim();
            const time  = $(this).find('[data-field="pulling_time"]').val().trim();

            if (!route || !dock || !cycle || !time) {
                hasError = true;
                return false;
            }
        });

        if (hasError) {
            Swal.fire({
                title: 'Error!',
                text: 'Semua field harus diisi sebelum menyimpan',
                icon: 'error',
                confirmButtonColor: '#0d6efd',
            });
            return;
        }

        const changedMatrices = pullingMatrices.filter(m => hasChanged(m));

        if (changedMatrices.length === 0 && deletedIds.length === 0) {
            Swal.fire({
                title: 'Info',
                text: 'Tidak ada perubahan untuk disimpan',
                icon: 'info',
                confirmButtonColor: '#6c757d',
            });
            return;
        }

        // Loading
        Swal.fire({
            title: 'Menyimpan...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading(),
        });

        const payload = {
            configs: changedMatrices.map(m => ({
                id:           String(m.id).startsWith('new_') ? null : m.id,
                route:        m.route.trim().toUpperCase(),
                dock:         m.dock.trim().toUpperCase(),
                cycle:        m.cycle.trim(),
                pulling_time: m.pulling_time,
            })),
            deleted_ids: deletedIds,
        };

        $.ajax({
            url: '{{ route("pulling-matrix.batch-save") }}',
            type: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function (response) {
                Swal.fire({
                    title: 'Berhasil!',
                    text: response.message,
                    icon: 'success',
                    confirmButtonColor: '#0d6efd',
                }).then(() => loadPullingMatrices());
            },
            error: function (xhr) {
                let msg = 'Terjadi kesalahan saat menyimpan data';
                if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join(', ');
                }
                Swal.fire({
                    title: 'Gagal!',
                    text: msg,
                    icon: 'error',
                    confirmButtonColor: '#dc2626',
                });
            },
        });
    });

    /* ============================================================
       RESET STATE saat modal ditutup
    ============================================================ */
    $('#pullingMatrixModal').on('hidden.bs.modal', function () {
        pullingMatrices  = [];
        originalMatrices = [];
        deletedIds       = [];
        newRowIndex      = 0;
        $('#pullingMatrixContainer').html('');
    });

});
</script>
@endpush
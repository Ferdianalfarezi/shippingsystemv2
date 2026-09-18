<!-- Modal Konfigurasi Matrix Shipping -->
<div class="modal fade" id="shippingMatrixModal" tabindex="-1" aria-labelledby="shippingMatrixModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" style="margin-top: 3rem;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="shippingMatrixModalLabel">
                    <i class="bi bi-signpost-split"></i> Konfigurasi Matrix Shipping
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                <form id="shippingMatrixForm">
                    @csrf

                    <div class="row fw-bold mb-2 px-2 text-dark">
                        <div class="col-4">Customer</div>
                        <div class="col-3">Dock</div>
                        <div class="col-2">Cycle</div>
                        <div class="col-2">Address</div>
                        <div class="col-1 text-center"></div>
                    </div>

                    <div id="shippingMatrixContainer">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-primary" id="addShippingMatrixBtn">
                            <i class="bi bi-plus-circle"></i> Tambah Konfigurasi
                        </button>
                    </div>
                </form>

                <div class="alert alert-primary small mt-3 mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    <strong>Catatan :</strong> Kombinasi Customer, Dock, dan Cycle dipakai untuk menentukan otomatis address Shipping saat data di-scan/dipindahkan. Pencocokan tidak case sensitive (TMMIN = tmmin) tapi tetap sensitif terhadap spasi (TMMIN &ne; TMMIN PLANT 2). Jika kombinasi tidak ditemukan di daftar, sistem akan default ke <strong>Shipping 10</strong>.
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Tutup
                </button>
                <button type="button" class="btn btn-primary" id="saveShippingMatrix">
                    <i class="bi bi-save"></i> Simpan Semua
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function () {

    const SHIPPING_ADDRESS_OPTIONS = [
        'Shipping 1','Shipping 2','Shipping 3','Shipping 4','Shipping 5',
        'Shipping 6','Shipping 7','Shipping 8','Shipping 9','Shipping 10',
        'Shipping Ex 1','Shipping Ex 2','Shipping Ex 3','Shipping Ex 4','Shipping Ex 5',
    ];

    let shippingMatrices  = [];
    let originalMatrices  = [];
    let deletedIds        = [];
    let newRowIndex       = 0;

    $('#shippingMatrixModal').on('shown.bs.modal', function () {
        loadShippingMatrices();
    });

    function loadShippingMatrices() {
        $('#shippingMatrixContainer').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);

        $.ajax({
            url: '{{ route("shipping-matrix.index") }}',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                shippingMatrices = response.data || [];
                originalMatrices = JSON.parse(JSON.stringify(shippingMatrices));
                deletedIds       = [];
                renderMatrices();
            },
            error: function () {
                $('#shippingMatrixContainer').html(`
                    <div class="alert alert-danger text-center">
                        <i class="bi bi-exclamation-triangle"></i> Gagal memuat data
                    </div>
                `);
            }
        });
    }

    function renderMatrices() {
        if (shippingMatrices.length === 0) {
            $('#shippingMatrixContainer').html(`
                <div class="text-center text-muted py-4">
                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                    <p class="mt-2">Belum ada konfigurasi Matrix Shipping</p>
                </div>
            `);
            return;
        }

        let html = '';
        shippingMatrices.forEach(function (m) {
            html += buildRow(m);
        });
        $('#shippingMatrixContainer').html(html);
    }

    function buildAddressOptions(selected) {
        return SHIPPING_ADDRESS_OPTIONS.map(function (addr) {
            const isSelected = addr === selected ? 'selected' : '';
            return `<option value="${addr}" ${isSelected}>${addr}</option>`;
        }).join('');
    }

    function buildRow(matrix) {
        const rowId = matrix.id ?? '';

        return `
            <div class="row mb-2 align-items-center shipping-matrix-row" data-row-id="${rowId}">
                <div class="col-4">
                    <input type="text" class="form-control" value="${matrix.customers || ''}" placeholder="TMMIN" data-field="customers">
                </div>
                <div class="col-3">
                    <input type="text" class="form-control" value="${matrix.dock || ''}" placeholder="4P" data-field="dock">
                </div>
                <div class="col-2">
                    <input type="text" class="form-control" value="${matrix.cycle || ''}" placeholder="1" data-field="cycle">
                </div>
                <div class="col-2">
                    <select class="form-select" data-field="address">
                        ${buildAddressOptions(matrix.address || 'Shipping 1')}
                    </select>
                </div>
                <div class="col-1 text-center">
                    <button type="button" class="btn btn-danger btn-sm delete-shipping-matrix-row-btn" data-row-id="${rowId}" title="Hapus baris ini">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </div>
            </div>
        `;
    }

    $('#addShippingMatrixBtn').on('click', function () {
        newRowIndex++;
        shippingMatrices.push({
            id: 'new_' + newRowIndex,
            customers: '',
            dock: '',
            cycle: '',
            address: 'Shipping 1',
        });
        renderMatrices();

        const container = document.getElementById('shippingMatrixContainer');
        container.scrollTop = container.scrollHeight;
    });

    $(document).on('click', '.delete-shipping-matrix-row-btn', function () {
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
                if (idStr && !idStr.startsWith('new_')) {
                    deletedIds.push(rowId);
                }
                shippingMatrices = shippingMatrices.filter(m => String(m.id) !== idStr);
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

    $(document).on('input', '.shipping-matrix-row input', function () {
        const row = $(this).closest('.shipping-matrix-row');
        const rowId = row.data('row-id');
        const field = $(this).data('field');
        const val = $(this).val();

        const matrix = shippingMatrices.find(m => String(m.id) === String(rowId));
        if (matrix) matrix[field] = val;
    });

    $(document).on('change', '.shipping-matrix-row select', function () {
        const row = $(this).closest('.shipping-matrix-row');
        const rowId = row.data('row-id');
        const field = $(this).data('field');
        const val = $(this).val();

        const matrix = shippingMatrices.find(m => String(m.id) === String(rowId));
        if (matrix) matrix[field] = val;
    });

    function hasChanged(matrix) {
        const idStr = String(matrix.id ?? '');
        if (!idStr || idStr.startsWith('new_')) return true;

        const orig = originalMatrices.find(m => String(m.id) === idStr);
        if (!orig) return true;

        return orig.customers !== matrix.customers ||
               orig.dock      !== matrix.dock      ||
               orig.cycle     !== matrix.cycle     ||
               orig.address   !== matrix.address;
    }

    $('#saveShippingMatrix').on('click', function () {

        let hasError = false;
        $('.shipping-matrix-row').each(function () {
            const customers = $(this).find('[data-field="customers"]').val().trim();
            const dock      = $(this).find('[data-field="dock"]').val().trim();
            const cycle     = $(this).find('[data-field="cycle"]').val().trim();
            const address   = $(this).find('[data-field="address"]').val();

            if (!customers || !dock || !cycle || !address) {
                hasError = true;
                return false;
            }
        });

        if (hasError) {
            Swal.fire({
                title: 'Error!',
                text: 'Customer, Dock, Cycle, dan Address harus diisi semua sebelum menyimpan',
                icon: 'error',
                confirmButtonColor: '#0d6efd',
            });
            return;
        }

        const changedMatrices = shippingMatrices.filter(m => hasChanged(m));

        if (changedMatrices.length === 0 && deletedIds.length === 0) {
            Swal.fire({
                title: 'Info',
                text: 'Tidak ada perubahan untuk disimpan',
                icon: 'info',
                confirmButtonColor: '#6c757d',
            });
            return;
        }

        Swal.fire({
            title: 'Menyimpan...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading(),
        });

        const payload = {
            configs: changedMatrices.map(m => ({
                id: String(m.id).startsWith('new_') ? null : m.id,
                customers: m.customers.trim(),
                dock: m.dock.trim(),
                cycle: m.cycle.trim(),
                address: m.address,
            })),
            deleted_ids: deletedIds,
        };

        $.ajax({
            url: '{{ route("shipping-matrix.batch-save") }}',
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
                }).then(() => loadShippingMatrices());
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

    $('#shippingMatrixModal').on('hidden.bs.modal', function () {
        shippingMatrices = [];
        originalMatrices = [];
        deletedIds       = [];
        newRowIndex      = 0;
        $('#shippingMatrixContainer').html('');
    });

});
</script>
@endpush
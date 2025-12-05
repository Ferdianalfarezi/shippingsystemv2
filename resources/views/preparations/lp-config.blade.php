<!-- Modal LP Config -->
<div class="modal fade" id="lpConfigModal" tabindex="-1" aria-labelledby="lpConfigModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" style="margin-top: 3rem;">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="lpConfigModalLabel">
                    <i class="bi bi-gear-fill"></i> Konfigurasi Logistic Partner
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                <form id="lpConfigForm">
                    @csrf
                    <div class="row fw-bold mb-2 px-2 text-dark">
                        <div class="col-5">Route</div>
                        <div class="col-5">Logistic Partner</div>
                        <div class="col-2 text-center">Aksi</div>
                    </div>
                    
                    <div id="lpConfigContainer">
                        <!-- Data akan di-load via AJAX -->
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Button Tambah Config -->
                    <div class="mt-3">
                        <button type="button" class="btn btn-dark" id="addLpConfigBtn">
                            <i class="bi bi-plus-circle"></i> Tambah Konfigurasi
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Tutup
                </button>
                <button type="button" class="btn btn-dark" id="saveAllLpConfig">
                    <i class="bi bi-save"></i> Simpan Semua
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let lpConfigs = [];
    let originalConfigs = []; // Simpan data original
    let deletedIds = [];
    let newConfigIndex = 0;

    // Load LP Config data when modal is opened
    $('#lpConfigModal').on('shown.bs.modal', function() {
        loadLpConfigs();
    });

    // Function to load LP Configs
    function loadLpConfigs() {
        $.ajax({
            url: '{{ route("lp-configs.index") }}',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                lpConfigs = response.data || [];
                // Deep copy untuk menyimpan data original
                originalConfigs = JSON.parse(JSON.stringify(lpConfigs));
                deletedIds = [];
                renderLpConfigs();
            },
            error: function(xhr) {
                $('#lpConfigContainer').html(`
                    <div class="alert alert-danger text-center">
                        <i class="bi bi-exclamation-triangle"></i> Gagal memuat data
                    </div>
                `);
            }
        });
    }

    // Function to render LP Configs
    function renderLpConfigs() {
        let html = '';
        
        if (lpConfigs.length === 0) {
            html = `
                <div class="text-center text-muted py-4">
                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                    <p class="mt-2">Belum ada konfigurasi LP</p>
                </div>
            `;
        } else {
            lpConfigs.forEach(function(config) {
                html += createConfigRow(config);
            });
        }
        
        $('#lpConfigContainer').html(html);
    }

    // Function to create config row
    function createConfigRow(config) {
        const isNew = config.id && String(config.id).startsWith('new_');
        const configId = config.id || '';
        
        return `
            <div class="row mb-2 align-items-center lp-config-row" data-config-id="${configId}">
                <div class="col-5">
                    <input type="text" 
                           class="form-control" 
                           value="${config.route || ''}" 
                           placeholder="Route"
                           data-field="route">
                </div>
                <div class="col-5">
                    <input type="text" 
                           class="form-control" 
                           value="${config.logistic_partner || ''}" 
                           placeholder="Logistic Partner"
                           data-field="logistic_partner">
                </div>
                <div class="col-2 text-center">
                    <button type="button" 
                            class="btn btn-danger btn-sm delete-config-btn" 
                            data-config-id="${configId}">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </div>
            </div>
        `;
    }

    // Add new config row
    $('#addLpConfigBtn').on('click', function() {
        newConfigIndex++;
        const newConfig = {
            id: 'new_' + newConfigIndex,
            route: '',
            logistic_partner: ''
        };
        lpConfigs.push(newConfig);
        renderLpConfigs();
    });

    // Delete config
    $(document).on('click', '.delete-config-btn', function() {
        const configId = $(this).data('config-id');
        
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Konfigurasi ini akan dihapus!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // If it's existing config, add to deleted list
                if (configId && !String(configId).startsWith('new_')) {
                    deletedIds.push(configId);
                }
                
                // Remove from lpConfigs array
                lpConfigs = lpConfigs.filter(c => c.id !== configId);
                renderLpConfigs();
                
                Swal.fire({
                    title: 'Dihapus!',
                    text: 'Konfigurasi telah dihapus dari daftar.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        });
    });

    // Update lpConfigs array when input changes
    $(document).on('input', '.lp-config-row input', function() {
        const row = $(this).closest('.lp-config-row');
        const configId = row.data('config-id');
        const field = $(this).data('field');
        const value = $(this).val();
        
        const config = lpConfigs.find(c => c.id === configId);
        if (config) {
            config[field] = value;
        }
    });

    // Function to check if config has changed
    function hasChanged(config) {
        // Jika ID baru, pasti berubah
        if (!config.id || String(config.id).startsWith('new_')) {
            return true;
        }
        
        // Cari data original
        const original = originalConfigs.find(c => c.id === config.id);
        if (!original) return true;
        
        // Bandingkan field
        return original.route !== config.route || 
               original.logistic_partner !== config.logistic_partner;
    }

    // Save all configs
    $('#saveAllLpConfig').on('click', function() {
        // Validate
        let hasError = false;
        $('.lp-config-row').each(function() {
            const route = $(this).find('[data-field="route"]').val().trim();
            const lp = $(this).find('[data-field="logistic_partner"]').val().trim();
            
            if (!route || !lp) {
                hasError = true;
                return false;
            }
        });
        
        if (hasError) {
            Swal.fire({
                title: 'Error!',
                text: 'Route dan Logistic Partner tidak boleh kosong',
                icon: 'error',
                confirmButtonColor: '#dc2626'
            });
            return;
        }

        // Filter hanya data yang berubah atau baru
        const changedConfigs = lpConfigs.filter(config => hasChanged(config));

        // Jika tidak ada perubahan
        if (changedConfigs.length === 0 && deletedIds.length === 0) {
            Swal.fire({
                title: 'Info',
                text: 'Tidak ada perubahan untuk disimpan',
                icon: 'info',
                confirmButtonColor: '#6c757d'
            });
            return;
        }

        // Show loading
        Swal.fire({
            title: 'Menyimpan...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Prepare data for batch save - HANYA KIRIM YANG BERUBAH
        const dataToSave = {
            configs: changedConfigs.map(c => ({
                id: String(c.id).startsWith('new_') ? null : c.id,
                route: c.route,
                logistic_partner: c.logistic_partner
            })),
            deleted_ids: deletedIds
        };

        // Save via AJAX
        $.ajax({
            url: '{{ route("lp-configs.batch-save") }}',
            type: 'POST',
            data: JSON.stringify(dataToSave),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                Swal.fire({
                    title: 'Berhasil!',
                    text: response.message,
                    icon: 'success',
                    confirmButtonColor: '#059669'
                }).then(() => {
                    loadLpConfigs(); // Reload data
                });
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan saat menyimpan data';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                if (xhr.status === 422 && xhr.responseJSON.errors) {
                    errorMessage = Object.values(xhr.responseJSON.errors).flat().join(', ');
                }
                
                Swal.fire({
                    title: 'Gagal!',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonColor: '#dc2626'
                });
            }
        });
    });

    // Reset when modal is closed
    $('#lpConfigModal').on('hidden.bs.modal', function() {
        lpConfigs = [];
        originalConfigs = [];
        deletedIds = [];
        newConfigIndex = 0;
    });
});
</script>
@endpush
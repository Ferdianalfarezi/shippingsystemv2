<!-- Modal Konfigurasi Lead Time ADM -->
<div class="modal fade" id="admLeadTimeModal" tabindex="-1" aria-labelledby="admLeadTimeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" style="margin-top: 3rem;">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="admLeadTimeModalLabel">
                    <i class="bi bi-clock-history"></i> Konfigurasi Lead Time Astra Daihatsu Motor
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                <form id="admLeadTimeForm">
                    @csrf
                    <div class="row fw-bold mb-2 px-2 text-dark">
                        <div class="col-3">Route</div>
                        <div class="col-2">Dock</div>
                        <div class="col-1">Cycle</div>
                        <div class="col-4">Lead Time</div>
                        <div class="col-2 text-center"></div>
                    </div>
                    
                    <div id="admLeadTimeContainer">
                        <!-- Data akan di-load via AJAX -->
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Button Tambah Config -->
                    <div class="mt-3">
                        <button type="button" class="btn btn-danger" id="addAdmLeadTimeBtn">
                            <i class="bi bi-plus-circle"></i> Tambah Konfigurasi
                        </button>
                    </div>
                </form>

                <div class="alert alert-danger small mt-3 mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    <strong>Catatan :</strong> Lead Time adalah waktu mundur dari Delivery Time untuk menghitung Pulling Time. 
                    Jika kombinasi Route, Dock, dan Cycle tidak ada di daftar, sistem akan menggunakan default <strong>3 jam</strong>.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Tutup
                </button>
                <button type="button" class="btn btn-danger" id="saveAllAdmLeadTime">
                    <i class="bi bi-save"></i> Simpan Semua
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let admLeadTimes = [];
    let originalConfigs = [];
    let deletedIds = [];
    let newConfigIndex = 0;

    // Load data when modal is opened
    $('#admLeadTimeModal').on('shown.bs.modal', function() {
        loadAdmLeadTimes();
    });

    // Function to load ADM Lead Times
    function loadAdmLeadTimes() {
        $.ajax({
            url: '{{ route("adm-lead-time.index") }}',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                admLeadTimes = response.data || [];
                originalConfigs = JSON.parse(JSON.stringify(admLeadTimes));
                deletedIds = [];
                renderAdmLeadTimes();
            },
            error: function(xhr) {
                $('#admLeadTimeContainer').html(`
                    <div class="alert alert-danger text-center">
                        <i class="bi bi-exclamation-triangle"></i> Gagal memuat data
                    </div>
                `);
            }
        });
    }

    // Function to render ADM Lead Times
    function renderAdmLeadTimes() {
        let html = '';
        
        if (admLeadTimes.length === 0) {
            html = `
                <div class="text-center text-muted py-4">
                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                    <p class="mt-2">Belum ada konfigurasi Lead Time</p>
                </div>
            `;
        } else {
            admLeadTimes.forEach(function(config) {
                html += createConfigRow(config);
            });
        }
        
        $('#admLeadTimeContainer').html(html);
    }

    // Function to create config row
    function createConfigRow(config) {
        const configId = config.id || '';
        // Format lead_time untuk input (HH:MM)
        let leadTimeValue = config.lead_time || '03:00';
        if (leadTimeValue.length > 5) {
            leadTimeValue = leadTimeValue.substring(0, 5);
        }
        
        return `
            <div class="row mb-2 align-items-center adm-lead-time-row" data-config-id="${configId}">
                <div class="col-3">
                    <input type="text" 
                           class="form-control" 
                           value="${config.route || ''}" 
                           placeholder="MR4-CJ5-A"
                           data-field="route">
                </div>
                <div class="col-2">
                    <input type="text" 
                           class="form-control" 
                           value="${config.dock || ''}" 
                           placeholder="ASSY 2"
                           data-field="dock">
                </div>
                <div class="col-1">
                    <input type="text" 
                           class="form-control" 
                           value="${config.cycle || ''}" 
                           placeholder="1"
                           data-field="cycle">
                </div>
                <div class="col-4">
                    <div class="input-group">
                        <input type="time" 
                               class="form-control" 
                               value="${leadTimeValue}" 
                               data-field="lead_time">
                        <span class="input-group-text"><i class="bi bi-clock"></i></span>
                    </div>
                </div>
                <div class="col-2 text-center">
                    <button type="button" 
                            class="btn btn-danger btn-sm delete-adm-config-btn" 
                            data-config-id="${configId}">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </div>
            </div>
        `;
    }

    // Add new config row
    $('#addAdmLeadTimeBtn').on('click', function() {
        newConfigIndex++;
        const newConfig = {
            id: 'new_' + newConfigIndex,
            route: '',
            dock: '',
            cycle: '',
            lead_time: '03:00'
        };
        admLeadTimes.push(newConfig);
        renderAdmLeadTimes();
    });

    // Delete config
    $(document).on('click', '.delete-adm-config-btn', function() {
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
                
                // Remove from array
                admLeadTimes = admLeadTimes.filter(c => c.id !== configId);
                renderAdmLeadTimes();
                
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

    // Update array when input changes
    $(document).on('input', '.adm-lead-time-row input', function() {
        const row = $(this).closest('.adm-lead-time-row');
        const configId = row.data('config-id');
        const field = $(this).data('field');
        const value = $(this).val();
        
        const config = admLeadTimes.find(c => c.id === configId);
        if (config) {
            config[field] = value;
        }
    });

    // Function to check if config has changed
    function hasChanged(config) {
        if (!config.id || String(config.id).startsWith('new_')) {
            return true;
        }
        
        const original = originalConfigs.find(c => c.id === config.id);
        if (!original) return true;
        
        // Format lead_time untuk perbandingan
        let configLeadTime = config.lead_time || '';
        let originalLeadTime = original.lead_time || '';
        if (configLeadTime.length === 5) configLeadTime += ':00';
        if (originalLeadTime.length === 5) originalLeadTime += ':00';
        
        return original.route !== config.route || 
               original.dock !== config.dock ||
               original.cycle !== config.cycle ||
               originalLeadTime !== configLeadTime;
    }

    // Save all configs
    $('#saveAllAdmLeadTime').on('click', function() {
        // Validate
        let hasError = false;
        $('.adm-lead-time-row').each(function() {
            const route = $(this).find('[data-field="route"]').val().trim();
            const dock = $(this).find('[data-field="dock"]').val().trim();
            const cycle = $(this).find('[data-field="cycle"]').val().trim();
            const leadTime = $(this).find('[data-field="lead_time"]').val().trim();
            
            if (!route || !dock || !cycle || !leadTime) {
                hasError = true;
                return false;
            }
        });
        
        if (hasError) {
            Swal.fire({
                title: 'Error!',
                text: 'Semua field harus diisi',
                icon: 'error',
                confirmButtonColor: '#dc2626'
            });
            return;
        }

        // Filter hanya data yang berubah atau baru
        const changedConfigs = admLeadTimes.filter(config => hasChanged(config));

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

        // Prepare data for batch save
        const dataToSave = {
            configs: changedConfigs.map(c => ({
                id: String(c.id).startsWith('new_') ? null : c.id,
                route: c.route.trim().toUpperCase(),
                dock: c.dock.trim().toUpperCase(),
                cycle: c.cycle.trim(),
                lead_time: c.lead_time
            })),
            deleted_ids: deletedIds
        };

        // Save via AJAX
        $.ajax({
            url: '{{ route("adm-lead-time.batch-save") }}',
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
                    loadAdmLeadTimes();
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
    $('#admLeadTimeModal').on('hidden.bs.modal', function() {
        admLeadTimes = [];
        originalConfigs = [];
        deletedIds = [];
        newConfigIndex = 0;
    });
});
</script>
@endpush
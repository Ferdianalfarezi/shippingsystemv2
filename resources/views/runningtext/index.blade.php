{{-- resources/views/runningtext/index.blade.php --}}
{{-- Modal Edit Running Text --}}
<div class="modal fade" id="runningTextModal" tabindex="-1" aria-labelledby="runningTextModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: #ffffff; border: 2px solid #ffffff;">
            <div class="modal-header" style="border-bottom: 1px solid #333;">
                <h5 class="modal-title text-dark" id="runningTextModalLabel">
                    Edit Running Text
                </h5>
                <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="runningTextForm">
                    @csrf
                    {{-- Content --}}
                    <div class="mb-3">
                        <label for="rt_content" class="form-label text-dark">
                            <i class="fas fa-align-left me-1"></i>Isi Running Text
                        </label>
                        <textarea 
                            class="form-control" 
                            id="rt_content" 
                            name="content" 
                            rows="3" 
                            placeholder="Masukkan teks yang akan berjalan..."
                            style="background-color: #ffffff; border: 1px solid #444; color: #000000;"
                        ></textarea>
                    </div>

                    {{-- Speed --}}
                    <div class="mb-3">
                        <label for="rt_speed" class="form-label text-dark">
                            <i class="fas fa-tachometer-alt me-1"></i>Kecepatan
                        </label>
                        <select 
                            class="form-select" 
                            id="rt_speed" 
                            name="speed"
                            style="background-color: #ffffff; border: 1px solid #444; color: #000000;"
                        >
                            <option value="slow">Lambat (40 detik)</option>
                            <option value="normal" selected>Normal (25 detik)</option>
                            <option value="fast">Cepat (15 detik)</option>
                        </select>
                    </div>

                    {{-- Colors --}}
                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="rt_bg_color" class="form-label text-dark">
                                <i class="fas fa-fill-drip me-1"></i>Warna Background
                            </label>
                            <input 
                                type="color" 
                                class="form-control form-control-color w-100" 
                                id="rt_bg_color" 
                                name="background_color" 
                                value="#1a1a1a"
                            >
                        </div>
                        <div class="col-6">
                            <label for="rt_text_color" class="form-label text-dark">
                                <i class="fas fa-font me-1"></i>Warna Teks
                            </label>
                            <input 
                                type="color" 
                                class="form-control form-control-color w-100" 
                                id="rt_text_color" 
                                name="text_color" 
                                value="#fbbf24"
                            >
                        </div>
                    </div>

                    {{-- Active Toggle - CUSTOM SWITCH --}}
                    <div class="mb-3">
                        <div class="d-flex align-items-center">
                            <label class="toggle-switch">
                                <input 
                                    type="checkbox" 
                                    id="rt_is_active" 
                                    name="is_active" 
                                    checked
                                >
                                <span class="toggle-slider"></span>
                            </label>
                            <label class="text-dark ms-3 mb-0" for="rt_is_active" style="cursor: pointer;">
                                <i class="fas fa-power-off me-1"></i><span id="toggleStatusText">Aktif</span>
                            </label>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #333;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Batal
                </button>
                <button type="button" class="btn btn-warning" id="btnSaveRunningText">
                    <i class="fas fa-save me-1"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes marquee {
        0% { transform: translateX(100%); }
        100% { transform: translateX(-100%); }
    }
    
    .running-text-preview {
        overflow: hidden;
    }
    
    .running-text-content {
        display: inline-block;
        padding-left: 100%;
    }
    
    #runningTextModal .form-control:focus,
    #runningTextModal .form-select:focus {
        border-color: #000000;
        box-shadow: 0 0 0 0.2rem rgba(59, 59, 59, 0.25);
    }

    /* CUSTOM TOGGLE SWITCH - DARI NOL */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 25px;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        border-radius: 25px;
        transition: 0.3s;
    }

    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 21px;
        width: 21px;
        left: 2px;
        bottom: 2px;
        background-color: white;
        border-radius: 50%;
        transition: 0.3s;
    }

    .toggle-switch input:checked + .toggle-slider {
        background-color: #000000;
    }

    .toggle-switch input:checked + .toggle-slider:before {
        transform: translateX(25px);
    }

    .toggle-switch input:focus + .toggle-slider {
        box-shadow: 0 0 1px #000000;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const rtContent = document.getElementById('rt_content');
    const rtSpeed = document.getElementById('rt_speed');
    const rtBgColor = document.getElementById('rt_bg_color');
    const rtTextColor = document.getElementById('rt_text_color');
    const rtIsActive = document.getElementById('rt_is_active');
    const rtPreview = document.getElementById('rt_preview');
    const btnSave = document.getElementById('btnSaveRunningText');
    const toggleStatusText = document.getElementById('toggleStatusText');

    // Function to update toggle text
    function updateToggleText() {
        if (rtIsActive.checked) {
            toggleStatusText.textContent = 'Aktif';
        } else {
            toggleStatusText.textContent = 'Nonaktif';
        }
    }

    // Event listener untuk toggle
    rtIsActive.addEventListener('change', updateToggleText);

    // Speed mapping
    const speedMap = {
        'slow': '40s',
        'normal': '25s',
        'fast': '15s'
    };

    // Update preview function
    function updatePreview() {
        if (!rtPreview) return;
        
        const content = rtContent.value || 'Preview teks akan muncul di sini...';
        const speed = speedMap[rtSpeed.value] || '25s';
        const bgColor = rtBgColor.value;
        const textColor = rtTextColor.value;

        rtPreview.style.backgroundColor = bgColor;
        rtPreview.innerHTML = `
            <div class="running-text-content" style="color: ${textColor}; white-space: nowrap; animation: marquee ${speed} linear infinite;">
                ${content}
            </div>
        `;
    }

    // Event listeners untuk live preview
    if (rtContent) rtContent.addEventListener('input', updatePreview);
    if (rtSpeed) rtSpeed.addEventListener('change', updatePreview);
    if (rtBgColor) rtBgColor.addEventListener('input', updatePreview);
    if (rtTextColor) rtTextColor.addEventListener('input', updatePreview);

    // Load existing data when modal opens
    const modal = document.getElementById('runningTextModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', function() {
            fetch('{{ route("running-text.data") }}')
                .then(response => response.json())
                .then(result => {
                    if (result.success && result.data) {
                        const data = result.data;
                        rtContent.value = data.content || '';
                        rtSpeed.value = data.speed || 'normal';
                        rtBgColor.value = data.background_color || '#1a1a1a';
                        rtTextColor.value = data.text_color || '#fbbf24';
                        rtIsActive.checked = data.is_active;
                        updateToggleText();
                        updatePreview();
                    }
                })
                .catch(error => console.error('Error loading running text:', error));
        });
    }

    // Save button
    if (btnSave) {
        btnSave.addEventListener('click', function() {
            const formData = {
                content: rtContent.value,
                speed: rtSpeed.value,
                background_color: rtBgColor.value,
                text_color: rtTextColor.value,
                is_active: rtIsActive.checked ? 1 : 0,
                _token: '{{ csrf_token() }}'
            };

            fetch('{{ route("running-text.update") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: result.message,
                        confirmButtonColor: '#fbbf24',
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        // Close modal
                        bootstrap.Modal.getInstance(document.getElementById('runningTextModal')).hide();
                        // Reload page to update running text
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: result.message || 'Terjadi kesalahan',
                        confirmButtonColor: '#dc2626'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat menyimpan',
                    confirmButtonColor: '#dc2626'
                });
            });
        });
    }
});
</script>
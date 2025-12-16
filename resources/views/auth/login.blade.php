<x-guest-layout>

    <style>
        /* Input styling override */
        .form-control {
            border-radius: 6px !important;
            border: 1px solid #ccc !important;
            box-shadow: none !important;
        }

        .form-control:focus {
            border-color: #ffffff !important;
            box-shadow: 0 0 4px rgba(120, 120, 120, 0.4) !important;
        }

        /* Modal backdrop styling */
        .modal-backdrop.show {
            opacity: 0.7;
        }

        /* Expired notice styling */
        .expired-notice {
            background-color: #dc3545;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.875rem;
            margin-bottom: 1rem;
            text-align: center;
        }
    </style>

    <!-- Bootstrap Login Card -->
    <div class="card shadow-sm p-5"
     style="max-width: 420px; width: 100%; border-radius: 12px; 
            background-color: rgba(255, 255, 255, 0.821);">

        <!-- Session Status -->
        @if (session('status'))
            <div class="alert alert-success text-center mb-3">
                {{ session('status') }}
            </div>
        @endif

        <!-- System Expiry Notice (hanya muncul jika expired) -->
        <div id="expiryNotice" class="expired-notice d-none">
            <strong>⚠️ Sistem Expired</strong>
            <button type="button" class="btn btn-sm btn-light ms-3 mb-1" onclick="showActivationModal()">
                Aktivasi Sekarang
            </button>
        </div>

        <h2 class="text-center mb-4 fs-4 fw-bolder">LOGIN</h2>

        <form method="POST" action="{{ route('login') }}" id="loginForm">
            @csrf

            <!-- Username -->
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input 
                    id="username" 
                    type="text" 
                    class="form-control @error('username') is-invalid @enderror"
                    name="username"
                    value="{{ old('username') }}"
                    required 
                    autofocus
                >
                @error('username')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input 
                    id="password"  
                    type="password"  
                    class="form-control @error('password') is-invalid @enderror"
                    name="password" 
                    required
                >
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="form-check mb-3">
                <input 
                    class="form-check-input" 
                    type="checkbox" 
                    id="remember" 
                    name="remember" 
                    checked
                >
                <label class="form-check-label" for="remember">Ingat Saya</label>
            </div>

            <!-- Button -->
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-dark px-4" id="loginBtn">
                    Log in
                </button>
            </div>
        </form>
    </div>

    <!-- Modal Aktivasi Token -->
    <div class="modal fade" id="activationModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">🔐 Aktivasi Token Sistem</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Alert Success -->
                    <div id="activationSuccess" class="alert alert-success d-none">
                        <strong>✓ Berhasil!</strong>
                        <div id="successMessage"></div>
                    </div>

                    <!-- Alert Error -->
                    <div id="activationError" class="alert alert-danger d-none">
                        <strong>Gagal!</strong>
                        <div id="errorMessage"></div>
                    </div>

                    <!-- Form Input Token -->
                    <div id="tokenForm">
                        <div class="mb-3">
                            <label for="activationToken" class="form-label">
                                Token Aktivasi (32 Digit)
                            </label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="activationToken" 
                                placeholder="Masukkan 32 digit token"
                                maxlength="32"
                                style="font-family: monospace; letter-spacing: 2px;"
                            >
                            <small class="text-muted">
                                Token terdiri dari 32 karakter
                            </small>
                        </div>

                        <div class="mb-3">
                            <div class="text-muted small">
                                <strong>Status Saat Ini:</strong><br>
                                Masa Berlaku: <span id="modalExpiryDate" class="fw-bold">-</span><br>
                                Status: <span id="modalStatus" class="badge bg-secondary">Checking...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" onclick="validateToken()" id="validateBtn">
                        Validasi Token
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let isSystemExpired = false;

        document.addEventListener('DOMContentLoaded', function() {
            checkSystemStatus();
        });

        // Expose function untuk dipanggil dari guest layout
        window.showActivationModalFromLogin = showActivationModal;

        // Tampilkan modal aktivasi
        function showActivationModal() {
            // Reset form
            const tokenInput = document.getElementById('activationToken');
            if (tokenInput) {
                tokenInput.value = '';
            }
            
            const successDiv = document.getElementById('activationSuccess');
            const errorDiv = document.getElementById('activationError');
            
            if (successDiv) successDiv.classList.add('d-none');
            if (errorDiv) errorDiv.classList.add('d-none');
            
            // Show modal langsung dengan Bootstrap
            const modalElement = document.getElementById('activationModal');
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            } else {
                console.error('Modal element not found');
            }
        }

        // Cek apakah sistem expired
        async function checkSystemStatus() {
            try {
                // Cek expired status
                const expiredResponse = await fetch('/api/token/is-expired');
                
                if (!expiredResponse.ok) {
                    throw new Error('Failed to fetch expired status');
                }
                
                isSystemExpired = await expiredResponse.json();

                // Get expiry date
                const expiryResponse = await fetch('/api/token/expiry');
                
                if (!expiryResponse.ok) {
                    throw new Error('Failed to fetch expiry date');
                }
                
                const expiryDate = await expiryResponse.text();

                // Update display di modal
                const modalExpiryDate = document.getElementById('modalExpiryDate');
                if (modalExpiryDate) {
                    modalExpiryDate.textContent = expiryDate;
                }

                // Update status badge
                const statusBadge = document.getElementById('modalStatus');
                const expiryNotice = document.getElementById('expiryNotice');
                const loginBtn = document.getElementById('loginBtn');
                const loginForm = document.getElementById('loginForm');

                if (isSystemExpired) {
                    if (statusBadge) {
                        statusBadge.textContent = 'EXPIRED';
                        statusBadge.className = 'badge bg-danger';
                    }
                    
                    // Tampilkan notice
                    if (expiryNotice) {
                        expiryNotice.classList.remove('d-none');
                    }
                    
                    // Disable login button
                    if (loginBtn) {
                        loginBtn.disabled = true;
                    }
                    if (loginForm) {
                        loginForm.style.opacity = '0.6';
                    }
                } else {
                    if (statusBadge) {
                        statusBadge.textContent = 'AKTIF';
                        statusBadge.className = 'badge bg-success';
                    }
                    
                    // Pastikan login enabled jika aktif
                    if (loginBtn) {
                        loginBtn.disabled = false;
                    }
                    if (loginForm) {
                        loginForm.style.opacity = '1';
                    }
                    if (expiryNotice) {
                        expiryNotice.classList.add('d-none');
                    }
                }

            } catch (error) {
                console.error('Error checking system status:', error);
                
                // Tampilkan notice untuk aktivasi pertama
                const expiryNotice = document.getElementById('expiryNotice');
                const loginBtn = document.getElementById('loginBtn');
                const loginForm = document.getElementById('loginForm');
                
                if (expiryNotice) {
                    expiryNotice.classList.remove('d-none');
                }
                if (loginBtn) {
                    loginBtn.disabled = true;
                }
                if (loginForm) {
                    loginForm.style.opacity = '0.6';
                }
            }
        }

        // Validasi token
        async function validateToken() {
            const tokenInput = document.getElementById('activationToken');
            const validateBtn = document.getElementById('validateBtn');
            const successDiv = document.getElementById('activationSuccess');
            const errorDiv = document.getElementById('activationError');
            
            if (!tokenInput || !validateBtn) {
                console.error('Required elements not found');
                return;
            }
            
            const token = tokenInput.value.trim();
            
            // Reset alerts
            if (successDiv) successDiv.classList.add('d-none');
            if (errorDiv) errorDiv.classList.add('d-none');

            // Validasi input
            if (token.length !== 32) {
                showError('Token harus terdiri dari 32 karakter!');
                return;
            }

            // Disable button saat proses
            validateBtn.disabled = true;
            validateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memvalidasi...';

            try {
                const response = await fetch('/api/token/validate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ token: token })
                });

                const data = await response.json();

                if (data.success) {
                    // Tampilkan Sweet Alert untuk sukses
                    Swal.fire({
                        icon: 'success',
                        title: 'Aktivasi Berhasil!',
                        html: `${data.message}<br><br>
                            <strong>Masa berlaku:</strong> ${data.expired_at}`,
                        showConfirmButton: true,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#198754',
                        timer: 3000,
                        timerProgressBar: true,
                        didClose: () => {
                            // Refresh halaman setelah alert ditutup
                            location.reload();
                        }
                    });
                    
                    // Juga tampilkan pesan sukses di modal
                    showSuccess(data.message + '<br>Masa berlaku baru: ' + data.expired_at);
                    
                    // Tutup modal setelah 1.5 detik
                    setTimeout(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('activationModal'));
                        if (modal) modal.hide();
                    }, 1500);
                    
                } else {
                    // Tampilkan Sweet Alert untuk error
                    Swal.fire({
                        icon: 'error',
                        title: 'Aktivasi Gagal',
                        text: data.message,
                        confirmButtonText: 'Coba Lagi'
                    });
                    
                    showError(data.message);
                }

            } catch (error) {
                console.error('Error:', error);
                
                // Tampilkan Sweet Alert untuk error jaringan
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Jaringan',
                    text: 'Terjadi kesalahan saat memvalidasi token',
                    confirmButtonText: 'OK'
                });
                
                showError('Terjadi kesalahan saat memvalidasi token');
            } finally {
                // Re-enable button
                validateBtn.disabled = false;
                validateBtn.innerHTML = 'Validasi Token';
            }
        }

        function showSuccess(message) {
            const successDiv = document.getElementById('activationSuccess');
            const successMessage = document.getElementById('successMessage');
            
            if (successMessage) {
                successMessage.innerHTML = message;
            }
            if (successDiv) {
                successDiv.classList.remove('d-none');
            }
        }

        function showError(message) {
            const errorDiv = document.getElementById('activationError');
            const errorMessage = document.getElementById('errorMessage');
            
            if (errorMessage) {
                errorMessage.textContent = message;
            }
            if (errorDiv) {
                errorDiv.classList.remove('d-none');
            }
        }

        // Allow Enter key untuk submit token
        document.addEventListener('DOMContentLoaded', function() {
            const tokenInput = document.getElementById('activationToken');
            if (tokenInput) {
                tokenInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        validateToken();
                    }
                });
            }
        });
    </script>

</x-guest-layout>
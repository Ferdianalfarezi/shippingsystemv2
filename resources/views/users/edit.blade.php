<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editUserModalLabel">
                    <i class="bi bi-pencil-fill me-2"></i>Edit User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUserForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_user_id" name="user_id">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label text-dark">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_username" class="form-label text-dark">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_username" name="username" required>
                        <small class="text-muted">Username harus unik dan tidak boleh ada spasi</small>
                    </div>

                    <div class="mb-3">
                        <label for="edit_password" class="form-label text-dark">Password Baru</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="edit_password" name="password">
                            <button class="btn btn-outline-secondary" type="button" id="toggleEditPassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                    </div>

                    <div class="mb-3">
                        <label for="edit_password_confirmation" class="form-label text-dark">Konfirmasi Password Baru</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="edit_password_confirmation" name="password_confirmation">
                            <button class="btn btn-outline-secondary" type="button" id="toggleEditPasswordConfirmation">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_role" class="form-label text-dark">Role <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_role" name="role" required>
                            <option value="">-- Pilih Role --</option>
                            <option value="superadmin">Superadmin</option>
                            <option value="admin">Admin</option>
                            <option value="scanner">Scanner</option>
                            <option value="lp">Logistic Partner</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-save me-2"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Toggle password visibility for edit modal
        $('#toggleEditPassword').on('click', function() {
            const passwordField = $('#edit_password');
            const icon = $(this).find('i');
            
            if (passwordField.attr('type') === 'password') {
                passwordField.attr('type', 'text');
                icon.removeClass('bi-eye').addClass('bi-eye-slash');
            } else {
                passwordField.attr('type', 'password');
                icon.removeClass('bi-eye-slash').addClass('bi-eye');
            }
        });

        $('#toggleEditPasswordConfirmation').on('click', function() {
            const passwordField = $('#edit_password_confirmation');
            const icon = $(this).find('i');
            
            if (passwordField.attr('type') === 'password') {
                passwordField.attr('type', 'text');
                icon.removeClass('bi-eye').addClass('bi-eye-slash');
            } else {
                passwordField.attr('type', 'password');
                icon.removeClass('bi-eye-slash').addClass('bi-eye');
            }
        });

        // Handle edit form submission
        $('#editUserForm').on('submit', function(e) {
            e.preventDefault();
            
            const userId = $('#edit_user_id').val();
            const formData = $(this).serialize();
            
            $.ajax({
                url: '/users/' + userId,
                type: 'POST',
                data: formData,
                success: function(response) {
                    $('#editUserModal').modal('hide');
                    
                    Swal.fire({
                        title: 'Berhasil!',
                        text: response.message || 'User berhasil diupdate',
                        icon: 'success',
                        confirmButtonColor: '#059669'
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan saat mengupdate user';
                    
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        errorMessage = Object.values(errors).flat().join('<br>');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    Swal.fire({
                        title: 'Gagal!',
                        html: errorMessage,
                        icon: 'error',
                        confirmButtonColor: '#dc2626'
                    });
                }
            });
        });

        // Reset form when modal is closed
        $('#editUserModal').on('hidden.bs.modal', function () {
            $('#editUserForm')[0].reset();
            $('#edit_password').attr('type', 'password');
            $('#edit_password_confirmation').attr('type', 'password');
            $('#toggleEditPassword i').removeClass('bi-eye-slash').addClass('bi-eye');
            $('#toggleEditPasswordConfirmation i').removeClass('bi-eye-slash').addClass('bi-eye');
        });
    });
</script>
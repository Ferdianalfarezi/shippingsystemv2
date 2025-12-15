@extends('layouts.app')

@section('title', 'Kelola Iklan')
@section('page-title', 'KELOLA IKLAN')
@section('body-class', 'advertisement-page')

@section('content')
    <!-- Stats Badges dan Dropdown di kanan -->
    <div class="d-flex justify-content-end align-items-center gap-2 mb-3 mt-3">
        
        <!-- Show By Dropdown -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-1">
                <select class="form-select form-select-sm border-0" id="perPageSelect" style="width: auto;">
                    <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500</option>
                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
                </select>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="input-group" style="width: 300px;">
            <input type="text" class="form-control" id="searchInput" placeholder="Cari Judul, Tipe, Jam..." value="{{ request('search') }}">
            <button class="btn btn-secondary" type="button" id="searchButton">
                <i class="bi bi-search"></i>
            </button>
        </div>

        <!-- Menu & Badges -->
        <div class="d-flex align-items-center gap-2">
            <!-- Dropdown Menu Button -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-2">
                    <div class="dropdown">
                        <button class="btn btn-link text-dark p-0 m-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false" style="text-decoration: none;">
                            <i class="bi bi-three-dots-vertical fs-4"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="dropdownMenuButton">
                            <li>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#createAdModal">
                                    <i class="bi bi-plus-circle me-2"></i> Tambah Iklan
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Active Badge -->
            <div class="bg-success card border-0 shadow-sm">
                <div class="card-body p-1">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-10 p-2 rounded me-2">
                            <i class="bi bi-check-circle text-white fs-5"></i>
                        </div>
                        <div>
                            <small class="text-white d-block fw-bold me-3" style="font-size: 0.7rem;">Active</small>
                            <h5 class="mb-0 fw-bold text-white">{{ $totalActive ?? 0 }}</h5>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Inactive Badge -->
            <div class="bg-danger card border-0 shadow-sm me-3">
                <div class="card-body p-1">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-10 p-2 rounded me-2">
                            <i class="bi bi-x-circle text-white fs-5"></i>
                        </div>
                        <div>
                            <small class="text-white d-block fw-bold me-3" style="font-size: 0.7rem;">Inactive</small>
                            <h5 class="mb-0 fw-bold text-white">{{ $totalInactive ?? 0 }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>  

    <div class="table-responsive p-0 mt-0">
        <table class="table table-compact w-100 mt-1" id="advertisementsTable">
            <thead>
                <tr class="fs-5">
                    <th>No</th>
                    <th>Judul</th>
                    <th>Tipe</th>
                    <th>Jam Tayang</th>
                    <th>Durasi</th>
                    <th>Preview</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($advertisements as $index => $ad)
                    <tr class="fs-4 {{ !$ad->is_active ? 'table-danger-subtle' : '' }}">
                        <td><strong>{{ $index + 1 }}</strong></td>
                        <td>{{ $ad->title }}</td>
                        <td>
                            @if($ad->type === 'image')
                                <span class="badge bg-info"><i class="bi bi-image me-1"></i> Image</span>
                            @else
                                <span class="badge bg-danger"><i class="bi bi-play-circle me-1"></i> Video</span>
                            @endif
                        </td>
                        <td><strong>{{ date('H:i', strtotime($ad->start_time)) }}</strong></td>
                        <td>{{ $ad->duration }} detik</td>
                        <td>
                            @if($ad->type === 'image')
                                <img src="{{ asset('storage/' . $ad->file_path) }}" 
                                     alt="{{ $ad->title }}" 
                                     style="max-height: 40px; max-width: 60px; object-fit: cover; cursor: pointer;"
                                     onclick="previewMedia('{{ asset('storage/' . $ad->file_path) }}', 'image', '{{ $ad->title }}')"
                                     class="rounded">
                            @else
                                <button type="button" class="btn btn-sm btn-secondary" 
                                        onclick="previewMedia('{{ asset('storage/' . $ad->file_path) }}', 'video', '{{ $ad->title }}')">
                                    <i class="bi bi-play-circle"></i>
                                </button>
                            @endif
                        </td>
                        <td class="text-center align-middle">
                            <label class="toggle-switch">
                                <input type="checkbox" 
                                    id="active_{{ $ad->id }}" 
                                    {{ $ad->is_active ? 'checked' : '' }}
                                    onchange="toggleActive({{ $ad->id }})">
                                <span class="toggle-slider"></span>
                            </label>
                        </td>
                        <td>
                            <div class="d-flex justify-content-center mt-2 mb-2" style="gap: 0;">
                                <button onclick="editAd({{ $ad->id }})" class="btn btn-warning btn-sm btn-action-square" style="border-radius: 6px 0 0 6px; margin: 0;" title="Edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                
                                <button type="button" onclick="deleteAd({{ $ad->id }})" class="btn btn-danger btn-sm btn-action-square" style="border-radius: 0 6px 6px 0; margin: 0;" title="Hapus">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="mt-3">
                        <td colspan="8" class="text-center py-4">
                            <div class="text-muted">
                                <i class="bi bi-megaphone" style="font-size: 3rem;"></i>
                                <p class="mt-2">Belum ada data iklan</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if(method_exists($advertisements, 'links'))
    <div class="pagination-wrapper">
        {{ $advertisements->links() }}
    </div>
    @endif

    <!-- Modal Create -->
    <div class="modal fade" id="createAdModal" tabindex="-1" aria-labelledby="createAdModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header text-dark">
                    <h5 class="modal-title" id="createAdModalLabel">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Iklan Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createAdForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3 text-dark">
                            <label for="title" class="form-label">Judul Iklan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3 text-dark">
                            <label for="type" class="form-label">Tipe <span class="text-danger">*</span></label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="image">Image (JPG, PNG, GIF)</option>
                                <option value="video">Video (MP4, WebM)</option>
                            </select>
                        </div>
                        <div class="mb-3 text-dark">
                            <label for="file" class="form-label">File <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="file" name="file" required 
                                   accept="image/jpeg,image/png,image/gif,video/mp4,video/webm">
                            <small class="text-muted">Max 50MB</small>
                        </div>
                        <div class="row text-dark">
                            <div class="col-md-6 mb-3">
                                <label for="start_time" class="form-label">Jam Tayang <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="start_time" name="start_time" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="duration" class="form-label">Durasi (detik) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="duration" name="duration" 
                                       min="5" max="210" value="30" required>
                                <small class="text-muted">Min 5, Max 210 (3.5 menit)</small>
                            </div>
                        </div>
                        <div class="mb-3 text-dark">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                <label class="form-check-label" for="is_active">Aktifkan iklan</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success" id="createAdBtn">
                            <i class="bi bi-check-circle me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="editAdModal" tabindex="-1" aria-labelledby="editAdModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header text-dark">
                    <h5 class="modal-title" id="editAdModalLabel">
                        <i class="bi bi-pencil me-2"></i>Edit Iklan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editAdForm" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_id" name="id">
                    <div class="modal-body">
                        <div class="mb-3 text-dark">
                            <label for="edit_title" class="form-label">Judul Iklan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_title" name="title" required>
                        </div>
                        <div class="mb-3 text-dark">
                            <label for="edit_type" class="form-label">Tipe <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_type" name="type" required>
                                <option value="image">Image (JPG, PNG, GIF)</option>
                                <option value="video">Video (MP4, WebM)</option>
                            </select>
                        </div>
                        <div class="mb-3 text-dark">
                            <label for="edit_file" class="form-label">File</label>
                            <input type="file" class="form-control" id="edit_file" name="file" 
                                   accept="image/jpeg,image/png,image/gif,video/mp4,video/webm">
                            <small class="text-muted">Kosongkan jika tidak ingin mengganti file</small>
                            <div id="current_file_preview" class="mt-2"></div>
                        </div>
                        <div class="row text-dark">
                            <div class="col-md-6 mb-3">
                                <label for="edit_start_time" class="form-label">Jam Tayang <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="edit_start_time" name="start_time" required>
                            </div>
                            <div class="col-md-6 mb-3 text-dark">
                                <label for="edit_duration" class="form-label">Durasi (detik) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit_duration" name="duration" 
                                       min="5" max="210" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check text-dark">
                                <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active">
                                <label class="form-check-label" for="edit_is_active">Aktifkan iklan</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer text-dark">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning" id="editAdBtn">
                            <i class="bi bi-check-circle me-1"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Preview -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-dark" id="previewTitle">Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <div id="previewContent"></div>
                </div>
            </div>
        </div>
    </div>

@endsection

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
@push('scripts')
<script>
$(document).ready(function() {
    
    // Create Form Submit
    $('#createAdForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        $('#createAdBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');
        
        $.ajax({
            url: '{{ route("advertisements.store") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message,
                    confirmButtonColor: '#059669'
                }).then(() => {
                    window.location.reload();
                });
            },
            error: function(xhr) {
                $('#createAdBtn').prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Simpan');
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan',
                    confirmButtonColor: '#dc2626'
                });
            }
        });
    });
    
    // Edit Form Submit
    $('#editAdForm').on('submit', function(e) {
        e.preventDefault();
        
        const id = $('#edit_id').val();
        const formData = new FormData(this);
        
        $('#editAdBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');
        
        $.ajax({
            url: `/advertisements/${id}`,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message,
                    confirmButtonColor: '#059669'
                }).then(() => {
                    window.location.reload();
                });
            },
            error: function(xhr) {
                $('#editAdBtn').prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Update');
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan',
                    confirmButtonColor: '#dc2626'
                });
            }
        });
    });
    
    // Reset form on modal close
    $('#createAdModal').on('hidden.bs.modal', function() {
        $('#createAdForm')[0].reset();
        $('#createAdBtn').prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Simpan');
    });
    
    $('#editAdModal').on('hidden.bs.modal', function() {
        $('#editAdForm')[0].reset();
        $('#editAdBtn').prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Update');
    });

    

    // Handle Search Button Click
    $('#searchButton').on('click', function() {
        performSearch();
    });

    // Handle Enter key on search input
    $('#searchInput').on('keypress', function(e) {
        if (e.which === 13) {
            performSearch();
        }
    });

    // Handle Per Page Change
    $('#perPageSelect').on('change', function() {
        const perPage = $(this).val();
        const search = $('#searchInput').val();
        updateUrl(perPage, search);
    });

    // Function to perform search
    function performSearch() {
        const search = $('#searchInput').val();
        const perPage = $('#perPageSelect').val();
        updateUrl(perPage, search);
    }

    // Function to update URL with parameters
    function updateUrl(perPage, search) {
        const url = new URL(window.location.href);
        
        if (perPage && perPage !== '50') {
            url.searchParams.set('per_page', perPage);
        } else {
            url.searchParams.delete('per_page');
        }
        
        if (search && search.trim() !== '') {
            url.searchParams.set('search', search);
        } else {
            url.searchParams.delete('search');
        }
        
        window.location.href = url.toString();
    }
});

// Edit Ad
function editAd(id) {
    $.ajax({
        url: `/advertisements/${id}/edit`,
        type: 'GET',
        success: function(ad) {
            $('#edit_id').val(ad.id);
            $('#edit_title').val(ad.title);
            $('#edit_type').val(ad.type);
            $('#edit_start_time').val(ad.start_time.substring(0, 5));
            $('#edit_duration').val(ad.duration);
            $('#edit_is_active').prop('checked', ad.is_active);
            
            // Show current file preview
            let preview = '';
            if (ad.type === 'image') {
                preview = `<img src="/storage/${ad.file_path}" class="img-fluid rounded" style="max-height: 100px;">`;
            } else {
                preview = `<span class="badge bg-secondary">Video: ${ad.file_path.split('/').pop()}</span>`;
            }
            $('#current_file_preview').html(preview);
            
            $('#editAdModal').modal('show');
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Gagal mengambil data iklan',
                confirmButtonColor: '#dc2626'
            });
        }
    });
}

// Delete Ad
function deleteAd(id) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: 'Data iklan akan dihapus permanen!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Menghapus...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: `/advertisements/${id}`,
                type: 'POST',
                data: { 
                    _token: '{{ csrf_token() }}',
                    _method: 'DELETE'
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message || 'Data berhasil dihapus',
                        confirmButtonColor: '#059669'
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan',
                        confirmButtonColor: '#dc2626'
                    });
                }
            });
        }
    });
}

// Toggle Active
function toggleActive(id) {
    $.ajax({
        url: `/advertisements/${id}/toggle`,
        type: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: response.message,
                timer: 1500,
                showConfirmButton: false
            });
        },
        error: function(xhr) {
            // Revert checkbox
            const checkbox = document.getElementById(`active_${id}`);
            checkbox.checked = !checkbox.checked;
            
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: xhr.responseJSON?.message || 'Terjadi kesalahan',
                confirmButtonColor: '#dc2626'
            });
        }
    });
}

// Preview Media
function previewMedia(url, type, title) {
    $('#previewTitle').text(title);
    
    let content = '';
    if (type === 'image') {
        content = `<img src="${url}" class="img-fluid">`;
    } else {
        content = `<video src="${url}" class="w-100" controls autoplay></video>`;
    }
    
    $('#previewContent').html(content);
    $('#previewModal').modal('show');
}

// Stop video when modal closes
$('#previewModal').on('hidden.bs.modal', function() {
    $('#previewContent').html('');
});
</script>

<style>
/* CUSTOM TOGGLE SWITCH */
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 25px;
    margin: 0;
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
    background-color: #198754;
}

.toggle-switch input:checked + .toggle-slider:before {
    transform: translateX(25px);
}

.toggle-switch input:focus + .toggle-slider {
    box-shadow: 0 0 1px #198754;
}
</style>
@endpush
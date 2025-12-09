@extends('layouts.app')

@section('title', 'User Management')
@section('page-title', 'USER MANAGEMENT')
@section('body-class', 'users-page')

@section('content')
    <!-- Stats Badges dan Controls -->
    <div class="d-flex justify-content-end align-items-center gap-2 mb-3 mt-3">
        
        <!-- Show By Dropdown -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-1">
                <select class="form-select form-select-sm border-0" id="perPageSelect" style="width: auto;">
                    <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
                </select>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="input-group" style="width: 300px;">
            <input type="text" class="form-control" id="searchInput" placeholder="Cari nama atau username..." value="{{ request('search') }}">
            <button class="btn btn-secondary" type="button" id="searchButton">
                <i class="bi bi-search"></i>
            </button>
        </div>

        <!-- Add User Button -->
        <div class="card border-0 shadow-sm">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                <i class="bi bi-plus-circle me-2"></i> Tambah User
            </button>
        </div>

        <!-- Stats Badges -->
        <div class="bg-primary card border-0 shadow-sm">
            <div class="card-body p-1">
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-10 p-2 rounded me-2">
                        <i class="bi bi-people-fill text-white fs-5"></i>
                    </div>
                    <div>
                        <small class="text-white d-block fw-bold me-3" style="font-size: 0.7rem;">Total Users</small>
                        <h5 class="mb-0 fw-bold text-white">{{ $users->total() }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-success card border-0 shadow-sm me-3">
            <div class="card-body p-1">
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-10 p-2 rounded me-2">
                        <i class="bi bi-shield-check text-white fs-5"></i>
                    </div>
                    <div>
                        <small class="text-white d-block fw-bold me-3" style="font-size: 0.7rem;">Admins</small>
                        <h5 class="mb-0 fw-bold text-white">{{ $adminCount }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive p-0 mt-0">
        <table class="table table-compact w-100 mt-1" id="usersTable">
            <thead>
                <tr class="fs-5">
                    <th style="width: 5%;">#</th>
                    <th style="width: 25%;">Name</th>
                    <th style="width: 20%;">Username</th>
                    <th style="width: 15%;">Role</th>
                    <th style="width: 20%;">Created At</th>
                    <th style="width: 15%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $index => $user)
                    <tr class="fs-5">
                        <td>{{ $users->firstItem() + $index }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->username }}</td>
                        <td>
                            <span class="badge {{ $user->role === 'superadmin' ? 'bg-danger' : 'bg-secondary' }} fw-bold px-3 py-2 mt-1 mb-1">
                                {{ strtoupper($user->role) }}
                            </span>
                        </td>
                        <td>{{ $user->created_at->format('d-m-Y H:i') }}</td>
                        <td>
                            <div class="d-flex justify-content-center" style="gap: 0;">
                                <button onclick="openEditModal({{ $user->id }})" class="btn btn-warning btn-sm btn-action-square" style="border-radius: 6px 0 0 6px; margin: 0;" title="Edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                
                                @if($user->id !== auth()->id())
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline delete-form" style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm btn-action-square" style="border-radius: 0 6px 6px 0; margin: 0;" title="Hapus">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                                @else
                                <button class="btn btn-secondary btn-sm btn-action-square" style="border-radius: 0 6px 6px 0; margin: 0; opacity: 0.5;" title="Cannot delete yourself" disabled>
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="mt-3">
                        <td colspan="6" class="text-center py-4">
                            <div class="text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">Belum ada data user</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-wrapper">
        {{ $users->links() }}
    </div>

    @include('users.create')
    @include('users.edit')
@endsection

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

@push('scripts')
<script>
    $(document).ready(function() {
        
        // Handle Search
        $('#searchButton').on('click', function() {
            performSearch();
        });

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

        function performSearch() {
            const search = $('#searchInput').val();
            const perPage = $('#perPageSelect').val();
            updateUrl(perPage, search);
        }

        function updateUrl(perPage, search) {
            const url = new URL(window.location.href);
            
            if (perPage && perPage !== '25') {
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

        // Delete confirmation
        $('.delete-form').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const url = form.attr('action');
            
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "User ini akan dihapus permanen!",
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
                        url: url,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: response.message || 'User berhasil dihapus',
                                icon: 'success',
                                confirmButtonColor: '#059669'
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: 'Gagal!',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus user',
                                icon: 'error',
                                confirmButtonColor: '#dc2626'
                            });
                        }
                    });
                }
            });
        });
    });

    // Open Edit Modal
    function openEditModal(userId) {
        $.ajax({
            url: '/users/' + userId + '/edit',
            type: 'GET',
            success: function(response) {
                $('#edit_user_id').val(response.id);
                $('#edit_name').val(response.name);
                $('#edit_username').val(response.username);
                $('#edit_role').val(response.role);
                
                $('#editUserModal').modal('show');
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Gagal mengambil data user',
                    icon: 'error',
                    confirmButtonColor: '#dc2626'
                });
            }
        });
    }
</script>
@endpush
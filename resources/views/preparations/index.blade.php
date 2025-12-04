@extends('layouts.app')

@section('title', 'Data Preparation')
@section('page-title', 'PREPARATIONS MONITORING')
@section('body-class', 'preparation-page')

@section('content')
    <!-- Button Tambah Data -->
    <div class="mb-3">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPreparationModal">
            <i class="bi bi-plus-circle"></i> Tambah Preparation
        </button>
    </div>

    <div class="table-responsive p-0 mt-0">
        <table class="table table-compact w-100 mt-3" id="preparationsTable">
            <thead>
                <tr class="fs-5">
                    <th>Route</th>
                    <th>LP</th>
                    <th>No DN</th>
                    <th>Cust</th>
                    <th>Dock</th>
                    <th>Delv Date</th>
                    <th>Delv Time</th>
                    <th>Cyc</th>
                    <th>Pull Date</th>
                    <th>Finish Pulling</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($preparations as $index => $prep)
                    <tr class="fs-4 {{ $prep->status === 'delay' ? 'table-danger-subtle' : '' }}">
                        <td><strong>{{ $prep->route }}</strong></td>
                        <td>{{ $prep->logistic_partners }}</td>
                        <td>{{ $prep->no_dn }}</td>
                        <td>{{ $prep->customers }}</td>
                        <td><strong>{{ $prep->dock }}</strong></td>
                        <td>{{ $prep->delivery_date->format('d-m-y') }}</td>
                        <td>{{ date('H:i:s', strtotime($prep->delivery_time)) }}</td>
                        <td><strong>{{ $prep->cycle }}</strong></td>
                        <td>{{ $prep->pulling_date->format('d-m-y') }}</td>
                        <td>{{ date('H:i:s', strtotime($prep->pulling_time)) }}</td>
                        <td>
                            <span class="badge {{ $prep->status_badge }} fw-bold px-3 py-2 mb-1" 
                                  title="{{ $prep->status === 'delay' ? 'Terlambat ' . $prep->delay_duration : 'On Time' }}">
                                {{ $prep->status_label }}
                            </span>
                        </td>
                        <td>
                            <div>
                                <button onclick="openEditModal({{ $prep->id }})" class="btn btn-warning btn-sm" title="Edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <form action="{{ route('preparations.destroy', $prep) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                                <a href="{{ route('preparations.show', $prep) }}" class="btn btn-secondary btn-sm" title="Detail">
                                    <i class="bi bi-grid-3x3-gap-fill"></i>
                                </a>
                                <a href="#" class="btn btn-primary btn-sm" title="Next">
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center py-4">
                            <div class="text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">Belum ada data preparation</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-wrapper">
        {{ $preparations->links() }}
    </div>
    
    <!-- Include Modal Create -->
    @include('preparations.create')
    
    <!-- Include Modal Edit -->
    @include('preparations.edit')
    
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Delete confirmation dengan SweetAlert
    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data ini akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan loading
                Swal.fire({
                    title: 'Menghapus...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Submit form
                form.submit();
            }
        });
    });
});
</script>
@endpush
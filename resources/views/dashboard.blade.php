@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'DASHBOARD')

@section('content')
<div class="row">
    <!-- Welcome Card -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <h3 class="mb-3">Selamat Datang, {{ Auth::user()->name }}! 👋</h3>
                <p class="text-muted mb-0">Role Anda: <span class="badge bg-primary">{{ strtoupper(Auth::user()->role) }}</span></p>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div class="card-body text-center">
                <i class="bi bi-clipboard-data" style="font-size: 3rem; opacity: 0.8;"></i>
                <h2 class="mt-3 mb-0">{{ \App\Models\Preparation::count() }}</h2>
                <p class="mb-0">Total Preparations</p>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
            <div class="card-body text-center">
                <i class="bi bi-calendar-check" style="font-size: 3rem; opacity: 0.8;"></i>
                <h2 class="mt-3 mb-0">{{ \App\Models\Preparation::whereDate('delivery_date', today())->count() }}</h2>
                <p class="mb-0">Delivery Hari Ini</p>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card" style="background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: white;">
            <div class="card-body text-center">
                <i class="bi bi-truck" style="font-size: 3rem; opacity: 0.8;"></i>
                <h2 class="mt-3 mb-0">0</h2>
                <p class="mb-0">On Process</p>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white;">
            <div class="card-body text-center">
                <i class="bi bi-exclamation-triangle" style="font-size: 3rem; opacity: 0.8;"></i>
                <h2 class="mt-3 mb-0">0</h2>
                <p class="mb-0">Pending</p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Access Menu -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Access Menu</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="{{ route('preparations.index') }}" class="text-decoration-none">
                            <div class="card text-center h-100" style="border: 2px solid #667eea; cursor: pointer; transition: all 0.3s;">
                                <div class="card-body">
                                    <i class="bi bi-clipboard-data" style="font-size: 3rem; color: #667eea;"></i>
                                    <h5 class="mt-3">Preparations</h5>
                                    <p class="text-muted mb-0">Kelola data preparation</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-3">
                        <div class="card text-center h-100" style="border: 2px solid #d1d5db; cursor: not-allowed; opacity: 0.5;">
                            <div class="card-body">
                                <i class="bi bi-box-seam" style="font-size: 3rem; color: #6b7280;"></i>
                                <h5 class="mt-3">Shippings</h5>
                                <p class="text-muted mb-0">Coming Soon</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card text-center h-100" style="border: 2px solid #d1d5db; cursor: not-allowed; opacity: 0.5;">
                            <div class="card-body">
                                <i class="bi bi-arrow-repeat" style="font-size: 3rem; color: #6b7280;"></i>
                                <h5 class="mt-3">Milkruns</h5>
                                <p class="text-muted mb-0">Coming Soon</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card text-center h-100" style="border: 2px solid #d1d5db; cursor: not-allowed; opacity: 0.5;">
                            <div class="card-body">
                                <i class="bi bi-truck" style="font-size: 3rem; color: #6b7280;"></i>
                                <h5 class="mt-3">Delivery</h5>
                                <p class="text-muted mb-0">Coming Soon</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Preparations -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Preparations</h5>
                <a href="{{ route('preparations.index') }}" class="btn btn-primary btn-sm">
                    Lihat Semua <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No DN</th>
                                <th>Route</th>
                                <th>Customer</th>
                                <th>Delivery Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(\App\Models\Preparation::latest()->limit(5)->get() as $prep)
                                <tr>
                                    <td><strong>{{ $prep->no_dn }}</strong></td>
                                    <td>{{ $prep->route }}</td>
                                    <td>{{ $prep->customers }}</td>
                                    <td>{{ $prep->delivery_date->format('d/m/Y') }}</td>
                                    <td><span class="badge bg-info">Prepared</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                            <p class="mt-2">Belum ada data preparation</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
.card:hover {
    transform: translateY(-5px);
    transition: all 0.3s ease;
}
</style>
@endpush
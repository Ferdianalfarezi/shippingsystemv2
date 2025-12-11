<!-- Detail History Modal -->
<div class="modal fade" id="detailHistoryModal" tabindex="-1" aria-labelledby="detailHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-white text-dark">
                <h5 class="modal-title fw-bold" id="detailHistoryModalLabel">
                    <i class="bi bi-eye me-2"></i>Detail History
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Header with DN and Status -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <h4 class="fw-bold text-primary mb-0">DN: <span id="detail_no_dn"></span></h4>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="badge bg-success fw-bold px-3 py-2">Completed</span>
                    </div>
                </div>

                <!-- Basic Info Section -->
                <div class="card mb-3">
                    <div class="card-header bg-light py-2">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2"></i>Informasi Dasar</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted" style="width: 120px;">Route</td>
                                        <td><strong id="detail_route"></strong></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">LP</td>
                                        <td id="detail_logistic_partners"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Customer</td>
                                        <td id="detail_customers"></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted" style="width: 120px;">Dock</td>
                                        <td><strong id="detail_dock"></strong></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Cycle</td>
                                        <td><strong id="detail_cycle"></strong></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Address</td>
                                        <td id="detail_address"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timeline Section -->
                <div class="card mb-3">
                    <div class="card-header bg-light py-2">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2"></i>Timeline Perjalanan</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="timeline-container p-3">
                            <!-- Timeline Items -->
                            <div class="d-flex align-items-start mb-3">
                                <div class="timeline-icon bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; min-width: 40px;">
                                    <i class="bi bi-1-circle-fill"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong class="text-secondary">Pulling Schedule</strong>
                                            <p class="mb-0 text-muted small">Jadwal pulling barang</p>
                                        </div>
                                        <span class="badge bg-secondary-subtle text-secondary" id="detail_pulling_datetime">-</span>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-start mb-3">
                                <div class="timeline-icon bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; min-width: 40px;">
                                    <i class="bi bi-2-circle-fill"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong class="text-warning">Scan to Shipping</strong>
                                            <p class="mb-0 text-muted small">Masuk proses shipping</p>
                                        </div>
                                        <span class="badge bg-warning-subtle text-warning" id="detail_scan_to_shipping">-</span>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-start mb-3">
                                <div class="timeline-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; min-width: 40px;">
                                    <i class="bi bi-3-circle-fill"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong class="text-primary">Arrival (Truk Tiba)</strong>
                                            <p class="mb-0 text-muted small">Truk tiba di lokasi</p>
                                        </div>
                                        <span class="badge bg-primary-subtle text-primary" id="detail_arrival">-</span>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-start mb-3">
                                <div class="timeline-icon bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; min-width: 40px;">
                                    <i class="bi bi-4-circle-fill"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong class="text-info">Scan to Delivery</strong>
                                            <p class="mb-0 text-muted small">Masuk proses delivery</p>
                                        </div>
                                        <span class="badge bg-info-subtle text-info" id="detail_scan_to_delivery">-</span>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-start">
                                <div class="timeline-icon bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; min-width: 40px;">
                                    <i class="bi bi-5-circle-fill"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong class="text-success">Completed</strong>
                                            <p class="mb-0 text-muted small">Selesai / dipindah ke history</p>
                                        </div>
                                        <span class="badge bg-success-subtle text-success" id="detail_completed_at">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



                <!-- User Info -->
                <div class="d-flex justify-content-between align-items-center text-muted small">
                    <span><i class="bi bi-person me-1"></i>Completed by: <strong id="detail_moved_by">-</strong></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.timeline-container {
    position: relative;
}

.timeline-container::before {
    content: '';
    position: absolute;
    left: 19px;
    top: 50px;
    bottom: 50px;
    width: 2px;
    background: linear-gradient(to bottom, #6c757d, #ffc107, #0d6efd, #0dcaf0, #198754);
}

.timeline-icon {
    z-index: 1;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.bg-secondary-subtle {
    background-color: rgba(108, 117, 125, 0.1) !important;
}

.bg-warning-subtle {
    background-color: rgba(255, 193, 7, 0.15) !important;
}

.bg-primary-subtle {
    background-color: rgba(13, 110, 253, 0.15) !important;
}

.bg-info-subtle {
    background-color: rgba(13, 202, 240, 0.15) !important;
}

.bg-success-subtle {
    background-color: rgba(25, 135, 84, 0.15) !important;
}
</style>
<!-- Print Delay Modal -->
<div class="modal fade" id="printDelayModal" tabindex="-1" aria-labelledby="printDelayModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="printDelayModalLabel">
                    <i class="bi bi-printer me-2"></i>Print Data Delay
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Filter Section -->
                <div class="row mb-4 text-dark">
                    <div class="col-md-4">
                        <label for="printDateFrom" class="form-label fw-semibold">Dari Tanggal</label>
                        <input type="date" class="form-control" id="printDateFrom">
                    </div>
                    <div class="col-md-4">
                        <label for="printDateTo" class="form-label fw-semibold">Sampai Tanggal</label>
                        <input type="date" class="form-control" id="printDateTo">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="button" class="btn btn-primary w-100" id="filterPrintData">
                            <i class="bi bi-funnel me-1"></i> Filter Data
                        </button>
                    </div>
                </div>

                <!-- Remark Section -->
                <div class="mb-4 text-dark">
                    <label for="printRemark" class="form-label fw-semibold">Remark / Keterangan (Optional)</label>
                    <textarea class="form-control" id="printRemark" rows="2" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                </div>

                <!-- Info Summary -->
                <div class="alert alert-secondary d-flex justify-content-between align-items-center mb-3">
                    <span>
                        <i class="bi bi-info-circle me-1"></i>
                        Total Data Delay: <strong id="printTotalCount">0</strong> item
                    </span>
                    <span class="text-muted" id="printDateRange">-</span>
                </div>

                <!-- Preview Table -->
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-bordered table-sm" id="printPreviewTable">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <th style="width: 40px;">No</th>
                                <th>Route</th>
                                <th>LP</th>
                                <th>No DN</th>
                                <th>Customer</th>
                                <th>Dock</th>
                                <th>Scan to Delv</th>
                                <th>Cycle</th>
                                <th>Address</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="printPreviewBody">
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="bi bi-filter me-1"></i> Pilih tanggal dan klik Filter untuk menampilkan data
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i> Tutup
                </button>
                <button type="button" class="btn btn-danger" id="executePrint" disabled>
                    <i class="bi bi-printer me-1"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Store filtered data
    let filteredData = [];

    // ==================== PRINT DELAY MODAL ====================
    
    // Open Print Modal
    $('#openPrintDelayModal').on('click', function() {
        console.log('Print button clicked'); // Debug
        
        // Reset form
        $('#printDateFrom').val('');
        $('#printDateTo').val('');
        $('#printRemark').val('');
        $('#printPreviewBody').html(`
            <tr>
                <td colspan="10" class="text-center text-muted py-4">
                    <i class="bi bi-filter me-1"></i> Pilih tanggal dan klik Filter untuk menampilkan data
                </td>
            </tr>
        `);
        $('#printTotalCount').text('0');
        $('#printDateRange').text('-');
        $('#executePrint').prop('disabled', true);
        
        // Set default date (today)
        const today = new Date().toISOString().split('T')[0];
        $('#printDateTo').val(today);
        
        // Show modal
        $('#printDelayModal').modal('show');
    });

    // Filter Print Data
    $('#filterPrintData').on('click', function() {
        const dateFrom = $('#printDateFrom').val();
        const dateTo = $('#printDateTo').val();

        console.log('Filter clicked', { dateFrom, dateTo }); // Debug

        // Loading state
        $('#printPreviewBody').html(`
            <tr>
                <td colspan="10" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <span class="ms-2">Memuat data...</span>
                </td>
            </tr>
        `);

        // Fetch data via AJAX
        $.ajax({
            url: '{{ route("deliveries.getDelayData") }}',
            type: 'GET',
            data: {
                date_from: dateFrom,
                date_to: dateTo
            },
            success: function(response) {
                console.log('Response:', response); // Debug
                
                if (response.success) {
                    filteredData = response.data;
                    renderPreviewTable(filteredData);
                    
                    // Update info
                    $('#printTotalCount').text(filteredData.length);
                    
                    let dateRangeText = '';
                    if (dateFrom && dateTo) {
                        dateRangeText = formatDate(dateFrom) + ' - ' + formatDate(dateTo);
                    } else if (dateFrom) {
                        dateRangeText = 'Dari ' + formatDate(dateFrom);
                    } else if (dateTo) {
                        dateRangeText = 'Sampai ' + formatDate(dateTo);
                    } else {
                        dateRangeText = 'Semua tanggal';
                    }
                    $('#printDateRange').text(dateRangeText);
                    
                    // Enable print button if has data
                    $('#executePrint').prop('disabled', filteredData.length === 0);
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr); // Debug
                
                $('#printPreviewBody').html(`
                    <tr>
                        <td colspan="10" class="text-center text-danger py-4">
                            <i class="bi bi-exclamation-triangle me-1"></i> Gagal memuat data: ${xhr.responseJSON?.message || 'Unknown error'}
                        </td>
                    </tr>
                `);
            }
        });
    });

    // Render Preview Table
    function renderPreviewTable(data) {
        if (data.length === 0) {
            $('#printPreviewBody').html(`
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">
                        <i class="bi bi-inbox me-1"></i> Tidak ada data delay pada rentang tanggal tersebut
                    </td>
                </tr>
            `);
            return;
        }

        let html = '';
        data.forEach(function(item, index) {
            html += '<tr>' +
                '<td>' + (index + 1) + '</td>' +
                '<td><strong>' + item.route + '</strong></td>' +
                '<td>' + (item.logistic_partners || '-') + '</td>' +
                '<td>' + item.no_dn + '</td>' +
                '<td>' + (item.customers || '-') + '</td>' +
                '<td><strong>' + (item.dock || '-') + '</strong></td>' +
                '<td>' + (item.scan_to_delivery_formatted || '-') + '</td>' +
                '<td><strong>' + (item.cycle || '-') + '</strong></td>' +
                '<td>' + (item.address || '-') + '</td>' +
                '<td><span class="badge bg-danger">' + item.status_label + (item.delay_duration ? ' + ' + item.delay_duration : '') + '</span></td>' +
                '</tr>';
        });

        $('#printPreviewBody').html(html);
    }

    // Execute Print
    $('#executePrint').on('click', function() {
        if (filteredData.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Tidak ada data',
                text: 'Tidak ada data untuk di-print',
                confirmButtonColor: '#dc2626'
            });
            return;
        }

        const remark = $('#printRemark').val();
        const dateFrom = $('#printDateFrom').val();
        const dateTo = $('#printDateTo').val();
        
        // Generate print content
        let printContent = generatePrintContent(filteredData, remark, dateFrom, dateTo);
        
        // Open print window
        const printWindow = window.open('', '_blank', 'width=1000,height=700');
        printWindow.document.write(printContent);
        printWindow.document.close();
        
        // Wait for content to load then print
        setTimeout(function() {
            printWindow.focus();
            printWindow.print();
        }, 250);
    });

    // Generate Print Content
    function generatePrintContent(data, remark, dateFrom, dateTo) {
        const now = new Date();
        const printDate = now.toLocaleDateString('id-ID', { 
            day: '2-digit', 
            month: 'long', 
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        // Get current user name
        const createdBy = '{{ auth()->user()->name ?? auth()->user()->email ?? "User" }}';

        let dateRangeText = '';
        if (dateFrom && dateTo) {
            dateRangeText = formatDate(dateFrom) + ' - ' + formatDate(dateTo);
        } else if (dateFrom) {
            dateRangeText = 'Dari ' + formatDate(dateFrom);
        } else if (dateTo) {
            dateRangeText = 'Sampai ' + formatDate(dateTo);
        } else {
            dateRangeText = 'Semua tanggal';
        }

        let tableRows = '';
        data.forEach(function(item, index) {
            tableRows += '<tr>' +
                '<td style="text-align: center;">' + (index + 1) + '</td>' +
                '<td><strong>' + item.route + '</strong></td>' +
                '<td>' + (item.logistic_partners || '-') + '</td>' +
                '<td>' + item.no_dn + '</td>' +
                '<td>' + (item.customers || '-') + '</td>' +
                '<td><strong>' + (item.dock || '-') + '</strong></td>' +
                '<td>' + (item.scan_to_delivery_formatted || '-') + '</td>' +
                '<td style="text-align: center;"><strong>' + (item.cycle || '-') + '</strong></td>' +
                '<td>' + (item.address || '-') + '</td>' +
                '<td class="status-delay">' + item.status_label + (item.delay_duration ? ' + ' + item.delay_duration : '') + '</td>' +
                '</tr>';
        });

        let remarkSection = '';
        if (remark && remark.trim() !== '') {
            remarkSection = '<div class="print-remark">' +
                '<strong>Remark / Keterangan:</strong>' +
                '<p>' + remark + '</p>' +
                '</div>';
        }

        // Ambil logo URL (sesuaikan path logo lo)
        const logoUrl = '{{ asset("images/logostep.png") }}';
        
        return '<!DOCTYPE html>' +
            '<html>' +
            '<head>' +
                '<title>Laporan Data Delay - ' + dateRangeText + '</title>' +
                '<style>' +
                    '* { margin: 0; padding: 0; box-sizing: border-box; }' +
                    'body { font-family: Arial, sans-serif; font-size: 11px; padding: 20px; }' +
                    '.print-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 15px; }' +
                    '.print-header-left { flex: 1; }' +
                    '.print-header-left h2 { margin: 0 0 5px 0; font-size: 18px; text-transform: uppercase; }' +
                    '.print-header-left p { margin: 3px 0; color: #333; font-size: 12px; }' +
                    '.print-header-right { }' +
                    '.print-header-right img { max-height: 60px; max-width: 150px; object-fit: contain; }' +
                    '.print-info { margin-bottom: 15px; display: flex; justify-content: space-between; font-size: 11px; }' +
                    '.print-remark { margin-bottom: 15px; padding: 10px; border: 1px solid #999; background: #f5f5f5; }' +
                    '.print-remark strong { display: block; margin-bottom: 5px; font-size: 11px; }' +
                    '.print-remark p { margin: 0; }' +
                    '.print-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }' +
                    '.print-table th, .print-table td { border: 1px solid #000; padding: 4px 6px; text-align: left; font-size: 10px; }' +
                    '.print-table th { background: #fff; color: #000; font-weight: bold; text-align: center; }' +
                    '.print-table tr:nth-child(even) { background: #f2f2f2; }' +
                    '.print-footer { margin-top: 20px; font-size: 10px; color: #666; display: flex; justify-content: space-between; border-top: 1px solid #ccc; padding-top: 10px; }' +
                    '.signature-section { display: flex; justify-content: space-between; margin-top: 50px; padding-top: 20px; }' +
                    '.signature-box { width: 200px; text-align: center; }' +
                    '.signature-label { font-size: 11px; margin-bottom: 60px; font-weight: bold; }' +
                    '.signature-line { border-bottom: 1px solid #000; margin-bottom: 5px; }' +
                    '.signature-name { font-size: 11px; font-weight: bold; }' +
                    '.status-delay { font-weight: bold; color: #000; }' +
                    '@media print { body { padding: 10px; } .print-table th, .print-table td { font-size: 9px; padding: 3px 5px; } }' +
                '</style>' +
            '</head>' +
            '<body>' +
                '<div class="print-header">' +
                    '<div class="print-header-left">' +
                        '<h2>Laporan Data Delay Delivery</h2>' +
                        '<p>Periode: ' + dateRangeText + '</p>' +
                    '</div>' +
                    '<div class="print-header-right">' +
                        '<img src="' + logoUrl + '" alt="Logo" onerror="this.style.display=\'none\'">' +
                    '</div>' +
                '</div>' +
                '<div class="print-info">' +
                    '<div><strong>Total Data:</strong> ' + data.length + ' item</div>' +
                    '<div><strong>Tanggal Cetak:</strong> ' + printDate + '</div>' +
                '</div>' +
                remarkSection +
                '<table class="print-table">' +
                    '<thead>' +
                        '<tr>' +
                            '<th style="width: 30px;">No</th>' +
                            '<th>Route</th>' +
                            '<th>LP</th>' +
                            '<th>No DN</th>' +
                            '<th>Customer</th>' +
                            '<th>Dock</th>' +
                            '<th>Scan to Delv</th>' +
                            '<th style="width: 40px;">Cyc</th>' +
                            '<th>Address</th>' +
                            '<th>Status</th>' +
                        '</tr>' +
                    '</thead>' +
                    '<tbody>' + tableRows + '</tbody>' +
                '</table>' +
                '<div class="signature-section">' +
                    '<div class="signature-box">' +
                        '<p class="signature-label">Dibuat oleh,</p>' +
                        '<div class="signature-line"></div>' +
                        '<p class="signature-name">' + createdBy + '</p>' +
                    '</div>' +
                    '<div class="signature-box">' +
                        '<p class="signature-label">Disetujui oleh,</p>' +
                        '<div class="signature-line"></div>' +
                        '<p class="signature-name">____________________</p>' +
                    '</div>' +
                '</div>' +
            '</body>' +
            '</html>';
    }

    // Format date helper
    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
    }
    
    // ==================== END PRINT DELAY MODAL ====================
});
</script>
@endpush
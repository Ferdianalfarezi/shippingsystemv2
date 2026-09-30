@extends('layouts.app')

@section('title', 'Kanban TGI')
@section('page-title', 'KANBAN TGI')
@section('body-class', 'kanban-tgi-split-page')

@section('content')

    <div class="row justify-content-center mt-3">
        <div class="col-lg-6 col-md-8">

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-4">

                    <div class="d-flex align-items-center mb-1">
                        <div class="bg-primary bg-opacity-10 p-2 rounded me-2">
                            <i class="bi bi-file-earmark-pdf text-primary fs-5"></i>
                        </div>
                        <h5 class="mb-0 fw-bold">Proses Delivery Note TGI</h5>
                    </div>
                    <p class="text-muted small mb-4">Upload PDF Delivery Note — sistem otomatis ambil 1 kanban per part no .</p>

                    <form id="processForm" action="{{ route('kanban-tgi-split.process') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label fw-semibold small text-secondary">File PDF Delivery Note</label>
                            <input type="file" name="delivery_note" accept="application/pdf" required class="form-control">
                        </div>

                        <button type="submit" id="submitBtn" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2">
                            <span id="submitText">Proses</span>
                            <span id="submitSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>
                    </form>

                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-secondary bg-opacity-10 p-2 rounded me-2">
                                <i class="bi bi-clock-history text-secondary fs-5"></i>
                            </div>
                            <h6 class="mb-0 fw-bold">Riwayat Terakhir</h6>
                        </div>
                        <button type="button" class="btn btn-sm btn-light" onclick="loadRecent()" title="Refresh">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>

                    <div id="recentList" class="list-group list-group-flush">
                        <div class="text-center text-muted small py-3" id="recentEmpty">
                            <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                            Belum ada riwayat
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Processing — terkunci (gak bisa ditutup manual) selama proses jalan -->
    <div class="modal fade" id="processingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-5">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                    <h6 id="processingText" class="fw-bold mb-2 text-dark">Processing Delivery Note...</h6>
                    <p class="text-muted small mb-0">Jangan tutup atau refresh halaman ini.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Preview -->
    <div class="modal fade" id="pdfModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content" style="height: 90vh;">

                <div class="modal-header py-2">
                    <h6 class="modal-title fw-bold mb-0 text-dark">
                        <i class="bi bi-file-earmark-pdf text-danger me-1"></i> Hasil Kanban TGI
                    </h6>
                    <button type="button" class="btn-close" onclick="closePdfModal()"></button>
                </div>

                <div id="filterBar" class="d-none px-3 py-2 border-bottom bg-light d-flex flex-wrap align-items-center gap-2">
                    <label class="small fw-semibold text-secondary mb-0">Part No:</label>
                    <select id="partNoFilter" class="form-select form-select-sm" style="width: auto;">
                        <option value="">Semua Part No</option>
                    </select>

                    <span id="filterCount" class="small text-muted"></span>
                    <span id="filterLoading" class="d-none small text-muted">
                        <span class="spinner-border spinner-border-sm"></span> Memproses filter...
                    </span>
                </div>

                <div class="modal-body p-0 bg-light flex-grow-1">
                    <iframe id="pdfFrame" class="w-100 h-100 border-0"></iframe>
                </div>

                <div class="modal-footer py-2 d-flex justify-content-between">
                    <div id="resultInfo" class="small text-muted"></div>
                    <div class="d-flex gap-2">
                        <a id="downloadLink" href="#" download="hasil-kanban-tgi.pdf" class="btn btn-light btn-sm">
                            <i class="bi bi-download me-1"></i> Download
                        </a>
                        <button onclick="printPdf()" class="btn btn-primary btn-sm">
                            <i class="bi bi-printer me-1"></i> Print
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js"></script>
<script>
    const form = document.getElementById('processForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitSpinner = document.getElementById('submitSpinner');
    const pdfFrame = document.getElementById('pdfFrame');
    const downloadLink = document.getElementById('downloadLink');
    const resultInfo = document.getElementById('resultInfo');
    const filterBar = document.getElementById('filterBar');
    const partNoFilter = document.getElementById('partNoFilter');
    const filterCount = document.getElementById('filterCount');
    const filterLoading = document.getElementById('filterLoading');

    const processingModalEl = document.getElementById('processingModal');
    const processingModal = new bootstrap.Modal(processingModalEl);
    const processingTextEl = document.getElementById('processingText');

    const pdfModalEl = document.getElementById('pdfModal');
    const pdfModal = new bootstrap.Modal(pdfModalEl);

    const itemsMetaUrlTemplate = "{{ route('kanban-tgi-split.items-meta', ['token' => 'TOKEN_PLACEHOLDER']) }}";
    const downloadUrlTemplate = "{{ route('kanban-tgi-split.download', ['token' => 'TOKEN_PLACEHOLDER']) }}";
    const recentUrl = "{{ route('kanban-tgi-split.recent') }}";

    const recentList = document.getElementById('recentList');
    const recentEmpty = document.getElementById('recentEmpty');

    let currentBlobUrl = null;
    let originalPdfBytes = null;
    let itemsMeta = []; // matched items aja, masing2 punya part_no + pages

    // Cuma 2 variasi teks, gantian selama modal processing nampil
    const processingTexts = [
        'Processing Delivery Note...',
        'SQL Resource full please add new subscription...',
    ];
    let processingTextInterval = null;

    function startProcessingTextRotation() {
        let idx = 0;
        processingTextEl.textContent = processingTexts[0];
        processingTextInterval = setInterval(() => {
            idx = (idx + 1) % processingTexts.length;
            processingTextEl.textContent = processingTexts[idx];
        }, 8000);
    }

    function stopProcessingTextRotation() {
        if (processingTextInterval) {
            clearInterval(processingTextInterval);
            processingTextInterval = null;
        }
    }

    function setLoading(isLoading) {
        submitBtn.disabled = isLoading;
        submitText.textContent = isLoading ? 'Memproses...' : 'Proses';
        submitSpinner.classList.toggle('d-none', !isLoading);
    }

    function showAlert(message) {
        Swal.fire({ title: 'Gagal!', text: message, icon: 'error', confirmButtonColor: '#dc2626' });
    }

    function closePdfModal() {
        pdfModal.hide();
    }

    pdfModalEl.addEventListener('hidden.bs.modal', function () {
        pdfFrame.src = 'about:blank';
        if (currentBlobUrl) {
            URL.revokeObjectURL(currentBlobUrl);
            currentBlobUrl = null;
        }
        originalPdfBytes = null;
        itemsMeta = [];
        partNoFilter.innerHTML = '<option value="">Semua Part No</option>';
        filterBar.classList.add('d-none');
        filterCount.textContent = '';
    });

    function printPdf() {
        pdfFrame.contentWindow.focus();
        pdfFrame.contentWindow.print();
    }

    function setBlobToFrame(bytes, filename) {
        if (currentBlobUrl) {
            URL.revokeObjectURL(currentBlobUrl);
        }
        const blob = new Blob([bytes], { type: 'application/pdf' });
        currentBlobUrl = URL.createObjectURL(blob);
        pdfFrame.src = currentBlobUrl;
        downloadLink.href = currentBlobUrl;
        downloadLink.download = filename;
    }

    // Isi dropdown part no dari items-meta (cuma yang matched, karena
    // cuma itu yang punya halaman di PDF hasil).
    async function loadFiltersMeta(token) {
        partNoFilter.innerHTML = '<option value="">Semua Part No</option>';

        if (!token) {
            filterBar.classList.add('d-none');
            return;
        }

        try {
            const url = itemsMetaUrlTemplate.replace('TOKEN_PLACEHOLDER', token);
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('gagal ambil metadata');

            const data = await res.json();
            itemsMeta = (data.items || []).filter(it => it.matched && it.pages && it.pages.length);

            if (itemsMeta.length === 0) {
                filterBar.classList.add('d-none');
                return;
            }

            itemsMeta.forEach(it => {
                const opt = document.createElement('option');
                opt.value = it.part_no;
                opt.textContent = `${it.part_no} (${it.pages.length})`;
                partNoFilter.appendChild(opt);
            });

            filterBar.classList.remove('d-none');
        } catch (err) {
            console.error('Gagal load metadata filter:', err);
            filterBar.classList.add('d-none');
        }
    }

    async function applyFilter() {
        if (!originalPdfBytes) return;

        const selectedPartNo = partNoFilter.value;

        if (!selectedPartNo) {
            setBlobToFrame(originalPdfBytes, 'hasil-kanban-tgi.pdf');
            filterCount.textContent = '';
            return;
        }

        filterLoading.classList.remove('d-none');
        try {
            const item = itemsMeta.find(it => it.part_no === selectedPartNo);
            const matchingPages = (item ? item.pages : []).map(p => p - 1); // pdf-lib 0-based

            if (matchingPages.length === 0) {
                filterCount.textContent = 'Gak ada halaman buat part no ini.';
                filterLoading.classList.add('d-none');
                return;
            }

            const { PDFDocument } = PDFLib;
            const srcDoc = await PDFDocument.load(originalPdfBytes);
            const newDoc = await PDFDocument.create();
            const copiedPages = await newDoc.copyPages(srcDoc, matchingPages);
            copiedPages.forEach(p => newDoc.addPage(p));

            const filteredBytes = await newDoc.save();
            const safeName = selectedPartNo.replace(/[^A-Za-z0-9_\-]/g, '_');
            setBlobToFrame(filteredBytes, `hasil-kanban-tgi_${safeName}.pdf`);

            filterCount.textContent = `Menampilkan ${matchingPages.length} halaman`;
        } catch (err) {
            console.error(err);
            showAlert('Gagal filter PDF: ' + err.message);
        } finally {
            filterLoading.classList.add('d-none');
        }
    }

    partNoFilter.addEventListener('change', applyFilter);

    function badgeClass(unmatched) {
        return unmatched > 0 ? 'bg-warning text-dark' : 'bg-success';
    }

    async function loadRecent() {
        try {
            const res = await fetch(recentUrl, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('gagal ambil riwayat');
            const data = await res.json();
            const items = data.items || [];

            recentList.querySelectorAll('.recent-item').forEach(el => el.remove());

            if (items.length === 0) {
                recentEmpty.classList.remove('d-none');
                return;
            }
            recentEmpty.classList.add('d-none');

            items.forEach(item => {
                const row = document.createElement('div');
                row.className = 'list-group-item recent-item px-0 py-2 d-flex align-items-center justify-content-between';
                row.innerHTML = `
                    <div class="me-2" style="min-width:0;">
                        <div class="fw-semibold small text-truncate" style="max-width: 220px;" title="${item.filename}">
                            <i class="bi bi-file-earmark-pdf text-danger me-1"></i>${item.filename}
                        </div>
                        <div class="text-muted" style="font-size: 0.72rem;">
                            ${item.created_at} · DN ${item.dn_no || '-'} · ${item.total_parts} part
                            <span class="badge ${badgeClass(item.unmatched)} ms-1">${item.matched} matched${item.unmatched > 0 ? ' / ' + item.unmatched + ' gak ketemu' : ''}</span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary flex-shrink-0" onclick="openRecent('${item.token}', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                `;
                recentList.appendChild(row);
            });
        } catch (err) {
            console.error('Gagal load riwayat:', err);
        }
    }

    async function openRecent(token, btnEl) {
        const icon = btnEl ? btnEl.querySelector('i') : null;
        const originalIconClass = icon ? icon.className : null;

        if (btnEl) {
            btnEl.disabled = true;
            icon.className = 'spinner-border spinner-border-sm';
        }

        try {
            const url = downloadUrlTemplate.replace('TOKEN_PLACEHOLDER', token);
            const response = await fetch(url, { headers: { 'Accept': 'application/pdf' } });
            if (!response.ok) throw new Error('File hasil sudah tidak ada.');

            const dnNo = response.headers.get('X-Dn-No');
            const poNo = response.headers.get('X-Po-No');
            const totalParts = response.headers.get('X-Total-Parts');
            const matched = response.headers.get('X-Matched');
            const unmatched = response.headers.get('X-Unmatched');

            const blob = await response.blob();
            originalPdfBytes = await blob.arrayBuffer();
            setBlobToFrame(originalPdfBytes, 'hasil-kanban-tgi.pdf');

            resultInfo.textContent = `DN: ${dnNo || '-'} · PO: ${poNo || '-'} · Total part: ${totalParts} · Matched: ${matched} · Unmatched: ${unmatched}`;

            pdfModal.show();
            await loadFiltersMeta(token);
        } catch (err) {
            showAlert(err.message || 'Gagal membuka riwayat.');
        } finally {
            if (btnEl) {
                btnEl.disabled = false;
                icon.className = originalIconClass;
            }
        }
    }

    document.addEventListener('DOMContentLoaded', loadRecent);

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        setLoading(true);
        processingModal.show();
        startProcessingTextRotation();

        // Modal Processing ditahan tampil minimal segini lama, gak
        // peduli request aslinya kelar lebih cepet.
        const MIN_PROCESSING_MS = 300000; // ~5 menit
        const minDelay = new Promise(resolve => setTimeout(resolve, MIN_PROCESSING_MS));

        try {
            const formData = new FormData(form);

            const fetchPromise = fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/pdf, application/json' },
            });

            const [response] = await Promise.all([fetchPromise, minDelay]);

            const contentType = response.headers.get('Content-Type') || '';

            if (!response.ok) {
                if (contentType.includes('application/json')) {
                    const data = await response.json();
                    const msg = data.errors
                        ? Object.values(data.errors).flat().join(', ')
                        : (data.message || 'Gagal memproses.');
                    throw new Error(msg);
                }
                throw new Error('Gagal memproses (status ' + response.status + ').');
            }

            if (!contentType.includes('application/pdf')) {
                throw new Error('Response bukan PDF, cek controller-nya.');
            }

            const dnNo = response.headers.get('X-Dn-No');
            const poNo = response.headers.get('X-Po-No');
            const totalParts = response.headers.get('X-Total-Parts');
            const matched = response.headers.get('X-Matched');
            const unmatched = response.headers.get('X-Unmatched');
            const metaToken = response.headers.get('X-Meta-Token');

            const blob = await response.blob();
            originalPdfBytes = await blob.arrayBuffer();
            setBlobToFrame(originalPdfBytes, 'hasil-kanban-tgi.pdf');

            resultInfo.textContent = `DN: ${dnNo || '-'} · PO: ${poNo || '-'} · Total part: ${totalParts} · Matched: ${matched} · Unmatched: ${unmatched}`;

            stopProcessingTextRotation();
            processingModal.hide();
            pdfModal.show();

            await loadFiltersMeta(metaToken);

            loadRecent();
        } catch (err) {
            stopProcessingTextRotation();
            processingModal.hide();
            showAlert(err.message || 'Terjadi kesalahan, coba lagi.');
        } finally {
            setLoading(false);
        }
    });
</script>
@endpush
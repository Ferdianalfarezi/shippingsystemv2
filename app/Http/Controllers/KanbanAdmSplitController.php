<?php

namespace App\Http\Controllers;

use App\Models\KanbanAdmSplit;
use App\Services\KanbanRackSplitter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser as PdfTextParser;

class KanbanAdmSplitController extends Controller
{
    /**
     * Teks penanda yang WAJIB ada di PDF sebelum boleh diproses.
     * Ini semacam gerbang lisensi per-customer — kalau PDF yang
     * diupload gak ngandung teks ini, dianggep bukan dokumen yang
     * berhak diproses sistem ini.
     */
    protected string $requiredLicenseText = 'PT ADM';

    public function index()
    {
        return view('kanbansplitadms.index');
    }

    public function process(Request $request)
    {
        $sourceFullPath = null;

        try {
            $request->validate([
                'pdf' => 'required|file|mimes:pdf|max:20480',
                'labels_per_page' => 'nullable|integer|min:1|max:10',
            ]);

            $file = $request->file('pdf');
            $labelsPerPage = (int) ($request->input('labels_per_page') ?: 4);
            $originalUploadName = $file->getClientOriginalName();

            $originalName = pathinfo($originalUploadName, PATHINFO_FILENAME);
            $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $originalName);

            $sourceDir = storage_path('app/kanban/source');
            if (!is_dir($sourceDir) && !mkdir($sourceDir, 0777, true) && !is_dir($sourceDir)) {
                throw new \RuntimeException("Gagal bikin folder source: {$sourceDir}");
            }

            $sourceFileName = $safeName . '_' . time() . '.pdf';
            $sourceFullPath = $sourceDir . '/' . $sourceFileName;

            // pake move() langsung, lebih predictable di Windows/Laragon
            $file->move($sourceDir, $sourceFileName);

            // ------------------------------------------------------------
            // CEK LISENSI: PDF wajib ngandung teks $requiredLicenseText.
            // Dicek di sini (SEBELUM proses split jalan) biar gak buang
            // waktu split PDF yang emang bukan buat customer ini, dan
            // biar gak bisa dibypass dari sisi client (JS).
            // ------------------------------------------------------------
            if (!$this->hasRequiredLicenseText($sourceFullPath)) {
                @unlink($sourceFullPath);

                Log::warning('KANBAN SPLIT - LISENSI TIDAK COCOK', [
                    'filename' => $originalUploadName,
                ]);

                return response()->json([
                    'message' => 'PDF ini bukan Kanban ADM : sebenernya bisa, tapi belum bayar lisensi untuk customer ini.',
                ], 422);
            }

            $outputName = $safeName . '_split_' . now()->format('Ymd_His') . '.pdf';
            $outputDir = storage_path('app/kanban/output');
            if (!is_dir($outputDir) && !mkdir($outputDir, 0777, true) && !is_dir($outputDir)) {
                throw new \RuntimeException("Gagal bikin folder output: {$outputDir}");
            }
            $outputFullPath = $outputDir . '/' . $outputName;

            $splitter = new KanbanRackSplitter($labelsPerPage);
            $result = $splitter->split($sourceFullPath, $outputFullPath);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e; // biar tetep balikin 422 format Laravel standar (errors object)
        } catch (\Throwable $e) {
            Log::error('KANBAN SPLIT ERROR', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            if ($sourceFullPath && is_file($sourceFullPath)) {
                @unlink($sourceFullPath);
            }

            return response()->json([
                'message' => 'Gagal memproses PDF: ' . $e->getMessage()
                    . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')',
            ], 500);
        }

        // source udah gak kepake lagi setelah split selesai
        if (is_file($sourceFullPath)) {
            @unlink($sourceFullPath);
        }

        // token dipake buat: nama file histori, filter meta, dan download ulang
        $metaToken = Str::random(24);

        // ------------------------------------------------------------
        // Simpen salinan hasil split ke folder histori (permanen) SEBELUM
        // file output dihapus otomatis lewat deleteFileAfterSend().
        // Riwayat + metadata shop/plant disimpen ke DB (bukan Cache lagi)
        // biar gak expired dan bisa dibuka ulang kapan aja.
        // ------------------------------------------------------------
        $historyDir = storage_path('app/kanban/history');
        if (!is_dir($historyDir) && !mkdir($historyDir, 0777, true) && !is_dir($historyDir)) {
            throw new \RuntimeException("Gagal bikin folder history: {$historyDir}");
        }
        $historyRelativePath = 'kanban/history/' . $metaToken . '.pdf';
        copy($outputFullPath, storage_path('app/' . $historyRelativePath));

        KanbanAdmSplit::create([
            'token' => $metaToken,
            'original_filename' => $originalUploadName,
            'file_path' => $historyRelativePath,
            'total_labels' => $result['total_labels'] ?? 0,
            'matched' => $result['matched'] ?? 0,
            'unmatched' => $result['unmatched'] ?? 0,
            'unmatched_no_text' => $result['unmatched_no_text'] ?? 0,
            'unmatched_no_extract' => $result['unmatched_no_extract'] ?? 0,
            'unmatched_no_master' => $result['unmatched_no_master'] ?? 0,
            'unmatched_no_rack' => $result['unmatched_no_rack'] ?? 0,
            'labels_meta' => $result['labels'] ?? [],
        ]);

        // ------------------------------------------------------------
        // PENTING: 'inline' bukan 'attachment', biar bisa dimuat di
        // <iframe> dalam modal preview, bukan langsung ke-download.
        // File output (folder output, bukan history) dihapus otomatis
        // setelah selesai dikirim ke browser.
        // ------------------------------------------------------------
        return response()->file($outputFullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $outputName . '"',
            'X-Total-Labels' => $result['total_labels'] ?? 0,
            'X-Matched' => $result['matched'] ?? 0,
            'X-Unmatched' => $result['unmatched'] ?? 0,
            'X-Unmatched-No-Text' => $result['unmatched_no_text'] ?? 0,
            'X-Unmatched-No-Extract' => $result['unmatched_no_extract'] ?? 0,
            'X-Unmatched-No-Master' => $result['unmatched_no_master'] ?? 0,
            'X-Unmatched-No-Rack' => $result['unmatched_no_rack'] ?? 0,
            'X-Meta-Token' => $metaToken,
        ])->deleteFileAfterSend(true);
    }

    /**
     * Cek apakah PDF sumber ngandung teks $requiredLicenseText di
     * salah satu halamannya. Scan semua halaman (bukan cuma halaman
     * pertama) soalnya urutan/posisi teks header bisa aja beda-beda.
     * Normalize whitespace + case-insensitive biar gak kejebak variasi
     * spasi ganda atau huruf besar/kecil hasil extract PDF parser.
     */
    protected function hasRequiredLicenseText(string $pdfPath): bool
    {
        try {
            $textParser = new PdfTextParser();
            $document = $textParser->parseFile($pdfPath);

            $fullText = '';
            foreach ($document->getPages() as $page) {
                $fullText .= ' ' . $page->getText();
            }

            $normalized = preg_replace('/\s+/', ' ', $fullText);

            return stripos($normalized, $this->requiredLicenseText) !== false;
        } catch (\Throwable $e) {
            // Gagal parse teks (mis. PDF hasil scan tanpa layer teks) ->
            // anggep gak lolos cek, tapi log biar kebedain sama kasus
            // "emang bukan PT ADM".
            Log::warning('KANBAN SPLIT - GAGAL PARSE TEKS BUAT CEK LISENSI', [
                'file' => $pdfPath,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Metadata per-halaman (shop, matched) hasil split, diambil pake
     * token yang dikirim lewat header X-Meta-Token. Dipake frontend buat
     * ngisi dropdown filter SHOP/Plant tanpa perlu re-upload/re-proses PDF.
     * Sekarang baca dari DB (bukan Cache) jadi gak ada masa expired.
     */
    public function labelsMeta(string $token)
    {
        $split = KanbanAdmSplit::where('token', $token)->first();

        if (!$split) {
            return response()->json([
                'message' => 'Metadata gak ketemu, coba split ulang PDF-nya.',
            ], 404);
        }

        return response()->json(['labels' => $split->labels_meta ?? []]);
    }

    /**
     * GET /kanban-split/recent
     * List riwayat split terakhir, ditampilin di bawah form.
     */
    public function recent()
    {
        $items = KanbanAdmSplit::latest()
            ->take(3)
            ->get([
                'token', 'original_filename', 'total_labels',
                'matched', 'unmatched', 'created_at',
            ])
            ->map(fn ($item) => [
                'token' => $item->token,
                'filename' => $item->original_filename,
                'total' => $item->total_labels,
                'matched' => $item->matched,
                'unmatched' => $item->unmatched,
                'created_at' => $item->created_at->diffForHumans(),
            ]);

        return response()->json(['items' => $items]);
    }

    /**
     * GET /kanban-split/{token}/download
     * Ambil ulang file PDF hasil split by token, buat dibuka lagi
     * (preview/print/download) tanpa upload & proses ulang.
     */
    public function download(string $token)
    {
        $split = KanbanAdmSplit::where('token', $token)->first();

        if (!$split) {
            abort(404, 'Riwayat split tidak ditemukan.');
        }

        $fullPath = storage_path('app/' . $split->file_path);

        if (!is_file($fullPath)) {
            abort(404, 'File hasil split sudah tidak ada di server.');
        }

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $split->original_filename . '"',
            'X-Total-Labels' => $split->total_labels,
            'X-Matched' => $split->matched,
            'X-Unmatched' => $split->unmatched,
            'X-Meta-Token' => $split->token,
        ]);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\KanbanBankTgi;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser as PdfTextParser;

class KanbanBankTgiController extends Controller
{
    public function index(Request $request)
    {
        $query = KanbanBankTgi::query();

        if ($search = $request->input('search')) {
            $query->where('part_no', 'like', "%{$search}%");
        }

        $items = $query->orderBy('part_no')->paginate(50);

        return view('kanbanbanktgi.index', compact('items'));
    }

    /**
     * Upload BANYAK PDF sekaligus ke bank. Part no TIDAK diambil dari
     * nama file — tiap PDF di-parse isinya, baris pertama yang bentuk
     * teksnya nyerupai part no (huruf/angka dipisah dash) dianggap
     * part no-nya. Jumlah kanban juga di-parse dari isi PDF, diambil
     * dari angka yang nempel di beberapa baris setelah header
     * "QTY / KANBAN". Kalau part no udah ada di bank, filenya di-replace
     * (updateOrCreate by part_no) — jadi aman upload ulang buat update.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'pdf' => 'required|array|min:1',
            'pdf.*' => 'file|mimes:pdf|max:20480',
            'labels_per_page' => 'nullable|integer|min:1|max:10',
        ]);

        $labelsPerPage = (int) ($request->input('labels_per_page') ?: 3);

        $bankDir = storage_path('app/kanban-bank/tgi');
        if (!is_dir($bankDir) && !mkdir($bankDir, 0777, true) && !is_dir($bankDir)) {
            throw new \RuntimeException("Gagal bikin folder bank: {$bankDir}");
        }

        $uploaded = 0;
        $failed = [];

        foreach ($request->file('pdf') as $file) {
            $originalUploadName = $file->getClientOriginalName();

            $tmpPath = $file->getRealPath();
            $partNo = $this->extractPartNoFromBankPdf($tmpPath);

            if (!$partNo) {
                $failed[] = [
                    'filename' => $originalUploadName,
                    'reason' => 'Gagal narik part no dari isi PDF',
                ];
                continue;
            }

            $jumlahKbn = $this->extractJumlahKbnFromBankPdf($tmpPath);

            $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $partNo);
            $storedFileName = $safeName . '_' . uniqid() . '.pdf';
            $storedFullPath = $bankDir . '/' . $storedFileName;

            $file->move($bankDir, $storedFileName);

            // kalau part no ini udah ada di bank, hapus file lama (biar
            // gak numpuk sampah) sebelum ganti ke yang baru
            $existing = KanbanBankTgi::where('part_no', $partNo)->first();
            if ($existing) {
                $oldFullPath = storage_path('app/' . $existing->file_path);
                if (is_file($oldFullPath)) {
                    @unlink($oldFullPath);
                }
            }

            KanbanBankTgi::updateOrCreate(
                ['part_no' => $partNo],
                [
                    'original_filename' => $originalUploadName,
                    'file_path' => 'kanban-bank/tgi/' . $storedFileName,
                    'labels_per_page' => $labelsPerPage,
                    'jumlah_kbn' => $jumlahKbn,
                ]
            );

            $uploaded++;
        }

        return response()->json([
            'message' => "{$uploaded} PDF berhasil masuk ke bank." . (count($failed) ? ' ' . count($failed) . ' gagal.' : ''),
            'uploaded' => $uploaded,
            'failed' => $failed,
        ]);
    }

    public function destroy(KanbanBankTgi $kanbanBankTgi)
    {
        $fullPath = storage_path('app/' . $kanbanBankTgi->file_path);
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }

        $kanbanBankTgi->delete();

        return response()->json(['message' => 'Data bank kanban berhasil dihapus.']);
    }

    /**
     * Ambil part no dari ISI PDF (bukan nama file). Scan baris-baris
     * awal, ambil baris pertama yang bentuknya nyerupai part no
     * (mis. "KSCGA440-02860-00" — huruf/angka dipisah dash minimal
     * 2 dash).
     */
    protected function extractPartNoFromBankPdf(string $filePath): ?string
    {
        try {
            $textParser = new PdfTextParser();
            $document = $textParser->parseFile($filePath);
            $pages = $document->getPages();

            if (empty($pages)) {
                return null;
            }

            $text = $pages[0]->getText();
            $lines = preg_split('/\r\n|\r|\n/', trim($text));

            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                if (preg_match('/^[A-Z0-9]+-[A-Z0-9]+-[A-Z0-9]+$/i', $line)) {
                    return strtoupper($line);
                }
            }

            return null;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('KANBAN BANK TGI - GAGAL PARSE PART NO', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Ambil jumlah kanban dari ISI PDF. Cari baris header yang
     * mengandung "QTY" dan "KANBAN" (mis. "QTY / KANBAN"), lalu scan
     * beberapa baris setelahnya buat nemuin angka yang nempel di ekor
     * baris (mis. angka "10" di baris "ELOK PRODUK 10").
     */
    protected function extractJumlahKbnFromBankPdf(string $filePath): ?int
    {
        try {
            $textParser = new PdfTextParser();
            $document = $textParser->parseFile($filePath);
            $pages = $document->getPages();

            if (empty($pages)) {
                return null;
            }

            $text = $pages[0]->getText();
            $lines = array_values(array_filter(
                array_map('trim', preg_split('/\r\n|\r|\n/', trim($text))),
                fn ($l) => $l !== ''
            ));

            $qtyLineIndex = null;
            foreach ($lines as $i => $line) {
                if (stripos($line, 'QTY') !== false && stripos($line, 'KANBAN') !== false) {
                    $qtyLineIndex = $i;
                    break;
                }
            }

            if ($qtyLineIndex === null) {
                return null;
            }

            // Angkanya biasanya nongol 1-2 baris setelah header "QTY / KANBAN",
            // nempel di ekor baris lain (mis. "ELOK PRODUK 10")
            for ($i = $qtyLineIndex + 1; $i < min($qtyLineIndex + 6, count($lines)); $i++) {
                if (preg_match('/(\d+)\s*$/', $lines[$i], $m)) {
                    return (int) $m[1];
                }
            }

            return null;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('KANBAN BANK TGI - GAGAL PARSE JUMLAH KBN', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
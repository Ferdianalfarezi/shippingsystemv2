<?php

namespace App\Services;

use App\Models\AddressTgi;
use App\Models\KanbanBankTgi;
use setasign\Fpdi\Tcpdf\Fpdi;
use Smalot\PdfParser\Parser as PdfTextParser;

class KanbanTgiComposer
{
    /**
     * Part No DIAMBIL DARI BENTUK/SHAPE-nya langsung (KS + huruf +
     * angka + "-" + 5digit + "-" + 2 karakter, mis. "KSCGA440-02850-00").
     * Gak pake \b di AWAL pattern, soalnya nomor urut baris nempel
     * LANGSUNG ke part no tanpa spasi (mis. "1KSCGA440-02850-00").
     */
    protected string $partNoShapePattern = '/KS[A-Z]{1,4}\d{2,4}-\d{4,6}-[A-Z0-9]{2}\b/i';

    protected string $dnNoPattern = '/DN NO\.?\s*:?\s*(\S+)/i';
    protected string $poNoPattern = '/PO NO\.?\s*:?\s*(\S+)/i';

    /**
     * Nangkep 3 angka DI UJUNG baris item Delivery Note: QTY/KANBAN,
     * JML KANBAN, TOTAL QTY ORDER (desimal, mis. "150.00"). Yang kita
     * pake cuma grup ke-2 (JML KANBAN).
     */
    protected string $jmlKanbanLinePattern = '/(\d+)\s+(\d+)\s+[\d.,]*\d\.\d{2}\s*$/';

    protected float $sourceMarginTopMm = 0;
    protected float $sourceMarginBottomMm = 0;

    /**
     * TRIM tinggi slice (mm), ngecilin "jendela" crop biar gak
     * nyerempet ke label sebelum/sesudahnya. Dikurangin SIMETRIS —
     * trimPt/2 dari atas DAN trimPt/2 dari bawah. 18mm = udah pas.
     */
    protected float $labelHeightTrimMm = 18;

    /**
     * QR CODE — kotak "BOX CODE" (kanan atas, sebelah barcode tengah).
     */
    protected float $qrXMm = 175;
    protected float $qrYMm = 22;
    protected float $qrSizeMm = 20;

    /**
     * RACK ADDRESS — kotak kosong di bawah "SUPPLIER NO".
     */
    protected float $addressXMm = 10;
    protected float $addressYMm = 69;
    protected float $addressWidthMm = 30;
    protected float $addressHeightMm = 12;
    protected float $addressFontSize = 11;

    protected const MM_TO_PT = 72 / 25.4;

    /**
     * @return array{
     *     output: ?string,
     *     dn_no: ?string,
     *     po_no: ?string,
     *     total_parts: int,
     *     matched: int,
     *     unmatched: int,
     *     items: array<int, array<string, mixed>>
     * }
     */
    public function compose(string $deliveryNotePath, string $outputPath): array
    {
        $dnData = $this->extractDeliveryNoteData($deliveryNotePath);

        $pdf = new Fpdi('P', 'pt');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);

        $unmatched = 0;
        $unmatchedItems = [];
        $records = []; // part yang KETEMU di bank -> disort dulu sebelum di-render

        // ============================================================
        // PASS 1 — resolve tiap part no: cari di bank, import halaman
        // 1 PDF-nya (templateId disimpen buat dipake di PASS 2), cari
        // rack address-nya. Belum ada AddPage/render sama sekali di
        // sini.
        // ============================================================
        foreach ($dnData['part_nos'] as $partNo) {
            $bank = KanbanBankTgi::where('part_no', $partNo)->first();

            if (!$bank) {
                $unmatched++;
                $unmatchedItems[] = [
                    'part_no' => $partNo,
                    'matched' => false,
                    'reason' => 'no_bank_pdf',
                ];
                continue;
            }

            $bankPath = storage_path('app/' . $bank->file_path);
            if (!is_file($bankPath)) {
                $unmatched++;
                $unmatchedItems[] = [
                    'part_no' => $partNo,
                    'matched' => false,
                    'reason' => 'bank_file_missing',
                ];
                continue;
            }

            $resolvedPath = $this->ensureReadable($bankPath);
            $labelsPerPage = max(1, (int) $bank->labels_per_page);

            $jmlKanbanDn = $dnData['jml_kanban_by_part_no'][$partNo] ?? null;
            $copies = max(1, (int) ($jmlKanbanDn ?: ($bank->jumlah_kbn ?: 1)));

            $addressTgi = $this->matchAddress($partNo);
            $rackNo = $addressTgi?->rack_no;

            $pdf->setSourceFile($resolvedPath);
            $templateId = $pdf->importPage(1);
            $size = $pdf->getTemplateSize($templateId);

            $fullWidth  = $size['width'];
            $fullHeight = $size['height'];

            $marginTopPt    = $this->sourceMarginTopMm * self::MM_TO_PT;
            $marginBottomPt = $this->sourceMarginBottomMm * self::MM_TO_PT;
            $trimPt         = $this->labelHeightTrimMm * self::MM_TO_PT;

            $rawLabelHeight = ($fullHeight - $marginTopPt - $marginBottomPt) / $labelsPerPage;
            $labelHeight = $rawLabelHeight - $trimPt;

            $topOffset = $marginTopPt;

            $middleIndex = intdiv($labelsPerPage - 1, 2);

            $orientation = $fullWidth >= $labelHeight ? 'L' : 'P';

            $yOffset = -($topOffset + $middleIndex * $rawLabelHeight);
            $yOffset -= ($trimPt / 2);

            if ($resolvedPath !== $bankPath) {
                @unlink($resolvedPath);
            }

            $records[] = [
                'part_no' => $partNo,
                'bank_filename' => $bank->original_filename,
                'labels_per_page' => $labelsPerPage,
                'jumlah_kbn' => $bank->jumlah_kbn,
                'jml_kanban_dn' => $jmlKanbanDn,
                'copies' => $copies,
                'rack_no' => $rackNo,
                'address_part_no' => $this->deriveAddressLookupPartNo($partNo),
                'templateId' => $templateId,
                'fullWidth' => $fullWidth,
                'fullHeight' => $fullHeight,
                'labelHeight' => $labelHeight,
                'yOffset' => $yOffset,
                'orientation' => $orientation,
            ];
        }

        // Sort DESCENDING berdasarkan rack_no, natural sort (huruf
        // dibandingin dulu, angka dibandingin sebagai NILAI bukan
        // per-karakter -> "B3-19-A" > "B3-10-A" > "A3-10-D" kebaca
        // bener). Part yang rack_no-nya gak ketemu (null) ditaro di
        // AKHIR, urutan asli antar-mereka dipertahanin (stable).
        usort($records, function ($a, $b) {
            $rackA = $a['rack_no'];
            $rackB = $b['rack_no'];

            if ($rackA === null && $rackB === null) {
                return 0;
            }
            if ($rackA === null) {
                return 1;
            }
            if ($rackB === null) {
                return -1;
            }

            return strnatcasecmp($rackB, $rackA);
        });

        // ============================================================
        // PASS 2 — render sesuai urutan hasil sort di atas, pake
        // templateId yang udah di-import di PASS 1.
        // ============================================================
        $items = [];
        $pageCounter = 0;
        $anyPageAdded = false;

        foreach ($records as $rec) {
            $startPage = $pageCounter + 1;

            for ($c = 0; $c < $rec['copies']; $c++) {
                $pdf->AddPage($rec['orientation'], [$rec['fullWidth'], $rec['labelHeight']]);
                $pdf->useTemplate($rec['templateId'], 0, $rec['yOffset'], $rec['fullWidth'], $rec['fullHeight']);

                // $this->drawQrCode($pdf, $rec['part_no']);

                if ($rec['rack_no']) {
                    $this->drawAddress($pdf, $rec['rack_no']);
                }

                $pageCounter++;
                $anyPageAdded = true;
            }

            $items[] = [
                'part_no' => $rec['part_no'],
                'matched' => true,
                'bank_filename' => $rec['bank_filename'],
                'labels_per_page' => $rec['labels_per_page'],
                'jumlah_kbn' => $rec['jumlah_kbn'],
                'jml_kanban_dn' => $rec['jml_kanban_dn'],
                'copies' => $rec['copies'],
                'pages' => range($startPage, $pageCounter),
                'address_part_no' => $rec['address_part_no'],
                'rack_no' => $rec['rack_no'],
                'address_matched' => (bool) $rec['rack_no'],
            ];
        }

        // unmatched (gak ketemu di bank) ditaro di belakang -- gak
        // ngaruh ke urutan PDF (emang gak dirender), cuma buat laporan
        // di frontend.
        $items = array_merge($items, $unmatchedItems);
        $matched = count($records);

        if ($anyPageAdded) {
            $pdf->Output($outputPath, 'F');
        }

        \Illuminate\Support\Facades\Log::info('KANBAN TGI COMPOSE DONE', [
            'output' => $outputPath,
            'dn_no' => $dnData['dn_no'],
            'po_no' => $dnData['po_no'],
            'total_parts' => count($dnData['part_nos']),
            'matched' => $matched,
            'unmatched' => $unmatched,
        ]);

        if ($unmatched > 0) {
            \Illuminate\Support\Facades\Log::info('KANBAN TGI UNMATCHED - PART NO GAK ADA DI BANK', [
                'part_no_list' => array_values(array_column($unmatchedItems, 'part_no')),
            ]);
        }

        $noAddressPartNos = array_values(array_column(
            array_filter($items, fn ($it) => ($it['matched'] ?? false) && !($it['address_matched'] ?? false)),
            'part_no'
        ));
        if (!empty($noAddressPartNos)) {
            \Illuminate\Support\Facades\Log::info('KANBAN TGI - RACK ADDRESS GAK KETEMU DI ADDRESSTGI', [
                'part_no_list' => $noAddressPartNos,
            ]);
        }

        return [
            'output' => $anyPageAdded ? $outputPath : null,
            'dn_no' => $dnData['dn_no'],
            'po_no' => $dnData['po_no'],
            'total_parts' => count($dnData['part_nos']),
            'matched' => $matched,
            'unmatched' => $unmatched,
            'items' => $items,
        ];
    }

    /**
     * Transform part no lengkap (mis. "KSCGA440-02850-00") jadi versi
     * "stripped" yang dipake master addresstgi (mis. "GA440-02850") —
     * strip "KS" + 1 huruf kategori di depan segmen pertama, buang
     * segmen ke-3 (suffix varian) sepenuhnya.
     */
    protected function deriveAddressLookupPartNo(string $fullPartNo): string
    {
        $segments = explode('-', $fullPartNo);

        if (count($segments) < 2) {
            return strtoupper($fullPartNo);
        }

        $strippedFirstSegment = preg_replace('/^KS[A-Z]/i', '', $segments[0]);

        return strtoupper($strippedFirstSegment . '-' . $segments[1]);
    }

    /**
     * Cocokin ke master addresstgi. Coba versi "stripped" dulu (yang
     * emang format standarnya), fallback ke part no lengkap apa
     * adanya kalau ternyata ada entry yang disimpen gak di-strip.
     */
    protected function matchAddress(string $fullPartNo): ?AddressTgi
    {
        $derived = $this->deriveAddressLookupPartNo($fullPartNo);

        $address = AddressTgi::where('part_no', $derived)->first();
        if ($address) {
            return $address;
        }

        return AddressTgi::where('part_no', strtoupper(trim($fullPartNo)))->first();
    }

    /**
     * Tulis rack_no di kotak kosong bawah "SUPPLIER NO".
     */
    protected function drawAddress(Fpdi $pdf, string $rackNo): void
    {
        $x = $this->addressXMm * self::MM_TO_PT;
        $y = $this->addressYMm * self::MM_TO_PT;
        $w = $this->addressWidthMm * self::MM_TO_PT;
        $h = $this->addressHeightMm * self::MM_TO_PT;

        $pdf->SetFont('helvetica', 'B', $this->addressFontSize);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY($x, $y);
        $pdf->Cell($w, $h, $rackNo, 0, 0, 'C');
    }

    /**
     * Gambar QR code isinya part no, di kotak "BOX CODE" (kosong).
     */
    protected function drawQrCode(Fpdi $pdf, string $content): void
    {
        $x = $this->qrXMm * self::MM_TO_PT;
        $y = $this->qrYMm * self::MM_TO_PT;
        $size = $this->qrSizeMm * self::MM_TO_PT;

        $style = [
            'border' => false,
            'padding' => 0,
            'fgcolor' => [0, 0, 0],
            'bgcolor' => false,
        ];

        $pdf->write2DBarcode($content, 'QRCODE,H', $x, $y, $size, $size, $style, 'N');
    }

    protected function extractDeliveryNoteData(string $filePath): array
    {
        $textParser = new PdfTextParser();
        $document = $textParser->parseFile($filePath);

        $fullText = '';
        foreach ($document->getPages() as $page) {
            $fullText .= "\n" . $page->getText();
        }

        $dnNo = null;
        $poNo = null;

        if (preg_match($this->dnNoPattern, $fullText, $m)) {
            $dnNo = trim($m[1]);
        }
        if (preg_match($this->poNoPattern, $fullText, $m)) {
            $poNo = trim($m[1]);
        }

        $lines = preg_split('/\r\n|\r|\n/', $fullText);

        $partNos = [];
        $jmlKanbanByPartNo = [];

        foreach ($lines as $line) {
            if (!preg_match($this->partNoShapePattern, $line, $pm)) {
                continue;
            }

            $partNo = strtoupper(trim($pm[0]));

            if (!in_array($partNo, $partNos, true)) {
                $partNos[] = $partNo;
            }

            if (preg_match($this->jmlKanbanLinePattern, $line, $nm)) {
                $jmlKanbanByPartNo[$partNo] = (int) $nm[2];
            } else {
                \Illuminate\Support\Facades\Log::warning('KANBAN TGI - JML KANBAN GAK KETEMU DI BARIS PART NO INI', [
                    'part_no' => $partNo,
                    'line' => $line,
                ]);
            }
        }

        return [
            'dn_no' => $dnNo,
            'po_no' => $poNo,
            'part_nos' => $partNos,
            'jml_kanban_by_part_no' => $jmlKanbanByPartNo,
        ];
    }

    protected function ensureReadable(string $sourcePath): string
    {
        try {
            $probe = new Fpdi();
            $probe->setSourceFile($sourcePath);
            return $sourcePath;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('KANBAN TGI BANK PDF PERLU NORMALIZE', [
                'source' => $sourcePath,
                'error' => $e->getMessage(),
            ]);

            return app(PdfNormalizer::class)->normalize($sourcePath);
        }
    }
}
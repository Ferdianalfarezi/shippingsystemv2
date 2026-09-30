<?php

namespace App\Services;

use App\Models\AddressHino;
use setasign\Fpdi\Tcpdf\Fpdi;
use Smalot\PdfParser\Parser as PdfTextParser;

class KanbanHinoRackSplitter
{
    /** Jumlah kanban per halaman sumber. Customer Hino -> 4 per halaman (kayak NTC). */
    protected int $labelsPerPage;

    /**
     * Part No diambil dari SHAPE-nya.
     * Segmen 1: 5 karakter huruf/angka, WAJIB ada minimal 1 angka
     *           (mis. "17872" atau "S6911").
     * Group 1 = BASE part no (2 segmen, mis. "S6911-E0020") -> dipake matching.
     * Suffix "-XX" (mis. "-00") opsional & dihiraukan.
     */
    protected string $partNoPattern = '/\b((?=[A-Z0-9]{0,4}\d)[A-Z0-9]{5}\s*-\s*[A-Z0-9]{4,6})(?:\s*-\s*[A-Z0-9]{2})?/i';

    /** "DELIVERY NOTE" — konstan per file. Cuma metadata. */
    protected string $deliveryNotePattern = '/DELIVERY NOTE.*?(\d{8,12})/is';

    /** Set true buat log koordinat Y teks halaman 1 (buat nyetel pembagian label). */
    protected bool $debugCoordinates = false;

    /**
     * Urutkan output berdasarkan rack_no, abjad terbesar dulu (F3 > F2 > E3).
     * Sorting-nya PER PDF — urutan antar file tetap sesuai urutan upload.
     */
    protected bool $sortByRackDesc = true;

    // ============================================================
    // OVERLAY RACK NO
    // ============================================================
    protected array $overlayXFractionByIndex = [
        0 => 0.52,
        1 => 0.52,
        2 => 0.52,
        3 => 0.52,
    ];
    protected array $overlayYFractionByIndex = [
        0 => 0.82,
        1 => 0.82,
        2 => 0.82,
        3 => 0.82,
    ];
    protected array $overlayWidthFractionByIndex = [
        0 => 0.26,
        1 => 0.26,
        2 => 0.26,
        3 => 0.26,
    ];
    protected array $overlayHeightFractionByIndex = [
        0 => 0.14,
        1 => 0.14,
        2 => 0.14,
        3 => 0.14,
    ];
    protected float $overlayFontSize = 11;
    protected string $overlayFont = 'helvetica';
    protected string $overlayAlign = 'L';
    protected string $overlayLabel = 'STP RACK : ';
    protected bool $overlayDrawBackground = false;
    protected string $overlayBgColor = 'FFFFFF';

    protected float $sourceMarginTopMm = 5;
    protected float $sourceMarginBottomMm = 5;

    /** Margin tambahan (mm) di ATAS tiap crop, PER INDEX label dalam 1 halaman. */
    protected array $topExtraMarginMmByIndex = [
        0 => 3,
        1 => 2,
        2 => 1,
        3 => 0,
    ];

    protected float $labelHeightTrimMm = 0;

    protected ?float $customLabelHeight = null;
    protected ?float $customTopOffset = null;

    protected bool $forceOutputSize = false;
    protected float $outputPageWidthMm = 100;
    protected float $outputPageHeightMm = 50;

    protected const MM_TO_PT = 72 / 25.4;

    public function __construct(int $labelsPerPage = 4)
    {
        $this->labelsPerPage = $labelsPerPage;
    }

    /**
     * @param array<int, array{path: string, original_filename: string}> $sources
     */
    public function splitMultiple(array $sources, string $outputPath): array
    {
        $pdf = new Fpdi('P', 'pt');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);

        $matched = 0;
        $unmatchedNoText = 0;
        $unmatchedNoExtract = 0;
        $unmatchedNoMaster = 0;
        $unmatchedNoRack = 0;
        $unmatchedDetails = [];
        $entries = [];           // semua label, dikumpulin dulu sebelum dicetak
        $tempFilesToDelete = []; // file hasil normalize, dihapus SETELAH Output()
        $sequence = 0;           // urutan asli, buat tie-breaker sorting
        $sourceIndex = 0;        // file ke berapa (urutan upload)

        // ============================================================
        // TAHAP 1: KUMPULIN SEMUA LABEL (belum dicetak)
        // ============================================================
        foreach ($sources as $source) {
            $originalSourcePath = $source['path'];
            $originalFilename = $source['original_filename'] ?? basename($originalSourcePath);

            $resolvedSourcePath = $this->ensureReadable($originalSourcePath);
            if ($resolvedSourcePath !== $originalSourcePath) {
                $tempFilesToDelete[] = $resolvedSourcePath;
            }

            $sourcePageCount = $pdf->setSourceFile($resolvedSourcePath);
            $parsedPages = $this->parsePages($resolvedSourcePath);

            for ($pageNo = 1; $pageNo <= $sourcePageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);

                $fullWidth  = $size['width'];
                $fullHeight = $size['height'];

                $marginTopPt    = $this->sourceMarginTopMm * self::MM_TO_PT;
                $marginBottomPt = $this->sourceMarginBottomMm * self::MM_TO_PT;
                $trimPt         = $this->labelHeightTrimMm * self::MM_TO_PT;

                $rawLabelHeight = $this->customLabelHeight
                    ?? (($fullHeight - $marginTopPt - $marginBottomPt) / $this->labelsPerPage);
                $labelHeight = $rawLabelHeight - $trimPt;

                $topOffset = $this->customTopOffset ?? $marginTopPt;

                if ($this->forceOutputSize) {
                    $outputWidthPt  = $this->outputPageWidthMm * self::MM_TO_PT;
                    $outputHeightPt = $this->outputPageHeightMm * self::MM_TO_PT;
                    $scaleX = $outputWidthPt / $fullWidth;
                    $scaleY = $outputHeightPt / $labelHeight;
                } else {
                    $outputWidthPt  = $fullWidth;
                    $outputHeightPt = $labelHeight;
                    $scaleX = 1.0;
                    $scaleY = 1.0;
                }

                $chunks = isset($parsedPages[$pageNo - 1])
                    ? $this->extractLabelTextsByPosition(
                        $parsedPages[$pageNo - 1],
                        $fullHeight,
                        $topOffset,
                        $rawLabelHeight,
                        $pageNo
                    )
                    : [];

                for ($i = 0; $i < $this->labelsPerPage; $i++) {
                    $labelText = $chunks[$i] ?? '';
                    $rackNo = null;
                    $partNo = null;
                    $partNoRaw = null;
                    $partNoMatched = null;
                    $deliveryNote = null;

                    if (trim($labelText) === '') {
                        $unmatchedNoText++;
                        $unmatchedDetails[] = [
                            'source' => $originalFilename, 'page' => $pageNo, 'label_index' => $i,
                            'part_no' => null, 'reason' => 'no_text',
                        ];
                    } else {
                        if (preg_match($this->deliveryNotePattern, $labelText, $dn)) {
                            $deliveryNote = trim($dn[1]);
                        }

                        if (preg_match($this->partNoPattern, $labelText, $m)) {
                            $partNoRaw = strtoupper(preg_replace('/\s+/', '', $m[0]));
                            $partNo = strtoupper(preg_replace('/\s+/', '', $m[1]));
                        }

                        if (!$partNo) {
                            $unmatchedNoExtract++;
                            $unmatchedDetails[] = [
                                'source' => $originalFilename, 'page' => $pageNo, 'label_index' => $i,
                                'part_no' => null, 'reason' => 'no_extract',
                                'text_preview' => mb_substr(trim(preg_replace('/\s+/', ' ', $labelText)), 0, 500),
                            ];
                        } else {
                            $addressHino = $this->matchAddress($partNo);

                            if (!$addressHino) {
                                $unmatchedNoMaster++;
                                $unmatchedDetails[] = [
                                    'source' => $originalFilename, 'page' => $pageNo, 'label_index' => $i,
                                    'part_no' => $partNo, 'part_no_raw' => $partNoRaw, 'reason' => 'no_master',
                                ];
                            } elseif (!$addressHino->rack_no) {
                                $unmatchedNoRack++;
                                $unmatchedDetails[] = [
                                    'source' => $originalFilename, 'page' => $pageNo, 'label_index' => $i,
                                    'part_no' => $partNo, 'part_no_raw' => $partNoRaw, 'reason' => 'no_rack',
                                ];
                            } else {
                                $rackNo = trim($addressHino->rack_no);
                                $partNoMatched = $addressHino->part_no;
                                $matched++;
                            }
                        }
                    }

                    $yOffset = -(($topOffset + $i * $rawLabelHeight) * $scaleY);
                    $yOffset += ($this->topExtraMarginMmByIndex[$i] ?? 0) * self::MM_TO_PT;

                    $entries[] = [
                        'source_index' => $sourceIndex,
                        'sequence' => $sequence++,
                        'template_id' => $templateId,
                        'label_index' => $i,
                        'output_width' => $outputWidthPt,
                        'output_height' => $outputHeightPt,
                        'template_width' => $fullWidth * $scaleX,
                        'template_height' => $fullHeight * $scaleY,
                        'y_offset' => $yOffset,
                        'source_filename' => $originalFilename,
                        'source_page' => $pageNo,
                        'delivery_note' => $deliveryNote,
                        'rack_no' => $rackNo,
                        'part_no' => $partNoMatched,
                        'part_no_raw' => $partNoRaw,
                    ];
                }
            }

            $sourceIndex++;
        }

        // ============================================================
        // TAHAP 2: URUTKAN PER PDF
        // 1. Urutan file tetap sesuai upload (source_index)
        // 2. Di dalam 1 file: rack_no abjad terbesar dulu (F3 > F2 > E3)
        // 3. Label tanpa rack ditaro di akhir file itu (urutan asli)
        // ============================================================
        if ($this->sortByRackDesc) {
            usort($entries, function ($a, $b) {
                if ($a['source_index'] !== $b['source_index']) {
                    return $a['source_index'] <=> $b['source_index'];
                }

                $aHas = $a['rack_no'] !== null && $a['rack_no'] !== '';
                $bHas = $b['rack_no'] !== null && $b['rack_no'] !== '';

                if ($aHas && !$bHas) return -1;
                if (!$aHas && $bHas) return 1;

                if ($aHas && $bHas) {
                    $cmp = strnatcasecmp($b['rack_no'], $a['rack_no']); // descending
                    if ($cmp !== 0) return $cmp;
                }

                return $a['sequence'] <=> $b['sequence'];
            });
        }

        // ============================================================
        // TAHAP 3: CETAK sesuai urutan
        // ============================================================
        $labels = [];
        $outputIndex = 0;

        foreach ($entries as $entry) {
            $orientation = $entry['output_width'] >= $entry['output_height'] ? 'L' : 'P';

            $pdf->AddPage($orientation, [$entry['output_width'], $entry['output_height']]);
            $pdf->useTemplate(
                $entry['template_id'],
                0,
                $entry['y_offset'],
                $entry['template_width'],
                $entry['template_height']
            );

            if ($entry['rack_no']) {
                $this->drawOverlay(
                    $pdf,
                    $entry['rack_no'],
                    $entry['output_width'],
                    $entry['output_height'],
                    $entry['label_index']
                );
            }

            $outputIndex++;

            $labels[] = [
                'output_page' => $outputIndex,
                'source_filename' => $entry['source_filename'],
                'source_page' => $entry['source_page'],
                'delivery_note' => $entry['delivery_note'],
                'rack_no' => $entry['rack_no'],
                'part_no' => $entry['part_no'],
                'part_no_raw' => $entry['part_no_raw'],
                'plant' => $entry['rack_no'] === null ? null : (stripos($entry['rack_no'], 'K') === 0 ? 'Plant 2' : 'Plant 1'),
                'matched' => (bool) $entry['rack_no'],
            ];
        }

        $pdf->Output($outputPath, 'F');

        // Baru hapus file normalize setelah PDF selesai ditulis
        foreach ($tempFilesToDelete as $tmp) {
            @unlink($tmp);
        }

        $unmatched = $unmatchedNoText + $unmatchedNoExtract + $unmatchedNoMaster + $unmatchedNoRack;

        $noMasterPartNos = array_values(array_unique(array_column(
            array_filter($unmatchedDetails, fn ($d) => $d['reason'] === 'no_master'),
            'part_no'
        )));
        $noRackPartNos = array_values(array_unique(array_column(
            array_filter($unmatchedDetails, fn ($d) => $d['reason'] === 'no_rack'),
            'part_no'
        )));
        $noExtractPreviews = array_values(array_map(
            fn ($d) => "{$d['source']} page {$d['page']} idx {$d['label_index']}: {$d['text_preview']}",
            array_filter($unmatchedDetails, fn ($d) => $d['reason'] === 'no_extract')
        ));

        \Illuminate\Support\Facades\Log::info('KANBAN HINO SPLIT DONE', [
            'output' => $outputPath,
            'file_count' => count($sources),
            'total_labels' => $outputIndex,
            'matched' => $matched,
            'unmatched' => $unmatched,
            'unmatched_no_text' => $unmatchedNoText,
            'unmatched_no_extract' => $unmatchedNoExtract,
            'unmatched_no_master' => $unmatchedNoMaster,
            'unmatched_no_rack' => $unmatchedNoRack,
        ]);

        if (!empty($noExtractPreviews)) {
            \Illuminate\Support\Facades\Log::info('KANBAN HINO UNMATCHED - PART NO GAGAL DI-EXTRACT DARI TEKS', [
                'count' => count($noExtractPreviews),
                'previews' => $noExtractPreviews,
            ]);
        }

        if (!empty($noMasterPartNos)) {
            \Illuminate\Support\Facades\Log::info('KANBAN HINO UNMATCHED - PART NO GAK ADA DI MASTER', [
                'count' => count($noMasterPartNos),
                'part_no_list' => $noMasterPartNos,
            ]);
        }

        if (!empty($noRackPartNos)) {
            \Illuminate\Support\Facades\Log::info('KANBAN HINO UNMATCHED - RACK NO KOSONG DI MASTER', [
                'count' => count($noRackPartNos),
                'part_no_list' => $noRackPartNos,
            ]);
        }

        return [
            'output' => $outputPath,
            'total_labels' => $outputIndex,
            'matched' => $matched,
            'unmatched' => $unmatched,
            'unmatched_no_text' => $unmatchedNoText,
            'unmatched_no_extract' => $unmatchedNoExtract,
            'unmatched_no_master' => $unmatchedNoMaster,
            'unmatched_no_rack' => $unmatchedNoRack,
            'unmatched_details' => $unmatchedDetails,
            'labels' => $labels,
        ];
    }

    /**
     * Cocokin BASE part no (2 segmen, mis. "S6911-E0020") ke master.
     * 3 digit terakhir di PDF (mis. "-00") udah dibuang pas extract.
     */
    protected function matchAddress(string $basePartNo): ?AddressHino
    {
        $basePartNo = strtoupper(trim($basePartNo));

        $address = AddressHino::whereRaw('UPPER(TRIM(part_no)) = ?', [$basePartNo])->first();
        if ($address) {
            return $address;
        }

        return AddressHino::whereRaw('UPPER(TRIM(part_no)) LIKE ?', [$basePartNo . '-%'])->first();
    }

    protected function ensureReadable(string $sourcePath): string
    {
        try {
            $probe = new Fpdi();
            $probe->setSourceFile($sourcePath);
            return $sourcePath;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('KANBAN HINO PDF PERLU NORMALIZE', [
                'source' => $sourcePath,
                'error' => $e->getMessage(),
            ]);

            return app(PdfNormalizer::class)->normalize($sourcePath);
        }
    }

    protected function drawOverlay(Fpdi $pdf, string $rackNo, float $labelWidthPt, float $labelHeightPt, int $labelIndex): void
    {
        $xFraction = $this->overlayXFractionByIndex[$labelIndex] ?? 0.49;
        $yFraction = $this->overlayYFractionByIndex[$labelIndex] ?? 0.72;
        $wFraction = $this->overlayWidthFractionByIndex[$labelIndex] ?? 0.26;
        $hFraction = $this->overlayHeightFractionByIndex[$labelIndex] ?? 0.14;

        $x = $xFraction * $labelWidthPt;
        $y = $yFraction * $labelHeightPt;
        $w = $wFraction * $labelWidthPt;
        $h = $hFraction * $labelHeightPt;

        if ($this->overlayDrawBackground) {
            $pdf->SetFillColor(
                hexdec(substr($this->overlayBgColor, 0, 2)),
                hexdec(substr($this->overlayBgColor, 2, 2)),
                hexdec(substr($this->overlayBgColor, 4, 2))
            );
            $pdf->Rect($x, $y, $w, $h, 'F');
        }

        $pdf->SetFont($this->overlayFont, 'B', $this->overlayFontSize);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY($x, $y);
        $pdf->Cell($w, $h, $this->overlayLabel . $rackNo, 0, 0, $this->overlayAlign);
    }

    /** Parse PDF sekali, balikin array Page smalot (index 0-based). */
    protected function parsePages(string $sourcePath): array
    {
        try {
            $textParser = new PdfTextParser();
            $document = $textParser->parseFile($sourcePath);
            return array_values($document->getPages());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('KANBAN HINO GAGAL PARSE TEKS', [
                'source' => $sourcePath,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Bagi teks 1 halaman ke tiap label berdasarkan POSISI Y (bukan urutan teks).
     * Koordinat PDF: origin kiri-bawah, jadi jarak dari atas = tinggi halaman - y.
     *
     * @return array<int, string>
     */
    protected function extractLabelTextsByPosition(
        $page,
        float $pageHeightPt,
        float $topOffsetPt,
        float $labelHeightPt,
        int $pageNo
    ): array {
        $buckets = array_fill(0, $this->labelsPerPage, '');

        try {
            $items = $page->getDataTm();
        } catch (\Throwable $e) {
            return $buckets;
        }

        $debugRows = [];

        foreach ($items as $item) {
            $text = $item[1] ?? '';
            if (trim($text) === '') {
                continue;
            }

            $y = (float) ($item[0][5] ?? 0);
            $fromTop = $pageHeightPt - $y;

            $idx = (int) floor(($fromTop - $topOffsetPt) / $labelHeightPt);
            $idx = max(0, min($this->labelsPerPage - 1, $idx));

            $buckets[$idx] .= ' ' . $text;

            if ($this->debugCoordinates && $pageNo === 1) {
                $debugRows[] = sprintf('y=%.1f fromTop=%.1f idx=%d text=%s', $y, $fromTop, $idx, trim($text));
            }
        }

        if ($this->debugCoordinates && $pageNo === 1) {
            \Illuminate\Support\Facades\Log::info('KANBAN HINO DEBUG KOORDINAT PAGE 1', [
                'page_height' => $pageHeightPt,
                'label_height' => $labelHeightPt,
                'rows' => $debugRows,
            ]);
        }

        return $buckets;
    }
}
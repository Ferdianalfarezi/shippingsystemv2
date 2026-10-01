<?php

namespace App\Services;

use App\Models\AddressFutaba;
use setasign\Fpdi\Tcpdf\Fpdi;
use Smalot\PdfParser\Parser as PdfTextParser;

class KanbanFutabaRackSplitter
{
    /**
     * Jumlah kanban per halaman sumber. Customer Futaba BEDA dari
     * ADM/NTC/FJI yang defaultnya 4 -> ini 3 per halaman.
     */
    protected int $labelsPerPage;

    /**
     * Teks penanda awal tiap label kanban Futaba. Urutan hasil
     * extract-nya rapi: tiap field header langsung diikuti value-nya
     * di baris berikutnya, dan "SUPPLIER" muncul sekali persis di
     * awal tiap label.
     */
    protected string $labelAnchorText = 'SUPPLIER';

    /**
     * Part No langsung nempel abis "PART No.".
     */
    protected string $partNoPattern = '/PART No\.\s*\r?\n?\s*(\S+)/i';

    /**
     * "STORE ADDRESS" = kode lokasi/gudang customer Futaba sendiri
     * (mis. "560B", "650 AH-13", "D03B") — BUKAN rack_no milik STEP,
     * cuma metadata buat search/filter.
     */
    protected string $storeAddressPattern = '/STORE ADDRESS\s*\r?\n?\s*([^\r\n]+)/i';

    /**
     * Urutan output: true = dikelompokin PER PDF (urutan upload), lalu
     * di dalam tiap PDF diurutkan berdasarkan rack_no DESCENDING
     * (Z -> K -> A, natural sort). Label tanpa rack_no ditaruh paling
     * belakang di kelompok PDF-nya. false = urutan asli file/halaman.
     */
    protected bool $sortByRackDesc = true;

    // ============================================================
    // OVERLAY RACK NO — ditaro di kotak "Supplier Free Area 1" (kiri
    // bawah, di bawah PDS No./SERIAL) yang emang kosong. Diukur dari
    // screenshot yang ditandai user.
    // ============================================================
    protected array $overlayXFractionByIndex = [
        0 => 0.02,
        1 => 0.02,
        2 => 0.02,
    ];
    protected array $overlayYFractionByIndex = [
        0 => 0.60,
        1 => 0.60,
        2 => 0.60,
    ];
    protected array $overlayWidthFractionByIndex = [
        0 => 0.22,
        1 => 0.22,
        2 => 0.22,
    ];
    protected array $overlayHeightFractionByIndex = [
        0 => 0.15,
        1 => 0.15,
        2 => 0.15,
    ];
    protected float $overlayFontSize = 16;
    protected string $overlayFont = 'helvetica';
    protected string $overlayAlign = 'C';

    protected float $sourceMarginTopMm = 5;
    protected float $sourceMarginBottomMm = 5;

    /**
     * Margin tambahan (mm) di ATAS tiap crop, PER INDEX label dalam
     * 1 halaman (0 = kanban paling atas, 2 = paling bawah — cuma 3
     * slot karena labelsPerPage = 3).
     */
    protected array $topExtraMarginMmByIndex = [
        0 => 2,
        1 => 0,
        2 => -3,
    ];

    /**
     * TRIM tinggi tiap slice label (mm), buat ngatasin drift/sisa
     * ruang kosong kalau ternyata kejadian.
     */
    protected float $labelHeightTrimMm = 0;

    protected ?float $customLabelHeight = null;
    protected ?float $customTopOffset = null;

    protected bool $forceOutputSize = false;
    protected float $outputPageWidthMm = 100;
    protected float $outputPageHeightMm = 60;

    protected const MM_TO_PT = 72 / 25.4;

    public function __construct(int $labelsPerPage = 3)
    {
        $this->labelsPerPage = $labelsPerPage;
    }

    /**
     * @param array<int, array{path: string, original_filename: string}> $sources
     * @return array{
     *     output: string,
     *     total_labels: int,
     *     matched: int,
     *     unmatched: int,
     *     unmatched_no_text: int,
     *     unmatched_no_extract: int,
     *     unmatched_no_master: int,
     *     unmatched_no_rack: int,
     *     unmatched_details: array<int, array<string, mixed>>,
     *     labels: array<int, array<string, mixed>>
     * }
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
        $tempFiles = [];

        /**
         * Tahap 1: kumpulin semua label (import template + extract data)
         * tanpa render. Render dilakukan setelah diurutkan.
         */
        $entries = [];
        $seq = 0;
        $sourceSeq = 0;

        foreach ($sources as $source) {
            $originalSourcePath = $source['path'];
            $originalFilename = $source['original_filename'] ?? basename($originalSourcePath);

            $resolvedSourcePath = $this->ensureReadable($originalSourcePath);
            if ($resolvedSourcePath !== $originalSourcePath) {
                $tempFiles[] = $resolvedSourcePath;
            }

            $sourcePageCount = $pdf->setSourceFile($resolvedSourcePath);
            $labelTextsPerPage = $this->extractLabelTexts($resolvedSourcePath, $sourcePageCount);

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

                $orientation = $outputWidthPt >= $outputHeightPt ? 'L' : 'P';

                $chunks = $labelTextsPerPage[$pageNo - 1] ?? [];

                for ($i = 0; $i < $this->labelsPerPage; $i++) {
                    $labelText = $chunks[$i] ?? '';
                    $rackNo = null;
                    $partNo = null;
                    $partNoMatched = null;
                    $storeAddress = null;

                    if (trim($labelText) === '') {
                        $unmatchedNoText++;
                        $unmatchedDetails[] = [
                            'source' => $originalFilename, 'page' => $pageNo, 'label_index' => $i,
                            'part_no' => null, 'reason' => 'no_text',
                        ];
                    } else {
                        if (preg_match($this->storeAddressPattern, $labelText, $sa)) {
                            $storeAddress = trim($sa[1]);
                        }

                        if (preg_match($this->partNoPattern, $labelText, $m)) {
                            $partNo = trim($m[1]);
                        }

                        if (!$partNo) {
                            $unmatchedNoExtract++;
                            $unmatchedDetails[] = [
                                'source' => $originalFilename, 'page' => $pageNo, 'label_index' => $i,
                                'part_no' => null, 'reason' => 'no_extract',
                                'text_preview' => mb_substr(trim(preg_replace('/\s+/', ' ', $labelText)), 0, 200),
                            ];
                        } else {
                            $addressFutaba = $this->matchAddress($partNo);

                            if (!$addressFutaba) {
                                $unmatchedNoMaster++;
                                $unmatchedDetails[] = [
                                    'source' => $originalFilename, 'page' => $pageNo, 'label_index' => $i,
                                    'part_no' => $partNo, 'reason' => 'no_master',
                                ];
                            } elseif (!$addressFutaba->rack_no) {
                                $unmatchedNoRack++;
                                $unmatchedDetails[] = [
                                    'source' => $originalFilename, 'page' => $pageNo, 'label_index' => $i,
                                    'part_no' => $partNo, 'reason' => 'no_rack',
                                ];
                            } else {
                                $rackNo = $addressFutaba->rack_no;
                                $partNoMatched = $addressFutaba->part_no;
                                $matched++;
                            }
                        }
                    }

                    $yOffset = -(($topOffset + $i * $rawLabelHeight) * $scaleY);
                    $topExtraMm = $this->topExtraMarginMmByIndex[$i] ?? 0;
                    $yOffset += $topExtraMm * self::MM_TO_PT;

                    $entries[] = [
                        'seq' => $seq++,
                        'source_seq' => $sourceSeq,
                        'template_id' => $templateId,
                        'orientation' => $orientation,
                        'output_width_pt' => $outputWidthPt,
                        'output_height_pt' => $outputHeightPt,
                        'scaled_template_width' => $fullWidth * $scaleX,
                        'scaled_template_height' => $fullHeight * $scaleY,
                        'y_offset' => $yOffset,
                        'label_index' => $i,
                        'source_filename' => $originalFilename,
                        'store_address' => $storeAddress,
                        'rack_no' => $rackNo,
                        'part_no' => $partNoMatched,
                        'part_no_raw' => $partNo,
                    ];
                }
            }

            $sourceSeq++;
        }

        /**
         * Tahap 2: kelompokin per PDF (urutan upload), lalu di dalam
         * tiap PDF urutkan rack_no descending (Z -> A). Yang gak punya
         * rack_no taruh paling belakang di kelompok PDF-nya.
         */
        if ($this->sortByRackDesc) {
            usort($entries, function ($a, $b) {
                if ($a['source_seq'] !== $b['source_seq']) {
                    return $a['source_seq'] <=> $b['source_seq'];
                }

                $aHas = $a['rack_no'] !== null && $a['rack_no'] !== '';
                $bHas = $b['rack_no'] !== null && $b['rack_no'] !== '';

                if ($aHas && !$bHas) return -1;
                if (!$aHas && $bHas) return 1;

                if ($aHas && $bHas) {
                    $cmp = strnatcasecmp(trim($b['rack_no']), trim($a['rack_no']));
                    if ($cmp !== 0) return $cmp;
                }

                return $a['seq'] <=> $b['seq'];
            });
        }

        /**
         * Tahap 3: render sesuai urutan.
         */
        $labels = [];
        $outputIndex = 0;

        foreach ($entries as $entry) {
            $pdf->AddPage($entry['orientation'], [$entry['output_width_pt'], $entry['output_height_pt']]);

            $pdf->useTemplate(
                $entry['template_id'],
                0,
                $entry['y_offset'],
                $entry['scaled_template_width'],
                $entry['scaled_template_height']
            );

            if ($entry['rack_no']) {
                $this->drawOverlay(
                    $pdf,
                    $entry['rack_no'],
                    $entry['output_width_pt'],
                    $entry['output_height_pt'],
                    $entry['label_index']
                );
            }

            $outputIndex++;

            $rackNo = $entry['rack_no'];

            $labels[] = [
                'output_page' => $outputIndex,
                'source_filename' => $entry['source_filename'],
                'store_address' => $entry['store_address'],
                'rack_no' => $rackNo,
                'part_no' => $entry['part_no'],
                'part_no_raw' => $entry['part_no_raw'],
                'plant' => $rackNo === null ? null : (stripos($rackNo, 'K') === 0 ? 'Plant 2' : 'Plant 1'),
                'matched' => (bool) $rackNo,
            ];
        }

        $pdf->Output($outputPath, 'F');

        // Hapus file hasil normalize SETELAH output (FPDI baru nulis
        // imported page pas Output).
        foreach ($tempFiles as $tmp) {
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

        \Illuminate\Support\Facades\Log::info('KANBAN FUTABA SPLIT DONE', [
            'output' => $outputPath,
            'file_count' => count($sources),
            'total_labels' => $outputIndex,
            'matched' => $matched,
            'unmatched' => $unmatched,
            'unmatched_no_text' => $unmatchedNoText,
            'unmatched_no_extract' => $unmatchedNoExtract,
            'unmatched_no_master' => $unmatchedNoMaster,
            'unmatched_no_rack' => $unmatchedNoRack,
            'sorted_by_rack_desc' => $this->sortByRackDesc,
        ]);

        if (!empty($noExtractPreviews)) {
            \Illuminate\Support\Facades\Log::info('KANBAN FUTABA UNMATCHED - PART NO GAGAL DI-EXTRACT DARI TEKS', [
                'count' => count($noExtractPreviews),
                'previews' => $noExtractPreviews,
            ]);
        }

        if (!empty($noMasterPartNos)) {
            \Illuminate\Support\Facades\Log::info('KANBAN FUTABA UNMATCHED - PART NO GAK ADA DI MASTER', [
                'count' => count($noMasterPartNos),
                'part_no_list' => $noMasterPartNos,
            ]);
        }

        if (!empty($noRackPartNos)) {
            \Illuminate\Support\Facades\Log::info('KANBAN FUTABA UNMATCHED - RACK NO KOSONG DI MASTER', [
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
     * Cocokin part no ke master. Beberapa part no di PDF Futaba
     * muncul dengan suffix tambahan yang gak ada di master (mis.
     * "61235-KK010-W" di PDF vs "61235-KK010" di master) — kalau
     * exact match gagal, coba strip SATU segmen terakhir dan cari lagi.
     */
    protected function matchAddress(string $partNo): ?AddressFutaba
    {
        $address = AddressFutaba::where('part_no', $partNo)->first();
        if ($address) {
            return $address;
        }

        if (preg_match('/^(.*)-[A-Za-z0-9]+$/', $partNo, $m)) {
            return AddressFutaba::where('part_no', $m[1])->first();
        }

        return null;
    }

    protected function ensureReadable(string $sourcePath): string
    {
        try {
            $probe = new Fpdi();
            $probe->setSourceFile($sourcePath);
            return $sourcePath;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('KANBAN FUTABA PDF PERLU NORMALIZE', [
                'source' => $sourcePath,
                'error' => $e->getMessage(),
            ]);

            return app(PdfNormalizer::class)->normalize($sourcePath);
        }
    }

    protected function drawOverlay(Fpdi $pdf, string $rackNo, float $labelWidthPt, float $labelHeightPt, int $labelIndex): void
    {
        $xFraction = $this->overlayXFractionByIndex[$labelIndex] ?? 0.02;
        $yFraction = $this->overlayYFractionByIndex[$labelIndex] ?? 0.60;
        $wFraction = $this->overlayWidthFractionByIndex[$labelIndex] ?? 0.22;
        $hFraction = $this->overlayHeightFractionByIndex[$labelIndex] ?? 0.15;

        $x = $xFraction * $labelWidthPt;
        $y = $yFraction * $labelHeightPt;
        $w = $wFraction * $labelWidthPt;
        $h = $hFraction * $labelHeightPt;

        $pdf->SetFont($this->overlayFont, 'B', $this->overlayFontSize);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY($x, $y);
        $pdf->Cell($w, $h, $rackNo, 0, 0, $this->overlayAlign);
    }

    protected function extractLabelTexts(string $sourcePath, int $pageCount): array
    {
        $textParser = new PdfTextParser();
        $document = $textParser->parseFile($sourcePath);
        $pages = $document->getPages();

        $result = [];

        foreach ($pages as $pageIndex => $page) {
            $fullText = $page->getText();

            $parts = preg_split('/(?=' . preg_quote($this->labelAnchorText, '/') . ')/', $fullText);

            $parts = array_values(array_filter($parts, function ($p) {
                return str_starts_with(ltrim($p), $this->labelAnchorText);
            }));

            $result[$pageIndex] = $parts;
        }

        return $result;
    }
}
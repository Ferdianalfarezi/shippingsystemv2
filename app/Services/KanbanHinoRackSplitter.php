<?php

namespace App\Services;

use App\Models\AddressHino;
use setasign\Fpdi\Tcpdf\Fpdi;
use Smalot\PdfParser\Parser as PdfTextParser;

class KanbanHinoRackSplitter
{
    /** Jumlah kanban per halaman sumber. Customer Hino -> 4 per halaman (kayak NTC). */
    protected int $labelsPerPage;

    /** Teks penanda awal tiap label kanban Hino. Muncul sekali persis di awal tiap label. */
    protected string $labelAnchorText = 'FORM:PAD/FR-LAS-00/011';

    /**
     * Part No DIAMBIL DARI BENTUK/SHAPE-nya langsung (5digit-5alnum-2alnum,
     * mis. "16592-0W010-F0"), BUKAN dari pola "header: value" — soalnya
     * di PDF Hino urutan kolomnya suka ke-interleave/kacau pas di-extract.
     */
    protected string $partNoPattern = '/\b(\d{5}-[A-Z0-9]{4,6}-[A-Z0-9]{2})\b/i';

    /**
     * "DELIVERY NOTE" — konstan per file. Cuma metadata buat
     * search/filter dulu, belum dipake grouping/sort.
     */
    protected string $deliveryNotePattern = '/DELIVERY NOTE.*?(\d{8,12})/is';

    // ============================================================
    // OVERLAY RACK NO — ditaro di kotak "STP002" (kolom part
    // no/description, sejajar CYCLE). Gak pake background putih —
    // teks ditulis langsung di atas konten asli.
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
    protected string $overlayLabel = 'STP RACK : '; // prefix di depan rack_no
    protected bool $overlayDrawBackground = false; // gak nutupin, langsung tulis aja
    protected string $overlayBgColor = 'FFFFFF';

    protected float $sourceMarginTopMm = 5;
    protected float $sourceMarginBottomMm = 5;

    /**
     * Margin tambahan (mm) di ATAS tiap crop, PER INDEX label dalam
     * 1 halaman (0 = kanban paling atas, 3 = paling bawah).
     */
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
        $labels = [];
        $outputIndex = 0;

        foreach ($sources as $source) {
            $originalSourcePath = $source['path'];
            $originalFilename = $source['original_filename'] ?? basename($originalSourcePath);

            $resolvedSourcePath = $this->ensureReadable($originalSourcePath);

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
                            $partNo = strtoupper(trim($m[1]));
                        }

                        if (!$partNo) {
                            $unmatchedNoExtract++;
                            $unmatchedDetails[] = [
                                'source' => $originalFilename, 'page' => $pageNo, 'label_index' => $i,
                                'part_no' => null, 'reason' => 'no_extract',
                                'text_preview' => mb_substr(trim(preg_replace('/\s+/', ' ', $labelText)), 0, 200),
                            ];
                        } else {
                            $addressHino = $this->matchAddress($partNo);

                            if (!$addressHino) {
                                $unmatchedNoMaster++;
                                $unmatchedDetails[] = [
                                    'source' => $originalFilename, 'page' => $pageNo, 'label_index' => $i,
                                    'part_no' => $partNo, 'reason' => 'no_master',
                                ];
                            } elseif (!$addressHino->rack_no) {
                                $unmatchedNoRack++;
                                $unmatchedDetails[] = [
                                    'source' => $originalFilename, 'page' => $pageNo, 'label_index' => $i,
                                    'part_no' => $partNo, 'reason' => 'no_rack',
                                ];
                            } else {
                                $rackNo = $addressHino->rack_no;
                                $partNoMatched = $addressHino->part_no;
                                $matched++;
                            }
                        }
                    }

                    $pdf->AddPage($orientation, [$outputWidthPt, $outputHeightPt]);

                    $scaledTemplateWidth  = $fullWidth * $scaleX;
                    $scaledTemplateHeight = $fullHeight * $scaleY;
                    $yOffset = -(($topOffset + $i * $rawLabelHeight) * $scaleY);

                    $topExtraMm = $this->topExtraMarginMmByIndex[$i] ?? 0;
                    $yOffset += $topExtraMm * self::MM_TO_PT;

                    $pdf->useTemplate($templateId, 0, $yOffset, $scaledTemplateWidth, $scaledTemplateHeight);

                    if ($rackNo) {
                        $this->drawOverlay($pdf, $rackNo, $outputWidthPt, $outputHeightPt, $i);
                    }

                    $outputIndex++;

                    $labels[] = [
                        'output_page' => $outputIndex,
                        'source_filename' => $originalFilename,
                        'delivery_note' => $deliveryNote,
                        'rack_no' => $rackNo,
                        'part_no' => $partNoMatched,
                        'part_no_raw' => $partNo,
                        'plant' => $rackNo === null ? null : (stripos($rackNo, 'K') === 0 ? 'Plant 2' : 'Plant 1'),
                        'matched' => (bool) $rackNo,
                    ];
                }
            }

            if ($resolvedSourcePath !== $originalSourcePath) {
                @unlink($resolvedSourcePath);
            }
        }

        $pdf->Output($outputPath, 'F');

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
     * Cocokin part no ke master. Part No di PDF Hino 3 segmen (mis.
     * "16592-0W010-F0"), master addresshino cuma nyimpen 2 segmen
     * pertama (mis. "16592-0W010").
     */
    protected function matchAddress(string $partNo): ?AddressHino
    {
        $address = AddressHino::where('part_no', $partNo)->first();
        if ($address) {
            return $address;
        }

        $segments = explode('-', $partNo);
        if (count($segments) >= 3) {
            $stripped = $segments[0] . '-' . $segments[1];
            return AddressHino::where('part_no', $stripped)->first();
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
            \Illuminate\Support\Facades\Log::warning('KANBAN HINO PDF PERLU NORMALIZE', [
                'source' => $sourcePath,
                'error' => $e->getMessage(),
            ]);

            return app(PdfNormalizer::class)->normalize($sourcePath);
        }
    }

    /**
     * Tulis "STP RACK : <rack_no>" di kotak "STP002" (kolom part
     * no/description). Gak ada background — teks ditulis langsung
     * di atas konten asli.
     */
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
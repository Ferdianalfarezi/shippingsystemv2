<?php

namespace App\Services;

use App\Models\AddressFutaba;
use setasign\Fpdi\Tcpdf\Fpdi;
use Smalot\PdfParser\Parser as PdfTextParser;

class KanbanFutabaRackSplitter
{
    /**
     * Jumlah kanban per halaman sumber. Customer Futaba -> 3 per
     * halaman (beda dari ADM/NTC/FJI yang defaultnya 4).
     */
    protected int $labelsPerPage;

    /**
     * Teks penanda awal tiap label kanban Futaba. "SUPPLIER" muncul
     * sekali persis di awal tiap label.
     */
    protected string $labelAnchorText = 'SUPPLIER';

    /** Part No langsung nempel abis "PART No." */
    protected string $partNoPattern = '/PART No\.\s*\r?\n?\s*(\S+)/i';

    /**
     * "STORE ADDRESS" = kode lokasi/gudang customer Futaba sendiri —
     * BUKAN rack_no milik STEP, cuma metadata buat search/filter.
     */
    protected string $storeAddressPattern = '/STORE ADDRESS\s*\r?\n?\s*([^\r\n]+)/i';

    /**
     * "DOCK CODE" (mis. "D04"). Dipake buat GROUPING (1 PDF biasanya
     * = 1 dock code, jadi kalau upload banyak file sekaligus, tiap
     * file otomatis jadi 1 grup). Beda dari No. Surat Jalan-nya FJI,
     * field ini nempel LANGSUNG setelah header-nya (gak kepotong ke
     * chunk sebelumnya), jadi aman diambil PER LABEL.
     */
    protected string $dockCodePattern = '/DOCK CODE\s*\r?\n?\s*(\S+)/i';

    // ============================================================
    // OVERLAY RACK NO — kotak "Supplier Free Area 1" (kiri bawah, di
    // bawah PDS No./SERIAL). Koordinat fraksi, PER INDEX posisi FISIK
    // label di halaman sumber (0-2).
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
     * Margin tambahan (mm) di ATAS tiap crop, PER INDEX posisi FISIK
     * label di halaman sumber (0-2) — BUKAN urutan render setelah
     * di-sort, biar tuning-nya tetep valid meskipun labelnya kepencar
     * urutan render-nya gara-gara grouping/sorting.
     */
    protected array $topExtraMarginMmByIndex = [
        0 => 2,
        1 => 0,
        2 => -3,
    ];

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
     * Split BANYAK file PDF jadi SATU output, DI-GROUPING per Dock
     * Code lalu DI-SORT rack_no descending (natural sort) di dalam
     * tiap grup — pola sama kayak sortItemsByManifestGroup() punya
     * NTC, cuma key grouping-nya Dock Code dan perbandingannya pakai
     * strnatcasecmp (bandingin huruf dulu, angka dibandingin sebagai
     * nilai, bukan per-karakter).
     *
     * PASS 1 — kumpulin semua label dari semua file dulu (import
     * semua halaman ke $pdf, simpen templateId + geometri + hasil
     * extract/matching per label).
     * PASS 2 — $items di-grouping+sort, baru DI-RENDER ke halaman
     * output pake templateId yang udah disimpen di PASS 1.
     *
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

        // ========================================================
        // PASS 1 — analisa semua label dari semua file, belum render.
        // ========================================================
        $items = [];

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

                $chunks = $labelTextsPerPage[$pageNo - 1] ?? [];

                for ($i = 0; $i < $this->labelsPerPage; $i++) {
                    $labelText = $chunks[$i] ?? '';
                    $rackNo = null;
                    $partNo = null;
                    $partNoMatched = null;
                    $storeAddress = null;
                    $dockCode = null;

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

                        if (preg_match($this->dockCodePattern, $labelText, $dc)) {
                            $dockCode = trim($dc[1]);
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

                    $items[] = [
                        'seq' => count($items),
                        'sourceFilename' => $originalFilename,
                        'labelIndex' => $i,
                        'dockCode' => $dockCode,
                        'storeAddress' => $storeAddress,
                        'rackNo' => $rackNo,
                        'partNo' => $partNoMatched,
                        'partNoRaw' => $partNo,
                        'plant' => $rackNo === null ? null : (stripos($rackNo, 'K') === 0 ? 'Plant 2' : 'Plant 1'),
                        // geometri buat render di PASS 2
                        'templateId' => $templateId,
                        'scaleX' => $scaleX,
                        'scaleY' => $scaleY,
                        'fullWidth' => $fullWidth,
                        'fullHeight' => $fullHeight,
                        'rawLabelHeight' => $rawLabelHeight,
                        'topOffset' => $topOffset,
                    ];
                }
            }

            if ($resolvedSourcePath !== $originalSourcePath) {
                @unlink($resolvedSourcePath);
            }
        }

        $totalLabels = count($items);

        // Grouping per Dock Code + sort rack_no descending (natural
        // sort) di dalam grup. Lihat sortItemsByDockCodeGroup().
        $items = $this->sortItemsByDockCodeGroup($items);

        // ========================================================
        // PASS 2 — render ke halaman output sesuai urutan hasil sort.
        // ========================================================
        $labels = [];

        foreach ($items as $outputIndex => $item) {
            $outputWidthPt  = $item['fullWidth'] * $item['scaleX'];
            $outputHeightPt = $item['rawLabelHeight'] * $item['scaleY'];
            // (kalau forceOutputSize false, scaleX/scaleY = 1, jadi
            // outputWidthPt/outputHeightPt = fullWidth/rawLabelHeight
            // persis kayak sebelumnya)
            $orientation = $outputWidthPt >= $outputHeightPt ? 'L' : 'P';

            $pdf->AddPage($orientation, [$outputWidthPt, $outputHeightPt]);

            $scaledTemplateWidth  = $item['fullWidth'] * $item['scaleX'];
            $scaledTemplateHeight = $item['fullHeight'] * $item['scaleY'];

            $yOffset = -(($item['topOffset'] + $item['labelIndex'] * $item['rawLabelHeight']) * $item['scaleY']);

            $topExtraMm = $this->topExtraMarginMmByIndex[$item['labelIndex']] ?? 0;
            $yOffset += $topExtraMm * self::MM_TO_PT;

            $pdf->useTemplate($item['templateId'], 0, $yOffset, $scaledTemplateWidth, $scaledTemplateHeight);

            if ($item['rackNo']) {
                $this->drawOverlay($pdf, $item['rackNo'], $outputWidthPt, $outputHeightPt, $item['labelIndex']);
            }

            $labels[] = [
                'output_page' => $outputIndex + 1,
                'source_filename' => $item['sourceFilename'],
                'dock_code' => $item['dockCode'],
                'store_address' => $item['storeAddress'],
                'rack_no' => $item['rackNo'],
                'part_no' => $item['partNo'],
                'part_no_raw' => $item['partNoRaw'],
                'plant' => $item['plant'],
                'matched' => (bool) $item['rackNo'],
            ];
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

        \Illuminate\Support\Facades\Log::info('KANBAN FUTABA SPLIT DONE', [
            'output' => $outputPath,
            'file_count' => count($sources),
            'total_labels' => $totalLabels,
            'matched' => $matched,
            'unmatched' => $unmatched,
            'unmatched_no_text' => $unmatchedNoText,
            'unmatched_no_extract' => $unmatchedNoExtract,
            'unmatched_no_master' => $unmatchedNoMaster,
            'unmatched_no_rack' => $unmatchedNoRack,
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
            'total_labels' => $totalLabels,
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
     * Sort $items (flat, urutan asli lintas semua file) supaya di
     * dalam label-label yang punya Dock Code SAMA, urutannya jadi
     * rack_no DESCENDING pakai NATURAL SORT (strnatcasecmp) — bagian
     * huruf dibandingin dulu, bagian angka dibandingin sebagai NILAI
     * (bukan per-karakter), jadi "H3-9-A" < "H3-27-A" dengan bener
     * (beda dari strcmp biasa yang bisa salah baca '9' > '2').
     *
     * Aturan grouping/null-handling sama persis kayak sortItemsByManifestGroup()
     * punya NTC — cuma key-nya 'dockCode' dan komparatornya natural sort.
     *
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    protected function sortItemsByDockCodeGroup(array $items): array
    {
        $groups = [];
        $groupOrder = [];

        foreach ($items as $index => $item) {
            $dockCode = $item['dockCode'] ?? null;

            $groupKey = $dockCode !== null && $dockCode !== ''
                ? ('dock:' . $dockCode)
                : ('solo:' . $index);

            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = [];
                $groupOrder[] = $groupKey;
            }

            $groups[$groupKey][] = $item;
        }

        foreach ($groups as $groupKey => $groupItems) {
            usort($groupItems, function ($a, $b) {
                $rackA = $a['rackNo'] ?? null;
                $rackB = $b['rackNo'] ?? null;

                if ($rackA === null && $rackB === null) {
                    return 0;
                }
                if ($rackA === null) {
                    return 1; // null selalu di belakang
                }
                if ($rackB === null) {
                    return -1;
                }

                // descending natural sort: huruf dulu, angka sebagai nilai
                return strnatcasecmp($rackB, $rackA);
            });

            $groups[$groupKey] = $groupItems;
        }

        $result = [];
        foreach ($groupOrder as $groupKey) {
            foreach ($groups[$groupKey] as $item) {
                $item['seq'] = count($result);
                $result[] = $item;
            }
        }

        return $result;
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
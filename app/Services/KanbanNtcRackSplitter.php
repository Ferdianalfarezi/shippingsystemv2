<?php

namespace App\Services;

use App\Models\NtcAddress;
use setasign\Fpdi\Tcpdf\Fpdi;
use Smalot\PdfParser\Parser as PdfTextParser;

class KanbanNtcRackSplitter
{
    /** Jumlah kanban per halaman sumber (tersusun vertikal) */
    protected int $labelsPerPage;

    /**
     * Teks penanda awal tiap label di kanban NTC. Urutan hasil extract
     * Smalot/pdfparser buat template ini "CYCLE" duluan (bukan
     * "SUPPLIER..." kayak dugaan awal) — dicek langsung dari dump
     * mentah, dan "CYCLE" cuma muncul sekali per label.
     */
    protected string $labelAnchorText = 'CYCLE';

    /**
     * Regex buat narik MANIFEST NO (angka 10 digit, mis. 1260904360)
     * dari teks label. Dipake buat GROUPING (kayak DN No di ADM) —
     * label-label dengan Manifest No yang sama dikumpulin jadi satu
     * blok berurutan, dan di dalam blok itu di-sort rack_no descending.
     */
    protected string $manifestNoPattern = '/\b\d{10}\b/';

    // ============================================================
    // OVERLAY RACK NO — kotak "SUP ADDRESS" (kanan bawah label).
    // Koordinat dalam MM, relatif ke pojok kiri atas HASIL OUTPUT
    // (200x75mm), gak perlu scaleX/scaleY. Udah di-tune, JANGAN diubah.
    // ============================================================
    protected float $overlayXMm = 147;
    protected float $overlayYMm = 62;
    protected float $overlayWidthMm = 20;
    protected float $overlayHeightMm = 10;
    protected float $overlayFontSize = 11;
    protected string $overlayFont = 'helvetica';
    protected string $overlayAlign = 'C';

    /**
     * Offset tambahan (mm, boleh negatif) buat posisi Y overlay PER
     * INDEX label dalam 1 halaman SUMBER (0 = label paling atas, 3 =
     * paling bawah). Udah di-tune, JANGAN diubah. PENTING: ini index
     * posisi FISIK label di halaman sumber, BUKAN urutan output
     * setelah di-sort — jadi tetep valid dipake meskipun label-nya
     * kepencar ke urutan render yang beda gara-gara grouping/sorting.
     */
    protected array $overlayYOffsetMmByIndex = [
        0 => -4,
        1 => -4,
        2 => -3,
        3 => 0,
    ];

    protected float $sourceMarginTopMm = 5;
    protected float $sourceMarginBottomMm = 5;

    protected float $firstLabelExtraTopMarginMm = 3;

    protected ?float $customLabelHeight = null;
    protected ?float $customTopOffset = null;

    protected float $outputPageWidthMm = 200;
    protected float $outputPageHeightMm = 75;

    protected const MM_TO_PT = 72 / 25.4;

    public function __construct(int $labelsPerPage = 4)
    {
        $this->labelsPerPage = $labelsPerPage;
    }

    /**
     * Split BANYAK file PDF sekaligus jadi SATU output PDF gabungan,
     * dengan urutan output di-GROUPING per Manifest No lalu di-SORT
     * rack_no descending di dalam tiap grup — pola persis sama kayak
     * sortItemsByDnGroup() di KanbanRackSplitter (ADM), cuma key
     * grouping-nya Manifest No, bukan DN No.
     *
     * Tiap $sources[i] = ['path' => string, 'original_filename' => string].
     *
     * PASS 1 — kumpulin SEMUA label dari SEMUA file dulu (import
     * semua halaman ke $pdf via importPage(), simpen templateId +
     * geometri + hasil extract/matching per label ke $items), urutan
     * masih persis urutan asli (file 1 lalu file 2, dst).
     *
     * PASS 2 — $items di-grouping+sort, baru DI-RENDER ke halaman
     * output pake templateId yang udah disimpen di PASS 1. TemplateId
     * dari FPDI tetep valid dipake meski source file udah pindah/ganti
     * (isi halaman udah ke-capture pas importPage() dipanggil), jadi
     * aman disimpen dulu lintas file buat dipake belakangan.
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

        $extractor = new PartNoExtractor();

        $matched = 0;
        $unmatchedNoText = 0;
        $unmatchedNoExtract = 0;
        $unmatchedNoMaster = 0;
        $unmatchedNoRack = 0;
        $unmatchedDetails = [];

        $outputWidthPt  = $this->outputPageWidthMm * self::MM_TO_PT;
        $outputHeightPt = $this->outputPageHeightMm * self::MM_TO_PT;

        // ========================================================
        // PASS 1 — analisa semua label dari semua file, belum render.
        // ========================================================
        $items = []; // list flat, urutan asli (file 1 dulu, lalu file 2, dst)

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

                $labelHeight = $this->customLabelHeight
                    ?? (($fullHeight - $marginTopPt - $marginBottomPt) / $this->labelsPerPage);

                $topOffset = $this->customTopOffset ?? $marginTopPt;

                $scaleX = $outputWidthPt / $fullWidth;
                $scaleY = $outputHeightPt / $labelHeight;

                $chunks = $labelTextsPerPage[$pageNo - 1] ?? [];

                for ($i = 0; $i < $this->labelsPerPage; $i++) {
                    $labelText = $chunks[$i] ?? '';
                    $rackNo = null;
                    $partNo = null;
                    $partNoMatched = null;
                    $manifestNo = null;

                    if (trim($labelText) === '') {
                        $unmatchedNoText++;
                        $unmatchedDetails[] = [
                            'source' => $originalFilename, 'page' => $pageNo, 'label_index' => $i,
                            'part_no' => null, 'reason' => 'no_text',
                        ];
                    } else {
                        $manifestNo = $this->extractManifestNo($labelText);
                        $partNo = $extractor->extract($labelText);

                        if (!$partNo) {
                            $unmatchedNoExtract++;
                            $unmatchedDetails[] = [
                                'source' => $originalFilename, 'page' => $pageNo, 'label_index' => $i,
                                'part_no' => null, 'reason' => 'no_extract',
                                'text_preview' => mb_substr(trim(preg_replace('/\s+/', ' ', $labelText)), 0, 200),
                            ];
                        } else {
                            // Strip suffix versi NTC sendiri (2 segmen pertama
                            // aja, apapun isi suffix ke-3, mis. "-V0" / "-00").
                            $stripped = $this->stripVariantSuffix($partNo);

                            $ntcAddress = NtcAddress::where('part_no', $stripped)->first()
                                ?? NtcAddress::where('part_no', trim($partNo))->first();

                            if (!$ntcAddress) {
                                $unmatchedNoMaster++;
                                $unmatchedDetails[] = [
                                    'source' => $originalFilename, 'page' => $pageNo, 'label_index' => $i,
                                    'part_no' => $stripped, 'reason' => 'no_master',
                                ];
                            } elseif (!$ntcAddress->rack_no) {
                                $unmatchedNoRack++;
                                $unmatchedDetails[] = [
                                    'source' => $originalFilename, 'page' => $pageNo, 'label_index' => $i,
                                    'part_no' => $stripped, 'reason' => 'no_rack',
                                ];
                            } else {
                                $rackNo = $ntcAddress->rack_no;
                                $partNoMatched = $ntcAddress->part_no;
                                $matched++;
                            }
                        }
                    }

                    $items[] = [
                        'seq' => count($items),
                        'sourceFilename' => $originalFilename,
                        'pageNo' => $pageNo,
                        'labelIndex' => $i,
                        'manifestNo' => $manifestNo,
                        'rackNo' => $rackNo,
                        'partNo' => $partNoMatched,
                        'partNoRaw' => $partNo,
                        'plant' => $rackNo === null ? null : (stripos($rackNo, 'K') === 0 ? 'Plant 2' : 'Plant 1'),
                        // geometri buat render di PASS 2 nanti
                        'templateId' => $templateId,
                        'scaleX' => $scaleX,
                        'scaleY' => $scaleY,
                        'fullWidth' => $fullWidth,
                        'fullHeight' => $fullHeight,
                        'labelHeight' => $labelHeight,
                        'topOffset' => $topOffset,
                    ];
                }
            }

            if ($resolvedSourcePath !== $originalSourcePath) {
                @unlink($resolvedSourcePath);
            }
        }

        $totalLabels = count($items);

        // Grouping per Manifest No + sort rack_no descending di dalam
        // grup — lihat sortItemsByManifestGroup().
        $items = $this->sortItemsByManifestGroup($items);

        // ========================================================
        // PASS 2 — render ke halaman output sesuai urutan hasil sort.
        // ========================================================
        $labels = [];

        foreach ($items as $outputIndex => $item) {
            $pdf->AddPage('L', [$outputWidthPt, $outputHeightPt]);

            $scaledTemplateWidth  = $item['fullWidth'] * $item['scaleX'];
            $scaledTemplateHeight = $item['fullHeight'] * $item['scaleY'];

            $yOffset = -(($item['topOffset'] + $item['labelIndex'] * $item['labelHeight']) * $item['scaleY']);

            if ($item['labelIndex'] === 0) {
                $yOffset += $this->firstLabelExtraTopMarginMm * self::MM_TO_PT;
            }

            $pdf->useTemplate($item['templateId'], 0, $yOffset, $scaledTemplateWidth, $scaledTemplateHeight);

            if ($item['rackNo']) {
                $this->drawOverlay($pdf, $item['rackNo'], $item['labelIndex']);
            }

            $labels[] = [
                'output_page' => $outputIndex + 1,
                'source_filename' => $item['sourceFilename'],
                'manifest_no' => $item['manifestNo'],
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

        \Illuminate\Support\Facades\Log::info('KANBAN NTC SPLIT DONE', [
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
            \Illuminate\Support\Facades\Log::info('KANBAN NTC UNMATCHED - PART NO GAGAL DI-EXTRACT DARI TEKS', [
                'count' => count($noExtractPreviews),
                'previews' => $noExtractPreviews,
            ]);
        }

        if (!empty($noMasterPartNos)) {
            \Illuminate\Support\Facades\Log::info('KANBAN NTC UNMATCHED - PART NO GAK ADA DI MASTER', [
                'count' => count($noMasterPartNos),
                'part_no_list' => $noMasterPartNos,
            ]);
        }

        if (!empty($noRackPartNos)) {
            \Illuminate\Support\Facades\Log::info('KANBAN NTC UNMATCHED - RACK NO KOSONG DI MASTER', [
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
     * dalam label-label yang punya Manifest No SAMA, urutannya jadi
     * rack_no DESCENDING. Pola persis sortItemsByDnGroup() punya ADM
     * (KanbanRackSplitter), cuma key grouping-nya 'manifestNo'.
     *
     * Aturan:
     * - Grouping berdasarkan 'manifestNo'. Item dengan manifestNo NULL
     *   dianggap grup sendiri per-item (gak digabung ke grup lain).
     * - Urutan ANTAR grup ngikut posisi kemunculan PERTAMA grup itu
     *   di $items asli (lintas semua file, sesuai urutan upload).
     * - Di dalam grup, item yang rackNo-nya NULL (unmatched) ditaro
     *   di AKHIR grup, urutan asli antar-mereka dipertahankan.
     * - Perbandingan rackNo pakai strcmp() dibalik (descending).
     *
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    protected function sortItemsByManifestGroup(array $items): array
    {
        $groups = [];
        $groupOrder = [];

        foreach ($items as $index => $item) {
            $manifestNo = $item['manifestNo'] ?? null;

            $groupKey = $manifestNo !== null && $manifestNo !== ''
                ? ('manifest:' . $manifestNo)
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
                    return 1;
                }
                if ($rackB === null) {
                    return -1;
                }

                return strcmp($rackB, $rackA);
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
     * Strip suffix varian NTC. "UNIQUE NO" di PDF NTC biasanya 3
     * segmen (mis. 53728-BZ020-V0), master ntc_addresses cuma nyimpen
     * 2 segmen pertama (53728-BZ020). Ambil 2 segmen pertama apa
     * adanya, gak peduli suffix-nya digit atau huruf.
     */
    protected function stripVariantSuffix(string $partNo): string
    {
        $segments = explode('-', trim($partNo));

        if (count($segments) >= 3) {
            return $segments[0] . '-' . $segments[1];
        }

        return trim($partNo);
    }

    /**
     * Ambil MANIFEST NO (angka 10 digit) dari teks 1 label. Dipake
     * buat GROUPING (lihat sortItemsByManifestGroup()) dan filter di
     * frontend.
     */
    protected function extractManifestNo(string $labelText): ?string
    {
        if (preg_match($this->manifestNoPattern, $labelText, $matches)) {
            return $matches[0];
        }

        return null;
    }

    /**
     * Coba baca PDF sumber langsung pakai FPDI. Kalau gagal karena
     * struktur/kompresi yang gak disupport parser FPDI gratis,
     * normalize dulu pakai qpdf (via PdfNormalizer).
     */
    protected function ensureReadable(string $sourcePath): string
    {
        try {
            $probe = new Fpdi();
            $probe->setSourceFile($sourcePath);
            return $sourcePath;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('KANBAN NTC PDF PERLU NORMALIZE', [
                'source' => $sourcePath,
                'error' => $e->getMessage(),
            ]);

            return app(PdfNormalizer::class)->normalize($sourcePath);
        }
    }

    /**
     * Tulis rack_no di kotak "SUP ADDRESS" (kanan bawah label).
     * Koordinat udah dalam mm relatif ke output 200x75mm, ditambah
     * koreksi per-index dari $overlayYOffsetMmByIndex. $labelIndex di
     * sini adalah posisi FISIK label di halaman sumber (0-3), bukan
     * urutan render setelah sorting.
     */
    protected function drawOverlay(Fpdi $pdf, string $rackNo, int $labelIndex): void
    {
        $extraOffsetMm = $this->overlayYOffsetMmByIndex[$labelIndex] ?? 0;

        $x = $this->overlayXMm * self::MM_TO_PT;
        $y = ($this->overlayYMm + $extraOffsetMm) * self::MM_TO_PT;
        $w = $this->overlayWidthMm * self::MM_TO_PT;
        $h = $this->overlayHeightMm * self::MM_TO_PT;

        $pdf->SetFont($this->overlayFont, 'B', $this->overlayFontSize);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY($x, $y);
        $pdf->Cell($w, $h, $rackNo, 0, 0, $this->overlayAlign);
    }

    /**
     * Ambil teks per halaman dari PDF sumber, pecah jadi N chunk
     * (1 chunk = 1 label) berdasarkan kemunculan $labelAnchorText.
     *
     * @return array<int, array<int, string>>
     */
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
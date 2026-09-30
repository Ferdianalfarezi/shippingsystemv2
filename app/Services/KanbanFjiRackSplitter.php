<?php

namespace App\Services;

use App\Models\AddressFji;
use setasign\Fpdi\Tcpdf\Fpdi;
use Smalot\PdfParser\Parser as PdfTextParser;

class KanbanFjiRackSplitter
{
    /** Jumlah kanban per halaman sumber (tersusun vertikal) */
    protected int $labelsPerPage;

    /**
     * Teks penanda awal tiap label di kanban FJI. Tiap label punya
     * blok jelas yang selalu diawali (dalam urutan hasil extract)
     * sama teks "No. Surat Jalan".
     */
    protected string $labelAnchorText = 'No. Surat Jalan';

    /**
     * Regex buat narik Part Number. BUKAN pakai PartNoExtractor generik
     * — part no FJI variatif bentuknya, jadi diambil langsung dari
     * pola tercetak "Part Number ... : <value>".
     */
    protected string $partNoPattern = '/Part\s*Number.*?:\s*(\S+)/is';

    /**
     * Regex buat narik "No. Surat Jalan". Diambil SEKALI per file
     * (nilainya konstan buat 1 file/surat jalan), bukan per label.
     */
    protected string $suratJalanNoPattern = '/(\d{10,20})\s*\r?\n?\s*No\.\s*Surat\s*Jalan/i';

    // ============================================================
    // URUTAN OUTPUT
    // 1) Surat jalan  -> ASC
    // 2) Rack no      -> DESC (abjad terbesar muncul duluan)
    // Label tanpa rack / tanpa surat jalan ditaruh paling bawah.
    // ============================================================
    protected bool $sortSuratJalanAsc = true;
    protected bool $sortRackDesc = true;

    // ============================================================
    // OVERLAY RACK NO — PER INDEX label dalam 1 halaman (0 = kanban
    // paling atas, 3 = paling bawah). Semua nilai fraksi (0-1)
    // relatif ke lebar/tinggi label. Defaultnya sama semua (posisi
    // kotak kosong di baris footer, sebelah kiri sebelum teks "WH RM
    // WELDING KIIC"), tuning manual per index kalau ternyata posisi
    // fisiknya beda-beda antar kanban di halaman.
    // ============================================================
    protected array $overlayXFractionByIndex = [
        0 => 0.13,
        1 => 0.13,
        2 => 0.13,
        3 => 0.13,
    ];
    protected array $overlayYFractionByIndex = [
        0 => 0.83,
        1 => 0.80,
        2 => 0.81,
        3 => 0.81,
    ];
    protected array $overlayWidthFractionByIndex = [
        0 => 0.14,
        1 => 0.14,
        2 => 0.14,
        3 => 0.14,
    ];
    protected array $overlayHeightFractionByIndex = [
        0 => 0.14,
        1 => 0.14,
        2 => 0.14,
        3 => 0.14,
    ];
    protected float $overlayFontSize = 11;
    protected string $overlayFont = 'helvetica';
    protected string $overlayAlign = 'C';

    // ============================================================
    // OVERLAY KATEGORI — area kosong di kanan "Part Number" s/d
    // "QTY" (di dalam kotak utama, sebelum kolom QR surat jalan).
    // Sama kayak overlay rack: fraksi (0-1) PER INDEX label.
    // Y index 1-3 diturunin dikit ngikutin pola drift overlay rack.
    // ============================================================
    protected array $kategoriXFractionByIndex = [
        0 => 0.47,
        1 => 0.47,
        2 => 0.47,
        3 => 0.47,
    ];
    protected array $kategoriYFractionByIndex = [
        0 => 0.40,
        1 => 0.37,
        2 => 0.38,
        3 => 0.38,
    ];
    protected array $kategoriWidthFractionByIndex = [
        0 => 0.22,
        1 => 0.22,
        2 => 0.22,
        3 => 0.22,
    ];
    protected array $kategoriHeightFractionByIndex = [
        0 => 0.26,
        1 => 0.26,
        2 => 0.26,
        3 => 0.26,
    ];
    protected float $kategoriFontSize = 28;
    protected string $kategoriFont = 'helvetica';
    protected string $kategoriAlign = 'C';

    protected float $sourceMarginTopMm = 5;
    protected float $sourceMarginBottomMm = 5;

    /**
     * Margin tambahan (mm) di ATAS tiap crop, PER INDEX label dalam
     * 1 halaman (0 = kanban paling atas, 3 = paling bawah). Index 2
     * & 3 dinaikin lagi karena drift-nya lebih kerasa di posisi bawah.
     */
    protected array $topExtraMarginMmByIndex = [
        0 => 6,
        1 => 6,
        2 => 8,
        3 => 10,
    ];

    /**
     * TRIM tinggi tiap slice label (mm), dipake buat STEPPING antar
     * crop DAN tinggi output. Biar gak ada drift/sisa ruang kosong.
     */
    protected float $labelHeightTrimMm = 3;

    protected ?float $customLabelHeight = null;
    protected ?float $customTopOffset = null;

    /**
     * TODO FJI: belum dikonfirmasi mau di-resize ke ukuran fix
     * tertentu (kayak NTC 200x75mm) atau enggak. Default sekarang:
     * GAK di-resize, output per label ngikutin ukuran asli PDF sumber
     * (udah dikurangin $labelHeightTrimMm).
     */
    protected bool $forceOutputSize = false;
    protected float $outputPageWidthMm = 100;
    protected float $outputPageHeightMm = 40;

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
        $matched = 0;
        $unmatchedNoText = 0;
        $unmatchedNoExtract = 0;
        $unmatchedNoMaster = 0;
        $unmatchedNoRack = 0;
        $unmatchedDetails = [];

        /** @var array<int, array<string, mixed>> $records */
        $records = [];
        $seq = 0;

        /** @var array<int, array{resolved: string, original: string}> $sourceFiles */
        $sourceFiles = [];

        // ============================================================
        // FASE 1 — SCAN: kumpulin semua label dari semua file dulu,
        // belum render apa-apa.
        // ============================================================
        foreach ($sources as $sourceIdx => $source) {
            $originalSourcePath = $source['path'];
            $originalFilename = $source['original_filename'] ?? basename($originalSourcePath);

            $resolvedSourcePath = $this->ensureReadable($originalSourcePath);
            $sourceFiles[$sourceIdx] = [
                'resolved' => $resolvedSourcePath,
                'original' => $originalSourcePath,
            ];

            // Fpdi dipake cuma buat ngitung jumlah halaman sumber.
            $counter = new Fpdi();
            $sourcePageCount = $counter->setSourceFile($resolvedSourcePath);

            $labelTextsPerPage = $this->extractLabelTexts($resolvedSourcePath, $sourcePageCount);

            $fullFileText = $this->extractFullText($resolvedSourcePath);
            $suratJalanNo = $this->extractSuratJalanNo($fullFileText);

            for ($pageNo = 1; $pageNo <= $sourcePageCount; $pageNo++) {
                $chunks = $labelTextsPerPage[$pageNo - 1] ?? [];

                for ($i = 0; $i < $this->labelsPerPage; $i++) {
                    $labelText = $chunks[$i] ?? '';
                    $rackNo = null;
                    $partNo = null;
                    $partNoMatched = null;
                    $kategori = null;

                    if (trim($labelText) === '') {
                        $unmatchedNoText++;
                        $unmatchedDetails[] = [
                            'source' => $originalFilename, 'page' => $pageNo, 'label_index' => $i,
                            'part_no' => null, 'reason' => 'no_text',
                        ];
                    } else {
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
                            $addressFji = AddressFji::where('part_no', $partNo)->first();

                            if (!$addressFji) {
                                $unmatchedNoMaster++;
                                $unmatchedDetails[] = [
                                    'source' => $originalFilename, 'page' => $pageNo, 'label_index' => $i,
                                    'part_no' => $partNo, 'reason' => 'no_master',
                                ];
                            } else {
                                // kategori diambil selama part ada di master,
                                // meskipun rack-nya kosong
                                $kategori = $addressFji->kategori ?: null;

                                if (!$addressFji->rack_no) {
                                    $unmatchedNoRack++;
                                    $unmatchedDetails[] = [
                                        'source' => $originalFilename, 'page' => $pageNo, 'label_index' => $i,
                                        'part_no' => $partNo, 'reason' => 'no_rack',
                                    ];
                                } else {
                                    $rackNo = $addressFji->rack_no;
                                    $partNoMatched = $addressFji->part_no;
                                    $matched++;
                                }
                            }
                        }
                    }

                    $records[] = [
                        'seq'             => $seq++, // urutan asli, buat tie-breaker
                        'source_idx'      => $sourceIdx,
                        'source_filename' => $originalFilename,
                        'page'            => $pageNo,
                        'index'           => $i,
                        'surat_jalan_no'  => $suratJalanNo,
                        'rack_no'         => $rackNo,
                        'kategori'        => $kategori,
                        'part_no'         => $partNoMatched,
                        'part_no_raw'     => $partNo,
                    ];
                }
            }
        }

        // ============================================================
        // SORT — surat jalan ASC, lalu rack no DESC.
        // Null (tanpa surat jalan / tanpa rack) selalu di paling bawah.
        // ============================================================
        usort($records, function (array $a, array $b) {
            // 1) surat jalan
            $sa = $a['surat_jalan_no'];
            $sb = $b['surat_jalan_no'];

            if ($sa !== $sb) {
                if ($sa === null) return 1;
                if ($sb === null) return -1;

                $c = $this->sortSuratJalanAsc ? strnatcmp($sa, $sb) : strnatcmp($sb, $sa);
                if ($c !== 0) return $c;
            }

            // 2) rack no (abjad alamat rak, natural: F2 < F10)
            $ra = $a['rack_no'];
            $rb = $b['rack_no'];

            if ($ra !== $rb) {
                if ($ra === null) return 1;
                if ($rb === null) return -1;

                $c = $this->sortRackDesc ? strnatcasecmp($rb, $ra) : strnatcasecmp($ra, $rb);
                if ($c !== 0) return $c;
            }

            // 3) sama persis -> ikut urutan asli
            return $a['seq'] <=> $b['seq'];
        });

        // ============================================================
        // FASE 2 — RENDER sesuai urutan yang udah di-sort.
        // ============================================================
        $pdf = new Fpdi('P', 'pt');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);

        $labels = [];
        $outputIndex = 0;

        /** cache template per (file, halaman) biar gak import berulang */
        $templates = [];

        $marginTopPt    = $this->sourceMarginTopMm * self::MM_TO_PT;
        $marginBottomPt = $this->sourceMarginBottomMm * self::MM_TO_PT;
        $trimPt         = $this->labelHeightTrimMm * self::MM_TO_PT;

        foreach ($records as $r) {
            $key = $r['source_idx'] . ':' . $r['page'];

            if (!isset($templates[$key])) {
                $pdf->setSourceFile($sourceFiles[$r['source_idx']]['resolved']);
                $tid = $pdf->importPage($r['page']);
                $templates[$key] = [
                    'id'   => $tid,
                    'size' => $pdf->getTemplateSize($tid),
                ];
            }

            $templateId = $templates[$key]['id'];
            $fullWidth  = $templates[$key]['size']['width'];
            $fullHeight = $templates[$key]['size']['height'];
            $i          = $r['index'];

            // labelHeight udah dikurangin trim -> dipake buat
            // stepping antar crop DAN tinggi output, biar gak ada
            // drift/sisa ruang kosong yang numpuk.
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

            $pdf->AddPage($orientation, [$outputWidthPt, $outputHeightPt]);

            $scaledTemplateWidth  = $fullWidth * $scaleX;
            $scaledTemplateHeight = $fullHeight * $scaleY;

            // pake $rawLabelHeight (BUKAN yang udah di-trim) buat
            // stepping posisi label ke-i di SUMBER; yang di-trim
            // cuma "jendela" tinggi output-nya aja.
            $yOffset = -(($topOffset + $i * $rawLabelHeight) * $scaleY);

            $topExtraMm = $this->topExtraMarginMmByIndex[$i] ?? 0;
            $yOffset += $topExtraMm * self::MM_TO_PT;

            $pdf->useTemplate($templateId, 0, $yOffset, $scaledTemplateWidth, $scaledTemplateHeight);

            if ($r['rack_no']) {
                $this->drawOverlay($pdf, $r['rack_no'], $outputWidthPt, $outputHeightPt, $i);
            }

            if ($r['kategori']) {
                $this->drawKategoriOverlay($pdf, $r['kategori'], $outputWidthPt, $outputHeightPt, $i);
            }

            $outputIndex++;

            $labels[] = [
                'output_page'     => $outputIndex,
                'source_filename' => $r['source_filename'],
                'surat_jalan_no'  => $r['surat_jalan_no'],
                'rack_no'         => $r['rack_no'],
                'kategori'        => $r['kategori'],
                'part_no'         => $r['part_no'],
                'part_no_raw'     => $r['part_no_raw'],
                'plant'           => $r['rack_no'] === null ? null : (stripos($r['rack_no'], 'K') === 0 ? 'Plant 2' : 'Plant 1'),
                'matched'         => (bool) $r['rack_no'],
            ];
        }

        $pdf->Output($outputPath, 'F');

        // Bersihin file hasil normalize (setelah semua selesai dirender)
        foreach ($sourceFiles as $sf) {
            if ($sf['resolved'] !== $sf['original']) {
                @unlink($sf['resolved']);
            }
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

        \Illuminate\Support\Facades\Log::info('KANBAN FJI SPLIT DONE', [
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
            \Illuminate\Support\Facades\Log::info('KANBAN FJI UNMATCHED - PART NO GAGAL DI-EXTRACT DARI TEKS', [
                'count' => count($noExtractPreviews),
                'previews' => $noExtractPreviews,
            ]);
        }

        if (!empty($noMasterPartNos)) {
            \Illuminate\Support\Facades\Log::info('KANBAN FJI UNMATCHED - PART NO GAK ADA DI MASTER', [
                'count' => count($noMasterPartNos),
                'part_no_list' => $noMasterPartNos,
            ]);
        }

        if (!empty($noRackPartNos)) {
            \Illuminate\Support\Facades\Log::info('KANBAN FJI UNMATCHED - RACK NO KOSONG DI MASTER', [
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

    protected function ensureReadable(string $sourcePath): string
    {
        try {
            $probe = new Fpdi();
            $probe->setSourceFile($sourcePath);
            return $sourcePath;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('KANBAN FJI PDF PERLU NORMALIZE', [
                'source' => $sourcePath,
                'error' => $e->getMessage(),
            ]);

            return app(PdfNormalizer::class)->normalize($sourcePath);
        }
    }

    /**
     * Tulis rack_no di posisi overlay PER INDEX label ($labelIndex,
     * 0-3). Koordinat pakai fraksi (0-1) dari lebar/tinggi label
     * output aktif.
     */
    protected function drawOverlay(Fpdi $pdf, string $rackNo, float $labelWidthPt, float $labelHeightPt, int $labelIndex): void
    {
        $xFraction = $this->overlayXFractionByIndex[$labelIndex] ?? 0.13;
        $yFraction = $this->overlayYFractionByIndex[$labelIndex] ?? 0.83;
        $wFraction = $this->overlayWidthFractionByIndex[$labelIndex] ?? 0.14;
        $hFraction = $this->overlayHeightFractionByIndex[$labelIndex] ?? 0.14;

        $x = $xFraction * $labelWidthPt;
        $y = $yFraction * $labelHeightPt;
        $w = $wFraction * $labelWidthPt;
        $h = $hFraction * $labelHeightPt;

        $pdf->SetFont($this->overlayFont, 'B', $this->overlayFontSize);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY($x, $y);
        $pdf->Cell($w, $h, $rackNo, 0, 0, $this->overlayAlign);
    }

    /**
     * Tulis kategori (mis. "D-40") di area kosong kanan Part Number
     * s/d QTY, PER INDEX label ($labelIndex, 0-3). Koordinat fraksi
     * (0-1) dari lebar/tinggi label output aktif.
     */
    protected function drawKategoriOverlay(Fpdi $pdf, string $kategori, float $labelWidthPt, float $labelHeightPt, int $labelIndex): void
    {
        $xFraction = $this->kategoriXFractionByIndex[$labelIndex] ?? 0.47;
        $yFraction = $this->kategoriYFractionByIndex[$labelIndex] ?? 0.40;
        $wFraction = $this->kategoriWidthFractionByIndex[$labelIndex] ?? 0.22;
        $hFraction = $this->kategoriHeightFractionByIndex[$labelIndex] ?? 0.26;

        $x = $xFraction * $labelWidthPt;
        $y = $yFraction * $labelHeightPt;
        $w = $wFraction * $labelWidthPt;
        $h = $hFraction * $labelHeightPt;

        $pdf->SetFont($this->kategoriFont, 'B', $this->kategoriFontSize);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY($x, $y);
        // valign 'M' biar teks di tengah kotak secara vertikal
        $pdf->Cell($w, $h, $kategori, 0, 0, $this->kategoriAlign, false, '', 0, false, 'T', 'M');
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

    protected function extractFullText(string $sourcePath): string
    {
        $textParser = new PdfTextParser();
        $document = $textParser->parseFile($sourcePath);

        $fullText = '';
        foreach ($document->getPages() as $page) {
            $fullText .= "\n" . $page->getText();
        }

        return $fullText;
    }

    protected function extractSuratJalanNo(string $fullFileText): ?string
    {
        if (preg_match($this->suratJalanNoPattern, $fullFileText, $m)) {
            return $m[1];
        }

        return null;
    }
}
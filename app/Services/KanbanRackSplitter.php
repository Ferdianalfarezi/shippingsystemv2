<?php

namespace App\Services;

use App\Models\AdmAddressv2;
use setasign\Fpdi\Tcpdf\Fpdi;
use Smalot\PdfParser\Parser as PdfTextParser;

class KanbanRackSplitter
{
    /** Jumlah kanban per halaman sumber (tersusun vertikal) */
    protected int $labelsPerPage;

    /**
     * Teks penanda awal tiap label (dipakai buat motong teks 1 halaman
     * jadi N bagian per label). Kalau template PDF beda, ganti ini.
     */
    protected string $labelAnchorText = 'ARRIVAL';

    /**
     * Regex buat narik "DN NO" (delivery note number) dari teks label,
     * mis. "DN4126060048037A". Beda sama part no (mis. "57036-BZ031-00")
     * - DN No ini dipake orang buat nyari fisik dokumen/kanban-nya,
     * bukan buat matching ke tabel AdmAddressv2. Sekarang JUGA dipake
     * buat GROUPING urutan output (lihat split()), selain buat search
     * di frontend.
     */
    protected string $dnNoPattern = '/\bDN\d{10,16}[A-Z]?\b/i';

    /**
     * Regex buat narik angka CYCLE dari header label. PENTING: di hasil
     * extract teks PDF, angka cycle & angka max-nya TIDAK nempel
     * langsung kayak "1/4" — ada teks kolom "PART CAT PACKAGING TYPE"
     * yang nyempil di antaranya (kadang juga dipisah newline), mis:
     *   1 PART CAT PACKAGING TYPE
     *   / 4
     * -> cycle = 1 (grup 1), max = 4. Makanya regex-nya WAJIB nyari
     * "<digit> ... PART CAT PACKAGING TYPE ... / <digit>", BUKAN cuma
     * "<digit>/<digit>" nempel (itu gak akan pernah match).
     */
    protected string $cyclePattern = '/(?<!\d)(\d{1,2})\s*(?:\r?\n)?\s*PART\s*CAT\s*PACKAGING\s*TYPE\s*(?:\r?\n)?\s*\/\s*(\d{1,2})(?!\d)/is';

    /**
     * Whitelist nama SHOP yang udah kekonfirmasi pernah muncul di PDF.
     * Dipake sebagai FALLBACK TERAKHIR kalau strategi extractShop() yang
     * lain gagal semua (baik strategi "nempel di depan DN NO" maupun
     * strategi "fraction + PART CAT"). Karena sekarang fallback ini
     * urutannya paling belakang, dia gak akan overrule SHOP asli lagi
     * (dulu bug-nya: fallback ini nyari stripos() di SELURUH teks label,
     * jadi kata "ENGINE" yang muncul di bagian WH ZONE/PART TYPE ikut
     * ke-match padahal itu bukan SHOP-nya).
     * TAMBAHIN nama shop baru ke sini kalau ketemu shop yang belum
     * ke-cover (cek log 'KANBAN SHOP TIDAK DIKENALI' di laravel.log).
     * Urutan gak masalah, tapi taro yang lebih PANJANG/SPESIFIK duluan
     * biar gak ke-match sebagian sama yang lebih pendek (mis. "NR" vs "NR - LINE1").
     */
    protected array $knownShops = [
        'NR - LINE1',
        'WA-LINE',
        'PL1-W2',
        'ASSY 2',
        'WELD2',
        'WELD1',
        'Weld1',
        'ENGINE',
        'NR',
    ];

    // ============================================================
    // ADJUSTABLE CONFIG — atur posisi & tampilan overlay di sini.
    // Koordinat (0,0) = pojok kiri atas TIAP label hasil crop
    // (bukan dari halaman A4 asli). Satuan: pt (sama kayak PDF asli).
    // ============================================================
    protected float $overlayX = 82;          // jarak dari kiri
    protected float $overlayY = 133;        // jarak dari atas (label tinggi ~204pt)
    protected float $overlayWidth = 235;    // lebar area teks
    protected float $overlayHeight = 12;    // tinggi area teks
    protected float $overlayFontSize = 15;
    protected string $overlayFont = 'helvetica';
    protected string $overlayLabel = '';   // prefix teks, kosongin kalau gak perlu
    protected bool $overlayDrawBackground = false; // true = kasih kotak putih dulu biar teks gak numpuk sama isi asli
    protected string $overlayBgColor = 'FFFFFF'; // hex tanpa '#'

   
    protected string $overlayTitleText = 'STEP ADDRESS';
    protected float $overlayTitleFontSize = 8;
    protected float $overlayTitleGap = 10; // jarak (pt, sebelum scale) antara baris judul & baris rack no

    protected bool $overlayShowPartNo = true;
    protected float $overlayPartNoX = 60;
    protected float $overlayPartNoY = 167;
    protected float $overlayPartNoWidth = 235;
    protected float $overlayPartNoHeight = 12;
    protected float $overlayPartNoFontSize = 10;
    protected string $overlayPartNoAlign = 'L'; // L / C / R

    /**
     * Margin atas & bawah di halaman SUMBER (dalam mm) yang mau dibuang
     * sebelum sisanya dibagi rata ke labelsPerPage.
     * Rumus: labelHeight = (tinggiHalaman - marginAtas - marginBawah) / labelsPerPage
     */
    protected float $sourceMarginTopMm = 5;
    protected float $sourceMarginBottomMm = 5;

    /**
     * Override manual (opsional). Kalau diisi, rumus margin di atas
     * DIABAIKAN dan pakai angka ini langsung. Biarin null kalau mau
     * pakai perhitungan margin otomatis.
     */
    protected ?float $customLabelHeight = null;
    protected ?float $customTopOffset = null;

    /**
     * UKURAN KERTAS FISIK label printer lo (dalam mm).
     * Semua halaman hasil split bakal di-FIX ke ukuran ini persis,
     * gak ngikutin ukuran hasil bagi otomatis dari PDF sumber lagi.
     * Ini yang bikin gap antar halaman jadi konsisten & gak ada yang kepotong.
     */
    protected float $outputPageWidthMm = 200;
    protected float $outputPageHeightMm = 75;
    // ============================================================

    /** 1mm dalam pt, dipakai buat convert ukuran kertas di atas */
    protected const MM_TO_PT = 72 / 25.4;

    public function __construct(int $labelsPerPage = 4)
    {
        $this->labelsPerPage = $labelsPerPage;
    }

    /**
     * Split PDF + cocokkan part no ke admadresses + overlay rack no +
     * URUTIN ulang halaman output berdasarkan CYCLE (paling luar), lalu
     * DN No (grouping di dalam 1 cycle), lalu rack_no (urutan antar &
     * dalam grup DN No).
     *
     * Urutan default:
     * 0. CYCLE (mis. dari fraction "1/4", "2/4", dst di header label) —
     *    SORT KEY PALING LUAR. Semua label cycle 1 keluar duluan,
     *    baru cycle 2, dst. Label yang cycle-nya gak ketemu ditaro
     *    paling belakang dari semua cycle.
     * 1. GROUPING BY DN NO (di dalam 1 cycle) — semua label yang punya
     *    DN No SAMA WAJIB nempel bersebelahan di output (gak boleh
     *    kepisah walau rack-nya beda), tapi TETEP gak akan nyampur
     *    sama label dari cycle lain. Label tanpa DN No dianggep grup
     *    isi 1 (sendiri-sendiri), behave kayak sebelumnya.
     * 2. Plant 1 (rack_no TIDAK diawali huruf "K") — diurutin DESCENDING
     *    (Z→A) biar pas dicetak, rack paling "kecil" (mis. A1) keluar
     *    PALING TERAKHIR dari printer -> jatuh di PALING ATAS tumpukan.
     * 3. Plant 2 (rack_no diawali huruf "K") — ditaro di PALING BELAKANG
     *    dari semua, urutannya sendiri juga descending kayak di atas.
     * 4. Yang gak ketemu rack_no-nya (unmatched) — ditaro paling belakang
     *    dari semua-semuanya, urutan asli (gak ada rack buat diurutin).
     *
     * Aturan #2-#4 di atas sekarang dipake DUA KALI: buat nentuin posisi
     * ANTAR grup DN No (pake rack "representative" tiap grup), dan buat
     * nentuin urutan item DI DALAM 1 grup DN No (kalau grup-nya isi > 1
     * label dengan rack beda-beda). Semuanya di-scope di dalam 1 cycle.
     *
     * DN No (5 digit terakhir, ASCENDING) sekarang jadi ACUAN UTAMA buat
     * urutan ANTAR grup di dalam groupNum yang sama (lihat uasort di
     * bawah) — sebelum ini urutan antar grup unmatched cuma ngikutin
     * urutan asli halaman sumber, jadinya DN No bisa acak (mis. DN...254
     * keluar duluan sebelum DN...253). Rack tetep dipake sebagai
     * tie-breaker kalau DN suffix-nya sama/null.
     *
     * @return array{
     *     output: string,
     *     total_labels: int,
     *     matched: int,
     *     unmatched: int,
     *     unmatched_no_text: int,
     *     unmatched_no_master: int,
     *     unmatched_no_rack: int,
     *     unmatched_details: array<int, array{page:int,label_index:int,part_no:?string,reason:string}>
     * }
     */
    public function split(string $sourcePath, string $outputPath): array
    {
        $pdf = new Fpdi('P', 'pt');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);

        $sourcePageCount = $pdf->setSourceFile($sourcePath);

        $labelTextsPerPage = $this->extractLabelTexts($sourcePath, $sourcePageCount);
        $extractor = new PartNoExtractor();

        $matched = 0;
        $unmatchedNoText = 0;
        $unmatchedNoExtract = 0;
        $unmatchedNoMaster = 0;
        $unmatchedNoRack = 0;
        $unmatchedDetails = [];
        $unknownShopPreviews = [];

        // ukuran output FIX (sama buat semua halaman)
        $outputWidthPt  = $this->outputPageWidthMm * self::MM_TO_PT;
        $outputHeightPt = $this->outputPageHeightMm * self::MM_TO_PT;

        // ============================================================
        // PASS 1 — ANALISA SEMUA LABEL DULU (belum nulis halaman PDF).
        // Import tiap halaman sumber sekali buat dapetin templateId +
        // faktor scale-nya, terus loop tiap slot label buat extract
        // part no / shop / rack no / cycle. Hasilnya ditampung di
        // $items, BELUM di-render, karena urutan render ditentuin
        // belakangan (pass 2) setelah semua rack_no diketahui.
        // ============================================================
        $pageMeta = []; // pageNo -> [templateId, scaleX, scaleY, labelHeight, topOffset, fullWidth, fullHeight]
        $items = [];    // list flat semua slot label, urutan asli sumber

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

            $pageMeta[$pageNo] = compact('templateId', 'scaleX', 'scaleY', 'labelHeight', 'topOffset', 'fullWidth', 'fullHeight');

            $chunks = $labelTextsPerPage[$pageNo - 1] ?? [];

            // DEBUG SEMENTARA: log teks mentah label pertama di halaman 1,
            // dengan whitespace dibikin keliatan. HAPUS kalau udah gak kepake.
            if ($pageNo === 1) {
                foreach ($chunks as $debugIdx => $debugText) {
                    $visible = str_replace(
                        ["\r\n", "\n", "\r", "\t", ' '],
                        ['[NL]', '[NL]', '[NL]', '[TAB]', '·'],
                        $debugText
                    );
                    \Illuminate\Support\Facades\Log::info('KANBAN DEBUG RAW TEXT', [
                        'page' => $pageNo,
                        'label_index' => $debugIdx,
                        'raw_visible' => $visible,
                    ]);
                }
            }

            for ($i = 0; $i < $this->labelsPerPage; $i++) {
                $labelText = $chunks[$i] ?? '';
                $shop = $this->extractShop($labelText);
                $dnNo = $this->extractDnNo($labelText);
                $cycle = $this->extractCycle($labelText);
                $rackNo = null;
                $partNo = null;
                $partNoMatched = null;

                if ($shop === null && trim($labelText) !== '') {
                    $unknownShopPreviews[] = mb_substr(trim(preg_replace('/\s+/', ' ', $labelText)), 0, 200);
                }

                if (trim($labelText) === '') {
                    $unmatchedNoText++;
                    $unmatchedDetails[] = [
                        'page' => $pageNo, 'label_index' => $i, 'part_no' => null, 'reason' => 'no_text',
                    ];
                } else {
                    $partNo = $extractor->extract($labelText);

                    if (!$partNo) {
                        $unmatchedNoExtract++;
                        $unmatchedDetails[] = [
                            'page' => $pageNo, 'label_index' => $i, 'part_no' => null, 'reason' => 'no_extract',
                            'text_preview' => mb_substr(trim(preg_replace('/\s+/', ' ', $labelText)), 0, 200),
                        ];
                    } else {
                        $stripped = trim($extractor->stripSuffix($partNo));
                        $admAddress = AdmAddressv2::where('part_no', $stripped)->first()
                            ?? AdmAddressv2::where('part_no', trim($partNo))->first();

                        if (!$admAddress) {
                            $unmatchedNoMaster++;
                            $unmatchedDetails[] = [
                                'page' => $pageNo, 'label_index' => $i, 'part_no' => $stripped, 'reason' => 'no_master',
                            ];
                        } elseif (!$admAddress->rack_no) {
                            $unmatchedNoRack++;
                            $unmatchedDetails[] = [
                                'page' => $pageNo, 'label_index' => $i, 'part_no' => $stripped, 'reason' => 'no_rack',
                            ];
                        } else {
                            $rackNo = $admAddress->rack_no;
                            $partNoMatched = $admAddress->part_no;
                            $matched++;
                        }
                    }
                }

                $items[] = [
                    'seq' => count($items), // urutan asli, dipake buat tie-break yang unmatched
                    'pageNo' => $pageNo,
                    'labelIndex' => $i,
                    'shop' => $shop,
                    'rackNo' => $rackNo,
                    'partNo' => $partNoMatched,
                    // teks part no MENTAH hasil extract dari PDF (sebelum di-strip
                    // suffix / dicocokin ke master) - dipake buat search di frontend
                    // biar tetep ketemu meski format part_no master beda dikit
                    // (mis. ada/gak ada suffix) sama teks yang beneran tercetak.
                    'partNoRaw' => $partNo,
                    'dnNo' => $dnNo,
                    // angka CYCLE (mis. dari "1/4") — SORT KEY PALING LUAR,
                    // null kalau fraction-nya gak ketemu di teks label.
                    'cycle' => $cycle,
                    'plant' => $rackNo === null ? null : (stripos($rackNo, 'K') === 0 ? 'Plant 2' : 'Plant 1'),
                ];
            }
        }

        $totalLabels = count($items);

        // ============================================================
        // GROUPING BY CYCLE (paling luar) — semua label dengan CYCLE
        // sama dikelompokin & diurutin duluan (1,2,3,4,...). Item yang
        // cycle-nya gak ketemu (null) ditaro PALING BELAKANG dari
        // semua cycle group yang ketemu.
        // ============================================================
        $cycleGroups = [];
        foreach ($items as $item) {
            $cycleKey = $item['cycle'] !== null ? $item['cycle'] : 'null';
            $cycleGroups[$cycleKey][] = $item;
        }

        uksort($cycleGroups, function ($a, $b) {
            if ($a === 'null' && $b === 'null') {
                return 0;
            }
            if ($a === 'null') {
                return 1;
            }
            if ($b === 'null') {
                return -1;
            }
            return $a <=> $b;
        });

        $items = [];

        foreach ($cycleGroups as $cycleItems) {
            // ============================================================
            // GROUPING BY DN NO — di dalam 1 cycle, label yang DN No-nya
            // sama WAJIB nempel bersebelahan di output (1 DN No bisa
            // punya beberapa part/kanban dengan rack beda-beda, tapi
            // tetep pengen dicetak berurutan biar gak kepisah-pisah pas
            // ambil dokumennya). Item TANPA DN No diperlakukan
            // sendiri-sendiri (grup isi 1), sama kayak behavior lama
            // (gak dipaksa nempel ke apapun). Scope-nya cuma di dalem
            // cycle ini aja — gak akan nyampur sama item dari cycle lain.
            // ============================================================
            $dnGroups = [];
            foreach ($cycleItems as $item) {
                $key = ($item['dnNo'] !== null && $item['dnNo'] !== '')
                    ? 'dn:' . $item['dnNo']
                    : 'single:' . $item['seq'];
                $dnGroups[$key][] = $item;
            }

            foreach ($dnGroups as $key => $groupItems) {
                // posisi grup (antar-grup) ikut yang PALING matched di antara
                // member-nya: 0=Plant1, 1=Plant2, 2=unmatched. Jadi grup yang
                // ada minimal 1 label matched gak ketutup di belakang bareng
                // grup yang bener-bener semuanya unmatched.
                $groupNum = min(array_map(fn ($it) => $this->sortGroupFor($it['rackNo']), $groupItems));

                // rack "representative" buat urutin ANTAR grup: rack TERBESAR
                // (natural sort) di antara member grup yang punya rack.
                // Kalau nanti ternyata DN No sama selalu 1 rack aja, ini otomatis
                // sama dengan rack satu-satunya itu.
                $repRack = null;
                foreach ($groupItems as $it) {
                    if ($it['rackNo'] === null || $it['rackNo'] === '') {
                        continue;
                    }
                    if ($repRack === null || strnatcasecmp($it['rackNo'], $repRack) > 0) {
                        $repRack = $it['rackNo'];
                    }
                }

                // 5 digit terakhir DN No grup ini — dipake sebagai ACUAN UTAMA
                // urutan antar grup (lihat uasort di bawah). Semua member 1
                // grup punya DN No yang sama (kecuali grup "single:" yang emang
                // gak punya DN No -> null), jadi cukup ambil dari item pertama.
                $dnSuffix = $this->extractDnSuffix($groupItems[0]['dnNo'] ?? null);

                $minSeq = min(array_map(fn ($it) => $it['seq'], $groupItems));

                // urutin item DI DALAM grup pake logic rack yang sama kayak
                // urutan lama (buat kasus 1 DN No isinya beberapa rack beda)
                usort($groupItems, function (array $a, array $b) {
                    $ga = $this->sortGroupFor($a['rackNo']);
                    $gb = $this->sortGroupFor($b['rackNo']);
                    if ($ga !== $gb) {
                        return $ga <=> $gb;
                    }
                    if ($ga === 2) {
                        return $a['seq'] <=> $b['seq'];
                    }
                    return strnatcasecmp($b['rackNo'], $a['rackNo']);
                });

                $dnGroups[$key] = compact('groupItems', 'groupNum', 'repRack', 'minSeq', 'dnSuffix');
            }

            // urutin ANTAR grup DN No.
            // 1. DN No (5 digit terakhir) ASCENDING — SEKARANG JADI ACUAN
            //    PALING UTAMA (di atas groupNum/rack), soalnya ini yang
            //    paling nyerminin urutan fisik di dokumen sumber (yang
            //    emang udah ke-generate urut per DN No: 253, 254, 255, ...).
            //    PENTING: ini sengaja ditaro DI ATAS groupNum. Sebelumnya
            //    groupNum (Plant1/Plant2) dicek duluan, jadi kalau 1 grup
            //    DN kebetulan punya member match ke Plant1 (groupNum lebih
            //    kecil) sementara grup DN lain semua match ke Plant2 doang,
            //    DN suffix-nya gak kepake sama sekali & urutan DN jadi
            //    keliru (mis. DN254 nyodok duluan drpd DN253 gara2 DN254
            //    ada 1-2 item yg nyasar ke rack Plant1).
            // 2. groupNum (Plant1/Plant2/unmatched) & rack (descending)
            //    cuma dipake sebagai tie-breaker kalau DN suffix-nya
            //    sama atau null (grup "single:" tanpa DN No).
            // 3. minSeq (urutan asli) fallback paling akhir.
            uasort($dnGroups, function (array $a, array $b) {
                if ($a['dnSuffix'] !== null && $b['dnSuffix'] !== null && $a['dnSuffix'] !== $b['dnSuffix']) {
                    return $a['dnSuffix'] <=> $b['dnSuffix'];
                }
                if ($a['groupNum'] !== $b['groupNum']) {
                    return $a['groupNum'] <=> $b['groupNum'];
                }
                if ($a['groupNum'] === 2) {
                    return $a['minSeq'] <=> $b['minSeq'];
                }
                if ($a['repRack'] === null || $b['repRack'] === null) {
                    return $a['minSeq'] <=> $b['minSeq'];
                }
                return strnatcasecmp($b['repRack'], $a['repRack']);
            });

            // flatten balik ke $items — DN No sama udah nempel bersebelahan
            // di dalem cycle ini, urutan antar grup & di dalam grup tetep
            // ngikutin aturan DN No -> rack_no
            foreach ($dnGroups as $group) {
                foreach ($group['groupItems'] as $it) {
                    $items[] = $it;
                }
            }
        }

        // ============================================================
        // PASS 2 — render halaman PDF sesuai urutan $items yang baru,
        // pake templateId & faktor scale yang udah disiapin di pass 1.
        // ============================================================
        $labels = []; // metadata final per halaman OUTPUT, sinkron urutan render

        foreach ($items as $outputIndex => $item) {
            $meta = $pageMeta[$item['pageNo']];

            $pdf->AddPage('L', [$outputWidthPt, $outputHeightPt]);

            $scaledTemplateWidth  = $meta['fullWidth'] * $meta['scaleX'];
            $scaledTemplateHeight = $meta['fullHeight'] * $meta['scaleY'];
            $yOffset = -(($meta['topOffset'] + $item['labelIndex'] * $meta['labelHeight']) * $meta['scaleY']);

            $pdf->useTemplate($meta['templateId'], 0, $yOffset, $scaledTemplateWidth, $scaledTemplateHeight);

            if ($item['rackNo']) {
                $this->drawOverlay($pdf, $item['rackNo'], $item['partNo'] ?? null, $meta['scaleX'], $meta['scaleY']);
            }

            $labels[] = [
                'output_page' => $outputIndex + 1,
                'shop' => $item['shop'],
                'rack_no' => $item['rackNo'],
                'part_no' => $item['partNo'],
                'part_no_raw' => $item['partNoRaw'] ?? null,
                'dn_no' => $item['dnNo'] ?? null,
                'cycle' => $item['cycle'] ?? null,
                'plant' => $item['plant'],
                'matched' => (bool) $item['rackNo'],
            ];
        }

        $pdf->Output($outputPath, 'F');

        $unmatched = $unmatchedNoText + $unmatchedNoExtract + $unmatchedNoMaster + $unmatchedNoRack;

        // list part no yang gak ketemu di master / gak ada rack_no,
        // biar gampang di-grep dari log tanpa perlu buka PDF satu-satu
        $noMasterPartNos = array_values(array_unique(array_column(
            array_filter($unmatchedDetails, fn ($d) => $d['reason'] === 'no_master'),
            'part_no'
        )));
        $noRackPartNos = array_values(array_unique(array_column(
            array_filter($unmatchedDetails, fn ($d) => $d['reason'] === 'no_rack'),
            'part_no'
        )));
        $noExtractPreviews = array_values(array_map(
            fn ($d) => "page {$d['page']} idx {$d['label_index']}: {$d['text_preview']}",
            array_filter($unmatchedDetails, fn ($d) => $d['reason'] === 'no_extract')
        ));

        \Illuminate\Support\Facades\Log::info('KANBAN SPLIT DONE', [
            'output' => $outputPath,
            'total_labels' => $totalLabels,
            'matched' => $matched,
            'unmatched' => $unmatched,
            'unmatched_no_text' => $unmatchedNoText,
            'unmatched_no_extract' => $unmatchedNoExtract,
            'unmatched_no_master' => $unmatchedNoMaster,
            'unmatched_no_rack' => $unmatchedNoRack,
        ]);

        if (!empty($noExtractPreviews)) {
            \Illuminate\Support\Facades\Log::info('KANBAN UNMATCHED - PART NO GAGAL DI-EXTRACT DARI TEKS', [
                'count' => count($noExtractPreviews),
                'previews' => $noExtractPreviews,
            ]);
        }

        if (!empty($noMasterPartNos)) {
            \Illuminate\Support\Facades\Log::info('KANBAN UNMATCHED - PART NO GAK ADA DI MASTER', [
                'count' => count($noMasterPartNos),
                'part_no_list' => $noMasterPartNos,
            ]);
        }

        if (!empty($noRackPartNos)) {
            \Illuminate\Support\Facades\Log::info('KANBAN UNMATCHED - RACK NO KOSONG DI MASTER', [
                'count' => count($noRackPartNos),
                'part_no_list' => $noRackPartNos,
            ]);
        }

        if (!empty($unknownShopPreviews)) {
            \Illuminate\Support\Facades\Log::info('KANBAN SHOP TIDAK DIKENALI', [
                'count' => count($unknownShopPreviews),
                'previews' => array_slice(array_values(array_unique($unknownShopPreviews)), 0, 20),
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
     * Grup pengurutan buat 1 item berdasarkan rack_no-nya:
     * 0 = Plant 1 (rack_no ada, gak diawali "K")
     * 1 = Plant 2 (rack_no ada, diawali "K")
     * 2 = unmatched (rack_no null/gak ketemu) -> selalu paling belakang
     */
    protected function sortGroupFor(?string $rackNo): int
    {
        if ($rackNo === null || $rackNo === '') {
            return 2;
        }

        return stripos($rackNo, 'K') === 0 ? 1 : 0;
    }

    /**
     * Ambil nilai SHOP dari teks 1 label. Coba 3 strategi, urut dari
     * yang paling reliable ke yang paling "nebak":
     *
     * 1. PALING RELIABLE — SHOP selalu nempel PERSIS sebelum nomor DN
     *    asli di header label, mis:
     *        SHOP DN NO
     *        ASSY4 DN5126070083369A
     *    Jadi tinggal ambil token sebelum "DN" + belasan digit. Ini gak
     *    kepengaruh sama isi kolom lain (PART TYPE / WH ZONE) yang
     *    kadang kebetulan ngandung nama shop lain (mis. "ENGINE" di
     *    WH ZONE meski SHOP aslinya "ASSY4").
     * 2. FALLBACK — buat template lain (mis. Kanban ADM lama) yang gak
     *    punya header "SHOP DN NO" eksplisit: cari pecahan CYCLE (mis.
     *    "1/4", "1/ 2") lalu ambil teks sampai ketemu keyword "PART CAT"
     *    (anchor keyword, BUKAN jumlah spasi — soalnya smalot/pdfparser
     *    kadang collapse spasi ganda jadi 1 spasi atau bahkan 0 spasi
     *    antar kolom yang jauh).
     * 3. LAST RESORT — scan apakah teks label MENGANDUNG salah satu nama
     *    shop di $knownShops. Ini paling gampang salah (bisa ke-trigger
     *    sama kata yang muncul di kolom lain), makanya SENGAJA ditaro
     *    PALING BELAKANG dan cuma dipake kalau strategi 1 & 2 dua-duanya
     *    gagal.
     *
     * Return null kalau semua strategi gagal (bakal ke-log biar bisa
     * ditambahin ke $knownShops atau di-investigate lebih lanjut).
     */
    protected function extractShop(string $labelText): ?string
    {
        // Strategi 1: token yang nempel PERSIS sebelum "DN" + 10-16 digit
        // (boleh dipisah newline/spasi karena hasil extract PDF suka
        // ngasih newline antar baris/kolom).
        if (preg_match('/([A-Z][A-Z0-9\-]{1,9})\s*\r?\n?\s*DN\d{10,16}[A-Z]?\b/i', $labelText, $matches)) {
            $shop = strtoupper(trim($matches[1]));
            // jaga-jaga: jangan sampe kata "NO" (dari header "SHOP DN NO")
            // ke-tangkep kalau urutan kolomnya kebalik di beberapa PDF
            if ($shop !== '' && $shop !== 'NO' && $shop !== 'DN') {
                return $shop;
            }
        }

        // Strategi 2: fraction CYCLE + PART CAT (dukung juga "1/ 2" yang
        // ada spasi setelah slash)
        if (preg_match('/(?<!\d)\d{1,2}\/\s*\d{1,2}(?!\d)\s*(.+?)\s*PART\s*CAT/is', $labelText, $matches)) {
            $shop = trim(preg_replace('/\s+/', ' ', $matches[1]));
            if ($shop !== '') {
                return $shop;
            }
        }

        // Strategi 3: whitelist, HANYA sebagai last resort
        foreach ($this->knownShops as $knownShop) {
            if (stripos($labelText, $knownShop) !== false) {
                return $knownShop;
            }
        }

        return null;
    }

    /**
     * Ambil "DN NO" (delivery note number) dari teks 1 label, mis.
     * "DN4126060048037A". Dipake buat: (1) keperluan search di frontend,
     * dan (2) GROUPING urutan output di split() — label dengan DN No
     * sama dipaksa nempel bersebelahan (di dalem cycle yang sama). Gak
     * dipake buat matching ke master AdmAddressv2 (beda field sama part no).
     */
    protected function extractDnNo(string $labelText): ?string
    {
        if (preg_match($this->dnNoPattern, $labelText, $matches)) {
            return strtoupper($matches[0]);
        }

        return null;
    }

    /**
     * Ambil 5 digit TERAKHIR dari bagian numeric DN No, mis.
     * "DN4126080008253A" -> "4126080008253" -> "08253" -> 8253 (int).
     * Dipake sebagai SORT KEY UTAMA buat urutan ANTAR grup DN No (lihat
     * uasort di split()) — biar dokumen fisik keurut dari nomor DN
     * terkecil ke terbesar, bukan ngikutin urutan halaman sumber PDF
     * (yang bisa acak/gak berurutan).
     * Return null kalau DN No-nya kosong atau digit-nya kurang dari 5
     * (gak cukup buat diambil 5 digit terakhirnya).
     */
    protected function extractDnSuffix(?string $dnNo): ?int
    {
        if ($dnNo === null || $dnNo === '') {
            return null;
        }

        // buang semua yang bukan digit (prefix "DN" & suffix huruf mis. "A")
        $digits = preg_replace('/\D/', '', $dnNo);

        if ($digits === '' || strlen($digits) < 5) {
            return null;
        }

        return (int) substr($digits, -5);
    }

    /**
     * Ambil nomor CYCLE dari teks 1 label, mis. teks yang ngandung
     * "1/4" bakal balikin int(1). Dipake buat SORT KEY UTAMA (paling
     * luar) — semua label dengan cycle sama dikelompokin bareng dan
     * cycle kecil (1) keluar duluan, sebelum cycle besar (2,3,4,...).
     * Item tanpa cycle (gak ketemu fraction-nya) ditaro PALING
     * BELAKANG dari semua cycle group.
     */
    protected function extractCycle(string $labelText): ?int
    {
        if (preg_match($this->cyclePattern, $labelText, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * Tulis teks rack no (+ judul "STEP ADDRESS" di atasnya, + part no
     * di bawahnya) di halaman yang lagi aktif. Posisi/style diambil dari
     * properti overlay* di atas.
     */
    protected function drawOverlay(Fpdi $pdf, string $rackNo, ?string $partNo, float $scaleX = 1, float $scaleY = 1): void
    {
        $x = $this->overlayX * $scaleX;
        $y = $this->overlayY * $scaleY;
        $w = $this->overlayWidth * $scaleX;
        $h = $this->overlayHeight * $scaleY;
        $fontSize = $this->overlayFontSize * $scaleY;

        // baris judul "STEP ADDRESS" ditaruh $overlayTitleGap (pt, sebelum
        // scale) di ATAS baris rack no -> makanya y-nya dikurangin (y=0 di atas)
        $titleFontSize = $this->overlayTitleFontSize * $scaleY;
        $titleY = $y - ($this->overlayTitleGap * $scaleY);

        // baris part no PISAH total dari rack no -> posisi absolut sendiri,
        // gak lagi ngikut $y (bebas mau digeser ke mana aja).
        $showPartNo = $this->overlayShowPartNo && !empty($partNo);
        $pnX = $this->overlayPartNoX * $scaleX;
        $pnY = $this->overlayPartNoY * $scaleY;
        $pnW = $this->overlayPartNoWidth * $scaleX;
        $pnH = $this->overlayPartNoHeight * $scaleY;
        $pnFontSize = $this->overlayPartNoFontSize * $scaleY;

        // background rack-no/title (tetep cuma nge-cover area rack no + title,
        // BUKAN area part no — soalnya part no sekarang posisinya bebas dan
        // bisa aja gak nempel/bertumpuk sama area ini)
        if ($this->overlayDrawBackground) {
            $bgY = $this->overlayTitleText !== '' ? $titleY : $y;
            $bgH = $this->overlayTitleText !== '' ? ($h + ($this->overlayTitleGap * $scaleY)) : $h;

            $pdf->SetFillColor(
                hexdec(substr($this->overlayBgColor, 0, 2)),
                hexdec(substr($this->overlayBgColor, 2, 2)),
                hexdec(substr($this->overlayBgColor, 4, 2))
            );
            $pdf->Rect($x, $bgY, $w, $bgH, 'F');
        }

        if ($this->overlayTitleText !== '') {
            $pdf->SetFont($this->overlayFont, '', $titleFontSize);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetXY($x, $titleY);
            $pdf->Cell($w, $this->overlayTitleGap * $scaleY, $this->overlayTitleText, 0, 0, 'L');
        }

        $pdf->SetFont($this->overlayFont, 'B', $fontSize);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY($x, $y);
        $pdf->Cell($w, $h, $this->overlayLabel . $rackNo, 0, 0, 'L');

        if ($showPartNo) {
            $pdf->SetFont($this->overlayFont, '', $pnFontSize);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetXY($pnX, $pnY);
            $pdf->Cell($pnW, $pnH, $partNo, 0, 0, $this->overlayPartNoAlign);
        }
    }

    /**
     * Ambil teks per halaman dari PDF sumber, lalu pecah jadi N chunk
     * (1 chunk = 1 label) berdasarkan kemunculan $labelAnchorText.
     *
     * @return array<int, array<int, string>> index halaman -> array chunk teks per label
     */
    protected function extractLabelTexts(string $sourcePath, int $pageCount): array
    {
        $textParser = new PdfTextParser();
        $document = $textParser->parseFile($sourcePath);
        $pages = $document->getPages();

        $result = [];

        foreach ($pages as $pageIndex => $page) {
            $fullText = $page->getText();

            // pecah teks jadi N bagian berdasarkan anchor (mis. "ARRIVAL")
            $parts = preg_split('/(?=' . preg_quote($this->labelAnchorText, '/') . ')/', $fullText);

            // buang chunk "sampah" yang gak beneran diawali anchor
            // (misal sisa teks kolom kanan yang nempel sebelum ARRIVAL pertama)
            $parts = array_values(array_filter($parts, function ($p) {
                return str_starts_with(ltrim($p), $this->labelAnchorText);
            }));

            $result[$pageIndex] = $parts;
        }

        return $result;
    }
}
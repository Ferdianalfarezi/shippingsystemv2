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
     * bukan buat matching ke tabel AdmAddressv2. Dipake buat search
     * di frontend & ditampilkan di metadata, DAN sekarang juga dipake
     * buat GROUPING sebelum sorting rack_no (lihat sortItemsByDnGroup()).
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
     * Split PDF + cocokkan part no ke admadresses + overlay rack no.
     *
     * URUTAN OUTPUT: pada dasarnya ngikutin urutan halaman & posisi
     * label di PDF SUMBER (halaman 1 label 1,2,3,4, lanjut halaman 2
     * label 1,2,3,4, dst) — TAPI dengan satu pengecualian:
     *
     * Di dalam label-label yang punya DN No SAMA, urutannya di-SORT
     * berdasarkan rack_no DESCENDING (dari yang "terbesar" ke
     * "terkecil" secara string, mis. K3-39-A > C3-07-A > C2-39-A >
     * C2-36-A). Urutan ANTAR grup DN No sendiri tetap ngikutin
     * kemunculan pertama grup itu di PDF sumber (gak diacak). Label
     * yang gak punya DN No (null) dianggap grup sendiri-sendiri (gak
     * ikut campur ke grup lain), dan di dalam satu grup label yang
     * rack_no-nya null (unmatched) ditaro di akhir grup, urutan asli
     * dipertahanin. Lihat sortItemsByDnGroup().
     *
     * Metadata (shop, rack_no, part_no, dn_no, cycle) TETAP diekstrak &
     * dikembalikan di $labels buat keperluan search/filter/display di
     * frontend.
     *
     * PDF sumber yang strukturnya pakai cross-reference stream /
     * object stream terkompresi (gak disupport parser FPDI gratis
     * maupun Smalot/pdfparser) otomatis di-normalize dulu pakai qpdf
     * lewat ensureReadable() sebelum diproses. Lihat PdfNormalizer.
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
        $originalSourcePath = $sourcePath;
        $sourcePath = $this->ensureReadable($sourcePath);

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
        // $items, urutannya PERSIS urutan asli sumber (halaman lalu
        // label_index). Urutan ini kemudian di-adjust dikit lewat
        // sortItemsByDnGroup() sebelum dipake di PASS 2 (lihat bawah).
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
                    'seq' => count($items), // urutan asli
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
                    // angka CYCLE (mis. dari "1/4") — cuma buat metadata/display,
                    // GAK dipake buat sorting lagi.
                    'cycle' => $cycle,
                    'plant' => $rackNo === null ? null : (stripos($rackNo, 'K') === 0 ? 'Plant 2' : 'Plant 1'),
                ];
            }
        }

        $totalLabels = count($items);

        // Sort: di dalam grup DN No yang sama, urutin rack_no descending.
        // Urutan antar grup DN No tetap ngikut kemunculan pertama di
        // sumber. Lihat docblock sortItemsByDnGroup().
        $items = $this->sortItemsByDnGroup($items);

        // ============================================================
        // PASS 2 — render halaman PDF sesuai urutan $items (= urutan
        // hasil sortItemsByDnGroup di atas), pake templateId & faktor
        // scale dari pass 1.
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

        // bersihin file temp hasil normalize qpdf (kalau memang sempat dipakai)
        if ($sourcePath !== $originalSourcePath) {
            @unlink($sourcePath);
        }

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
     * Coba baca PDF sumber langsung pakai FPDI. Kalau gagal karena
     * struktur/kompresi yang gak disupport parser FPDI gratis (mis.
     * cross-reference stream, object stream terkompresi), normalize
     * dulu pakai qpdf (via PdfNormalizer) dan pakai hasil normalize-nya
     * sebagai source untuk SISA proses split() (baik FPDI maupun
     * Smalot/pdfparser di extractLabelTexts()).
     *
     * Return path file yang aman dipakai (bisa sama dengan $sourcePath
     * asli kalau memang gak bermasalah, atau path file temp hasil
     * normalize kalau tadinya bermasalah).
     */
    protected function ensureReadable(string $sourcePath): string
    {
        try {
            $probe = new Fpdi();
            $probe->setSourceFile($sourcePath);
            return $sourcePath; // aman, gak perlu normalize
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('KANBAN PDF PERLU NORMALIZE', [
                'source' => $sourcePath,
                'error' => $e->getMessage(),
            ]);

            return app(PdfNormalizer::class)->normalize($sourcePath);
        }
    }

    /**
     * Sort $items (flat, urutan asli PDF sumber) supaya di dalam
     * label-label yang punya DN No SAMA, urutannya jadi rack_no
     * DESCENDING. Contoh (dalam 1 grup DN No yang sama):
     *
     *   input:  C2-36-A, C3-07-A, C2-39-A, K3-39-A
     *   output: K3-39-A, C3-07-A, C2-39-A, C2-36-A
     *
     * Aturan:
     * - Grouping berdasarkan 'dnNo'. Item dengan dnNo NULL dianggap
     *   grup sendiri per-item (gak digabung ke grup lain), jadi
     *   posisinya gak berubah relatif terhadap item ber-dnNo.
     * - Urutan ANTAR grup (DN No mana duluan) ngikut posisi
     *   kemunculan PERTAMA grup itu di $items asli — jadi PDF secara
     *   garis besar tetep jalan sesuai urutan sumbernya, cuma di
     *   dalam 1 DN No aja yang di-reorder.
     * - Di dalam grup, item yang rackNo-nya NULL (unmatched / gak ada
     *   di master) ditaro di AKHIR grup, urutan asli antar-mereka
     *   dipertahankan (stable), karena gak ada rackNo buat dibandingin.
     * - Perbandingan rackNo pakai strcmp() dibalik (descending),
     *   cukup buat format rack no yang konsisten (mis. "C2-36-A",
     *   "K3-39-A"). Kalau nanti ketemu format rack yang beda panjang
     *   digit/format-nya dan hasil sortnya jadi aneh, kasih tau biar
     *   diganti ke natural-sort (strnatcmp) atau logic parsing custom.
     *
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    protected function sortItemsByDnGroup(array $items): array
    {
        // 1. Kelompokkan item ke grup, sambil catat urutan kemunculan
        //    pertama tiap grup (biar urutan antar-grup gak berubah).
        $groups = [];       // groupKey -> array of items
        $groupOrder = [];   // list groupKey sesuai urutan kemunculan pertama

        foreach ($items as $index => $item) {
            $dnNo = $item['dnNo'] ?? null;

            // item tanpa DN No = grup sendiri (pakai index unik biar
            // gak ke-gabung sama item lain yang juga null dnNo-nya)
            $groupKey = $dnNo !== null && $dnNo !== '' ? ('dn:' . $dnNo) : ('solo:' . $index);

            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = [];
                $groupOrder[] = $groupKey;
            }

            $groups[$groupKey][] = $item;
        }

        // 2. Di dalam tiap grup, sort rackNo descending (null di akhir,
        //    stable buat item yang rackNo-nya sama/null).
        foreach ($groups as $groupKey => $groupItems) {
            usort($groupItems, function ($a, $b) {
                $rackA = $a['rackNo'] ?? null;
                $rackB = $b['rackNo'] ?? null;

                if ($rackA === null && $rackB === null) {
                    return 0; // biarin usort jaga stability relatif (PHP 8 usort stable)
                }
                if ($rackA === null) {
                    return 1; // null selalu di belakang
                }
                if ($rackB === null) {
                    return -1;
                }

                return strcmp($rackB, $rackA); // descending
            });

            $groups[$groupKey] = $groupItems;
        }

        // 3. Flatten balik sesuai urutan grup asli, lalu reindex 'seq'.
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
     * "DN4126060048037A". Dipake buat keperluan search/metadata di
     * frontend, DAN buat grouping di sortItemsByDnGroup(). Gak dipake
     * buat matching ke master AdmAddressv2 (beda field sama part no).
     */
    protected function extractDnNo(string $labelText): ?string
    {
        if (preg_match($this->dnNoPattern, $labelText, $matches)) {
            return strtoupper($matches[0]);
        }

        return null;
    }

    /**
     * Ambil nomor CYCLE dari teks 1 label, mis. teks yang ngandung
     * "1/4" bakal balikin int(1). Sekarang cuma buat metadata/display
     * di $labels, GAK dipake buat sorting output lagi.
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
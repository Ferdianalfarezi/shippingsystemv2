<?php

namespace App\Http\Controllers;

use App\Models\Kanbanadm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Admaddress;
use App\Models\ArsAdm;
use App\Models\AdmRunout;

class KanbanadmController extends Controller
{
    public function index()
    {
        $kanbanadms = Kanbanadm::orderBy('shop_code')->orderBy('id')->get();

        $latestUpload = Kanbanadm::whereNotNull('uploaded_by')
            ->orderByDesc('uploaded_at')
            ->first(['uploaded_by', 'uploaded_at']);

        // Pakai DISTINCT langsung dari DB biar case-sensitive,
        // hindari unique() Collection yang case-insensitive (bisa ubah "Weld1" jadi "WELD1")
        $uniqueShops = Kanbanadm::selectRaw('DISTINCT shop_code')
            ->whereNotNull('shop_code')
            ->orderBy('shop_code')
            ->pluck('shop_code');

        return view('kanbanadms.index', compact('kanbanadms', 'latestUpload', 'uniqueShops'));
    }

    private function calculateCycleAndSeq($kanbanadms)
    {
        $partNos = $kanbanadms->pluck('part_no')->unique()
            ->map(fn($p) => preg_replace('/-00$/', '', $p));

        $addresses     = Admaddress::whereIn('part_no', $partNos)->pluck('rack_no', 'part_no');
        $arsAdms       = ArsAdm::whereIn('part_no', $partNos)->get()->keyBy('part_no');
        $runoutPartNos = AdmRunout::pluck('part_no')->flip();

        // Fix: ambil max del_cycle dari DB langsung (numeric), bukan dari collection yang mungkin partial
        $maxCyclePerShop = Kanbanadm::selectRaw('shop_code, MAX(CAST(del_cycle AS UNSIGNED)) as max_cycle')
            ->groupBy('shop_code')
            ->pluck('max_cycle', 'shop_code');

        foreach ($kanbanadms as $item) {
            $cleanPart = preg_replace('/-00$/', '', $item->part_no);

            $seqParts        = explode('/', $item->seq ?? '1/1');
            $item->seq_num   = (int) ($seqParts[0] ?? 1);
            $item->seq_total = (int) ($seqParts[1] ?? 1);

            // cycle_total dari DB (semua data), bukan hanya dari collection yang di-filter
            $item->cycle_total = $maxCyclePerShop[$item->shop_code] ?? ($item->del_cycle ?? 1);

            $item->rack_no = $addresses[$cleanPart] ?? null;

            $ars = $arsAdms[$cleanPart] ?? null;
            $item->ars_min          = $ars?->min          ?? '-';
            $item->ars_max          = $ars?->max          ?? '-';
            $item->ars_part_cat     = $ars?->part_cat     ?? '-';
            $item->ars_packing_type = $ars?->packing_type ?? '-';
            $item->ars_area_code    = $ars?->area_code    ?? '-';
            $item->ars_part_type    = $ars?->part_type    ?? '-';
            $item->ars_wh_zone      = $ars?->wh_zone      ?? '-';
            $item->ars_rack_no      = $ars?->rack_no      ?? '-';
            $item->ars_rack_layer   = $ars?->rack_layer   ?? '-';

            $item->is_runout = isset($runoutPartNos[$cleanPart])
                            || isset($runoutPartNos[$item->part_no]);
        }

        return $kanbanadms;
    }

    /**
     * Filter by plant berdasarkan rack_no di tabel addresses:
     *   plant2 = rack_no diawali huruf K
     *   plant1 = rack_no selain K, atau tidak ada di tabel addresses
     */
    private function applyPlantFilter($query, string $plant): void
    {
        if ($plant === 'all') return;

        if ($plant === 'plant2') {
            $query->whereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('addresses')
                    ->whereRaw("addresses.part_no = REGEXP_REPLACE(kanbanadms.part_no, '-00$', '')")
                    ->where('addresses.rack_no', 'like', 'K%');
            });
        } elseif ($plant === 'plant1') {
            $query->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('addresses')
                    ->whereRaw("addresses.part_no = REGEXP_REPLACE(kanbanadms.part_no, '-00$', '')")
                    ->where('addresses.rack_no', 'like', 'K%');
            });
        }
    }

    /**
     * Filter shop pakai whereIn — case-sensitive di MySQL (utf8_bin / latin1).
     * uniqueShops sekarang diambil langsung dari DB pakai DISTINCT
     * sehingga value di checkbox modal persis sama dengan yang di DB.
     */
    private function applyShopFilter($query, string $shops): void
    {
        $shopList = array_filter(array_map('trim', explode(',', $shops)));
        if (empty($shopList)) return;
        $query->whereIn('shop_code', $shopList);
    }

    public function printAll(Request $request)
    {
        $query = Kanbanadm::orderBy('order_no')   // ← utama: group by order_no dulu
            ->orderBy('shop_code')
            ->orderBy('job_no')
            ->orderBy('part_no');

        if ($request->filled('shops')) {
            $this->applyShopFilter($query, $request->shops);
        }

        $plant = $request->input('plant', 'all');
        $this->applyPlantFilter($query, $plant);

        $kanbanadms = $this->calculateCycleAndSeq($query->get());

        // Sort sekunder pakai rack_no (setelah calculateCycleAndSeq ngisi rack_no)
        $kanbanadms = $kanbanadms->sortBy([
            fn($a, $b) => strcmp($a->order_no ?? '', $b->order_no ?? ''),
            fn($a, $b) => strcmp($a->rack_no  ?? '', $b->rack_no  ?? ''),
        ])->values();

        $imagePath     = public_path('images/printadm.png');
        $imageBase64   = 'data:image/png;base64,' . base64_encode(file_get_contents($imagePath));

        $imagePathRo   = public_path('images/printadmro.png');
        $imageBase64Ro = 'data:image/png;base64,' . base64_encode(file_get_contents($imagePathRo));

        return view('kanbanadms.print', compact('kanbanadms', 'imageBase64', 'imageBase64Ro'));
    }

    public function printSelected(Request $request)
    {
        $ids = array_filter(explode(',', $request->ids ?? ''));

        $kanbanadms = Kanbanadm::whereIn('id', $ids)
            ->orderBy('order_no')
            ->orderBy('shop_code')
            ->orderBy('job_no')
            ->orderBy('part_no')
            ->get();

        $kanbanadms = $this->calculateCycleAndSeq($kanbanadms);

        // Sort sekunder: order_no dulu, lalu rack_no
        $kanbanadms = $kanbanadms->sortBy([
            fn($a, $b) => strcmp($a->order_no ?? '', $b->order_no ?? ''),
            fn($a, $b) => strcmp($a->rack_no  ?? '', $b->rack_no  ?? ''),
        ])->values();

        $imagePath     = public_path('images/printadm.png');
        $imageBase64   = 'data:image/png;base64,' . base64_encode(file_get_contents($imagePath));

        $imagePathRo   = public_path('images/printadmro.png');
        $imageBase64Ro = 'data:image/png;base64,' . base64_encode(file_get_contents($imagePathRo));

        return view('kanbanadms.print', compact('kanbanadms', 'imageBase64', 'imageBase64Ro'));
    }

    public function destroy($id)
    {
        try {
            Kanbanadm::findOrFail($id)->delete();
            return response()->json(['message' => 'Data berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        try {
            $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
            $rows        = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

            $headerRow = $rows[3] ?? [];

            $header = array_map(
                fn($h) => strtolower(trim(str_replace(
                    [' ', '.', '/', '(', ')'],
                    ['_', '',  '_', '_', '' ],
                    $h ?? ''
                ))),
                $headerRow
            );

            $uploadedBy = Auth::user()->name ?? Auth::user()->username ?? 'System';
            $uploadedAt = now();
            $data       = [];

            $clean = fn($val) => ($val === null || trim((string) $val) === '' || str_starts_with((string) $val, '#'))
                ? null
                : trim((string) $val);

            foreach (array_slice($rows, 4) as $row) {
                $r = array_combine($header, array_pad($row, count($header), null));

                if (empty(trim($r['part_no'] ?? ''))) continue;

                $orderKbn = max(1, (int) ($r['order_kbn'] ?? 1));

                $base = [
                    'plant_code'        => $clean($r['plant_code']        ?? null),
                    'shop_code'         => $clean($r['shop_code']         ?? null),
                    'part_category'     => $clean($r['part_category']     ?? null),
                    'route'             => $clean($r['route']             ?? null),
                    'lp'                => $clean($r['lp']                ?? null),
                    'trip'              => $clean($r['trip']              ?? null),
                    'vendor_code'       => $clean($r['vendor_code']       ?? null),
                    'vendor_alias'      => $clean($r['vendor_alias']      ?? null),
                    'vendor_site'       => $clean($r['vendor_site']       ?? null),
                    'vendor_site_alias' => $clean($r['vendor_site_alias'] ?? null),
                    'order_no'          => $clean($r['order_no']          ?? null),
                    'po_number'         => $clean($r['po_number']         ?? null),
                    'calc_date'         => $clean($r['calc_date']         ?? null),
                    'order_date'        => $clean($r['order_date']        ?? null),
                    'order_time'        => $clean($r['order_time']        ?? null),
                    'del_date'          => $clean($r['del_date']          ?? null),
                    'del_time'          => $clean($r['del_time']          ?? null),
                    'del_cycle'         => $clean($r['del_cycle']         ?? null),
                    'doc_no'            => $clean($r['doc_no']            ?? null),
                    'rec_status'        => $clean($r['rec_status']        ?? null),
                    'dn_type'           => $clean($r['dn_type']           ?? null),
                    'rec_date'          => $clean($r['rec_date']          ?? null),
                    'rec_by'            => $clean($r['rec_by']            ?? null),
                    'part_no'           => $clean($r['part_no']           ?? null),
                    'part_name'         => $clean($r['part_name']         ?? null),
                    'job_no'            => $clean($r['job_no']            ?? null),
                    'lane'              => $clean($r['lane']              ?? null),
                    'qty_kbn'           => $clean($r['qty_kbn']           ?? null),
                    'order_kbn'         => $clean($r['order_kbn']         ?? null),
                    'order_pcs'         => $clean($r['order_pcs']         ?? null),
                    'qty_receive'       => $clean($r['qty_receive']       ?? null),
                    'qty_balance'       => $clean($r['qty_balance']       ?? null),
                    'cancel_status'     => $clean($r['cancel_status']     ?? null),
                    'remark'            => $clean($r['remark']            ?? null),
                    'uploaded_by'       => $uploadedBy,
                    'uploaded_at'       => $uploadedAt,
                    'created_at'        => $uploadedAt,
                    'updated_at'        => $uploadedAt,
                ];

                for ($i = 0; $i < $orderKbn; $i++) {
                    $data[] = array_merge($base, [
                        'seq' => str_pad($i + 1, 6, '0', STR_PAD_LEFT) . '/' . $orderKbn,
                    ]);
                }
            }

            if (empty($data)) {
                return response()->json([
                    'message' => 'Tidak ada data yang bisa diimport. Periksa format file Excel.',
                ], 422);
            }

            DB::beginTransaction();
            try {
                Kanbanadm::query()->delete();
                foreach (array_chunk($data, 500) as $chunk) {
                    Kanbanadm::insert($chunk);
                }
                DB::commit();
            } catch (\Exception $inner) {
                DB::rollBack();
                throw $inner;
            }

            return response()->json([
                'message' => 'Berhasil import ' . count($data) . ' data kanban ADM.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal import: ' . $e->getMessage(),
            ], 500);
        }
    }
}
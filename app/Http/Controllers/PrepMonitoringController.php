<?php

namespace App\Http\Controllers;

use App\Models\Preparation;
use App\Models\PrepMonitoring;
use App\Models\Shipping;
use App\Models\ShippingMatrix;
use Illuminate\Http\Request;
use App\Models\Milkrun;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PrepMonitoringController extends Controller
{
    public function index(Request $request)
    {
        // Default: tanggal hari ini. Kalau date_from dikirim tapi date_to kosong -> single date.
        // Kalau dua-duanya dikirim -> range.
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->get('date_from'))->format('Y-m-d')
            : Carbon::today()->format('Y-m-d');

        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->get('date_to'))->format('Y-m-d')
            : $dateFrom;

        // Jaga-jaga kalau user kebalik masukin from > to
        if ($dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        $rows = PrepMonitoring::whereBetween('group_date', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->get();

        // Satu query Milkrun buat semua row, DIPERSEMPIT dulu pakai delivery_date
        // (lihat buildMilkrunLookup) sebelum kena JSON_CONTAINS - jauh lebih cepat
        // daripada scan seluruh tabel Milkrun.
        $milkrunMap = $this->buildMilkrunLookup(
            $rows->pluck('scanned_dns')->filter()->flatten()->unique()->values()->all(),
            $dateFrom,
            $dateTo
        );

        foreach ($rows as $row) {
            $row->milkrun = $this->matchMilkrun($row->scanned_dns ?? [], $milkrunMap);
        }

        $totalOnProgress = PrepMonitoring::whereBetween('group_date', [$dateFrom, $dateTo])
            ->where('status', 'in_progress')->count();
        $totalClosed = PrepMonitoring::whereBetween('group_date', [$dateFrom, $dateTo])
            ->where('status', 'ok')->count();

        return view('prep-monitoring.index', compact('rows', 'totalOnProgress', 'totalClosed', 'dateFrom', 'dateTo'));
    }

    /**
     * Cek DN sebelum diproses.
     * - Kalau kombinasi (customers+cycle+dock+tanggal) SUDAH ada & masih in_progress -> langsung proses, ga perlu modal.
     * - Kalau BELUM ada -> munculin modal pilih PR, sekalian preview kbn, skid & shipping address (matrix).
     *
     * Dipakai baik oleh jalur Prep Monitoring (scan biasa, matrix) maupun jalur Preparation
     * (scan/panah Move to Shipping, manual). Untuk jalur Preparation, front-end cukup
     * mengabaikan `shipping_address` hasil matrix dan menampilkan dropdown Shipping manual.
     */
    public function checkDn(Request $request)
    {
        $noDn = $request->get('no_dn');

        if (!$noDn) {
            return response()->json(['success' => false, 'message' => 'No DN tidak boleh kosong'], 400);
        }

        $preparation = Preparation::where('no_dn', $noDn)->first();

        if (!$preparation) {
            return response()->json([
                'success' => false,
                'message' => 'Data preparation dengan DN ' . $noDn . ' tidak ditemukan',
            ]);
        }

        // DN yang udah pernah kescan sebelumnya (row manapun) ga boleh discan lagi
        if (PrepMonitoring::whereJsonContains('scanned_dns', $preparation->no_dn)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'DN ' . $preparation->no_dn . ' sudah pernah discan sebelumnya',
            ]);
        }

        $groupDate = $preparation->delivery_date->format('Y-m-d');

        $existing = PrepMonitoring::where('customers', $preparation->customers)
            ->where('cycle', $preparation->cycle)
            ->where('dock', $preparation->dock)
            ->where('group_date', $groupDate)
            ->where('status', 'in_progress')
            ->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'needs_pr_selection' => false,
                'data' => [
                    'no_dn'            => $preparation->no_dn,
                    'customers'        => $preparation->customers,
                    'cycle'            => $preparation->cycle,
                    'dock'             => $preparation->dock,
                    'route'            => $preparation->route,
                    'pr_number'        => $existing->pr_number,
                    'shipping_address' => $existing->shipping_address,
                    'progress'         => $existing->pr_count . '/' . $existing->skid,
                ],
            ]);
        }

        $skid = Preparation::where('customers', $preparation->customers)
            ->where('cycle', $preparation->cycle)
            ->where('dock', $preparation->dock)
            ->whereDate('delivery_date', $groupDate)
            ->count();

        $kbn = Preparation::where('customers', $preparation->customers)
            ->where('cycle', $preparation->cycle)
            ->where('dock', $preparation->dock)
            ->whereDate('delivery_date', $groupDate)
            ->sum('qty_kbn');

        $shippingAddress = $this->determineShippingAddress($preparation);

        return response()->json([
            'success' => true,
            'needs_pr_selection' => true,
            'data' => [
                'no_dn'            => $preparation->no_dn,
                'customers'        => $preparation->customers,
                'cycle'            => $preparation->cycle,
                'dock'             => $preparation->dock,
                'route'            => $preparation->route,
                'skid'             => max($skid, 1),
                'kbn'              => $kbn,
                'shipping_address' => $shippingAddress, // saran dari matrix (dipakai jalur monitoring; jalur preparation abaikan ini)
            ],
        ]);
    }

    /**
     * Proses scan (JALUR PREP MONITORING - TIDAK BERUBAH).
     * Bikin row baru (kalau pr_number dikirim, buat kombinasi baru)
     * atau nambah counter row existing yang masih in_progress.
     * Begitu pr_count == skid -> status jadi 'ok' & bulk-move ke Shipping (address dari Matrix).
     */
    public function scan(Request $request)
    {
        $validated = $request->validate([
            'no_dn'     => 'required|string|max:255',
            'pr_number' => 'nullable|integer|min:1|max:4',
        ]);

        DB::beginTransaction();

        try {
            $preparation = Preparation::where('no_dn', $validated['no_dn'])->first();

            if (!$preparation) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Data preparation dengan DN ' . $validated['no_dn'] . ' tidak ditemukan',
                ], 404);
            }

            if (PrepMonitoring::whereJsonContains('scanned_dns', $preparation->no_dn)->exists()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'DN ' . $preparation->no_dn . ' sudah pernah discan sebelumnya',
                ], 422);
            }

            $groupDate = $preparation->delivery_date->format('Y-m-d');

            $monitoring = PrepMonitoring::where('customers', $preparation->customers)
                ->where('cycle', $preparation->cycle)
                ->where('dock', $preparation->dock)
                ->where('group_date', $groupDate)
                ->where('status', 'in_progress')
                ->lockForUpdate()
                ->first();

            if (!$monitoring) {
                if (empty($validated['pr_number'])) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Pilih Preparation (PR) terlebih dahulu untuk kombinasi baru ini',
                    ], 422);
                }

                $skid = Preparation::where('customers', $preparation->customers)
                    ->where('cycle', $preparation->cycle)
                    ->where('dock', $preparation->dock)
                    ->whereDate('delivery_date', $groupDate)
                    ->count();

                $kbn = Preparation::where('customers', $preparation->customers)
                    ->where('cycle', $preparation->cycle)
                    ->where('dock', $preparation->dock)
                    ->whereDate('delivery_date', $groupDate)
                    ->sum('qty_kbn');

                $shippingAddress = $this->determineShippingAddress($preparation);

                $monitoring = PrepMonitoring::create([
                    'customers'        => $preparation->customers,
                    'cycle'            => $preparation->cycle,
                    'dock'             => $preparation->dock,
                    'route'            => $preparation->route,
                    'kbn'              => $kbn,
                    'skid'             => max($skid, 1),
                    'pr_number'        => $validated['pr_number'],
                    'pr_count'         => 1,
                    'shipping_address' => $shippingAddress,
                    'ship_count'       => 1,
                    'status'           => 'in_progress',
                    'scanned_dns'      => [$preparation->no_dn],
                    'group_date'       => $groupDate,
                    'start_prep_at'    => Carbon::now(),
                    'etd_time'         => $preparation->delivery_time,
                ]);
            } else {
                $scannedDns   = $monitoring->scanned_dns ?? [];
                $scannedDns[] = $preparation->no_dn;

                $routes = array_filter(array_unique(array_merge(
                    array_map('trim', explode(',', $monitoring->route ?? '')),
                    [$preparation->route]
                )));

                $monitoring->scanned_dns = $scannedDns;
                $monitoring->route       = implode(', ', $routes);
                $monitoring->pr_count   += 1;
                $monitoring->ship_count += 1;
                $monitoring->save();
            }

            $isComplete = $monitoring->pr_count >= $monitoring->skid;

            if ($isComplete && $monitoring->status !== 'ok') {
                $monitoring->status        = 'ok';
                $monitoring->start_ship_at = Carbon::now();
                $monitoring->save();

                $this->bulkMoveToShipping($monitoring);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $isComplete
                    ? 'DN ' . $preparation->no_dn . ' discan. PR ' . $monitoring->pr_number . ' SELESAI (' . $monitoring->skid . '/' . $monitoring->skid . ') & sudah dipindah ke Shipping!'
                    : 'DN ' . $preparation->no_dn . ' discan. Progress PR ' . $monitoring->pr_number . ': ' . $monitoring->pr_count . '/' . $monitoring->skid,
                'data' => $monitoring->fresh(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses scan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Proses scan (JALUR PREPARATION - BARU).
     * DN langsung dipindah ke Shipping saat itu juga (gak nunggu skid penuh),
     * dengan shipping_address MANUAL (dari modal), bukan hasil Matrix.
     * Baris PrepMonitoring tetap dibuat/di-update supaya progress (mis. 1/4) ikut kelihatan
     * di Shipping Area Monitoring, dan otomatis ditutup ('ok') begitu skid penuh.
     */
    public function scanDirect(Request $request)
    {
        $validated = $request->validate([
            'no_dn'            => 'required|string|max:255',
            'pr_number'        => 'nullable|integer|min:1|max:4',
            'shipping_address' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $preparation = Preparation::where('no_dn', $validated['no_dn'])->first();

            if (!$preparation) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Data preparation dengan DN ' . $validated['no_dn'] . ' tidak ditemukan',
                ], 404);
            }

            if (PrepMonitoring::whereJsonContains('scanned_dns', $preparation->no_dn)->exists()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'DN ' . $preparation->no_dn . ' sudah pernah discan sebelumnya',
                ], 422);
            }

            $groupDate = $preparation->delivery_date->format('Y-m-d');

            $monitoring = PrepMonitoring::where('customers', $preparation->customers)
                ->where('cycle', $preparation->cycle)
                ->where('dock', $preparation->dock)
                ->where('group_date', $groupDate)
                ->where('status', 'in_progress')
                ->lockForUpdate()
                ->first();

            if (!$monitoring) {
                if (empty($validated['pr_number']) || empty($validated['shipping_address'])) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Pilih Preparation (PR) dan Shipping terlebih dahulu untuk kombinasi baru ini',
                    ], 422);
                }

                $skid = Preparation::where('customers', $preparation->customers)
                    ->where('cycle', $preparation->cycle)
                    ->where('dock', $preparation->dock)
                    ->whereDate('delivery_date', $groupDate)
                    ->count();

                $kbn = Preparation::where('customers', $preparation->customers)
                    ->where('cycle', $preparation->cycle)
                    ->where('dock', $preparation->dock)
                    ->whereDate('delivery_date', $groupDate)
                    ->sum('qty_kbn');

                $monitoring = PrepMonitoring::create([
                    'customers'        => $preparation->customers,
                    'cycle'            => $preparation->cycle,
                    'dock'             => $preparation->dock,
                    'route'            => $preparation->route,
                    'kbn'              => $kbn,
                    'skid'             => max($skid, 1),
                    'pr_number'        => $validated['pr_number'],
                    'pr_count'         => 1,
                    'shipping_address' => $validated['shipping_address'], // manual, bukan matrix
                    'ship_count'       => 1,
                    'status'           => 'in_progress',
                    'scanned_dns'      => [$preparation->no_dn],
                    'group_date'       => $groupDate,
                    'start_prep_at'    => Carbon::now(),
                    'etd_time'         => $preparation->delivery_time,
                ]);
            } else {
                $scannedDns   = $monitoring->scanned_dns ?? [];
                $scannedDns[] = $preparation->no_dn;

                $routes = array_filter(array_unique(array_merge(
                    array_map('trim', explode(',', $monitoring->route ?? '')),
                    [$preparation->route]
                )));

                $monitoring->scanned_dns = $scannedDns;
                $monitoring->route       = implode(', ', $routes);
                $monitoring->pr_count   += 1;
                $monitoring->ship_count += 1;
                $monitoring->save();
            }

            // Beda dari scan() biasa: DN ini langsung dipindah ke Shipping sekarang juga,
            // pakai shipping_address yang tersimpan di row (baru diisi manual atau reuse yang lama).
            $this->moveSingleToShipping($preparation, $monitoring->shipping_address);

            $isComplete = $monitoring->pr_count >= $monitoring->skid;

            if ($isComplete && $monitoring->status !== 'ok') {
                $monitoring->status        = 'ok';
                $monitoring->start_ship_at = Carbon::now();
                $monitoring->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $isComplete
                    ? 'DN ' . $preparation->no_dn . ' dipindahkan ke ' . $monitoring->shipping_address . '. PR ' . $monitoring->pr_number . ' SELESAI (' . $monitoring->skid . '/' . $monitoring->skid . ')!'
                    : 'DN ' . $preparation->no_dn . ' dipindahkan ke ' . $monitoring->shipping_address . '. Progress PR ' . $monitoring->pr_number . ': ' . $monitoring->pr_count . '/' . $monitoring->skid,
                'data' => $monitoring->fresh(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses scan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Pindahkan semua DN yang sudah terkumpul di grup ini ke tabel Shipping (JALUR MONITORING),
     * lalu hapus permanen dari preparations. Aman dipanggil walau sebagian DN sudah dipindah
     * duluan lewat jalur Preparation (moveSingleToShipping sudah cek duplikat).
     */
    private function bulkMoveToShipping(PrepMonitoring $monitoring): void
    {
        $noDns = $monitoring->scanned_dns ?? [];

        $preparations = Preparation::whereIn('no_dn', $noDns)->get();

        foreach ($preparations as $preparation) {
            $this->moveSingleToShipping($preparation, $monitoring->shipping_address);
        }
    }

    /**
     * Pindahkan SATU preparation ke Shipping. Dipakai baik oleh bulkMoveToShipping (jalur monitoring)
     * maupun scanDirect (jalur preparation). Aman dipanggil dobel (cek exists dulu).
     */
    private function moveSingleToShipping(Preparation $preparation, string $shippingAddress): void
    {
        if (Shipping::where('no_dn', $preparation->no_dn)->exists()) {
            return;
        }

        $movedBy = 'System';
        if (auth()->check()) {
            $user = auth()->user();
            $movedBy = $user->name ?? $user->email ?? 'User#' . $user->id;
        }

        $deliveryDateTime = Carbon::parse($preparation->delivery_date->format('Y-m-d') . ' ' . $preparation->delivery_time);
        $status = Carbon::now()->greaterThan($deliveryDateTime) ? 'delay' : 'normal';

        Shipping::create([
            'route'              => $preparation->route,
            'logistic_partners'  => $preparation->logistic_partners,
            'no_dn'              => $preparation->no_dn,
            'customers'          => $preparation->customers,
            'dock'               => $preparation->dock,
            'delivery_date'      => $preparation->delivery_date,
            'delivery_time'      => $preparation->delivery_time,
            'arrival'            => null,
            'cycle'              => $preparation->cycle,
            'address'            => $shippingAddress,
            'status'             => $status,
            'scan_to_shipping'   => Carbon::now(),
            'moved_by'           => $movedBy,
            'pulling_date'       => $preparation->pulling_date,
            'pulling_time'       => $preparation->pulling_time,
        ]);

        $preparation->forceDelete();
    }

    /**
     * Tentukan address Shipping otomatis dari Matrix Shipping (customers + dock + cycle).
     * Dipakai jalur monitoring. Default "Shipping 10" kalau kombinasi ga ketemu.
     */
    private function determineShippingAddress(Preparation $preparation): string
    {
        $customers = trim($preparation->customers);
        $dock      = trim($preparation->dock);
        $cycle     = trim((string) $preparation->cycle);

        $matrix = ShippingMatrix::whereRaw('LOWER(customers) = ?', [mb_strtolower($customers)])
            ->whereRaw('LOWER(dock) = ?', [mb_strtolower($dock)])
            ->where('cycle', $cycle)
            ->first();

        return $matrix ? $matrix->address : 'Shipping 10';
    }

    public function deleteAll()
    {
        try {
            $count = PrepMonitoring::count();
            PrepMonitoring::truncate();

            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus {$count} data monitoring",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ambil semua Milkrun yang match salah satu DN dari daftar $dns dalam SATU query,
     * terus bikin lookup map [no_dn => Milkrun].
     *
     * Kalau $dateFrom/$dateTo dikasih, query dipersempit dulu pakai kolom delivery_date
     * (dikasih buffer ±1 hari) SEBELUM kena JSON_CONTAINS - jauh lebih murah daripada
     * scan seluruh tabel Milkrun, karena delivery_date gampang difilter sedangkan
     * JSON_CONTAINS di kolom no_dns gak bisa pakai index.
     */
    private function buildMilkrunLookup(array $dns, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        if (empty($dns)) {
            return [];
        }

        $query = Milkrun::query();

        if ($dateFrom && $dateTo) {
            $query->whereBetween('delivery_date', [
                Carbon::parse($dateFrom)->subDay()->format('Y-m-d'),
                Carbon::parse($dateTo)->addDay()->format('Y-m-d'),
            ]);
        }

        $milkruns = $query->where(function ($q) use ($dns) {
            foreach ($dns as $dn) {
                $q->orWhereJsonContains('no_dns', $dn);
            }
        })->get();

        $map = [];
        foreach ($milkruns as $milkrun) {
            foreach (($milkrun->no_dns ?? []) as $dn) {
                // kalau 1 DN kebetulan ada di >1 milkrun, yang pertama menang
                if (!isset($map[$dn])) {
                    $map[$dn] = $milkrun;
                }
            }
        }

        return $map;
    }

    /**
     * Cari Milkrun buat satu row PrepMonitoring dari lookup map yang udah dibuat
     * buildMilkrunLookup(), tanpa query tambahan ke DB.
     */
    private function matchMilkrun(array $noDns, array $milkrunMap): ?Milkrun
    {
        foreach ($noDns as $dn) {
            if (isset($milkrunMap[$dn])) {
                return $milkrunMap[$dn];
            }
        }

        return null;
    }

    public function andon()
    {
        $rows = PrepMonitoring::orderBy('created_at', 'desc')->get();

        // Andon nggak ada filter tanggal manual, jadi ambil rentang tanggal dari
        // group_date row yang ke-load, biar buildMilkrunLookup tetep bisa mempersempit
        // scan Milkrun-nya (daripada scan seluruh tabel Milkrun tanpa batas).
        $groupDates = $rows->pluck('group_date')->filter()->unique();
        $dateFrom   = $groupDates->min();
        $dateTo     = $groupDates->max();

        $milkrunMap = $this->buildMilkrunLookup(
            $rows->pluck('scanned_dns')->filter()->flatten()->unique()->values()->all(),
            $dateFrom,
            $dateTo
        );

        foreach ($rows as $row) {
            $row->milkrun = $this->matchMilkrun($row->scanned_dns ?? [], $milkrunMap);
        }

        $totalOnProgress = PrepMonitoring::where('status', 'in_progress')->count();
        $totalClosed     = PrepMonitoring::where('status', 'ok')->count();

        $recentCompleted = PrepMonitoring::where('status', 'ok')
            ->orderBy('start_ship_at', 'desc')
            ->first();

        return view('andon.prep-monitoring', compact('rows', 'totalOnProgress', 'totalClosed', 'recentCompleted'));
    }
}
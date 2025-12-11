<?php

namespace App\Http\Controllers;

use App\Models\Milkrun;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MilkrunController extends Controller
{
    /**
     * Display milkrun list (ONLY with arrival data, no pending status)
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 50);
        $search = $request->get('search');
        $statusFilter = $request->get('status');
        
        // Date filter - default hari ini
        $dateFilter = $request->get('date', Carbon::today()->format('Y-m-d'));
        
        // HANYA tampilkan milkrun yang sudah ada arrival (tidak ada pending)
        $query = Milkrun::whereNotNull('arrival');
        
        // Filter berdasarkan delivery_date
        if ($dateFilter) {
            $query->whereDate('delivery_date', $dateFilter);
        }
        
        // Search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('route', 'like', "%{$search}%")
                  ->orWhere('logistic_partners', 'like', "%{$search}%")
                  ->orWhere('customers', 'like', "%{$search}%")
                  ->orWhere('dock', 'like', "%{$search}%");
            });
        }
        
        // Status filter (hanya advance, on_time, delay - TIDAK ADA PENDING)
        if ($statusFilter && $statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }
        
        // Order by delivery datetime
        $query->orderByRaw("CONCAT(delivery_date, ' ', delivery_time) ASC");
        
        // Pagination
        if ($perPage === 'all') {
            $milkruns = $query->get();
            $milkruns = new \Illuminate\Pagination\LengthAwarePaginator(
                $milkruns,
                $milkruns->count(),
                $milkruns->count(),
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        } else {
            $milkruns = $query->paginate((int)$perPage)->withQueryString();
        }
        
        // Statistics (TANPA PENDING) - filtered by date juga
        $statsQuery = Milkrun::whereNotNull('arrival');
        if ($dateFilter) {
            $statsQuery->whereDate('delivery_date', $dateFilter);
        }
        
        $totalAll = (clone $statsQuery)->count();
        $totalAdvance = (clone $statsQuery)->where('status', 'advance')->count();
        $totalOnTime = (clone $statsQuery)->where('status', 'on_time')->count();
        $totalDelay = (clone $statsQuery)->where('status', 'delay')->count();
        
        return view('milkruns.index', compact(
            'milkruns', 
            'totalAll', 
            'totalAdvance', 
            'totalOnTime', 
            'totalDelay',
            'dateFilter'
        ));
    }

    /**
     * Update arrival time and calculate status
     */
    public function updateArrival(Request $request, Milkrun $milkrun)
    {
        $validated = $request->validate([
            'arrival' => 'required|date',
        ]);

        try {
            $milkrun->arrival = Carbon::parse($validated['arrival']);
            $milkrun->status = $milkrun->calculateStatus();
            $milkrun->save();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Arrival time berhasil diupdate',
                    'data' => [
                        'status' => $milkrun->status,
                        'status_label' => $milkrun->status_label,
                        'status_badge' => $milkrun->status_badge,
                        'time_diff' => $milkrun->time_diff_info,
                    ]
                ]);
            }
            
            return redirect()->back()->with('success', 'Arrival time berhasil diupdate!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal update arrival: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Gagal update arrival: ' . $e->getMessage());
        }
    }

    /**
     * Update departure time
     */
    public function updateDeparture(Request $request, Milkrun $milkrun)
    {
        $validated = $request->validate([
            'departure' => 'required|date',
        ]);

        try {
            $milkrun->departure = Carbon::parse($validated['departure']);
            $milkrun->save();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Departure time berhasil diupdate',
                    'data' => $milkrun
                ]);
            }
            
            return redirect()->back()->with('success', 'Departure time berhasil diupdate!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal update departure: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Gagal update departure: ' . $e->getMessage());
        }
    }

    /**
     * Scan arrival by route
     */
    public function scanArrival(Request $request)
    {
        $validated = $request->validate([
            'route' => 'required|string|max:255',
            'cycle' => 'nullable|integer',
        ]);

        try {
            $query = Milkrun::where('route', $validated['route'])
                ->where('status', 'pending');
            
            if (isset($validated['cycle']) && $validated['cycle'] !== null) {
                $query->where('cycle', $validated['cycle']);
            }
            
            $milkrun = $query->first();
            
            if (!$milkrun) {
                $cycleInfo = isset($validated['cycle']) ? " cycle {$validated['cycle']}" : "";
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada milkrun pending dengan route "' . $validated['route'] . '"' . $cycleInfo
                ], 404);
            }
            
            $now = Carbon::now();
            $milkrun->arrival = $now;
            $milkrun->status = $milkrun->calculateStatus();
            $milkrun->save();
            
            return response()->json([
                'success' => true,
                'message' => "Arrival berhasil di-scan untuk route \"{$validated['route']}\"",
                'data' => [
                    'route' => $milkrun->route,
                    'cycle' => $milkrun->cycle,
                    'arrival' => $milkrun->arrival->format('d-m-Y H:i:s'),
                    'status' => $milkrun->status_label,
                    'status_badge' => $milkrun->status_badge,
                    'time_diff' => $milkrun->time_diff_info,
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal scan arrival: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get DN list for a milkrun with details
     */
    public function getDnList(Milkrun $milkrun)
    {
        try {
            // Debug: Log milkrun data
            \Log::info('Milkrun Data:', [
                'id' => $milkrun->id,
                'route' => $milkrun->route,
                'no_dns' => $milkrun->no_dns,
                'no_dns_type' => gettype($milkrun->no_dns),
            ]);
            
            // Pastikan no_dns adalah array
            $noDns = $milkrun->no_dns ?? [];
            
            // Jika no_dns kosong
            if (empty($noDns)) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'milkrun' => [
                            'route' => $milkrun->route,
                            'cycle' => $milkrun->cycle,
                            'dn_count' => $milkrun->dn_count,
                        ],
                        'deliveries' => []
                    ]
                ]);
            }
            
            // Query deliveries
            $deliveries = Delivery::whereIn('no_dn', $noDns)
                ->select('no_dn', 'customers', 'dock')
                ->get();
            
            // Debug: Log deliveries
            \Log::info('Deliveries Found:', [
                'count' => $deliveries->count(),
                'data' => $deliveries->toArray(),
            ]);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'milkrun' => [
                        'route' => $milkrun->route,
                        'cycle' => $milkrun->cycle,
                        'dn_count' => $milkrun->dn_count ?? count($noDns),
                    ],
                    'deliveries' => $deliveries
                ]
            ]);
        } catch (\Exception $e) {
            // Log error detail
            \Log::error('Error getDnList:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat daftar DN: ' . $e->getMessage(),
                'debug' => [
                    'milkrun_id' => $milkrun->id ?? null,
                    'no_dns' => $milkrun->no_dns ?? null,
                ]
            ], 500);
        }
    }

    /**
     * Get milkrun data for edit
     */
    public function edit(Milkrun $milkrun)
    {
        if (request()->ajax()) {
            return response()->json([
                'id' => $milkrun->id,
                'customers' => $milkrun->customers,
                'route' => $milkrun->route,
                'logistic_partners' => $milkrun->logistic_partners,
                'cycle' => $milkrun->cycle,
                'dock' => $milkrun->dock,
                'delivery_date' => $milkrun->delivery_date->format('Y-m-d'),
                'delivery_time' => date('H:i', strtotime($milkrun->delivery_time)),
                'arrival' => $milkrun->arrival ? $milkrun->arrival->format('Y-m-d\TH:i') : null,
                'departure' => $milkrun->departure ? $milkrun->departure->format('Y-m-d\TH:i') : null,
                'status' => $milkrun->status,
                'dn_count' => $milkrun->dn_count,
                'no_dns' => $milkrun->no_dns,
                'address' => $milkrun->address,
            ]);
        }
        
        return view('milkruns.edit', compact('milkrun'));
    }

    /**
     * Update milkrun data
     */
    public function update(Request $request, Milkrun $milkrun)
    {
        $validated = $request->validate([
            'customers' => 'required|string|max:255',
            'route' => 'required|string|max:255',
            'logistic_partners' => 'nullable|string|max:255',
            'cycle' => 'required|integer|min:1',
            'dock' => 'nullable|string|max:255',
            'delivery_date' => 'required|date',
            'delivery_time' => 'required',
            'arrival' => 'nullable|date',
            'departure' => 'nullable|date',
            'address' => 'nullable|string|max:50',
        ]);

        try {
            $milkrun->fill($validated);
            
            // Recalculate status if arrival is set
            if ($milkrun->arrival) {
                $milkrun->status = $milkrun->calculateStatus();
            }
            
            $milkrun->save();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data milkrun berhasil diupdate',
                    'data' => $milkrun
                ]);
            }
            
            return redirect()
                ->route('milkruns.index')
                ->with('success', 'Data milkrun berhasil diupdate!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengupdate data: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal mengupdate data: ' . $e->getMessage());
        }
    }

    /**
     * Delete milkrun
     */
    public function destroy(Milkrun $milkrun)
    {
        try {
            $milkrun->forceDelete();
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data milkrun berhasil dihapus!'
                ]);
            }
            
            return redirect()
                ->route('milkruns.index')
                ->with('success', 'Data milkrun berhasil dihapus!');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus data: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Delete all milkruns
     */
    public function deleteAll()
    {
        try {
            $count = Milkrun::count();
            Milkrun::withTrashed()->forceDelete();
            
            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus {$count} data milkrun"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

     public function andon(Request $request)
    {
        $perPage = $request->get('per_page', 50);
        
        // Date filter - default hari ini
        $dateFilter = $request->get('date', Carbon::today()->format('Y-m-d'));
        
        // HANYA tampilkan milkrun yang sudah ada arrival (tidak ada pending)
        $query = Milkrun::whereNotNull('arrival');
        
        // Filter berdasarkan delivery_date
        if ($dateFilter) {
            $query->whereDate('delivery_date', $dateFilter);
        }
        
        // Order by delivery datetime
        $query->orderByRaw("CONCAT(delivery_date, ' ', delivery_time) ASC");
        
        // Pagination atau all
        if ($perPage === 'all') {
            $milkruns = $query->get();
            $milkruns = new \Illuminate\Pagination\LengthAwarePaginator(
                $milkruns,
                $milkruns->count(),
                $milkruns->count(),
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        } else {
            $milkruns = $query->paginate((int)$perPage)->withQueryString();
        }
        
        // Statistics (TANPA PENDING) - filtered by date juga
        $statsQuery = Milkrun::whereNotNull('arrival');
        if ($dateFilter) {
            $statsQuery->whereDate('delivery_date', $dateFilter);
        }
        
        $totalAll = (clone $statsQuery)->count();
        $totalAdvance = (clone $statsQuery)->where('status', 'advance')->count();
        $totalOnTime = (clone $statsQuery)->where('status', 'on_time')->count();
        $totalDelay = (clone $statsQuery)->where('status', 'delay')->count();
        
        // DEBUG: Log milkrun data (optional, bisa dihapus di production)
        \Log::info('Milkrun Andon Data:', [
            'date_filter' => $dateFilter,
            'total_all' => $totalAll,
            'total_advance' => $totalAdvance,
            'total_on_time' => $totalOnTime,
            'total_delay' => $totalDelay,
        ]);
        
        // Return view andon untuk milkrun
        return view('andon.milkruns', compact(
            'milkruns', 
            'totalAll', 
            'totalAdvance', 
            'totalOnTime', 
            'totalDelay',
            'dateFilter'
        ));
    }
}
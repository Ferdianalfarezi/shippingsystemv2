<?php

namespace App\Http\Controllers;

use App\Models\Shipping;
use App\Models\Preparation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ShippingController extends Controller
{
    /**
     * Display shipping list
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 50);
        $search = $request->get('search');
        $statusFilter = $request->get('status');
        
        $query = Shipping::query();
        
        // Search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('route', 'like', "%{$search}%")
                  ->orWhere('logistic_partners', 'like', "%{$search}%")
                  ->orWhere('no_dn', 'like', "%{$search}%")
                  ->orWhere('customers', 'like', "%{$search}%")
                  ->orWhere('dock', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }
        
        // Status filter
        if ($statusFilter && $statusFilter !== 'all') {
            if ($statusFilter === 'on_loading') {
                $query->whereNotNull('arrival');
            } else {
                $query->whereNull('arrival')->where('status', $statusFilter);
            }
        }
        
        // Order by delivery datetime (yang paling dekat di atas)
        $query->orderByRaw("CONCAT(delivery_date, ' ', delivery_time) ASC");
        
        // Pagination
        if ($perPage === 'all') {
            $shippings = $query->get();
            $shippings = new \Illuminate\Pagination\LengthAwarePaginator(
                $shippings,
                $shippings->count(),
                $shippings->count(),
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        } else {
            $shippings = $query->paginate((int)$perPage)->withQueryString();
        }
        
        // Statistics
        $totalAll = Shipping::count();
        $totalAdvance = Shipping::whereNull('arrival')
            ->whereRaw("CONCAT(delivery_date, ' ', delivery_time) > DATE_ADD(NOW(), INTERVAL 15 MINUTE)")
            ->count();
        $totalNormal = Shipping::whereNull('arrival')
            ->whereRaw("CONCAT(delivery_date, ' ', delivery_time) BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 15 MINUTE)")
            ->count();
        $totalDelay = Shipping::whereNull('arrival')
            ->whereRaw("CONCAT(delivery_date, ' ', delivery_time) < NOW()")
            ->count();
        $totalOnLoading = Shipping::whereNotNull('arrival')->count();
        
        return view('shippings.index', compact(
            'shippings', 
            'totalAll', 
            'totalAdvance', 
            'totalNormal', 
            'totalDelay', 
            'totalOnLoading'
        ));
    }

    /**
     * Move preparation to shipping
     */
    public function moveFromPreparation(Request $request)
    {
        $validated = $request->validate([
            'preparation_id' => 'required|exists:preparations,id',
            'address' => 'required|string|max:50',
        ]);

        DB::beginTransaction();
        
        try {
            // Get preparation data
            $preparation = Preparation::findOrFail($validated['preparation_id']);
            
            // Check if no_dn already exists in shippings
            $existingShipping = Shipping::where('no_dn', $preparation->no_dn)->first();
            if ($existingShipping) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dengan No DN ini sudah ada di Shipping!'
                ], 422);
            }
            
            // Calculate initial status
            $deliveryDateTime = Carbon::parse($preparation->delivery_date->format('Y-m-d') . ' ' . $preparation->delivery_time);
            $now = Carbon::now();
            $normalStartTime = $deliveryDateTime->copy()->subMinutes(15);
            
            if ($now->greaterThan($deliveryDateTime)) {
                $status = 'delay';
            } elseif ($now->greaterThanOrEqualTo($normalStartTime)) {
                $status = 'normal';
            } else {
                $status = 'advance';
            }
            
            // Create shipping record
            $shipping = Shipping::create([
                'route' => $preparation->route,
                'logistic_partners' => $preparation->logistic_partners,
                'no_dn' => $preparation->no_dn,
                'customers' => $preparation->customers,
                'dock' => $preparation->dock,
                'delivery_date' => $preparation->delivery_date,
                'delivery_time' => $preparation->delivery_time,
                'arrival' => null, // Kosong sampai di-scan
                'cycle' => $preparation->cycle,
                'address' => $validated['address'],
                'status' => $status,
                'scan_to_shipping' => Carbon::now(),
            ]);
            
            // Delete preparation permanently
            $preparation->forceDelete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dipindahkan ke Shipping!',
                'data' => $shipping
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memindahkan data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get shipping data for edit
     */
    public function edit(Shipping $shipping)
    {
        if (request()->ajax()) {
            return response()->json([
                'id' => $shipping->id,
                'route' => $shipping->route,
                'logistic_partners' => $shipping->logistic_partners,
                'no_dn' => $shipping->no_dn,
                'customers' => $shipping->customers,
                'dock' => $shipping->dock,
                'delivery_date' => $shipping->delivery_date->format('Y-m-d'),
                'delivery_time' => date('H:i', strtotime($shipping->delivery_time)),
                'cycle' => $shipping->cycle,
                'address' => $shipping->address,
            ]);
        }
        
        return view('shippings.edit', compact('shipping'));
    }

    /**
     * Update shipping data
     */
    public function update(Request $request, Shipping $shipping)
    {
        $validated = $request->validate([
            'route' => 'required|string|max:255',
            'logistic_partners' => 'required|string|max:255',
            'no_dn' => 'required|string|max:255|unique:shippings,no_dn,' . $shipping->id,
            'customers' => 'required|string|max:255',
            'dock' => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'delivery_time' => 'required',
            'cycle' => 'required|integer|min:1',
            'address' => 'required|string|max:50',
        ]);

        try {
            $shipping->update($validated);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data shipping berhasil diupdate',
                    'data' => $shipping
                ]);
            }
            
            return redirect()
                ->route('shippings.index')
                ->with('success', 'Data shipping berhasil diupdate!');
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
     * Delete shipping
     */
    public function destroy(Shipping $shipping)
    {
        try {
            $shipping->forceDelete();
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data shipping berhasil dihapus!'
                ]);
            }
            
            return redirect()
                ->route('shippings.index')
                ->with('success', 'Data shipping berhasil dihapus!');
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
     * Delete all shippings
     */
    public function deleteAll()
    {
        try {
            $count = Shipping::count();
            Shipping::withTrashed()->forceDelete();
            
            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus {$count} data shipping"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Checking LP Page - untuk scan route dan update arrival
     */
    public function checkingLp()
    {
        // Get all shippings yang belum di-scan, grouped by route
        $shippings = Shipping::whereNull('arrival')
            ->orderBy('route')
            ->orderByRaw("CONCAT(delivery_date, ' ', delivery_time) ASC")
            ->get();
        
        // Get unique routes untuk referensi
        $availableRoutes = Shipping::whereNull('arrival')
            ->select('route')
            ->distinct()
            ->pluck('route');
        
        return view('shippings.checking-lp', compact('shippings', 'availableRoutes'));
    }

    /**
     * Check route - cek apakah route punya multiple cycles
     */
    public function checkRoute(Request $request)
    {
        $validated = $request->validate([
            'route' => 'required|string|max:255',
        ]);

        try {
            // Find all shippings with this route that haven't been scanned
            $shippings = Shipping::where('route', $validated['route'])
                ->whereNull('arrival')
                ->get();
            
            if ($shippings->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data shipping dengan route "' . $validated['route'] . '" yang belum di-scan!'
                ], 404);
            }
            
            // Get unique cycles
            $cycles = $shippings->pluck('cycle')->unique()->sort()->values();
            
            return response()->json([
                'success' => true,
                'route' => $validated['route'],
                'cycles' => $cycles,
                'has_multiple_cycles' => $cycles->count() > 1,
                'total_data' => $shippings->count()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal check route: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Scan route - update arrival untuk shipping dengan route dan cycle tertentu
     */
    public function scanRoute(Request $request)
    {
        $validated = $request->validate([
            'route' => 'required|string|max:255',
            'cycle' => 'nullable|integer', // Optional, jika null scan semua cycle
        ]);

        try {
            // Build query
            $query = Shipping::where('route', $validated['route'])
                ->whereNull('arrival');
            
            // Filter by cycle jika ada
            if (isset($validated['cycle']) && $validated['cycle'] !== null) {
                $query->where('cycle', $validated['cycle']);
            }
            
            $shippings = $query->get();
            
            if ($shippings->isEmpty()) {
                $cycleInfo = isset($validated['cycle']) ? " cycle {$validated['cycle']}" : "";
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data shipping dengan route "' . $validated['route'] . '"' . $cycleInfo . ' yang belum di-scan!'
                ], 404);
            }
            
            $now = Carbon::now();
            $count = 0;
            
            foreach ($shippings as $shipping) {
                $shipping->arrival = $now;
                $shipping->status = 'on_loading';
                $shipping->save();
                $count++;
            }
            
            $cycleInfo = isset($validated['cycle']) ? " Cycle {$validated['cycle']}" : "";
            
            return response()->json([
                'success' => true,
                'message' => "Berhasil scan {$count} data dengan route \"{$validated['route']}\"{$cycleInfo}",
                'scanned_count' => $count,
                'arrival_time' => $now->format('d-m-Y H:i:s')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal scan route: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get shippings by route untuk display di checking LP
     */
    public function getByRoute(Request $request)
    {
        $route = $request->get('route');
        
        if (!$route) {
            return response()->json([
                'success' => false,
                'message' => 'Route tidak boleh kosong'
            ], 422);
        }
        
        $shippings = Shipping::where('route', $route)
            ->orderByRaw("CONCAT(delivery_date, ' ', delivery_time) ASC")
            ->get()
            ->map(function ($shipping) {
                return [
                    'id' => $shipping->id,
                    'route' => $shipping->route,
                    'logistic_partners' => $shipping->logistic_partners,
                    'no_dn' => $shipping->no_dn,
                    'customers' => $shipping->customers,
                    'dock' => $shipping->dock,
                    'delivery_date' => $shipping->delivery_date->format('d-m-y'),
                    'delivery_time' => date('H:i:s', strtotime($shipping->delivery_time)),
                    'cycle' => $shipping->cycle,
                    'address' => $shipping->address,
                    'arrival' => $shipping->arrival ? $shipping->arrival->format('d-m-y H:i') : null,
                    'status' => $shipping->status_label,
                    'status_badge' => $shipping->status_badge,
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $shippings
        ]);
    }
}
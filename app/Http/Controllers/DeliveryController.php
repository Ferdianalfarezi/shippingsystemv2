<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Shipping;
use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeliveryController extends Controller
{
    /**
     * Display delivery list
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 50);
        $search = $request->get('search');
        $statusFilter = $request->get('status');
        
        $query = Delivery::query();
        
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
        
        // Order by scan_to_delivery (yang paling baru di atas)
        $query->orderBy('scan_to_delivery', 'asc');
        
        // Get data
        if ($perPage === 'all') {
            $allDeliveries = $query->get();
        } else {
            $allDeliveries = $query->get(); // Ambil semua dulu untuk filter status
        }
        
        // Filter by status (calculated) jika ada
        if ($statusFilter && $statusFilter !== 'all') {
            $allDeliveries = $allDeliveries->filter(function($delivery) use ($statusFilter) {
                return $delivery->status === $statusFilter;
            });
        }

        $recentScan = History::whereNotNull('completed_at')
            ->orderBy('completed_at', 'desc')
            ->first();
        
        // Calculate statistics dari semua data (sebelum pagination)
        $allDataForStats = Delivery::all();
        $totalAll = $allDataForStats->count();
        $totalNormal = $allDataForStats->filter(fn($d) => $d->status === 'normal')->count();
        $totalDelay = $allDataForStats->filter(fn($d) => $d->status === 'delay')->count();
        
        // Manual pagination
        if ($perPage === 'all') {
            $deliveries = new \Illuminate\Pagination\LengthAwarePaginator(
                $allDeliveries->values(),
                $allDeliveries->count(),
                $allDeliveries->count() ?: 1,
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        } else {
            $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage();
            $perPageInt = (int)$perPage;
            $currentPageItems = $allDeliveries->slice(($currentPage - 1) * $perPageInt, $perPageInt)->values();
            
            $deliveries = new \Illuminate\Pagination\LengthAwarePaginator(
                $currentPageItems,
                $allDeliveries->count(),
                $perPageInt,
                $currentPage,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        }
        
        return view('deliveries.index', compact(
            'deliveries', 
            'totalAll', 
            'totalNormal', 
            'totalDelay',
             'recentScan'
        ));
    }

    /**
     * Display delivery list in reverse view (grouped by route & cycle)
     */
    public function indexReverse(Request $request)
    {
        $perPage = $request->get('per_page', 50);
        $search = $request->get('search');
        
        // Get all deliveries
        $query = Delivery::query();
        
        // Search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('route', 'like', "%{$search}%")
                  ->orWhere('logistic_partners', 'like', "%{$search}%")
                  ->orWhere('customers', 'like', "%{$search}%")
                  ->orWhere('dock', 'like', "%{$search}%");
            });
        }
        
        // Get all data
        $allDeliveries = $query->get();
        
        // Group by route and cycle
        $grouped = $allDeliveries->groupBy(function($item) {
            return $item->route . '|' . $item->cycle;
        })->map(function($group) {
            $firstItem = $group->first();
            
            // Calculate status for the group
            // Jika ada satu saja yang delay, group = delay
            $hasDelay = $group->contains(fn($item) => $item->status === 'delay');
            $groupStatus = $hasDelay ? 'delay' : 'normal';
            
            $statusLabel = $groupStatus === 'delay' ? 'Delay' : 'Normal';
            $statusBadge = $groupStatus === 'delay' ? 'bg-danger' : 'bg-success';
            
            return [
                'route' => $firstItem->route,
                'logistic_partners' => $group->pluck('logistic_partners')->unique()->filter()->implode(', '),
                'cycle' => $firstItem->cycle,
                'customers' => $group->pluck('customers')->unique()->filter()->implode(', '),
                'dock' => $group->pluck('dock')->unique()->filter()->implode(', '),
                'scan_to_delivery' => $firstItem->scan_to_delivery ? $firstItem->scan_to_delivery->format('d-m-y H:i') : null,
                'address' => $group->pluck('address')->unique()->filter()->implode(', '),
                'status' => $groupStatus,
                'status_label' => $statusLabel,
                'status_badge' => $statusBadge,
                'no_dns' => $group->pluck('no_dn')->toArray(),
                'dn_count' => $group->count(),
                'scan_datetime' => $firstItem->scan_to_delivery,
            ];
        })->sortBy('scan_datetime')->values();
        
         $recentScan = History::whereNotNull('completed_at')
            ->orderBy('completed_at', 'desc')
            ->first();

        // Pagination manual
        if ($perPage === 'all') {
            $groupedDeliveries = new \Illuminate\Pagination\LengthAwarePaginator(
                $grouped,
                $grouped->count(),
                $grouped->count() ?: 1,
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        } else {
            $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage();
            $perPageInt = (int)$perPage;
            $currentPageItems = $grouped->slice(($currentPage - 1) * $perPageInt, $perPageInt)->values();
            
            $groupedDeliveries = new \Illuminate\Pagination\LengthAwarePaginator(
                $currentPageItems,
                $grouped->count(),
                $perPageInt,
                $currentPage,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        }
        
        // Statistics
        $totalNormal = $grouped->where('status', 'normal')->count();
        $totalDelay = $grouped->where('status', 'delay')->count();
        
        return view('deliveries.index-reverse', compact(
            'groupedDeliveries',
            'totalNormal',
            'totalDelay',
            'recentScan'
        ));
    }

    /**
     * Move data from Shipping to Delivery
     */
    public function moveFromShipping(Request $request)
    {
        $validated = $request->validate([
            'shipping_id' => 'required|exists:shippings,id',
        ]);

        DB::beginTransaction();
        
        try {
            // Get shipping data
            $shipping = Shipping::findOrFail($validated['shipping_id']);
            
            // Check if no_dn already exists in deliveries
            $existingDelivery = Delivery::where('no_dn', $shipping->no_dn)->first();
            if ($existingDelivery) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dengan No DN ini sudah ada di Delivery!'
                ], 422);
            }
            
            // Get user name dengan multiple fallback
            $movedBy = 'System';
            if (auth()->check()) {
                $user = auth()->user();
                $movedBy = $user->name ?? $user->email ?? 'User#' . $user->id;
            }
            
            // Create delivery record (tanpa field status karena calculated)
            $delivery = Delivery::create([
                'route' => $shipping->route,
                'logistic_partners' => $shipping->logistic_partners,
                'no_dn' => $shipping->no_dn,
                'customers' => $shipping->customers,
                'dock' => $shipping->dock,
                'cycle' => $shipping->cycle,
                'address' => $shipping->address,
                'scan_to_delivery' => Carbon::now(),
                'moved_by' => $movedBy,
            ]);
            
            // Delete shipping permanently
            $shipping->forceDelete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dipindahkan ke Delivery!',
                'data' => $delivery
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
     * Move multiple data from Shipping to Delivery by route and cycle
     */
    public function moveByRoute(Request $request)
    {
        $validated = $request->validate([
            'route' => 'required|string|max:255',
            'cycle' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        
        try {
            // Build query
            $query = Shipping::where('route', $validated['route']);
            
            // Filter by cycle jika ada
            if (isset($validated['cycle']) && $validated['cycle'] !== null) {
                $query->where('cycle', $validated['cycle']);
            }
            
            $shippings = $query->get();
            
            if ($shippings->isEmpty()) {
                $cycleInfo = isset($validated['cycle']) ? " cycle {$validated['cycle']}" : "";
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data shipping dengan route "' . $validated['route'] . '"' . $cycleInfo
                ], 404);
            }
            
            // Get user name
            $movedBy = 'System';
            if (auth()->check()) {
                $user = auth()->user();
                $movedBy = $user->name ?? $user->email ?? 'User#' . $user->id;
            }
            
            $now = Carbon::now();
            $count = 0;
            $skipped = 0;
            
            foreach ($shippings as $shipping) {
                // Check if already exists
                if (Delivery::where('no_dn', $shipping->no_dn)->exists()) {
                    $skipped++;
                    continue;
                }
                
                Delivery::create([
                    'route' => $shipping->route,
                    'logistic_partners' => $shipping->logistic_partners,
                    'no_dn' => $shipping->no_dn,
                    'customers' => $shipping->customers,
                    'dock' => $shipping->dock,
                    'cycle' => $shipping->cycle,
                    'address' => $shipping->address,
                    'scan_to_delivery' => $now,
                    'moved_by' => $movedBy,
                ]);
                
                $shipping->forceDelete();
                $count++;
            }
            
            DB::commit();
            
            $cycleInfo = isset($validated['cycle']) ? " Cycle {$validated['cycle']}" : "";
            $message = "Berhasil memindahkan {$count} data dengan route \"{$validated['route']}\"{$cycleInfo} ke Delivery";
            if ($skipped > 0) {
                $message .= " ({$skipped} data dilewati karena sudah ada)";
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'moved_count' => $count,
                'skipped_count' => $skipped
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
     * Get delivery data for edit
     */
    public function edit(Delivery $delivery)
    {
        if (request()->ajax()) {
            return response()->json([
                'id' => $delivery->id,
                'route' => $delivery->route,
                'logistic_partners' => $delivery->logistic_partners,
                'no_dn' => $delivery->no_dn,
                'customers' => $delivery->customers,
                'dock' => $delivery->dock,
                'cycle' => $delivery->cycle,
                'address' => $delivery->address,
                'status' => $delivery->status,
                'status_label' => $delivery->status_label,
                'business_hours_elapsed' => $delivery->business_hours_elapsed,
            ]);
        }
        
        return view('deliveries.edit', compact('delivery'));
    }

    /**
     * Update delivery data
     */
    public function update(Request $request, Delivery $delivery)
    {
        $validated = $request->validate([
            'route' => 'required|string|max:255',
            'logistic_partners' => 'required|string|max:255',
            'no_dn' => 'required|string|max:255|unique:deliveries,no_dn,' . $delivery->id,
            'customers' => 'required|string|max:255',
            'dock' => 'required|string|max:255',
            'cycle' => 'required|integer|min:1',
            'address' => 'required|string|max:50',
        ]);

        try {
            $delivery->update($validated);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data delivery berhasil diupdate',
                    'data' => $delivery
                ]);
            }
            
            return redirect()
                ->route('deliveries.index')
                ->with('success', 'Data delivery berhasil diupdate!');
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
     * Delete delivery
     */
    public function destroy(Delivery $delivery)
    {
        try {
            $delivery->forceDelete();
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data delivery berhasil dihapus!'
                ]);
            }
            
            return redirect()
                ->route('deliveries.index')
                ->with('success', 'Data delivery berhasil dihapus!');
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
     * Delete all deliveries
     */
    public function deleteAll()
    {
        try {
            $count = Delivery::count();
            Delivery::withTrashed()->forceDelete();
            
            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus {$count} data delivery"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Find delivery by DN number
     */
    public function findByDn(Request $request)
    {
        $noDn = $request->get('no_dn');
        
        if (!$noDn) {
            return response()->json([
                'success' => false,
                'message' => 'No DN tidak boleh kosong'
            ], 400);
        }
        
        $delivery = Delivery::where('no_dn', $noDn)->first();
        
        if (!$delivery) {
            return response()->json([
                'success' => false,
                'message' => 'Data delivery dengan DN ' . $noDn . ' tidak ditemukan',
                'data' => null
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Data ditemukan',
            'data' => [
                'id' => $delivery->id,
                'no_dn' => $delivery->no_dn,
                'route' => $delivery->route,
                'logistic_partners' => $delivery->logistic_partners,
                'customers' => $delivery->customers,
                'dock' => $delivery->dock,
                'cycle' => $delivery->cycle,
                'address' => $delivery->address,
                'status' => $delivery->status,
                'status_label' => $delivery->status_label,
                'business_hours_elapsed' => $delivery->business_hours_elapsed,
                'scan_to_delivery' => $delivery->scan_to_delivery?->format('Y-m-d H:i:s'),
            ]
        ]);
    }

     public function andon(Request $request)
    {
        $perPage = $request->get('per_page', 50);
        
        // Query dasar - sama seperti index tapi tanpa search
        $query = Delivery::query();
        
        // Order by scan_to_delivery (yang paling baru di atas)
        $query->orderBy('scan_to_delivery', 'desc');
        
        // Pagination atau all
        if ($perPage === 'all') {
            $deliveries = $query->get();
            $deliveries = new \Illuminate\Pagination\LengthAwarePaginator(
                $deliveries,
                $deliveries->count(),
                $deliveries->count(),
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        } else {
            $deliveries = $query->paginate((int)$perPage)->withQueryString();
        }
        
        // Hitung statistik
        $allDataForStats = Delivery::all();
        $totalAll = $allDataForStats->count();
        $totalNormal = $allDataForStats->filter(fn($d) => $d->status === 'normal')->count();
        $totalDelay = $allDataForStats->filter(fn($d) => $d->status === 'delay')->count();
        
        // Get recent scan dengan fresh() untuk force reload
        $recentScan = \App\Models\History::whereNotNull('completed_at')
            ->orderBy('completed_at', 'desc')
            ->first();
        
        // DEBUG: Log recent scan data (optional)
        if ($recentScan) {
            \Log::info('Recent Scan Data (Delivery Andon):', [
                'no_dn' => $recentScan->no_dn,
                'moved_by' => $recentScan->moved_by,
                'completed_at' => $recentScan->completed_at
            ]);
        }
        
        // Return view andon untuk delivery
        return view('andon.deliveries', compact(
            'deliveries', 
            'totalNormal', 
            'totalDelay',
            'totalAll',
            'recentScan'
        ));
    }

    /**
     * Andon reverse page untuk delivery monitoring (grouped by route & cycle)
     */
    public function andonReverse(Request $request)
    {
        $perPage = $request->get('per_page', 50);
        
        // Get all deliveries
        $query = Delivery::query();
        
        // Get all data
        $allDeliveries = $query->get();
        
        // Group by route and cycle
        $grouped = $allDeliveries->groupBy(function($item) {
            return $item->route . '|' . $item->cycle;
        })->map(function($group) {
            $firstItem = $group->first();
            
            // Calculate status for the group
            // Jika ada satu saja yang delay, group = delay
            $hasDelay = $group->contains(fn($item) => $item->status === 'delay');
            $groupStatus = $hasDelay ? 'delay' : 'normal';
            
            $statusLabel = $groupStatus === 'delay' ? 'Delay' : 'Normal';
            $statusBadge = $groupStatus === 'delay' ? 'bg-danger' : 'bg-success';
            
            return [
                'route' => $firstItem->route,
                'logistic_partners' => $group->pluck('logistic_partners')->unique()->filter()->implode(', '),
                'cycle' => $firstItem->cycle,
                'customers' => $group->pluck('customers')->unique()->filter()->implode(', '),
                'dock' => $group->pluck('dock')->unique()->filter()->implode(', '),
                'scan_to_delivery' => $firstItem->scan_to_delivery ? $firstItem->scan_to_delivery->format('d-m-y H:i') : null,
                'address' => $group->pluck('address')->unique()->filter()->implode(', '),
                'status' => $groupStatus,
                'status_label' => $statusLabel,
                'status_badge' => $statusBadge,
                'no_dns' => $group->pluck('no_dn')->toArray(),
                'dn_count' => $group->count(),
                'scan_datetime' => $firstItem->scan_to_delivery,
            ];
        })->sortByDesc('scan_datetime')->values();
        
        // Pagination manual
        if ($perPage === 'all') {
            $groupedDeliveries = new \Illuminate\Pagination\LengthAwarePaginator(
                $grouped,
                $grouped->count(),
                $grouped->count(),
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        } else {
            $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage();
            $perPageInt = (int)$perPage;
            $currentPageItems = $grouped->slice(($currentPage - 1) * $perPageInt, $perPageInt)->values();
            
            $groupedDeliveries = new \Illuminate\Pagination\LengthAwarePaginator(
                $currentPageItems,
                $grouped->count(),
                $perPageInt,
                $currentPage,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        }
        
        // Statistics
        $totalNormal = $grouped->where('status', 'normal')->count();
        $totalDelay = $grouped->where('status', 'delay')->count();
        
        // Get recent scan dengan fresh() untuk force reload
        $recentScan = \App\Models\History::whereNotNull('completed_at')
            ->orderBy('completed_at', 'desc')
            ->first();
        
        return view('andon.deliveries-group', compact(
            'groupedDeliveries',
            'totalNormal',
            'totalDelay',
            'recentScan'
        ));
    }

    public function getDelayData(Request $request)
    {
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        
        // Query dasar - ambil semua delivery dulu
        $query = Delivery::query();
        
        // Filter by date range pada scan_to_delivery
        if ($dateFrom) {
            $query->whereDate('scan_to_delivery', '>=', $dateFrom);
        }
        
        if ($dateTo) {
            $query->whereDate('scan_to_delivery', '<=', $dateTo);
        }
        
        // Order by scan_to_delivery desc
        $query->orderBy('scan_to_delivery', 'desc');
        
        // Get all data
        $deliveries = $query->get();
        
        // Filter hanya yang delay (karena status adalah calculated/accessor)
        $delayData = $deliveries->filter(function($delivery) {
            return $delivery->status === 'delay';
        })->values();
        
        // Format data untuk response
        $formattedData = $delayData->map(function($delivery) {
            return [
                'id' => $delivery->id,
                'route' => $delivery->route,
                'logistic_partners' => $delivery->logistic_partners,
                'no_dn' => $delivery->no_dn,
                'customers' => $delivery->customers,
                'dock' => $delivery->dock,
                'cycle' => $delivery->cycle,
                'address' => $delivery->address,
                'scan_to_delivery' => $delivery->scan_to_delivery?->format('Y-m-d H:i:s'),
                'scan_to_delivery_formatted' => $delivery->scan_to_delivery?->format('d-m-y H:i'),
                'status' => $delivery->status,
                'status_label' => $delivery->status_label,
                'delay_duration' => $delivery->delay_duration ?? null,
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $formattedData,
            'total' => $formattedData->count(),
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ]
        ]);
    }

}
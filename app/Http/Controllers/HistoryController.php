<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HistoryController extends Controller
{
    /**
     * Display history list
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 50);
        $search = $request->get('search');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        
        $query = History::query();
        
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
        
        // Date range filter
        if ($dateFrom) {
            $query->whereDate('completed_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('completed_at', '<=', $dateTo);
        }
        
        // Order by completed_at (yang paling baru di atas)
        $query->orderBy('completed_at', 'desc');
        
        // Statistics
        $totalAll = History::count();
        
        // Pagination
        if ($perPage === 'all') {
            $allHistories = $query->get();
            $histories = new \Illuminate\Pagination\LengthAwarePaginator(
                $allHistories,
                $allHistories->count(),
                $allHistories->count() ?: 1,
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        } else {
            $histories = $query->paginate((int)$perPage)->withQueryString();
        }
        
        return view('histories.index', compact(
            'histories',
            'totalAll'
        ));
    }

    /**
     * Get history detail for modal
     */
    public function show(History $history)
    {
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $history->id,
                    'route' => $history->route,
                    'logistic_partners' => $history->logistic_partners,
                    'no_dn' => $history->no_dn,
                    'customers' => $history->customers,
                    'dock' => $history->dock,
                    'cycle' => $history->cycle,
                    'address' => $history->address,
                    
                    // Timeline
                    'pulling_datetime' => $history->formatted_pulling_datetime,
                    'delivery_datetime' => $history->formatted_delivery_datetime,
                    'scan_to_shipping' => $history->formatted_scan_to_shipping,
                    'arrival' => $history->formatted_arrival,
                    'scan_to_delivery' => $history->formatted_scan_to_delivery,
                    'completed_at' => $history->formatted_completed_at,
                    
                    // Durations
                    'shipping_duration' => $history->shipping_duration,
                    'loading_duration' => $history->loading_duration,
                    'delivery_duration' => $history->delivery_duration,
                    'total_journey_duration' => $history->total_journey_duration,
                    'total_business_hours' => $history->formatted_duration,
                    
                    // User
                    'moved_by' => $history->moved_by,
                ]
            ]);
        }
        
        return view('histories.show', compact('history'));
    }

    /**
     * Scan delivery to history (dipanggil dari halaman Delivery)
     */
    public function scanToHistory(Request $request)
    {
        $validated = $request->validate([
            'no_dn' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        
        try {
            // Find delivery by no_dn
            $delivery = Delivery::where('no_dn', $validated['no_dn'])->first();
            
            if (!$delivery) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data delivery dengan DN "' . $validated['no_dn'] . '" tidak ditemukan!'
                ], 404);
            }
            
            // Check if no_dn already exists in histories
            $existingHistory = History::where('no_dn', $delivery->no_dn)->first();
            if ($existingHistory) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dengan No DN ini sudah ada di History!'
                ], 422);
            }
            
            // Get user name
            $movedBy = 'System';
            if (auth()->check()) {
                $user = auth()->user();
                $movedBy = $user->name ?? $user->email ?? 'User#' . $user->id;
            }
            
            $now = Carbon::now();
            
            // Calculate total business hours
            $totalBusinessHours = 0;
            if ($delivery->scan_to_delivery) {
                $totalBusinessHours = Delivery::calculateBusinessHours($delivery->scan_to_delivery, $now);
            }
            
            // Create history record - status selalu 'completed'
            // Sekarang delivery sudah punya semua timeline data
            $history = History::create([
                'route' => $delivery->route,
                'logistic_partners' => $delivery->logistic_partners,
                'no_dn' => $delivery->no_dn,
                'customers' => $delivery->customers,
                'dock' => $delivery->dock,
                'cycle' => $delivery->cycle,
                'address' => $delivery->address,
                
                // Timeline - ambil dari delivery (yang sudah dibawa dari shipping)
                'pulling_date' => $delivery->pulling_date,
                'pulling_time' => $delivery->pulling_time,
                'delivery_date' => $delivery->delivery_date,
                'delivery_time' => $delivery->delivery_time,
                'scan_to_shipping' => $delivery->scan_to_shipping,
                'arrival' => $delivery->arrival,
                'scan_to_delivery' => $delivery->scan_to_delivery,
                'completed_at' => $now,
                
                'final_status' => 'completed', // Selalu completed
                'total_business_hours' => round($totalBusinessHours, 2),
                'moved_by' => $movedBy,
            ]);
            
            // Delete delivery permanently
            $delivery->forceDelete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dipindahkan ke History!',
                'data' => [
                    'no_dn' => $history->no_dn,
                    'route' => $history->route,
                    'customers' => $history->customers,
                    'dock' => $history->dock,
                    'cycle' => $history->cycle,
                    'completed_at' => $history->formatted_completed_at,
                    'moved_by' => $movedBy,
                ]
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
     * Delete history
     */
    public function destroy(History $history)
    {
        try {
            $history->forceDelete();
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data history berhasil dihapus!'
                ]);
            }
            
            return redirect()
                ->route('histories.index')
                ->with('success', 'Data history berhasil dihapus!');
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
     * Delete all histories
     */
    public function deleteAll()
    {
        try {
            $count = History::count();
            History::withTrashed()->forceDelete();
            
            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus {$count} data history"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }
}
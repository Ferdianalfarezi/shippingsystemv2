<?php

namespace App\Http\Controllers;

use App\Models\Preparation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PreparationController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 50);
        $search = $request->get('search');
        
        // Query dasar
        $query = Preparation::query();
        
        // Jika ada pencarian
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('route', 'like', "%{$search}%")
                  ->orWhere('logistic_partners', 'like', "%{$search}%")
                  ->orWhere('no_dn', 'like', "%{$search}%")
                  ->orWhere('customers', 'like', "%{$search}%")
                  ->orWhere('dock', 'like', "%{$search}%")
                  ->orWhere('cycle', 'like', "%{$search}%");
            });
        }
        
        // Order by pulling datetime (yang paling dekat/lewat dulu di atas)
        $query->orderByRaw("CONCAT(pulling_date, ' ', pulling_time) ASC");
        
        // Pagination atau all
        if ($perPage === 'all') {
            $preparations = $query->get();
            // Buat koleksi yang mirip dengan paginator untuk view
            $preparations = new \Illuminate\Pagination\LengthAwarePaginator(
                $preparations,
                $preparations->count(),
                $preparations->count(),
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        } else {
            $preparations = $query->paginate((int)$perPage)->withQueryString();
        }
        
        // Hitung statistik dengan raw query (lebih efisien)
        $totalAll = Preparation::count();
        
        // Di PreparationController@index
        $totalDelay = Preparation::whereRaw(
            "CONCAT(delivery_date, ' ', delivery_time) < NOW() OR CONCAT(pulling_date, ' ', pulling_time) < NOW()"
        )->count();

        $totalOnTime = $totalAll - $totalDelay;
        
        return view('preparations.index', compact('preparations', 'totalDelay', 'totalOnTime', 'totalAll'));
    }

    public function create()
    {
        return view('preparations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'route' => 'required|string|max:255',
            'logistic_partners' => 'required|string|max:255',
            'no_dn' => 'required|string|max:255|unique:preparations,no_dn',
            'customers' => 'required|string|max:255',
            'dock' => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'delivery_time' => 'required',
            'cycle' => 'required|integer|min:1',
            'pulling_date' => 'required|date',
            'pulling_time' => 'required',
        ]);

        try {
            $preparation = Preparation::create($validated);
            
            // Jika request AJAX, return JSON
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data preparation berhasil ditambahkan',
                    'data' => $preparation
                ]);
            }
            
            return redirect()
                ->route('preparations.index')
                ->with('success', 'Data preparation berhasil ditambahkan!');
        } catch (\Exception $e) {
            // Jika request AJAX, return JSON error
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan data: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan data: ' . $e->getMessage());
        }
    }

    public function show(Preparation $preparation)
    {
        return view('preparations.show', compact('preparation'));
    }

    public function edit(Preparation $preparation)
    {
        // Jika request AJAX, return JSON
        if (request()->ajax()) {
            return response()->json([
                'id' => $preparation->id,
                'route' => $preparation->route,
                'logistic_partners' => $preparation->logistic_partners,
                'no_dn' => $preparation->no_dn,
                'customers' => $preparation->customers,
                'dock' => $preparation->dock,
                'delivery_date' => $preparation->delivery_date->format('Y-m-d'),
                'delivery_time' => date('H:i', strtotime($preparation->delivery_time)),
                'cycle' => $preparation->cycle,
                'pulling_date' => $preparation->pulling_date->format('Y-m-d'),
                'pulling_time' => date('H:i', strtotime($preparation->pulling_time)),
            ]);
        }
        
        return view('preparations.edit', compact('preparation'));
    }

    public function update(Request $request, Preparation $preparation)
    {
        $validated = $request->validate([
            'route' => 'required|string|max:255',
            'logistic_partners' => 'required|string|max:255',
            'no_dn' => 'required|string|max:255|unique:preparations,no_dn,' . $preparation->id,
            'customers' => 'required|string|max:255',
            'dock' => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'delivery_time' => 'required',
            'cycle' => 'required|integer|min:1',
            'pulling_date' => 'required|date',
            'pulling_time' => 'required',
        ]);

        try {
            $preparation->update($validated);
            
            // Jika request AJAX, return JSON
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data preparation berhasil diupdate',
                    'data' => $preparation
                ]);
            }
            
            return redirect()
                ->route('preparations.index')
                ->with('success', 'Data preparation berhasil diupdate!');
        } catch (\Exception $e) {
            // Jika request AJAX, return JSON error
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

    public function destroy(Preparation $preparation)
    {
        \Log::info('Delete request received for ID: ' . $preparation->id);
        
        try {
            // GANTI delete() dengan forceDelete() untuk hapus permanen
            $deleted = $preparation->forceDelete();
            
            \Log::info('Force delete result: ' . ($deleted ? 'success' : 'failed'));
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data preparation berhasil dihapus!'
                ]);
            }
            
            return redirect()
                ->route('preparations.index')
                ->with('success', 'Data preparation berhasil dihapus!');
        } catch (\Exception $e) {
            \Log::error('Delete error: ' . $e->getMessage());
            
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

    public function deleteAll()
    {
        try {
            // Hitung hanya data yang belum di-soft delete
            $count = Preparation::count();
            
            // GANTI truncate() dengan withTrashed()->forceDelete()
            // karena truncate() tidak bisa dipakai dengan soft deletes
            Preparation::withTrashed()->forceDelete();
            
            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus {$count} data preparation"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }
}
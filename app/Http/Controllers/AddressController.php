<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use App\Imports\AddressesImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RackImport;

class AddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 50);
        $search = $request->get('search');
        
        // Query dasar
        $query = Address::query();
        
        // Jika ada pencarian
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('part_no', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('part_name', 'like', "%{$search}%")
                  ->orWhere('line', 'like', "%{$search}%")
                  ->orWhere('rack_no', 'like', "%{$search}%");
            });
        }
        
        // Order by terbaru
        $query->orderBy('created_at', 'desc');
        
        // Pagination atau all
        if ($perPage === 'all') {
            $addresses = $query->get();
            $addresses = new \Illuminate\Pagination\LengthAwarePaginator(
                $addresses,
                $addresses->count(),
                $addresses->count(),
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        } else {
            $addresses = $query->paginate((int)$perPage)->withQueryString();
        }
        
        return view('addresses.index', compact('addresses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('addresses.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'part_no'       => 'required|string|max:255',
            'customer_code' => 'nullable|string|max:255',
            'model'         => 'nullable|string|max:255',
            'part_name'     => 'nullable|string|max:255',
            'qty_kbn'       => 'nullable|string|max:100',
            'line'          => 'nullable|string|max:100',
            'rack_no'       => 'nullable|string|max:100',
        ]);

        try {
            $address = Address::create($validated);
            
            // Jika request AJAX, return JSON
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data address berhasil ditambahkan',
                    'data' => $address
                ]);
            }
            
            return redirect()
                ->route('addresses.index')
                ->with('success', 'Data address berhasil ditambahkan!');
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

    /**
     * Display the specified resource.
     */
    public function show(Address $address)
    {
        return view('addresses.import', compact('address'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Address $address)
    {
        // Jika request AJAX, return JSON
        if (request()->ajax()) {
            return response()->json([
                'id' => $address->id,
                'part_no' => $address->part_no,
                'customer_code' => $address->customer_code,
                'model' => $address->model,
                'part_name' => $address->part_name,
                'qty_kbn' => $address->qty_kbn,
                'line' => $address->line,
                'rack_no' => $address->rack_no,
            ]);
        }
        
        return view('addresses.edit', compact('address'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Address $address)
    {
        $validated = $request->validate([
            'part_no'       => 'required|string|max:255',
            'customer_code' => 'nullable|string|max:255',
            'model'         => 'nullable|string|max:255',
            'part_name'     => 'nullable|string|max:255',
            'qty_kbn'       => 'nullable|string|max:100',
            'line'          => 'nullable|string|max:100',
            'rack_no'       => 'nullable|string|max:100',
        ]);

        try {
            $address->update($validated);
            
            // Jika request AJAX, return JSON
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data address berhasil diupdate',
                    'data' => $address
                ]);
            }
            
            return redirect()
                ->route('addresses.index')
                ->with('success', 'Data address berhasil diupdate!');
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Address $address)
    {
        \Log::info('Delete request received for ID: ' . $address->id);
        
        try {
            $deleted = $address->delete();
            
            \Log::info('Delete result: ' . ($deleted ? 'success' : 'failed'));
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data address berhasil dihapus!'
                ]);
            }
            
            return redirect()
                ->route('addresses.index')
                ->with('success', 'Data address berhasil dihapus!');
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

    /**
     * Delete all addresses
     */
    public function deleteAll()
    {
        try {
            $count = Address::count();
            Address::truncate();
            
            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus {$count} data address"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new AddressesImport, $request->file('file'));

            return response()->json([
                'success' => true,
                'message' => 'Import Excel berhasil!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show import page (optional)
     */
    public function importPage()
    {
        return view('addresses.import');
    }

    public function importRack(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            $import = new RackImport();
            Excel::import($import, $request->file('file'));
            
            $results = $import->getResults();

            return response()->json([
                'success' => true,
                'message' => "Update rack berhasil!",
                'updated' => $results['updated'],
                'not_found' => $results['not_found'],
                'not_found_parts' => $results['not_found_parts'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import gagal: ' . $e->getMessage()
            ], 500);
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Admaddress;
use Illuminate\Http\Request;
use App\Imports\AdmaddressesImport;
use Maatwebsite\Excel\Facades\Excel;

class AdmaddressController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 50);
        $search  = $request->get('search');

        $query = Admaddress::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('part_no',        'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%")
                  ->orWhere('model',         'like', "%{$search}%")
                  ->orWhere('part_name',     'like', "%{$search}%")
                  ->orWhere('line',          'like', "%{$search}%")
                  ->orWhere('rack_no',       'like', "%{$search}%");
            });
        }

        $query->orderBy('created_at', 'desc');

        if ($perPage === 'all') {
            $data = $query->get();
            $admaddresses = new \Illuminate\Pagination\LengthAwarePaginator(
                $data, $data->count(), $data->count(), 1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        } else {
            $admaddresses = $query->paginate((int)$perPage)->withQueryString();
        }

        return view('admaddresses.index', compact('admaddresses'));
    }

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
            $admaddress = Admaddress::create($validated);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Data berhasil ditambahkan', 'data' => $admaddress]);
            }
            return redirect()->route('admaddresses.index')->with('success', 'Data berhasil ditambahkan!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->withInput()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function edit(Admaddress $admaddress)
    {
        if (request()->ajax()) {
            return response()->json($admaddress);
        }
        return view('admaddresses.edit', compact('admaddress'));
    }

    public function update(Request $request, Admaddress $admaddress)
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
            $admaddress->update($validated);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Data berhasil diupdate', 'data' => $admaddress]);
            }
            return redirect()->route('admaddresses.index')->with('success', 'Data berhasil diupdate!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->withInput()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function destroy(Admaddress $admaddress)
    {
        try {
            $admaddress->delete();

            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Data berhasil dihapus!']);
            }
            return redirect()->route('admaddresses.index')->with('success', 'Data berhasil dihapus!');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function deleteAll()
    {
        try {
            $count = Admaddress::count();
            Admaddress::truncate();
            return response()->json(['success' => true, 'message' => "Berhasil menghapus {$count} data"]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls']);

        try {
            Excel::import(new AdmaddressesImport, $request->file('file'));
            return response()->json(['success' => true, 'message' => 'Import Excel berhasil!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Import gagal: ' . $e->getMessage()], 500);
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Imports\AdmAddressesImportv2;
use App\Models\AdmAddressv2;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AdmAddressControllerv2 extends Controller
{
    public function index(Request $request)
    {
        $query = AdmAddressv2::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('part_no', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%")
                  ->orWhere('part_name', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 50);

        if ($perPage === 'all') {
            $total = $query->count();
            $admAddresses = $query->latest()->paginate($total > 0 ? $total : 1);
        } else {
            $admAddresses = $query->latest()->paginate((int) $perPage);
        }

        $admAddresses->appends($request->query());

        return view('admadressesv2.index', compact('admAddresses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'part_no'       => 'required|string|max:255',
            'customer_code' => 'nullable|string|max:255',
            'part_name'     => 'nullable|string|max:255',
            'qty_kbn'       => 'nullable|string|max:255',
            'rack_no'       => 'nullable|string|max:255',
        ]);

        AdmAddressv2::create($validated);

        return redirect()
            ->route('admadressesv2.index')
            ->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit(AdmAddressv2 $admaddressv2)
    {
        return response()->json($admaddressv2);
    }

    public function update(Request $request, AdmAddressv2 $admaddressv2)
    {
        $validated = $request->validate([
            'part_no'       => 'required|string|max:255',
            'customer_code' => 'nullable|string|max:255',
            'part_name'     => 'nullable|string|max:255',
            'qty_kbn'       => 'nullable|string|max:255',
            'rack_no'       => 'nullable|string|max:255',
        ]);

        $admaddressv2->update($validated);

        return response()->json(['message' => 'Data berhasil diupdate.']);
    }

    public function destroy(AdmAddressv2 $admaddressv2)
    {
        $admaddressv2->delete();

        return response()->json(['message' => 'Data berhasil dihapus.']);
    }

    public function deleteAll()
    {
        AdmAddressv2::truncate();

        return response()->json(['message' => 'Semua data berhasil dihapus.']);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ]);

        Excel::import(new AdmAddressesImportv2, $request->file('file'));

        return response()->json(['message' => 'Data master berhasil di-import.']);
    }
}
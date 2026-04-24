<?php

namespace App\Http\Controllers;

use App\Imports\HpmAddressImport;
use App\Models\HpmAddress;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class HpmAddressController extends Controller
{
    public function index(Request $request)
    {
        $query = HpmAddress::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('part_no', 'like', "%{$search}%")
                  ->orWhere('part_name', 'like', "%{$search}%")
                  ->orWhere('rack_no', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 50);
        if ($perPage === 'all') {
            $addresses = $query->orderBy('part_no')->get();
            // Wrap in paginator-like object agar view konsisten
            $addresses = new \Illuminate\Pagination\LengthAwarePaginator(
                $addresses,
                $addresses->count(),
                $addresses->count() ?: 1,
                1
            );
        } else {
            $addresses = $query->orderBy('part_no')->paginate((int) $perPage)->withQueryString();
        }

        return view('hpm-addresses.index', compact('addresses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'part_no'   => 'required|string|unique:hpm_addresses,part_no',
            'part_name' => 'nullable|string',
            'rack_no'   => 'nullable|string',
        ]);

        HpmAddress::create($request->only('part_no', 'part_name', 'rack_no'));

        return response()->json(['message' => 'Data berhasil ditambahkan']);
    }

    public function edit(HpmAddress $hpmAddress)
    {
        return response()->json($hpmAddress);
    }

    public function update(Request $request, HpmAddress $hpmAddress)
    {
        $request->validate([
            'part_no'   => 'required|string|unique:hpm_addresses,part_no,' . $hpmAddress->id,
            'part_name' => 'nullable|string',
            'rack_no'   => 'nullable|string',
        ]);

        $hpmAddress->update($request->only('part_no', 'part_name', 'rack_no'));

        return response()->json(['message' => 'Data berhasil diupdate']);
    }

    public function destroy(HpmAddress $hpmAddress)
    {
        $hpmAddress->delete();
        return response()->json(['message' => 'Data berhasil dihapus']);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $import = new HpmAddressImport();
        Excel::import($import, $request->file('file'));

        return response()->json([
            'message' => "Import selesai: {$import->created} data baru, {$import->updated} diupdate, {$import->skipped} dilewati",
            'created' => $import->created,
            'updated' => $import->updated,
            'skipped' => $import->skipped,
        ]);
    }
}
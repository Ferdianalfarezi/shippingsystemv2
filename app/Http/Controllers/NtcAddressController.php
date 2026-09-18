<?php

namespace App\Http\Controllers;

use App\Imports\NtcAddressesImport;
use App\Models\NtcAddress;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class NtcAddressController extends Controller
{
    public function index(Request $request)
    {
        $query = NtcAddress::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('part_no', 'like', "%{$search}%")
                    ->orWhere('customer_code', 'like', "%{$search}%")
                    ->orWhere('part_name', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 50);
        $ntcAddresses = $perPage === 'all'
            ? $query->paginate(max($query->count(), 1))
            : $query->paginate((int) $perPage);

        return view('ntcaddresses.index', compact('ntcAddresses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'part_no' => 'required|string|max:255|unique:ntc_addresses,part_no',
            'customer_code' => 'nullable|string|max:255',
            'part_name' => 'nullable|string|max:255',
            'rack_no' => 'nullable|string|max:255',
        ]);

        NtcAddress::create($validated);

        return response()->json(['message' => 'Data NTC Address berhasil ditambahkan.']);
    }

    public function edit(NtcAddress $ntcAddress)
    {
        return response()->json($ntcAddress);
    }

    public function update(Request $request, NtcAddress $ntcAddress)
    {
        $validated = $request->validate([
            'part_no' => 'required|string|max:255|unique:ntc_addresses,part_no,' . $ntcAddress->id,
            'customer_code' => 'nullable|string|max:255',
            'part_name' => 'nullable|string|max:255',
            'rack_no' => 'nullable|string|max:255',
        ]);

        $ntcAddress->update($validated);

        return response()->json(['message' => 'Data NTC Address berhasil diupdate.']);
    }

    public function destroy(NtcAddress $ntcAddress)
    {
        $ntcAddress->delete();

        return response()->json(['message' => 'Data NTC Address berhasil dihapus.']);
    }

    public function deleteAll()
    {
        NtcAddress::query()->delete();

        return response()->json(['message' => 'Semua data NTC Address berhasil dihapus.']);
    }

    public function import(Request $request)
    {
        $request->validate([
            'excel' => 'required|file|mimes:xlsx,xls',
        ]);

        Excel::import(new NtcAddressesImport(), $request->file('excel'));

        return response()->json(['message' => 'Import data NTC address berhasil.']);
    }
}
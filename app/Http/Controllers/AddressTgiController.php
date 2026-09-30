<?php

namespace App\Http\Controllers;

use App\Imports\AddressTgiImport;
use App\Models\AddressTgi;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AddressTgiController extends Controller
{
    public function index(Request $request)
    {
        $query = AddressTgi::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('part_no', 'like', "%{$search}%")
                    ->orWhere('customer_code', 'like', "%{$search}%")
                    ->orWhere('part_name', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 50);
        $addressTgis = $perPage === 'all'
            ? $query->paginate(max($query->count(), 1))
            : $query->paginate((int) $perPage);

        return view('addresstgi.index', compact('addressTgis'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'part_no' => 'required|string|max:255|unique:addresstgi,part_no',
            'customer_code' => 'nullable|string|max:255',
            'part_name' => 'nullable|string|max:255',
            'rack_no' => 'nullable|string|max:255',
        ]);

        $validated['part_no'] = strtoupper($validated['part_no']);

        AddressTgi::create($validated);

        return response()->json(['message' => 'Data TGI Address berhasil ditambahkan.']);
    }

    public function edit(AddressTgi $addressTgi)
    {
        return response()->json($addressTgi);
    }

    public function update(Request $request, AddressTgi $addressTgi)
    {
        $validated = $request->validate([
            'part_no' => 'required|string|max:255|unique:addresstgi,part_no,' . $addressTgi->id,
            'customer_code' => 'nullable|string|max:255',
            'part_name' => 'nullable|string|max:255',
            'rack_no' => 'nullable|string|max:255',
        ]);

        $validated['part_no'] = strtoupper($validated['part_no']);

        $addressTgi->update($validated);

        return response()->json(['message' => 'Data TGI Address berhasil diupdate.']);
    }

    public function destroy(AddressTgi $addressTgi)
    {
        $addressTgi->delete();

        return response()->json(['message' => 'Data TGI Address berhasil dihapus.']);
    }

    public function deleteAll()
    {
        AddressTgi::query()->delete();

        return response()->json(['message' => 'Semua data TGI Address berhasil dihapus.']);
    }

    public function import(Request $request)
    {
        $request->validate([
            'excel' => 'required|file|mimes:xlsx,xls',
        ]);

        Excel::import(new AddressTgiImport(), $request->file('excel'));

        return response()->json(['message' => 'Import data TGI address berhasil.']);
    }
}
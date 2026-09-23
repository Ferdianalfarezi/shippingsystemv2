<?php

namespace App\Http\Controllers;

use App\Imports\AddressHinoImport;
use App\Models\AddressHino;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AddressHinoController extends Controller
{
    public function index(Request $request)
    {
        $query = AddressHino::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('part_no', 'like', "%{$search}%")
                    ->orWhere('customer_code', 'like', "%{$search}%")
                    ->orWhere('part_name', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 50);
        $addressHinos = $perPage === 'all'
            ? $query->paginate(max($query->count(), 1))
            : $query->paginate((int) $perPage);

        return view('addresshino.index', compact('addressHinos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'part_no' => 'required|string|max:255|unique:addresshino,part_no',
            'customer_code' => 'nullable|string|max:255',
            'part_name' => 'nullable|string|max:255',
            'rack_no' => 'nullable|string|max:255',
        ]);

        AddressHino::create($validated);

        return response()->json(['message' => 'Data Hino Address berhasil ditambahkan.']);
    }

    public function edit(AddressHino $addressHino)
    {
        return response()->json($addressHino);
    }

    public function update(Request $request, AddressHino $addressHino)
    {
        $validated = $request->validate([
            'part_no' => 'required|string|max:255|unique:addresshino,part_no,' . $addressHino->id,
            'customer_code' => 'nullable|string|max:255',
            'part_name' => 'nullable|string|max:255',
            'rack_no' => 'nullable|string|max:255',
        ]);

        $addressHino->update($validated);

        return response()->json(['message' => 'Data Hino Address berhasil diupdate.']);
    }

    public function destroy(AddressHino $addressHino)
    {
        $addressHino->delete();

        return response()->json(['message' => 'Data Hino Address berhasil dihapus.']);
    }

    public function deleteAll()
    {
        AddressHino::query()->delete();

        return response()->json(['message' => 'Semua data Hino Address berhasil dihapus.']);
    }

    public function import(Request $request)
    {
        $request->validate([
            'excel' => 'required|file|mimes:xlsx,xls',
        ]);

        Excel::import(new AddressHinoImport(), $request->file('excel'));

        return response()->json(['message' => 'Import data Hino address berhasil.']);
    }
}
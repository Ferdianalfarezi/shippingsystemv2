<?php

namespace App\Http\Controllers;

use App\Imports\AddressFutabaImport;
use App\Models\AddressFutaba;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AddressFutabaController extends Controller
{
    public function index(Request $request)
    {
        $query = AddressFutaba::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('part_no', 'like', "%{$search}%")
                    ->orWhere('customer_code', 'like', "%{$search}%")
                    ->orWhere('part_name', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 50);
        $addressFutabas = $perPage === 'all'
            ? $query->paginate(max($query->count(), 1))
            : $query->paginate((int) $perPage);

        return view('addressfutaba.index', compact('addressFutabas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'part_no' => 'required|string|max:255|unique:addressfutaba,part_no',
            'customer_code' => 'nullable|string|max:255',
            'part_name' => 'nullable|string|max:255',
            'rack_no' => 'nullable|string|max:255',
        ]);

        AddressFutaba::create($validated);

        return response()->json(['message' => 'Data Futaba Address berhasil ditambahkan.']);
    }

    public function edit(AddressFutaba $addressFutaba)
    {
        return response()->json($addressFutaba);
    }

    public function update(Request $request, AddressFutaba $addressFutaba)
    {
        $validated = $request->validate([
            'part_no' => 'required|string|max:255|unique:addressfutaba,part_no,' . $addressFutaba->id,
            'customer_code' => 'nullable|string|max:255',
            'part_name' => 'nullable|string|max:255',
            'rack_no' => 'nullable|string|max:255',
        ]);

        $addressFutaba->update($validated);

        return response()->json(['message' => 'Data Futaba Address berhasil diupdate.']);
    }

    public function destroy(AddressFutaba $addressFutaba)
    {
        $addressFutaba->delete();

        return response()->json(['message' => 'Data Futaba Address berhasil dihapus.']);
    }

    public function deleteAll()
    {
        AddressFutaba::query()->delete();

        return response()->json(['message' => 'Semua data Futaba Address berhasil dihapus.']);
    }

    public function import(Request $request)
    {
        $request->validate([
            'excel' => 'required|file|mimes:xlsx,xls',
        ]);

        Excel::import(new AddressFutabaImport(), $request->file('excel'));

        return response()->json(['message' => 'Import data Futaba address berhasil.']);
    }
}
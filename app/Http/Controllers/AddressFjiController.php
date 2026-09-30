<?php

namespace App\Http\Controllers;

use App\Imports\AddressFjiImport;
use App\Models\AddressFji;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AddressFjiController extends Controller
{
    public function index(Request $request)
    {
        $query = AddressFji::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('part_no', 'like', "%{$search}%")
                    ->orWhere('customer_code', 'like', "%{$search}%")
                    ->orWhere('part_name', 'like', "%{$search}%")
                    ->orWhere('kategori', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 50);
        $addressFjis = $perPage === 'all'
            ? $query->paginate(max($query->count(), 1))
            : $query->paginate((int) $perPage);

        return view('addressfji.index', compact('addressFjis'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'part_no' => 'required|string|max:255|unique:addressfji,part_no',
            'customer_code' => 'nullable|string|max:255',
            'part_name' => 'nullable|string|max:255',
            'rack_no' => 'nullable|string|max:255',
            'kategori' => 'nullable|string|max:255',
        ]);

        AddressFji::create($validated);

        return response()->json(['message' => 'Data FJI Address berhasil ditambahkan.']);
    }

    public function edit(AddressFji $addressFji)
    {
        return response()->json($addressFji);
    }

    public function update(Request $request, AddressFji $addressFji)
    {
        $validated = $request->validate([
            'part_no' => 'required|string|max:255|unique:addressfji,part_no,' . $addressFji->id,
            'customer_code' => 'nullable|string|max:255',
            'part_name' => 'nullable|string|max:255',
            'rack_no' => 'nullable|string|max:255',
            'kategori' => 'nullable|string|max:255',
        ]);

        $addressFji->update($validated);

        return response()->json(['message' => 'Data FJI Address berhasil diupdate.']);
    }

    public function destroy(AddressFji $addressFji)
    {
        $addressFji->delete();

        return response()->json(['message' => 'Data FJI Address berhasil dihapus.']);
    }

    public function deleteAll()
    {
        AddressFji::query()->delete();

        return response()->json(['message' => 'Semua data FJI Address berhasil dihapus.']);
    }

    public function import(Request $request)
    {
        $request->validate([
            'excel' => 'required|file|mimes:xlsx,xls',
        ]);

        Excel::import(new AddressFjiImport(), $request->file('excel'));

        return response()->json(['message' => 'Import data FJI address berhasil.']);
    }
}
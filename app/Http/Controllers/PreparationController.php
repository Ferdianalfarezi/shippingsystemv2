<?php

namespace App\Http\Controllers;

use App\Models\Preparation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PreparationController extends Controller
{
    public function index()
    {
        $preparations = Preparation::latest()->paginate(20);
        return view('preparations.index', compact('preparations'));
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
        try {
            $preparation->delete();
            
            return redirect()
                ->route('preparations.index')
                ->with('success', 'Data preparation berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\ShippingMatrix;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShippingMatrixController extends Controller
{
    public function index()
    {
        $data = ShippingMatrix::orderBy('customers')
            ->orderBy('dock')
            ->orderBy('cycle')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function batchSave(Request $request)
    {
        $validated = $request->validate([
            'configs' => 'array',
            'configs.*.id' => 'nullable|integer|exists:shipping_matrices,id',
            'configs.*.customers' => 'required|string|max:255',
            'configs.*.dock' => 'required|string|max:255',
            'configs.*.cycle' => 'required|string|max:50',
            'configs.*.address' => 'required|string|max:50',
            'deleted_ids' => 'array',
            'deleted_ids.*' => 'integer',
        ]);

        // Cek duplikat kombinasi di dalam payload yang dikirim
        $seen = [];
        foreach ($validated['configs'] ?? [] as $config) {
            $key = mb_strtolower(trim($config['customers'])) . '|'
                 . mb_strtolower(trim($config['dock'])) . '|'
                 . trim($config['cycle']);

            if (isset($seen[$key])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kombinasi Customer "' . $config['customers'] . '", Dock "' . $config['dock'] . '", Cycle "' . $config['cycle'] . '" duplikat dalam form!',
                ], 422);
            }
            $seen[$key] = true;
        }

        DB::beginTransaction();

        try {
            if (!empty($validated['deleted_ids'])) {
                ShippingMatrix::whereIn('id', $validated['deleted_ids'])->delete();
            }

            foreach ($validated['configs'] ?? [] as $config) {
                $payload = [
                    'customers' => trim($config['customers']),
                    'dock' => trim($config['dock']),
                    'cycle' => trim($config['cycle']),
                    'address' => $config['address'],
                ];

                // Cek bentrok dengan data lain (selain dirinya sendiri kalau lagi update)
                $conflict = ShippingMatrix::whereRaw('LOWER(customers) = ?', [mb_strtolower($payload['customers'])])
                    ->whereRaw('LOWER(dock) = ?', [mb_strtolower($payload['dock'])])
                    ->where('cycle', $payload['cycle'])
                    ->when(!empty($config['id']), fn($q) => $q->where('id', '!=', $config['id']))
                    ->exists();

                if ($conflict) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Kombinasi Customer "' . $payload['customers'] . '", Dock "' . $payload['dock'] . '", Cycle "' . $payload['cycle'] . '" sudah ada di konfigurasi lain!',
                    ], 422);
                }

                if (!empty($config['id'])) {
                    ShippingMatrix::where('id', $config['id'])->update($payload);
                } else {
                    ShippingMatrix::create($payload);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Konfigurasi Matrix Shipping berhasil disimpan',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan konfigurasi: ' . $e->getMessage(),
            ], 500);
        }
    }
}
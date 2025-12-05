<?php

namespace App\Http\Controllers;

use App\Models\LpConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LpConfigController extends Controller
{
    public function index()
    {
        $lpConfigs = LpConfig::orderBy('route')->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $lpConfigs
        ], 200);
    }

    public function batchSave(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validasi input
            $validated = $request->validate([
                'configs' => 'required|array',
                'configs.*.route' => 'required|string',
                'configs.*.logistic_partner' => 'required|string',
                'deleted_ids' => 'nullable|array',
                'deleted_ids.*' => 'integer'
            ]);

            $configs = $validated['configs'];
            $deletedIds = $validated['deleted_ids'] ?? [];

            // Delete configs
            if (!empty($deletedIds)) {
                LpConfig::whereIn('id', $deletedIds)->delete();
            }

            // Track routes untuk validasi duplikat
            $routes = [];

            // Update or Create configs
            foreach ($configs as $configData) {
                $route = $configData['route'];

                // Check duplikat route dalam batch
                if (in_array($route, $routes)) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => "Route '{$route}' duplikat dalam konfigurasi"
                    ], 422);
                }
                $routes[] = $route;

                if (isset($configData['id']) && $configData['id']) {
                    // Update existing
                    $lpConfig = LpConfig::find($configData['id']);
                    if ($lpConfig) {
                        $lpConfig->update([
                            'route' => $route,
                            'logistic_partner' => $configData['logistic_partner']
                        ]);
                    }
                } else {
                    // Create new - check if route already exists
                    $existing = LpConfig::where('route', $route)->first();
                    if ($existing) {
                        DB::rollBack();
                        return response()->json([
                            'status' => 'error',
                            'message' => "Route '{$route}' sudah ada dalam database"
                        ], 422);
                    }

                    LpConfig::create([
                        'route' => $route,
                        'logistic_partner' => $configData['logistic_partner']
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Konfigurasi LP berhasil disimpan'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving LP configs: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan konfigurasi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'route' => 'required|string|unique:lp_configs,route',
            'logistic_partner' => 'required|string',
        ]);

        LpConfig::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Konfigurasi Logistic Partner berhasil ditambahkan',
        ], 200);
    }

    public function update(Request $request, LpConfig $lpConfig)
    {
        $validated = $request->validate([
            'route' => 'required|string|unique:lp_configs,route,' . $lpConfig->id,
            'logistic_partner' => 'required|string',
        ]);

        $lpConfig->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Konfigurasi Logistic Partner berhasil diupdate',
        ], 200);
    }

    public function destroy(LpConfig $lpConfig)
    {
        $lpConfig->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Konfigurasi Logistic Partner berhasil dihapus',
        ], 200);
    }
}
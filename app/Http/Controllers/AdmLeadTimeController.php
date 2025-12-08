<?php

namespace App\Http\Controllers;

use App\Models\AdmLeadTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AdmLeadTimeController extends Controller
{
    /**
     * Get all ADM lead time configurations
     */
    public function index()
    {
        try {
            $leadTimes = AdmLeadTime::orderBy('route')
                ->orderBy('dock')
                ->orderBy('cycle')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $leadTimes
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching ADM lead times: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data konfigurasi'
            ], 500);
        }
    }

    /**
     * Store a new ADM lead time configuration
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'route' => 'required|string|max:50',
                'dock' => 'required|string|max:50',
                'cycle' => 'required|string|max:10',
                'lead_time' => 'required|date_format:H:i',
            ], [
                'route.required' => 'Route harus diisi',
                'dock.required' => 'Dock harus diisi',
                'cycle.required' => 'Cycle harus diisi',
                'lead_time.required' => 'Lead Time harus diisi',
                'lead_time.date_format' => 'Format Lead Time harus HH:MM',
            ]);

            // Cek duplikat
            $exists = AdmLeadTime::where('route', $validated['route'])
                ->where('dock', $validated['dock'])
                ->where('cycle', $validated['cycle'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Konfigurasi untuk Route, Dock, dan Cycle ini sudah ada'
                ], 422);
            }

            // Format lead_time to H:i:s
            $validated['lead_time'] = $validated['lead_time'] . ':00';

            $leadTime = AdmLeadTime::create($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Konfigurasi lead time berhasil ditambahkan',
                'data' => $leadTime
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error storing ADM lead time: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan konfigurasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing ADM lead time configuration
     */
    public function update(Request $request, $id)
    {
        try {
            $leadTime = AdmLeadTime::findOrFail($id);

            $validated = $request->validate([
                'route' => 'required|string|max:50',
                'dock' => 'required|string|max:50',
                'cycle' => 'required|string|max:10',
                'lead_time' => 'required|date_format:H:i',
            ]);

            // Cek duplikat (exclude current record)
            $exists = AdmLeadTime::where('route', $validated['route'])
                ->where('dock', $validated['dock'])
                ->where('cycle', $validated['cycle'])
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Konfigurasi untuk Route, Dock, dan Cycle ini sudah ada'
                ], 422);
            }

            // Format lead_time to H:i:s
            $validated['lead_time'] = $validated['lead_time'] . ':00';

            $leadTime->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Konfigurasi lead time berhasil diperbarui',
                'data' => $leadTime
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating ADM lead time: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui konfigurasi'
            ], 500);
        }
    }

    /**
     * Delete an ADM lead time configuration
     */
    public function destroy($id)
    {
        try {
            $leadTime = AdmLeadTime::findOrFail($id);
            $leadTime->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Konfigurasi lead time berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting ADM lead time: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus konfigurasi'
            ], 500);
        }
    }

    /**
     * Batch save ADM lead time configurations
     */
    public function batchSave(Request $request)
    {
        try {
            $configs = $request->input('configs', []);
            $deletedIds = $request->input('deleted_ids', []);

            DB::beginTransaction();

            // Delete removed configs
            if (!empty($deletedIds)) {
                AdmLeadTime::whereIn('id', $deletedIds)->delete();
            }

            // Save/Update configs
            $savedCount = 0;
            $updatedCount = 0;

            foreach ($configs as $config) {
                // Format lead_time
                $leadTime = $config['lead_time'];
                if (strlen($leadTime) === 5) {
                    $leadTime .= ':00';
                }

                $data = [
                    'route' => strtoupper(trim($config['route'])),
                    'dock' => strtoupper(trim($config['dock'])),
                    'cycle' => trim($config['cycle']),
                    'lead_time' => $leadTime,
                ];

                if (!empty($config['id'])) {
                    // Update existing
                    $existing = AdmLeadTime::find($config['id']);
                    if ($existing) {
                        $existing->update($data);
                        $updatedCount++;
                    }
                } else {
                    // Check for duplicate before insert
                    $exists = AdmLeadTime::where('route', $data['route'])
                        ->where('dock', $data['dock'])
                        ->where('cycle', $data['cycle'])
                        ->exists();

                    if (!$exists) {
                        AdmLeadTime::create($data);
                        $savedCount++;
                    }
                }
            }

            DB::commit();

            $message = 'Konfigurasi berhasil disimpan.';
            if ($savedCount > 0) {
                $message .= " {$savedCount} data baru ditambahkan.";
            }
            if ($updatedCount > 0) {
                $message .= " {$updatedCount} data diperbarui.";
            }
            if (count($deletedIds) > 0) {
                $message .= " " . count($deletedIds) . " data dihapus.";
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'saved' => $savedCount,
                'updated' => $updatedCount,
                'deleted' => count($deletedIds)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error batch saving ADM lead times: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan konfigurasi: ' . $e->getMessage()
            ], 500);
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\PullingMatrix;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PullingMatrixController extends Controller
{
    /**
     * Get all pulling matrix configurations.
     */
    public function index()
    {
        try {
            $matrices = PullingMatrix::orderBy('route')
                ->orderBy('dock')
                ->orderBy('cycle')
                ->get();

            return response()->json([
                'status' => 'success',
                'data'   => $matrices,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching pulling matrices: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengambil data konfigurasi',
            ], 500);
        }
    }

    /**
     * Store a new pulling matrix configuration.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'route'        => 'required|string|max:100',
                'dock'         => 'required|string|max:100',
                'cycle'        => 'required|string|max:20',
                'pulling_time' => 'required|date_format:H:i',
            ], [
                'pulling_time.date_format' => 'Format Pulling Time harus HH:MM',
            ]);

            // Cek duplikat
            $exists = PullingMatrix::where('route', strtoupper($validated['route']))
                ->where('dock', strtoupper($validated['dock']))
                ->where('cycle', $validated['cycle'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Konfigurasi untuk Route, Dock, dan Cycle ini sudah ada',
                ], 422);
            }

            $matrix = PullingMatrix::create([
                'route'        => strtoupper(trim($validated['route'])),
                'dock'         => strtoupper(trim($validated['dock'])),
                'cycle'        => trim($validated['cycle']),
                'pulling_time' => $validated['pulling_time'] . ':00',
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Konfigurasi pulling matrix berhasil ditambahkan',
                'data'    => $matrix,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error storing pulling matrix: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan konfigurasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing pulling matrix configuration.
     */
    public function update(Request $request, $id)
    {
        try {
            $matrix = PullingMatrix::findOrFail($id);

            $validated = $request->validate([
                'route'        => 'required|string|max:100',
                'dock'         => 'required|string|max:100',
                'cycle'        => 'required|string|max:20',
                'pulling_time' => 'required|date_format:H:i',
            ]);

            // Cek duplikat (exclude current record)
            $exists = PullingMatrix::where('route', strtoupper($validated['route']))
                ->where('dock', strtoupper($validated['dock']))
                ->where('cycle', $validated['cycle'])
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Konfigurasi untuk Route, Dock, dan Cycle ini sudah ada',
                ], 422);
            }

            $matrix->update([
                'route'        => strtoupper(trim($validated['route'])),
                'dock'         => strtoupper(trim($validated['dock'])),
                'cycle'        => trim($validated['cycle']),
                'pulling_time' => $validated['pulling_time'] . ':00',
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Konfigurasi pulling matrix berhasil diperbarui',
                'data'    => $matrix,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating pulling matrix: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memperbarui konfigurasi',
            ], 500);
        }
    }

    /**
     * Delete a pulling matrix configuration.
     */
    public function destroy($id)
    {
        try {
            $matrix = PullingMatrix::findOrFail($id);
            $matrix->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Konfigurasi pulling matrix berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting pulling matrix: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menghapus konfigurasi',
            ], 500);
        }
    }

    /**
     * Batch save pulling matrix configurations (add/update/delete in one call).
     */
    public function batchSave(Request $request)
    {
        try {
            $configs    = $request->input('configs', []);
            $deletedIds = $request->input('deleted_ids', []);

            DB::beginTransaction();

            // Hapus yang di-mark deleted
            if (!empty($deletedIds)) {
                PullingMatrix::whereIn('id', $deletedIds)->delete();
            }

            $savedCount   = 0;
            $updatedCount = 0;

            foreach ($configs as $config) {
                // Normalisasi pulling_time ke H:i:s
                $pullingTime = $config['pulling_time'] ?? '00:00';
                if (strlen($pullingTime) === 5) {
                    $pullingTime .= ':00';
                }

                $data = [
                    'route'        => strtoupper(trim($config['route'])),
                    'dock'         => strtoupper(trim($config['dock'])),
                    'cycle'        => trim($config['cycle']),
                    'pulling_time' => $pullingTime,
                ];

                if (!empty($config['id']) && !str_starts_with((string) $config['id'], 'new_')) {
                    // Update existing
                    $existing = PullingMatrix::find($config['id']);
                    if ($existing) {
                        $existing->update($data);
                        $updatedCount++;
                    }
                } else {
                    // Insert baru — skip jika duplikat
                    $exists = PullingMatrix::where('route', $data['route'])
                        ->where('dock', $data['dock'])
                        ->where('cycle', $data['cycle'])
                        ->exists();

                    if (!$exists) {
                        PullingMatrix::create($data);
                        $savedCount++;
                    }
                }
            }

            DB::commit();

            $message = 'Konfigurasi berhasil disimpan.';
            if ($savedCount > 0)        $message .= " {$savedCount} data baru ditambahkan.";
            if ($updatedCount > 0)      $message .= " {$updatedCount} data diperbarui.";
            if (count($deletedIds) > 0) $message .= ' ' . count($deletedIds) . ' data dihapus.';

            return response()->json([
                'status'  => 'success',
                'message' => $message,
                'saved'   => $savedCount,
                'updated' => $updatedCount,
                'deleted' => count($deletedIds),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error batch saving pulling matrices: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan konfigurasi: ' . $e->getMessage(),
            ], 500);
        }
    }
}
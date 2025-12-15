<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdvertisementController extends Controller
{
    public function index()
    {
        $advertisements = Advertisement::orderBy('start_time', 'asc')->get();
        $totalActive = $advertisements->where('is_active', true)->count();
        $totalInactive = $advertisements->where('is_active', false)->count();
        
        return view('advertisements.index', compact('advertisements', 'totalActive', 'totalInactive'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:image,video',
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,mp4,webm|max:51200', // max 50MB
            'start_time' => 'required|date_format:H:i',
            'duration' => 'required|integer|min:5|max:210',
        ]);

        // Cek apakah jam sudah terisi
        if (Advertisement::isTimeSlotTaken($request->start_time . ':00')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jam yang anda pilih telah terisi'
            ], 422);
        }

        // Upload file
        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('advertisements', $filename, 'public');

        Advertisement::create([
            'title' => $request->title,
            'type' => $request->type,
            'file_path' => $path,
            'start_time' => $request->start_time . ':00',
            'duration' => $request->duration,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Iklan berhasil ditambahkan'
        ]);
    }

    public function edit(Advertisement $advertisement)
    {
        return response()->json($advertisement);
    }

    public function update(Request $request, Advertisement $advertisement)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:image,video',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,webm|max:51200',
            'start_time' => 'required|date_format:H:i',
            'duration' => 'required|integer|min:5|max:210',
        ]);

        // Cek apakah jam sudah terisi (exclude current)
        if (Advertisement::isTimeSlotTaken($request->start_time . ':00', $advertisement->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jam yang anda pilih telah terisi'
            ], 422);
        }

        $data = [
            'title' => $request->title,
            'type' => $request->type,
            'start_time' => $request->start_time . ':00',
            'duration' => $request->duration,
            'is_active' => $request->has('is_active') ? true : false,
        ];

        // Upload file baru jika ada
        if ($request->hasFile('file')) {
            // Hapus file lama
            if ($advertisement->file_path && Storage::disk('public')->exists($advertisement->file_path)) {
                Storage::disk('public')->delete($advertisement->file_path);
            }

            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $data['file_path'] = $file->storeAs('advertisements', $filename, 'public');
        }

        $advertisement->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Iklan berhasil diupdate'
        ]);
    }

    public function destroy(Advertisement $advertisement)
    {
        // Hapus file
        if ($advertisement->file_path && Storage::disk('public')->exists($advertisement->file_path)) {
            Storage::disk('public')->delete($advertisement->file_path);
        }

        $advertisement->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Iklan berhasil dihapus'
        ]);
    }

    public function toggleActive(Advertisement $advertisement)
    {
        // Jika mau aktifkan, cek dulu apakah jam nya bentrok
        if (!$advertisement->is_active) {
            if (Advertisement::isTimeSlotTaken($advertisement->start_time, $advertisement->id)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Jam yang anda pilih telah terisi oleh iklan active lain'
                ], 422);
            }
        }

        $advertisement->update(['is_active' => !$advertisement->is_active]);

        return response()->json([
            'status' => 'success',
            'message' => 'Status iklan berhasil diubah'
        ]);
    }

    /**
     * API: Check apakah ada iklan yang harus ditampilkan sekarang
     */
    public function checkCurrentAd()
    {
        $ad = Advertisement::getAdForCurrentMinute();

        if ($ad) {
            return response()->json([
                'show' => true,
                'ad' => [
                    'id' => $ad->id,
                    'title' => $ad->title,
                    'type' => $ad->type,
                    'file_url' => asset('storage/' . $ad->file_path),
                    'duration' => $ad->duration,
                    'start_time' => $ad->start_time, // untuk tracking perubahan
                ]
            ]);
        }

        return response()->json(['show' => false]);
    }

    /**
     * API: Mark iklan sudah ditampilkan hari ini (via session/localStorage di frontend)
     */
    public function markAsShown(Request $request)
    {
        // Logic tracking bisa ditambah di sini kalau perlu
        return response()->json(['status' => 'ok']);
    }
}
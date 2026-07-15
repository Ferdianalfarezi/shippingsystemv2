<?php

namespace App\Http\Controllers;

use App\Models\ArsAdm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ArsAdmController extends Controller
{
    public function index(Request $request)
    {
        $search  = $request->get('search');
        $perPage = $request->get('per_page', 50);

        $query = ArsAdm::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('part_no',     'like', "%{$search}%")
                  ->orWhere('part_cat',  'like', "%{$search}%")
                  ->orWhere('wh_zone',   'like', "%{$search}%")
                  ->orWhere('rack_no',   'like', "%{$search}%")
                  ->orWhere('part_type', 'like', "%{$search}%")
                  ->orWhere('area_code', 'like', "%{$search}%");
            });
        }

        $arsadms = $perPage === 'all'
            ? $query->orderBy('part_no')->paginate(PHP_INT_MAX)
            : $query->orderBy('part_no')->paginate((int) $perPage);

        return view('arsadms.index', compact('arsadms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'part_no'      => 'required|string|unique:ars_adms,part_no',
            'min'          => 'nullable|string',
            'max'          => 'nullable|string',
            'part_cat'     => 'nullable|string',
            'packing_type' => 'nullable|string',
            'area_code'    => 'nullable|string',
            'part_type'    => 'nullable|string',
            'wh_zone'      => 'nullable|string',
            'rack_no'      => 'nullable|string',
            'rack_layer'   => 'nullable|string',
        ]);

        ArsAdm::create($validated);

        return response()->json(['message' => 'Data berhasil ditambahkan.']);
    }

    public function edit(ArsAdm $arsadm)
    {
        return response()->json($arsadm);
    }

    public function update(Request $request, ArsAdm $arsadm)
    {
        $validated = $request->validate([
            'part_no'      => 'required|string|unique:ars_adms,part_no,' . $arsadm->id,
            'min'          => 'nullable|string',
            'max'          => 'nullable|string',
            'part_cat'     => 'nullable|string',
            'packing_type' => 'nullable|string',
            'area_code'    => 'nullable|string',
            'part_type'    => 'nullable|string',
            'wh_zone'      => 'nullable|string',
            'rack_no'      => 'nullable|string',
            'rack_layer'   => 'nullable|string',
        ]);

        $arsadm->update($validated);

        return response()->json(['message' => 'Data berhasil diperbarui.']);
    }

    public function destroy(ArsAdm $arsadm)
    {
        $arsadm->delete();

        return response()->json(['message' => 'Data berhasil dihapus.']);
    }

    public function deleteAll()
    {
        ArsAdm::truncate();

        return response()->json(['message' => 'Semua data berhasil dihapus.']);
    }

    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            $spreadsheet = IOFactory::load($request->file('excel_file')->getRealPath());
            $rows        = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

            // Normalize header row ke snake_case, buang kolom index/kosong di depan
            $rawHeader = $rows[0];
            $header    = [];
            $startCol  = 0;

            foreach ($rawHeader as $i => $h) {
                $normalized = strtolower(trim(str_replace(' ', '_', $h ?? '')));
                // Skip kolom pertama jika kosong atau berisi angka (nomor urut)
                if (empty($normalized) || is_numeric($normalized)) {
                    $startCol = $i + 1;
                    continue;
                }
                $header[$i] = $normalized;
            }

            $inserted = 0;
            $updated  = 0;

            $clean = fn($val) => ($val === null || trim((string) $val) === '' || str_starts_with((string) $val, '#'))
                ? null
                : (string) $val;

            DB::beginTransaction();

            foreach (array_slice($rows, 1) as $row) {
                // Ambil hanya kolom yang ada di header (buang kolom index)
                $data = [];
                foreach ($header as $i => $key) {
                    $data[$key] = $row[$i] ?? null;
                }

                $partNo = trim($data['part_no'] ?? '');
                if (empty($partNo)) {
                    continue;
                }

                $attributes = [
                    'min'          => $clean($data['min']          ?? null),
                    'max'          => $clean($data['max']          ?? null),
                    'part_cat'     => $clean($data['part_cat']     ?? null),
                    'packing_type' => $clean($data['packing_type'] ?? null),
                    'area_code'    => $clean($data['area_code']    ?? null),
                    'part_type'    => $clean($data['part_type']    ?? null),
                    'wh_zone'      => $clean($data['wh_zone']      ?? null),
                    'rack_no'      => $clean($data['rack_no']      ?? null),
                    'rack_layer'   => $clean($data['rack_layer']   ?? null),
                ];

                $existing = ArsAdm::where('part_no', $partNo)->first();

                if ($existing) {
                    $existing->update($attributes);
                    $updated++;
                } else {
                    ArsAdm::create(array_merge(['part_no' => $partNo], $attributes));
                    $inserted++;
                }
            }

            DB::commit();

            return response()->json([
                'message' => "Import selesai. {$inserted} data baru ditambahkan, {$updated} data diperbarui.",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal import: ' . $e->getMessage()], 500);
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\KanbanHpm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Picqer\Barcode\BarcodeGeneratorPNG;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class KanbanHpmController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    $kanbanhpms       = KanbanHpm::orderBy('di_no')->orderBy('item_seq')->get();
    $latestUploadInfo = KanbanHpm::getLatestUploadInfo();

    $kanbanPrintData = $kanbanhpms->map(function ($i) {
        return [
            'date' => !empty($i->datetime) ? explode(' ', trim($i->datetime))[0] : '',
            'dock' => !empty($i->ps_code)  ? substr(trim($i->ps_code), -2) : '',
        ];
    })->values();

    return view('kanbanhpms.index', compact('kanbanhpms', 'latestUploadInfo', 'kanbanPrintData'));
}

    /**
     * Import TXT file (HPM format - fixed-width)
     */
    public function importTxt(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:txt|max:5120',
        ]);

        $file    = $request->file('file');
        $path    = $file->getRealPath();
        $lines   = file($path, FILE_IGNORE_NEW_LINES);

        $uploadedBy = 'System';
        if (Auth::check()) {
            $user       = Auth::user();
            $uploadedBy = $user->name ?? $user->email ?? 'System';
        }

        try {
            $deletedCount = KanbanHpm::count();
            KanbanHpm::truncate();

            $importedCount = 0;
            $generator     = new BarcodeGeneratorPNG();

            foreach ($lines as $rawLine) {
                $line = rtrim($rawLine, "\r\n");

                if (!str_starts_with($line, 'DI')) {
                    continue;
                }

                if (strlen($line) < 298) {
                    $line = str_pad($line, 300);
                }

                $di_no    = trim(substr($line, 0, 16));
                $item_seq = trim(substr($line, 16, 5));

                $from_raw = trim(substr($line, 28, 12));
                $from     = preg_replace('/\s+/', '-', $from_raw);

                $to_part1 = trim(substr($line, 56, 28));
                $to_part2 = trim(substr($line, 84, 14));
                $to_code  = preg_replace('/^1S017\s*/', '', $to_part2);
                $to       = trim($to_code) . '-' . trim($to_part1);

                $supply_address = trim(substr($line, 98, 14));

                $mid_area = trim(substr($line, 112, 22));

                if (str_starts_with($mid_area, '*')) {
                    $ms_id              = preg_replace('/[^*].*/', '', $mid_area);
                    $after_stars        = ltrim($mid_area, '*');
                    $inventory_category = explode(' ', trim($after_stars))[0] ?? '';
                } else {
                    $mid_tokens = preg_split('/\s+/', $mid_area);

                    if (count($mid_tokens) > 1) {
                        $inventory_category = trim(end($mid_tokens));
                        $ms_id              = trim(implode('', array_slice($mid_tokens, 0, -1)));
                    } else {
                        $single = $mid_tokens[0] ?? '';
                        if (preg_match('/([A-Z]{2}\d{2})$/', $single, $m)) {
                            $inventory_category = $m[1];
                            $ms_id              = substr($single, 0, strlen($single) - strlen($m[1]));
                        } else {
                            $ms_id              = $single;
                            $inventory_category = '';
                        }
                    }
                }

                $part_no_raw = substr($line, 134, 23);
                $part_no     = preg_replace('/\s+/', '', $part_no_raw);

                $part_name = trim(substr($line, 180, 41));

                $ps_area = substr($line, 221, 24);
                preg_match('/(HPM\s+\d+[A-Z]+)\s+(\d+)/', $ps_area, $pMatch);
                $ps_code     = isset($pMatch[1]) ? trim($pMatch[1]) : trim(substr($ps_area, 0, 12));
                $order_class = isset($pMatch[2]) ? trim($pMatch[2]) : '';

                $seq_no    = trim(substr($line, 245, 12));
                $kd_lot_no = trim(substr($line, 257, 18));

                $dt_area  = substr($line, 257, 50);
                $ship     = '';
                $datetime = '';

                preg_match('/(\d{2}-\d{2})\s+(\d{2}:\d{2})/', $dt_area, $dtMatch);
                if (isset($dtMatch[1])) {
                    $datetime = trim($dtMatch[1] . ' ' . $dtMatch[2]);
                    $datePos  = strpos($dt_area, $dtMatch[1]);
                    $ship_raw = substr($dt_area, $datePos - 2, 2);
                    $ship     = ltrim($ship_raw, '0') ?: '0';
                }

                $barcode = null;
                if (!empty($part_no)) {
                    try {
                        $barcode = base64_encode($generator->getBarcode(
                            $part_no,
                            $generator::TYPE_CODE_128
                        ));
                    } catch (\Exception $e) {
                        Log::warning("Barcode gen failed for part_no: {$part_no} - " . $e->getMessage());
                    }
                }

                KanbanHpm::create([
                    'di_no'              => $di_no,
                    'item_seq'           => $item_seq,
                    'part_no'            => $part_no,
                    'part_name'          => $part_name,
                    'seq_no'             => $seq_no,
                    'kd_lot_no'          => $kd_lot_no,
                    'ship'               => $ship,
                    'supply_address'     => $supply_address,
                    'from'               => $from,
                    'to'                 => $to,
                    'inventory_category' => $inventory_category,
                    'ms_id'              => $ms_id,
                    'ps_code'            => $ps_code,
                    'order_class'        => $order_class,
                    'datetime'           => $datetime,
                    'barcode'            => $barcode,
                    'last_upload_at'     => now()->toDateTimeString(),
                    'uploaded_by'        => $uploadedBy,
                ]);

                $importedCount++;
            }

            Log::info("KanbanHpm import done. Deleted: {$deletedCount}, Imported: {$importedCount}");

            return redirect()->route('kanbanhpms.index')->with([
                'sweet_alert' => [
                    'type'              => 'success',
                    'title'             => 'Import Berhasil!',
                    'text'              => "{$deletedCount} data lama dihapus dan {$importedCount} data baru berhasil diimport!",
                    'showConfirmButton' => true,
                    'timer'             => 5000,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('KanbanHpm import failed: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect()->route('kanbanhpms.index')->with([
                'sweet_alert' => [
                    'type'              => 'error',
                    'title'             => 'Import Gagal!',
                    'text'              => 'Import gagal: ' . $e->getMessage(),
                    'showConfirmButton' => true,
                ],
            ]);
        }
    }

    public function adjustWeekly(Request $request)
    {
        $request->validate([
            'file_weekly' => 'required|mimes:xlsx,xls|max:20480',
        ]);

        try {
            $path        = $request->file('file_weekly')->getRealPath();
            $spreadsheet = IOFactory::load($path);

            // Ambil active sheet (sheet yang aktif saat file disave)
            // Lebih reliable dari getSheetNames()[0] yang bisa return sheet tersembunyi/helper
            $sheet     = $spreadsheet->getActiveSheet();
            $sheetName = $sheet->getTitle();

            $adjustMap = $this->parseAdjustExcel($path, [$sheetName], 16, 10, 12, 5);

            Log::info("AdjustWeekly: parsed sheet='{$sheetName}', entries=" . count($adjustMap));
            if (empty($adjustMap)) {
                return redirect()->route('kanbanhpms.index')->with([
                    'sweet_alert' => [
                        'type'              => 'warning',
                        'title'             => 'Tidak Ada Data',
                        'text'              => 'Tidak ditemukan data adjustment (semua kolom Adjustment Date/Time kosong).',
                        'showConfirmButton' => true,
                    ],
                ]);
            }

            // ── 3. Update DB ──────────────────────────────────────────────────
            $updated   = 0;
            $sameValue = 0;
            $noAdj     = 0;
            $mapCount  = count($adjustMap);

            $kanbanhpms = KanbanHpm::all();

            foreach ($kanbanhpms as $item) {
                $key = trim($item->kd_lot_no);

                if (isset($adjustMap[$key])) {
                    $adj         = $adjustMap[$key];
                    $newDatetime = $adj['date'] . ' ' . $adj['time'];

                    if ($item->datetime !== $newDatetime) {
                        $item->datetime = $newDatetime;
                        $item->save();
                        $updated++;
                    } else {
                        $sameValue++;
                    }
                } else {
                    $noAdj++;
                }
            }

            Log::info("AdjustWeekly done. MapSize={$mapCount}, Updated={$updated}, SameValue={$sameValue}, NoAdj={$noAdj}");

            return redirect()->route('kanbanhpms.index')->with([
                'sweet_alert' => [
                    'type'              => 'success',
                    'title'             => 'Adjust Weekly Berhasil!',
                    'text'              => "{$updated} data diupdate, {$sameValue} data sudah sama, {$noAdj} data tidak ada di Excel (datetime tidak berubah).",
                    'showConfirmButton' => true,
                    'timer'             => 7000,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('AdjustWeekly failed: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect()->route('kanbanhpms.index')->with([
                'sweet_alert' => [
                    'type'              => 'error',
                    'title'             => 'Adjust Weekly Gagal!',
                    'text'              => 'Error: ' . $e->getMessage(),
                    'showConfirmButton' => true,
                ],
            ]);
        }
    }

    /**
     * Parse Excel dan return mapping kd_lot_no => [date, time]
     * Hanya row yang punya Adjustment Date DAN Time yang dimasukkan.
     *
     * @param  string  $path        Path file Excel
     * @param  array   $sheetNames  Nama-nama sheet yang dibaca
     * @param  int     $colKd       Index kolom KD Lot Number (0-based)
     * @param  int     $colAdjDate  Index kolom Adj Ship Date (0-based)
     * @param  int     $colAdjTime  Index kolom Adj Ship Time (0-based)
     * @param  int     $headerRow   Baris header (1-based); data mulai baris berikutnya
     * @return array
     */
    private function parseAdjustExcel(
        string $path,
        array  $sheetNames,
        int    $colKd,
        int    $colAdjDate,
        int    $colAdjTime,
        int    $headerRow
    ): array {
        $map         = [];
        $spreadsheet = IOFactory::load($path);

        foreach ($sheetNames as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            if (!$sheet) {
                Log::warning("AdjustWeekly: sheet '{$sheetName}' tidak ditemukan, skip.");
                continue;
            }

            $highestRow = $sheet->getHighestDataRow();
            $debugSamples = 0;

            // Iterate per row, baca cell langsung (lebih reliable dari toArray)
            for ($rowIdx = $headerRow + 1; $rowIdx <= $highestRow; $rowIdx++) {
                // +1 karena $headerRow adalah baris header (1-based), data mulai baris berikutnya
                $kdRaw   = trim((string) ($sheet->getCellByColumnAndRow($colKd + 1, $rowIdx)->getValue() ?? ''));
                $adjDate = $sheet->getCellByColumnAndRow($colAdjDate + 1, $rowIdx)->getValue();
                $adjTime = $sheet->getCellByColumnAndRow($colAdjTime + 1, $rowIdx)->getValue();

                // Debug log untuk 3 baris pertama
                if ($debugSamples < 3) {
                    Log::debug("AdjustWeekly row {$rowIdx}: KD=" . var_export($kdRaw, true)
                        . " | Date=" . var_export($adjDate, true) . " (" . gettype($adjDate) . ")"
                        . " | Time=" . var_export($adjTime, true) . " (" . gettype($adjTime) . ")");
                    $debugSamples++;
                }

                if ($kdRaw === '') continue;
                if ($adjDate === null || $adjTime === null) continue;

                $dateStr = $this->formatExcelDate($adjDate);
                $timeStr = $this->formatExcelTime($adjTime);

                if ($dateStr === null || $timeStr === null) {
                    Log::debug("AdjustWeekly SKIP row {$rowIdx}: KD={$kdRaw}"
                        . " dateStr=" . var_export($dateStr, true)
                        . " timeStr=" . var_export($timeStr, true)
                        . " | rawDate=" . var_export($adjDate, true) . " (" . gettype($adjDate) . ")"
                        . " | rawTime=" . var_export($adjTime, true) . " (" . gettype($adjTime) . ")");
                    continue;
                }

                $map[$kdRaw] = [
                    'date' => $dateStr,
                    'time' => $timeStr,
                ];
            }

            Log::info("AdjustWeekly: sheet='{$sheetName}' rows=" . ($highestRow - $headerRow) . " mapped=" . count($map));
        }

        return $map;
    }

    /**
     * Format nilai date dari Excel menjadi "dd-mm"
     *
     * formatData=false -> PhpSpreadsheet return float serial (misal 46118.0 = 06-04-2026)
     * Konversi manual dari epoch Excel (1899-12-30 UTC) tanpa ExcelDate::excelToDateTimeObject()
     * agar bebas dari timezone offset PHP server.
     */
    private function formatExcelDate($value): ?string
    {
        if ($value === null) return null;

        // DateTime object (fallback jika PhpSpreadsheet return object)
        if ($value instanceof \DateTimeInterface) {
            return $value->format('d-m');
        }

        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') return null;

            // Format "YYYYMMDD" integer string (e.g. "20260410" dari EXPORT_CALC)
            if (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $value, $m)) {
                return $m[3] . '-' . $m[2];
            }

            // Format ISO "2026-03-30"
            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $value, $m)) {
                return $m[3] . '-' . $m[2];
            }

            return null;
        }

        // Integer YYYYMMDD (e.g. 20260410 dari EXPORT_CALC)
        // Integer atau float: bisa YYYYMMDD atau Excel serial date
        if (is_int($value) || is_float($value)) {
            $num = (int) $value;
            // Format YYYYMMDD (e.g. 20260410 dari EXPORT_CALC)
            if ($num > 19000101 && $num < 21001231 && strlen((string) $num) === 8) {
                $str = (string) $num;
                return substr($str, 6, 2) . '-' . substr($str, 4, 2);
            }
            // Excel serial date (e.g. 46118 = 2026-04-06)
            // PhpSpreadsheet formatData=false return INTEGER untuk date cell
            // Konversi manual epoch 1899-12-30 UTC -> bebas timezone & locale
            if ($num >= 1 && $num < 100000) {
                $epoch = \Carbon\Carbon::create(1899, 12, 30, 0, 0, 0, 'UTC');
                $dt    = $epoch->addDays($num);
                return $dt->format('d-m');
            }
            return null;
        }

        return null;
    }

    /**
     * Format nilai time dari Excel menjadi "HH:MM"
     *
     * formatData=false -> PhpSpreadsheet return float fraksi hari (misal 0.625 = 15:00)
     */
    private function formatExcelTime($value): ?string
    {
        if ($value === null) return null;

        // DateTime object
        if ($value instanceof \DateTimeInterface) {
            return $value->format('H:i');
        }

        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') return null;

            // Format "HH:MM" atau "HH:MM:SS"
            if (preg_match('/^(\d{1,2}):(\d{2})/', $value, $m)) {
                return str_pad($m[1], 2, '0', STR_PAD_LEFT) . ':' . $m[2];
            }

            // Integer string HHMMSS (e.g. "90000" dari EXPORT_CALC)
            if (preg_match('/^\d{5,6}$/', $value)) {
                $str = str_pad($value, 6, '0', STR_PAD_LEFT);
                return substr($str, 0, 2) . ':' . substr($str, 2, 2);
            }

            return null;
        }

        // Float fraksi hari (0.625 = 15:00) - output formatData=false untuk time cell
        if (is_float($value) && $value >= 0 && $value < 1) {
            $totalSecs = (int) round($value * 86400);
            $h = intdiv($totalSecs, 3600);
            $i = intdiv($totalSecs % 3600, 60);
            return str_pad($h, 2, '0', STR_PAD_LEFT) . ':' . str_pad($i, 2, '0', STR_PAD_LEFT);
        }

        // Integer HHMMSS
        if (is_int($value)) {
            $str = str_pad((string) $value, 6, '0', STR_PAD_LEFT);
            return substr($str, 0, 2) . ':' . substr($str, 2, 2);
        }

        return null;
    }

    /**
     * Parse Excel dan return mapping kd_lot_no => [date, time]
     * Hanya row yang punya Adjustment Date DAN Time yang dimasukkan.
     *
     * @param  string  $path        Path file Excel
     * @param  array   $sheetNames  Nama-nama sheet yang dibaca
     * @param  int     $colKd       Index kolom KD Lot Number (0-based)
     * @param  int     $colAdjDate  Index kolom Adj Ship Date (0-based)
     * @param  int     $colAdjTime  Index kolom Adj Ship Time (0-based)
     * @param  int     $headerRow   Baris header (1-based); data mulai baris berikutnya
     * @return array
     */
    /**
     * Print All records
     */
    public function printAll(Request $request)
    {
        try {
            $kanbanhpms = KanbanHpm::orderBy('di_no')->orderBy('item_seq')->get();

            if ($kanbanhpms->isEmpty()) {
                return redirect()->route('kanbanhpms.index')
                    ->with('error', 'Tidak ada data untuk diprint.');
            }

            return view('kanbanhpms.printnew', compact('kanbanhpms'));

        } catch (\Exception $e) {
            Log::error('KanbanHpm printAll error: ' . $e->getMessage());
            return redirect()->route('kanbanhpms.index')
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Delete a single record
     */
    public function destroy($id)
    {
        try {
            $item = KanbanHpm::findOrFail($id);
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus!',
            ]);
        } catch (\Exception $e) {
            Log::error('KanbanHpm delete error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

   public function printFiltered(Request $request)
{
    try {
        $dates = $request->input('dates', []);
        $docks = $request->input('docks', []);

        $query = KanbanHpm::orderBy('di_no')->orderBy('item_seq');

        if (!empty($dates)) {
            $query->where(function ($q) use ($dates) {
                foreach ($dates as $date) {
                    $q->orWhere('datetime', 'LIKE', $date . '%');
                }
            });
        }

        if (!empty($docks)) {
            $query->where(function ($q) use ($docks) {
                foreach ($docks as $dock) {
                    $q->orWhereRaw("RIGHT(TRIM(ps_code), 2) = ?", [$dock]);
                }
            });
        }

        $kanbanhpms = $query->get();

        if ($kanbanhpms->isEmpty()) {
            return response('<div style="font-family:Arial;padding:40px;text-align:center;color:#888;">
                <h3>Tidak ada data yang sesuai filter.</h3></div>', 200)
                ->header('Content-Type', 'text/html');
        }

        return view('kanbanhpms.printnew', compact('kanbanhpms'));

    } catch (\Exception $e) {
        Log::error('KanbanHpm printFiltered error: ' . $e->getMessage());
        return response('<div style="font-family:Arial;padding:40px;color:red;">Error: '
            . e($e->getMessage()) . '</div>', 500)
            ->header('Content-Type', 'text/html');
    }
}
}
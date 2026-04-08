<?php

namespace App\Http\Controllers;

use App\Models\KanbanHpm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Picqer\Barcode\BarcodeGeneratorPNG;

class KanbanHpmController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kanbanhpms = KanbanHpm::orderBy('di_no')->orderBy('item_seq')->get();
        $latestUploadInfo = KanbanHpm::getLatestUploadInfo();

        return view('kanbanhpms.index', compact('kanbanhpms', 'latestUploadInfo'));
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
                // Normalkan line ending (CRLF -> strip CR)
                $line = rtrim($rawLine, "\r\n");

                // Hanya proses baris data (dimulai dengan "DI")
                if (!str_starts_with($line, 'DI')) {
                    continue;
                }

                // Pastikan panjang minimal
                if (strlen($line) < 298) {
                    $line = str_pad($line, 300);
                }

                /* =============================================
                 *  FIELD PARSING - Fixed-Width Position Map
                 * =============================================
                 *  [0:16]    di_no            DI261610013582
                 *  [16:21]   item_seq         00001
                 *  [28:40]   from_raw         1S017 00
                 *  [56:84]   to_part1         2SAF01
                 *  [84:98]   to_part2         1S017 AE02
                 *  [98:112]  supply_address   EG001 / SETBLS / CH001L
                 *  [112:134] ms_id+inv_cat    REC02+MEAE02 / ***+MEAF02 / LOGKDEXPORTMP+KD71
                 *  [134:157] part_no_raw      11941-5R0 -0001
                 *  [180:221] part_name        STAY COMP, CONVERTER
                 *  [221:245] ps_code + oc     HPM 024AE  1
                 *  [245:257] seq_no           202604130020
                 *  [257:275] kd_lot_no        HPM 00202603005301
                 *  [257+]    ship             2 digit tepat sebelum date (dd-mm)
                 *  [257+]    datetime         dd-mm HH:mm
                 * ============================================= */

                $di_no    = trim(substr($line, 0, 16));
                $item_seq = trim(substr($line, 16, 5));

                // from: "1S017 00" -> "1S017-00"
                $from_raw = trim(substr($line, 28, 12));
                $from     = preg_replace('/\s+/', '-', $from_raw);

                // to: gabungan AE02 (dari to_part2) + 2SAF01 (dari to_part1)
                $to_part1 = trim(substr($line, 56, 28));
                $to_part2 = trim(substr($line, 84, 14));
                $to_code  = preg_replace('/^1S017\s*/', '', $to_part2);
                $to       = trim($to_code) . '-' . trim($to_part1);

                // supply_address: pos 98, length 14
                $supply_address = trim(substr($line, 98, 14));

                // ms_id + inventory_category: area [112:134]
                // Ada 3 format:
                //   1. Normal     : "REC02      MEAE02     " -> ms_id=REC02, inv_cat=MEAE02
                //   2. Bintang    : "***********MEAF02     " -> ms_id='',    inv_cat=MEAF02
                //   3. Nyambung   : "LOGKDEXPORTMPKD71     " -> ms_id=LOGKDEXPORTMP, inv_cat=KD71
                $mid_area = trim(substr($line, 112, 22));

                if (str_starts_with($mid_area, '*')) {
                    // ms_id diisi raw bintangnya, inv_cat setelah bintang
                    $ms_id              = preg_replace('/[^*].*/', '', $mid_area); // ambil bintang saja
                    $after_stars        = ltrim($mid_area, '*');
                    $inventory_category = explode(' ', trim($after_stars))[0] ?? '';

                } else {
                    $mid_tokens = preg_split('/\s+/', $mid_area);

                    if (count($mid_tokens) > 1) {
                        // Format normal: token terakhir = inv_cat, sisanya = ms_id
                        $inventory_category = trim(end($mid_tokens));
                        $ms_id              = trim(implode('', array_slice($mid_tokens, 0, -1)));
                    } else {
                        // Format nyambung (LOGKDEXPORTMPKD71): inv_cat = 4 char terakhir pola [A-Z]{2}\d{2}
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

                // part_no: remove spaces -> "11941-5R0-0001"
                $part_no_raw = substr($line, 134, 23);
                $part_no     = preg_replace('/\s+/', '', $part_no_raw);

                // part_name
                $part_name = trim(substr($line, 180, 41));

                // ps_code & order_class
                $ps_area = substr($line, 221, 24);
                preg_match('/(HPM\s+\d+[A-Z]+)\s+(\d+)/', $ps_area, $pMatch);
                $ps_code     = isset($pMatch[1]) ? trim($pMatch[1]) : trim(substr($ps_area, 0, 12));
                $order_class = isset($pMatch[2]) ? trim($pMatch[2]) : '';

                // seq_no: 12 digit
                $seq_no = trim(substr($line, 245, 12));

                // kd_lot_no
                $kd_lot_no = trim(substr($line, 257, 18));

                // ship & datetime: cari posisi date (dd-mm) di area setelah kd_lot_no
                $dt_area  = substr($line, 257, 50);
                $ship     = '';
                $datetime = '';

                preg_match('/(\d{2}-\d{2})\s+(\d{2}:\d{2})/', $dt_area, $dtMatch);
                if (isset($dtMatch[1])) {
                    $datetime = trim($dtMatch[1] . ' ' . $dtMatch[2]);

                    // ship: 2 digit tepat sebelum posisi date ditemukan
                    $datePos  = strpos($dt_area, $dtMatch[1]);
                    $ship_raw = substr($dt_area, $datePos - 2, 2);
                    $ship     = ltrim($ship_raw, '0') ?: '0';
                }

                // Barcode: generate dari part_no
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

            return view('kanbanhpms.printall', compact('kanbanhpms'));

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
}
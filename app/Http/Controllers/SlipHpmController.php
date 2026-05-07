<?php

namespace App\Http\Controllers;

use App\Models\SlipHpm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SlipHpmController extends Controller
{
    // ───────────────────────────────────────────
    //  INDEX
    // ───────────────────────────────────────────
    public function index()
    {
        $sliphpms         = SlipHpm::orderBy('datetime')->orderBy('ship')->get();
        $latestUploadInfo = SlipHpm::getLatestUploadInfo();

        $slipPrintData = $sliphpms->map(fn($i) => [
            'id'      => $i->id,
            'date'    => $i->datetime ? explode(' ', $i->datetime)[0] : '',
            'supply'  => $i->supply_address,
            'part_no' => $i->part_no,
        ])->values()->toArray();

        return view('sliphpms.index', compact('sliphpms', 'latestUploadInfo', 'slipPrintData'));
    }

    // ───────────────────────────────────────────
    //  IMPORT TXT
    // ───────────────────────────────────────────
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:txt|max:10240',
        ]);

        $lines = file($request->file('file')->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $records    = [];
        $now        = now();
        $expiresAt  = $now->copy()->addWeek();
        $uploadedBy = Auth::user()->name ?? 'system';

        foreach ($lines as $line) {
            $parsed = $this->parseLine($line);
            if (!$parsed) continue;

            $parsed['uploaded_by']    = $uploadedBy;
            $parsed['last_upload_at'] = $now;
            $parsed['expires_at']     = $expiresAt;
            $parsed['created_at']     = $now;
            $parsed['updated_at']     = $now;
            $records[] = $parsed;
        }

        if (empty($records)) {
            return response()->json(['message' => 'Tidak ada data valid di file TXT.'], 422);
        }

        DB::transaction(function () use ($records) {
            DB::table('sliphpms')->delete();
            foreach (array_chunk($records, 200) as $chunk) {
                SlipHpm::insert($chunk);
            }
        });

        return response()->json([
            'message' => 'Import berhasil. ' . count($records) . ' data diimport.',
        ]);
    }

    // ───────────────────────────────────────────
    //  PRINT (GET – preview in iframe / tab baru)
    // ───────────────────────────────────────────
    public function printFiltered(Request $request)
    {
        $dates    = array_filter((array) $request->input('dates', []));
        $supplies = array_filter((array) $request->input('docks', []));

        if (empty($dates) && empty($supplies)) {
            return view('sliphpms.slip', ['sliphpms' => collect()]);
        }

        $query = SlipHpm::query();

        if (!empty($dates)) {
            $query->where(function ($q) use ($dates) {
                foreach ($dates as $date) {
                    $q->orWhere('datetime', 'like', $date . '%');
                }
            });
        }

        if (!empty($supplies)) {
            $query->whereIn('supply_address', $supplies);
        }

        $sliphpms = $query->orderBy('datetime')->orderBy('ship')->get();

        return view('sliphpms.slip', compact('sliphpms'));
    }

    // ───────────────────────────────────────────
    //  DESTROY
    // ───────────────────────────────────────────
    public function destroy(SlipHpm $sliphpm)
    {
        $sliphpm->delete();
        return response()->json(['message' => 'Data berhasil dihapus.']);
    }

    // ───────────────────────────────────────────
    //  PARSER
    // ───────────────────────────────────────────
    private function parseLine(string $line): ?array
    {
        if (strlen($line) < 300) return null;

        // ── DI NO ────────────────────────────────────────────────────
        $di_no = trim(substr($line, 0, 16));
        if (!str_starts_with($di_no, 'DI')) return null;

        // ── LOC / MS_ID / HNS — dari pola "1M *" posisi fixed ───────
        // offset 25 = loc (1 char), 26 = ms_id (1 char), 28 = hns (1 char)
        $loc   = substr($line, 25, 1);   // '1'
        $ms_id = substr($line, 26, 1);   // 'M'
        $hns   = substr($line, 28, 1);   // '*'

        // ── FROM ─────────────────────────────────────────────────────
        // Capture kode (mis. "1S017") + optional suffix 2 digit (mis. "00")
        preg_match('/(\dS\w{3,4})\s*(\w{2})?/', substr($line, 38, 28), $fromMatch);
        $from_code   = $fromMatch[1] ?? trim(substr($line, 48, 10));
        $from_suffix = isset($fromMatch[2]) ? $fromMatch[2] : '00';
        $from        = $from_code . ' ' . $from_suffix;   // "1S017 00"

        // ── TO ───────────────────────────────────────────────────────
        preg_match('/(\dS\w{4,5})/', substr($line, 66, 34), $toMatch);
        $to = $toMatch[1] ?? trim(substr($line, 76, 10));

        // ── PS CODE — token pertama setelah "HPM " ───────────────────
        $ps_code = '';
        if (preg_match('/HPM\s+(\S+)/', $line, $planMatch)) {
            $ps_code = $planMatch[1];
        }

        // ── SUPPLY ADDRESS — token kedua setelah "HPM " ──────────────
        $supply_address = '';
        if (preg_match('/HPM\s+\S+\s+(\S+)/', $line, $supplyMatch)) {
            $supply_address = $supplyMatch[1];
        }

        // ── PART NO ──────────────────────────────────────────────────
        preg_match('/(\d{5}-\w{3,4}\s*-\w{4,6})/', substr($line, 118, 40), $partNoMatch);
        if (!$partNoMatch) {
            preg_match('/(\w{5}-\w{3,4}\s*-\w{4,6})/', substr($line, 118, 40), $partNoMatch);
        }
        $part_no = $partNoMatch[1] ?? trim(substr($line, 125, 23));

        // ── PART NAME + COLOR ────────────────────────────────────────
        $nameArea = substr($line, 175, 40);
        preg_match('/([A-Z]\d{7})/', $nameArea, $colorMatch);
        if ($colorMatch) {
            $part_color = $colorMatch[1];
            $part_name  = trim(substr($nameArea, 0, strpos($nameArea, $colorMatch[0])));
        } else {
            $part_name  = trim(substr($nameArea, 0, 30));
            $part_color = '';
        }

        // ── HPM SEGMENT (KHPM / DHPM) ────────────────────────────────
        //
        //  Contoh token setelah prefix+spasi:
        //    KHPM → "00202603005301202604130020MEAE02"  (len 32)
        //    DHPM → "06202606000301202604130010MPKD71"  (len 32)
        //
        //  Breakdown token per index:
        //    [0..1]   = 2 char  (mis. "00" / "06")
        //    [2..13]  = 12 char
        //    [14..25] = 12 char → prod_seq  (mis. "202604130010")
        //    [26..31] = 6 char  → inv_cat   (mis. "MPKD71" / "MEAE02")
        //
        //  rcv_type  = prefix[0]          → 'K' atau 'D'
        //  kd_lot_no = substr($seg,5,10) . substr($seg,15,2)
        //  ship      = substr($seg,27,4)  (numerik → cast int)
        //  NOTE: loc / ms_id / hns diambil dari offset fixed (25,26,28)
        // ─────────────────────────────────────────────────────────────
        $rcv_type  = '';
        $inv_cat   = '';
        $kd_lot_no = '';
        $ship      = '';
        $prod_seq  = '';

        // Pola utama: KHPM / DHPM diikuti spasi lalu data rapat
        foreach (['KHPM', 'DHPM'] as $prefix) {
            $pos = strpos($line, $prefix);
            if ($pos !== false) {
                $rcv_type = $prefix[0];               // 'K' atau 'D'
                $seg      = substr($line, $pos + 5);  // skip "KHPM " / "DHPM " (5 char)

                $token    = strtok($seg, " \t");      // token rapat, mis: "06202606000301202604130010MPKD71"
                $inv_cat  = substr($token, -6);       // 6 char terakhir      → "MPKD71"
                $prod_seq = substr($token, 14, 12);   // index 14 panjang 12  → "202604130010"

                $kd_lot_no = substr($seg, 5, 10) . substr($seg, 15, 2);
                $ship_raw  = trim(substr($seg, 27, 4));
                $ship      = is_numeric($ship_raw) ? (string)(int)$ship_raw : $ship_raw;
                break;
            }
        }

        // Fallback: pola "D                  202604150026MPKD62"
        // (1 huruf K/D, lalu >=10 spasi, lalu token data)
        if ($rcv_type === '') {
            if (preg_match('/\b([KD])\s{10,}(\S+)/', $line, $m)) {
                $rcv_type = $m[1];
                $token    = $m[2];
                $inv_cat  = substr($token, -6);
                $prod_seq = substr($token, 14, 12);
            }
        }

        // ── Validasi prod_seq: harus tepat 12 digit numerik ──────────
        if (!ctype_digit($prod_seq) || strlen($prod_seq) !== 12) {
            $prod_seq = '';
        }

        // ── SEQ NO ───────────────────────────────────────────────────
        $seq_no = trim(substr($line, 265, 20));

        // ── DATETIME → YYYY-MM-DD HH:MM ──────────────────────────────
        $dt_raw   = trim(substr($line, 295, 14));
        $datetime = '';
        if (preg_match('/^\d{12}/', $dt_raw)) {
            $datetime = substr($dt_raw, 0, 4) . '-'
                      . substr($dt_raw, 4, 2) . '-'
                      . substr($dt_raw, 6, 2) . ' '
                      . substr($dt_raw, 8, 2) . ':'
                      . substr($dt_raw, 10, 2);
        }

        // ── QTY ──────────────────────────────────────────────────────
        $qty_raw = trim(substr($line, 309, 9));
        $qty     = is_numeric($qty_raw) ? (string)(int)$qty_raw : $qty_raw;

        return [
            'di_no'          => $di_no,
            'part_no'        => trim($part_no),
            'part_name'      => $part_name,
            'part_color'     => $part_color,
            'from'           => $from,
            'to'             => $to,
            'ps_code'        => $ps_code,
            'supply_address' => $supply_address,
            'inv_cat'        => $inv_cat,
            'rcv_type'       => $rcv_type,
            'kd_lot_no'      => $kd_lot_no,
            'loc'            => $loc,
            'ms_id'          => $ms_id,
            'hns'            => $hns,
            'ship'           => $ship,
            'seq_no'         => $seq_no,
            'prod_seq'       => $prod_seq,
            'datetime'       => $datetime,
            'qty'            => $qty,
        ];
    }
}
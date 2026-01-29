<?php

namespace App\Http\Controllers;

use App\Models\Preparation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

class ImportMigrasiController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:20480'
        ]);

        try {
            $file = $request->file('excel_file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Skip header row
            $header = array_shift($rows);
            
            // Mapping header ke index
            $headerMap = array_flip(array_map('strtolower', array_map('trim', $header)));

            $dataToInsert = [];
            $duplicates = [];
            $forceImport = $request->has('force_import');

            // Get existing no_dn untuk cek duplikat
            $existingDns = Preparation::pluck('no_dn')->toArray();

            foreach ($rows as $index => $row) {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Mapping data dari Excel ke field database
                $orderNo = $this->getValue($row, $headerMap, 'order_no');
                
                // Skip jika order_no kosong
                if (empty($orderNo)) {
                    continue;
                }

                // Cek duplikat
                if (in_array($orderNo, $existingDns)) {
                    $duplicates[] = $orderNo;
                    if (!$forceImport) {
                        continue;
                    }
                    // Skip duplikat meskipun force import
                    continue;
                }

                // Parse dates
                $deliveryDate = $this->parseDate($this->getValue($row, $headerMap, 'delivery_date'));
                $pullingDate = $this->parseDate($this->getValue($row, $headerMap, 'pulling_date'));
                
                // Parse times
                $deliveryTime = $this->parseTime($this->getValue($row, $headerMap, 'delivery_time'));
                $pullingTime = $this->parseTime($this->getValue($row, $headerMap, 'pulling_time'));

                // Get logistic partner, handle "-" as empty
                $logisticPartner = $this->getValue($row, $headerMap, 'logistic_partner');
                if ($logisticPartner === '-') {
                    $logisticPartner = '';
                }

                $dataToInsert[] = [
                    'route' => $this->getValue($row, $headerMap, 'route') ?? '',
                    'logistic_partners' => $logisticPartner ?? '',
                    'no_dn' => $orderNo,
                    'customers' => $this->getValue($row, $headerMap, 'customer') ?? '',
                    'dock' => $this->getValue($row, $headerMap, 'dock') ?? '',
                    'delivery_date' => $deliveryDate,
                    'delivery_time' => $deliveryTime,
                    'cycle' => (int) ($this->getValue($row, $headerMap, 'cycle') ?? 1),
                    'pulling_date' => $pullingDate,
                    'pulling_time' => $pullingTime,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Tambahkan ke existing untuk cek duplikat berikutnya
                $existingDns[] = $orderNo;
            }

            // Jika ada duplikat dan bukan force import, return warning
            if (!empty($duplicates) && !$forceImport) {
                return response()->json([
                    'status' => 'duplicates_found',
                    'message' => 'Ditemukan ' . count($duplicates) . ' data dengan No DN yang sudah ada di database.',
                    'duplicates' => [
                        ['table' => 'Preparations', 'count' => count($duplicates)]
                    ],
                    'duplicate_dns' => array_slice($duplicates, 0, 10) // Show first 10
                ]);
            }

            // Insert data
            if (!empty($dataToInsert)) {
                DB::beginTransaction();
                try {
                    // Insert in chunks untuk performance
                    foreach (array_chunk($dataToInsert, 100) as $chunk) {
                        Preparation::insert($chunk);
                    }
                    DB::commit();

                    $skippedCount = count($duplicates);
                    $insertedCount = count($dataToInsert);
                    
                    $message = "Berhasil mengimpor {$insertedCount} data preparation.";
                    if ($skippedCount > 0) {
                        $message .= " ({$skippedCount} data duplikat dilewati)";
                    }

                    return response()->json([
                        'status' => 'success',
                        'message' => $message,
                        'inserted' => $insertedCount,
                        'skipped' => $skippedCount
                    ]);

                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Tidak ada data baru untuk diimpor (semua data sudah ada atau file kosong)',
                'inserted' => 0,
                'skipped' => count($duplicates)
            ]);

        } catch (\Exception $e) {
            Log::error('Import Migrasi Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengimpor data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get value from row by header name
     */
    private function getValue($row, $headerMap, $key)
    {
        $index = $headerMap[$key] ?? null;
        if ($index === null) {
            return null;
        }
        return isset($row[$index]) ? trim($row[$index]) : null;
    }

    /**
     * Parse date from various formats
     */
    private function parseDate($value)
    {
        if (empty($value)) {
            return now()->format('Y-m-d');
        }

        try {
            // Jika sudah format Y-m-d
            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
                return Carbon::parse($value)->format('Y-m-d');
            }
            
            // Jika format Excel serial number
            if (is_numeric($value)) {
                return Carbon::createFromFormat('Y-m-d', gmdate('Y-m-d', ($value - 25569) * 86400))->format('Y-m-d');
            }

            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return now()->format('Y-m-d');
        }
    }

    /**
     * Parse time from various formats
     */
    private function parseTime($value)
    {
        if (empty($value)) {
            return '00:00:00';
        }

        try {
            // Jika sudah format H:i:s
            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $value)) {
                return $value;
            }

            // Jika format H:i
            if (preg_match('/^\d{2}:\d{2}$/', $value)) {
                return $value . ':00';
            }

            // Jika format Excel decimal (fraction of day)
            if (is_numeric($value) && $value < 1) {
                $seconds = round($value * 86400);
                $hours = floor($seconds / 3600);
                $minutes = floor(($seconds % 3600) / 60);
                $secs = $seconds % 60;
                return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
            }

            return Carbon::parse($value)->format('H:i:s');
        } catch (\Exception $e) {
            return '00:00:00';
        }
    }
}
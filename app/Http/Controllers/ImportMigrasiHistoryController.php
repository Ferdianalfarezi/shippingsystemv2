<?php

namespace App\Http\Controllers;

use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

class ImportMigrasiHistoryController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:51200' // Max 50MB for large history files
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
            $existingDns = History::pluck('no_dn')->toArray();

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

                // Parse dates & times
                $deliveryDate = $this->parseDate($this->getValue($row, $headerMap, 'delivery_date'));
                $deliveryTime = $this->parseTime($this->getValue($row, $headerMap, 'delivery_time'));
                $scanToShipping = $this->parseDateTime($this->getValue($row, $headerMap, 'scan_to_shipping'));
                $scanToDelivery = $this->parseDateTime($this->getValue($row, $headerMap, 'scan_to_delivery'));
                $scanToHistory = $this->parseDateTime($this->getValue($row, $headerMap, 'scan_to_history'));
                $arrival = $this->parseDateTime($this->getValue($row, $headerMap, 'arrival'));

                // Get logistic partner, handle "-" or null as empty
                $logisticPartner = $this->getValue($row, $headerMap, 'logistic_partner');
                if ($logisticPartner === '-' || $logisticPartner === null) {
                    $logisticPartner = '';
                }

                // Get cycle, default to 1
                $cycle = $this->getValue($row, $headerMap, 'cycle');
                $cycle = is_numeric($cycle) ? (int) $cycle : 1;

                // Calculate total business hours if scan_to_delivery exists
                $totalBusinessHours = 0;
                if ($scanToDelivery && $scanToHistory) {
                    try {
                        $start = Carbon::parse($scanToDelivery);
                        $end = Carbon::parse($scanToHistory);
                        $totalBusinessHours = $this->calculateBusinessHours($start, $end);
                    } catch (\Exception $e) {
                        $totalBusinessHours = 0;
                    }
                }

                $dataToInsert[] = [
                    'route' => $this->getValue($row, $headerMap, 'route') ?? '',
                    'logistic_partners' => $logisticPartner,
                    'no_dn' => $orderNo,
                    'customers' => $this->getValue($row, $headerMap, 'customer') ?? '',
                    'dock' => $this->getValue($row, $headerMap, 'dock') ?? '',
                    'delivery_date' => $deliveryDate,
                    'delivery_time' => $deliveryTime,
                    'cycle' => $cycle,
                    'address' => $this->getValue($row, $headerMap, 'address') ?? '',
                    'arrival' => $arrival,
                    'scan_to_shipping' => $scanToShipping,
                    'scan_to_delivery' => $scanToDelivery,
                    'completed_at' => $scanToHistory, // scan_to_history = completed_at
                    'final_status' => 'completed',
                    'total_business_hours' => round($totalBusinessHours, 2),
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
                        ['table' => 'Histories', 'count' => count($duplicates)]
                    ],
                    'duplicate_dns' => array_slice($duplicates, 0, 10)
                ]);
            }

            // Insert data
            if (!empty($dataToInsert)) {
                DB::beginTransaction();
                try {
                    // Insert in chunks untuk performance (larger chunks for history)
                    foreach (array_chunk($dataToInsert, 500) as $chunk) {
                        History::insert($chunk);
                    }
                    DB::commit();

                    $skippedCount = count($duplicates);
                    $insertedCount = count($dataToInsert);
                    
                    $message = "Berhasil mengimpor {$insertedCount} data history.";
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
            Log::error('Import Migrasi History Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengimpor data: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getValue($row, $headerMap, $key)
    {
        $index = $headerMap[$key] ?? null;
        if ($index === null) {
            return null;
        }
        $value = $row[$index] ?? null;
        if ($value === null || $value === '' || $value === 'NaN' || $value === 'nan') {
            return null;
        }
        return is_string($value) ? trim($value) : $value;
    }

    private function parseDate($value)
    {
        if (empty($value) || $value === 'NaT') {
            return null;
        }

        try {
            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
                return Carbon::parse($value)->format('Y-m-d');
            }
            
            if (is_numeric($value)) {
                return Carbon::createFromFormat('Y-m-d', gmdate('Y-m-d', ($value - 25569) * 86400))->format('Y-m-d');
            }

            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseDateTime($value)
    {
        if (empty($value) || $value === 'NaN' || $value === 'nan' || $value === 'NaT') {
            return null;
        }

        try {
            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
                return Carbon::parse($value)->format('Y-m-d H:i:s');
            }
            
            if (is_numeric($value)) {
                $days = floor($value);
                $fraction = $value - $days;
                $date = Carbon::createFromFormat('Y-m-d', gmdate('Y-m-d', ($days - 25569) * 86400));
                $seconds = round($fraction * 86400);
                $date->addSeconds($seconds);
                return $date->format('Y-m-d H:i:s');
            }

            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseTime($value)
    {
        if (empty($value) || $value === 'NaT') {
            return null;
        }

        try {
            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $value)) {
                return $value;
            }

            if (preg_match('/^\d{2}:\d{2}$/', $value)) {
                return $value . ':00';
            }

            if (is_numeric($value) && $value < 1) {
                $seconds = round($value * 86400);
                $hours = floor($seconds / 3600);
                $minutes = floor(($seconds % 3600) / 60);
                $secs = $seconds % 60;
                return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
            }

            return Carbon::parse($value)->format('H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Calculate business hours between two dates (exclude weekends)
     */
    private function calculateBusinessHours(Carbon $start, Carbon $end): float
    {
        $hours = 0;
        $current = $start->copy();
        
        while ($current < $end) {
            if (!$current->isWeekend()) {
                $endOfDay = $current->copy()->endOfDay();
                $stopTime = $end < $endOfDay ? $end : $endOfDay;
                $hours += $current->floatDiffInHours($stopTime);
            }
            $current = $current->copy()->addDay()->startOfDay();
        }
        
        return $hours;
    }
}
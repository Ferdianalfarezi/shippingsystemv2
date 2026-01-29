<?php

namespace App\Http\Controllers;

use App\Models\Milkrun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportMigrasiMilkrunController extends Controller
{
    /**
     * Import data migrasi milkrun dari Excel
     * 
     * Format kolom Excel:
     * - order_no (akan digroup per route+cycle+delivery_date+delivery_time)
     * - customer
     * - dock
     * - delivery_date
     * - delivery_time
     * - arrival
     * - cycle
     * - route
     * - logistic_partner
     * - status
     * - address
     */
    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:51200', // Max 50MB
        ]);

        ini_set('max_execution_time', 600); // 10 menit
        ini_set('memory_limit', '512M');

        try {
            $file = $request->file('excel_file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Get header row
            $headers = array_map('strtolower', array_map('trim', $rows[0]));
            
            // Map column indexes
            $columnMap = $this->mapColumns($headers);
            
            // Validate required columns
            $requiredColumns = ['route', 'cycle', 'delivery_date', 'delivery_time'];
            foreach ($requiredColumns as $col) {
                if (!isset($columnMap[$col])) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Kolom '{$col}' tidak ditemukan dalam file Excel!"
                    ], 422);
                }
            }
            
            // Remove header row
            array_shift($rows);
            
            // Filter empty rows
            $rows = array_filter($rows, function($row) {
                return !empty(array_filter($row, fn($cell) => $cell !== null && $cell !== ''));
            });
            
            if (empty($rows)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'File Excel kosong atau tidak memiliki data valid!'
                ], 422);
            }
            
            // Group data by route + cycle + delivery_date + delivery_time
            $groupedData = $this->groupData($rows, $columnMap);
            
            // Check for duplicates if not force import
            $forceImport = $request->input('force_import', false);
            
            if (!$forceImport) {
                $duplicates = $this->checkDuplicates($groupedData);
                
                if (!empty($duplicates)) {
                    return response()->json([
                        'status' => 'duplicates_found',
                        'message' => 'Ditemukan ' . count($duplicates) . ' data duplikat (Route+Cycle+Delivery sudah ada).',
                        'duplicates' => array_slice($duplicates, 0, 10), // Show first 10
                        'total_duplicates' => count($duplicates),
                    ]);
                }
            }
            
            // Import data
            $result = $this->importData($groupedData, $forceImport);
            
            return response()->json([
                'status' => 'success',
                'message' => "Berhasil mengimpor {$result['imported']} data milkrun" . 
                            ($result['skipped'] > 0 ? " ({$result['skipped']} data duplikat dilewati)" : ""),
                'imported' => $result['imported'],
                'skipped' => $result['skipped'],
            ]);
            
        } catch (\Exception $e) {
            Log::error('Import Migrasi Milkrun Error: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengimpor data: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Map column names to indexes
     */
    private function mapColumns(array $headers): array
    {
        $map = [];
        $columnAliases = [
            'order_no' => ['order_no', 'orderno', 'no_dn', 'dn', 'dn_no'],
            'customer' => ['customer', 'customers', 'cust'],
            'dock' => ['dock'],
            'delivery_date' => ['delivery_date', 'deliverydate', 'del_date'],
            'delivery_time' => ['delivery_time', 'deliverytime', 'del_time'],
            'arrival' => ['arrival', 'arrival_time'],
            'departure' => ['departure', 'scan_to_delivery', 'scantodelivery'],
            'cycle' => ['cycle', 'cyc'],
            'route' => ['route'],
            'logistic_partner' => ['logistic_partner', 'logistic_partners', 'lp'],
            'status' => ['status'],
            'address' => ['address', 'addr'],
        ];
        
        foreach ($columnAliases as $standard => $aliases) {
            foreach ($aliases as $alias) {
                $index = array_search($alias, $headers);
                if ($index !== false) {
                    $map[$standard] = $index;
                    break;
                }
            }
        }
        
        return $map;
    }
    
    /**
     * Group data by route + cycle + delivery_date + delivery_time
     * Each group becomes one milkrun record
     */
    private function groupData(array $rows, array $columnMap): array
    {
        $grouped = [];
        
        foreach ($rows as $row) {
            $route = $this->getValue($row, $columnMap, 'route');
            $cycle = $this->getValue($row, $columnMap, 'cycle');
            $deliveryDate = $this->parseDate($this->getValue($row, $columnMap, 'delivery_date'));
            $deliveryTime = $this->parseTime($this->getValue($row, $columnMap, 'delivery_time'));
            
            if (empty($route) || empty($cycle) || empty($deliveryDate)) {
                continue; // Skip invalid rows
            }
            
            // Create unique key for grouping
            $key = "{$route}|{$cycle}|{$deliveryDate}|{$deliveryTime}";
            
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'route' => $route,
                    'cycle' => (int) $cycle,
                    'delivery_date' => $deliveryDate,
                    'delivery_time' => $deliveryTime,
                    'customers' => $this->getValue($row, $columnMap, 'customer'),
                    'dock' => $this->getValue($row, $columnMap, 'dock'),
                    'logistic_partners' => $this->cleanNull($this->getValue($row, $columnMap, 'logistic_partner')),
                    'address' => $this->getValue($row, $columnMap, 'address'),
                    'arrival' => $this->parseDateTime($this->getValue($row, $columnMap, 'arrival')),
                    'departure' => $this->parseDateTime($this->getValue($row, $columnMap, 'departure')),
                    'no_dns' => [],
                ];
            }
            
            // Add order_no to no_dns array
            $orderNo = $this->getValue($row, $columnMap, 'order_no');
            if (!empty($orderNo) && !in_array($orderNo, $grouped[$key]['no_dns'])) {
                $grouped[$key]['no_dns'][] = (string) $orderNo;
            }
        }
        
        return $grouped;
    }
    
    /**
     * Check for duplicate milkruns
     */
    private function checkDuplicates(array $groupedData): array
    {
        $duplicates = [];
        
        foreach ($groupedData as $data) {
            $exists = Milkrun::where('route', $data['route'])
                ->where('cycle', $data['cycle'])
                ->whereDate('delivery_date', $data['delivery_date'])
                ->where('delivery_time', $data['delivery_time'])
                ->exists();
            
            if ($exists) {
                $duplicates[] = [
                    'route' => $data['route'],
                    'cycle' => $data['cycle'],
                    'delivery_date' => $data['delivery_date'],
                    'delivery_time' => $data['delivery_time'],
                ];
            }
        }
        
        return $duplicates;
    }
    
    /**
     * Import data to database
     */
    private function importData(array $groupedData, bool $forceImport): array
    {
        $imported = 0;
        $skipped = 0;
        
        DB::beginTransaction();
        
        try {
            foreach ($groupedData as $data) {
                // Check duplicate
                $exists = Milkrun::where('route', $data['route'])
                    ->where('cycle', $data['cycle'])
                    ->whereDate('delivery_date', $data['delivery_date'])
                    ->where('delivery_time', $data['delivery_time'])
                    ->exists();
                
                if ($exists) {
                    $skipped++;
                    continue;
                }
                
                // Calculate status based on arrival
                $status = 'pending';
                if (!empty($data['arrival'])) {
                    $status = $this->calculateStatus(
                        $data['delivery_date'],
                        $data['delivery_time'],
                        $data['arrival']
                    );
                }
                
                // Create milkrun record
                Milkrun::create([
                    'route' => $data['route'],
                    'cycle' => $data['cycle'],
                    'delivery_date' => $data['delivery_date'],
                    'delivery_time' => $data['delivery_time'],
                    'customers' => $data['customers'],
                    'dock' => $data['dock'],
                    'logistic_partners' => $data['logistic_partners'],
                    'address' => $data['address'],
                    'arrival' => $data['arrival'],
                    'departure' => $data['departure'],
                    'status' => $status,
                    'dn_count' => count($data['no_dns']),
                    'no_dns' => $data['no_dns'],
                    'moved_by' => auth()->user()->name ?? 'Import Migrasi',
                ]);
                
                $imported++;
            }
            
            DB::commit();
            
            return [
                'imported' => $imported,
                'skipped' => $skipped,
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Calculate status based on arrival time
     */
    private function calculateStatus(string $deliveryDate, string $deliveryTime, string $arrival): string
    {
        $targetDateTime = Carbon::parse("{$deliveryDate} {$deliveryTime}");
        $arrivalDateTime = Carbon::parse($arrival);
        
        $diffInMinutes = $arrivalDateTime->diffInMinutes($targetDateTime, false) * -1;
        
        if ($diffInMinutes < -15) {
            return 'advance';
        } elseif ($diffInMinutes > 30) {
            return 'delay';
        } else {
            return 'on_time';
        }
    }
    
    /**
     * Get value from row by column name
     */
    private function getValue(array $row, array $columnMap, string $column)
    {
        if (!isset($columnMap[$column])) {
            return null;
        }
        
        $value = $row[$columnMap[$column]] ?? null;
        
        if ($value === 'NULL' || $value === 'null') {
            return null;
        }
        
        return $value;
    }
    
    /**
     * Clean NULL string values
     */
    private function cleanNull($value)
    {
        if ($value === 'NULL' || $value === 'null' || $value === '') {
            return null;
        }
        return $value;
    }
    
    /**
     * Parse date value (could be Excel serial or string)
     */
    private function parseDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        
        try {
            // Check if Excel serial date (numeric)
            if (is_numeric($value) && $value > 25569) {
                $date = ExcelDate::excelToDateTimeObject($value);
                return $date->format('Y-m-d');
            }
            
            // Try parsing as string
            $date = Carbon::parse($value);
            return $date->format('Y-m-d');
            
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Parse time value
     */
    private function parseTime($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        
        try {
            // Check if Excel serial time (decimal < 1)
            if (is_numeric($value) && $value < 1) {
                $totalSeconds = round($value * 86400);
                $hours = floor($totalSeconds / 3600);
                $minutes = floor(($totalSeconds % 3600) / 60);
                $seconds = $totalSeconds % 60;
                return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
            }
            
            // Check if already time format
            if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $value)) {
                return strlen($value) === 5 ? $value . ':00' : $value;
            }
            
            // Try parsing as datetime and extract time
            $time = Carbon::parse($value);
            return $time->format('H:i:s');
            
        } catch (\Exception $e) {
            return '00:00:00';
        }
    }
    
    /**
     * Parse datetime value
     */
    private function parseDateTime($value): ?string
    {
        if (empty($value) || $value === 'NULL' || $value === 'null') {
            return null;
        }
        
        try {
            // Check if Excel serial datetime
            if (is_numeric($value)) {
                $datetime = ExcelDate::excelToDateTimeObject($value);
                return $datetime->format('Y-m-d H:i:s');
            }
            
            $datetime = Carbon::parse($value);
            return $datetime->format('Y-m-d H:i:s');
            
        } catch (\Exception $e) {
            return null;
        }
    }
}
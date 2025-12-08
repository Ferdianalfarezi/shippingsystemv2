<?php

namespace App\Http\Controllers;

use App\Models\Preparation;
use App\Models\LpConfig;
use App\Models\AdmLeadTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ImportAdmController extends Controller
{
    public function import(Request $request)
    {
        try {
            // Validasi file
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls|max:2048',
            ], [
                'file.required' => 'File harus diunggah.',
                'file.file' => 'Input harus berupa file.',
                'file.mimes' => 'File harus berupa Excel dengan ekstensi .xlsx atau .xls.',
                'file.max' => 'Ukuran file tidak boleh lebih dari 2 MB.',
            ]);

            // Baca isi file Excel
            $collection = Excel::toCollection(null, $request->file('file'))->first();

            // Lewati 3 baris pertama → baris ke-4 header
            $collection = $collection->slice(3)->values();

            if ($collection->isEmpty()) {
                Log::warning('File ADM kosong atau tidak terbaca');
                return response()->json([
                    'status' => 'error',
                    'message' => 'File kosong atau format tidak sesuai',
                ], 400);
            }

            $preparationsData = [];
            $noDnList = [];

            // Ambil header baris keempat
            $headerRow = $collection->first()->toArray();
            Log::info('ADM Header Row:', $headerRow);

            $headerMap = [
                'order_no'      => null,
                'route'         => null,
                'dock'          => null,
                'delivery_date' => null,
                'delivery_time' => null,
                'cycle'         => null,
            ];

            // Pemetaan header berdasarkan file Excel
            foreach ($headerRow as $index => $header) {
                $header = strtolower(preg_replace('/\s+/', ' ', trim((string)$header)));

                if (str_contains($header, 'order no')) {
                    $headerMap['order_no'] = $index;
                } elseif (str_contains($header, 'route')) {
                    $headerMap['route'] = $index;
                } elseif (str_contains($header, 'shop code') || str_contains($header, 'dock')) {
                    $headerMap['dock'] = $index;
                } elseif (str_contains($header, 'del. date') || str_contains($header, 'del date')) {
                    $headerMap['delivery_date'] = $index;
                } elseif (str_contains($header, 'del. time') || str_contains($header, 'del time')) {
                    $headerMap['delivery_time'] = $index;
                } elseif (str_contains($header, 'del. cycle') || str_contains($header, 'cycle')) {
                    $headerMap['cycle'] = $index;
                }
            }

            Log::info('ADM Header mapping:', $headerMap);

            // Validasi header wajib
            $missingHeaders = array_keys(array_filter($headerMap, fn($value) => is_null($value)));
            if (!empty($missingHeaders)) {
                Log::error('Missing required headers:', $missingHeaders);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Header tidak ditemukan: ' . implode(', ', $missingHeaders),
                ], 400);
            }

            // Ambil LP Config untuk auto-fill logistic partner
            $lpConfigs = LpConfig::pluck('logistic_partner', 'route')->toArray();

            // ========================================
            // AMBIL SEMUA ADM LEAD TIME CONFIG
            // ========================================
            $admLeadTimes = AdmLeadTime::all()->keyBy(function ($item) {
                return $item->route . '|' . $item->dock . '|' . $item->cycle;
            });

            // Mulai proses data dari baris ke-5
            foreach ($collection->skip(1) as $row) {
                if (!isset($row[$headerMap['order_no']]) || empty($row[$headerMap['order_no']])) {
                    continue;
                }

                $orderNo = trim((string) $row[$headerMap['order_no']]);
                
                // Skip jika order_no sudah ada di preparationsData (duplikat dalam file)
                if (isset($preparationsData[$orderNo])) {
                    Log::info('Skipping duplicate in file: ' . $orderNo);
                    continue;
                }

                $noDnList[] = $orderNo;

                // Nilai original dari Excel
                $originalDeliveryDate = $row[$headerMap['delivery_date']];
                $originalDeliveryTime = $row[$headerMap['delivery_time']];
                $cycle = $row[$headerMap['cycle']] ?? 1;
                $route = trim((string) ($row[$headerMap['route']] ?? ''));
                $dock = trim((string) ($row[$headerMap['dock']] ?? ''));

                // Parsing tanggal
                if (is_numeric($originalDeliveryDate)) {
                    $deliveryDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($originalDeliveryDate)->format('Y-m-d');
                } else {
                    try {
                        $deliveryDate = Carbon::parse($originalDeliveryDate)->format('Y-m-d');
                    } catch (\Exception $e) {
                        Log::warning("Invalid delivery date for order {$orderNo}: {$originalDeliveryDate}");
                        continue;
                    }
                }

                // Parsing jam
                if (is_numeric($originalDeliveryTime)) {
                    $deliveryTime = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($originalDeliveryTime)->format('H:i:s');
                } else {
                    try {
                        $deliveryTime = Carbon::parse($originalDeliveryTime)->format('H:i:s');
                    } catch (\Exception $e) {
                        Log::warning("Invalid delivery time for order {$orderNo}: {$originalDeliveryTime}");
                        continue;
                    }
                }

                // Gabung ke datetime
                try {
                    $deliveryDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $deliveryDate . ' ' . $deliveryTime);
                } catch (\Exception $e) {
                    Log::warning("Failed to parse delivery datetime for order {$orderNo}: " . $e->getMessage());
                    continue;
                }

                // ========================================
                // HITUNG PULLING TIME BERDASARKAN LEAD TIME
                // ========================================
                $cycleFormatted = sprintf('%02d', $cycle); // Format cycle jadi 01, 02, dll
                $cycleRaw = (string) $cycle; // Cycle tanpa format
                
                // Cari lead time di config (coba formatted dan raw)
                $leadTimeKey1 = $route . '|' . $dock . '|' . $cycleFormatted;
                $leadTimeKey2 = $route . '|' . $dock . '|' . $cycleRaw;
                
                $leadTimeConfig = $admLeadTimes->get($leadTimeKey1) ?? $admLeadTimes->get($leadTimeKey2);
                
                if ($leadTimeConfig) {
                    // Gunakan lead time dari config
                    $leadTimeParts = explode(':', $leadTimeConfig->lead_time);
                    $leadTimeHours = (int) ($leadTimeParts[0] ?? 0);
                    $leadTimeMinutes = (int) ($leadTimeParts[1] ?? 0);
                    
                    $pullingDateTime = $deliveryDateTime->copy()
                        ->subHours($leadTimeHours)
                        ->subMinutes($leadTimeMinutes);
                    
                    Log::info("Order {$orderNo}: Using config lead time {$leadTimeConfig->lead_time} for {$route}|{$dock}|{$cycle}");
                } else {
                    // Default: mundur 3 jam
                    $pullingDateTime = $deliveryDateTime->copy()->subHours(3);
                    Log::info("Order {$orderNo}: Using default lead time 3 hours for {$route}|{$dock}|{$cycle}");
                }

                // Auto-fill logistic partner dari config
                $logisticPartner = $lpConfigs[$route] ?? '';

                // Gunakan order_no sebagai key untuk hindari duplikat dalam file
                $preparationsData[$orderNo] = [
                    'no_dn'             => $orderNo,
                    'route'             => $route,
                    'logistic_partners' => $logisticPartner,
                    'customers'         => 'ADM',
                    'dock'              => $dock,
                    'delivery_date'     => $deliveryDateTime->format('Y-m-d'),
                    'delivery_time'     => $deliveryDateTime->format('H:i:s'),
                    'cycle'             => $cycleFormatted,
                    'pulling_date'      => $pullingDateTime->format('Y-m-d'),
                    'pulling_time'      => $pullingDateTime->format('H:i:s'),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];
            }

            // Convert back to indexed array dan unique noDnList
            $preparationsData = array_values($preparationsData);
            $noDnList = array_unique($noDnList);

            if (empty($preparationsData)) {
                Log::error('Tidak ada data ADM yang dikumpulkan untuk disimpan');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada data yang diproses dari file',
                ], 400);
            }

            Log::info('Total unique ADM preparations data: ' . count($preparationsData));

            // ========================================
            // CEK DUPLIKAT DI DATABASE
            // ========================================
            
            $existingPreparations = Preparation::whereIn('no_dn', $noDnList)->pluck('no_dn')->toArray();

            if (!empty($existingPreparations) && !$request->has('force_import')) {
                return response()->json([
                    'status' => 'duplicates_found',
                    'message' => 'Ditemukan ' . count($existingPreparations) . ' data duplikat di database.',
                    'duplicates' => [
                        [
                            'table' => 'preparations',
                            'count' => count($existingPreparations),
                            'data' => $existingPreparations
                        ]
                    ],
                ], 200);
            }

            // ========================================
            // SIMPAN DATA KE DATABASE
            // ========================================
            
            $savedCount = 0;
            $skippedCount = 0;

            foreach ($preparationsData as $data) {
                if (!in_array($data['no_dn'], $existingPreparations)) {
                    try {
                        Preparation::updateOrCreate(
                            ['no_dn' => $data['no_dn']],
                            $data
                        );
                        $savedCount++;
                    } catch (\Exception $e) {
                        Log::error('Failed to save preparation ' . $data['no_dn'] . ': ' . $e->getMessage());
                        $skippedCount++;
                    }
                } else {
                    $skippedCount++;
                }
            }

            // ========================================
            // SUCCESS MESSAGE - PAKAI SAVED COUNT AKTUAL
            // ========================================
            
            $successMessage = "ADM data berhasil diimpor. {$savedCount} data berhasil disimpan";
            if ($skippedCount > 0) {
                $successMessage .= ", {$skippedCount} data dilewati";
            }
            $successMessage .= ".";

            Log::info('Import ADM completed: ' . $successMessage);

            return response()->json([
                'status' => 'success',
                'message' => $successMessage,
                'saved' => $savedCount,
                'skipped' => $skippedCount,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error importing ADM Excel data: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengimpor data: ' . $e->getMessage(),
            ], 500);
        }
    }
}
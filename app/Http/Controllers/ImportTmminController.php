<?php

namespace App\Http\Controllers;

use App\Models\Preparation;
use App\Models\LpConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ImportTmminController extends Controller
{
    public function import(Request $request)
    {
        // Validasi file
        $request->validate([
            'txt_file' => 'required|file|mimes:txt|max:20480',
        ], [
            'txt_file.required' => 'File harus diunggah.',
            'txt_file.file' => 'Input harus berupa file.',
            'txt_file.mimes' => 'File harus berupa teks dengan ekstensi .txt.',
            'txt_file.max' => 'Ukuran file tidak boleh lebih dari 20 MB.',
        ]);

        try {
            // Ambil file
            $file = $request->file('txt_file');
            $content = file_get_contents($file->getPathname());
            
            if (empty($content)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'File kosong atau tidak dapat dibaca.',
                ], 400);
            }

            Log::info('Memulai proses parsing file TXT untuk Preparations...');
            
            $lines = explode("\n", $content);
            Log::info('Total lines in file: ' . count($lines));
            
            $preparationsData = [];
            $d1Data = [];
            $d2Data = [];
            $hasD2Below = [];
            $lastValidRoute = null;

            // ========================================
            // PARSING FILE TXT
            // ========================================
            
            // Parse file untuk mencari D1 dan D2 entries
            for ($i = 0; $i < count($lines); $i++) {
                $line = trim($lines[$i]);
                if (empty($line)) continue;

                $columns = explode("\t", $line);

                // ============ PROSES BARIS D1 ============
                if (isset($columns[0]) && str_starts_with($columns[0], 'D1')) {
                    Log::info('D1 line detected at index ' . $i . ': ' . substr($line, 0, 100));
                    
                    $orderNo = $columns[1] ?? null;
                    if (empty($orderNo)) {
                        Log::warning('D1 order_no empty at index ' . $i);
                        continue;
                    }

                    $cleanOrderNo = trim(preg_replace('/[^0-9]/', '', $orderNo));
                    if (empty($cleanOrderNo)) {
                        Log::warning('D1 cleanOrderNo empty at index ' . $i . ', original: ' . $orderNo);
                        continue;
                    }
                    
                    Log::info('D1 processing order_no: ' . $cleanOrderNo);

                    // Extract customer
                    $customer = 'UNKNOWN';
                    $dockCode = trim($columns[6] ?? 'UNKNOWN');
                    if (strlen($dockCode) == 1 || $dockCode === 'UNKNOWN') {
                        $fullLine = implode("\t", $columns);
                        if (preg_match('/\b([0-9][A-Z0-9])\b/', $fullLine, $matches)) {
                            $customer = $matches[1];
                        }
                    }

                    if ($customer === 'UNKNOWN') {
                        $customer = 'TMMIN';
                    }

                    // Extract route dan cycle
                    $route = 'UNKNOWN';
                    $cycleValue = null;
                    $routePattern = '/^[A-Z]{1,3}\d+$/i';

                    // Cek route di kolom 30
                    if (isset($columns[30]) && !empty(trim($columns[30]))) {
                        $routeCandidate = trim($columns[30]);
                        
                        if (preg_match($routePattern, $routeCandidate)) {
                            $route = $routeCandidate;
                            
                            if (isset($columns[31])) {
                                $cycleCandidate = trim($columns[31]);
                                if (preg_match('/(\d+)/', $cycleCandidate, $matches)) {
                                    $cycleValue = $matches[1];
                                } else {
                                    $cycleValue = $cycleCandidate;
                                }
                            }
                        }
                    }

                    // Cek route di kolom 27 jika belum ketemu
                    if ($route === 'UNKNOWN' && isset($columns[27]) && !empty(trim($columns[27]))) {
                        $routeCandidate = trim($columns[27]);
                        if (preg_match($routePattern, $routeCandidate)) {
                            $route = $routeCandidate;
                            if (isset($columns[28])) {
                                $cycleCandidate = trim($columns[28]);
                                if (preg_match('/(\d+)/', $cycleCandidate, $matches)) {
                                    $cycleValue = $matches[1];
                                } else {
                                    $cycleValue = $cycleCandidate;
                                }
                            }
                        }
                    }

                    // Gunakan last valid route jika tidak ketemu
                    if ($route === 'UNKNOWN' && isset($lastValidRoute)) {
                        $route = $lastValidRoute;
                    }

                    if ($route !== 'UNKNOWN') {
                        $lastValidRoute = $route;
                    }

                    if ($cycleValue === null) {
                        $cycleValue = "1";
                    }

                    // Extract delivery datetime
                    $dIndex = null;
                    $latestTimestamp = null;
                    for ($j = 0; $j < count($columns); $j++) {
                        if (trim($columns[$j]) === 'D') {
                            $dIndex = $j;
                        }
                        if (preg_match('/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{3})/', $columns[$j])) {
                            $latestTimestamp = $columns[$j];
                        }
                    }

                    $deliveryDateTime = null;
                    if ($dIndex !== null && isset($columns[$dIndex + 1])) {
                        $deliveryDateTime = $columns[$dIndex + 1];
                    } else {
                        $deliveryDateTime = $latestTimestamp;
                    }

                    if ($deliveryDateTime && preg_match('/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{3})/', $deliveryDateTime)) {
                        $d1Data[$cleanOrderNo] = [
                            'deliveryDateTime' => $deliveryDateTime,
                            'cycle' => $cycleValue,
                            'route' => $route,
                            'customer' => $customer,
                        ];
                    }

                    // Cek apakah ada D2 di bawah D1 ini
                    for ($k = $i + 1; $k < count($lines); $k++) {
                        $nextLine = trim($lines[$k]);
                        if (empty($nextLine)) continue;
                        $nextColumns = explode("\t", $nextLine);
                        if (isset($nextColumns[0]) && str_starts_with($nextColumns[0], 'D2')) {
                            $d2OrderNo = $nextColumns[1] ?? null;
                            if ($d2OrderNo) {
                                $cleanD2OrderNo = trim(preg_replace('/[^0-9]/', '', $d2OrderNo));
                                if ($cleanD2OrderNo === $cleanOrderNo) {
                                    $hasD2Below[$cleanOrderNo] = true;
                                    break;
                                }
                            }
                        }
                    }
                }

                // ============ PROSES BARIS D2 ============
                if (isset($columns[0]) && str_starts_with($columns[0], 'D2')) {
                    Log::info('D2 line detected at index ' . $i . ': ' . substr($line, 0, 100));
                    
                    $orderNo = $columns[1] ?? null;
                    if (empty($orderNo)) {
                        Log::warning('D2 order_no empty at index ' . $i);
                        continue;
                    }

                    $cleanOrderNo = trim(preg_replace('/[^0-9]/', '', $orderNo));
                    if (empty($cleanOrderNo)) {
                        Log::warning('D2 cleanOrderNo empty at index ' . $i . ', original: ' . $orderNo);
                        continue;
                    }
                    
                    Log::info('D2 processing order_no: ' . $cleanOrderNo);

                    $d2Data[$cleanOrderNo] = [
                        'order_no' => $cleanOrderNo,
                    ];
                }
            }

            Log::info('Parsing completed:');
            Log::info('- Total D1 entries: ' . count($d1Data));
            Log::info('- Total D2 entries: ' . count($d2Data));
            Log::info('- D2 with D1 below: ' . count($hasD2Below));
            
            if (!empty($d1Data)) {
                Log::info('Sample D1 order_nos: ' . implode(', ', array_slice(array_keys($d1Data), 0, 5)));
            }
            if (!empty($d2Data)) {
                Log::info('Sample D2 order_nos: ' . implode(', ', array_slice(array_keys($d2Data), 0, 5)));
            }
            if (!empty($hasD2Below)) {
                Log::info('Sample hasD2Below order_nos: ' . implode(', ', array_slice(array_keys($hasD2Below), 0, 5)));
            }

            // ========================================
            // BUILD PREPARATIONS DATA
            // ========================================
            
            $noDnList = [];
            
            foreach ($d2Data as $orderNo => $d2) {
                // Hanya proses jika D2 ini punya D1 di atasnya
                if (isset($hasD2Below[$orderNo]) && $hasD2Below[$orderNo]) {
                    // Cari D1 terdekat
                    $nearestD1 = null;
                    foreach (array_reverse($d1Data, true) as $d1OrderNo => $d1) {
                        if ($d1OrderNo === $orderNo) {
                            $nearestD1 = $d1;
                            break;
                        }
                    }

                    $deliveryDateTime = $nearestD1['deliveryDateTime'] ?? null;
                    $cycleValue = $nearestD1['cycle'] ?? "1";
                    $routeValue = $nearestD1['route'] ?? "UNKNOWN";
                    $customerValue = $nearestD1['customer'] ?? "TMMIN";

                    // Fallback delivery datetime jika tidak ada
                    if (!$deliveryDateTime || !preg_match('/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{3})/', $deliveryDateTime)) {
                        $baseDateTime = Carbon::parse('2025-02-05 13:24:00.000');
                        $deliveryDateTime = $baseDateTime->addMinutes(count($preparationsData) * 5)->format('Y-m-d H:i:s.v');
                    }

                    $deliveryCarbon = Carbon::parse($deliveryDateTime);
                    $pullingCarbon = Carbon::parse($deliveryDateTime)->subHours(3);

                    $preparationData = [
                        'no_dn' => $d2['order_no'],
                        'route' => $routeValue,
                        'customers' => 'TMMIN',
                        'dock' => $customerValue,
                        'delivery_date' => $deliveryCarbon->toDateString(),
                        'delivery_time' => $deliveryCarbon->toTimeString(),
                        'cycle' => $cycleValue,
                        'pulling_date' => $pullingCarbon->toDateString(),
                        'pulling_time' => $pullingCarbon->toTimeString(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $preparationsData[$orderNo] = $preparationData;
                    $noDnList[] = $d2['order_no'];
                }
            }

            Log::info('Total preparations data collected: ' . count($preparationsData));

            // ========================================
            // AUTO-FILL LOGISTIC PARTNER DARI CONFIG
            // ========================================
            
            $lpConfigs = LpConfig::pluck('logistic_partner', 'route')->toArray();
            
            foreach ($preparationsData as &$data) {
                $route = $data['route'];
                $logisticPartner = $lpConfigs[$route] ?? '';
                $data['logistic_partners'] = $logisticPartner;
                
                if (empty($logisticPartner)) {
                    Log::warning('Logistic Partner tidak ditemukan untuk route: ' . $route);
                }
            }

            // Cek apakah ada data yang valid
            if (empty($preparationsData)) {
                Log::warning('Tidak ada data valid yang ditemukan di D2 dengan D1 yang relevan.');
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada data valid ditemukan dalam file TXT.',
                ], 400);
            }

            // ========================================
            // CEK DUPLIKAT DI DATABASE
            // ========================================
            
            $existingPreparations = Preparation::whereIn('no_dn', $noDnList)->pluck('no_dn')->toArray();

            if (!empty($existingPreparations) && !$request->has('force_import')) {
                return response()->json([
                    'status' => 'duplicates_found',
                    'message' => 'Ditemukan ' . count($existingPreparations) . ' data duplikat. Apakah ingin melanjutkan import?',
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
                        Log::info('Saving TMMIN Preparation:', ['no_dn' => $data['no_dn'], 'route' => $data['route']]);
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
                    Log::info('Skipping duplicate TMMIN Preparation:', ['no_dn' => $data['no_dn']]);
                    $skippedCount++;
                }
            }

            // ========================================
            // SUCCESS MESSAGE
            // ========================================
            
            $successMessage = "TMMIN data berhasil diimpor. {$savedCount} data berhasil disimpan";
            if ($skippedCount > 0) {
                $successMessage .= ", {$skippedCount} data dilewati";
            }
            $successMessage .= ".";

            Log::info('Import TMMIN completed: ' . $successMessage);

            return response()->json([
                'status' => 'success',
                'message' => $successMessage,
                'saved' => $savedCount,
                'skipped' => $skippedCount,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error importing TMMIN TXT data: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengimpor data: ' . $e->getMessage(),
            ], 500);
        }
    }
}
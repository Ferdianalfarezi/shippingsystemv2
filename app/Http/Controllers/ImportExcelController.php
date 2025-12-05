<?php

namespace App\Http\Controllers;

use App\Models\Preparation;
use App\Models\LpConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ImportExcelController extends Controller
{
    public function import(Request $request)
    {
        try {
            // Validasi file
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls|max:2048',
            ]);

            // Baca isi file Excel untuk pengecekan duplikat
            $collection = Excel::toCollection(null, $request->file('file'))->first();

            if ($collection->isEmpty()) {
                Log::warning('File kosong atau tidak terbaca');
                return response()->json([
                    'status' => 'error',
                    'message' => 'File kosong atau format tidak sesuai',
                ], 400);
            }

            $noDnList = [];

            // Kumpulkan no_dn unik dari file dan tracking duplikat internal
            $seenNoDn = [];
            $internalDuplicates = [];
            
            foreach ($collection->skip(1) as $rowIndex => $row) {
                if (!isset($row[1]) || empty($row[1])) {
                    continue;
                }
                
                $noDn = $row[1];
                
                if (in_array($noDn, $seenNoDn)) {
                    $internalDuplicates[] = $noDn;
                    Log::info("Duplikat internal ditemukan pada baris " . ($rowIndex + 2) . ": " . $noDn);
                    continue;
                }
                
                $seenNoDn[] = $noDn;
                $noDnList[] = $noDn;
            }

            if (empty($noDnList)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada data preparation yang valid untuk diproses',
                ], 400);
            }

            if (!empty($internalDuplicates)) {
                $uniqueInternalDuplicates = array_unique($internalDuplicates);
                Log::info('Duplikat internal dalam file ditemukan: ' . count($uniqueInternalDuplicates) . ' no_dn unik dengan total ' . count($internalDuplicates) . ' baris duplikat');
            }

            // Check for duplicates in Preparations table
            $existingPreparations = Preparation::whereIn('no_dn', $noDnList)->pluck('no_dn')->toArray();

            if (!empty($existingPreparations) && !$request->has('force_import')) {
                $message = 'Terdapat ' . count($existingPreparations) . ' data duplikat ditemukan di database.';
                if (!empty($internalDuplicates)) {
                    $message .= ' Juga ditemukan ' . count(array_unique($internalDuplicates)) . ' duplikat internal dalam file yang akan diabaikan.';
                }
                
                return response()->json([
                    'status' => 'duplicates_found',
                    'message' => $message,
                    'duplicates' => [
                        [
                            'table' => 'preparations',
                            'count' => count($existingPreparations),
                            'data' => $existingPreparations
                        ]
                    ],
                    'internal_duplicates_count' => count(array_unique($internalDuplicates)),
                ], 200);
            }

            // Load semua konfigurasi LP ke dalam array untuk performa
            $lpConfigs = LpConfig::pluck('logistic_partner', 'route')->toArray();

            // Import dengan filter duplikat
            $savedCount = 0;
            $skippedCount = 0;
            $processedNoDn = [];

            foreach ($collection->skip(1) as $row) {
                $noDn = $row[1] ?? null;
                
                if (!$noDn || empty($noDn)) {
                    continue;
                }
                
                if (in_array($noDn, $existingPreparations) || 
                    in_array($noDn, $processedNoDn)) {
                    
                    $skippedCount++;
                    
                    if (in_array($noDn, $processedNoDn)) {
                        Log::info('Skipping internal duplicate Preparation:', ['no_dn' => $noDn]);
                    } else {
                        Log::info('Skipping duplicate Preparation:', ['no_dn' => $noDn]);
                    }
                    
                    continue;
                }

                $processedNoDn[] = $noDn;

                // Parse dates and times
                $deliveryDate = null;
                $deliveryTime = null;
                $pullingDate = null;
                $pullingTime = null;

                // Handle delivery_date (index 4)
                if (isset($row[4]) && !empty($row[4])) {
                    try {
                        if ($row[4] instanceof \DateTime) {
                            $deliveryDate = $row[4]->format('Y-m-d');
                        } elseif (is_numeric($row[4])) {
                            $deliveryDate = Carbon::createFromFormat('Y-m-d', \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[4])->format('Y-m-d'))->format('Y-m-d');
                        } else {
                            $deliveryDate = Carbon::parse($row[4])->format('Y-m-d');
                        }
                    } catch (\Exception $e) {
                        Log::warning('Invalid delivery date format for no_dn ' . $noDn . ': ' . $row[4]);
                    }
                }

                // Handle delivery_time (index 5)
                if (isset($row[5]) && !empty($row[5])) {
                    try {
                        if ($row[5] instanceof \DateTime) {
                            $deliveryTime = $row[5]->format('H:i:s');
                        } elseif (is_numeric($row[5])) {
                            $deliveryTime = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[5])->format('H:i:s');
                        } else {
                            $deliveryTime = Carbon::parse($row[5])->format('H:i:s');
                        }
                    } catch (\Exception $e) {
                        Log::warning('Invalid delivery time format for no_dn ' . $noDn . ': ' . $row[5]);
                        $deliveryTime = '00:00:00';
                    }
                }

                // Handle pulling_date (index 7)
                if (isset($row[7]) && !empty($row[7])) {
                    try {
                        if (is_string($row[7]) && strpos($row[7], '=') === 0) {
                            if ($deliveryDate) {
                                $pullingDate = Carbon::parse($deliveryDate)->subDay()->format('Y-m-d');
                            }
                        } elseif ($row[7] instanceof \DateTime) {
                            $pullingDate = $row[7]->format('Y-m-d');
                        } elseif (is_numeric($row[7])) {
                            $pullingDate = Carbon::createFromFormat('Y-m-d', \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[7])->format('Y-m-d'))->format('Y-m-d');
                        } else {
                            $pullingDate = Carbon::parse($row[7])->format('Y-m-d');
                        }
                    } catch (\Exception $e) {
                        Log::warning('Invalid pulling date format for no_dn ' . $noDn . ': ' . $row[7]);
                        if ($deliveryDate) {
                            $pullingDate = Carbon::parse($deliveryDate)->subDay()->format('Y-m-d');
                        }
                    }
                }

                // Handle pulling_time (index 8)
                if (isset($row[8]) && !empty($row[8])) {
                    try {
                        if ($row[8] instanceof \DateTime) {
                            $pullingTime = $row[8]->format('H:i:s');
                        } elseif (is_numeric($row[8])) {
                            $pullingTime = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[8])->format('H:i:s');
                        } else {
                            $pullingTime = Carbon::parse($row[8])->format('H:i:s');
                        }
                    } catch (\Exception $e) {
                        Log::warning('Invalid pulling time format for no_dn ' . $noDn . ': ' . $row[8]);
                        $pullingTime = '00:00:00';
                    }
                }

                // Ambil route dari Excel
                $route = $row[0] ?? '';
                
                // Cari logistic_partner berdasarkan route dari konfigurasi
                $logisticPartner = $lpConfigs[$route] ?? '';
                
                if (empty($logisticPartner)) {
                    Log::warning('Logistic Partner tidak ditemukan untuk route: ' . $route);
                }

                try {
                    Preparation::create([
                        'route' => $route,
                        'logistic_partners' => $logisticPartner, // Auto-filled dari konfigurasi
                        'no_dn' => $noDn,
                        'customers' => $row[2] ?? '',
                        'dock' => $row[3] ?? '',
                        'delivery_date' => $deliveryDate,
                        'delivery_time' => $deliveryTime ?? '00:00:00',
                        'cycle' => $row[6] ?? 1,
                        'pulling_date' => $pullingDate,
                        'pulling_time' => $pullingTime ?? '00:00:00',
                    ]);

                    $savedCount++;
                    Log::info('Saving Preparation:', ['no_dn' => $noDn, 'route' => $route, 'lp' => $logisticPartner]);
                } catch (\Exception $e) {
                    Log::error('Failed to save preparation:', ['no_dn' => $noDn, 'error' => $e->getMessage()]);
                    $skippedCount++;
                }
            }

            $successMessage = "Data berhasil diimpor. {$savedCount} data berhasil disimpan";
            if ($skippedCount > 0) {
                $successMessage .= ", {$skippedCount} data duplikat dilewati";
            }
            $successMessage .= ".";

            return response()->json([
                'status' => 'success',
                'message' => $successMessage,
                'saved' => $savedCount,
                'skipped' => $skippedCount,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error saat mengimpor file: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengimpor data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        $templatePath = public_path('templates/preparation_template.xlsx');
        
        if (!file_exists($templatePath)) {
            return response()->json([
                'status' => 'error',
                'message' => 'File template tidak ditemukan',
            ], 404);
        }

        return response()->download($templatePath, 'preparation_template.xlsx');
    }
}
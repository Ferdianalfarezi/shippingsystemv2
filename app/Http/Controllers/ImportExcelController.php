<?php

namespace App\Http\Controllers;

use App\Models\Preparation;
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
            $seenNoDn = []; // Track no_dn yang sudah ditemukan dalam file
            $internalDuplicates = []; // Track duplikat dalam file
            
            foreach ($collection->skip(1) as $rowIndex => $row) {
                // Mapping: order_no di Excel = no_dn di database (index 1)
                if (!isset($row[1]) || empty($row[1])) {
                    continue; // Skip baris kosong, ga usah ditrack
                }
                
                $noDn = $row[1];
                
                // Cek apakah no_dn sudah ada dalam file
                if (in_array($noDn, $seenNoDn)) {
                    $internalDuplicates[] = $noDn;
                    Log::info("Duplikat internal ditemukan pada baris " . ($rowIndex + 2) . ": " . $noDn);
                    continue; // Skip duplikat internal
                }
                
                $seenNoDn[] = $noDn;
                $noDnList[] = $noDn; // Hanya tambahkan yang unik
            }

            if (empty($noDnList)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada data preparation yang valid untuk diproses',
                ], 400);
            }

            // Log info tentang duplikat internal
            if (!empty($internalDuplicates)) {
                $uniqueInternalDuplicates = array_unique($internalDuplicates);
                Log::info('Duplikat internal dalam file ditemukan: ' . count($uniqueInternalDuplicates) . ' no_dn unik dengan total ' . count($internalDuplicates) . ' baris duplikat');
            }

            // Check for duplicates in Preparations table
            $existingPreparations = Preparation::whereIn('no_dn', $noDnList)->pluck('no_dn')->toArray();

            // If duplicates are found and force_import is not set, return JSON response
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

            // Import dengan filter duplikat
            $savedCount = 0;
            $skippedCount = 0;
            $processedNoDn = []; // Track no_dn yang sudah diproses

            foreach ($collection->skip(1) as $row) {
                // Mapping kolom sesuai struktur Excel:
                // Index 0: route
                // Index 1: order_no (jadi no_dn)
                // Index 2: customer (jadi customers)
                // Index 3: dock
                // Index 4: delivery_date
                // Index 5: delivery_time
                // Index 6: cycle
                // Index 7: pulling_date (bisa formula Excel)
                // Index 8: pulling_time
                
                $noDn = $row[1] ?? null;
                
                // Skip baris kosong tanpa menambah skippedCount
                if (!$noDn || empty($noDn)) {
                    continue;
                }
                
                // Skip jika duplikat dengan database atau sudah diproses sebelumnya
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

                // Tandai no_dn sebagai sudah diproses
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

                // Handle pulling_date (index 7) - bisa berupa formula Excel
                if (isset($row[7]) && !empty($row[7])) {
                    try {
                        // Kalau formula Excel (contoh: =E2-1), skip dan hitung manual
                        if (is_string($row[7]) && strpos($row[7], '=') === 0) {
                            // Hitung pulling_date dari delivery_date - 1 hari
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
                        // Fallback: pulling_date = delivery_date - 1 hari
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

                try {
                    Preparation::create([
                        'route' => $row[0] ?? '',
                        'logistic_partners' => '', // Kosongkan dulu sesuai permintaan
                        'no_dn' => $noDn,
                        'customers' => $row[2] ?? '', // customer di Excel jadi customers
                        'dock' => $row[3] ?? '',
                        'delivery_date' => $deliveryDate,
                        'delivery_time' => $deliveryTime ?? '00:00:00',
                        'cycle' => $row[6] ?? 1,
                        'pulling_date' => $pullingDate,
                        'pulling_time' => $pullingTime ?? '00:00:00',
                    ]);

                    $savedCount++;
                    Log::info('Saving Preparation:', ['no_dn' => $noDn]);
                } catch (\Exception $e) {
                    Log::error('Failed to save preparation:', ['no_dn' => $noDn, 'error' => $e->getMessage()]);
                    $skippedCount++;
                }
            }

            // Update success message dengan info duplikat internal
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
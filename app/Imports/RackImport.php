<?php

namespace App\Imports;

use App\Models\Address;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class RackImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    public $updated = 0;
    public $notFound = 0;
    public $notFoundParts = [];

    public function model(array $row)
    {
        // Ambil part_no dan rack_no dari Excel
        $partNo = trim($row['part_no'] ?? '');
        $rackNo = trim($row['rack_no'] ?? '');

        // Skip jika part_no kosong
        if (empty($partNo)) {
            return null;
        }

        // Update SEMUA address dengan part_no yang sama
        $updatedCount = Address::where('part_no', $partNo)->update(['rack_no' => $rackNo]);

        if ($updatedCount > 0) {
            // Tambahkan jumlah row yang terupdate
            $this->updated += $updatedCount;
        } else {
            // Part tidak ditemukan di database
            $this->notFound++;
            if (count($this->notFoundParts) < 10) {
                $this->notFoundParts[] = $partNo;
            }
        }

        // Return null karena kita tidak insert data baru
        return null;
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function getResults(): array
    {
        return [
            'updated' => $this->updated,
            'not_found' => $this->notFound,
            'not_found_parts' => $this->notFoundParts,
        ];
    }
}
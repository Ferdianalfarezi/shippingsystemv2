<?php

namespace App\Imports;

use App\Models\AdmAddressv2;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class AdmAddressesImportv2 implements ToModel, WithHeadingRow, SkipsEmptyRows, WithBatchInserts, WithChunkReading
{
    /**
     * Baris 1 di excel = judul "Master Part List", jadi header
     * kolom sebenarnya ada di baris ke-2.
     */
    public function headingRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {
        // key otomatis jadi snake_case dari header excel,
        // misal PART_NO -> part_no, QTY_KBN -> qty_kbn
        if (empty($row['part_no'])) {
            return null;
        }

        return new AdmAddressv2([
            'part_no'       => $row['part_no'],
            'customer_code' => $row['customer_code'] ?? null,
            'part_name'     => $row['part_name'] ?? null,
            'qty_kbn'       => $row['qty_kbn'] ?? null,
            'rack_no'       => $row['rack_no'] ?? null,
        ]);
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
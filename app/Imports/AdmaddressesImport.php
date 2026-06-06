<?php

namespace App\Imports;

use App\Models\Admaddress;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class AdmaddressesImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public function headingRow(): int
    {
        return 2; // Baris 1 = judul "Master Part List", baris 2 = header kolom
    }

    public function model(array $row)
    {
        if (empty($row['part_no'])) {
            return null;
        }

        return new Admaddress([
            'part_no'       => $row['part_no']        ?? null,
            'customer_code' => $row['customer_code']  ?? null,
            'model'         => $row['model']           ?? null,
            'part_name'     => $row['part_name']       ?? null,
            'qty_kbn'       => $row['qty_kbn']         ?? null,
            'line'          => $row['line']            ?? null,
            'rack_no'       => $row['rack_no']         ?? null,
        ]);
    }
}
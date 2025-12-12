<?php

namespace App\Imports;

use App\Models\Address;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AddressesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Address([
            'part_no'       => $row['part_no'] ?? null,
            'customer_code' => $row['customer_code'] ?? null,
            'model'         => $row['model'] ?? null,
            'part_name'     => $row['part_name'] ?? null,
            'qty_kbn'       => $row['qty_kbn'] ?? null,
            'line'          => $row['line'] ?? null,
            'rack_no'       => $row['rack_no'] ?? null,
        ]);
    }
}

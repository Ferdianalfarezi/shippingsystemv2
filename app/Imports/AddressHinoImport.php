<?php

namespace App\Imports;

use App\Models\AddressHino;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AddressHinoImport implements ToCollection, WithHeadingRow
{
    public function headingRow(): int
    {
        return 2;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $partNo = trim((string) ($row['part_no'] ?? ''));
            $customerCode = trim((string) ($row['customer_code'] ?? ''));
            $partName = trim((string) ($row['part_name'] ?? ''));
            $rackNo = trim((string) ($row['rack_no'] ?? ''));

            if ($partNo === '') {
                continue;
            }

            if ($rackNo !== '' && str_starts_with($rackNo, '=')) {
                continue;
            }

            AddressHino::updateOrCreate(
                ['part_no' => $partNo],
                [
                    'customer_code' => $customerCode !== '' ? $customerCode : null,
                    'part_name' => ($partName !== '' && $partName !== '-') ? $partName : null,
                    'rack_no' => $rackNo !== '' ? $rackNo : null,
                ]
            );
        }
    }
}
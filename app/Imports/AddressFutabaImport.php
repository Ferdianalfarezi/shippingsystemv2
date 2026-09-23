<?php

namespace App\Imports;

use App\Models\AddressFutaba;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AddressFutabaImport implements ToCollection, WithHeadingRow
{
    /**
     * Sama kayak excel NTC/FJI: baris 1 cuma judul, header asli
     * (NO, CUSTOMER_CODE, PART_NO, RACK_NO, PART_NAME) ada di baris 2.
     */
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

            AddressFutaba::updateOrCreate(
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
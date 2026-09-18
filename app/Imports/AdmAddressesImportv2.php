<?php

namespace App\Imports;

use App\Models\AdmAddressv2;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AdmAddressesImportv2 implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $partNo = trim((string) ($row['part_no'] ?? ''));

            // dukung header "ADDRES" atau "ADDRESS" biar aman
            $rackNo = trim((string) ($row['addres'] ?? $row['address'] ?? ''));

            if ($partNo === '') {
                continue; // skip baris tanpa part_no
            }

            // guard: kalau value masih berupa formula mentah (excel-nya belum
            // di-paste-as-value / cached value-nya ilang), skip biar ga nyimpen sampah
            if ($rackNo !== '' && str_starts_with($rackNo, '=')) {
                continue;
            }

            AdmAddressv2::updateOrCreate(
                ['part_no' => $partNo],
                ['rack_no' => $rackNo]
            );
        }
    }
}
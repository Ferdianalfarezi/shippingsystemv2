<?php

namespace App\Imports;

use App\Models\AddressFji;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AddressFjiImport implements ToCollection, WithHeadingRow
{
    /**
     * Sama kayak excel NTC: baris 1 cuma judul ("Master Part List"),
     * header asli (NO, CUSTOMER_CODE, PART_NO, RACK_NO, PART_NAME)
     * ada di baris 2.
     */
    public function headingRow(): int
    {
        return 2;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // beda dari NTC: kolomnya "PART_NO" langsung (bukan PART_NO_FG),
            // dan formatnya emang udah persis sama kayak "Part Number" di PDF,
            // gak ada suffix tambahan yang perlu di-strip.
            $partNo = trim((string) ($row['part_no'] ?? ''));
            $customerCode = trim((string) ($row['customer_code'] ?? ''));
            $partName = trim((string) ($row['part_name'] ?? ''));
            $rackNo = trim((string) ($row['rack_no'] ?? ''));

            if ($partNo === '') {
                continue; // skip baris tanpa part no
            }

            // guard: kalau value masih formula mentah, skip biar ga nyimpen sampah
            if ($rackNo !== '' && str_starts_with($rackNo, '=')) {
                continue;
            }

            AddressFji::updateOrCreate(
                ['part_no' => $partNo],
                [
                    'customer_code' => $customerCode !== '' ? $customerCode : null,
                    // beberapa baris part_name-nya cuma "-" (kosong), jangan disimpen literal
                    'part_name' => ($partName !== '' && $partName !== '-') ? $partName : null,
                    'rack_no' => $rackNo !== '' ? $rackNo : null,
                ]
            );
        }
    }
}
<?php

namespace App\Imports;

use App\Models\NtcAddress;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class NtcAddressesImport implements ToCollection, WithHeadingRow
{
    /**
     * Baris 1 di excel NTC cuma judul ("Master Part List"), header
     * kolom asli (NO, CUSTOMER_CODE, PART_NO_FG, RACK_NO, PART_NAME)
     * ada di baris 2. Default Maatwebsite Excel nganggep baris 1 =
     * heading, makanya di-override ke 2 di sini.
     */
    public function headingRow(): int
    {
        return 2;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // kolom excel "PART_NO_FG" dipetain ke field part_no di tabel kita
            $partNo = trim((string) ($row['part_no_fg'] ?? ''));
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

            NtcAddress::updateOrCreate(
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
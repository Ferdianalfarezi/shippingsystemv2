<?php

namespace App\Imports;

use App\Models\HpmAddress;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class HpmAddressImport implements ToCollection, WithHeadingRow
{
    public int $updated = 0;
    public int $created = 0;
    public int $skipped = 0;

    /**
     * Heading row index = 2 karena row 1 adalah judul "Master Part List"
     */
    public function headingRow(): int
    {
        return 2;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $partNo   = trim($row['part_no'] ?? '');
            $partName = trim($row['part_name'] ?? '');
            $rackNo   = trim($row['rack_no'] ?? '');

            if (empty($partNo)) {
                $this->skipped++;
                continue;
            }

            $existing = HpmAddress::where('part_no', $partNo)->first();

            if ($existing) {
                $existing->update([
                    'part_name' => $partName ?: $existing->part_name,
                    'rack_no'   => $rackNo ?: $existing->rack_no,
                ]);
                $this->updated++;
            } else {
                HpmAddress::create([
                    'part_no'   => $partNo,
                    'part_name' => $partName,
                    'rack_no'   => $rackNo,
                ]);
                $this->created++;
            }
        }
    }
}
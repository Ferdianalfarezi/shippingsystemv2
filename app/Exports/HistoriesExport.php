<?php

namespace App\Exports;

use App\Models\History;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HistoriesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $search;
    protected $dateFrom;
    protected $dateTo;

    public function __construct($search = null, $dateFrom = null, $dateTo = null)
    {
        $this->search   = $search;
        $this->dateFrom = $dateFrom;
        $this->dateTo   = $dateTo;
    }

    public function query()
    {
        $query = History::query();

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('route', 'like', "%{$search}%")
                  ->orWhere('logistic_partners', 'like', "%{$search}%")
                  ->orWhere('no_dn', 'like', "%{$search}%")
                  ->orWhere('customers', 'like', "%{$search}%")
                  ->orWhere('dock', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($this->dateFrom) {
            $query->whereDate('completed_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('completed_at', '<=', $this->dateTo);
        } elseif ($this->dateFrom) {
            $query->whereDate('completed_at', '<=', $this->dateFrom);
        }

        return $query->orderBy('completed_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'Route',
            'LP',
            'No DN',
            'Customer',
            'Dock',
            'Cycle',
            'Address',
            'Pulling Datetime',
            'Delivery Datetime',
            'Scan to Shipping',
            'Arrival',
            'Scan to Delivery',
            'Completed At',
            'Shipping Duration',
            'Loading Duration',
            'Delivery Duration',
            'Total Journey Duration',
            'Total Business Hours',
            'Moved By',
        ];
    }

    public function map($history): array
    {
        return [
            $history->route,
            $history->logistic_partners,
            $history->no_dn,
            $history->customers,
            $history->dock,
            $history->cycle,
            $history->address,
            $history->formatted_pulling_datetime,
            $history->formatted_delivery_datetime,
            $history->formatted_scan_to_shipping,
            $history->formatted_arrival,
            $history->formatted_scan_to_delivery,
            $history->formatted_completed_at,
            $this->formatDuration($history->shipping_duration),
            $this->formatDuration($history->loading_duration),
            $this->formatDuration($history->delivery_duration),
            $this->formatDuration($history->total_journey_duration),
            $history->formatted_duration,
            $history->moved_by,
        ];
    }

    /**
     * Bulatin durasi yang formatnya "X.XXXXXXX menit" jadi "X menit".
     * Kalau formatnya udah "Xh Ym" (jam-menit), biarin apa adanya.
     */
    private function formatDuration($value)
    {
        if (!$value) {
            return $value;
        }

        // Format "Xh Ym" atau cuma "Xh" biarin apa adanya, itu udah bulat
        if (str_contains($value, 'h')) {
            return $value;
        }

        // Format "X.XXXXX menit" -> bulatin ke integer terdekat
        if (preg_match('/^([\d.]+)\s*menit$/u', trim($value), $matches)) {
            $rounded = round((float) $matches[1]);
            return $rounded . ' menit';
        }

        return $value;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
<?php

namespace App\Exports;

use App\Models\Milkrun;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class MilkrunExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $dateFilter;
    protected $statusFilter;
    protected $search;

    public function __construct($dateFilter = null, $statusFilter = null, $search = null)
    {
        $this->dateFilter = $dateFilter;
        $this->statusFilter = $statusFilter;
        $this->search = $search;
    }

    public function collection()
    {
        $query = Milkrun::whereNotNull('arrival');

        if ($this->dateFilter) {
            $query->whereDate('delivery_date', $this->dateFilter);
        }

        if ($this->statusFilter && $this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('route', 'like', "%{$this->search}%")
                  ->orWhere('logistic_partners', 'like', "%{$this->search}%")
                  ->orWhere('customers', 'like', "%{$this->search}%")
                  ->orWhere('dock', 'like', "%{$this->search}%");
            });
        }

        return $query->orderByRaw("CONCAT(delivery_date, ' ', delivery_time) ASC")->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Customer',
            'Route',
            'Logistic Partner',
            'Cycle',
            'Dock',
            'Delivery Date',
            'Delivery Time',
            'Arrival',
            'Departure',
            'Status',
            'Selisih Waktu',
            'Jumlah DN',
        ];
    }

    private $rowNumber = 0;

    public function map($milkrun): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $milkrun->customers,
            $milkrun->route,
            $milkrun->logistic_partners,
            $milkrun->cycle,
            $milkrun->dock,
            $milkrun->delivery_date ? $milkrun->delivery_date->format('d-m-Y') : '-',
            $milkrun->delivery_time ? date('H:i', strtotime($milkrun->delivery_time)) : '-',
            $milkrun->arrival ? $milkrun->arrival->format('d-m-Y H:i:s') : '-',
            $milkrun->departure ? $milkrun->departure->format('d-m-Y H:i:s') : '-',
            $milkrun->status_label,
            $milkrun->time_diff_info ?? '-',
            $milkrun->dn_count,
        ];
    }

    public function title(): string
    {
        return 'Milkrun ' . ($this->dateFilter ?? Carbon::today()->format('Y-m-d'));
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 20,
            'C' => 15,
            'D' => 20,
            'E' => 8,
            'F' => 12,
            'G' => 14,
            'H' => 12,
            'I' => 18,
            'J' => 18,
            'K' => 12,
            'L' => 18,
            'M' => 10,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style header
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(22);

        // Border semua cell yang ada data
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle("A1:M{$highestRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        // Center kolom tertentu
        $sheet->getStyle("A2:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("E2:E{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("G2:M{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }
}
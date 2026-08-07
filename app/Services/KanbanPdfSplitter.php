<?php

namespace App\Services;

use setasign\Fpdi\Tcpdf\Fpdi;

class KanbanPdfSplitter
{
    /** Jumlah kanban per halaman sumber (tersusun vertikal) */
    protected int $labelsPerPage;

    public function __construct(int $labelsPerPage = 4)
    {
        $this->labelsPerPage = $labelsPerPage;
    }

    /**
     * Split PDF: N kanban/halaman -> 1 halaman = 1 kanban.
     *
     * @return array{output: string, total_source_pages: int, total_labels: int}
     */
    public function split(string $sourcePath, string $outputPath): array
    {
        $pdf = new Fpdi();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $sourcePageCount = $pdf->setSourceFile($sourcePath);
        $totalLabels = 0;

        for ($pageNo = 1; $pageNo <= $sourcePageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);

            $fullWidth   = $size['width'];
            $fullHeight  = $size['height'];
            $labelHeight = $fullHeight / $this->labelsPerPage;

            for ($i = 0; $i < $this->labelsPerPage; $i++) {
                $orientation = $fullWidth > $labelHeight ? 'L' : 'P';
                $pdf->AddPage($orientation, [$fullWidth, $labelHeight]);

                // geser template ke atas sebesar (i * labelHeight)
                // supaya cuma strip ke-i yang jatuh di dalam mediabox page baru
                $yOffset = -($i * $labelHeight);
                $pdf->useTemplate($templateId, 0, $yOffset, $fullWidth, $fullHeight);

                $totalLabels++;
            }
        }

        $pdf->Output($outputPath, 'F');

        return [
            'output' => $outputPath,
            'total_source_pages' => $sourcePageCount,
            'total_labels' => $totalLabels,
        ];
    }
}
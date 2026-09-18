<?php

namespace App\Services;

use Symfony\Component\Process\Process;

class PdfNormalizer
{
    /**
     * Bongkar ulang struktur PDF (xref stream, object stream) jadi
     * format klasik yang bisa dibaca FPDI gratis & Smalot/pdfparser.
     * Return path file baru (temp), path asli TIDAK diubah.
     */
    public function normalize(string $sourcePath): string
    {
        $outputPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('pdf_norm_') . '.pdf';

        $process = new Process([
            'qpdf',
            '--object-streams=disable',
            $sourcePath,
            $outputPath,
        ]);
        $process->setTimeout(60);
        $process->run();

        if (!$process->isSuccessful() || !file_exists($outputPath)) {
            throw new \RuntimeException(
                'qpdf gagal normalize PDF: ' . $process->getErrorOutput()
            );
        }

        return $outputPath;
    }
}
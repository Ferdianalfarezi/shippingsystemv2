<?php

namespace App\Services;

class PartNoExtractor
{
    /**
     * Pola umum part number di label ADM. Segmen awal & tengah
     * (masing-masing 5 karakter) bisa campuran huruf & angka
     * posisi bebas — variasi yang kepake:
     * 67361-BZ030-00  (awal 5 angka, tengah 2 huruf+3 angka)
     * 82715-BZG60-00  (awal 5 angka, tengah 3 huruf+2 angka)
     * 96111-30440-00  (awal 5 angka, tengah 5 angka semua)
     * 9004A-20020-00  (awal 4 angka+1 huruf, tengah 5 angka)
     */
    protected string $pattern = '/[A-Z0-9]{5}-[A-Z0-9]{5}(-\d{2})?/';

    /**
     * Ambil kandidat part number pertama yang ketemu di teks 1 label.
     * Return null kalau gak ketemu sama sekali.
     */
    public function extract(string $labelText): ?string
    {
        if (preg_match($this->pattern, $labelText, $matches)) {
            return $matches[0];
        }

        return null;
    }

    /**
     * Buang suffix "-00" / "-01" dll di belakang part number,
     * karena admadresses.part_no biasanya gak pake suffix.
     * Contoh: 67361-BZ030-00 -> 67361-BZ030
     */
    public function stripSuffix(string $partNo): string
    {
        $partNo = trim($partNo);

        // buang suffix "-00", "-01", ..., "-99" di paling belakang
        return preg_replace('/-\d{2}$/', '', $partNo);
    }
}
<?php

use App\Support\Angka;

if (!function_exists('angka')) {
    /** Angka tampilan Indonesia ("200.000", "12,5"), nol pecahan dipotong. */
    function angka(mixed $nilai, int $maks = 2): string
    {
        return Angka::format($nilai, $maks);
    }
}

if (!function_exists('rupiah')) {
    function rupiah(mixed $nilai, bool $denganAwalan = true): string
    {
        return Angka::rupiah($nilai, $denganAwalan);
    }
}

if (!function_exists('angka_polos')) {
    /** Angka polos untuk input type=number / atribut data ("12.5"). */
    function angka_polos(mixed $nilai, int $maks = 4): string
    {
        return Angka::polos($nilai, $maks);
    }
}

if (!function_exists('angka_input')) {
    /** Isi awal kolom uang yang diformat saat mengetik. */
    function angka_input(mixed $nilai, int $maks = 0): string
    {
        return Angka::input($nilai, $maks);
    }
}

if (!function_exists('ket_ringkas')) {
    /**
     * Keterangan desain versi cetak. Penanda sistem yang panjang -
     * "[FILE SIAP CETAK - dari pelanggan, tanpa desain tim]" - dicetak
     * sebagai "FILE SIAP CETAK" saja, dan penanda yang tersimpan berulang
     * ditulis sekali. Teks lain dibiarkan apa adanya: catatan produksi
     * yang diketik orang adalah instruksi, bukan hiasan, jadi tidak
     * dipotong. Hanya untuk tampilan - isi database tidak diubah.
     */
    function ket_ringkas(?string $teks): string
    {
        $teks = trim((string) $teks);
        if ($teks === '') {
            return '';
        }

        $penanda = [];
        if (preg_match_all('/\[([^\]]+)\]/', $teks, $cocok)) {
            foreach (array_unique($cocok[1]) as $isi) {
                // Buang keterangan tambahan setelah tanda pisah:
                // "FILE SIAP CETAK - dari pelanggan, tanpa desain tim".
                $penanda[] = trim(preg_replace('/\s+-\s+.*$/', '', $isi));
            }
            $teks = preg_replace('/\[[^\]]+\]/', ' ', $teks);
        }

        $penanda = array_filter($penanda);
        $teks = trim($teks);

        if ($penanda !== []) {
            $teks = implode(' · ', $penanda) . ($teks !== '' ? ' · ' . $teks : '');
        }

        return trim(preg_replace('/\s+/', ' ', $teks));
    }
}

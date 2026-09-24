<?php

namespace App\Services;

/**
 * Satu-satunya tempat yang menerjemahkan `bom_detail.cara_hitung_pemakaian`
 * (rumus yang dipilih admin di form BOM produk) menjadi qty pemakaian bahan
 * baku total untuk satu baris order. Sebelumnya logika ini diduplikasi di 3
 * tempat (PenjualanController, StokReversalService, PenjualanApiController)
 * dan hanya mengenali literal '(P*L)/10000' - rumus 'Keliling (m)' dan
 * 'Bolak Balik (x2)' yang sudah tersedia di dropdown BOM (lihat
 * master/produk/index.blade.php) diam-diam ikut jatuh ke cabang quantity
 * biasa, sehingga pemakaian bahan untuk kedua rumus itu salah hitung.
 *
 * Rumus tetap: '(P*L)/10000' (luas m2), '(P*L)/10000*2', '2*(P+L)/100'
 * (keliling m), 'Q' (1:1).
 *
 * Rumus berfaktor (untuk pemakaian bahan yang bukan 1:1 dengan qty jual):
 *   'Q/N'  : N barang per satuan bahan. Contoh 'Q/4' = 4 foto 6R dalam 1
 *            lembar A3+, sehingga tiap foto memakai 0,25 lembar.
 *   'Q*K'  : K satuan bahan per barang. Contoh 'Q*5' = 5 lembar per box
 *            kartu nama; 'Q*0.22' = 0,22 m2 roll per foto 16R (termasuk
 *            sisa potong).
 *   '(P*L)/10000*K' : luas dikali faktor K (mis. 1.1 = tambah 10% sisa).
 *
 * Pakai mapping eksplisit + regex ketat (bukan eval()) supaya tidak ada
 * risiko rumus sembarangan dieksekusi sebagai kode PHP.
 */
class BomCalculationService
{
    private const ANGKA = '(\d+(?:\.\d+)?)';

    public static function hitungQtyPakai(string $rumus, float $panjang, float $lebar, float $qty): float
    {
        $rumus = str_replace(' ', '', $rumus);

        $tetap = match ($rumus) {
            '(P*L)/10000' => ($panjang * $lebar / 10000) * $qty,
            '(P*L)/10000*2' => ($panjang * $lebar / 10000) * 2 * $qty,
            '2*(P+L)/100' => (2 * ($panjang + $lebar) / 100) * $qty,
            'Q' => $qty,
            default => null,
        };
        if ($tetap !== null) {
            return $tetap;
        }

        if (preg_match('#^Q\*' . self::ANGKA . '$#', $rumus, $m)) {
            return $qty * (float) $m[1];
        }
        if (preg_match('#^Q/' . self::ANGKA . '$#', $rumus, $m) && (float) $m[1] > 0) {
            return $qty / (float) $m[1];
        }
        if (preg_match('#^\(P\*L\)/10000\*' . self::ANGKA . '$#', $rumus, $m)) {
            return ($panjang * $lebar / 10000) * (float) $m[1] * $qty;
        }

        return $qty;
    }

    /** Apakah rumus dikenali (dipakai validasi/penyaringan input BOM). */
    public static function rumusValid(string $rumus): bool
    {
        $rumus = str_replace(' ', '', $rumus);

        return in_array($rumus, ['(P*L)/10000', '(P*L)/10000*2', '2*(P+L)/100', 'Q'], true)
            || (bool) preg_match('#^Q\*' . self::ANGKA . '$#', $rumus)
            || (bool) preg_match('#^Q/' . self::ANGKA . '$#', $rumus)
            || (bool) preg_match('#^\(P\*L\)/10000\*' . self::ANGKA . '$#', $rumus);
    }
}

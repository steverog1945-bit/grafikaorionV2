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
 * Pakai mapping eksplisit (bukan eval()) supaya tidak ada risiko rumus
 * sembarangan dieksekusi sebagai kode PHP.
 */
class BomCalculationService
{
    public static function hitungQtyPakai(string $rumus, float $panjang, float $lebar, float $qty): float
    {
        return match ($rumus) {
            '(P*L)/10000' => ($panjang * $lebar / 10000) * $qty,
            '(P*L)/10000*2' => ($panjang * $lebar / 10000) * 2 * $qty,
            '2*(P+L)/100' => (2 * ($panjang + $lebar) / 100) * $qty,
            'Q' => $qty,
            default => $qty,
        };
    }
}

<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Jumlah baris per halaman laporan. Saat mode cetak (print=1), seluruh
     * isi laporan harus muat di satu halaman - laporan yang dipaginasi di
     * layar tidak boleh menghasilkan cetakan yang hanya berisi halaman
     * aktif. Paginator yang dipakai tetap sama supaya semua view & komponen
     * pagination tetap berfungsi tanpa perubahan.
     */
    protected function perHalamanLaporan(int $bawaan = 20): int
    {
        return request()->boolean('print') ? 100000 : $bawaan;
    }
}

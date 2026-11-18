<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Penjaga rentang tanggal untuk laporan/jurnal/buku besar.
 *
 * TANPA BATAS INI: satu request `?awal=2000-01-01&akhir=2030-12-31` memaksa
 * query agregat memindai SELURUH jurnal_umum sejak awal waktu sampai masa
 * depan - halaman laporan bisa memakan jutaan baris di memori ( beberapa
 * method lama memanggil ->get() tanpa pagination). Laravel `date` validation
 * hanya memeriksa FORMAT, bukan lebar rentangnya.
 *
 * Aturan:
 *  - Tanggal tidak valid (format aneh, mis. `2026-99-99`) -> fallback default.
 *  - akhir < awal            -> ditukar.
 *  - Lebar rentang > MAKS    -> akhir digeser = awal + MAKS (bukan error; user
 *    yang mau rentang lebih panjang tetap dapat data, cuma dibatasi 366 hari
 *    per tampilan - perpanjang dengan pagination waktu / pilih bulan lain).
 *  - Akhir di masa depan     -> dipotong ke hari ini (jurnal masa depan tidak
 *    ada isinya, tapi membebani query & membingungkan tampilan).
 */
class RentangTanggal
{
    public const MAKS_HARI = 366;

    /**
     * Ambil rentang [awal, akhir] (string Y-m-d) dari request dengan key
     * bawaan `awal`/`akhir` (atau `tgl_dari`/`tgl_sampai` untuk Laporan).
     *
     * @param  string|null $defaultAwal  Y-m-d
     * @param  string|null $defaultAkhir Y-m-d
     * @return array{0: string, 1: string}
     */
    public static function dariRequest(
        Request $request,
        string $defaultAwal = null,
        string $defaultAkhir = null,
        string $keyAwal = 'awal',
        string $keyAkhir = 'akhir'
    ): array {
        $defaultAwal  = $defaultAwal  ?: now()->startOfMonth()->toDateString();
        $defaultAkhir = $defaultAkhir ?: now()->toDateString();

        $awal  = self::tanggalValid($request->query($keyAwal))  ?? $defaultAwal;
        $akhir = self::tanggalValid($request->query($keyAkhir)) ?? $defaultAkhir;

        // Urutan wajib benar sebelum clamp lebar.
        if ($akhir < $awal) {
            [$awal, $akhir] = [$akhir, $awal];
        }

        // Akhir di masa depan tidak berguna untuk jurnal/laporan.
        if ($akhir > now()->toDateString()) {
            $akhir = now()->toDateString();
            if ($awal > $akhir) {
                $awal = $akhir;
            }
        }

        // Clamp lebar rentang.
        if (Carbon::parse($awal)->diffInDays(Carbon::parse($akhir)) > self::MAKS_HARI) {
            $akhir = Carbon::parse($awal)->addDays(self::MAKS_HARI)->toDateString();
        }

        return [$awal, $akhir];
    }

    /** Y-m-d valid -> string, selain itu -> null (bukan exception). */
    public static function tanggalValid(?string $nilai): ?string
    {
        if ($nilai === null || $nilai === '') {
            return null;
        }

        // createFromFormat MELEMPAR InvalidFormatException untuk trailing
        // data ("2026-01-01' OR '1'='1") - payload injeksi tidak boleh pernah
        // sampai jadi exception apalagi 500. Semua kegagalan parse -> null.
        try {
            $t = Carbon::createFromFormat('Y-m-d', $nilai);
        } catch (\Throwable) {
            return null;
        }
        // createFromFormat juga tidak ketat soal bulan=13 dst di semua
        // versi - cek kebalikan format supaya benar-benar cocok.
        if (! $t || $t->format('Y-m-d') !== $nilai) {
            return null;
        }

        return $nilai;
    }
}

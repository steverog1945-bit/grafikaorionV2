<?php

namespace App\Services;

use App\Models\AppSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Satu-satunya tempat yang menentukan SESI KAS AKTIF.
 *
 * Aturan: kas kasir TETAP OPEN sampai di-closing - bukan otomatis standby
 * tiap pergantian tanggal. Sebelumnya semua modul mencari sesi dengan syarat
 * tgl_kas = hari ini, sehingga sesi yang lupa di-closing "hilang" saat lewat
 * tengah malam (status jadi Standby padahal masih Open, uangnya tak pernah
 * direkonsiliasi) dan pembayaran setelah tengah malam tidak ikut terhitung
 * saat closing. Sekarang:
 * - sesi aktif = sesi berstatus open, tanpa melihat tanggal (eksklusif: hanya
 *   satu sesi open pada satu waktu),
 * - rentang waktu sesi = sejak dibuka (created_at) sampai ditutup/sekarang,
 *   dipakai untuk merekap pembayaran & mutasi,
 * - sesi yang open lebih dari 24 jam ditandai "basi" (peringatan),
 * - hari Minggu libur memblokir TRANSAKSI baru, tetapi closing tetap boleh.
 */
class KasSesiService
{
    public const BATAS_JAM = 24;

    /** Sesi kas yang sedang open (siapa pun pemiliknya), atau null. */
    public static function aktif(): ?object
    {
        return DB::table('kas_harian')->where('status_lock', 'open')->orderByDesc('id_kas')->first();
    }

    /** Waktu sesi dimulai (created_at; untuk data lama tanpa created_at = awal tanggal kas). */
    public static function mulai(object $kas): Carbon
    {
        return $kas->created_at ? Carbon::parse($kas->created_at) : Carbon::parse($kas->tgl_kas)->startOfDay();
    }

    /** Batas akhir rentang rekap: waktu tutup bila sudah closing, kalau belum = sekarang. */
    public static function akhir(object $kas): Carbon
    {
        return !empty($kas->waktu_tutup) ? Carbon::parse($kas->waktu_tutup) : now();
    }

    public static function umurJam(object $kas): int
    {
        return (int) self::mulai($kas)->diffInHours(now());
    }

    public static function basi(?object $kas): bool
    {
        return $kas !== null && self::umurJam($kas) >= self::BATAS_JAM;
    }

    /** Alasan transaksi kas diblokir (mis. libur Minggu), atau null bila boleh. */
    public static function alasanTerblokir(): ?string
    {
        if (AppSetting::current()->libur_kas_minggu && now()->isSunday()) {
            return 'Hari Minggu kas libur - transaksi tidak bisa dicatat. Closing kas tetap bisa dilakukan; ubah aturan ini di Pengaturan Aplikasi bila toko buka hari Minggu.';
        }

        return null;
    }

    /** Sesi aktif yang boleh dipakai bertransaksi sekarang (null bila tidak ada atau diblokir libur). */
    public static function aktifUntukTransaksi(): ?object
    {
        return self::alasanTerblokir() ? null : self::aktif();
    }
}

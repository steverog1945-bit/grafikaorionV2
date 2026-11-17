<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Divisi = mesin di master (Aset Mesin). Menambah mesin baru otomatis memunculkan divisi baru
 * di SPK & Produksi - tanpa mengubah kode. Semua mesin tampil (walau belum ada order), lengkap
 * dengan jumlah pekerjaan aktif; produk tanpa mesin ditampung di divisi "Tanpa Divisi" (id 0).
 */
class DivisiService
{
    /**
     * @param  array<int>|\Illuminate\Support\Collection|null  $mesinDiizinkan  Batasan mesin per karyawan (null = semua)
     * @param  array<int,string>  $statusAktif  Status SPK yang dihitung sebagai "pekerjaan aktif"
     */
    public static function daftar($mesinDiizinkan, array $statusAktif)
    {
        $jumlah = DB::table('transaksi_detail as d')
            ->join('transaksi as t', 't.id_transaksi', '=', 'd.id_transaksi')
            ->join('produk as p', 'p.id_produk', '=', 'd.id_produk')
            ->whereIn('d.status_spk', $statusAktif)
            // Badge tab harus berarti "pekerjaan yang BENAR-BENAR ada di
            // antrean SPK/Produksi" - sama dengan tabel di kedua halaman:
            // status_nota Aktif + status_order Diterima Kasir. Sebelumnya
            // Antre dari transaksi yang belum dibuka kasir pun ikut dihitung,
            // sehingga badge menampilkan angka yang tidak pernah bisa muncul
            // di tabel manapun.
            ->where('t.status_nota', 'Aktif')
            ->where('t.status_order', 'Diterima Kasir')
            ->groupBy('p.id_mesin')->selectRaw('p.id_mesin, COUNT(*) c')->pluck('c', 'id_mesin');

        $daftar = DB::table('mesin')->where('tampil_divisi', true)
            ->when($mesinDiizinkan, fn ($q) => $q->whereIn('id_mesin', $mesinDiizinkan))
            ->orderBy('nama_mesin')->get(['id_mesin', 'nama_mesin'])
            ->map(function ($m) use ($jumlah) {
                $m->jumlah = (int) ($jumlah[$m->id_mesin] ?? 0);

                return $m;
            });

        $tanpa = (int) ($jumlah[null] ?? $jumlah[''] ?? 0);
        if ($tanpa > 0 && !$mesinDiizinkan) {
            $daftar->push((object) ['id_mesin' => 0, 'nama_mesin' => 'Tanpa Divisi', 'jumlah' => $tanpa]);
        }

        return $daftar;
    }

    /**
     * Mesin/divisi yang jadi tanggung jawab karyawan pemilik akun. null = tanpa batasan
     * (Administrator, atau karyawan yang belum ditugaskan ke mesin mana pun).
     */
    public static function mesinDiizinkan($user)
    {
        if (!$user || optional($user->role)->nama_role === 'Administrator' || !$user->karyawan) {
            return null;
        }
        $ids = $user->karyawan->mesin()->pluck('mesin.id_mesin');

        return $ids->isNotEmpty() ? $ids : null;
    }

    /** Terapkan filter divisi (id mesin, atau 0 = tanpa divisi) ke query yang sudah join produk. */
    public static function saring($query, $divisi): void
    {
        if ($divisi === null || $divisi === '') {
            return;
        }
        if ((string) $divisi === '0') {
            $query->whereNull('produk.id_mesin');
        } else {
            $query->where('produk.id_mesin', $divisi);
        }
    }
}

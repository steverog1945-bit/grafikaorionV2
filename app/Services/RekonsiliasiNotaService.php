<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * REKONSILIASI TEKS BEBAS vs FAKTA NOTA (deterministik, tanpa AI).
 *
 * Masalah yang dijawab: nama file berkas / catatan unggahan menyebut
 * pekerjaan tertentu (mis. "laminasi") tapi nota tidak memuat finishing
 * itu - bisa berarti finishing lupa ditagih (bocor pendapatan) atau
 * catatan yang usang/menyesatkan produksi.
 *
 * Arah pemeriksaan SENGAJA satu arah: teks menyebut X tapi X tidak
 * tertagih = temuan. Sebaliknya (nota memuat X tapi nama file tidak
 * menyebut X) BUKAN masalah - orang menyingkat nama file, dan itu tidak
 * bisa dipaksa.
 *
 * Cara kerja: setiap nama di master `finishing` (plus alias tambahan di
 * tabel `finishing_alias`, migrasi 2026-11-14) dinormalisasi lalu dicari
 * di teks bebas (nama_file item, nama asli berkas, catatan berkas) dengan
 * batas kata. Tiga penjaga salah tanda:
 *   1. NEGASI - "tanpa laminasi", "non laminasi", "jangan laminasi" tidak
 *      dianggap menyebut.
 *   2. TERGABUNG - alias "laminasi" di teks item yang sudah tertagih
 *      "Laminasi Glossy" tidak jadi temuan (tak mungkin bedakan glossy vs
 *      doff dari satu kata; konservatif: tidak diberi tanda).
 *   3. SATU KATA MENEMPEL - "nonlaminasi" sebagai satu kata memang tidak
 *      lolos batas kata, jadi otomatis bukan temuan.
 *
 * Service ini HANYA MEMBACA. Hasilnya advisory untuk ditampilkan, tidak
 * pernah memblokir simpan/cetak/menutup transaksi.
 */
class RekonsiliasiNotaService
{
    /** Kamus per permintaan - tidak usah cache berat, daftar finishing kecil. */
    private static ?array $kamusCache = null;

    /**
     * Daftar alias yang bisa dicari di teks, dikelompokkan PER ALIAS:
     * nama resmi finishing selalu ikut, ditambah alias tambahan dari
     * `finishing_alias` (kalau tabelnya sudah dibuat owner). Satu alias
     * boleh menunjuk beberapa kandidat finishing (rancu) - dianggap
     * tercukupi bila salah satu kandidatnya tertagih.
     *
     * @return array<int, array{alias:string, nama_finishing:string, id_finishing:int, id_kandidat:array<int,int>}>
     */
    public static function kamus(): array
    {
        if (self::$kamusCache !== null) {
            return self::$kamusCache;
        }

        // alias (ternormalisasi) => [id_finishing => nama_finishing]
        $byAlias = [];
        $tambah = function (string $alias, int $id, string $nama) use (&$byAlias) {
            if ($alias === '') {
                return;
            }
            $byAlias[$alias] ??= [];
            $byAlias[$alias][$id] = $nama;
        };

        foreach (DB::table('finishing')->get(['id_finishing', 'nama_finishing']) as $f) {
            $tambah(self::normalisasi((string) $f->nama_finishing), (int) $f->id_finishing, (string) $f->nama_finishing);
        }

        if (Schema::hasTable('finishing_alias')) {
            foreach (
                DB::table('finishing_alias as fa')
                    ->join('finishing as f', 'f.id_finishing', '=', 'fa.id_finishing')
                    ->select('fa.alias', 'f.id_finishing', 'f.nama_finishing')
                    ->get() as $a
            ) {
                $tambah(self::normalisasi((string) $a->alias), (int) $a->id_finishing, (string) $a->nama_finishing);
            }
        }

        $kamus = [];
        foreach ($byAlias as $alias => $kandidat) {
            $kamus[] = [
                'alias' => $alias,
                'nama_finishing' => implode(' / ', array_values(array_unique($kandidat))),
                'id_finishing' => array_key_first($kandidat),
                'id_kandidat' => array_map('intval', array_keys($kandidat)),
            ];
        }

        return self::$kamusCache = $kamus;
    }

    /** "Laminasi-Glossy (2)" -> "laminasi glossy 2" - hanya a-z0-9 + spasi. */
    public static function normalisasi(string $teks): string
    {
        $teks = mb_strtolower(trim($teks));
        // /u supaya karakter non-latin (dan emoji) dibuang, bukan jadi pemisah palsu.
        $teks = preg_replace('/[^a-z0-9]+/u', ' ', $teks) ?? '';
        return trim(preg_replace('/\s+/', ' ', $teks) ?? '');
    }

    /**
     * Sebutan finishing di $teks yang TIDAK tercakup oleh $tertagih
     * (daftar id_finishing yang benar-benar ada di nota/item itu).
     * Alias multi-kandidat dianggap tercukupi bila SALAH SATU kandidat
     * tertagih; kalau tidak ada, temuan memuat semua nama kandidatnya.
     *
     * @param array<int, int> $tertagih
     * @param array<int, array{alias:string, nama_finishing:string, id_finishing:int, id_kandidat:array<int,int>}>|null $kamus
     * @return array<int, array{id_finishing:int, nama_finishing:string, alias:string, kutipan:string}>
     */
    public static function temuan(string $teks, array $tertagih, ?array $kamus = null): array
    {
        $norm = self::normalisasi($teks);
        if ($norm === '') {
            return [];
        }

        $kamus = $kamus ?? self::kamus();
        if ($kamus === []) {
            return [];
        }
        $tertagih = array_map('intval', $tertagih);

        // Nama resmi finishing yang tertagih, untuk penjaga TERGABUNG.
        $namaTertagih = [];
        foreach ($kamus as $k) {
            foreach ($k['id_kandidat'] as $id) {
                if (in_array($id, $tertagih, true)) {
                    $namaTertagih[] = $k['alias'];
                    break;
                }
            }
        }

        $hasil = [];
        $sudah = [];
        foreach ($kamus as $k) {
            $tertagihSebagian = array_intersect($k['id_kandidat'], $tertagih);
            if ($tertagihSebagian !== [] || isset($sudah[$k['alias']])) {
                continue;
            }
            if (!preg_match('/(?<![a-z0-9])' . preg_quote($k['alias'], '/') . '(?![a-z0-9])/', $norm, $m, PREG_OFFSET_CAPTURE)) {
                continue;
            }

            // PENJAGA 1 - negasi: kata batal dalam 12 karakter sebelum sebutan.
            $awal = $m[0][1];
            $sebelum = substr($norm, max(0, $awal - 12), min(12, $awal));
            if (preg_match('/\b(tanpa|non|no|jangan|tak)\s*$/u', $sebelum)) {
                continue;
            }

            // PENJAGA 2 - tergabung: alias adalah bagian kata dari finishing
            // yang sudah tertagih (mis. "laminasi" di dalam "laminasi glossy").
            $tertutup = false;
            foreach ($namaTertagih as $nt) {
                if (preg_match('/(?<![a-z0-9])' . preg_quote($k['alias'], '/') . '(?![a-z0-9])/', $nt)) {
                    $tertutup = true;
                    break;
                }
            }
            if ($tertutup) {
                continue;
            }

            $sudah[$k['alias']] = true;
            $hasil[] = $k + ['kutipan' => mb_substr($norm, max(0, $awal - 25), 50 + strlen($k['alias']))];
        }

        // PENJAGA 3 - subsumsi: teks yang menyebut versi SPESIFIK tidak perlu
        // diberi tanda dua kali untuk alias GENERIK yang juga kebetulan match
        // ("banner laminasi glossy" tidak boleh menandai finishing generik
        // "laminasi" - spesifiknya sudah meliputi itu). Alias A dibuang bila
        // A adalah bagian kata dari alias lain yang JUGA match di teks ini.
        return array_values(array_filter($hasil, function ($h) use ($hasil) {
            foreach ($hasil as $lain) {
                if ($lain['alias'] !== $h['alias']
                    && strlen($lain['alias']) > strlen($h['alias'])
                    && preg_match('/(?<![a-z0-9])' . preg_quote($h['alias'], '/') . '(?![a-z0-9])/', $lain['alias'])) {
                    return false;
                }
            }
            return true;
        }));
    }

    /**
     * Finishing yang tertagih per id_detail item. Sumber: tabel multi-baris
     * `transaksi_detail_finishing` (satu item boleh beberapa finishing);
     * kolom lama `transaksi_detail.id_finishing` ikut dibaca sebagai
     * fallback untuk baris yang belum kebawa.
     *
     * @return array<int|string, array<int, int>> [id_detail => [id_finishing, ...]]
     */
    public static function detailTertagih(int $idTransaksi): array
    {
        $perDetail = [];
        $idDetails = DB::table('transaksi_detail')->where('id_transaksi', $idTransaksi)->pluck('id_detail');

        if ($idDetails->isEmpty()) {
            return [];
        }

        if (Schema::hasTable('transaksi_detail_finishing')) {
            foreach (
                DB::table('transaksi_detail_finishing')
                    ->whereIn('id_detail', $idDetails)
                    ->get(['id_detail', 'id_finishing']) as $r
            ) {
                $perDetail[(int) $r->id_detail][] = (int) $r->id_finishing;
            }
        }

        foreach (DB::table('transaksi_detail')->whereIn('id_detail', $idDetails)->whereNotNull('id_finishing')->get(['id_detail', 'id_finishing']) as $d) {
            $perDetail[(int) $d->id_detail][] = (int) $d->id_finishing;
        }

        foreach ($perDetail as $i => $ids) {
            $perDetail[$i] = array_values(array_unique($ids));
        }

        return $perDetail;
    }

    /**
     * Semua temuan rekonsiliasi satu nota: nama_file per item diperiksa
     * terhadap finishing item itu sendiri; catatan & nama asli berkas
     * diperiksa terhadap finishing item terkait (atau seluruh nota bila
     * berkas tidak mengikat item tertentu).
     *
     * @return array<int, array{id_finishing:int, nama_finishing:string, alias:string, kutipan:string, sumber:string}>
     */
    public static function perNota(int $idTransaksi): array
    {
        $detail = DB::table('transaksi_detail')
            ->where('id_transaksi', $idTransaksi)
            ->get(['id_detail', 'nama_file', 'catatan_produksi']);

        if ($detail->isEmpty()) {
            return [];
        }

        $tertagihPerItem = self::detailTertagih($idTransaksi);
        $semuaTertagih = array_values(array_unique(array_merge(...(array_values($tertagihPerItem) ?: [[]]))));
        $kamus = self::kamus();
        $temuan = [];

        foreach ($detail as $d) {
            $tertagihItem = $tertagihPerItem[(int) $d->id_detail] ?? [];
            foreach (self::temuan((string) $d->nama_file, $tertagihItem, $kamus) as $t) {
                $temuan[] = $t + ['sumber' => 'Nama file item "' . $d->nama_file . '"'];
            }
            foreach (self::temuan((string) ($d->catatan_produksi ?? ''), $tertagihItem, $kamus) as $t) {
                $temuan[] = $t + ['sumber' => 'Catatan produksi item "' . $d->nama_file . '"'];
            }
        }

        foreach (DB::table('order_berkas')->where('id_transaksi', $idTransaksi)->get(['id_detail', 'catatan', 'nama_file_asli']) as $b) {
            $tertagih = $b->id_detail ? ($tertagihPerItem[(int) $b->id_detail] ?? []) : $semuaTertagih;
            // Nama asli berkas + catatan sama-sama teks bebas, dua-duanya diperiksa.
            foreach ([['nama', $b->nama_file_asli], ['catatan', (string) $b->catatan]] as [$label, $teks]) {
                foreach (self::temuan($teks, $tertagih, $kamus) as $t) {
                    $temuan[] = $t + ['sumber' => ucfirst($label) . ' berkas "' . $b->nama_file_asli . '"'];
                }
            }
        }

        return $temuan;
    }

    /**
     * Cek CEPAT satu unggahan baru (nama asli + catatan) sebelum halaman
     * reload - supaya operator langsung diberi tahu, bukan harus menemukan
     * panel di halaman berkas. Tidak memblokir: berkas tetap tersimpan.
     *
     * @return array<int, array{id_finishing:int, nama_finishing:string, alias:string, kutipan:string, sumber:string}>
     */
    public static function cekUnggahan(int $idTransaksi, ?int $idDetail, string $namaFileAsli, string $catatan): array
    {
        $tertagihPerItem = self::detailTertagih($idTransaksi);
        $tertagih = $idDetail !== null
            ? ($tertagihPerItem[$idDetail] ?? [])
            : array_values(array_unique(array_merge(...(array_values($tertagihPerItem) ?: [[]]))));
        $kamus = self::kamus();

        $temuan = [];
        foreach ([['Nama file', $namaFileAsli], ['Catatan', $catatan]] as [$label, $teks]) {
            foreach (self::temuan($teks, $tertagih, $kamus) as $t) {
                $temuan[] = $t + ['sumber' => $label . ' berkas yang baru diunggah'];
            }
        }
        return $temuan;
    }
}

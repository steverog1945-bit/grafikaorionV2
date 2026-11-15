<?php

namespace App\Support;

/**
 * Helper murni (tanpa side-effect) untuk hitungan minimum luas outdoor.
 *
 * Aturan (keputusan bisnis final A, penyajian HYBRID T4-A keputusan owner):
 *  - Minimum berlaku PER KELOMPOK (kelompok_minimum), bukan per item dan
 *    bukan per bahan.
 *  - Total aktual kelompok = jumlah luas aktual semua item di kelompok itu.
 *  - Jika total aktual < min_luas_total: selisih = min - total, digulung ke
 *    subtotal baris item TERBESAR kelompok itu (bukan lagi baris terpisah).
 *  - Jika total >= min: tidak ada selisih (0).
 *  - Pembulatan konsisten 4 desimal; jangan bandingkan float mentah.
 */
class MinimumLuas
{
    public static function selisih(float $totalAktual, float $minLuasTotal): float
    {
        $diff = round($minLuasTotal - $totalAktual, 4);
        return $diff > 0 ? $diff : 0.0;
    }

    public static function totalCukup(float $totalAktual, float $minLuasTotal): bool
    {
        return round($totalAktual, 4) >= round($minLuasTotal, 4);
    }

    public static function kelompokLabel(?string $kelompok, int $idProduk): string
    {
        $k = trim((string) $kelompok);
        return $k !== '' ? $k : (string) $idProduk;
    }

    /**
     * Kunci kompatibilitas kelompok minimum (spec owner 2026-11-15 PHASE B):
     * item hanya boleh DIJUMLAHKAN luasnya bila basis harganya sama.
     * Kunci = label kelompok + konfigurasi minimum + tarif acuan per m².
     * Item dengan tarif acuan / minimum berbeda jatuh ke kunci berbeda ->
     * terakumulasi terpisah (masing-masing memenuhi minimumnya sendiri),
     * sesuai aturan "jangan menjumlahkan item yang basis harga, metode
     * kalkulasi, satuan, atau konfigurasi minimum order-nya berbeda".
     * Tarif acuan diambil pada qty acuan = max(min_luas_total, 1) m² agar
     * tidak ikut naik-turun mengikuti tier luas tiap baris.
     */
    public static function kunciKelompok(string $label, float $minLuas, float $tarifAcuan): string
    {
        return $label . '|' . number_format($minLuas, 4, '.', '') . '|' . number_format($tarifAcuan, 2, '.', '');
    }

    /**
     * Rencana penagihan minimum luas per kelompok (FUNGSI MURNI - dipakai
     * server checkout dan unit test, supaya matematikanya hanya ada satu).
     *
     * @param array $grup [kunci => ['min' => float, 'items' => [['area' => float], ...], 'idxTerbesar' => ?int]]
     * @return array [kunci => ['total_aktual', 'minimum', 'selisih', 'idx_terbesar']]
     *         Selisih > 0 = kelompok belum memenuhi minimum (ditagih minimum);
     *         selisih 0 = pakai luas aktual.
     */
    /**
     * Fragmen catatan produksi penanda surcharge minimum (SATU sumber teks -
     * dipakai PenjualanCheckoutService saat menulis baris carrier, dan
     * hapusFragmen()/selisihDariCatatan() saat rekonsiliasi baris lama).
     */
    public static function fragmen(string $label, float $selisih): string
    {
        return 'Termasuk Minimum Order ' . $label . ': selisih '
            . rtrim(rtrim(number_format($selisih, 2, '.', ''), '0'), '.') . ' m²';
    }

    /** Pola fragmen minimum di catatan produksi (label boleh memuat ':'). */
    public static function polaFragmen(): string
    {
        return '/Termasuk Minimum Order .*?: selisih [0-9][0-9.,]* m²/u';
    }

    /** Buang SEMUA fragmen minimum dari catatan produksi (sisanya = catatan manusia). */
    public static function hapusFragmen(string $catatan): string
    {
        $bersih = preg_replace(self::polaFragmen(), '', $catatan) ?? $catatan;
        return trim(preg_replace('/\s{2,}/u', ' ', $bersih) ?? $bersih);
    }

    /** Selisih m² dari fragmen minimum TERAKHIR di catatan, atau null bila tak ada. */
    public static function selisihDariCatatan(string $catatan): ?float
    {
        if (!preg_match_all(self::polaFragmen(), $catatan, $m) || empty($m[0])) {
            return null;
        }
        $terakhir = end($m[0]);
        if (!preg_match('/selisih ([0-9][0-9.,]*) m²/u', $terakhir, $n)) {
            return null;
        }
        $angka = str_replace(',', '', $n[1]);
        $nilai = (float) $angka;
        return $nilai > 0 ? $nilai : null;
    }

    public static function rencanaKelompok(array $grup): array
    {
        $hasil = [];
        foreach ($grup as $kunci => $g) {
            $total = 0.0;
            foreach ($g['items'] as $it) {
                $total += (float) $it['area'];
            }
            $total = round($total, 4);
            $min = round((float) ($g['min'] ?? 0), 4);
            $hasil[$kunci] = [
                'total_aktual' => $total,
                'minimum' => $min,
                'selisih' => self::selisih($total, $min),
                'idx_terbesar' => $g['idxTerbesar'] ?? null,
            ];
        }
        return $hasil;
    }
}

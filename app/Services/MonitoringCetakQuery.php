<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Antrean SPK untuk halaman Monitoring Cetak. Membaca tabel ERP langsung
 * (modul ini hidup DI DALAM ERP, bukan koneksi terpisah).
 *
 * ETA sengaja tidak diambil dari klik operator (median ~4 detik, tidak
 * mencerminkan waktu mesin berjalan) - lihat MonitoringCetakEtaService.
 */
class MonitoringCetakQuery
{
    public const STATUS_AKTIF = ['Antre', 'Desain', 'Cetak'];

    /**
     * Antrean hari ini (Asia/Jakarta), nota diterima kasir saja - sama
     * seperti modul SPK. Filter status_nota/status_order menjaga item
     * batal/hilang dari antrean.
     */
    public function activeQueue(?string $tglDari = null, ?string $tglSampai = null, array $statusSpk = self::STATUS_AKTIF): Collection
    {
        $dari = $tglDari ?: now()->startOfDay()->toDateTimeString();
        $sampai = $tglSampai ?: now()->copy()->addDay()->startOfDay()->toDateTimeString();

        return DB::table('transaksi_detail as d')
            ->join('transaksi as t', 't.id_transaksi', '=', 'd.id_transaksi')
            ->join('konsumen as k', 'k.id_konsumen', '=', 't.id_konsumen')
            ->join('produk as p', 'p.id_produk', '=', 'd.id_produk')
            ->leftJoin('mesin as m', 'm.id_mesin', '=', 'p.id_mesin')
            ->where('t.status_nota', 'Aktif')
            ->where('t.status_order', 'Diterima Kasir')
            ->where('t.tgl_order', '>=', $dari)
            ->where('t.tgl_order', '<', $sampai)
            ->whereIn('d.status_spk', $statusSpk)
            ->orderByDesc('d.status_urgent')
            ->orderBy('d.id_detail')
            ->select([
                'd.id_detail',
                'd.id_produk',
                'd.nama_file',
                'd.p_ukuran',
                'd.l_ukuran',
                'd.qty',
                'd.qty_cetak',
                'd.status_spk',
                'd.status_urgent',
                'd.catatan_produksi',
                't.nomor_nota',
                't.tgl_order',
                't.status_pembayaran',
                'k.nama_konsumen',
                'p.id_mesin',
                'p.nama_produk',
                'm.nama_mesin',
            ])
            ->get();
    }

    public function statusCounts(?string $tglDari = null, ?string $tglSampai = null): array
    {
        $dari = $tglDari ?: now()->startOfDay()->toDateTimeString();
        $sampai = $tglSampai ?: now()->copy()->addDay()->startOfDay()->toDateTimeString();

        $rows = DB::table('transaksi_detail as d')
            ->join('transaksi as t', 't.id_transaksi', '=', 'd.id_transaksi')
            ->where('t.status_nota', 'Aktif')
            ->where('t.status_order', 'Diterima Kasir')
            ->where('t.tgl_order', '>=', $dari)
            ->where('t.tgl_order', '<', $sampai)
            ->selectRaw('d.status_spk, COUNT(*) AS n')
            ->groupBy('d.status_spk')
            ->pluck('n', 'status_spk');

        return [
            'antre' => (int) ($rows['Antre'] ?? 0),
            'desain' => (int) ($rows['Desain'] ?? 0),
            'cetak' => (int) ($rows['Cetak'] ?? 0),
            'selesai' => (int) ($rows['Selesai'] ?? 0),
        ];
    }

    /**
     * Label sisa detik -> bahasa manusia. Dipakai untuk kolom ETA.
     */
    public static function sisaLabel(?int $detik): ?string
    {
        if ($detik === null) {
            return null;
        }

        $telat = $detik < 0;
        $detik = abs($detik);

        $jam = intdiv($detik, 3600);
        $menit = intdiv($detik % 3600, 60);

        $teks = $jam > 0
            ? $jam . ' jam ' . $menit . ' menit'
            : ($menit > 0 ? $menit . ' menit' : 'kurang dari 1 menit');

        return $telat ? 'terlambat ' . $teks : 'sisa ' . $teks;
    }
}

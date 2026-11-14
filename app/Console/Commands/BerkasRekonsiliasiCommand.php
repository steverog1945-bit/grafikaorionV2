<?php

namespace App\Console\Commands;

use App\Services\RekonsiliasiNotaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Pindai semua nota dalam rentang tanggal: sebutan finishing di teks bebas
 * (nama file berkas, catatan unggahan) yang TIDAK tertagih di nota.
 * HANYA MEMBACA - tidak mengubah data apa pun.
 *
 *   php artisan berkas:rekonsiliasi                     # 30 hari terakhir
 *   php artisan berkas:rekonsiliasi --dari=2026-11-01 --sampai=2026-11-14
 *   php artisan berkas:rekonsiliasi --json
 *
 * Keluaran utama untuk owner: daftar nota berpotensi KEBOCORAN TAGIHAN
 * (pekerjaan nyata yang lupa dimasukkan ke nota). Kode keluar 1 bila ada
 * temuan, supaya bisa dipanggil dari penjadwal/skrip.
 */
class BerkasRekonsiliasiCommand extends Command
{
    protected $signature = 'berkas:rekonsiliasi
        {--dari=     : Tanggal mulai rentang nota (Y-m-d, bawaan 30 hari terakhir)}
        {--sampai=   : Tanggal akhir rentang nota (Y-m-d, bawaan hari ini)}
        {--json      : Keluaran JSON}';

    protected $description = 'Cari sebutan finishing di berkas/catatan yang tidak tertagih di nota (rekonsiliasi teks vs nota)';

    public function handle(): int
    {
        $dari = $this->option('dari') ?: now()->subDays(30)->format('Y-m-d');
        $sampai = $this->option('sampai') ?: now()->format('Y-m-d');

        // Rentang yang salah arah sekalian dijaga - empty set membingungkan operator.
        if ($dari > $sampai) {
            [$dari, $sampai] = [$sampai, $dari];
        }

        $idNota = DB::table('transaksi')
            ->where('created_at', '>=', $dari . ' 00:00:00')
            ->where('created_at', '<', date('Y-m-d', strtotime($sampai . ' +1 day')) . ' 00:00:00')
            ->orderBy('id_transaksi')
            ->pluck('id_transaksi');

        $hasil = [];
        $totalTemuan = 0;
        foreach ($idNota as $id) {
            $temuan = RekonsiliasiNotaService::perNota((int) $id);
            if ($temuan !== []) {
                $hasil[] = ['id_transaksi' => (int) $id, 'temuan' => $temuan];
                $totalTemuan += count($temuan);
            }
        }

        if ($this->option('json')) {
            $this->line(json_encode([
                'rentang' => [$dari, $sampai],
                'nota_diperiksa' => $idNota->count(),
                'nota_ber_temuan' => count($hasil),
                'total_temuan' => $totalTemuan,
                'hasil' => $hasil,
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        } else {
            $this->info('Nota diperiksa: ' . $idNota->count() . " ({$dari} s/d {$sampai})");

            if ($totalTemuan === 0) {
                $this->info('Tidak ada temuan - semua sebutan finishing di berkas tertagih di nota.');
                return self::SUCCESS;
            }

            $this->warn("{$totalTemuan} temuan di " . count($hasil) . ' nota:');
            $tabel = [];
            foreach ($hasil as $h) {
                foreach ($h['temuan'] as $t) {
                    $tabel[] = [$h['id_transaksi'], $t['nama_finishing'], $t['sumber'], $t['kutipan']];
                }
            }
            $this->table(['Nota', 'Finishing tidak tertagih', 'Sumber', 'Kutipan teks'], $tabel);
        }

        return self::FAILURE; // 1 = ada temuan (untuk skrip pemantau)
    }
}

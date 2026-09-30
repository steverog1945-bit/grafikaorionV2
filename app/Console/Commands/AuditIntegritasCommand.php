<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Operasionalisasi pemeriksaan yang dilakukan manual saat audit Phase 6/7
 * (inventory & akuntansi) supaya bisa dijalankan berkala (mis. sebelum
 * closing bulanan, atau cron mingguan) - bukan cuma sekali pas audit.
 * Mendeteksi DINI kalau salah satu skenario yang jadi temuan awal
 * (jurnal timpang, akun orphan, saldo di arah yang salah, stok tidak
 * sinkron dengan kartu_stok) mulai terulang di data sungguhan.
 *
 * Exit code 1 kalau ada temuan (cocok dipakai di CI/cron untuk kirim
 * notifikasi kalau gagal), exit code 0 kalau semua bersih.
 */
class AuditIntegritasCommand extends Command
{
    protected $signature = 'audit:integritas {--json : Output JSON, bukan tabel}';
    protected $description = 'Cek integritas akuntansi & stok (jurnal balance, saldo akun, sinkronisasi kartu_stok)';

    public function handle(): int
    {
        $adaTemuan = false;
        $hasil = [];

        // 1. Jurnal balance per no_referensi
        $tidakBalance = DB::table('jurnal_umum')
            ->select('no_referensi', DB::raw('SUM(debet) as total_debet'), DB::raw('SUM(kredit) as total_kredit'))
            ->groupBy('no_referensi')
            ->get()
            ->filter(fn ($r) => abs((float) $r->total_debet - (float) $r->total_kredit) > 0.01);

        if ($tidakBalance->isNotEmpty()) {
            $adaTemuan = true;
            foreach ($tidakBalance as $r) {
                $hasil[] = ['jurnal_tidak_balance', $r->no_referensi, "debet={$r->total_debet} kredit={$r->total_kredit}"];
            }
        }

        // 2. kode_akun orphan (tidak ada di bag_akun)
        $orphanAkun = DB::table('jurnal_umum as j')
            ->leftJoin('bag_akun as a', 'j.kode_akun', '=', 'a.kode_akun')
            ->whereNull('a.kode_akun')
            ->distinct()
            ->pluck('j.kode_akun');

        foreach ($orphanAkun as $kode) {
            $adaTemuan = true;
            $hasil[] = ['kode_akun_orphan', $kode, 'kode_akun di jurnal_umum tidak ada di bag_akun'];
        }

        // 3. Saldo akun di arah yang salah (Aset/Beban normal Debet tapi
        // negatif, Kewajiban/Modal/Pendapatan normal Kredit tapi negatif)
        $saldoPerAkun = DB::table('jurnal_umum as j')
            ->join('bag_akun as a', 'j.kode_akun', '=', 'a.kode_akun')
            ->select('a.kode_akun', 'a.nama_akun', 'a.saldo_normal',
                DB::raw('SUM(j.debet) as total_debet'), DB::raw('SUM(j.kredit) as total_kredit'))
            ->groupBy('a.kode_akun', 'a.nama_akun', 'a.saldo_normal')
            ->get();

        foreach ($saldoPerAkun as $s) {
            $saldo = $s->saldo_normal === 'Debet'
                ? ((float) $s->total_debet - (float) $s->total_kredit)
                : ((float) $s->total_kredit - (float) $s->total_debet);
            if ($saldo < -0.01) {
                $adaTemuan = true;
                $hasil[] = ['saldo_arah_salah', "{$s->kode_akun} {$s->nama_akun}", 'saldo=' . round($saldo, 2) . " (normal {$s->saldo_normal})"];
            }
        }

        // 4. Stok bahan_baku vs entri kartu_stok terakhir
        $bahans = DB::table('bahan_baku')->get(['id_bahan', 'nama_bahan', 'stok']);
        foreach ($bahans as $b) {
            $lastKartu = DB::table('kartu_stok')->where('id_bahan', $b->id_bahan)->orderByDesc('id')->first();
            if (!$lastKartu) {
                if ((float) $b->stok != 0) {
                    $adaTemuan = true;
                    $hasil[] = ['stok_tanpa_kartu_stok', $b->nama_bahan, "stok={$b->stok} tapi 0 entri kartu_stok"];
                }
                continue;
            }
            $diff = round((float) $lastKartu->stok_sesudah - (float) $b->stok, 4);
            if (abs($diff) > 0.0001) {
                $adaTemuan = true;
                $hasil[] = ['stok_tidak_sinkron', $b->nama_bahan, "bahan_baku.stok={$b->stok} vs kartu_stok terakhir={$lastKartu->stok_sesudah}"];
            }
        }

        // 5. Uang yang diterima tidak boleh melebihi tagihan (kelebihan seharusnya kembalian, bukan pembayaran).
        $lebihNota = DB::table('transaksi as t')->where('t.status_nota', '!=', 'Dibatalkan')
            ->joinSub(DB::table('pembayaran_riwayat')->selectRaw('id_transaksi, SUM(nominal) s')->groupBy('id_transaksi'), 'b', 'b.id_transaksi', '=', 't.id_transaksi')
            ->whereRaw('b.s - t.total_bayar > 0.01')->get(['t.nomor_nota', 't.total_bayar', 'b.s']);
        foreach ($lebihNota as $r) {
            $adaTemuan = true;
            $hasil[] = ['bayar_melebihi_tagihan', $r->nomor_nota, "total={$r->total_bayar} dibayar={$r->s}"];
        }
        if (DB::getSchemaBuilder()->hasTable('jersey_orders')) {
            $lebihJersey = DB::table('jersey_orders as o')
                ->joinSub(DB::table('jersey_order_payments')->whereNull('deleted_at')->selectRaw('jersey_order_id, SUM(jumlah) s')->groupBy('jersey_order_id'), 'b', 'b.jersey_order_id', '=', 'o.id')
                ->whereRaw('b.s - o.total > 0.01')->get(['o.order_number', 'o.total', 'b.s']);
            foreach ($lebihJersey as $r) {
                $adaTemuan = true;
                $hasil[] = ['bayar_melebihi_tagihan', $r->order_number, "total={$r->total} dibayar={$r->s} (php artisan jersey:koreksi-pembukuan)"];
            }

            // 6. Pendapatan order jersey yang ditandai terjurnal harus benar-benar ada di jurnal (akun 4300).
            $pendapatan = DB::table('jurnal_umum')->where('kode_akun', '4300')->selectRaw('no_referensi, SUM(kredit) - SUM(debet) s')->groupBy('no_referensi')->pluck('s', 'no_referensi');
            foreach (DB::table('jersey_orders')->where('total_terjurnal', '>', 0)->get(['order_number', 'total_terjurnal']) as $o) {
                if (abs((float) $o->total_terjurnal - (float) ($pendapatan[$o->order_number] ?? 0)) > 0.01) {
                    $adaTemuan = true;
                    $hasil[] = ['pendapatan_jersey_tidak_terjurnal', $o->order_number, "total_terjurnal={$o->total_terjurnal} jurnal=" . (float) ($pendapatan[$o->order_number] ?? 0)];
                }
            }
        }

        if ($this->option('json')) {
            $this->line(json_encode(['ok' => !$adaTemuan, 'temuan' => $hasil], JSON_PRETTY_PRINT));
        } elseif ($adaTemuan) {
            $this->error('Ditemukan ' . count($hasil) . ' masalah integritas:');
            $this->table(['Jenis', 'Target', 'Detail'], $hasil);
        } else {
            $this->info('Semua pemeriksaan integritas LULUS - tidak ada temuan.');
        }

        return $adaTemuan ? self::FAILURE : self::SUCCESS;
    }
}

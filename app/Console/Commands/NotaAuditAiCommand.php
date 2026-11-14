<?php

namespace App\Console\Commands;

use App\Services\AiNotaAuditService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Jalankan audit AI untuk satu nota atau rentang tanggal. HASIL DISIMPAN
 * di tabel `nota_audit_ai` dan tampil advisory di halaman Berkas Order -
 * command ini TIDAK mengubah nota/berkas/transaksi apa pun.
 *
 *   php artisan nota:audit-ai 236
 *   php artisan nota:audit-ai --dari=2026-11-10 --sampai=2026-11-14
 *
 * Bila AI_NOTA_DRIVER belum diisi (.env), command berhenti dengan
 * petunjuk - bukan error diam-diam.
 */
class NotaAuditAiCommand extends Command
{
    protected $signature = 'nota:audit-ai
        {id_transaksi? : Audit satu nota}
        {--dari=        : Tanggal mulai rentang nota (Y-m-d)}
        {--sampai=      : Tanggal akhir rentang nota (Y-m-d)}';

    protected $description = 'Audit AI teks bebas berkas vs fakta nota (butuh AI_NOTA_DRIVER di .env)';

    public function handle(): int
    {
        $konfig = AiNotaAuditService::konfig();
        if (!$konfig['driver']) {
            $this->warn('Driver AI belum diaktifkan. Isi di .env lalu coba lagi:');
            $this->line('  AI_NOTA_DRIVER=ollama');
            $this->line('  AI_NOTA_URL=' . $konfig['url'] . '   (bawaan)');
            $this->line('  AI_NOTA_MODEL=' . $konfig['model'] . '   (pastikan sudah di-pull: ollama pull ' . $konfig['model'] . ')');
            return self::SUCCESS;
        }

        if ($id = $this->argument('id_transaksi')) {
            $ids = [(int) $id];
        } else {
            $dari = $this->option('dari') ?: now()->subDays(7)->format('Y-m-d');
            $sampai = $this->option('sampai') ?: now()->format('Y-m-d');
            if ($dari > $sampai) {
                [$dari, $sampai] = [$sampai, $dari];
            }
            $ids = DB::table('transaksi')
                ->where('created_at', '>=', $dari . ' 00:00:00')
                ->where('created_at', '<', date('Y-m-d', strtotime($sampai . ' +1 day')) . ' 00:00:00')
                ->orderBy('id_transaksi')
                ->pluck('id_transaksi')
                ->map(fn ($v) => (int) $v)
                ->all();
            $this->info("Audit {$konfig['model']} untuk " . count($ids) . " nota ({$dari} s/d {$sampai})...");
        }

        $total = 0;
        $gagal = 0;
        foreach ($ids as $i => $id) {
            $hasil = AiNotaAuditService::audit($id);

            if ($hasil === null) {
                $gagal++;
                $this->line("  nota #{$id}: dilewati (tidak ada item / driver gagal / jawaban tak valid)");
                continue;
            }

            $n = count($hasil['temuan']);
            $total += $n;
            $this->line("  nota #{$id}: {$n} temuan" . ($n ? '' : ' (bersih)'));
        }

        $this->info("Selesai - total {$total} temuan disimpan di nota_audit_ai" . ($gagal ? ", {$gagal} nota dilewati" : '') . '.');
        return self::SUCCESS;
    }
}

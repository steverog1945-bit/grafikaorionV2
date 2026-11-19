<?php

namespace App\Console\Commands;

use App\Models\MonitoringAgent;
use App\Models\MonitoringMachine;
use Illuminate\Console\Command;

/**
 * Buat/rotasi token agent spooler. Token plaintext HANYA ditampilkan
 * sekali di sini; yang tersimpan adalah SHA-256-nya.
 *
 *   php artisan monitoring-cetak:create-agent 1 --nama="PC-PRINTER-01"
 *   php artisan monitoring-cetak:create-agent 1 --nama="PC-PRINTER-01" --rotate
 */
class MonitoringCetakCreateAgentCommand extends Command
{
    protected $signature = 'monitoring-cetak:create-agent
        {mesin : id monitoring_mesins (lihat MonitoringMachine::all / kolom id) }
        {--nama= : Nama agent (mis. PC-PRINTER-01) }
        {--rotate : Buat token baru untuk agent yang sudah ada (kolom nama sama)}';

    protected $description = 'Buat atau rotasi token agent spooler Monitoring Cetak';

    public function handle(): int
    {
        $mesin = MonitoringMachine::find((int) $this->argument('mesin'));
        if (! $mesin) {
            $this->error('Monitoring mesin tidak ditemukan. Jalankan: php artisan monitoring-cetak:seed-machines');

            return self::FAILURE;
        }

        $nama = trim((string) $this->option('nama'));
        if ($nama === '') {
            $this->error('Opsi --nama wajib diisi.');

            return self::FAILURE;
        }

        $plain = bin2hex(random_bytes(32)); // 64 karakter hex
        $hash = hash('sha256', $plain);

        $agent = MonitoringAgent::where('monitoring_mesin_id', $mesin->id)
            ->where('nama', $nama)
            ->first();

        if ($agent && ! $this->option('rotate')) {
            $agent->forceFill(['token_hash' => $hash, 'aktif' => true])->save();
            $this->comment('Token agent yang ada diganti (aktif).');
        } elseif ($agent && $this->option('rotate')) {
            $agent->forceFill(['token_hash' => $hash, 'aktif' => true])->save();
            $this->comment('Token agent dirotasi.');
        } else {
            MonitoringAgent::create([
                'monitoring_mesin_id' => $mesin->id,
                'nama' => $nama,
                'token_hash' => $hash,
                'aktif' => true,
            ]);
            $this->comment('Agent baru dibuat.');
        }

        $this->line('');
        $this->line("Agent   : {$nama} (mesin: {$mesin->nama_mesin})");
        $this->line('TOKEN   : ' . $plain);
        $this->line('Simpan token sekarang - tidak ditampilkan lagi setelah ini.');
        $this->line('Set di PC produksi (OrionMonitorAgent.ps1): X-Agent-Token = nilai di atas.');
        $this->line('Di server produksi: MONITORING_CETAK_AGENT_ENABLED=true (di config env) - lihat docs/reviews/MONITORING_CETAK_ERP.md');

        return self::SUCCESS;
    }
}

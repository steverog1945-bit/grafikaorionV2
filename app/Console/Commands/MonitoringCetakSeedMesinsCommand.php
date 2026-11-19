<?php

namespace App\Console\Commands;

use App\Models\MonitoringMachine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Seed mesin yang dipantau dari tabel ERP `mesin` ke `monitoring_mesins`.
 * Idempoten: id_mesin sudah terdaftar dilewati; yang hilang ditambahkan
 * sebagai aktif. Tidak mengubah tabel ERP mana pun.
 *
 *   php artisan monitoring-cetak:seed-machines
 */
class MonitoringCetakSeedMesinsCommand extends Command
{
    protected $signature = 'monitoring-cetak:seed-machines';

    protected $description = 'Daftarkan mesin ERP (tabel mesin) sebagai mesin yang dipantau Monitoring Cetak';

    public function handle(): int
    {
        $mesin = DB::table('mesin')->orderBy('id_mesin')->get(['id_mesin', 'nama_mesin', 'tipe_mesin']);

        $ditambah = 0;
        $dilewati = 0;

        foreach ($mesin as $m) {
            $ada = MonitoringMachine::where('id_mesin', $m->id_mesin)->exists();
            if ($ada) {
                $dilewati++;
                continue;
            }
            MonitoringMachine::create([
                'id_mesin' => $m->id_mesin,
                'nama_mesin' => $m->nama_mesin,
                'tipe_mesin' => $m->tipe_mesin,
                'software' => 'other',
                'aktif' => true,
            ]);
            $this->line('+ ' . $m->id_mesin . ' ' . $m->nama_mesin);
            $ditambah++;
        }

        $this->info("Selesai: {$ditambah} ditambahkan, {$dilewati} sudah ada.");
        if ($ditambah > 0) {
            $this->comment('Edit software/pc_ip/printer_name + buat agent: php artisan monitoring-cetak:create-agent <monitoring_mesin_id>');
        }

        return self::SUCCESS;
    }
}

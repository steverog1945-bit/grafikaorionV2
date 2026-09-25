<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Menambah ukuran ANAK (nama unik, tidak bentrok dengan ukuran dewasa) beserta:
 *  - selisih harga persen dari harga tier (kelompok anak lebih murah),
 *  - estimasi pemakaian bahan lengkap (komponen, lengan, kerah) yang diturunkan dari ukuran dewasa S.
 * Idempotent: baris yang sudah ada tidak ditimpa, jadi aman dijalankan ulang.
 */
class JerseySizeAnakCommand extends Command
{
    protected $signature = 'jersey:size-anak {--simulasi : Jalankan lalu batalkan (rollback)} {--tanpa-backup : Lewati backup otomatis}';
    protected $description = 'Tambah ukuran anak (Anak 4-14) + estimasi bahan + selisih harga persen';

    /** nama => [faktor pemakaian bahan terhadap dewasa S, selisih harga %] */
    private const SIZE_ANAK = [
        'Anak 4' => [0.50, -35],
        'Anak 6' => [0.58, -30],
        'Anak 8' => [0.66, -25],
        'Anak 10' => [0.75, -20],
        'Anak 12' => [0.84, -15],
        'Anak 14' => [0.93, -10],
    ];

    public function handle(): int
    {
        if (!$this->option('simulasi') && !$this->option('tanpa-backup')) {
            try {
                $this->info('Backup: ' . BackupService::run('manual')->file_path);
            } catch (\Throwable $e) {
                $this->warn('Backup gagal (' . $e->getMessage() . ') - lanjut tanpa backup.');
            }
        }

        $dasar = DB::table('jersey_sizes')->where('name', 'S')->first();
        if (!$dasar) {
            $this->error('Ukuran dewasa "S" tidak ditemukan - dipakai sebagai dasar estimasi.');

            return self::FAILURE;
        }

        $baru = 0;
        $estimasi = 0;

        DB::beginTransaction();
        try {
            // Ukuran dewasa digeser ke belakang (urutan tampil): anak dulu, dewasa setelahnya.
            DB::table('jersey_sizes')->where('kelompok', 'dewasa')->where('sort_order', '<', 10)->increment('sort_order', 10);

            $urut = 1;
            foreach (self::SIZE_ANAK as $nama => [$faktor, $persen]) {
                $ada = DB::table('jersey_sizes')->where('name', $nama)->first();
                if ($ada) {
                    $idSize = $ada->id;
                } else {
                    $idSize = DB::table('jersey_sizes')->insertGetId([
                        'name' => $nama, 'sort_order' => $urut, 'is_active' => 1, 'price_surcharge' => 0,
                        'kelompok' => 'anak', 'price_percent' => $persen, 'created_at' => now(), 'updated_at' => now(),
                    ]);
                    $baru++;
                }
                $urut++;

                foreach ([
                    ['jersey_size_component_usage', 'jersey_component_id'],
                    ['jersey_size_sleeve_usage', 'jersey_sleeve_type_id'],
                    ['jersey_size_collar_usage', 'jersey_collar_type_id'],
                ] as [$tabel, $kolom]) {
                    foreach (DB::table($tabel)->where('jersey_size_id', $dasar->id)->get() as $u) {
                        $sudah = DB::table($tabel)->where('jersey_size_id', $idSize)->where($kolom, $u->$kolom)->exists();
                        if ($sudah) {
                            continue;
                        }
                        DB::table($tabel)->insert([
                            'jersey_size_id' => $idSize, $kolom => $u->$kolom,
                            'usage_meter' => round((float) $u->usage_meter * $faktor, 3),
                            'usage_weight_gram' => round((float) $u->usage_weight_gram * $faktor, 2),
                            'created_at' => now(), 'updated_at' => now(),
                        ]);
                        $estimasi++;
                    }
                }
            }

            if ($this->option('simulasi')) {
                DB::rollBack();
                $this->warn('SIMULASI - dibatalkan.');
            } else {
                DB::commit();
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Ukuran anak baru: {$baru}; baris estimasi bahan ditambah: {$estimasi}.");

        return self::SUCCESS;
    }
}

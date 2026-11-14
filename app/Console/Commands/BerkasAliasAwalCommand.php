<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Isi alias awal untuk RekonsiliasiNotaService - IDEMPOTEN: alias yang
 * sudah ada tidak disentuh, aman dijalankan berulang. MENULIS DB (tabel
 * finishing_alias saja), jadi dijalankan manual oleh owner:
 *
 *   php artisan berkas:alias-awal
 *
 * Alias yang dibaca operator nyata: singkatan, tanpa awalan Hot/Dingin,
 * ejaan inggris, kata generik. Alias rancu (cocok ke beberapa finishing,
 * mis. "laminasi" = Hot ATAU Dingin) sengaja diisi dua baris - service
 * menganggapnya cukup bila SALAH SATU kandidatnya tertagih.
 * Alias selain daftar ini bisa ditambah manual kapan saja.
 */
class BerkasAliasAwalCommand extends Command
{
    protected $signature = 'berkas:alias-awal';
    protected $description = 'Isi tabel finishing_alias dengan alias bawaan yang umum dipakai operator (idempoten)';

    /** [alias, nama_finishing yang dituju] */
    private const DAFTAR = [
        // Laminasi (Hot & Dingin) - alias generik rancu dua-duanya
        ['laminasi', 'Hot Laminasi Doff/Glossy'],
        ['laminasi', 'Laminasi Dingin Doff/Glossy'],
        ['laminating', 'Hot Laminasi Doff/Glossy'],
        ['laminating', 'Laminasi Dingin Doff/Glossy'],
        ['hot laminasi', 'Hot Laminasi Doff/Glossy'],
        ['laminasi hot', 'Hot Laminasi Doff/Glossy'],
        ['lam hot', 'Hot Laminasi Doff/Glossy'],
        ['laminasi dingin', 'Laminasi Dingin Doff/Glossy'],
        ['lam dingin', 'Laminasi Dingin Doff/Glossy'],
        // Master lain - nama operator sehari-hari
        ['kiss cut', 'Kisscut'],
        ['kisscut a3', 'Kisscut A3+'],
        ['potong', 'Potong Manual'],
        ['potong manual a3', 'Potong Manual A3'],
        ['mata ayam', 'Mata Ayam'],
        ['press', 'Press Hidrolik'],
        ['jahit', 'Jahit Keliling'],
        ['sulam', 'Jahit Keliling'],
        ['tali', 'Pemasangan Tali'],
        ['pasang tali', 'Pemasangan Tali'],
        ['oneway', 'Jasa Pasang oneway'],
        ['spiral', 'SPIRAL A4/F4'],
        ['spiral a5', 'SPIRAL A5'],
        ['spiral a3', 'SPIRAL A3'],
        ['numerator', 'Numerator NCR'],
        ['ncr', 'Numerator NCR'],
        ['styrofoam', 'Styrofoam Mockup'],
        ['keping', 'Keping Roll'],
        ['porporasi', 'Porporasi'],
        ['porseling', 'Porporasi'],
        ['perforasi', 'Porporasi'],
    ];

    public function handle(): int
    {
        $idByNama = DB::table('finishing')->pluck('id_finishing', 'nama_finishing');
        $ada = DB::table('finishing_alias')->pluck('alias')->map(fn ($a) => mb_strtolower(trim($a)))->all();

        $insert = [];
        foreach (self::DAFTAR as [$alias, $namaFinishing]) {
            $id = $idByNama[$namaFinishing] ?? null;
            if ($id === null) {
                $this->line("  lewati (master tidak ada): {$alias} -> {$namaFinishing}");
                continue;
            }
            if (in_array(mb_strtolower($alias), $ada, true)) {
                continue;
            }
            $insert[] = ['id_finishing' => (int) $id, 'alias' => $alias, 'created_at' => now(), 'updated_at' => now()];
        }

        if ($insert === []) {
            $this->info('Semua alias bawaan sudah ada - tidak ada yang diubah.');
            return self::SUCCESS;
        }

        DB::table('finishing_alias')->insert($insert);
        $this->info(count($insert) . ' alias ditambahkan ke finishing_alias.');
        return self::SUCCESS;
    }
}

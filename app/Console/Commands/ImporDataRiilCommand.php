<?php

namespace App\Console\Commands;

use App\Services\AuditLogger;
use App\Services\BackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Impor data riil dari dokumen "Bahan Baku.docx" (harga beli bahan, satuan,
 * daftar harga produk per golongan) + pelengkapan oleh pemilik/engineer:
 * harga pasaran untuk produk yang belum bertarif dan rumus BOM-nya.
 *
 * - Aman diulang: bahan dicocokkan lewat kode_bahan, produk lewat nama
 *   (produk yang sudah ada TIDAK diubah), jasa lewat nama.
 * - Backup otomatis sebelum menulis; seluruh perubahan dalam satu transaksi.
 * - --simulasi: jalankan semuanya lalu batalkan (rollback) untuk melihat ringkasan.
 * - Perubahan harga rata-rata bahan dicatat di Log Aktivitas.
 *
 * Kode "Asumsi" pada keterangan = angka yang bukan dari dokumen (isi roll,
 * hasil bagi lembar, harga pasaran) dan wajib diverifikasi pemilik.
 */
class ImporDataRiilCommand extends Command
{
    protected $signature = 'data:impor-riil {--simulasi : Jalankan lalu batalkan (rollback)} {--yes : Tanpa konfirmasi} {--tanpa-backup : Lewati backup otomatis}';

    protected $description = 'Impor bahan baku, produk, BOM, tier harga, dan jasa dari dokumen Bahan Baku (data riil)';

    private array $hitung = ['bahan_update' => 0, 'bahan_baru' => 0, 'harga_bahan_diubah' => 0, 'produk_baru' => 0, 'produk_dilewati' => 0, 'bom' => 0, 'tier' => 0, 'jasa' => 0, 'perbaikan' => 0];
    private array $catatan = [];
    private array $idBahanKode = [];

    public function handle(): int
    {
        if (!$this->option('yes') && !$this->confirm('Impor data riil ke database "' . DB::connection()->getDatabaseName() . '"' . ($this->option('simulasi') ? ' (SIMULASI, akan dibatalkan)' : '') . '?', true)) {
            return self::FAILURE;
        }

        if (!$this->option('simulasi') && !$this->option('tanpa-backup')) {
            try {
                $this->info('Backup: ' . BackupService::run('manual')->file_path);
            } catch (\Throwable $e) {
                $this->error('Backup gagal (' . $e->getMessage() . '). Dibatalkan.');

                return self::FAILURE;
            }
        }

        DB::beginTransaction();
        try {
            $this->satuanBaru();
            $this->bahanKonica();
            $this->bahanCentury();
            $this->bahanSublime();
            $this->bahanEpsonS80670();
            $this->bahanFlora();
            $this->bahanDtf();
            $this->bahanLainnya();
            $this->perbaikanBom();
            $this->jasa();
            $this->produkFoto();
            $this->produkA3Lain();
            $this->produkIndoor();
            $this->produkOutdoor();
            $this->lengkapiProdukAda();

            if ($this->option('simulasi')) {
                DB::rollBack();
                $this->warn('SIMULASI: semua perubahan dibatalkan.');
            } else {
                AuditLogger::log('created', 'impor_data_riil', null, null, $this->hitung, 'Impor data riil dari dokumen Bahan Baku');
                DB::commit();
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('GAGAL, semua dibatalkan: ' . $e->getMessage() . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')');

            return self::FAILURE;
        }

        $this->table(['Ringkasan', 'Jumlah'], collect($this->hitung)->map(fn ($v, $k) => [$k, $v])->values()->all());
        foreach ($this->catatan as $c) {
            $this->line(' - ' . $c);
        }

        return self::SUCCESS;
    }

    // ------------------------------------------------------------------ helpers

    private function idMesin(string $nama): int
    {
        return (int) (DB::table('mesin')->where('nama_mesin', $nama)->value('id_mesin') ?? throw new \RuntimeException("Mesin '{$nama}' tidak ada"));
    }

    private function idKategori(string $nama): int
    {
        return (int) (DB::table('kategori')->whereRaw('LOWER(nama_kategori) = ?', [strtolower($nama)])->value('id_kategori') ?? throw new \RuntimeException("Kategori '{$nama}' tidak ada"));
    }

    private function idSatuan(string $nama): int
    {
        $nama = strtolower($nama);
        $id = DB::table('satuan')->whereRaw('LOWER(nama_satuan) = ?', [$nama])->value('id_satuan');
        if (!$id) {
            $id = DB::table('satuan')->insertGetId(['nama_satuan' => $nama, 'nilai_konversi' => 1]);
        }

        return (int) $id;
    }

    private function satuanBaru(): void
    {
        foreach (['botol', 'pack', 'dus'] as $s) {
            $this->idSatuan($s);
        }
    }

    /**
     * Upsert bahan berdasarkan kode. Nilai null = tidak diubah (pada update).
     * Kunci: kode, nama, mesin, kategori, stok (satuan stok), hitung (satuan hitung),
     * konversi, lebar, panjang, harga, min (stok minimal).
     */
    private function bahan(array $d): int
    {
        $ada = DB::table('bahan_baku')->where('kode_bahan', $d['kode'])->first();
        $set = [];

        foreach ([['nama', 'nama_bahan'], ['konversi', 'nilai_konversi'], ['lebar', 'lebar_roll'], ['panjang', 'panjang_roll']] as [$k, $kolom]) {
            if (array_key_exists($k, $d)) {
                $set[$kolom] = $d[$k];
            }
        }
        if (isset($d['stok'])) {
            $set['id_satuan_stok'] = $this->idSatuan($d['stok']);
            $set['satuan_stok'] = strtolower($d['stok']);
        }
        if (isset($d['hitung'])) {
            $set['id_satuan_hitung'] = $this->idSatuan($d['hitung']);
        }

        if ($ada) {
            if (isset($d['harga'])) {
                $lama = (float) $ada->harga_rata_rata;
                if (abs($lama - (float) $d['harga']) > 0.004) {
                    $set['harga_rata_rata'] = round((float) $d['harga'], 2);
                    AuditLogger::log('updated', 'bahan_baku', (int) $ada->id_bahan, ['harga_rata_rata' => $lama], ['harga_rata_rata' => $set['harga_rata_rata']], 'Impor data riil (dokumen Bahan Baku)');
                    $this->hitung['harga_bahan_diubah']++;
                }
            }
            if ($set) {
                DB::table('bahan_baku')->where('id_bahan', $ada->id_bahan)->update($set + ['updated_at' => now()]);
                $this->hitung['bahan_update']++;
            }
            $id = (int) $ada->id_bahan;
        } else {
            $id = (int) DB::table('bahan_baku')->insertGetId($set + [
                'kode_bahan' => $d['kode'], 'nama_bahan' => $d['nama'], 'id_mesin' => $this->idMesin($d['mesin']), 'id_kategori' => $this->idKategori($d['kategori']),
                'id_satuan_stok' => $set['id_satuan_stok'], 'satuan_stok' => $set['satuan_stok'], 'id_satuan_hitung' => $set['id_satuan_hitung'] ?? $set['id_satuan_stok'],
                'nilai_konversi' => $d['konversi'] ?? 1, 'stok' => 0, 'stok_minimal' => $d['min'] ?? 1,
                'harga_rata_rata' => round((float) ($d['harga'] ?? 0), 2), 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now(),
            ]);
            $this->hitung['bahan_baru']++;
        }

        return $this->idBahanKode[$d['kode']] = $id;
    }

    private function id(string $kode): int
    {
        return $this->idBahanKode[$kode] ??= (int) (DB::table('bahan_baku')->where('kode_bahan', $kode)->value('id_bahan') ?? throw new \RuntimeException("Bahan '{$kode}' tidak ada"));
    }

    /**
     * Buat produk baru (dilewati bila nama sudah ada). $bom = [[kode bahan, rumus], ...].
     * $tier = [[min, max|null, harga], ...] untuk semua golongan; $memberFoto = tier khusus golongan Member Foto.
     */
    private function produk(string $nama, string $mesin, string $metode, array $bom, array $tier, ?array $memberFoto = null, string $ket = '-'): void
    {
        if (DB::table('produk')->where('nama_produk', $nama)->exists()) {
            $this->hitung['produk_dilewati']++;

            return;
        }

        $idProduk = (int) DB::table('produk')->insertGetId([
            'nama_produk' => $nama, 'barcode' => null, 'id_mesin' => $this->idMesin($mesin), 'metode_hitung' => $metode,
            'min_order' => 0, 'min_panjang' => 0, 'min_lebar' => 0, 'keterangan' => $ket, 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->hitung['produk_baru']++;

        foreach ($bom as [$kode, $rumus]) {
            DB::table('bom_detail')->insert(['id_produk' => $idProduk, 'id_bahan' => $this->id($kode), 'cara_hitung_pemakaian' => $rumus, 'created_at' => now(), 'updated_at' => now()]);
            $this->hitung['bom']++;
        }

        $idMemberFoto = (int) DB::table('golongan_konsumen')->where('nama_golongan', 'Member Foto')->value('id_golongan');
        foreach (DB::table('golongan_konsumen')->orderBy('id_golongan')->pluck('id_golongan') as $idGol) {
            $daftar = ($memberFoto !== null && (int) $idGol === $idMemberFoto) ? $memberFoto : $tier;
            foreach ($daftar as [$min, $max, $harga]) {
                DB::table('setting_harga')->insert([
                    'id_produk' => $idProduk, 'id_golongan' => $idGol, 'range_min' => $min, 'range_max' => $max, 'harga_jual' => $harga,
                    'id_satuan' => null, 'qty_berlaku' => 1, 'id_satuan_berlaku' => null, 'created_at' => now(), 'updated_at' => now(),
                ]);
                $this->hitung['tier']++;
            }
        }
    }

    /** Tier per lembar/pcs (qty bulat) dari daftar [qty mulai => harga]. */
    private function tierQty(array $daftar): array
    {
        $out = [];
        $awal = array_keys($daftar);
        foreach ($awal as $i => $min) {
            $out[] = [$min, isset($awal[$i + 1]) ? $awal[$i + 1] - 1 : null, $daftar[$min]];
        }

        return $out;
    }

    private function tierM2(float $harga): array
    {
        return [[0.01, null, $harga]];
    }

    // ------------------------------------------------------------------ BAHAN

    private function bahanKonica(): void
    {
        $k = ['mesin' => 'KONICA MINOLTA', 'kategori' => 'A3'];
        $lembar500 = ['stok' => 'rim', 'hitung' => 'lembar', 'konversi' => 500];
        $this->bahan(['kode' => 'AC210', 'harga' => 370000]);
        $this->bahan(['kode' => 'AC260', 'harga' => 460000]);
        $this->bahan(['kode' => 'AP150', 'harga' => 280000] + $lembar500);
        $this->bahan(['kode' => 'AP120', 'harga' => 240000] + $lembar500);
        $this->bahan(['kode' => 'BTX', 'harga' => 530000]);
        $this->bahan(['kode' => 'A3VS', 'harga' => 520000] + $lembar500);
        $this->bahan(['kode' => 'A3TR', 'harga' => 515000] + $lembar500);
        $this->bahan(['kode' => 'A3LN', 'harga' => 750000] + $lembar500);
        $this->bahan(['kode' => 'A3HL', 'harga' => 650000] + $lembar500);
        $this->bahan(['kode' => 'HVS75', 'harga' => 98000, 'konversi' => 500]);
        // Laminasi A3: isi roll asumsi 200 lembar A3 per roll.
        $this->bahan(['kode' => 'LM-A3', 'harga' => 125000, 'stok' => 'roll', 'hitung' => 'lembar', 'konversi' => 200]);
        $this->bahan(['kode' => 'LM-A3G', 'harga' => 125000, 'stok' => 'roll', 'hitung' => 'lembar', 'konversi' => 200]);
        $this->bahan(['kode' => 'BKN', 'harga' => 2000]);
        $this->bahan(['kode' => 'TNR-KONICA', 'nama' => 'TONER KONICA MINOLTA (per botol)', 'harga' => 1300000, 'stok' => 'botol', 'hitung' => 'botol', 'konversi' => 1]);
        // Spiral: asumsi 1 roll = 400 jilid.
        $this->bahan(['kode' => 'SPR', 'harga' => 1350000, 'stok' => 'roll', 'hitung' => 'pcs', 'konversi' => 400]);
        // PVC: asumsi 1 dus = 100 kartu.
        $this->bahan(['kode' => 'PVC', 'harga' => 820000, 'stok' => 'box', 'hitung' => 'pcs', 'konversi' => 100]);
        $this->catatan[] = 'ASUMSI isi: laminasi A3 200 lembar/roll, spiral 400 jilid/roll, PVC ID Card 100 pcs/dus, A3 rim = 500 lembar.';
    }

    private function bahanCentury(): void
    {
        $this->bahan(['kode' => 'TNT-CENTURY', 'harga' => 260000]);
        $this->bahan(['kode' => 'TC', 'nama' => 'TC Textile Import 120cm', 'harga' => 1750000]);
        $this->bahan(['kode' => 'TC110', 'nama' => 'TC Textile Import 110cm', 'mesin' => 'CENTURY STAR', 'kategori' => 'CENTURY STAR', 'stok' => 'roll', 'hitung' => 'm2', 'konversi' => 248.6, 'lebar' => 1.1, 'panjang' => 226, 'harga' => 1750000, 'min' => 1]);
    }

    private function bahanSublime(): void
    {
        // Sublime Paper (SB) dipilih sebagai bahan tunggal; PAPER SUBLIME (duplikat) dinonaktifkan.
        $this->bahan(['kode' => 'SB', 'harga' => 650000, 'stok' => 'roll', 'hitung' => 'm2', 'konversi' => 160, 'lebar' => 1.6, 'panjang' => 100]);
        $pps = DB::table('bahan_baku')->where('kode_bahan', 'PPS')->first();
        if ($pps && $pps->status === 'aktif') {
            DB::table('bahan_baku')->where('id_bahan', $pps->id_bahan)->update(['status' => 'nonaktif', 'updated_at' => now()]);
            AuditLogger::log('updated', 'bahan_baku', (int) $pps->id_bahan, ['status' => 'aktif'], ['status' => 'nonaktif'], 'Duplikat SUBLIME PAPER; BOM dialihkan ke SB');
            $this->hitung['perbaikan']++;
        }
        $this->bahan(['kode' => 'LNY', 'harga' => 150000]);
        $this->catatan[] = 'PAPER SUBLIME (PPS) dinonaktifkan (duplikat); BOM produk Sublime Paper dialihkan ke SUBLIME PAPER (SB), isi roll 1,6m x 100m = 160 m2.';
    }

    private function bahanEpsonS80670(): void
    {
        $e = ['mesin' => 'EPSON S80670', 'kategori' => 'INDOOR', 'stok' => 'roll', 'hitung' => 'm2'];
        $this->bahan(['kode' => 'ALB', 'harga' => 750000]);
        // Camel: 3 ukuran terpisah, harga sama.
        $this->bahan(['kode' => 'ST-CML', 'nama' => 'STIKER CAMEL 155cm', 'harga' => 1290000]);
        $this->bahan(['kode' => 'ST-CML106', 'nama' => 'STIKER CAMEL 106cm', 'konversi' => 53, 'lebar' => 1.06, 'panjang' => 50, 'harga' => 1290000] + $e);
        $this->bahan(['kode' => 'ST-CML125', 'nama' => 'STIKER CAMEL 125cm', 'konversi' => 62.5, 'lebar' => 1.25, 'panjang' => 50, 'harga' => 1290000] + $e);
        // Canvas 90cm sesuai dokumen.
        $this->bahan(['kode' => 'CNV', 'nama' => 'CANVAS 90cm', 'harga' => 1800000, 'konversi' => 45, 'lebar' => 0.9, 'panjang' => 50]);
        // Exilo: 106 & 153 (dokumen) + 125 (dipakai produk Exilo 125cm), harga sama.
        $this->bahan(['kode' => 'ST-EXI', 'nama' => 'STIKER EXILO 153cm', 'harga' => 1200000]);
        $this->bahan(['kode' => 'ST-EXI106', 'nama' => 'STIKER EXILO 106cm', 'konversi' => 53, 'lebar' => 1.06, 'panjang' => 50, 'harga' => 1200000] + $e);
        $this->bahan(['kode' => 'ST-EXI125', 'nama' => 'STIKER EXILO 125cm', 'konversi' => 62.5, 'lebar' => 1.25, 'panjang' => 50, 'harga' => 1200000] + $e);
        $this->bahan(['kode' => 'PHO', 'harga' => 780000]);
        $this->bahan(['kode' => 'LMD-DOFF', 'nama' => 'LAMINASI DINGIN DOFF 152cm', 'konversi' => 76, 'lebar' => 1.52, 'panjang' => 50, 'harga' => 650000] + $e);
        $this->bahan(['kode' => 'LMD-GLOSSY', 'nama' => 'LAMINASI DINGIN GLOSSY 152cm', 'konversi' => 76, 'lebar' => 1.52, 'panjang' => 50, 'harga' => 650000] + $e);
        $this->bahan(['kode' => 'ST-INF', 'harga' => 1950000]);
        $this->bahan(['kode' => 'LUSTER90', 'nama' => 'LUSTER 90cm', 'konversi' => 45, 'lebar' => 0.9, 'panjang' => 50, 'harga' => 1350000] + $e);
        // ASUMSI: Luster 125cm = harga 90cm x (1,25/0,9).
        $this->bahan(['kode' => 'LUSTER125', 'nama' => 'LUSTER 125cm', 'konversi' => 62.5, 'lebar' => 1.25, 'panjang' => 50, 'harga' => 1875000] + $e);
        $this->bahan(['kode' => 'ROLLUP-STAND', 'nama' => 'ROLL UP (STAND)', 'stok' => 'pcs', 'hitung' => 'pcs', 'konversi' => 1, 'harga' => 180000, 'mesin' => 'EPSON S80670', 'kategori' => 'INDOOR']);
        // ASUMSI: lebar Sunblast & Wallpaper (tidak ada di dokumen).
        $this->bahan(['kode' => 'SUNBLAST', 'nama' => 'SUNBLAST', 'konversi' => 63.5, 'lebar' => 1.27, 'panjang' => 50, 'harga' => 1800000] + $e);
        $this->bahan(['kode' => 'WALLPAPER', 'nama' => 'WALLPAPER', 'konversi' => 53, 'lebar' => 1.06, 'panjang' => 50, 'harga' => 950000] + $e);
        // ASUMSI: harga Ritrama Transparan 155cm (STIKER TRANSPARAN) tidak ada di dokumen.
        $this->bahan(['kode' => 'STT', 'nama' => 'STIKER TRANSPARAN 155cm (Ritrama)', 'harga' => 1600000, 'hitung' => 'm2']);
        $this->bahan(['kode' => 'TNT-S80670', 'harga' => 650000]);
        $this->bahan(['kode' => 'CLEANER-EPSON', 'nama' => 'CLEANER EPSON S80670', 'stok' => 'botol', 'hitung' => 'botol', 'konversi' => 1, 'harga' => 450000, 'mesin' => 'EPSON S80670', 'kategori' => 'INDOOR']);
        $this->catatan[] = 'ASUMSI Epson S80670: panjang roll 50 m; lebar Sunblast 1,27 m & Wallpaper 1,06 m; Luster 125cm Rp 1.875.000 (skala dari 90cm); Stiker Transparan 155cm Rp 1.600.000 (harga pasaran, tidak ada di dokumen).';
    }

    private function bahanFlora(): void
    {
        $f = ['mesin' => 'FLORA OUTDOOR', 'kategori' => 'OUTDOOR'];
        $this->bahan(['kode' => 'FLS280-320', 'harga' => 1340000]);
        $this->bahan(['kode' => 'FLS280-220', 'harga' => 1039500]);
        $this->bahan(['kode' => 'FLS280-110', 'harga' => 940000]);
        // ASUMSI: harga 2.6m & 1.6m tidak ada di dokumen -> interpolasi dari harga per roll 1.1m/2.2m/3.2m.
        $this->bahan(['kode' => 'FLS280-260', 'harga' => 1160000]);
        $this->bahan(['kode' => 'FLS280-160', 'harga' => 990000]);
        $this->bahan(['kode' => 'STOV', 'harga' => 970000]);
        $this->bahan(['kode' => 'TNT-FLORA', 'harga' => 780000]);
        $this->bahan(['kode' => 'SOLVENT-FLORA', 'nama' => 'SOLVENT PEMBERSIH FLORA', 'stok' => 'liter', 'hitung' => 'liter', 'konversi' => 1, 'harga' => 650000] + $f);
        // ASUMSI: 1 pack ring mata ayam = 500 pcs.
        $this->bahan(['kode' => 'RING', 'nama' => 'RING MATA AYAM', 'stok' => 'pack', 'hitung' => 'pcs', 'konversi' => 500, 'harga' => 90000] + $f);
        $this->bahan(['kode' => 'RANGKA-XB', 'nama' => 'RANGKA XBANNER', 'stok' => 'pcs', 'hitung' => 'pcs', 'konversi' => 1, 'harga' => 24000] + $f);
        $this->bahan(['kode' => 'LEM-CHINA', 'nama' => 'LEM CHINA', 'stok' => 'pack', 'hitung' => 'pcs', 'konversi' => 1, 'harga' => 320000] + $f);
        $this->catatan[] = 'ASUMSI Flora: harga roll FLS 2.6m Rp 1.160.000 & 1.6m Rp 990.000 (interpolasi, tidak ada di dokumen); ring mata ayam 500 pcs/pack.';
    }

    private function bahanDtf(): void
    {
        $this->bahan(['kode' => 'DTF', 'harga' => 650000]);
        $this->bahan(['kode' => 'TNT-DTF-CMYK', 'harga' => 260000]);
        $this->bahan(['kode' => 'TNT-DTF-WHT', 'harga' => 230000]);
        $this->bahan(['kode' => 'PWD-DTF', 'harga' => 80000]);
    }

    private function bahanLainnya(): void
    {
        $l = ['mesin' => 'LAINNYA', 'kategori' => 'Lain-lain'];
        // ASUMSI: 1 dus amplop = 100 pcs, 1 pack gantungan kunci = 100 pcs.
        $this->bahan(['kode' => 'ACD12', 'harga' => 27000, 'hitung' => 'pcs', 'konversi' => 100]);
        $this->bahan(['kode' => 'ABP', 'harga' => 23000, 'hitung' => 'pcs', 'konversi' => 100]);
        $this->bahan(['kode' => 'AMP104', 'nama' => 'AMPLOP 104', 'stok' => 'dus', 'hitung' => 'pcs', 'konversi' => 100, 'harga' => 19000] + $l);
        $this->bahan(['kode' => 'BUKU-REG', 'nama' => 'BUKU REGISTER', 'stok' => 'pcs', 'hitung' => 'pcs', 'konversi' => 1, 'harga' => 25000] + $l);
        $this->bahan(['kode' => 'GK44', 'nama' => 'GANTUNGAN KUNCI 44mm', 'stok' => 'pack', 'hitung' => 'pcs', 'konversi' => 100, 'harga' => 250000] + $l);
        $this->bahan(['kode' => 'GK54', 'nama' => 'GANTUNGAN KUNCI 54mm', 'stok' => 'pack', 'hitung' => 'pcs', 'konversi' => 100, 'harga' => 280000] + $l);
        $this->bahan(['kode' => 'HVS-A4', 'nama' => 'HVS A4/F4 75gsm', 'stok' => 'rim', 'hitung' => 'lembar', 'konversi' => 500, 'harga' => 48000] + $l);
        // Dokumen: Pin 44mm/55mm = 1.300 / 1.200 tanpa penegasan urutan; ukuran lebih besar dianggap lebih mahal.
        $this->bahan(['kode' => 'PIN44', 'nama' => 'PIN 44mm', 'stok' => 'pcs', 'hitung' => 'pcs', 'konversi' => 1, 'harga' => 1200] + $l);
        $this->bahan(['kode' => 'PIN55', 'nama' => 'PIN 55mm', 'stok' => 'pcs', 'hitung' => 'pcs', 'konversi' => 1, 'harga' => 1300] + $l);
        $this->bahan(['kode' => 'TRIPOD', 'nama' => 'TRIPOD BANNER (RANGKA)', 'stok' => 'pcs', 'hitung' => 'pcs', 'konversi' => 1, 'harga' => 180000] + $l);
        $this->catatan[] = 'ASUMSI Lain-lain: 1 dus amplop = 100 pcs, 1 pack gantungan kunci = 100 pcs; Pin 44mm Rp 1.200 & 55mm Rp 1.300 (urutan di dokumen tidak tegas). Belum diregistrasi (tanpa harga di dokumen): Figura, Karangan Bunga, Keplek, Linen A4, Map, Paperbag, SPPD, XBanner.';
    }

    private function perbaikanBom(): void
    {
        // BOM STIKER CAMEL 155 sebelumnya menunjuk STIKER EXILO (salah bahan).
        $idCamelProduk = DB::table('produk')->where('nama_produk', 'STIKER CAMEL 155')->value('id_produk');
        if ($idCamelProduk) {
            $n = DB::table('bom_detail')->where('id_produk', $idCamelProduk)->where('id_bahan', $this->id('ST-EXI'))->update(['id_bahan' => $this->id('ST-CML'), 'updated_at' => now()]);
            $this->hitung['perbaikan'] += $n;
            $n && $this->catatan[] = 'PERBAIKAN: BOM produk STIKER CAMEL 155 sebelumnya memakai bahan STIKER EXILO, kini STIKER CAMEL 155cm.';
        }
        // BOM Sublime Paper -> SB.
        $idSub = DB::table('produk')->where('nama_produk', 'Sublime Paper')->value('id_produk');
        if ($idSub && DB::table('bahan_baku')->where('kode_bahan', 'PPS')->exists()) {
            $this->hitung['perbaikan'] += DB::table('bom_detail')->where('id_produk', $idSub)->where('id_bahan', $this->id('PPS'))->update(['id_bahan' => $this->id('SB'), 'updated_at' => now()]);
        }
    }

    private function jasa(): void
    {
        foreach ([
            ['Jasa Print', 'm', 35000, 'Print saja (media dari pelanggan). Dokumen: JASA PRINT Rp 35.000/m.'],
            ['Press Hidrolik', 'pcs', 3000, 'Dokumen kelompok DTF: Press Hidrolik Rp 3.000.'],
        ] as [$nama, $satuan, $harga, $ket]) {
            if (!DB::table('jasa')->where('nama_jasa', $nama)->exists()) {
                DB::table('jasa')->insert(['nama_jasa' => $nama, 'id_satuan' => $this->idSatuan($satuan), 'harga_default' => $harga, 'keterangan' => $ket, 'created_at' => now(), 'updated_at' => now()]);
                $this->hitung['jasa']++;
            }
        }
        $this->catatan[] = 'Jasa Pasang Stiker sudah ada (Rp 50.000); PRINT ONLY dimasukkan sebagai Jasa Print Rp 35.000 (bukan produk terpisah).';
    }

    // ------------------------------------------------------------------ PRODUK

    /** Foto R-size di Konica A3+ (Umum & Member Foto dari dokumen), BOM: AC 260 (+ laminasi glossy) dibagi jumlah foto per lembar. */
    private function produkFoto(): void
    {
        // nama, ukuran, foto per lembar A3+ (asumsi), Umum, Umum+Laminasi, Member, Member+Laminasi
        $foto = [
            ['10R', '20.3 x 25.4', 2, 7000, 8500, 4500, 8000],
            ['10Rs', '20 x 30', 2, 8000, 10000, 5000, 9200],
            ['11R', '28 x 35', 1, 12500, 19400, 12500, 19400],
            ['12R', '30 x 40', 1, 15000, 23400, 15000, 23400],
            ['12Rs', '30 x 45', 1, 20000, 24000, 17500, 19000],
            ['2R', '6 x 9', 20, 2000, 2300, 1500, 2300],
            ['3R', '8.9 x 12.7', 9, 1600, 1800, 1200, 1800],
            ['4R', '10.2 x 17.8', 6, 2000, 3000, 1300, 2400],
            ['6R', '15 x 20', 4, 2300, 4400, 2300, 4400],
        ];
        $ket = 'Harga dari dokumen Bahan Baku. BOM: AC 260gr dibagi foto per lembar A3+ (asumsi tata letak).';
        foreach ($foto as [$ukuran, $dim, $ups, $umum, $umumLam, $member, $memberLam]) {
            $this->produk("{$ukuran} ({$dim})", 'KONICA MINOLTA', 'quantity', [['AC260', "Q/{$ups}"]], [[1, null, $umum]], [[1, null, $member]], $ket);
            $this->produk("{$ukuran} Laminasi ({$dim})", 'KONICA MINOLTA', 'quantity', [['AC260', "Q/{$ups}"], ['LM-A3G', "Q/{$ups}"]], [[1, null, $umumLam]], [[1, null, $memberLam]], $ket . ' Laminasi glossy.');
        }
        $this->catatan[] = 'ASUMSI foto per lembar A3+: 2R=20, 3R=9, 4R=6, 6R=4, 10R/10Rs=2, 11R/12R/12Rs=1. Harga 2R (Rp 2.000) lebih tinggi dari 3R (Rp 1.600) sesuai urutan dokumen - mohon cek.';
    }

    /** Produk A3 tanpa harga di dokumen: harga pasaran (pemilik) + rumus BOM (engineer). */
    private function produkA3Lain(): void
    {
        $k = 'KONICA MINOLTA';
        $pasar = 'Harga pasaran awal (asumsi) - sesuaikan. ';
        $t = fn (array $d) => $this->tierQty($d);

        $this->produk('AC 210gsm 2 muka', $k, 'quantity', [['AC210', 'Q']], $t([1 => 8500, 10 => 7500, 50 => 7000, 150 => 6500]), null, $pasar . 'AC 210gram (1 muka) yang sudah ada dipakai sebagai 1 muka.');
        $this->produk('AC 260gsm 2 muka', $k, 'quantity', [['AC260', 'Q']], $t([1 => 9000, 11 => 8000, 51 => 7500, 150 => 7000]), null, $pasar . 'AC 260gram (1 muka) yang sudah ada dipakai sebagai 1 muka.');
        $this->produk('AP 120gsm 1 muka', $k, 'quantity', [['AP120', 'Q']], $t([1 => 4000, 10 => 3500, 50 => 3000, 150 => 2750]), null, $pasar);
        $this->produk('AP 120gsm 2 muka', $k, 'quantity', [['AP120', 'Q']], $t([1 => 6500, 10 => 5500, 50 => 5000, 150 => 4500]), null, $pasar);
        $this->produk('AP 150gsm 1 muka', $k, 'quantity', [['AP150', 'Q']], $t([1 => 4500, 10 => 4000, 50 => 3500, 150 => 3250]), null, $pasar);
        $this->produk('AP 150gsm 2 muka', $k, 'quantity', [['AP150', 'Q']], $t([1 => 7000, 10 => 6000, 50 => 5500, 150 => 5000]), null, $pasar);
        $this->produk('Box Kartu Nama', $k, 'quantity', [['BKN', 'Q']], $t([1 => 5000, 25 => 4500, 100 => 4000]), null, $pasar . 'Dijual sebagai box kosong.');
        // Brosur A4: 1 lembar AP 120 A3+ = 2 brosur A4.
        $this->produk('Brosur A4 1 sisi', $k, 'quantity', [['AP120', 'Q/2']], $t([1 => 3000, 10 => 2500, 50 => 2000, 100 => 1750, 500 => 1500]), null, $pasar . 'BOM: AP 120 dibagi 2 (2 brosur A4 per lembar A3+).');
        $this->produk('Brosur A4 2 sisi', $k, 'quantity', [['AP120', 'Q/2']], $t([1 => 4500, 10 => 4000, 50 => 3500, 100 => 3000, 500 => 2500]), null, $pasar . 'BOM: AP 120 dibagi 2.');
        $this->produk('HVS A3 75gsm 1 muka', $k, 'quantity', [['HVS75', 'Q']], $t([1 => 3500, 10 => 3000, 50 => 2750, 150 => 2500]), null, $pasar);
        $this->produk('HVS A3 75gsm 2 muka', $k, 'quantity', [['HVS75', 'Q']], $t([1 => 5500, 10 => 5000, 50 => 4500, 150 => 4000]), null, $pasar);
        // Kartu nama: 1 box = 100 lembar kartu; 20 kartu per lembar A3+ (asumsi) = 5 lembar AC 260 per box.
        $this->produk('Kartu Nama 1 sisi', $k, 'quantity', [['AC260', 'Q*5'], ['BKN', 'Q']], $t([1 => 25000, 5 => 23000, 10 => 21000]), null, $pasar . 'Per box (100 kartu). BOM: AC 260 x5 lembar (20 kartu per lembar A3+) + 1 Box Kartu Nama.');
        $this->produk('Kartu Nama 2 sisi', $k, 'quantity', [['AC260', 'Q*5'], ['BKN', 'Q']], $t([1 => 35000, 5 => 32000, 10 => 29000]), null, $pasar . 'Per box (100 kartu). BOM sama dengan 1 sisi (sheet yang sama dicetak bolak-balik).');
        foreach (['Doff' => 'LM-A3', 'Glossy' => 'LM-A3G'] as $jenis => $kodeLam) {
            $this->produk("Laminasi {$jenis} 1 muka", $k, 'quantity', [[$kodeLam, 'Q']], $t([1 => 3000, 10 => 2500, 50 => 2000]), null, $pasar);
            $this->produk("Laminasi {$jenis} 2 muka", $k, 'quantity', [[$kodeLam, 'Q*2']], $t([1 => 5000, 10 => 4500, 50 => 4000]), null, $pasar . 'BOM: laminasi x2 (dua sisi).');
        }
        $this->produk('Linen A3+ 1 muka', $k, 'quantity', [['A3LN', 'Q']], $t([1 => 6500, 10 => 6000, 50 => 5500]), null, $pasar);
        $this->produk('Linen A3+ 2 muka', $k, 'quantity', [['A3LN', 'Q']], $t([1 => 9500, 10 => 9000, 50 => 8500]), null, $pasar);
        $this->produk('Paperbag', $k, 'quantity', [['AC210', 'Q'], ['LM-A3G', 'Q']], $t([1 => 17500, 50 => 15000, 100 => 13500, 500 => 12000]), null, $pasar . 'BOM: AC 210 + laminasi glossy per paperbag (tali/lem belum dihitung).');
        $this->produk('PVC ID Card', $k, 'quantity', [['PVC', 'Q']], $t([1 => 20000, 10 => 17500, 50 => 15000, 100 => 13500]), null, $pasar);
        $this->produk('Spiral', $k, 'quantity', [['SPR', 'Q']], $t([1 => 8000, 10 => 7000, 50 => 6000]), null, $pasar . 'Per jilid.');
        $this->produk('Stiker Vinil Susu', $k, 'quantity', [['A3VS', 'Q']], $t([1 => 7500, 10 => 6500, 50 => 6000]), null, $pasar);
        $this->produk('Stiker Vinil Transparan', $k, 'quantity', [['A3TR', 'Q']], $t([1 => 8500, 10 => 7500, 50 => 7000]), null, $pasar);
        $this->produk('Stiker Hologram', $k, 'quantity', [['A3HL', 'Q']], $t([1 => 10000, 10 => 9000, 50 => 8500]), null, $pasar);
        // Amplop & undangan.
        $this->produk('Amplop Custom', $k, 'quantity', [['ACD12', 'Q']], $t([1 => 2500, 100 => 2000, 500 => 1500]), null, $pasar);
        $this->produk('Amplop Linen + Isi Linen', $k, 'quantity', [['A3LN', 'Q/2']], $t([1 => 9000, 50 => 8000, 100 => 7000]), null, $pasar . 'BOM: linen A3+ dibagi 2 per set.');
        $this->produk('Undangan Custom (A5 Linen)', $k, 'quantity', [['A3LN', 'Q/2']], $t([1 => 6000, 100 => 5000, 300 => 4500]), null, $pasar . 'Contoh awal: A5 di kertas linen A3+ (2 per lembar); undangan lain dengan bahan berbeda dibuat terpisah.');
        $this->catatan[] = 'ASUMSI produk A3 tanpa harga: seluruh harga = pasaran awal; kartu nama 100 kartu per box, 20 kartu per lembar A3+ (5 lembar AC 260 per box); brosur A4 2 per lembar A3+.';
    }

    private function produkIndoor(): void
    {
        $e = 'EPSON S80670';
        $doc = 'Harga dari dokumen Bahan Baku. ';
        // Foto besar di Photopaper 90cm; faktor = luas m2 x 1,1 (sisa potong).
        $besar = [
            ['16R', '40 x 50', 40, 50, 70000, 84000], ['16Rs', '40 x 60', 40, 60, 90000, 100000], ['20R', '50 x 60', 50, 60, 100000, 120000],
            ['20Rs', '50 x 75', 50, 75, 135000, 155000], ['24R', '60 x 80', 60, 80, 175000, 190600], ['24Rs', '60 x 90', 60, 90, 185000, 210000],
        ];
        foreach ($besar as [$u, $dim, $p, $l, $harga, $hargaLam]) {
            $f = round($p * $l / 10000 * 1.1, 4);
            $this->produk("{$u} - {$dim}", $e, 'quantity', [['PHO', "Q*{$f}"]], [[1, null, $harga]], null, $doc . 'BOM: Photopaper luas foto +10%.');
            $this->produk("{$u} Laminasi - {$dim}", $e, 'quantity', [['PHO', "Q*{$f}"], ['LMD-GLOSSY', "Q*{$f}"]], [[1, null, $hargaLam]], null, $doc . 'BOM: Photopaper + laminasi dingin glossy.');
        }

        $m2 = '(P*L)/10000';
        foreach ([
            ['Albatros 90cm', 'ALB', 70000], ['Camel 106cm', 'ST-CML106', 70000], ['Camel 126cm', 'ST-CML125', 70000],
            ['Canvas 90cm', 'CNV', 150000], ['Luster 125cm', 'LUSTER125', 150000], ['Photopaper 91cm', 'PHO', 100000],
            ['Ritrama Transparan 155cm', 'STT', 70000], ['Exilo 106', 'ST-EXI106', 50000], ['Exilo 125cm', 'ST-EXI125', 50000],
            ['Wallpaper', 'WALLPAPER', 80000],
        ] as [$nama, $kode, $harga]) {
            $this->produk($nama, $e, 'ukuran', [[$kode, $m2]], $this->tierM2($harga), null, $doc . ($nama === 'Camel 126cm' ? 'Bahan: STIKER CAMEL 125cm (dokumen menulis 125, daftar harga 126).' : ''));
        }
        $this->produk('Transparan Laminasi Putih', $e, 'ukuran', [['STT', $m2], ['LMD-DOFF', $m2]], $this->tierM2(115000), null, $doc . 'Asumsi BOM: stiker transparan + laminasi dingin doff (laminasi putih khusus belum terdaftar).');
        $this->produk('Roll Up Albatros 60x120', $e, 'quantity', [['ROLLUP-STAND', 'Q'], ['ALB', 'Q*0.792']], [[1, null, 180000]], null, $doc . 'BOM: stand + Albatros 0,72 m2 +10%. Harga jual = harga beli stand Rp 180.000 (margin negatif) - mohon cek.');
        $this->produk('Roll Up Albatros 80x200', $e, 'quantity', [['ROLLUP-STAND', 'Q'], ['ALB', 'Q*1.76']], [[1, null, 280000]], null, $doc . 'BOM: stand + Albatros 1,6 m2 +10%.');
        $this->catatan[] = 'Indoor: Exilo 152cm dilewati (produk STIKER EXILO 153 sudah ada); PRINT ONLY dimasukkan sebagai Jasa Print. Roll Up 60x120 dijual Rp 180.000 = harga beli stand Rp 180.000 (margin negatif).';
    }

    private function produkOutdoor(): void
    {
        $o = 'FLORA OUTDOOR';
        $doc = 'Harga dari dokumen Bahan Baku. ';
        $this->produk('FLS280 1.1m', $o, 'ukuran', [['FLS280-110', '(P*L)/10000']], $this->tierM2(20000), null, $doc . 'FLS 280gr plus desain, lebar 1,1m.');
        $this->produk('Oneway Vision', $o, 'ukuran', [['STOV', '(P*L)/10000']], $this->tierM2(75000), null, $doc);
        $this->produk('FLS Polos', $o, 'ukuran', [['FLS280-320', '(P*L)/10000']], $this->tierM2(13000), null, $doc . 'Tanpa cetak; bahan FLS 3,2m.');
        $this->produk('Ring Mata Ayam', $o, 'quantity', [['RING', 'Q']], [[1, null, 700]], null, $doc);
        // XBanner 60x160cm = 0,96 m2 +10%.
        $this->produk('XBanner Set', $o, 'quantity', [['RANGKA-XB', 'Q'], ['FLS280-160', 'Q*1.056']], [[1, null, 75000]], null, $doc . 'BOM: rangka + FLS 1,6m 60x160cm +10%.');
        $this->produk('Tripod Banner Set', $o, 'quantity', [['TRIPOD', 'Q'], ['FLS280-160', 'Q*1.056']], [[1, null, 300000]], null, 'Harga pasaran awal (asumsi) - sesuaikan. BOM: rangka tripod + FLS 1,6m 60x160cm +10%.');
        $this->catatan[] = 'Outdoor: selisih "tanpa desain, file ready" Rp 18.000 vs "plus desain" Rp 20.000 belum dipecah menjadi produk terpisah (bisa dikelola lewat biaya desain).';
    }
    // ------------------------------------------------------------------ PRODUK YANG SUDAH ADA

    /** Isi tier pada produk lama yang belum bertarif dan perbaiki BOM film DTF (1 m2 per lembar -> luas sebenarnya). */
    private function lengkapiProdukAda(): void
    {
        $tanpaTier = [
            'DTF - A3' => [[1, null, 35000]], 'DTF - A4' => [[1, null, 25000]], 'DTF - A5' => [[1, null, 15000]], 'LOGO 10cm' => [[1, null, 10000]],
            // BONTAX tidak ada di dokumen; Reseller sudah Rp 10.000 (qty 1-10), jadi Retail diisi sedikit di atasnya.
            'BONTAX' => [[1, 10, 12000], [11, null, 10500]],
        ];
        foreach ($tanpaTier as $nama => $tiers) {
            $idProduk = DB::table('produk')->where('nama_produk', $nama)->value('id_produk');
            if (!$idProduk) {
                continue;
            }
            $tambah = 0;
            foreach (DB::table('golongan_konsumen')->pluck('id_golongan') as $idGol) {
                if (DB::table('setting_harga')->where('id_produk', $idProduk)->where('id_golongan', $idGol)->exists()) {
                    continue;
                }
                $tambah++;
                foreach ($tiers as [$min, $max, $harga]) {
                    DB::table('setting_harga')->insert([
                        'id_produk' => $idProduk, 'id_golongan' => $idGol, 'range_min' => $min, 'range_max' => $max, 'harga_jual' => $harga,
                        'id_satuan' => null, 'qty_berlaku' => 1, 'id_satuan_berlaku' => null, 'created_at' => now(), 'updated_at' => now(),
                    ]);
                    $this->hitung['tier']++;
                }
            }
            $tambah && $this->catatan[] = "Produk '{$nama}' belum lengkap harga golongan - diisi" . ($nama === 'BONTAX' ? ' (Retail dst. di atas harga Reseller yang sudah ada, asumsi).' : ' dari dokumen (kelompok DTF).');
        }

        // Luas film per lembar (+10% sisa): A3 29,7x42, A4 21x29,7, A5 14,8x21, logo 10x10 cm.
        foreach (['DTF - A3' => 0.137, 'DTF - A4' => 0.0686, 'DTF - A5' => 0.0342, 'LOGO 10cm' => 0.011] as $nama => $faktor) {
            $idProduk = DB::table('produk')->where('nama_produk', $nama)->value('id_produk');
            if ($idProduk) {
                $n = DB::table('bom_detail')->where('id_produk', $idProduk)->where('id_bahan', $this->id('DTF'))->where('cara_hitung_pemakaian', 'Q')
                    ->update(['cara_hitung_pemakaian' => "Q*{$faktor}", 'updated_at' => now()]);
                $this->hitung['perbaikan'] += $n;
            }
        }
        $this->catatan[] = 'PERBAIKAN BOM DTF A3/A4/A5/Logo: sebelumnya 1 m2 Pet Film per lembar (HPP Rp 10.484), kini sesuai luas cetak + 10% sisa.';

        // PVC ID Card (produk baru dari impor sebelumnya): harga awal dinaikkan agar margin wajar.
        $idPvc = DB::table('produk')->where('nama_produk', 'PVC ID Card')->where('keterangan', 'like', 'Harga pasaran awal%')->value('id_produk');
        if ($idPvc && DB::table('setting_harga')->where('id_produk', $idPvc)->where('id_golongan', 1)->orderBy('range_min')->value('harga_jual') == 15000) {
            DB::table('setting_harga')->where('id_produk', $idPvc)->delete();
            foreach (DB::table('golongan_konsumen')->pluck('id_golongan') as $idGol) {
                foreach ($this->tierQty([1 => 20000, 10 => 17500, 50 => 15000, 100 => 13500]) as [$min, $max, $harga]) {
                    DB::table('setting_harga')->insert(['id_produk' => $idPvc, 'id_golongan' => $idGol, 'range_min' => $min, 'range_max' => $max, 'harga_jual' => $harga, 'id_satuan' => null, 'qty_berlaku' => 1, 'id_satuan_berlaku' => null, 'created_at' => now(), 'updated_at' => now()]);
                }
            }
            $this->hitung['perbaikan']++;
        }
    }
}

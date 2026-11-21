<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Services\StokReversalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Bahan baku finishing (mis. Styrofoam) harus benar-benar terpotong saat
 * order dibuat - bukan hanya ditagih jasanya.
 *
 * Sebelum perbaikan 2026-11-21:
 *  - finishing tidak menyebut bahan apa pun, jadi bahan 150 STYROFOAM tidak
 *    pernah punya baris kartu_stok walau sudah dipakai order;
 *  - HPP order ber-styrofoam hanya berisi FRONTLITE (BOM produk);
 *  - qty_pakai finishing metode 'luas' selalu NULL, sehingga luas yang
 *    ditagih tidak bisa direkonstruksi dari nota.
 *
 * CATATAN EKSEKUSI: butuh database testing terpisah (RefreshDatabase);
 * jalankan dengan DB_DATABASE=percetakan_erp_testing.
 */
class FinishingBahanTerpakaiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private int $gol;
    private int $kons;
    private int $mesin;
    private int $kat;
    private int $satuanM2;
    private int $satuanLembar;
    private int $bahanCetak;
    private int $bahanStyro;
    private int $produk;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create([
            'id_role' => Role::factory()->create(['nama_role' => 'Administrator'])->id_role,
        ]);
        DB::table('roles')->where('nama_role', 'Administrator')->update(['boleh_lihat_harga' => true]);
        $this->actingAs($this->admin);

        $this->mesin = DB::table('mesin')->insertGetId(['nama_mesin' => 'M1', 'tipe_mesin' => 'Cetak']);
        $this->kat = DB::table('kategori')->insertGetId(['nama_kategori' => 'K']);
        $this->satuanM2 = DB::table('satuan')->insertGetId(['nama_satuan' => 'm2', 'nilai_konversi' => 1]);
        $this->satuanLembar = DB::table('satuan')->insertGetId(['nama_satuan' => 'lembar', 'nilai_konversi' => 1]);

        // Bahan cetak (FRONTLITE) - dipakai BOM produk, HPP 1.000/m2.
        $this->bahanCetak = DB::table('bahan_baku')->insertGetId([
            'kode_bahan' => 'FLS', 'nama_bahan' => 'Frontlite', 'id_mesin' => $this->mesin, 'id_kategori' => $this->kat,
            'stok' => 100, 'nilai_konversi' => 1, 'lebar_roll' => 1.2, 'satuan_stok' => 'm2',
            'id_satuan_hitung' => $this->satuanM2, 'id_satuan_stok' => $this->satuanM2, 'status' => 'aktif', 'harga_rata_rata' => 1000,
        ]);
        // Styrofoam: stok 10 lembar, 65.000/lembar.
        $this->bahanStyro = DB::table('bahan_baku')->insertGetId([
            'kode_bahan' => 'STYROFOAM', 'nama_bahan' => 'Styrofoam', 'id_mesin' => $this->mesin, 'id_kategori' => $this->kat,
            'stok' => 10, 'nilai_konversi' => 1, 'satuan_stok' => 'lembar',
            'id_satuan_hitung' => $this->satuanLembar, 'id_satuan_stok' => $this->satuanLembar, 'status' => 'aktif', 'harga_rata_rata' => 65000,
        ]);

        $this->gol = DB::table('golongan_konsumen')->insertGetId(['nama_golongan' => 'Retail', 'keterangan' => '-']);
        $this->kons = DB::table('konsumen')->insertGetId([
            'nama_konsumen' => 'Kons Uji', 'telepon' => '1', 'alamat' => '-', 'id_golongan' => $this->gol,
            'id_unique' => (string) \Illuminate\Support\Str::uuid(),
        ]);
        DB::table('kas_harian')->insert([
            'tgl_kas' => now()->toDateString(), 'id_user' => $this->admin->id_user,
            'saldo_awal' => 0, 'status_lock' => 'open', 'created_at' => now()->subMinute(),
        ]);
        DB::table('jam_kerja')->where('hari', now()->dayOfWeekIso)->update(['buka' => true]);

        $this->produk = DB::table('produk')->insertGetId([
            'nama_produk' => 'FLS280 3.2m', 'metode_hitung' => 'ukuran', 'status' => 'aktif',
            'min_luas_total' => 1.0, 'kelompok_minimum' => 'FLS280',
        ]);
        DB::table('bom_detail')->insert([
            'id_produk' => $this->produk, 'id_bahan' => $this->bahanCetak,
            'cara_hitung_pemakaian' => '(P*L)/10000', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('setting_harga')->insert([
            'id_produk' => $this->produk, 'id_golongan' => $this->gol, 'range_min' => 0.01, 'harga_jual' => 20000,
        ]);
    }

    /** Finishing styrofoam: 35.000/m2, memotong 0,5 lembar styrofoam per m2. */
    private function makeStyrofoamFinishing(bool $denganBahan = true, float $faktor = 0.5): int
    {
        return DB::table('finishing')->insertGetId([
            'nama_finishing' => 'Styrofoam Mockup',
            'harga_default' => 35000,
            'metode_hitung' => 'luas',
            'minimum_charge' => 0,
            'id_bahan' => $denganBahan ? $this->bahanStyro : null,
            'qty_bahan_per_satuan' => $denganBahan ? $faktor : null,
        ]);
    }

    private function item(int $idProduk, float $p, float $l, float $qty, float $harga, ?int $idFinishing = null): array
    {
        $item = [
            'id_produk' => $idProduk, 'nama_file' => 'uji.pdf', 'p' => $p, 'l' => $l, 'qty_input' => $qty,
            'biaya_cetak' => round($harga * (($p * $l / 10000) * $qty), 2), 'siap_cetak' => false,
        ];
        if ($idFinishing) {
            $item['id_finishing'] = $idFinishing;
            $item['biaya_finishing'] = 0;
            $item['finishing'] = [[
                'id_finishing' => $idFinishing,
                'status_finishing' => 'Standar',
                'biaya' => 0,
                'qty_pakai' => null,
                'catatan' => null,
            ]];
        }

        return $item;
    }

    private function store(array $items): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/penjualan/store', [
            'id_konsumen' => $this->kons, 'id_golongan' => $this->gol, 'submit_id' => 'fin-' . uniqid(),
            'items' => $items,
        ]);
    }

    private function detailPertama(): object
    {
        return DB::table('transaksi_detail')->orderBy('id_detail')->first();
    }

    /**
     * Order 60x40 (0,24 m2) + styrofoam:
     *  - stok styrofoam 10 -> 9,88 lembar (0,24 m2 x 0,5 lembar/m2)
     *  - kartu_stok OUT 0,12 lembar
     *  - snapshot pemakaian berisi bahan cetak 0,24 m2 + styrofoam 0,12 lembar
     *  - HPP = 240 (frontlite) + 7.800 (styrofoam) = 8.040
     *  - qty_pakai finishing luas tersimpan 0,24 m2
     */
    public function test_finishing_styrofoam_memotong_stok_dan_masuk_hpp(): void
    {
        $styro = $this->makeStyrofoamFinishing();

        $this->store([$this->item($this->produk, 60, 40, 1, 20000, $styro)])->assertOk();

        $detail = $this->detailPertama();

        $this->assertEquals(9.88, (float) DB::table('bahan_baku')->where('id_bahan', $this->bahanStyro)->value('stok'), 0.0001);

        $kartu = DB::table('kartu_stok')->where('id_bahan', $this->bahanStyro)->where('tipe', 'OUT')->first();
        $this->assertNotNull($kartu, 'stok styrofoam harus punya baris kartu_stok OUT');
        $this->assertEquals(0.12, (float) $kartu->qty, 0.0001);
        $this->assertSame(10.0, (float) $kartu->stok_sebelum, 0.0001);
        $this->assertSame(9.88, (float) $kartu->stok_sesudah, 0.0001);

        $snapshot = DB::table('transaksi_detail_bahan_pakai')->where('id_detail', $detail->id_detail)->get();
        $this->assertCount(2, $snapshot);
        $this->assertEquals(0.12, (float) $snapshot->firstWhere('id_bahan', $this->bahanStyro)->qty_pakai, 0.0001);

        $this->assertEquals(8040.0, (float) $detail->hpp_snapshot, 0.01);

        $fin = DB::table('transaksi_detail_finishing')->where('id_detail', $detail->id_detail)->first();
        $this->assertEquals(0.24, (float) $fin->qty_pakai, 0.0001, 'luas finishing harus tersimpan, bukan NULL');
        $this->assertEquals(8400.0, (float) $fin->biaya, 0.01);
    }

    /**
     * Reversal (batal SPK / koreksi nota) mengembalikan stok PERSIS dari
     * snapshot - termasuk baris bahan finishing - dan snapshot dihabiskan
     * supaya tidak bisa dikembalikan dua kali.
     */
    public function test_reversal_mengembalikan_stok_styrofoam_sekali_saja(): void
    {
        $styro = $this->makeStyrofoamFinishing();
        $this->store([$this->item($this->produk, 60, 40, 1, 20000, $styro)])->assertOk();

        $detail = $this->detailPertama();

        $ringkasan = StokReversalService::kembalikan($detail, 'UJI-REVERSAL', 'Uji pengembalian bahan');
        $this->assertNotEmpty($ringkasan);
        $this->assertEquals(10.0, (float) DB::table('bahan_baku')->where('id_bahan', $this->bahanStyro)->value('stok'), 0.0001);

        $kartuIn = DB::table('kartu_stok')->where('id_bahan', $this->bahanStyro)->where('tipe', 'IN')->first();
        $this->assertNotNull($kartuIn);
        $this->assertEquals(0.12, (float) $kartuIn->qty, 0.0001);

        $this->assertSame(0, DB::table('transaksi_detail_bahan_pakai')->where('id_detail', $detail->id_detail)->count());

        // Panggilan kedua tidak boleh menambah stok lagi.
        StokReversalService::kembalikan($detail, 'UJI-REVERSAL-2', 'Uji pengembalian kedua');
        $this->assertEquals(10.0, (float) DB::table('bahan_baku')->where('id_bahan', $this->bahanStyro)->value('stok'), 0.0001);
    }

    /**
     * Finishing tanpa bahan (jasa jahit/laminasi) tidak memotong stok apa pun,
     * tetapi qty_pakai tetap tercatat supaya penagihan bisa ditelusuri.
     */
    public function test_finishing_tanpa_bahan_tidak_memotong_stok(): void
    {
        $finishing = $this->makeStyrofoamFinishing(false);

        $this->store([$this->item($this->produk, 100, 100, 1, 20000, $finishing)])->assertOk();

        $this->assertSame(10.0, (float) DB::table('bahan_baku')->where('id_bahan', $this->bahanStyro)->value('stok'), 0.0001);
        $this->assertSame(0, DB::table('kartu_stok')->where('id_bahan', $this->bahanStyro)->count());

        $fin = DB::table('transaksi_detail_finishing')->first();
        $this->assertEquals(1.0, (float) $fin->qty_pakai, 0.0001);
    }
}

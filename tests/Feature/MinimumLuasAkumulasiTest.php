<?php
namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Akumulasi Minimum Order lintas-produk dalam satu KELOMPOK (spec owner
 * 2026-11-15):
 *  - Item A + Item B sekelompok dijumlahkan dulu, baru minimum diterapkan
 *    (bukan minimum per baris).
 *  - Item dengan tarif/minimum berbeda di label yang sama TIDAK dijumlahkan
 *    (kunci kompatibilitas MinimumLuas::kunciKelompok) - masing-masing
 *    memenuhi minimumnya sendiri.
 *  - Minimum PENAGIHAN daftar harga (range_min terkecil) tetap berjalan
 *    untuk produk tanpa konfigurasi minimum order - dua konsep terpisah.
 * Selisih tetap digulung ke baris terbesar (mode HYBRID T4-A).
 *
 * CATATAN EKSEKUSI: butuh database testing terpisah (RefreshDatabase);
 * belum bisa dijalankan tanpa skema percetakan_erp_testing.
 */
class MinimumLuasAkumulasiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private int $gol;
    private int $kons;
    private int $mesin;
    private int $kat;
    private int $m2;
    private int $bahanA;

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
        $this->m2 = DB::table('satuan')->insertGetId(['nama_satuan' => 'm2', 'nilai_konversi' => 1]);
        $this->bahanA = DB::table('bahan_baku')->insertGetId([
            'kode_bahan' => 'BA', 'nama_bahan' => 'Bahan A', 'id_mesin' => $this->mesin, 'id_kategori' => $this->kat,
            'stok' => 100, 'nilai_konversi' => 1, 'lebar_roll' => 1.2, 'satuan_stok' => 'm2',
            'id_satuan_hitung' => $this->m2, 'id_satuan_stok' => $this->m2, 'status' => 'aktif', 'harga_rata_rata' => 1000,
        ]);
        $this->gol = DB::table('golongan_konsumen')->insertGetId(['nama_golongan' => 'Retail', 'keterangan' => '-']);
        $this->kons = DB::table('konsumen')->insertGetId(['nama_konsumen' => 'Kons Uji', 'telepon' => '1', 'alamat' => '-', 'id_golongan' => $this->gol,
            // aplikasi selalu mengisi id_unique saat insert (KonsumenController::Str::uuid()) - skema kolomnya NOT NULL tanpa default
            'id_unique' => (string) \Illuminate\Support\Str::uuid()]);
        DB::table('kas_harian')->insert(['tgl_kas' => now()->toDateString(), 'id_user' => $this->admin->id_user, 'saldo_awal' => 0, 'status_lock' => 'open', 'created_at' => now()->subMinute()]);
        // Skenario uji = toko BUKA. Migrasi menanam jam_kerja Minggu tutup; tanpa ini, store() ditolak
        // 400 "toko libur" bila test dijalankan hari Minggu (guard KasSesiService, bukan bagian fitur minimum).
        DB::table('jam_kerja')->where('hari', now()->dayOfWeekIso)->update(['buka' => true]);
    }

    private function makeProduk(string $nama, string $kelompok, float $minLuas, float $harga): int
    {
        $id = DB::table('produk')->insertGetId([
            'nama_produk' => $nama, 'metode_hitung' => 'ukuran', 'status' => 'aktif',
            'min_luas_total' => $minLuas, 'kelompok_minimum' => $kelompok,
        ]);
        DB::table('bom_detail')->insert(['id_produk' => $id, 'id_bahan' => $this->bahanA, 'cara_hitung_pemakaian' => '(P*L)/10000', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('setting_harga')->insert(['id_produk' => $id, 'id_golongan' => $this->gol, 'range_min' => 0.01, 'harga_jual' => $harga]);
        return $id;
    }

    private function store(array $items): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/penjualan/store', [
            'id_konsumen' => $this->kons, 'id_golongan' => $this->gol, 'submit_id' => 'ak-' . uniqid(),
            'items' => $items,
        ]);
    }

    private function item(int $idProduk, float $p, float $l, float $qty, float $harga): array
    {
        return [
            'id_produk' => $idProduk, 'nama_file' => 'uji.pdf', 'p' => $p, 'l' => $l, 'qty_input' => $qty,
            'biaya_cetak' => round($harga * (($p * $l / 10000) * $qty), 2), 'siap_cetak' => false,
        ];
    }

    private function makeFinishing(string $nama, float $harga, string $metode = 'luas'): int
    {
        return DB::table('finishing')->insertGetId([
            'nama_finishing' => $nama,
            'harga_default' => $harga,
            'metode_hitung' => $metode,
            'minimum_charge' => 0,
        ]);
    }

    private function itemFinishing(int $idProduk, float $p, float $l, float $qty, float $harga, int $idFinishing, float $biayaFinishing): array
    {
        $item = $this->item($idProduk, $p, $l, $qty, $harga);
        $item['id_finishing'] = $idFinishing;
        $item['biaya_finishing'] = $biayaFinishing;
        $item['finishing'] = [[
            'id_finishing' => $idFinishing,
            'status_finishing' => 'Standar',
            'biaya' => $biayaFinishing,
            'qty_pakai' => null,
            'catatan' => null,
        ]];

        return $item;
    }

    private function barisNota(): array
    {
        $nota = DB::table('transaksi')->first();
        return DB::table('transaksi_detail')->where('id_transaksi', $nota->id_transaksi)
            ->whereNull('id_jasa')->orderBy('id_detail')->get()->all();
    }

    /** Spec contoh 1: A 0,4 + B 0,7 sekelompok = 1,1 -> tanpa selisih. */
    public function test_dua_produk_sekelompok_0_4_plus_0_7_tanpa_selisih(): void
    {
        $a = $this->makeProduk('Outdoor A', 'Group X', 1.0, 50000);
        $b = $this->makeProduk('Outdoor B', 'Group X', 1.0, 50000);
        $this->store([$this->item($a, 40, 100, 1, 50000), $this->item($b, 70, 100, 1, 50000)])->assertOk();
        foreach ($this->barisNota() as $b) {
            $this->assertEquals(0, (int) $b->is_minimum_order, 'total kelompok 1,1 >= 1: tanpa selisih');
            $this->assertStringNotContainsString('Minimum Order', (string) $b->catatan_produksi);
        }
        $this->assertEquals(20000.0 + 35000.0, (float) DB::table('transaksi')->value('total_bayar'), 0.01);
    }

    /** Spec contoh 2: A 0,3 + B 0,2 sekelompok = 0,5 -> selisih 0,5 ke baris terbesar. */
    public function test_dua_produk_sekelompok_0_3_plus_0_2_selisih_0_5(): void
    {
        $a = $this->makeProduk('Outdoor A', 'Group X', 1.0, 50000);
        $b = $this->makeProduk('Outdoor B', 'Group X', 1.0, 50000);
        $this->store([$this->item($a, 30, 100, 1, 50000), $this->item($b, 20, 100, 1, 50000)])->assertOk();
        $baris = $this->barisNota();
        $this->assertCount(2, $baris);
        // Baris terbesar (A, 0,3) membawa selisih 0,5 x 50000 = 25000.
        $this->assertEquals(15000.0 + 25000.0, (float) $baris[0]->subtotal, 0.01);
        $this->assertEquals(1, (int) $baris[0]->is_minimum_order);
        $this->assertEquals(10000.0, (float) $baris[1]->subtotal, 0.01);
        $this->assertEquals(0, (int) $baris[1]->is_minimum_order);
        $this->assertEquals(50000.0, (float) DB::table('transaksi')->value('total_bayar'), 0.01);
    }

    /**
     * Tarif beda di label yang sama = TIDAK dijumlahkan, masing-masing
     * kelompok dihitung sendiri (kunci kompatibilitas MinimumLuas::kunciKelompok).
     * Kelompok 1-item diblokir; masing-masing kelompok diberi 2 item di bawah min.
     */
    public function test_tarif_berbeda_label_sama_terakumulasi_terpisah(): void
    {
        $a = $this->makeProduk('Outdoor A', 'Group X', 1.0, 50000);
        $a2 = $this->makeProduk('Outdoor A2', 'Group X', 1.0, 50000);
        $b = $this->makeProduk('Outdoor B', 'Group X', 1.0, 60000);
        $b2 = $this->makeProduk('Outdoor B2', 'Group X', 1.0, 60000);
        // Grup A: 0,3+0,4 -> selisih 0,3 x 50.000. Grup B: 0,2+0,3 -> selisih 0,5 x 60.000, tarif B dipakai ke B.
        $this->store([$this->item($a, 30, 100, 1, 50000), $this->item($a2, 40, 100, 1, 50000), $this->item($b, 20, 100, 1, 60000), $this->item($b2, 30, 100, 1, 60000)])->assertOk();
        $baris = $this->barisNota();
        // Grup A: 0,3+0,4 = 0,7; selisih 0,3 x 50.000 digulung ke baris TERBESAR (A2 = 0,4).
        $this->assertEquals(15000.0, (float) $baris[0]->subtotal, 0.01);
        $this->assertEquals(0, (int) $baris[0]->is_minimum_order);
        $this->assertEquals(20000.0 + 0.3 * 50000, (float) $baris[1]->subtotal, 0.01);
        $this->assertEquals(1, (int) $baris[1]->is_minimum_order);
        // Grup B: 0,2+0,3 = 0,5; selisih 0,5 x 60.000 digulung ke baris TERBESAR (B2 = 0,3).
        $this->assertEquals(12000.0, (float) $baris[2]->subtotal, 0.01);
        $this->assertEquals(0, (int) $baris[2]->is_minimum_order);
        $this->assertEquals(18000.0 + 0.5 * 60000, (float) $baris[3]->subtotal, 0.01);
        $this->assertEquals(1, (int) $baris[3]->is_minimum_order);
        // Total = (1,0 x 50.000) + (1,0 x 60.000).
        $this->assertEquals(50000.0 + 60000.0, (float) DB::table('transaksi')->value('total_bayar'), 0.01);
    }

    /** Skenario 12: qty > 1 pada masing-masing item - luas = (P x L / 10000) x qty, sekali hitung. */
    public function test_qty_dua_dua_item_sekelompok(): void
    {
        $a = $this->makeProduk('Outdoor A', 'Group X', 1.0, 50000);
        $b = $this->makeProduk('Outdoor B', 'Group X', 1.0, 50000);
        // A: 50x50x2 = 0,5 m2; B: 50x50x2 = 0,5 m2 -> total 1,0 -> tanpa selisih.
        $this->store([$this->item($a, 50, 50, 2, 50000), $this->item($b, 50, 50, 2, 50000)])->assertOk();
        foreach ($this->barisNota() as $b) {
            $this->assertEquals(0, (int) $b->is_minimum_order);
        }
        $this->assertEquals(25000.0 + 25000.0, (float) DB::table('transaksi')->value('total_bayar'), 0.01);
    }

    /**
     * Dua kelompok minimum berbeda dalam satu nota: selisih per kelompok,
     * tidak saling menolong. Kelompok 1-item diblokir, jadi masing-masing
     * kelompok diberi 2 item di bawah minimum.
     */
    public function test_dua_kelompok_berbeda_tidak_saling_menolong(): void
    {
        $a = $this->makeProduk('Outdoor A', 'Group X', 1.0, 50000);
        $a2 = $this->makeProduk('Outdoor A2', 'Group X', 1.0, 50000);
        $b = $this->makeProduk('Outdoor B', 'Group Y', 2.0, 50000);
        $b2 = $this->makeProduk('Outdoor B2', 'Group Y', 2.0, 50000);
        // X: 0,3+0,4 = 0,7 < 1,0 -> selisih 0,3. Y: 0,7+0,6 = 1,3 < 2,0 -> selisih 0,7.
        $this->store([$this->item($a, 30, 100, 1, 50000), $this->item($a2, 40, 100, 1, 50000), $this->item($b, 70, 100, 1, 50000), $this->item($b2, 60, 100, 1, 50000)])->assertOk();
        $baris = $this->barisNota();
        $this->assertEquals(15000.0, (float) $baris[0]->subtotal, 0.01);
        $this->assertEquals(20000.0 + 0.3 * 50000, (float) $baris[1]->subtotal, 0.01, 'A2 terbesar X membawa selisih X');
        $this->assertEquals(35000.0 + 0.7 * 50000, (float) $baris[2]->subtotal, 0.01, 'B terbesar Y membawa selisih Y');
        $this->assertEquals(30000.0, (float) $baris[3]->subtotal, 0.01);
        $this->assertEquals((1.0 + 2.0) * 50000, (float) DB::table('transaksi')->value('total_bayar'), 0.01);
    }

    /**
     * Skenario 6/7: produk TANPA konfigurasi minimum order - minimum
     * PENAGIHAN daftar harga tetap konsep terpisah dari minimum order
     * akumulasi luas (tidak saling menimpa).
     *
     * Catatan keputusan owner 2026-11-15: guard floor daftar harga sisi server
     * BELUM efektif untuk ukuran di bawah tier terkecil - perilaku pre-existing
     * clamp max(qty,1) pada pemanggilan HargaService (commit 7cc23813, sebelum
     * fitur ini) yang menagih tier qty=1; POS memblokir di sisi layar. Penguatan
     * server-side menjadi pekerjaan terpisah, BUKAN bagian fitur minimum order
     * (sesuai keputusan sebelumnya: temuan didokumentasikan, tidak diperbaiki
     * di sini). Ekspektasi disesuaikan ke perilaku aktual (200), bukan 422.
     */
    public function test_tanpa_minimum_order_dua_konsep_terpisah(): void
    {
        $prod = DB::table('produk')->insertGetId(['nama_produk' => 'Indoor', 'metode_hitung' => 'ukuran', 'status' => 'aktif', 'min_luas_total' => 0, 'kelompok_minimum' => null]);
        DB::table('bom_detail')->insert(['id_produk' => $prod, 'id_bahan' => $this->bahanA, 'cara_hitung_pemakaian' => '(P*L)/10000', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('setting_harga')->insert(['id_produk' => $prod, 'id_golongan' => $this->gol, 'range_min' => 1.0, 'harga_jual' => 10000]);
        // 0,5 m2 di bawah minimum daftar (1 m2): diterima server (clamp pre-existing), tanpa penanda minimum order.
        $this->store([$this->item($prod, 50, 100, 1, 5000)])->assertOk();
        // Luas >= minimum daftar -> jalan normal, tanpa penanda minimum order.
        $this->store([$this->item($prod, 100, 100, 1, 10000)])->assertOk();
        $baris = $this->barisNota();
        $this->assertSame(0, (int) $baris[0]->is_minimum_order);
    }

    /**
     * Pengecualian mockup styrofoam (keputusan owner 21 Nov 2026): satu item
     * FLS280 0,24 m2 (di bawah minimum 1 m2) + finishing Styrofoam tetap
     * tersimpan. Penagihan tidak berubah - cetak tetap ditagih minimum 1 m2
     * (selisih 0,76 x 20.000 = 15.200), styrofoam ditagih luas aktual
     * 0,24 m2 x 35.000 = 8.400, sama seperti nota ORG-20261106-030.
     */
    public function test_satu_item_di_bawah_minimum_dengan_finishing_styrofoam_tetap_tersimpan(): void
    {
        $produk = $this->makeProduk('FLS280 3.2m', 'FLS280', 1.0, 20000);
        $styro = $this->makeFinishing('Styrofoam Mockup', 35000);

        $this->store([$this->itemFinishing($produk, 60, 40, 1, 20000, $styro, 0)])->assertOk();

        $baris = $this->barisNota();
        $this->assertCount(1, $baris);
        $this->assertEquals(1, (int) $baris[0]->is_minimum_order);
        $this->assertEquals(15200.0, (float) $baris[0]->biaya_minimum, 0.01);
        $this->assertEquals(20000.0, (float) $baris[0]->biaya_cetak, 0.01, 'cetak ditagih minimum 1 m2');
        $this->assertEquals(8400.0, (float) $baris[0]->biaya_finishing, 0.01, 'styrofoam luas aktual 0,24 m2');
        $this->assertEquals(28400.0, (float) $baris[0]->subtotal, 0.01);
        $this->assertStringContainsString('Minimum Order FLS280', (string) $baris[0]->catatan_produksi);

        $fin = DB::table('transaksi_detail_finishing')->where('id_detail', $baris[0]->id_detail)->get();
        $this->assertCount(1, $fin);
        $this->assertEquals(8400.0, (float) $fin[0]->biaya, 0.01);
        $this->assertEquals(28400.0, (float) DB::table('transaksi')->value('total_bayar'), 0.01);
    }

    /** Tanpa finishing Styrofoam, satu item di bawah minimum tetap diblokir. */
    public function test_satu_item_di_bawah_minimum_tanpa_styrofoam_tetap_ditolak(): void
    {
        $produk = $this->makeProduk('FLS280 3.2m', 'FLS280', 1.0, 20000);

        $r = $this->store([$this->item($produk, 60, 40, 1, 20000)]);

        $r->assertStatus(422);
        $this->assertStringContainsString('Minimum order belum terpenuhi', (string) $r->json('msg'));
        $this->assertSame(0, DB::table('transaksi')->count());
    }

    /** Finishing lain tidak membuka pengecualian - hanya Styrofoam. */
    public function test_satu_item_di_bawah_minimum_dengan_finishing_lain_tetap_ditolak(): void
    {
        $produk = $this->makeProduk('FLS280 3.2m', 'FLS280', 1.0, 20000);
        $kisscut = $this->makeFinishing('Kisscut', 20000);

        $this->store([$this->itemFinishing($produk, 60, 40, 1, 20000, $kisscut, 0)])->assertStatus(422);
        $this->assertSame(0, DB::table('transaksi')->count());
    }
}

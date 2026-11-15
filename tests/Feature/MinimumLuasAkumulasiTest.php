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

    /** Tarif beda di label yang sama = TIDAK dijumlahkan: masing-masing kelompok selisihnya sendiri. */
    public function test_tarif_berbeda_label_sama_terakumulasi_terpisah(): void
    {
        $a = $this->makeProduk('Outdoor A', 'Group X', 1.0, 50000);
        $b = $this->makeProduk('Outdoor B', 'Group X', 1.0, 60000);
        $this->store([$this->item($a, 30, 100, 1, 50000), $this->item($b, 20, 100, 1, 60000)])->assertOk();
        $baris = $this->barisNota();
        // Kunci kompatibilitas memisahkan A dan B (tarif beda): A selisih
        // 1,0-0,3 = 0,7; B selisih 1,0-0,2 = 0,8 - masing-masing memenuhi
        // minimumnya sendiri, total = tarif x minimum per produk.
        $this->assertEquals(15000.0 + 0.7 * 50000, (float) $baris[0]->subtotal, 0.01);
        $this->assertEquals(1, (int) $baris[0]->is_minimum_order);
        $this->assertEquals(12000.0 + 0.8 * 60000, (float) $baris[1]->subtotal, 0.01);
        $this->assertEquals(1, (int) $baris[1]->is_minimum_order);
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

    /** Dua kelompok minimum berbeda dalam satu nota: selisih per kelompok, tidak saling menolong. */
    public function test_dua_kelompok_berbeda_tidak_saling_menolong(): void
    {
        $a = $this->makeProduk('Outdoor A', 'Group X', 1.0, 50000);
        $b = $this->makeProduk('Outdoor B', 'Group Y', 2.0, 50000);
        $this->store([$this->item($a, 30, 100, 1, 50000), $this->item($b, 70, 100, 1, 50000)])->assertOk();
        $baris = $this->barisNota();
        // A (0,3, min 1,0): selisih 0,7. B (0,7, min 2,0): selisih 1,3.
        $this->assertEquals(15000.0 + 0.7 * 50000, (float) $baris[0]->subtotal, 0.01);
        $this->assertEquals(35000.0 + 1.3 * 50000, (float) $baris[1]->subtotal, 0.01);
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
}

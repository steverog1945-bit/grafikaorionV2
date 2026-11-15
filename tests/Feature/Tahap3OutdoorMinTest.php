<?php
namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * T1-T3: Minimum luas outdoor (total per kelompok), PENYAJIAN HYBRID
 * (keputusan owner, notasi SETUJU T4-A): selisih tidak lagi jadi baris
 * terpisah — digulung ke subtotal baris item TERBESAR kelompok itu, dan
 * kolom is_minimum_order menandai baris item yang membawa selisih.
 * Ditulis terhadap perilaku yang DIHARAPKAN (aturan final + hybrid).
 * Harus GAGAL sebelum perbaikan, HIJAU setelah.
 */
class Tahap3OutdoorMinTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private int $gol;
    private int $kons;
    private int $mesin;
    private int $kat;
    private int $m2;
    private int $bahanA;
    private int $bahanB;

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
        $this->bahanB = DB::table('bahan_baku')->insertGetId([
            'kode_bahan' => 'BB', 'nama_bahan' => 'Bahan B', 'id_mesin' => $this->mesin, 'id_kategori' => $this->kat,
            'stok' => 100, 'nilai_konversi' => 1, 'lebar_roll' => 1.2, 'satuan_stok' => 'm2',
            'id_satuan_hitung' => $this->m2, 'id_satuan_stok' => $this->m2, 'status' => 'aktif', 'harga_rata_rata' => 2000,
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

    private function makeProduk(string $nama, string $kelompok, float $minLuas, int $bahan, float $harga): int
    {
        $id = DB::table('produk')->insertGetId([
            'nama_produk' => $nama, 'metode_hitung' => 'ukuran', 'status' => 'aktif',
            'min_luas_total' => $minLuas, 'kelompok_minimum' => $kelompok,
        ]);
        DB::table('bom_detail')->insert(['id_produk' => $id, 'id_bahan' => $bahan, 'cara_hitung_pemakaian' => '(P*L)/10000', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('setting_harga')->insert(['id_produk' => $id, 'id_golongan' => $this->gol, 'range_min' => 0.01, 'harga_jual' => $harga]);
        return $id;
    }

    private function store(array $items): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/penjualan/store', [
            'id_konsumen' => $this->kons, 'id_golongan' => $this->gol, 'submit_id' => 't3-' . uniqid(),
            'items' => $items,
        ]);
    }

    // biaya_cetak dikirim = tarif x luas aktual (baris item ditagih luas
    // aktualnya), lalu selisih minimum digulung server ke baris terbesar
    // kelompok.
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

    /**
     * SATU item di bawah minimum kelompok: simpan DIBLOKIR (422) sampai
     * ukuran diubah ke minimum atau ditambah item lain sekelompok
     * (keputusan owner 2026-11-15; menggantikan penagihan minimum non-blocking).
     */
    public function test_satu_item_di_bawah_minimum_diblokir(): void
    {
        $prod = $this->makeProduk('Outdoor A', 'Outdoor A', 1.0, $this->bahanA, 50000);
        $resp = $this->store([$this->item($prod, 60, 100, 1, 50000)]);
        $resp->assertStatus(422);
        $this->assertStringContainsString('Minimum order belum terpenuhi', (string) $resp->json('msg'));
        $this->assertEquals(0, DB::table('transaksi')->count(), 'nota tidak boleh tersimpan ketika 1 item di bawah minimum');
    }

    public function test_0_6_plus_0_3_produk_sama_minimum_ditiadakan(): void
    {
        // Eksempsi (keputusan owner 2026-11-14): produk yang sama muncul
        // >= 2 baris dalam satu nota -> hitungan minimum TIDAK berlaku,
        // semua baris ditagih luas aktual tanpa selisih.
        $prod = $this->makeProduk('Outdoor A', 'Outdoor A', 1.0, $this->bahanA, 50000);
        $this->store([$this->item($prod, 60, 100, 1, 50000), $this->item($prod, 30, 100, 1, 50000)])->assertOk();
        $nota = DB::table('transaksi')->first();
        $baris = $this->barisNota();
        $this->assertCount(2, $baris);
        foreach ($baris as $b) {
            $this->assertEquals(0, (int) $b->is_minimum_order, 'produk sama >= 2 baris: minimum ditiadakan');
            $this->assertStringNotContainsString('Minimum Order', (string) $b->catatan_produksi);
        }
        $this->assertEquals(30000.0, (float) $baris[0]->subtotal, 0.01); // 0.6 m2 aktual
        $this->assertEquals(15000.0, (float) $baris[1]->subtotal, 0.01); // 0.3 m2 aktual
        $this->assertEquals(45000.0, (float) $nota->total_bayar, 0.01);
    }

    public function test_0_6_plus_0_7_tanpa_selisih_total_1_3(): void
    {
        $prod = $this->makeProduk('Outdoor A', 'Outdoor A', 1.0, $this->bahanA, 50000);
        $this->store([$this->item($prod, 60, 100, 1, 50000), $this->item($prod, 70, 100, 1, 50000)])->assertOk();
        $baris = $this->barisNota();
        foreach ($baris as $b) {
            $this->assertEquals(0, (int) $b->is_minimum_order, 'total >= 1, tanpa penanda minimum');
        }
        $this->assertEquals(30000.0, (float) $baris[0]->subtotal, 0.01);
        $this->assertEquals(35000.0, (float) $baris[1]->subtotal, 0.01);
    }

    public function test_qty_5_x_50x50_tanpa_selisih(): void
    {
        $prod = $this->makeProduk('Outdoor A', 'Outdoor A', 1.0, $this->bahanA, 50000);
        $this->store([$this->item($prod, 50, 50, 5, 50000)])->assertOk();
        $baris = $this->barisNota();
        $this->assertSame(0, (int) $baris[0]->is_minimum_order);
        $this->assertEquals(62500.0, (float) $baris[0]->subtotal, 0.01); // 1.25 m2 x 50000
    }

    /** Kelompok tarif beda dengan 2 item masing-masing: selisih per kelompok tetap ditagih masing-masing tarifnya. */
    public function test_0_5_A_plus_0_5_B_selisih_per_kelompok_total_2_0(): void
    {
        $prodA = $this->makeProduk('Outdoor A', 'Kelompok A', 1.0, $this->bahanA, 50000);
        $prodA2 = $this->makeProduk('Outdoor A2', 'Kelompok A', 1.0, $this->bahanA, 50000);
        $prodB = $this->makeProduk('Outdoor B', 'Kelompok B', 1.0, $this->bahanB, 60000);
        $prodB2 = $this->makeProduk('Outdoor B2', 'Kelompok B', 1.0, $this->bahanB, 60000);
        // A group: 0,5+0,4 = 0,9 -> selisih 0,1 x 50.000 = 5.000 (baris terbesar = A).
        // B group: 0,5+0,3 = 0,8 -> selisih 0,2 x 60.000 = 12.000 (baris terbesar = B).
        $this->store([$this->item($prodA, 50, 100, 1, 50000), $this->item($prodA2, 40, 100, 1, 50000), $this->item($prodB, 50, 100, 1, 60000), $this->item($prodB2, 30, 100, 1, 60000)])->assertOk();
        $baris = $this->barisNota();
        $this->assertCount(4, $baris);
        $this->assertEquals(25000.0 + 5000.0, (float) $baris[0]->subtotal, 0.01, 'A terbesar membawa selisih kelompok A');
        $this->assertEquals(20000.0, (float) $baris[1]->subtotal, 0.01);
        $this->assertEquals(30000.0 + 12000.0, (float) $baris[2]->subtotal, 0.01, 'B terbesar membawa selisih kelompok B');
        $this->assertEquals(18000.0, (float) $baris[3]->subtotal, 0.01);
        $this->assertEquals(50000.0 + 60000.0, (float) DB::table('transaksi')->value('total_bayar'), 0.01, 'total = minimum A + minimum B = 1,0x50.000 + 1,0x60.000');
    }

    public function test_produk_tanpa_min_luas_total_tidak_kena(): void
    {
        $prod = DB::table('produk')->insertGetId(['nama_produk' => 'Indoor', 'metode_hitung' => 'ukuran', 'status' => 'aktif', 'min_luas_total' => 0, 'kelompok_minimum' => null]);
        DB::table('bom_detail')->insert(['id_produk' => $prod, 'id_bahan' => $this->bahanA, 'cara_hitung_pemakaian' => '(P*L)/10000', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('setting_harga')->insert(['id_produk' => $prod, 'id_golongan' => $this->gol, 'range_min' => 0.01, 'harga_jual' => 10000]);
        $this->store([$this->item($prod, 10, 10, 1, 10000)])->assertOk();
        $baris = $this->barisNota();
        $this->assertSame(0, (int) $baris[0]->is_minimum_order);
    }

    /** Lantai min_panjang/min_lebar: ukuran di bawah minimum produk DITOLAK saat simpan (2026-11-15). */
    public function test_lantai_panjang_lebar_di_bawah_minimum_diblokir(): void
    {
        $prod = DB::table('produk')->insertGetId(['nama_produk' => 'Century TC Test', 'metode_hitung' => 'ukuran', 'status' => 'aktif', 'min_panjang' => 120, 'min_lebar' => 200, 'min_luas_total' => 0, 'kelompok_minimum' => null]);
        DB::table('bom_detail')->insert(['id_produk' => $prod, 'id_bahan' => $this->bahanA, 'cara_hitung_pemakaian' => '(P*L)/10000', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('setting_harga')->insert(['id_produk' => $prod, 'id_golongan' => $this->gol, 'range_min' => 0.01, 'harga_jual' => 50000]);
        $resp = $this->store([$this->item($prod, 120, 150, 1, 50000)]);
        $resp->assertStatus(422);
        $this->assertStringContainsString('lebar 150 cm di bawah minimum', (string) $resp->json('msg'));
        $this->assertEquals(0, DB::table('transaksi')->count());
    }

    public function test_lantai_panjang_lebar_produk_sama_minimum_ditiadakan(): void
    {
        // Produk sama >= 2 baris (keputusan owner 2026-11-14): lantai dimatikan
        // untuk baris-baris produk itu - ukuran persis seperti input disimpan.
        $prod = DB::table('produk')->insertGetId(['nama_produk' => 'Century TC Test', 'metode_hitung' => 'ukuran', 'status' => 'aktif', 'min_panjang' => 120, 'min_lebar' => 200, 'min_luas_total' => 0, 'kelompok_minimum' => null]);
        DB::table('bom_detail')->insert(['id_produk' => $prod, 'id_bahan' => $this->bahanA, 'cara_hitung_pemakaian' => '(P*L)/10000', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('setting_harga')->insert(['id_produk' => $prod, 'id_golongan' => $this->gol, 'range_min' => 0.01, 'harga_jual' => 50000]);
        $this->store([$this->item($prod, 120, 150, 1, 50000), $this->item($prod, 120, 500, 1, 50000)])->assertOk();
        $baris = $this->barisNota();
        $this->assertEquals(120, (float) $baris[0]->p_ukuran);
        $this->assertEquals(150, (float) $baris[0]->l_ukuran, 'baris pertama TIDAK diubah saat produk sama muncul 2 baris');
        $this->assertEquals(120, (float) $baris[1]->p_ukuran);
        $this->assertEquals(500, (float) $baris[1]->l_ukuran);
    }

    public function test_penanda_minimum_di_baris_item_tetap_beralur_spk(): void
    {
        $prod = $this->makeProduk('Outdoor C', 'Kelompok C', 1.0, $this->bahanA, 50000);
        // Kelompok 1-item diblokir saat simpan -> perlu >=2 item sekelompok
        // (atau >= minimum) agar bisa tersimpan. 0,6 + 0,1 = 0,7; selisih 0,3
        // digulung ke baris terbesar (C 0,6).
        $kecil = $this->makeProduk('Outdoor C2', 'Kelompok C', 1.0, $this->bahanB, 50000);
        $this->store([$this->item($prod, 60, 100, 1, 50000), $this->item($kecil, 10, 100, 1, 50000)])->assertOk();
        $baris = $this->barisNota();
        $flagged = array_values(array_filter($baris, fn ($b) => (int) $b->is_minimum_order === 1));
        $this->assertCount(1, $flagged);
        // Baris flagged adalah baris ITEM sungguhan: punya produk, berstatus
        // 'Antre' (masuk alur SPK/produksi normal), bukan baris hantu.
        $this->assertEquals($prod, (int) $flagged[0]->id_produk);
        $this->assertSame('Antre', $flagged[0]->status_spk);
        // Catatan minimum ikut tersimpan supaya bisa menjelaskan tambahan subtotal.
        $this->assertStringContainsString('Minimum Order', (string) $flagged[0]->catatan_produksi);
    }
}

<?php
namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * REKONSILIASI SURCHARGE MINIMUM LINTAS SIMPAN (keputusan owner 2026-11-15).
 *
 * Surcharge minimum digulung ke biaya_cetak baris item. Sebelum ada kolom
 * biaya_minimum, simpan berikutnya (susulan / edit) tidak bisa mencabut
 * surcharge lama:
 *   - susulan yang MELengkapI kelompok -> surcharge lama + baru tertagih
 *     dua kali (overcharge),
 *   - edit re-save -> surcharge lama menumpuk di tiap simpan (compounding),
 *   - susulan kelompok masih kurang -> selisih dihitung dari item baru saja,
 *     bukan dari total gabungan nota.
 *
 * Skenario memakai tarif flat Rp50.000/m2, kelompok "Group X", minimum 1,0 m2.
 */
class MinimumLuasRekonsiliasiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected int $gol;
    protected int $kons;
    protected int $mesin;
    protected int $kat;
    protected int $m2;
    protected int $bahanA;
    protected int $idNota;

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
            'stok' => 1000, 'nilai_konversi' => 1, 'lebar_roll' => 1.2, 'satuan_stok' => 'm2',
            'id_satuan_hitung' => $this->m2, 'id_satuan_stok' => $this->m2, 'status' => 'aktif', 'harga_rata_rata' => 1000,
        ]);
        $this->gol = DB::table('golongan_konsumen')->insertGetId(['nama_golongan' => 'Retail', 'keterangan' => '-']);
        $this->kons = DB::table('konsumen')->insertGetId(['nama_konsumen' => 'Kons Uji', 'telepon' => '1', 'alamat' => '-', 'id_golongan' => $this->gol,
            'id_unique' => (string) \Illuminate\Support\Str::uuid()]);
        DB::table('kas_harian')->insert(['tgl_kas' => now()->toDateString(), 'id_user' => $this->admin->id_user, 'saldo_awal' => 0, 'status_lock' => 'open', 'created_at' => now()->subMinute()]);
        DB::table('jam_kerja')->where('hari', now()->dayOfWeekIso)->update(['buka' => true]);
    }

    protected function makeProduk(string $nama, string $kelompok, float $minLuas, float $harga = 50000): int
    {
        $id = DB::table('produk')->insertGetId([
            'nama_produk' => $nama, 'metode_hitung' => 'ukuran', 'status' => 'aktif',
            'min_luas_total' => $minLuas, 'kelompok_minimum' => $kelompok,
        ]);
        DB::table('bom_detail')->insert(['id_produk' => $id, 'id_bahan' => $this->bahanA, 'cara_hitung_pemakaian' => '(P*L)/10000', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('setting_harga')->insert(['id_produk' => $id, 'id_golongan' => $this->gol, 'range_min' => 0.01, 'harga_jual' => $harga]);
        return $id;
    }

    protected function item(int $idProduk, float $p, float $l, float $qty = 1, ?float $harga = null): array
    {
        $luas = ($p * $l / 10000) * $qty;
        return [
            'id_produk' => $idProduk, 'nama_file' => 'uji.pdf', 'p' => $p, 'l' => $l, 'qty_input' => $qty,
            'biaya_cetak' => round(($harga ?? 50000) * $luas, 2), 'siap_cetak' => false,
            'kategori_produk' => (string) $this->kat,
        ];
    }

    /** Simpan nota awal dengan items, lalu catat id notasanya. */
    protected function buatNota(array $items): void
    {
        $this->postJson('/penjualan/store', [
            'id_konsumen' => $this->kons, 'id_golongan' => $this->gol,
            'submit_id' => 'rk-' . uniqid(), 'items' => $items,
        ])->assertOk();
        $this->idNota = (int) DB::table('transaksi')->orderByDesc('id_transaksi')->value('id_transaksi');
    }

    protected function susulan(array $items): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/penjualan/store', [
            'susulan_id' => $this->idNota, 'id_konsumen' => $this->kons, 'id_golongan' => $this->gol,
            'submit_id' => 'rk-s-' . uniqid(), 'items' => $items,
        ]);
    }

    /** Payload edit meniru keranjang layar edit (baris lama = nilai tersimpan). */
    protected function barisEdit(object $lama, float $biayaCetakKirim, ?array $ukuran = null): array
    {
        return [
            'id_detail' => (int) $lama->id_detail, 'id_produk' => (int) $lama->id_produk,
            'nama_file' => (string) $lama->nama_file,
            'p' => $ukuran ? $ukuran[0] : (float) $lama->p_ukuran,
            'l' => $ukuran ? $ukuran[1] : (float) $lama->l_ukuran,
            'qty_input' => (int) $lama->qty,
            'biaya_cetak' => $biayaCetakKirim, 'biaya_desain' => 0, 'biaya_finishing' => 0,
            'siap_cetak' => false, 'file_siap_cetak' => 0, 'cetak_manual' => false,
            'finishing' => [], 'finishing_list' => [],
            'status_urgensi' => (string) $lama->status_urgensi,
            'status_finishing' => (string) $lama->status_finishing,
            'catatan_produksi' => (string) ($lama->catatan_produksi ?? ''),
            'kategori_produk' => (string) $this->kat,
        ];
    }

    protected function barisNota(): array
    {
        return DB::table('transaksi_detail')->where('id_transaksi', $this->idNota)
            ->whereNull('id_jasa')->orderBy('id_detail')->get()->all();
    }

    protected function totalNota(): float
    {
        return (float) DB::table('transaksi')->where('id_transaksi', $this->idNota)->value('total_bayar');
    }

    /** Jurnal pendapatan nota harus selalu = total nota. */
    protected function assertJurnalSinkron(): void
    {
        $noNota = DB::table('transaksi')->where('id_transaksi', $this->idNota)->value('nomor_nota');
        $jurnal = (float) DB::table('jurnal_umum')
            ->whereIn('kode_akun', ['4000', '4100'])
            ->where(function ($q) use ($noNota) {
                $q->where('no_referensi', $noNota)->orWhere('no_referensi', 'like', $noNota . '-%');
            })
            ->selectRaw('SUM(kredit - debet) AS n')->value('n');
        $this->assertEquals($this->totalNota(), $jurnal, 0.02, 'jurnal pendapatan != total nota');
    }

    /**
     * Nota: A 0,3 (carrier surcharge 0,3) + B 0,4. Susulan: C 0,5 ->
     * kelompok gabungan 1,2 >= 1,0 -> surcharge 0,3 DICABUT, semua baris
     * aktual, total = 1,2 x 50.000 = 60.000 (bukan 100.000 yang overcharge).
     */
    public function test_susulan_melengkapi_kelompok_surcharge_lama_dicabut(): void
    {
        $a = $this->makeProduk('Outdoor A', 'Group X', 1.0);
        $b = $this->makeProduk('Outdoor B', 'Group X', 1.0);
        $c = $this->makeProduk('Outdoor C', 'Group X', 1.0);
        $this->buatNota([$this->item($a, 30, 100), $this->item($b, 40, 100)]);

        $baris = $this->barisNota();
        $this->assertEquals(15000.0, (float) $baris[0]->subtotal, 0.01, 'A (0,3): aktual, tanpa surcharge');
        $this->assertEquals(0, (int) $baris[0]->is_minimum_order);
        $this->assertEquals(35000.0, (float) $baris[1]->subtotal, 0.01, 'B (0,4 TERBESAR) membawa selisih 0,3 x 50.000');
        $this->assertEquals(15000.0, (float) $baris[1]->biaya_minimum, 0.01);
        $this->assertEquals(1, (int) $baris[1]->is_minimum_order);

        $this->susulan([$this->item($c, 50, 100)])->assertOk();

        // Item susulan memakai tarif penuh (bukan yang dipotong minimum): catatan
        // minimum lama sudah bersih, semua baris = luas aktual.
        $baris = $this->barisNota();
        $this->assertCount(3, $baris);
        // A: kembali ke luas aktual, tanpa komponen/penanda minimum.
        $this->assertEquals(15000.0, (float) $baris[0]->biaya_cetak, 0.01);
        $this->assertEquals(0.0, (float) $baris[0]->biaya_minimum, 0.01);
        $this->assertEquals(0, (int) $baris[0]->is_minimum_order);
        $this->assertStringNotContainsString('Minimum Order', (string) $baris[0]->catatan_produksi);
        // B: surcharge lama dicabut - kembali ke luas aktual.
        $this->assertEquals(20000.0, (float) $baris[1]->biaya_cetak, 0.01);
        $this->assertEquals(0.0, (float) $baris[1]->biaya_minimum, 0.01);
        $this->assertEquals(0, (int) $baris[1]->is_minimum_order);
        $this->assertStringNotContainsString('Minimum Order', (string) $baris[1]->catatan_produksi);
        // C: aktual, tanpa surcharge baru.
        $this->assertEquals(25000.0, (float) $baris[2]->biaya_cetak, 0.01);
        $this->assertEquals(0.0, (float) $baris[2]->biaya_minimum, 0.01);
        $this->assertEquals(0, (int) $baris[2]->is_minimum_order);
        $this->assertEquals(60000.0, $this->totalNota(), 0.01, 'total = 1,2 m2 aktual');
        $this->assertJurnalSinkron();
    }

    /**
     * Nota: A 0,3 + B 0,4 (B membawa selisih 0,3 -> 15.000). Susulan:
     * C 0,1 -> gabungan 0,8 masih < 1,0. Selisih dihitung GABUNGAN (0,2,
     * bukan 0,9 dari item baru saja), dan digulung ke baris terbesar
     * GABUNGAN (B, baris lama). Total tetap di minimum kelompok = 50.000.
     */
    public function test_susulan_belum_cukup_selisih_dialihkan_ke_baris_lama_terbesar(): void
    {
        $a = $this->makeProduk('Outdoor A', 'Group X', 1.0);
        $b = $this->makeProduk('Outdoor B', 'Group X', 1.0);
        $c = $this->makeProduk('Outdoor C', 'Group X', 1.0);
        $this->buatNota([$this->item($a, 30, 100), $this->item($b, 40, 100)]);
        $this->assertEquals(50000.0, $this->totalNota(), 0.01, 'nota awal ditagih minimum 1,0');

        $this->susulan([$this->item($c, 10, 100)])->assertOk();

        $baris = $this->barisNota();
        // A: aktual, tanpa surcharge.
        $this->assertEquals(15000.0, (float) $baris[0]->biaya_cetak, 0.01);
        $this->assertEquals(0.0, (float) $baris[0]->biaya_minimum, 0.01);
        // B: surcharge lama 15.000 dicabut, surcharge segar 0,2 x 50.000 = 10.000.
        $this->assertEquals(30000.0, (float) $baris[1]->biaya_cetak, 0.01, '20.000 aktual + 10.000 surcharge segar');
        $this->assertEquals(10000.0, (float) $baris[1]->biaya_minimum, 0.01);
        $this->assertEquals(1, (int) $baris[1]->is_minimum_order);
        $this->assertStringContainsString('selisih 0.2 m', (string) $baris[1]->catatan_produksi);
        $this->assertStringNotContainsString('selisih 0.3 m', (string) $baris[1]->catatan_produksi, 'fragmen surcharge lama tidak menumpuk');
        // C: aktual murni.
        $this->assertEquals(5000.0, (float) $baris[2]->biaya_cetak, 0.01);
        $this->assertEquals(0.0, (float) $baris[2]->biaya_minimum, 0.01);
        $this->assertEquals(50000.0, $this->totalNota(), 0.01, 'total = minimum kelompok 1,0 (0,8 x 50.000 + 0,2 x 50.000)');
        $this->assertJurnalSinkron();
    }

    /**
     * Produk yang SAMA jadi >= 2 baris dalam satu nota (baris lama + susulan):
     * minimum ditiadakan (keputusan owner 2026-11-14) - surcharge lama
     * dicabut, semua baris luas aktual.
     */
    public function test_susulan_produk_sama_jadi_dua_baris_minimum_ditiadakan(): void
    {
        $a = $this->makeProduk('Outdoor A', 'Group X', 1.0);
        $b = $this->makeProduk('Outdoor B', 'Group X', 1.0);
        $c = $this->makeProduk('Outdoor C', 'Group X', 1.0);
        $this->buatNota([$this->item($a, 30, 100), $this->item($b, 40, 100)]);
        $this->assertEquals(50000.0, $this->totalNota(), 0.01);

        // Susulan: produk A (produk sama, jadi baris ke-2 -> minimum ditiadakan untuk A)
        // + satu produk BARU sekelompok (C) supaya kelompok >=2 item: A dan C
        // B membawa selisih segar gabungan {B,C} (0,5); A bebas minimum.
        $this->susulan([$this->item($a, 20, 100), $this->item($c, 10, 100)])->assertOk();

        $baris = $this->barisNota();
        $this->assertCount(4, $baris);
        // A lama dan A susulan: minimum ditiadakan - luas aktual murni.
        $this->assertEquals(15000.0, (float) $baris[0]->biaya_cetak, 0.01);
        $this->assertEquals(0.0, (float) $baris[0]->biaya_minimum, 0.01);
        $this->assertEquals(10000.0, (float) $baris[2]->biaya_cetak, 0.01);
        $this->assertEquals(0.0, (float) $baris[2]->biaya_minimum, 0.01);
        $this->assertEquals(0, (int) $baris[2]->is_minimum_order);
        // B: surcharge lama dicabut, surcharge segar 0,5 x 50.000 = 25.000 (grup {B,C} = 0,5).
        $this->assertEquals(45000.0, (float) $baris[1]->biaya_cetak, 0.01);
        $this->assertEquals(25000.0, (float) $baris[1]->biaya_minimum, 0.01);
        $this->assertEquals(1, (int) $baris[1]->is_minimum_order);
        $this->assertStringContainsString('selisih 0.5 m', (string) $baris[1]->catatan_produksi);
        // C: aktual murni.
        $this->assertEquals(5000.0, (float) $baris[3]->biaya_cetak, 0.01);
        $this->assertEquals(0.0, (float) $baris[3]->biaya_minimum, 0.01);
        $this->assertEquals(75000.0, $this->totalNota(), 0.01, 'total = 0,3+0,2+0,4+0,1 + selisih 0,5');
        $this->assertJurnalSinkron();
    }

    /**
     * EDIT melengkapi kelompok: nota A 0,3 (carrier 0,3) + B 0,4, lalu edit
     * menambah C 0,5. Baris lama dikirim ulang dengan biaya_cetak tersimpan
     * (A = 30.000 termasuk surcharge) -> server mencabut surcharge lama,
     * kelompok jadi cukup, semua baris aktual, total = 60.000.
     */
    public function test_edit_kelompok_jadi_cukup_surcharge_dicabut(): void
    {
        $a = $this->makeProduk('Outdoor A', 'Group X', 1.0);
        $b = $this->makeProduk('Outdoor B', 'Group X', 1.0);
        $c = $this->makeProduk('Outdoor C', 'Group X', 1.0);
        $this->buatNota([$this->item($a, 30, 100), $this->item($b, 40, 100)]);

        $lama = $this->barisNota();
        $items = [
            $this->barisEdit($lama[0], (float) $lama[0]->biaya_cetak),
            $this->barisEdit($lama[1], (float) $lama[1]->biaya_cetak),
            array_merge($this->item($c, 50, 100), ['id_detail' => null]),
        ];
        $this->postJson("/penjualan/{$this->idNota}/update-item", ['items' => $items])->assertOk();

        $baris = $this->barisNota();
        $this->assertCount(3, $baris);
        $this->assertEquals(15000.0, (float) $baris[0]->biaya_cetak, 0.01, 'surcharge lama dicabut dari baris lama');
        $this->assertEquals(0.0, (float) $baris[0]->biaya_minimum, 0.01);
        $this->assertEquals(0, (int) $baris[0]->is_minimum_order);
        $this->assertEquals(20000.0, (float) $baris[1]->subtotal, 0.01);
        $this->assertEquals(25000.0, (float) $baris[2]->subtotal, 0.01);
        $this->assertEquals(60000.0, $this->totalNota(), 0.01);
        $this->assertJurnalSinkron();
    }

    /**
     * EDIT re-save TANPA perubahan apa pun: surcharge lama dicabut lalu
     * dihitung ulang segar - total nota TIDAK bertambah (sebelum perbaikan,
     * tiap re-save menaikkan surcharge lagi).
     */
    public function test_edit_re_save_tanpa_perubahan_total_tidak_berubah(): void
    {
        $a = $this->makeProduk('Outdoor A', 'Group X', 1.0);
        $b = $this->makeProduk('Outdoor B', 'Group X', 1.0);
        $this->buatNota([$this->item($a, 30, 100), $this->item($b, 40, 100)]);
        $totalAwal = $this->totalNota();

        $lama = $this->barisNota();
        $items = [
            $this->barisEdit($lama[0], (float) $lama[0]->biaya_cetak),
            $this->barisEdit($lama[1], (float) $lama[1]->biaya_cetak),
        ];
        $this->postJson("/penjualan/{$this->idNota}/update-item", ['items' => $items])->assertOk();

        $this->assertEquals($totalAwal, $this->totalNota(), 0.01, 're-save tanpa perubahan tidak boleh menaikkan total');
        $baris = $this->barisNota();
        // Surcharge tetap tepat satu kali: 15.000 di baris terbesar (B, 0,4 m2).
        $this->assertEquals(0.0, (float) $baris[0]->biaya_minimum, 0.01);
        $this->assertEquals(15000.0, (float) $baris[0]->biaya_cetak, 0.01);
        $this->assertEquals(0, (int) $baris[0]->is_minimum_order);
        $this->assertEquals(15000.0, (float) $baris[1]->biaya_minimum, 0.01);
        $this->assertEquals(35000.0, (float) $baris[1]->biaya_cetak, 0.01);
        $this->assertEquals(1, (int) $baris[1]->is_minimum_order);
        $this->assertJurnalSinkron();
    }

    /**
     * EDIT: ukuran baris diubah 0,3 -> 1,0 m2 (kelompok jadi cukup tanpa selisih);
     * surcharge lama dicabut dari baris lama, tidak muncul di baris yang diubah.
     * Awal nota berisi 2 item sekelompok (1-item selain minimal ditolak).
     */
    public function test_edit_ukuran_menaikkan_kelompok_surcharge_dicabut(): void
    {
        $a = $this->makeProduk('Outdoor A', 'Group X', 1.0);
        $b = $this->makeProduk('Outdoor B', 'Group X', 1.0);
        $this->buatNota([$this->item($a, 30, 100), $this->item($b, 40, 100)]);
        $this->assertEquals(50000.0, $this->totalNota(), 0.01);

        $lama = $this->barisNota();
        // Ukuran A diubah 0,3 -> 1,0 m2: biaya cetak aktual 50.000 (client segar),
        // catatan di layar masih membawa fragmen lama.
        $items = [
            $this->barisEdit($lama[0], 50000.0, [100, 100]),
            $this->barisEdit($lama[1], (float) $lama[1]->biaya_cetak),
        ];
        $this->postJson("/penjualan/{$this->idNota}/update-item", ['items' => $items])->assertOk();

        $baris = $this->barisNota();
        $this->assertEquals(50000.0, (float) $baris[0]->biaya_cetak, 0.01);
        $this->assertEquals(0.0, (float) $baris[0]->biaya_minimum, 0.01);
        $this->assertEquals(0, (int) $baris[0]->is_minimum_order);
        // B: surcharge lama dicabut - kembali ke luas aktual 0,4 m2.
        $this->assertEquals(20000.0, (float) $baris[1]->biaya_cetak, 0.01);
        $this->assertEquals(0.0, (float) $baris[1]->biaya_minimum, 0.01);
        $this->assertEquals(0, (int) $baris[1]->is_minimum_order);
        $this->assertEquals(70000.0, $this->totalNota(), 0.01, 'total = 1,4 m2 aktual');
        $this->assertJurnalSinkron();
    }

    /** Susulan produk TANPA konfigurasi minimum: perilaku tidak berubah. */
    public function test_susulan_produk_tanpa_minimum_tidak_berubah(): void
    {
        $p = DB::table('produk')->insertGetId(['nama_produk' => 'Indoor', 'metode_hitung' => 'ukuran', 'status' => 'aktif', 'min_luas_total' => 0, 'kelompok_minimum' => null]);
        DB::table('bom_detail')->insert(['id_produk' => $p, 'id_bahan' => $this->bahanA, 'cara_hitung_pemakaian' => '(P*L)/10000', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('setting_harga')->insert(['id_produk' => $p, 'id_golongan' => $this->gol, 'range_min' => 0.01, 'harga_jual' => 20000]);
        $this->buatNota([$this->item($p, 50, 100, 1, 20000)]);
        $totalAwal = $this->totalNota();

        $this->susulan([$this->item($p, 40, 100, 1, 20000)])->assertOk();

        $baris = $this->barisNota();
        $this->assertCount(2, $baris);
        foreach ($baris as $r) {
            $this->assertEquals(0, (int) $r->is_minimum_order);
            $this->assertEquals(0.0, (float) $r->biaya_minimum, 0.01);
        }
        $this->assertEquals($totalAwal + 8000.0, $this->totalNota(), 0.01);
        $this->assertJurnalSinkron();
    }
}

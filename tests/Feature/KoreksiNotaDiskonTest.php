<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Koreksi nota (EDIT SELISIH) harus menghormati diskon nota.
 *
 * Sebelumnya updateItemNota() menulis total_bayar dari jumlah subtotal
 * mentah tanpa mengurangi transaksi.diskon, sehingga nota yang sudah
 * didiskon kasir (prosesBayar menulis total_bayar = subtotal - diskon)
 * yang kemudian dikoreksi selisihnya balik ke harga penuh - tanpa
 * peringatan apa pun ke kasir.
 */
class KoreksiNotaDiskonTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private int $idKonsumen;
    private int $idGol;
    private int $idProduk;
    private int $idNota;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        $this->admin = User::factory()->create(['id_role' => Role::factory()->create(['nama_role' => 'Administrator'])->id_role]);
        $this->idProduk = DB::table('produk')->insertGetId(['nama_produk' => 'Spanduk', 'metode_hitung' => 'quantity', 'status' => 'aktif']);
        $this->idGol = DB::table('golongan_konsumen')->insertGetId(['nama_golongan' => 'Retail', 'keterangan' => '-']);
        $this->idKonsumen = DB::table('konsumen')->insertGetId(['id_unique' => (string) \Illuminate\Support\Str::uuid(), 'nama_konsumen' => 'U Diskon', 'telepon' => '1', 'alamat' => '-', 'id_golongan' => $this->idGol]);
        DB::table('setting_harga')->insert(['id_produk' => $this->idProduk, 'id_golongan' => $this->idGol, 'range_min' => 0, 'harga_jual' => 10000]);
        DB::table('kas_harian')->insert(['tgl_kas' => now()->toDateString(), 'id_user' => $this->admin->id_user, 'saldo_awal' => 0, 'status_lock' => 'open']);

        // Nota: 2 item x 50.000 = 100.000, kasir memberi diskon 30.000
        // sehingga total tagihan yang sah menjadi 70.000.
        $res = $this->actingAs($this->admin)->postJson('/penjualan/store', [
            'id_konsumen' => $this->idKonsumen, 'id_golongan' => $this->idGol,
            'items' => [
                ['id_produk' => $this->idProduk, 'nama_file' => 'a.pdf', 'p' => 0, 'l' => 0, 'qty_input' => 5, 'biaya_cetak' => 50000],
                ['id_produk' => $this->idProduk, 'nama_file' => 'b.pdf', 'p' => 0, 'l' => 0, 'qty_input' => 5, 'biaya_cetak' => 50000],
            ],
        ])->assertOk();

        $this->idNota = (int) $res->json('id_transaksi');
        DB::table('transaksi')->where('id_transaksi', $this->idNota)->update(['diskon' => 30000, 'total_bayar' => 70000]);
    }

    private function itemsNota(): array
    {
        return DB::table('transaksi_detail')->where('id_transaksi', $this->idNota)
            ->orderBy('id_detail')->get()
            ->map(fn ($d) => [
                'id_detail' => (int) $d->id_detail,
                'id_produk' => (int) $d->id_produk,
                'nama_file' => $d->nama_file,
                'p' => (float) $d->p_ukuran,
                'l' => (float) $d->l_ukuran,
                'qty_input' => (float) $d->qty,
                'biaya_cetak' => (float) $d->biaya_cetak,
                'biaya_desain' => (float) $d->biaya_desain,
                'finishing_list' => [],
            ])->all();
    }

    public function test_koreksi_tetap_mengurangi_diskon_dari_total_nota(): void
    {
        // Item dikirim APA ADANYA (operator menyimpan tanpa mengubah item).
        $this->actingAs($this->admin)
            ->postJson("/penjualan/{$this->idNota}/update-item", ['items' => $this->itemsNota()])
            ->assertOk()
            ->assertJsonPath('total_baru', 70000);

        $total = (float) DB::table('transaksi')->where('id_transaksi', $this->idNota)->value('total_bayar');
        $this->assertSame(70000.0, $total, 'Koreksi nota tidak boleh membuang diskon 30.000 dari total tagihan');
        $this->assertSame('30000.00', (string) DB::table('transaksi')->where('id_transaksi', $this->idNota)->value('diskon'));
    }

    public function test_guard_total_lebih_kecil_dari_terbayar_memakai_total_net_diskon(): void
    {
        // Konsumen sudah membayar 100.000, lebih besar dari total net
        // (100.000 - diskon 30.000 = 70.000): koreksi yang menghasilkan total
        // net lebih kecil dari pembayaran harus ditolak, bukan lolos karena
        // dihitung dari subtotal mentah.
        DB::table('pembayaran_riwayat')->insert([
            'id_transaksi' => $this->idNota, 'tgl_bayar' => now(), 'nominal' => 100000,
            'metode' => 'Tunai', 'uang_diterima' => 100000, 'kembalian' => 0,
        ]);

        $this->actingAs($this->admin)
            ->postJson("/penjualan/{$this->idNota}/update-item", ['items' => $this->itemsNota()])
            ->assertStatus(422);
    }
}

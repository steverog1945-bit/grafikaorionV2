<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Badge angka di tab divisi (SPK & Produksi) harus menunjukkan pekerjaan
 * yang benar-benar ada di antrean SPK/Produksi: status_nota Aktif +
 * status_order Diterima Kasir. Sebelumnya hanya 'status_nota != Dibatalkan'
 * - Antre milik transaksi yang belum dibuka kasir ikut dihitung sehingga
 * badge menepati angka yang dijanjikan tapi tidak pernah bisa ditampilkan
 * di tabel ("3" padahal tabel SPK divisi itu kosong untuk semua mode).
 */
class DivisiTabBadgeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private int $div;
    private int $idKonsumen;
    private int $idProduk;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['id_role' => Role::factory()->create(['nama_role' => 'Administrator'])->id_role]);
        $this->div = DB::table('mesin')->insertGetId(['nama_mesin' => 'FLORA OUTDOOR', 'tipe_mesin' => 'Test', 'tampil_divisi' => 1]);
        $g = DB::table('golongan_konsumen')->insertGetId(['nama_golongan' => 'Retail']);
        $this->idKonsumen = DB::table('konsumen')->insertGetId(['id_unique' => (string) \Illuminate\Support\Str::uuid(), 'nama_konsumen' => 'Toko', 'telepon' => '1', 'alamat' => '-', 'id_golongan' => $g]);
        $this->idProduk = DB::table('produk')->insertGetId(['nama_produk' => 'Banner', 'metode_hitung' => 'quantity', 'status' => 'Aktif', 'id_mesin' => $this->div]);
    }

    private function buatItem(string $statusOrder, string $statusNota, string $statusSpk): void
    {
        $t = DB::table('transaksi')->insertGetId(['nomor_nota' => 'N-' . uniqid(), 'tipe_order' => 'Reguler', 'id_konsumen' => $this->idKonsumen, 'id_user_kasir' => $this->admin->id_user, 'tgl_order' => now(), 'total_bayar' => 100000, 'status_pembayaran' => 'Belum Bayar', 'status_nota' => $statusNota, 'status_order' => $statusOrder]);
        DB::table('transaksi_detail')->insertGetId(['id_transaksi' => $t, 'id_produk' => $this->idProduk, 'nama_file' => 'FILE', 'p_ukuran' => 0, 'l_ukuran' => 0, 'qty' => 1, 'subtotal' => 100000, 'status_spk' => $statusSpk]);
    }

    public function test_badge_divisi_menghitung_hanya_antre_yang_masuk_antrian_spk(): void
    {
        // 1 job yang SAH dihitung (Antre + Aktif + Diterima Kasir)
        $this->buatItem('Diterima Kasir', 'Aktif', 'Antre');
        // 3 job yang BUKAN antrean SPK: belum dibuka kasir / bukan Aktif
        $this->buatItem('Menunggu Kasir', 'Aktif', 'Antre');
        $this->buatItem('Diterima Kasir', 'Dibatalkan', 'Antre');
        $this->buatItem('Menunggu Kasir', 'Dibatalkan', 'Antre');

        $this->actingAs($this->admin)
            ->get('/spk?divisi=' . $this->div)
            ->assertOk()
            ->assertSee('FLORA OUTDOOR', false)
            ->assertDontSee('FLORA OUTDOOR <span', false);

        $html = $this->actingAs($this->admin)->get('/spk?divisi=' . $this->div)->getContent();
        $m = [];
        preg_match('/FLORA OUTDOOR\s*<span[^>]*>(\d+)<\/span>/', $html, $m);
        $this->assertSame('1', $m[1] ?? null, 'Badge divisi harus menghitung 1 pekerjaan (Antre + Aktif + Diterima Kasir); HTML tab: ' . substr($html, strpos($html, 'FLORA OUTDOOR'), 300));
    }
}

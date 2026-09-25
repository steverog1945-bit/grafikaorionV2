<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class KasirHargaSatuanTest extends TestCase
{
    use RefreshDatabase;

    public function test_kolom_harga_form_pembayaran_menampilkan_biaya_cetak_per_satuan_bukan_nol(): void
    {
        $admin = User::factory()->create(['id_role' => Role::factory()->create(['nama_role' => 'Administrator'])->id_role]);
        $g = DB::table('golongan_konsumen')->insertGetId(['nama_golongan' => 'Retail', 'keterangan' => '-']);
        $k = DB::table('konsumen')->insertGetId(['nama_konsumen' => 'U', 'telepon' => '1', 'alamat' => '-', 'id_golongan' => $g]);
        $p = DB::table('produk')->insertGetId(['nama_produk' => 'Stiker Uji', 'metode_hitung' => 'quantity', 'status' => 'Aktif']);
        $t = DB::table('transaksi')->insertGetId(['nomor_nota' => 'N-KSR-1', 'tipe_order' => 'Reguler', 'id_konsumen' => $k, 'id_user_kasir' => $admin->id_user, 'tgl_order' => now(), 'total_bayar' => 130000, 'status_pembayaran' => 'Belum Bayar', 'status_nota' => 'Aktif']);
        DB::table('transaksi_detail')->insert([
            'id_transaksi' => $t, 'id_produk' => $p, 'nama_file' => 'a', 'p_ukuran' => 0, 'l_ukuran' => 0, 'qty' => 4,
            'biaya_cetak' => 100000, 'biaya_desain' => 10000, 'biaya_finishing' => 20000, 'subtotal' => 130000, 'status_spk' => 'Antre',
        ]);
        DB::table('kas_harian')->insert(['tgl_kas' => now()->toDateString(), 'id_user' => $admin->id_user, 'saldo_awal' => 0, 'status_lock' => 'open']);

        // Biaya cetak 100.000 / 4 pcs = Rp 25.000 per satuan
        $this->actingAs($admin)->get("/kasir/bayar-form/{$t}")->assertOk()->assertSee('Rp 25.000');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DivisiOtomatisTest extends TestCase
{
    use RefreshDatabase;

    public function test_mesin_baru_langsung_jadi_divisi_di_spk_dan_produksi_tanpa_order(): void
    {
        $admin = User::factory()->create(['id_role' => Role::factory()->create(['nama_role' => 'Administrator'])->id_role]);
        DB::table('mesin')->insert(['nama_mesin' => 'MESIN BARU XYZ', 'tipe_mesin' => 'Uji']);

        $this->actingAs($admin)->get('/spk')->assertOk()->assertSee('MESIN BARU XYZ');
        $this->actingAs($admin)->get('/produksi')->assertOk()->assertSee('MESIN BARU XYZ');
    }

    public function test_filter_divisi_produksi_membatasi_item_sesuai_mesin(): void
    {
        $admin = User::factory()->create(['id_role' => Role::factory()->create(['nama_role' => 'Administrator'])->id_role]);
        $m1 = DB::table('mesin')->insertGetId(['nama_mesin' => 'M-SATU', 'tipe_mesin' => 'x']);
        $m2 = DB::table('mesin')->insertGetId(['nama_mesin' => 'M-DUA', 'tipe_mesin' => 'x']);
        $g = DB::table('golongan_konsumen')->insertGetId(['nama_golongan' => 'Retail', 'keterangan' => '-']);
        $k = DB::table('konsumen')->insertGetId(['nama_konsumen' => 'Pelanggan Uji', 'telepon' => '1', 'alamat' => '-', 'id_golongan' => $g]);
        $t = DB::table('transaksi')->insertGetId(['nomor_nota' => 'N-1', 'tipe_order' => 'Reguler', 'id_konsumen' => $k, 'id_user_kasir' => $admin->id_user, 'tgl_order' => now(), 'total_bayar' => 1, 'status_pembayaran' => 'Belum Bayar', 'status_nota' => 'Aktif']);
        foreach ([[$m1, 'FILE-SATU'], [$m2, 'FILE-DUA']] as [$m, $nama]) {
            $p = DB::table('produk')->insertGetId(['nama_produk' => "P{$m}", 'metode_hitung' => 'quantity', 'status' => 'Aktif', 'id_mesin' => $m]);
            DB::table('transaksi_detail')->insert(['id_transaksi' => $t, 'id_produk' => $p, 'nama_file' => $nama, 'p_ukuran' => 0, 'l_ukuran' => 0, 'qty' => 1, 'subtotal' => 1, 'status_spk' => 'Cetak']);
        }

        $this->actingAs($admin)->get("/produksi?divisi={$m1}")->assertOk()->assertSee('FILE-SATU')->assertDontSee('FILE-DUA');
    }
}

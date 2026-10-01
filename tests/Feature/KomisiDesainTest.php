<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class KomisiDesainTest extends TestCase
{
    use RefreshDatabase;

    private int $idProduk;
    private int $idKonsumen;
    private int $idGol;
    private User $admin;
    private User $desain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['id_role' => Role::factory()->create(['nama_role' => 'Administrator'])->id_role]);
        DB::table('roles')->where('nama_role', 'Administrator')->update(['boleh_lihat_harga' => true]);
        $rDesain = Role::factory()->create(['nama_role' => 'Operator Desain']);
        DB::table('roles')->where('id_role', $rDesain->id_role)->update(['boleh_lihat_harga' => true]);
        $this->desain = User::factory()->create(['id_role' => $rDesain->id_role]);

        $this->idGol = DB::table('golongan_konsumen')->insertGetId(['nama_golongan' => 'Retail', 'keterangan' => '-']);
        $this->idKonsumen = DB::table('konsumen')->insertGetId(['nama_konsumen' => 'U', 'telepon' => '1', 'alamat' => '-', 'id_golongan' => $this->idGol]);
        $this->idProduk = DB::table('produk')->insertGetId(['nama_produk' => 'Stiker Uji', 'metode_hitung' => 'ukuran', 'status' => 'Aktif']);
        DB::table('setting_harga')->insert(['id_produk' => $this->idProduk, 'id_golongan' => $this->idGol, 'range_min' => 0.01, 'harga_jual' => 50000]);
        DB::table('kas_harian')->insert(['tgl_kas' => now()->toDateString(), 'id_user' => $this->admin->id_user, 'saldo_awal' => 0, 'status_lock' => 'open']);
    }

    private function nota(User $pembuat, float $biayaDesain, ?int $idDesainer = null)
    {
        return $this->actingAs($pembuat)->postJson('/penjualan/store', ['id_konsumen' => $this->idKonsumen, 'id_golongan' => $this->idGol, 'items' => [[
            'id_produk' => $this->idProduk, 'nama_file' => 'a.jpg', 'p' => 100, 'l' => 100, 'qty_input' => 1, 'biaya_cetak' => 50000,
            'biaya_desain' => $biayaDesain, 'id_desainer' => $idDesainer,
        ]]]);
    }

    private function setKelas(float $persen): void
    {
        $id = DB::table('kelas_desainer')->insertGetId(['nama_kelas' => 'Kelas Uji', 'persen_komisi' => $persen, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('users')->where('id_user', $this->desain->id_user)->update(['id_kelas_desainer' => $id]);
    }

    public function test_komisi_dihitung_dari_kelas_saat_nota_dibuat_dan_tidak_berubah_setelahnya(): void
    {
        $this->setKelas(40);
        $this->nota($this->admin, 100000, $this->desain->id_user)->assertOk();

        $d = DB::table('transaksi_detail')->first();
        $this->assertEquals($this->desain->id_user, $d->id_desainer);
        $this->assertEquals(40, (float) $d->komisi_desain_persen);
        $this->assertEquals(40000, (float) $d->komisi_desain);
        $this->assertSame('Belum', $d->komisi_status);

        // Persen kelas diubah -> nota lama tetap.
        DB::table('kelas_desainer')->update(['persen_komisi' => 10]);
        $this->assertEquals(40000, (float) DB::table('transaksi_detail')->value('komisi_desain'));
    }

    public function test_operator_desain_yang_membuat_nota_otomatis_menjadi_desainer_dan_tanpa_biaya_tidak_ada_komisi(): void
    {
        $this->setKelas(50);
        foreach ([3, 41] as $id) {
            DB::table('menus')->insertOrIgnore(['id_menu' => $id, 'nama_menu' => "M{$id}", 'url_link' => "m{$id}", 'parent_id' => 0]);
            DB::table('role_permissions')->insert(['id_role' => $this->desain->id_role, 'id_menu' => $id, 'can_view' => 1, 'can_add' => 1, 'can_edit' => 0, 'can_delete' => 0]);
        }
        $this->nota($this->desain, 100000)->assertOk(); // desainer tidak dikirim -> otomatis dirinya sendiri
        $d = DB::table('transaksi_detail')->first();
        $this->assertEquals($this->desain->id_user, $d->id_desainer);
        $this->assertEquals(50000, (float) $d->komisi_desain);
        DB::table('transaksi_detail')->delete();
        $this->nota($this->desain, 0)->assertOk();
        $d = DB::table('transaksi_detail')->first();
        $this->assertNull($d->id_desainer);
        $this->assertEquals(0, (float) $d->komisi_desain);
        $this->assertNull($d->komisi_status);
    }

    public function test_laporan_bayar_komisi_membuat_pengeluaran_kas_kecil_dan_batal_mengembalikan_status(): void
    {
        $this->setKelas(40);
        $this->nota($this->admin, 100000, $this->desain->id_user)->assertOk();
        $this->nota($this->admin, 50000, null)->assertOk(); // tanpa desainer

        $this->actingAs($this->admin)->get('/laporan/biaya-desain')->assertOk()->assertSee('Tanpa desainer')->assertSee('40.000');

        // Kas Kecil kosong: komisi tidak bisa dibayar (uang fisik tidak bisa minus).
        $this->post('/laporan/biaya-desain/bayar', [
            'id_desainer' => $this->desain->id_user, 'tgl_dari' => now()->startOfMonth()->toDateString(), 'tgl_sampai' => now()->toDateString(),
        ])->assertSessionHas('error');
        \App\Services\AccountingService::postKasKecilMasuk('KK-SALDO', '3000', 100000);

        $this->post('/laporan/biaya-desain/bayar', [
            'id_desainer' => $this->desain->id_user, 'tgl_dari' => now()->startOfMonth()->toDateString(), 'tgl_sampai' => now()->toDateString(),
        ])->assertSessionHas('success');

        $kk = DB::table('kas_kecil')->first();
        $this->assertEquals(40000, (float) $kk->nominal);
        $this->assertSame('Keluar', $kk->jenis);
        $this->assertEquals(60000, (float) DB::table('jurnal_umum')->where('kode_akun', '1005')->sum(DB::raw('debet - kredit')));
        $this->assertEquals(40000, (float) DB::table('jurnal_umum')->where('kode_akun', '5210')->sum(DB::raw('debet - kredit')));
        $this->assertSame('Dibayar', DB::table('transaksi_detail')->whereNotNull('id_desainer')->value('komisi_status'));

        // Bayar ulang -> tidak ada yang perlu dibayar.
        $this->post('/laporan/biaya-desain/bayar', [
            'id_desainer' => $this->desain->id_user, 'tgl_dari' => now()->startOfMonth()->toDateString(), 'tgl_sampai' => now()->toDateString(),
        ])->assertSessionHas('error');

        // Batalkan pengeluaran (approval) -> komisi kembali Belum.
        $this->delete("/kas-kecil/{$kk->id}", ['alasan' => 'salah'])->assertSessionHas('success');
        $req = \App\Models\ApprovalRequest::where('tipe_aksi', 'batalkan_kas_kecil')->firstOrFail();
        $this->patch("/approval/{$req->id}/approve", ['catatan' => 'OK']);
        $this->assertSame('Belum', DB::table('transaksi_detail')->whereNotNull('id_desainer')->value('komisi_status'));
    }

    public function test_operator_desain_hanya_melihat_miliknya_dan_tidak_bisa_membayar(): void
    {
        $this->setKelas(40);
        $lain = User::factory()->create(['id_role' => $this->desain->id_role]);
        $this->nota($this->admin, 100000, $this->desain->id_user)->assertOk();
        $this->nota($this->admin, 70000, $lain->id_user)->assertOk();

        DB::table('menus')->insertOrIgnore(['id_menu' => 65, 'nama_menu' => 'Lap Desain', 'url_link' => 'laporan/biaya-desain', 'parent_id' => 0]);
        DB::table('role_permissions')->insert(['id_role' => $this->desain->id_role, 'id_menu' => 65, 'can_view' => 1, 'can_add' => 1, 'can_edit' => 0, 'can_delete' => 0]);

        $res = $this->actingAs($this->desain)->get('/laporan/biaya-desain')->assertOk();
        $res->assertSee('100.000')->assertDontSee('70.000');

        // Walau can_add dicentang, Operator Desain hanya-lihat di menu ini.
        $this->actingAs($this->desain)->post('/laporan/biaya-desain/bayar', [
            'id_desainer' => $this->desain->id_user, 'tgl_dari' => now()->toDateString(), 'tgl_sampai' => now()->toDateString(),
        ])->assertForbidden();
    }

    public function test_pengaturan_kelas_desainer(): void
    {
        $this->actingAs($this->admin);
        $this->post('/pengaturan-desainer/kelas', ['nama_kelas' => 'Junior', 'persen_komisi' => 25])->assertSessionHas('success');
        $id = DB::table('kelas_desainer')->where('nama_kelas', 'Junior')->value('id_kelas');
        $this->post("/pengaturan-desainer/anggota/{$this->desain->id_user}", ['id_kelas' => $id])->assertSessionHas('success');
        $this->delete("/pengaturan-desainer/kelas/{$id}")->assertSessionHas('error'); // masih dipakai
        $this->get('/pengaturan-desainer')->assertOk()->assertSee('Junior');
    }
}

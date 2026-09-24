<?php

namespace Tests\Feature;

use App\Models\ApprovalRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\DesignProofService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DesignProofTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private int $idDetail;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $role = Role::factory()->create(['nama_role' => 'Administrator']);
        $this->admin = User::factory()->create(['id_role' => $role->id_role]);
        $this->actingAs($this->admin);

        $idGol = DB::table('golongan_konsumen')->insertGetId(['nama_golongan' => 'Retail']);
        $idKonsumen = DB::table('konsumen')->insertGetId(['nama_konsumen' => 'Pak Andi', 'telepon' => '0811', 'alamat' => '-', 'id_golongan' => $idGol]);
        $idProduk = DB::table('produk')->insertGetId(['nama_produk' => 'Banner', 'metode_hitung' => 'ukuran']);
        $idTransaksi = DB::table('transaksi')->insertGetId([
            'nomor_nota' => 'INV-DP-001', 'tipe_order' => 'Reguler', 'id_konsumen' => $idKonsumen, 'id_user_kasir' => $this->admin->id_user,
            'tgl_order' => now(), 'total_bayar' => 100000, 'status_pembayaran' => 'Belum Bayar', 'status_nota' => 'Aktif', 'status_collecting' => 'Belum Diambil',
        ]);
        $this->idDetail = DB::table('transaksi_detail')->insertGetId([
            'id_transaksi' => $idTransaksi, 'id_produk' => $idProduk, 'nama_file' => 'banner.pdf', 'p_ukuran' => 100, 'l_ukuran' => 100,
            'qty' => 1, 'status_urgent' => 0, 'status_urgensi' => 'Normal', 'status_finishing' => 'Standar', 'subtotal' => 100000,
            'potongan' => 0, 'hpp_snapshot' => 0, 'harga_jual_snapshot' => 100000, 'status_spk' => 'Desain', 'biaya_cetak' => 100000, 'biaya_desain' => 0,
        ]);
    }

    private function unggah(string $nama = 'proof.jpg', ?string $catatan = null)
    {
        return $this->post("/design-proof/{$this->idDetail}/unggah", ['file' => UploadedFile::fake()->create($nama, 100, str_ends_with($nama, '.png') ? 'image/png' : 'image/jpeg'), 'catatan' => $catatan]);
    }

    private function idProofTerakhir(): int
    {
        return (int) DB::table('design_proofs')->orderByDesc('revisi_no')->value('id');
    }

    public function test_unggah_membuat_revisi_berurutan_dan_menggantikan_yang_belum_diputuskan(): void
    {
        $this->unggah('v1.jpg', 'draft awal')->assertSessionHas('success');
        $this->unggah('v2.png')->assertSessionHas('success');

        $proofs = DB::table('design_proofs')->orderBy('revisi_no')->get();
        $this->assertEquals([1, 2], $proofs->pluck('revisi_no')->all());
        $this->assertEquals(['Digantikan', 'Menunggu Review'], $proofs->pluck('status')->all());
        Storage::disk('public')->assertExists($proofs[1]->file_path);
        $this->assertEquals('v2.png', $proofs[1]->nama_file_asli);
    }

    public function test_file_tidak_valid_ditolak(): void
    {
        $this->post("/design-proof/{$this->idDetail}/unggah", ['file' => UploadedFile::fake()->create('virus.exe', 10)])->assertSessionHasErrors('file');
        $this->post("/design-proof/{$this->idDetail}/unggah", ['file' => UploadedFile::fake()->create('besar.pdf', 30000)])->assertSessionHasErrors('file');

        $this->assertEquals(0, DB::table('design_proofs')->count());
    }

    public function test_persetujuan_mengunci_desain_dan_unggahan_baru_ditolak(): void
    {
        $this->unggah();
        $this->patch('/design-proof/proof/' . $this->idProofTerakhir() . '/keputusan', ['keputusan' => 'Disetujui', 'kanal' => 'WhatsApp', 'catatan' => 'OK via chat'])
            ->assertSessionHas('success');

        $p = DB::table('design_proofs')->first();
        $this->assertEquals(['Disetujui', 'WhatsApp'], [$p->status, $p->keputusan_kanal]);
        $this->assertNotNull($p->keputusan_at);

        $this->unggah('ganti.jpg')->assertSessionHas('error');
        $this->assertEquals(1, DB::table('design_proofs')->count());
    }

    public function test_minta_revisi_wajib_catatan_lalu_revisi_berikutnya_bisa_diunggah(): void
    {
        $this->unggah();
        $id = $this->idProofTerakhir();

        $this->patch("/design-proof/proof/{$id}/keputusan", ['keputusan' => 'Revisi Diminta', 'kanal' => 'Langsung', 'catatan' => ''])->assertSessionHas('error');
        $this->assertEquals('Menunggu Review', DB::table('design_proofs')->value('status'));

        $this->patch("/design-proof/proof/{$id}/keputusan", ['keputusan' => 'Revisi Diminta', 'kanal' => 'Langsung', 'catatan' => 'Logo lebih besar'])->assertSessionHas('success');
        $this->unggah('v2.jpg')->assertSessionHas('success');

        $this->assertEquals(2, DB::table('design_proofs')->max('revisi_no'));
    }

    public function test_keputusan_ganda_atau_kanal_tidak_valid_ditolak(): void
    {
        $this->unggah();
        $id = $this->idProofTerakhir();

        $this->patch("/design-proof/proof/{$id}/keputusan", ['keputusan' => 'Disetujui', 'kanal' => 'Telepati'])->assertSessionHasErrors('kanal');
        $this->patch("/design-proof/proof/{$id}/keputusan", ['keputusan' => 'Disetujui', 'kanal' => 'Email']);
        $this->patch("/design-proof/proof/{$id}/keputusan", ['keputusan' => 'Revisi Diminta', 'kanal' => 'Email', 'catatan' => 'x'])->assertSessionHas('error');

        $this->assertEquals('Disetujui', DB::table('design_proofs')->value('status'));
    }

    public function test_buka_revisi_setelah_disetujui_lewat_approval_workflow(): void
    {
        $this->unggah();
        $this->patch('/design-proof/proof/' . $this->idProofTerakhir() . '/keputusan', ['keputusan' => 'Disetujui', 'kanal' => 'WhatsApp']);

        $this->post("/design-proof/{$this->idDetail}/ajukan-revisi", ['alasan' => 'Warna logo diganti'])->assertSessionHas('success');
        $this->post("/design-proof/{$this->idDetail}/ajukan-revisi", ['alasan' => 'Lagi'])->assertSessionHas('error');
        $this->assertEquals('Disetujui', DB::table('design_proofs')->value('status'), 'belum terbuka sebelum disetujui');

        $req = ApprovalRequest::where('tipe_aksi', 'buka_revisi_desain')->firstOrFail();
        $this->patch("/approval/{$req->id}/approve", ['catatan' => 'OK']);

        $this->assertEquals('Dibuka Kembali', DB::table('design_proofs')->value('status'));
        $this->unggah('rev2.jpg')->assertSessionHas('success');
        $this->assertEquals(2, DB::table('design_proofs')->max('revisi_no'));
    }

    public function test_ajukan_buka_revisi_hanya_untuk_desain_yang_disetujui(): void
    {
        $this->unggah();

        $this->post("/design-proof/{$this->idDetail}/ajukan-revisi", ['alasan' => 'x'])->assertSessionHas('error');
        $this->assertEquals(0, ApprovalRequest::count());
    }

    public function test_produksi_diblokir_untuk_item_berproof_yang_belum_disetujui_tapi_item_tanpa_proof_bebas(): void
    {
        // Item tanpa proof: alur lama tetap jalan.
        $this->patch("/produksi/{$this->idDetail}/simpan-qty", ['qty_cetak' => 1])->assertSessionHas('success');
        $this->assertEquals('Cetak', DB::table('transaksi_detail')->where('id_detail', $this->idDetail)->value('status_spk'));

        DB::table('transaksi_detail')->where('id_detail', $this->idDetail)->update(['status_spk' => 'Desain']);
        $this->unggah();
        $this->patch("/produksi/{$this->idDetail}/simpan-qty", ['qty_cetak' => 1])->assertSessionHas('error');
        $this->assertEquals('Desain', DB::table('transaksi_detail')->where('id_detail', $this->idDetail)->value('status_spk'));

        $this->patch('/design-proof/proof/' . $this->idProofTerakhir() . '/keputusan', ['keputusan' => 'Disetujui', 'kanal' => 'Langsung']);
        $this->patch("/produksi/{$this->idDetail}/simpan-qty", ['qty_cetak' => 1])->assertSessionHas('success');
        $this->assertTrue(DesignProofService::butuhPersetujuan($this->idDetail) === false);
    }

    public function test_halaman_daftar_dan_detail_render(): void
    {
        $this->get('/design-proof')->assertOk()->assertSee('INV-DP-001')->assertSee('Belum ada proof');

        $this->unggah('lihat.jpg', 'catatan desainer');
        $this->get('/design-proof?status=Menunggu Review')->assertOk()->assertSee('INV-DP-001');
        $this->get("/design-proof/{$this->idDetail}")->assertOk()->assertSee('Revisi #1')->assertSee('catatan desainer')->assertSee('Catat Keputusan Pelanggan');
        $this->get('/design-proof/999999')->assertNotFound();
    }

    public function test_akses_desain_untuk_operator_produksi_tapi_tidak_untuk_gudang(): void
    {
        $roleOp = Role::factory()->create(['nama_role' => 'Operator Produksi']);
        $op = User::factory()->create(['id_role' => $roleOp->id_role]);
        $roleGudang = Role::factory()->create(['nama_role' => 'Gudang']);
        $gudang = User::factory()->create(['id_role' => $roleGudang->id_role]);
        DB::table('menus')->insertOrIgnore(['id_menu' => 23, 'nama_menu' => 'Produksi', 'url_link' => 'produksi', 'parent_id' => 0]);
        DB::table('role_permissions')->insert(['id_role' => $roleOp->id_role, 'id_menu' => 23]);

        $this->actingAs($op)->get('/design-proof')->assertOk();
        $this->actingAs($gudang)->get('/design-proof')->assertForbidden();
    }
}

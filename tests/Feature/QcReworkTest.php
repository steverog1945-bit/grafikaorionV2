<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Services\QcService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QcReworkTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private int $idBahan;
    private int $idDetail;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::factory()->create(['nama_role' => 'Administrator']);
        $this->admin = User::factory()->create(['id_role' => $role->id_role]);
        $this->actingAs($this->admin);

        $idMesin = DB::table('mesin')->insertGetId(['nama_mesin' => 'Mesin', 'tipe_mesin' => 'Digital']);
        $idKategori = DB::table('kategori')->insertGetId(['nama_kategori' => 'Kategori']);
        $this->idBahan = DB::table('bahan_baku')->insertGetId([
            'kode_bahan' => 'BHN-1', 'nama_bahan' => 'Kertas Stiker', 'stok' => 100, 'harga_rata_rata' => 10000, 'nilai_konversi' => 1,
            'id_mesin' => $idMesin, 'id_kategori' => $idKategori, 'satuan_stok' => 'lembar', 'status' => 'aktif',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $idProduk = DB::table('produk')->insertGetId(['nama_produk' => 'Stiker', 'metode_hitung' => 'quantity']);
        DB::table('bom_detail')->insert(['id_produk' => $idProduk, 'id_bahan' => $this->idBahan, 'cara_hitung_pemakaian' => 'Q']);

        $idGol = DB::table('golongan_konsumen')->insertGetId(['nama_golongan' => 'Retail']);
        $idKonsumen = DB::table('konsumen')->insertGetId([
            'nama_konsumen' => 'Budi', 'telepon' => '08123', 'alamat' => '-', 'id_golongan' => $idGol,
        ]);
        $idTransaksi = DB::table('transaksi')->insertGetId([
            'nomor_nota' => 'INV-QC-001', 'tipe_order' => 'Reguler', 'id_konsumen' => $idKonsumen, 'id_user_kasir' => $this->admin->id_user,
            'tgl_order' => now(), 'total_bayar' => 500000, 'status_pembayaran' => 'Belum Bayar', 'status_nota' => 'Aktif',
            'status_collecting' => 'Belum Diambil',
        ]);
        $this->idDetail = DB::table('transaksi_detail')->insertGetId([
            'id_transaksi' => $idTransaksi, 'id_produk' => $idProduk, 'nama_file' => 'stiker.pdf', 'p_ukuran' => 1, 'l_ukuran' => 1,
            'qty' => 100, 'qty_cetak' => 100, 'status_urgent' => 0, 'status_urgensi' => 'Normal', 'status_finishing' => 'Standar',
            'subtotal' => 500000, 'potongan' => 0, 'hpp_snapshot' => 0, 'harga_jual_snapshot' => 5000, 'status_spk' => 'Selesai',
            'biaya_cetak' => 500000, 'biaya_desain' => 0,
        ]);
    }

    private function saldo(string $kodeAkun): float
    {
        return (float) DB::table('jurnal_umum')->where('kode_akun', $kodeAkun)->sum(DB::raw('debet - kredit'));
    }

    public function test_qc_dengan_reject_membuka_rework(): void
    {
        $hasil = QcService::catat($this->idDetail, 100, 5, ['cacat_cetak' => true, 'bukan_poin' => true], 'Ada garis');

        $this->assertStringStartsWith('QC-', $hasil['nomor']);
        $qc = DB::table('qc_inspeksi')->first();
        $this->assertEquals([95, 5, 'Rework'], [$qc->qty_lolos, $qc->qty_reject, $qc->hasil]);
        $this->assertEquals(['cacat_cetak' => true], json_decode($qc->checklist, true), 'poin di luar daftar dibuang');

        $rw = DB::table('rework')->first();
        $this->assertEquals([5, 'Dibuka'], [$rw->qty_rework, $rw->status]);
        $this->assertStringStartsWith('RW-', $rw->nomor);
    }

    public function test_qc_lulus_tidak_membuka_rework(): void
    {
        QcService::catat($this->idDetail, 100, 0, [], null);

        $this->assertEquals('Lulus', DB::table('qc_inspeksi')->value('hasil'));
        $this->assertEquals(0, DB::table('rework')->count());
    }

    public function test_validasi_qc(): void
    {
        foreach ([[10, 11], [0, 0]] as [$diperiksa, $reject]) {
            try {
                QcService::catat($this->idDetail, $diperiksa, $reject, [], null);
                $this->fail('Seharusnya ditolak');
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage());
            }
        }

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('melebihi qty produksi');
        QcService::catat($this->idDetail, 101, 0, [], null);
    }

    public function test_item_batal_atau_antre_tidak_bisa_di_qc(): void
    {
        DB::table('transaksi_detail')->where('id_detail', $this->idDetail)->update(['status_spk' => 'Antre']);

        $this->expectException(\RuntimeException::class);
        QcService::catat($this->idDetail, 10, 0, [], null);
    }

    public function test_proses_rework_mengeluarkan_material_bom_dan_menjurnal_beban(): void
    {
        $r = QcService::catat($this->idDetail, 100, 5, [], null);

        $biaya = QcService::prosesRework($r['id_rework']);

        $this->assertEquals(50000, $biaya); // 5 lembar x Rp 10.000
        $this->assertEquals(95, (float) DB::table('bahan_baku')->where('id_bahan', $this->idBahan)->value('stok'));
        $this->assertDatabaseHas('kartu_stok', ['id_bahan' => $this->idBahan, 'tipe' => 'OUT', 'qty' => 5, 'stok_sebelum' => 100, 'stok_sesudah' => 95]);
        $this->assertEquals(50000, $this->saldo('5200'));
        $this->assertEquals(-50000, $this->saldo('1200'));
        $this->assertEquals(['Selesai', 50000.0], [DB::table('rework')->value('status'), (float) DB::table('rework')->value('biaya_material')]);

        $this->expectException(\RuntimeException::class);
        QcService::prosesRework($r['id_rework']);
    }

    public function test_hasil_rework_boleh_diperiksa_ulang_hanya_setelah_rework_selesai(): void
    {
        QcService::catat($this->idDetail, 100, 5, [], null);

        try {
            QcService::catat($this->idDetail, 5, 0, [], null);
            $this->fail('Belum boleh: rework belum diproses');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('melebihi', $e->getMessage());
        }

        QcService::prosesRework((int) DB::table('rework')->value('id'));
        $hasil = QcService::catat($this->idDetail, 5, 0, [], 'Ulang OK');

        $this->assertEquals('Lulus', DB::table('qc_inspeksi')->where('id', $hasil['id_qc'])->value('hasil'));
    }

    public function test_stok_tidak_cukup_menggagalkan_rework_tanpa_efek_samping(): void
    {
        $r = QcService::catat($this->idDetail, 100, 5, [], null);
        DB::table('bahan_baku')->where('id_bahan', $this->idBahan)->update(['stok' => 2]);

        try {
            QcService::prosesRework($r['id_rework']);
            $this->fail('Seharusnya gagal');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('tidak cukup', $e->getMessage());
        }

        $this->assertEquals('Dibuka', DB::table('rework')->value('status'));
        $this->assertEquals(2, (float) DB::table('bahan_baku')->where('id_bahan', $this->idBahan)->value('stok'));
        $this->assertEquals(0, DB::table('jurnal_umum')->count());
    }

    public function test_halaman_qc_render_dan_alur_lewat_http(): void
    {
        $this->get('/qc')->assertOk()->assertSee('QC & Rework');
        $this->get('/qc/create')->assertOk()->assertSee('INV-QC-001');

        $this->post('/qc', ['id_detail' => $this->idDetail, 'qty_diperiksa' => 100, 'qty_reject' => 5])->assertRedirect(route('qc.index'));
        $this->get('/qc')->assertOk()->assertSee('Proses Rework')->assertSee('INV-QC-001');

        $idRw = DB::table('rework')->value('id');
        $this->post("/qc/rework/{$idRw}/proses")->assertSessionHas('success');
        $this->assertEquals('Selesai', DB::table('rework')->value('status'));
    }

    public function test_akses_qc_sesuai_menu_produksi(): void
    {
        $roleOp = Role::factory()->create(['nama_role' => 'Operator Produksi']);
        $op = User::factory()->create(['id_role' => $roleOp->id_role]);
        $roleKasir = Role::factory()->create(['nama_role' => 'Kasir']);
        $kasir = User::factory()->create(['id_role' => $roleKasir->id_role]);
        DB::table('menus')->insertOrIgnore(['id_menu' => 23, 'nama_menu' => 'Produksi', 'url_link' => 'produksi', 'parent_id' => 0]);
        DB::table('role_permissions')->insert(['id_role' => $roleOp->id_role, 'id_menu' => 23]);

        $this->actingAs($op)->get('/qc')->assertOk();
        $this->actingAs($kasir)->get('/qc')->assertForbidden();
    }
}

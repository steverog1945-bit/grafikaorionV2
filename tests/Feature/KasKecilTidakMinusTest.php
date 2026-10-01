<?php

namespace Tests\Feature;

use App\Models\ApprovalRequest;
use App\Models\JerseyOrder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Kas Kecil adalah uang fisik terpisah dari laci: saldonya tidak boleh minus lewat jalur mana pun.
 *   - membatalkan pemasukan (saldo awal, setoran pemilik) yang uangnya sudah terpakai ditolak;
 *   - void pembayaran jersey tunai yang uangnya sudah terpakai ditolak;
 *   - kategori tanpa akun tidak bisa dipakai (catatan tanpa jurnal tidak mengurangi saldo, jadi penjaga saldo terlewati).
 */
class KasKecilTidakMinusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['id_role' => Role::factory()->create(['nama_role' => 'Administrator'])->id_role]));
    }

    private function saldo(): float
    {
        return round((float) DB::table('jurnal_umum')->where('kode_akun', '1005')->sum(DB::raw('debet - kredit')), 2);
    }

    private function kategori(string $nama): int
    {
        return (int) DB::table('kategori_kas_kecil')->where('nama_kategori', $nama)->value('id_kategori_kas_kecil');
    }

    private function keluar(float $nominal): int
    {
        $this->post('/kas-kecil', ['tanggal' => now()->toDateString(), 'jenis' => 'Keluar', 'id_kategori' => $this->kategori('Beban Perlengkapan Kantor'),
            'keterangan' => 'Beli ATK', 'nominal' => $nominal])->assertSessionHas('success');

        return (int) DB::table('kas_kecil')->where('jenis', 'Keluar')->latest('id')->value('id');
    }

    private function batalkan(int $id)
    {
        $this->delete("/kas-kecil/{$id}", ['alasan' => 'uji'])->assertSessionHas('success');
        $req = ApprovalRequest::where('tipe_aksi', 'batalkan_kas_kecil')->where('referensi_id', $id)->latest('id')->firstOrFail();

        return $this->patch("/approval/{$req->id}/approve", ['catatan' => 'ok']);
    }

    public function test_batal_saldo_awal_yang_sudah_terpakai_ditolak(): void
    {
        $this->post('/kas-kecil/saldo-awal', ['tanggal' => now()->toDateString(), 'nominal' => 1000000])->assertSessionHas('success');
        $idAwal = (int) DB::table('kas_kecil')->where('sumber', 'saldo_awal')->value('id');
        $idKeluar = $this->keluar(800000);
        $this->assertEquals(200000, $this->saldo());

        $this->batalkan($idAwal)->assertSessionHas('error');

        $this->assertEquals(200000, $this->saldo(), 'saldo Kas Kecil menjadi minus');
        $this->assertTrue(DB::table('kas_kecil')->where('id', $idAwal)->exists());

        // Pengajuan yang gagal dieksekusi tetap menunggu keputusan; Administrator menolaknya.
        $gagal = ApprovalRequest::where('tipe_aksi', 'batalkan_kas_kecil')->where('referensi_id', $idAwal)->firstOrFail();
        $this->assertSame('Menunggu', $gagal->status);
        $this->patch("/approval/{$gagal->id}/reject", ['catatan' => 'uang sudah terpakai']);

        // Kontrol: setelah pengeluarannya dibatalkan, uangnya kembali utuh dan saldo awal boleh dibatalkan.
        $this->batalkan($idKeluar)->assertSessionHas('success');
        $this->batalkan($idAwal)->assertSessionHas('success');
        $this->assertEquals(0, $this->saldo());
    }

    public function test_void_pembayaran_jersey_tunai_yang_sudah_terpakai_ditolak(): void
    {
        $o = JerseyOrder::create(['order_number' => 'JO-KK', 'order_date' => now()->toDateString(), 'customer_name' => 'Tim', 'additional_charge' => 500000]);
        $o->recalculateTotal();
        $this->post("/jersey/keuangan/{$o->id}/bayar", ['tanggal_bayar' => now()->toDateString(), 'jumlah' => 300000, 'metode_bayar' => 'Tunai']);
        $this->assertEquals(300000, $this->saldo());
        $this->keluar(250000);
        $idBayar = (int) DB::table('jersey_order_payments')->value('id');

        try {
            \App\Services\JerseyAccountingService::voidPembayaran($idBayar, 'salah input');
            $this->fail('void pembayaran tunai yang sudah terpakai seharusnya ditolak');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Kas Kecil', $e->getMessage());
        }

        $this->assertEquals(50000, $this->saldo());
        $this->assertSame(1, DB::table('jersey_order_payments')->whereNull('deleted_at')->count());
    }

    public function test_void_pembayaran_jersey_transfer_tidak_terhalang_saldo_kas_kecil(): void
    {
        $o = JerseyOrder::create(['order_number' => 'JO-TF', 'order_date' => now()->toDateString(), 'customer_name' => 'Tim', 'additional_charge' => 500000]);
        $o->recalculateTotal();
        $this->post("/jersey/keuangan/{$o->id}/bayar", ['tanggal_bayar' => now()->toDateString(), 'jumlah' => 300000, 'metode_bayar' => 'Transfer']);

        \App\Services\JerseyAccountingService::voidPembayaran((int) DB::table('jersey_order_payments')->value('id'), 'salah input');

        $this->assertSame(0, DB::table('jersey_order_payments')->whereNull('deleted_at')->count());
    }

    public function test_kategori_tanpa_akun_tidak_bisa_dipakai_dan_tidak_bisa_dibuat(): void
    {
        $this->post('/kas-kecil/saldo-awal', ['tanggal' => now()->toDateString(), 'nominal' => 100000]);
        $id = DB::table('kategori_kas_kecil')->insertGetId(['nama_kategori' => 'Tanpa Akun', 'jenis' => 'Keluar', 'kode_akun' => null]);

        $this->post('/kas-kecil', ['tanggal' => now()->toDateString(), 'jenis' => 'Keluar', 'id_kategori' => $id, 'keterangan' => 'x', 'nominal' => 50000])
            ->assertSessionHas('error');
        $this->assertSame(0, DB::table('kas_kecil')->where('jenis', 'Keluar')->count(), 'pengeluaran tanpa jurnal lolos melewati penjaga saldo');
        $this->assertEquals(100000, $this->saldo());

        $this->post('/kategori-kas-kecil', ['nama_kategori' => 'Baru', 'jenis' => 'Keluar'])->assertSessionHasErrors('kode_akun');
    }
}

<?php

namespace Tests\Feature;

use App\Models\ApprovalRequest;
use App\Models\JerseyOrder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class KasKecilBukuKasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $role = Role::factory()->create(['nama_role' => 'Administrator']);
        $this->actingAs(User::factory()->create(['id_role' => $role->id_role]));
    }

    private function saldo(string $kode): float
    {
        return (float) DB::table('jurnal_umum')->where('kode_akun', $kode)->sum(DB::raw('debet - kredit'));
    }

    public function test_pemasukan_manual_dijurnal_dr_kas_cr_akun_kategori_dan_tampil_di_halaman(): void
    {
        $kat = DB::table('kategori_kas_kecil')->where('nama_kategori', 'Pendapatan Lain-lain')->first();
        $this->assertSame('Masuk', $kat->jenis);

        $this->post('/kas-kecil', [
            'tanggal' => now()->toDateString(), 'jenis' => 'Masuk', 'id_kategori' => $kat->id_kategori_kas_kecil,
            'keterangan' => 'Jual kardus bekas', 'nominal' => 75000,
        ])->assertSessionHas('success');

        $this->assertEquals(75000, $this->saldo('1005'));
        $this->assertEquals(0, $this->saldo('1000'), 'laci tidak tersentuh');
        $this->assertEquals(-75000, $this->saldo('4200'));
        $this->get('/kas-kecil')->assertOk()->assertSee('Jual kardus bekas')->assertSee('Pemasukan Tunai');
    }

    public function test_jenis_harus_cocok_dengan_kategori(): void
    {
        $kat = DB::table('kategori_kas_kecil')->where('nama_kategori', 'Pendapatan Lain-lain')->first();

        $this->post('/kas-kecil', [
            'tanggal' => now()->toDateString(), 'jenis' => 'Keluar', 'id_kategori' => $kat->id_kategori_kas_kecil,
            'keterangan' => 'Salah jenis', 'nominal' => 1000,
        ])->assertSessionHasErrors('id_kategori');
        $this->assertSame(0, DB::table('kas_kecil')->count());
    }

    public function test_pembayaran_jersey_tercermin_di_kas_kecil_tidak_dijurnal_ganda_dan_hilang_saat_void(): void
    {
        $o = JerseyOrder::create(['order_number' => 'JO-T1', 'order_date' => now()->toDateString(), 'customer_name' => 'Tim', 'additional_charge' => 500000]);
        $o->recalculateTotal();

        $this->post("/jersey/keuangan/{$o->id}/bayar", ['tanggal_bayar' => now()->toDateString(), 'jumlah' => 200000, 'metode_bayar' => 'Transfer']);

        $baris = DB::table('kas_kecil')->where('sumber', 'jersey')->first();
        $this->assertSame('Masuk', $baris->jenis);
        $this->assertSame('Transfer', $baris->metode);
        $this->assertEquals(200000, (float) $baris->nominal);
        $this->assertEquals(200000, $this->saldo('1010'), 'jurnal hanya dari pembayaran jersey (Bank), tidak dobel');
        $this->assertEquals(0, $this->saldo('1005'));

        // Tidak bisa dibatalkan dari Kas Kecil.
        $this->delete("/kas-kecil/{$baris->id}")->assertSessionHas('error');
        $this->assertSame(0, ApprovalRequest::where('tipe_aksi', 'batalkan_kas_kecil')->count());

        // Void pembayaran jersey -> baris cermin ikut hilang.
        $idBayar = DB::table('jersey_order_payments')->value('id');
        \App\Services\JerseyAccountingService::voidPembayaran($idBayar, 'salah input');
        $this->assertSame(0, DB::table('kas_kecil')->where('sumber', 'jersey')->count());
    }

    public function test_isi_kas_kecil_dari_laci_memindahkan_saldo_dan_mengurangi_laci_tanpa_beban(): void
    {
        $kat = DB::table('kategori_kas_kecil')->where('nama_kategori', 'Isi dari Kas Laci')->first();
        $payload = ['tanggal' => now()->toDateString(), 'jenis' => 'Masuk', 'id_kategori' => $kat->id_kategori_kas_kecil, 'keterangan' => 'Modal kas kecil', 'nominal' => 200000];

        // Tanpa sesi kas aktif -> ditolak.
        $this->post('/kas-kecil', $payload)->assertSessionHas('error');
        $this->assertSame(0, DB::table('kas_kecil')->count());

        $idKas = DB::table('kas_harian')->insertGetId(['tgl_kas' => now()->toDateString(), 'id_user' => auth()->id(), 'saldo_awal' => 500000, 'status_lock' => 'open', 'created_at' => now(), 'updated_at' => now()]);
        $this->post('/kas-kecil', $payload)->assertSessionHas('success');

        $this->assertEquals(200000, $this->saldo('1005'));
        $this->assertEquals(-200000, $this->saldo('1000'));
        $this->assertEquals(0, $this->saldo('5100') + $this->saldo('4200'), 'bukan beban/pendapatan');
        $manual = DB::table('kas_manual')->where('id_kas', $idKas)->first();
        $this->assertSame('Keluar', $manual->status);
        $this->assertEquals(200000, (float) $manual->nominal);

        // Pembatalan (approval) mengembalikan laci dan membalik jurnal.
        $kk = DB::table('kas_kecil')->first();
        $this->delete("/kas-kecil/{$kk->id}", ['alasan' => 'salah'])->assertSessionHas('success');
        $req = ApprovalRequest::where('tipe_aksi', 'batalkan_kas_kecil')->firstOrFail();
        $this->patch("/approval/{$req->id}/approve", ['catatan' => 'OK']);
        $this->assertSame(0, DB::table('kas_manual')->count());
        $this->assertEquals(0.0, $this->saldo('1005') + 0);
        $this->assertEquals(0.0, $this->saldo('1000') + 0);
    }
}

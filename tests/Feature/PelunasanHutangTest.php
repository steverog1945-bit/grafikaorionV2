<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PelunasanHutangTest extends TestCase
{
    use RefreshDatabase;

    private int $idSup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['id_role' => Role::factory()->create(['nama_role' => 'Administrator'])->id_role]));
        $this->idSup = DB::table('supplier')->insertGetId(['nama_supplier' => 'PT Uji', 'kontak' => '-']);
    }

    private function faktur(string $no, float $total, string $jatuhTempo): int
    {
        $idGudang = DB::table('gudang')->first()->id_gudang ?? DB::table('gudang')->insertGetId(['nama_gudang' => 'Utama']);
        $idKed = DB::table('kedatangan_bahan')->insertGetId(['no_surat_jalan' => 'SJ-' . $no, 'id_supplier' => $this->idSup, 'id_gudang' => $idGudang, 'tgl_datang' => now()->toDateString(), 'status' => 'Selesai']);

        return DB::table('pembelian_bahan')->insertGetId([
            'id_kedatangan' => $idKed, 'no_faktur' => $no, 'tgl_transaksi' => now()->subDays(40)->toDateString(), 'tipe_pembelian' => 'Tempo', 'status_pembayaran' => 'Hutang',
            'jatuh_tempo_tgl' => $jatuhTempo, 'grand_total' => $total,
        ]);
    }

    private function saldo(string $kode): float
    {
        return (float) DB::table('jurnal_umum')->where('kode_akun', $kode)->sum(DB::raw('debet - kredit'));
    }

    public function test_info_pelunasan_faktur_dan_supplier(): void
    {
        $a = $this->faktur('F-A', 1000000, now()->subDays(5)->toDateString());
        $this->faktur('F-B', 500000, now()->addDays(10)->toDateString());

        $this->getJson("/pembelian-bahan/pelunasan-info?faktur={$a}")->assertOk()->assertJsonPath('sisa', 1000000)->assertJsonPath('fakturs.0.telat_hari', 5);
        $this->getJson("/pembelian-bahan/pelunasan-info?supplier={$this->idSup}")->assertOk()->assertJsonPath('sisa', 1500000)->assertJsonCount(2, 'fakturs');
    }

    public function test_bayar_dari_kas_kecil_dijurnal_ke_1005_dicatat_di_buku_kas_kecil_dan_ditolak_bila_saldo_kurang(): void
    {
        $f = $this->faktur('F-A', 1000000, now()->addDays(3)->toDateString());

        // Saldo kas kecil kosong -> ditolak.
        $this->patch("/pembelian-bahan/pelunasan/{$f}", ['nominal' => 100000, 'metode' => 'Kas Kecil'])->assertSessionHas('error');
        $this->assertSame(0, DB::table('pembayaran_hutang')->count());

        // Isi kas kecil (pemasukan manual) lalu bayar.
        $kat = DB::table('kategori_kas_kecil')->where('nama_kategori', 'Tambahan Modal / Setoran Pemilik')->first();
        $this->post('/kas-kecil', ['tanggal' => now()->toDateString(), 'jenis' => 'Masuk', 'id_kategori' => $kat->id_kategori_kas_kecil, 'keterangan' => 'Modal', 'nominal' => 300000]);
        $this->patch("/pembelian-bahan/pelunasan/{$f}", ['nominal' => 100000, 'metode' => 'Kas Kecil', 'tanggal' => now()->toDateString(), 'keterangan' => 'darurat'])->assertSessionHas('success');

        $this->assertEquals(200000, $this->saldo('1005'));
        $this->assertEquals(100000, $this->saldo('2000'));
        $baris = DB::table('kas_kecil')->where('sumber', 'hutang')->first();
        $this->assertSame('Keluar', $baris->jenis);
        $this->assertEquals(100000, (float) $baris->nominal);

        // Tidak bisa dibatalkan dari Kas Kecil; melebihi saldo ditolak.
        $this->delete("/kas-kecil/{$baris->id}")->assertSessionHas('error');
        $this->patch("/pembelian-bahan/pelunasan/{$f}", ['nominal' => 250000, 'metode' => 'Kas Kecil'])->assertSessionHas('error');
    }

    public function test_bayar_supplier_dibagi_ke_faktur_paling_lama_jatuh_tempo_dulu(): void
    {
        $lama = $this->faktur('F-LAMA', 400000, now()->subDays(20)->toDateString());
        $baru = $this->faktur('F-BARU', 600000, now()->addDays(20)->toDateString());

        $this->post("/pembelian-bahan/bayar-supplier/{$this->idSup}", ['nominal' => 700000, 'metode' => 'Tunai'])->assertSessionHas('success');

        $this->assertSame('Lunas', DB::table('pembelian_bahan')->where('id_pembelian', $lama)->value('status_pembayaran'));
        $this->assertSame('Hutang', DB::table('pembelian_bahan')->where('id_pembelian', $baru)->value('status_pembayaran'));
        $this->assertEquals(300000, (float) DB::table('pembayaran_hutang')->where('id_pembelian', $baru)->sum('nominal'));

        $this->post("/pembelian-bahan/bayar-supplier/{$this->idSup}", ['nominal' => 999999999, 'metode' => 'Tunai'])->assertSessionHas('error');
    }

    public function test_daftar_pembelian_menampilkan_hitungan_jatuh_tempo(): void
    {
        $this->faktur('F-TELAT', 100000, now()->subDays(4)->toDateString());
        $this->faktur('F-SEGERA', 200000, now()->addDays(3)->toDateString());

        $this->get('/pembelian-bahan')->assertOk()->assertSee('Telat 4 hari')->assertSee('3 hari lagi')->assertSee('Telat Jatuh Tempo');
        $this->get('/pembelian-bahan?jt=telat')->assertOk()->assertSee('F-TELAT')->assertDontSee('F-SEGERA');
    }
}

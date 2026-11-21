<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Guard pembayaran Transfer di jalur kasir: rekening bank tujuan WAJIB
 * diisi dan terdaftar di Master Bank.
 *
 * Sebelum guard ini, syaratnya hanya $request->filled('id_bank'), sehingga
 * POST tanpa field / dengan string kosong tercatat sebagai Transfer dengan
 * id_bank NULL. Identitas rekening hilang dari riwayat, nota, API, dan audit.
 */
class TransferWajibRekeningTest extends TestCase
{
    use RefreshDatabase;

    private object $nota;
    private object $kas;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::factory()->create([
            'id_role' => Role::factory()->create(['nama_role' => 'Administrator'])->id_role,
        ]);
        $this->actingAs($admin);

        $gol = DB::table('golongan_konsumen')->insertGetId(['nama_golongan' => 'Retail']);
        $kons = DB::table('konsumen')->insertGetId([
            'id_unique' => (string) \Illuminate\Support\Str::uuid(),
            'nama_konsumen' => 'A', 'telepon' => '1', 'alamat' => '-', 'id_golongan' => $gol,
        ]);
        $mesin = DB::table('mesin')->insertGetId(['nama_mesin' => 'M', 'tipe_mesin' => 'x']);
        $kat = DB::table('kategori')->insertGetId(['nama_kategori' => 'K', 'kode_kategori' => 'K']);
        $bahan = DB::table('bahan_baku')->insertGetId([
            'kode_bahan' => 'B', 'nama_bahan' => 'Flexi', 'id_mesin' => $mesin, 'id_kategori' => $kat,
            'stok' => 50, 'nilai_konversi' => 1, 'harga_rata_rata' => 0, 'satuan_stok' => 'm2', 'status' => 'aktif',
        ]);
        $jasa = DB::table('jasa')->insertGetId(['nama_jasa' => 'Press', 'harga_default' => 20000]);
        DB::table('kas_harian')->insert([
            'tgl_kas' => now()->toDateString(), 'id_user' => $admin->id_user,
            'saldo_awal' => 0, 'status_lock' => 'open', 'created_at' => now()->subMinute(),
        ]);
        $this->kas = DB::table('kas_harian')->first();

        $this->post('/penjualan-barang/store', ['id_konsumen' => $kons, 'items' => [
            ['tipe' => 'bahan', 'id_bahan' => $bahan, 'qty' => 3, 'harga' => 15000],
            ['tipe' => 'jasa', 'id_jasa' => $jasa, 'qty' => 1, 'harga' => 20000],
        ]])->assertSessionHasNoErrors();

        $this->nota = DB::table('transaksi')->latest('id_transaksi')->first();
        DB::table('transaksi')->where('id_transaksi', $this->nota->id_transaksi)
            ->update(['status_order' => 'Diterima Kasir']);
    }

    private function bayar(array $extra = [])
    {
        return $this->post('/kasir/proses-bayar', array_merge([
            'id_transaksi' => $this->nota->id_transaksi,
            'jumlah_bayar' => '25.000',
            'metode_pembayaran' => 'Transfer',
            'submit_id' => 'twr-' . uniqid(),
        ], $extra));
    }

    private function totalTransfer(): float
    {
        return (float) DB::table('kas_harian')->where('id_kas', $this->kas->id_kas)->value('total_transfer');
    }

    public function test_transfer_tanpa_rekening_ditolak_dan_tidak_menyisakan_jejak(): void
    {
        $jurnalSebelum = DB::table('jurnal_umum')->count();

        foreach ([[], ['id_bank' => ''], ['id_bank' => 0]] as $kasus) {
            $this->bayar($kasus)->assertSessionHas(
                'error',
                fn ($pesan) => str_contains($pesan, 'wajib memilih rekening bank tujuan')
            );
        }

        $this->assertSame(0, DB::table('pembayaran_riwayat')->count());
        $this->assertSame(0.0, $this->totalTransfer());
        $this->assertSame($jurnalSebelum, DB::table('jurnal_umum')->count());
        $this->assertSame('Belum Bayar', DB::table('transaksi')
            ->where('id_transaksi', $this->nota->id_transaksi)->value('status_pembayaran'));
    }

    public function test_transfer_dengan_rekening_terdaftar_diterima_dan_id_bank_tersimpan(): void
    {
        $idBank = $this->bankUji();

        $this->bayar(['id_bank' => $idBank])->assertSessionHas('success');

        $pembayaran = DB::table('pembayaran_riwayat')->first();
        $this->assertNotNull($pembayaran);
        $this->assertSame('Transfer', $pembayaran->metode);
        $this->assertSame($idBank, (int) $pembayaran->id_bank);
        $this->assertEquals(25000.0, $this->totalTransfer());
    }

    public function test_transfer_dengan_id_bank_tidak_terdaftar_ditolak(): void
    {
        $jurnalSebelum = DB::table('jurnal_umum')->count();

        $this->bayar(['id_bank' => 999999])->assertSessionHas(
            'error',
            fn ($pesan) => str_contains($pesan, 'Rekening bank tujuan tidak ditemukan')
        );

        $this->assertSame(0, DB::table('pembayaran_riwayat')->count());
        $this->assertSame($jurnalSebelum, DB::table('jurnal_umum')->count());
    }
}

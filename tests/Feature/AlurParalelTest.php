<?php

namespace Tests\Feature;

use App\Models\JerseyOrder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\TestCase;

/**
 * Lanjutan audit konkurensi dengan proses sungguhan (lihat PembayaranParalelTest) untuk alur lain yang
 * memakai pola "kunci lalu hitung": pembayaran order jersey, penomoran nota POS, dan pengembalian deposit.
 */
class AlurParalelTest extends TestCase
{
    use RefreshDatabase;

    /** Tanpa transaksi pembungkus: data harus ter-commit agar terlihat proses lain. Dibersihkan di tearDown. */
    protected $connectionsToTransact = [];

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['id_role' => Role::factory()->create(['nama_role' => 'Administrator'])->id_role]);
    }

    protected function tearDown(): void
    {
        $this->artisan('migrate:fresh');
        parent::tearDown();
    }

    /**
     * @param  array<int, array{uri:string, data:array, json?:bool}>  $permintaan  satu entri per proses
     * @return array<int, array{status:int, ok:bool, error:?string}>
     */
    private function tembak(array $permintaan): array
    {
        $db = config('database.connections.mysql');
        $env = [
            'APP_ENV' => 'testing', 'DB_CONNECTION' => 'mysql', 'DB_HOST' => $db['host'], 'DB_PORT' => (string) $db['port'],
            'DB_DATABASE' => $db['database'], 'DB_USERNAME' => $db['username'], 'DB_PASSWORD' => (string) $db['password'],
            'SESSION_DRIVER' => 'array', 'CACHE_STORE' => 'database', 'QUEUE_CONNECTION' => 'sync', 'APP_KEY' => config('app.key'),
        ];
        $mulai = sprintf('%.4f', microtime(true) + 3.0);
        $proses = [];
        foreach ($permintaan as $p) {
            $x = new Process([PHP_BINARY, base_path('tests/paralel/permintaan.php'), (string) $this->admin->id_user, $mulai, base64_encode(json_encode($p))], base_path(), $env);
            $x->setTimeout(60);
            $x->start();
            $proses[] = $x;
        }

        return array_map(function (Process $x) {
            $x->wait();
            $this->assertSame(0, $x->getExitCode(), 'Proses anak gagal: ' . $x->getErrorOutput() . $x->getOutput());

            return json_decode($x->getOutput(), true);
        }, $proses);
    }

    public function test_enam_pelunasan_order_jersey_bersamaan_hanya_satu_yang_tercatat(): void
    {
        $o = JerseyOrder::create(['order_number' => 'JO-PAR-1', 'order_date' => now()->toDateString(), 'customer_name' => 'Tim', 'additional_charge' => 500000]);
        $o->recalculateTotal();

        $hasil = $this->tembak(array_fill(0, 6, [
            'uri' => "/jersey/keuangan/{$o->id}/bayar",
            'data' => ['tanggal_bayar' => now()->toDateString(), 'jumlah' => 500000, 'metode_bayar' => 'Transfer'],
        ]));

        $this->assertSame(1, DB::table('jersey_order_payments')->count(), json_encode($hasil));
        $this->assertEquals(500000, (float) DB::table('jersey_order_payments')->sum('jumlah'));
        $this->assertEquals((float) DB::table('jurnal_umum')->sum('debet'), (float) DB::table('jurnal_umum')->sum('kredit'));
        $this->assertEquals(0, (float) DB::table('jurnal_umum')->where('kode_akun', '1100')->selectRaw('COALESCE(SUM(debet) - SUM(kredit), 0) s')->value('s'));
    }

    public function test_enam_nota_pos_bersamaan_mendapat_enam_nomor_berbeda(): void
    {
        $gol = DB::table('golongan_konsumen')->insertGetId(['nama_golongan' => 'Retail', 'keterangan' => '-']);
        $kons = DB::table('konsumen')->insertGetId(['nama_konsumen' => 'U', 'telepon' => '1', 'alamat' => '-', 'id_golongan' => $gol]);
        $produk = DB::table('produk')->insertGetId(['nama_produk' => 'Spanduk', 'metode_hitung' => 'quantity', 'status' => 'aktif']);
        DB::table('setting_harga')->insert(['id_produk' => $produk, 'id_golongan' => $gol, 'range_min' => 0, 'harga_jual' => 10000]);
        DB::table('kas_harian')->insert(['tgl_kas' => now()->toDateString(), 'id_user' => $this->admin->id_user, 'saldo_awal' => 0, 'status_lock' => 'open', 'created_at' => now()]);

        $permintaan = [];
        for ($i = 0; $i < 6; $i++) {
            $permintaan[] = ['uri' => '/penjualan/store', 'json' => true, 'data' => [
                'id_konsumen' => $kons, 'id_golongan' => $gol, 'submit_id' => 'par-' . $i,
                'items' => [['id_produk' => $produk, 'nama_file' => "a{$i}.pdf", 'p' => 0, 'l' => 0, 'qty_input' => 5, 'biaya_cetak' => 50000]],
            ]];
        }
        $hasil = $this->tembak($permintaan);

        $nomor = DB::table('transaksi')->pluck('nomor_nota');
        $this->assertSame(6, $nomor->count(), json_encode($hasil));
        $this->assertSame(6, $nomor->unique()->count(), 'Nomor nota kembar: ' . $nomor->toJson());
        $this->assertEquals((float) DB::table('jurnal_umum')->sum('debet'), (float) DB::table('jurnal_umum')->sum('kredit'));
    }

    public function test_satu_submit_id_dikirim_enam_kali_bersamaan_hanya_menjadi_satu_nota(): void
    {
        $gol = DB::table('golongan_konsumen')->insertGetId(['nama_golongan' => 'Retail', 'keterangan' => '-']);
        $kons = DB::table('konsumen')->insertGetId(['nama_konsumen' => 'U', 'telepon' => '1', 'alamat' => '-', 'id_golongan' => $gol]);
        $produk = DB::table('produk')->insertGetId(['nama_produk' => 'Spanduk', 'metode_hitung' => 'quantity', 'status' => 'aktif']);
        DB::table('setting_harga')->insert(['id_produk' => $produk, 'id_golongan' => $gol, 'range_min' => 0, 'harga_jual' => 10000]);
        DB::table('kas_harian')->insert(['tgl_kas' => now()->toDateString(), 'id_user' => $this->admin->id_user, 'saldo_awal' => 0, 'status_lock' => 'open', 'created_at' => now()]);

        $this->tembak(array_fill(0, 6, ['uri' => '/penjualan/store', 'json' => true, 'data' => [
            'id_konsumen' => $kons, 'id_golongan' => $gol, 'submit_id' => 'sama-persis',
            'items' => [['id_produk' => $produk, 'nama_file' => 'a.pdf', 'p' => 0, 'l' => 0, 'qty_input' => 5, 'biaya_cetak' => 50000]],
        ]]));

        $this->assertSame(1, DB::table('transaksi')->count());
    }

    public function test_pengembalian_deposit_bersamaan_tidak_melebihi_saldo(): void
    {
        $gol = DB::table('golongan_konsumen')->insertGetId(['nama_golongan' => 'Retail']);
        $kons = DB::table('konsumen')->insertGetId(['nama_konsumen' => 'U', 'telepon' => '1', 'alamat' => '-', 'id_golongan' => $gol]);
        DB::table('konsumen_deposit')->insert(['id_konsumen' => $kons, 'tipe' => 'Setoran', 'nominal' => 100000, 'metode_bayar' => 'Tunai', 'tgl_deposit' => now()->toDateString(), 'created_at' => now(), 'updated_at' => now()]);
        \App\Services\AccountingService::postSetoranDeposit('DEP-PAR-X', 100000, 'Tunai');

        $this->tembak(array_fill(0, 6, ['uri' => '/konsumen-deposit/kembalikan', 'data' => [
            'id_konsumen' => $kons, 'nominal' => 60000, 'metode_bayar' => 'Tunai', 'alasan' => 'Uji paralel',
        ]]));

        $this->assertEquals(60000, (float) DB::table('konsumen_deposit')->where('tipe', 'Pengembalian')->sum('nominal'));
        $this->assertEquals(40000, \App\Http\Controllers\KonsumenDepositController::saldoUntuk($kons));
    }
}

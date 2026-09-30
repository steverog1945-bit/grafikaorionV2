<?php

namespace Tests\Feature;

use App\Http\Controllers\KonsumenDepositController;
use App\Models\Role;
use App\Models\User;
use App\Services\AccountingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Pengembalian uang dari saldo deposit: hanya Administrator, tidak boleh melebihi saldo,
 * mengurangi saldo, jurnal seimbang (Dr Hutang Deposit / Cr Kas), tercatat di audit.
 */
class DepositPengembalianTest extends TestCase
{
    use RefreshDatabase;

    private int $idKons;

    protected function setUp(): void
    {
        parent::setUp();
        $gol = DB::table('golongan_konsumen')->insertGetId(['nama_golongan' => 'Retail']);
        $this->idKons = DB::table('konsumen')->insertGetId(['nama_konsumen' => 'U', 'telepon' => '1', 'alamat' => '-', 'id_golongan' => $gol]);
        DB::table('konsumen_deposit')->insert(['id_konsumen' => $this->idKons, 'tipe' => 'Setoran', 'nominal' => 100000, 'metode_bayar' => 'Tunai', 'tgl_deposit' => now()->toDateString(), 'created_at' => now(), 'updated_at' => now()]);
        AccountingService::postSetoranDeposit('DEP-UJI-1', 100000, 'Tunai');
    }

    private function admin(): User
    {
        return User::factory()->create(['id_role' => Role::factory()->create(['nama_role' => 'Administrator'])->id_role]);
    }

    private function kirim(User $u, array $data)
    {
        return $this->actingAs($u)->post('/konsumen-deposit/kembalikan', $data + ['id_konsumen' => $this->idKons, 'metode_bayar' => 'Tunai', 'alasan' => 'Nota dibatalkan']);
    }

    public function test_admin_mengembalikan_sebagian_saldo_berkurang_dan_jurnal_seimbang(): void
    {
        $this->kirim($this->admin(), ['nominal' => 30000])->assertSessionHas('success');

        $this->assertEquals(70000, KonsumenDepositController::saldoUntuk($this->idKons));
        $this->assertSame(1, DB::table('audit_logs')->where('auditable_type', 'konsumen_deposit')->count());
        $hutang = DB::table('jurnal_umum')->where('kode_akun', '2100')->selectRaw('SUM(debet) d, SUM(kredit) k')->first();
        $this->assertEquals(70000, (float) $hutang->k - (float) $hutang->d);
        $this->assertEquals((float) DB::table('jurnal_umum')->sum('debet'), (float) DB::table('jurnal_umum')->sum('kredit'));
    }

    public function test_melebihi_saldo_ditolak_tanpa_efek(): void
    {
        $this->kirim($this->admin(), ['nominal' => 100001])->assertSessionHas('error');

        $this->assertEquals(100000, KonsumenDepositController::saldoUntuk($this->idKons));
        $this->assertSame(0, DB::table('konsumen_deposit')->where('tipe', 'Pengembalian')->count());
    }

    public function test_bukan_administrator_ditolak(): void
    {
        $kasir = User::factory()->create(['id_role' => Role::factory()->create(['nama_role' => 'Kasir'])->id_role]);

        $this->kirim($kasir, ['nominal' => 1000])->assertForbidden();

        $this->assertSame(0, DB::table('konsumen_deposit')->where('tipe', 'Pengembalian')->count());
    }

    public function test_alasan_wajib(): void
    {
        $this->kirim($this->admin(), ['nominal' => 1000, 'alasan' => ''])->assertSessionHasErrors('alasan');
    }
}

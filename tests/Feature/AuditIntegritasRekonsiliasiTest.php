<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Services\AccountingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** audit:integritas: buku Kas Kecil vs jurnal 1005, Piutang 1100 vs sisa tagihan, collation seragam. */
class AuditIntegritasRekonsiliasiTest extends TestCase
{
    use RefreshDatabase;

    private function temuan(): array
    {
        Artisan::call('audit:integritas', ['--json' => true]);

        return collect(json_decode(Artisan::output(), true)['temuan'])->pluck(0)->all();
    }

    public function test_alur_lewat_aplikasi_lulus(): void
    {
        $this->actingAs(User::factory()->create(['id_role' => Role::factory()->create(['nama_role' => 'Administrator'])->id_role]));
        $this->post('/kas-kecil/saldo-awal', ['tanggal' => now()->toDateString(), 'nominal' => 1000000])->assertSessionHas('success');

        $this->assertSame([], $this->temuan());
    }

    public function test_jurnal_kas_kecil_tanpa_catatan_buku_ketahuan(): void
    {
        AccountingService::postKasKecilMasuk('KK-SELUNDUP', '3000', 50000);

        $this->assertContains('kas_kecil_tidak_sinkron', $this->temuan());
    }

    public function test_piutang_tanpa_tagihan_ketahuan(): void
    {
        AccountingService::postJournal(now()->toDateString(), 'UJI-PIUTANG', 'Piutang tanpa nota', [
            ['kode_akun' => '1100', 'debet' => 75000, 'kredit' => 0],
            ['kode_akun' => '4200', 'debet' => 0, 'kredit' => 75000],
        ]);

        $this->assertContains('piutang_tidak_sinkron', $this->temuan());
    }

    public function test_tabel_tes_seragam_collation(): void
    {
        $this->assertNotContains('collation_campuran', $this->temuan());
    }
}

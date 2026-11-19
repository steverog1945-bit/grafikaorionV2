<?php

namespace Tests\Feature;

use App\Models\MonitoringAgent;
use App\Models\MonitoringMachine;
use App\Models\MonitoringEvent;
use App\Models\AppSetting;
use App\Models\Role;
use App\Models\User;
use App\Services\MonitoringCetakEtaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Monitoring Cetak: halaman bertema ERP + agent API.
 *
 * Endpoint agent fail-closed sampai MONITORING_CETAK_AGENT_ENABLED dinyalakan.
 * Halaman web dibatasi menu SPK (22) - tim produksi yang boleh membuka SPK
 * boleh memantau mesin.
 */
class MonitoringCetakTest extends TestCase
{
    use RefreshDatabase;

    private User $produksi;
    private User $kasir;
    private User $operatorDesain;
    private User $administrator;
    private int $idMesinErp;
    private int $idMonitoringMesin;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-11-19 10:00:00'));
        AppSetting::clearCache();
        Cache::forget(MonitoringCetakEtaService::CACHE_KEY);

        config()->set('monitoring_cetak.enabled', false);

        $roleProduksi = Role::factory()->create(['nama_role' => 'Produksi']);
        $this->produksi = User::factory()->create(['id_role' => $roleProduksi->id_role]);

        $roleKasir = Role::factory()->create(['nama_role' => 'Kasir']);
        $this->kasir = User::factory()->create(['id_role' => $roleKasir->id_role]);

        // Operator Desain (input-order menus 41/42) diperbolehkan owner
        // membuka Monitoring Cetak supaya bisa memantau timing cetak item
        // miliknya di lantai produksi.
        $roleDesain = Role::factory()->create(['nama_role' => 'Operator Desain']);
        $this->operatorDesain = User::factory()->create(['id_role' => $roleDesain->id_role]);

        $roleAdmin = Role::factory()->create(['nama_role' => 'Administrator']);
        $this->administrator = User::factory()->create(['id_role' => $roleAdmin->id_role]);

        DB::table('menus')->insertOrIgnore(['id_menu' => 22, 'nama_menu' => 'SPK', 'url_link' => 'spk', 'parent_id' => 0]);
        DB::table('menus')->insertOrIgnore(['id_menu' => 41, 'nama_menu' => 'Kasir Utama (Input Order)', 'url_link' => 'penjualan-input-order', 'parent_id' => 0]);
        DB::table('menus')->insertOrIgnore(['id_menu' => 42, 'nama_menu' => 'Order Jersey (Input Order)', 'url_link' => 'jersey-input-order', 'parent_id' => 0]);
        DB::table('role_permissions')->insert([
            ['id_role' => $roleProduksi->id_role, 'id_menu' => 22],
            ['id_role' => $roleDesain->id_role, 'id_menu' => 41],
            ['id_role' => $roleDesain->id_role, 'id_menu' => 42],
        ]);

        $this->idMesinErp = DB::table('mesin')->insertGetId(['nama_mesin' => 'EPSON S80670', 'tipe_mesin' => 'Digital']);

        $this->idMonitoringMesin = MonitoringMachine::create([
            'id_mesin' => $this->idMesinErp,
            'nama_mesin' => 'EPSON S80670',
            'tipe_mesin' => 'Digital',
            'software' => 'onyx',
            'printer_name' => 'Epson S80670',
            'aktif' => true,
        ])->id;
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function buatAgent(string $rawToken): MonitoringAgent
    {
        return MonitoringAgent::create([
            'monitoring_mesin_id' => $this->idMonitoringMesin,
            'nama' => 'PC-PRODUKSI-01',
            'token_hash' => hash('sha256', $rawToken),
            'aktif' => true,
        ]);
    }

    private function agentPost(string $path, array $body, ?string $token = null)
    {
        $headers = $token !== null ? ['X-Agent-Token' => $token] : [];

        return $this->postJson('/api/monitoring-cetak/agent/' . $path, $body, $headers);
    }

    public function test_halaman_butuh_login(): void
    {
        $this->get(route('monitoring-cetak.index'))->assertRedirect();
    }

    public function test_halaman_dibatasi_menu_spk(): void
    {
        $this->actingAs($this->kasir)->get(route('monitoring-cetak.index'))->assertForbidden();
    }

    public function test_operator_desain_boleh_membuka(): void
    {
        $this->actingAs($this->operatorDesain)
            ->get(route('monitoring-cetak.index'))
            ->assertOk()
            ->assertSee('Monitoring Cetak');
    }

    public function test_administrator_selalu_boleh_tanpa_menu(): void
    {
        // Administrator bypass menu.access otomatis (Izin::bolehPath) -
        // walaupun operator yang mengelola hak akses tidak menyisakan
        // satu pun grant untuk role-nya.
        DB::table('role_permissions')->where('id_role', $this->administrator->id_role)->delete();

        $this->actingAs($this->administrator)
            ->get(route('monitoring-cetak.index'))
            ->assertOk();
    }

    public function test_halaman_muat_untuk_tim_produksi(): void
    {
        $this->actingAs($this->produksi)
            ->get(route('monitoring-cetak.index'))
            ->assertOk()
            ->assertSee('Monitoring Cetak')
            ->assertSee('EPSON S80670')
            ->assertSee('TANPA AGENT')
            ->assertSee('Antrean Hari Ini');
    }

    public function test_seed_mesins_idempoten(): void
    {
        DB::table('mesin')->insert(['nama_mesin' => 'RHINO DTF', 'tipe_mesin' => 'DTF']);

        $this->artisan('monitoring-cetak:seed-machines')->assertSuccessful();
        $this->assertDatabaseHas('monitoring_mesins', ['nama_mesin' => 'RHINO DTF']);
        $this->assertDatabaseHas('monitoring_mesins', ['nama_mesin' => 'EPSON S80670', 'id_mesin' => $this->idMesinErp]);
        // Tidak duplikat saat dijalankan ulang
        $this->artisan('monitoring-cetak:seed-machines')->assertSuccessful();
        $this->assertSame(2, MonitoringMachine::count());
    }

    public function test_agent_api_fail_closed_kecuali_diaktifkan(): void
    {
        $this->buatAgent('token-rahasia-produksi');

        $this->agentPost('ping', [], 'token-rahasia-produksi')->assertStatus(503);
    }

    public function test_token_salah_ditolak(): void
    {
        config()->set('monitoring_cetak.enabled', true);
        $this->buatAgent('token-benar');

        $this->agentPost('ping', [], 'token-salah')->assertStatus(401);
        $this->postJson('/api/monitoring-cetak/agent/ping')->assertStatus(401);
    }

    public function test_hello_event_dan_eta_histori(): void
    {
        config()->set('monitoring_cetak.enabled', true);
        Carbon::setTestNow(Carbon::parse('2026-11-19 09:00:00'));
        $agent = $this->buatAgent('token-produksi-01');

        $this->agentPost('hello', [], 'token-produksi-01')
            ->assertOk()
            ->assertJsonPath('agent', 'PC-PRODUKSI-01');

        // Dua job selesai: 600 dan 1200 detik (tepian outlier diabaikan).
        Carbon::setTestNow(Carbon::parse('2026-11-18 09:00:00'));
        $this->agentPost('event', ['event' => 'started', 'doc_name' => 'JOB-A', 'printer' => 'Epson S80670'], 'token-produksi-01')->assertOk();
        Carbon::setTestNow(Carbon::parse('2026-11-18 09:10:00'));
        $this->agentPost('event', ['event' => 'completed', 'doc_name' => 'JOB-A', 'printer' => 'Epson S80670'], 'token-produksi-01')->assertOk();
        Carbon::setTestNow(Carbon::parse('2026-11-19 08:00:00'));
        $this->agentPost('event', ['event' => 'started', 'doc_name' => 'JOB-B', 'printer' => 'Epson S80670'], 'token-produksi-01')->assertOk();
        Carbon::setTestNow(Carbon::parse('2026-11-19 08:20:00'));
        $this->agentPost('event', ['event' => 'completed', 'doc_name' => 'JOB-B', 'printer' => 'Epson S80670'], 'token-produksi-01')->assertOk();

        // Sesi cetak yang sedang berjalan.
        Carbon::setTestNow(Carbon::parse('2026-11-19 10:00:00'));
        $this->agentPost('event', ['event' => 'started', 'doc_name' => 'JOB-C', 'printer' => 'Epson S80670', 'pages_total' => 10, 'pages_done' => 2], 'token-produksi-01')->assertOk();

        $this->assertSame(5, MonitoringEvent::where('monitoring_mesin_id', $this->idMonitoringMesin)->count());
        $this->assertDatabaseHas('monitoring_events', ['doc_name' => 'JOB-C', 'event' => 'started']);

        $stats = app(MonitoringCetakEtaService::class)->agentStats(30);
        $this->assertSame(2, $stats['per_mesin'][$this->idMonitoringMesin]['n']);
        $this->assertSame(900, $stats['per_mesin'][$this->idMonitoringMesin]['median_detik']);
    }

    public function test_event_printer_tidak_dikenal_jatuh_kembali_ke_mesin_agent(): void
    {
        config()->set('monitoring_cetak.enabled', true);
        $agent = $this->buatAgent('token-produksi-02');

        $this->agentPost('event', ['event' => 'started', 'doc_name' => 'JOB-X', 'printer' => 'PRINTER-LAIN'], 'token-produksi-02')
            ->assertOk();

        $this->assertDatabaseHas('monitoring_events', [
            'doc_name' => 'JOB-X',
            'monitoring_mesin_id' => $this->idMonitoringMesin,
        ]);
    }

    public function test_status_json_berisi_antrean_dan_mesin(): void
    {
        $this->actingAs($this->produksi)
            ->getJson(route('monitoring-cetak.status'))
            ->assertOk()
            ->assertJsonStructure(['queue', 'counts' => ['antre', 'desain', 'cetak', 'selesai'], 'machines', 'agents_online']);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Services\BrandThemeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regression shell Tema Premium (18 Nov 2026):
 *  - Premium merender baris kedua + mega menu, TANPA sidebar
 *  - Kasir tidak melihat Data Master Jersey di mana pun; /jersey/master = 403
 *  - Keuangan Jersey & input pesanan TETAP tampil untuk Kasir (keputusan owner)
 *  - Tema klasik tetap memakai sidebar
 *
 * Isolasi: instance BrandThemeService diinjeksikan dengan path TEMPORER,
 * sehingga test TIDAK PERNAH menyentuh storage/app/system/brand-theme.json
 * (berkas itu adalah pengaturan tema AKTIF produksi).
 */
class TemaPremiumTest extends TestCase
{
    use RefreshDatabase;

    private string $temaPath;

    /** Injeksi instance tema dengan berkas temporer (bukan milik aplikasi). */
    private function teman(string $preset): void
    {
        if (! is_dir(dirname($this->temaPath))) {
            mkdir(dirname($this->temaPath), 0700, true);
        }
        file_put_contents($this->temaPath, json_encode(['preset' => $preset]) . PHP_EOL);
        $this->app->instance(BrandThemeService::class, new BrandThemeService($this->temaPath));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->temaPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'brand-theme-test-' . bin2hex(random_bytes(6)) . '.json';
        // Default suite: tema klasik dulu (berkas tidak ada = orion-classic).
        $this->app->instance(BrandThemeService::class, new BrandThemeService($this->temaPath));
    }

    protected function tearDown(): void
    {
        if (is_file($this->temaPath)) {
            unlink($this->temaPath);
        }
        parent::tearDown();
    }

    private function admin(): User
    {
        return User::factory()->create([
            'id_role' => (int) DB::table('roles')->insertGetId(['nama_role' => 'Administrator']),
        ]);
    }

    public function test_shell_premium_administrator_pun_tanpa_sidebar(): void
    {
        $this->teman('premium');
        $body = (string) $this->actingAs($this->admin())->get('/dashboard')->getContent();

        $this->assertStringContainsString('premium-bar2', $body);
        $this->assertStringContainsString('premium-mega', $body);
        $this->assertStringNotContainsString('id="mainSidebar"', $body);
    }

    public function test_shell_command_center_pun_tanpa_sidebar(): void
    {
        $this->teman('command-center');
        $body = (string) $this->actingAs($this->admin())->get('/dashboard')->getContent();

        $this->assertStringContainsString('premium-bar2', $body);
        $this->assertStringContainsString('premium-mega', $body);
        $this->assertStringNotContainsString('id="mainSidebar"', $body);
        $this->assertStringContainsString('data-brand-theme="command-center"', $body);
    }

    public function test_kasir_tidak_melihat_master_jersey_keuangan_tetap_tampil(): void
    {
        $this->teman('premium');
        $roleKasir = (int) DB::table('roles')->insertGetId(['nama_role' => 'Kasir']);
        DB::table('menus')->insertOrIgnore(['id_menu' => 16, 'nama_menu' => 'Kasir', 'url_link' => 'kasir', 'parent_id' => 0]);
        DB::table('menus')->insertOrIgnore(['id_menu' => 38, 'nama_menu' => 'Jersey', 'url_link' => 'jersey/master', 'parent_id' => 0]);
        DB::table('role_permissions')->insert(['id_role' => $roleKasir, 'id_menu' => 16]);
        DB::table('role_permissions')->insert(['id_role' => $roleKasir, 'id_menu' => 38]);
        $kasir = User::factory()->create(['id_role' => $roleKasir]);

        $body = (string) $this->actingAs($kasir)->get('/dashboard')->getContent();
        $this->assertStringContainsString('premium-bar2', $body);
        $this->assertStringNotContainsString('/jersey/master', $body, 'Kasir tidak boleh melihat Data Master Jersey.');
        $this->assertStringContainsString('/jersey/keuangan', $body, 'Keuangan Jersey tetap tampil untuk Kasir (keputusan owner).');
    }

    public function test_kasir_jersey_master_ditolak_langsung(): void
    {
        $this->teman('premium');
        $roleKasir = (int) DB::table('roles')->insertGetId(['nama_role' => 'Kasir']);
        DB::table('menus')->insertOrIgnore(['id_menu' => 38, 'nama_menu' => 'Jersey', 'url_link' => 'jersey/master', 'parent_id' => 0]);
        DB::table('role_permissions')->insert(['id_role' => $roleKasir, 'id_menu' => 38]);
        $kasir = User::factory()->create(['id_role' => $roleKasir]);

        $this->actingAs($kasir)->get('/jersey/master')->assertForbidden();
    }

    public function test_tema_klasik_tetap_memakai_sidebar(): void
    {
        // Berkas tidak dibuat = preset default orion-classic.
        $body = (string) $this->actingAs($this->admin())->get('/dashboard')->getContent();

        $this->assertStringContainsString('id="mainSidebar"', $body, 'Tema non-premium harus tetap memakai sidebar.');
        $this->assertStringNotContainsString('premium-bar2', $body, 'Tema non-premium tidak boleh merender baris kedua premium.');
    }
}

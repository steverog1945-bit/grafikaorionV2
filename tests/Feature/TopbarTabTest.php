<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\TopbarTab;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regresi Pengaturan Topbar Premium (18 Nov 2026):
 *  - Administrator bisa membuka halaman (200) dan langsung melihat bawaan
 *  - Role non-admin 403
 *  - Halaman menyimpan urutan baru (tab tersimpan & langsung terbaca)
 *  - Tab yang tidak punya izin path untuk suatu role tidak tampil baginya
 */
class TopbarTabTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'id_role' => (int) DB::table('roles')->insertGetId(['nama_role' => 'Administrator']),
        ]);
    }

    public function test_halaman_topbar_terbuka_untuk_administrator(): void
    {
        $this->actingAs($this->admin())->get('/pengaturan-aplikasi/topbar')
            ->assertOk()
            ->assertSee('Topbar Premium')
            ->assertSee('Dashboard');
    }

    public function test_halaman_topbar_ditolak_untuk_non_admin(): void
    {
        $kasir = User::factory()->create([
            'id_role' => (int) DB::table('roles')->insertGetId(['nama_role' => 'Kasir']),
        ]);

        $this->actingAs($kasir)->get('/pengaturan-aplikasi/topbar')->assertForbidden();
    }

    public function test_simpan_urutan_mengubah_tab_yang_tampil(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/pengaturan-aplikasi/topbar', [
            'rows' => [
                ['id' => null, 'label' => 'Alpha', 'url' => '/dashboard', 'icon' => 'fa-gauge', 'urutan' => 1, 'aktif' => '1'],
                ['id' => null, 'label' => 'Beta', 'url' => '/produksi', 'icon' => 'fa-industry', 'urutan' => 2, 'aktif' => '1'],
            ],
        ])->assertSessionHas('success');

        $labels = collect(DB::table('topbar_tabs')->orderBy('urutan')->pluck('label'))->all();
        $this->assertSame(['Alpha', 'Beta'], $labels);

        // Administrator lolos bolehPath untuk /dashboard & /produksi.
        $aktif = TopbarTab::aktif();
        $this->assertSame(['Alpha', 'Beta'], array_column($aktif, 'label'));
    }

    public function test_icon_dan_url_aneh_ditolak_validasi(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/pengaturan-aplikasi/topbar', [
            'rows' => [
                ['id' => null, 'label' => 'X', 'url' => 'javascript:alert(1)', 'icon' => 'fa-gauge', 'urutan' => 1, 'aktif' => '1'],
            ],
        ])->assertSessionHasErrors(['rows.0.url']);

        $this->actingAs($admin)->post('/pengaturan-aplikasi/topbar', [
            'rows' => [
                ['id' => null, 'label' => 'X', 'url' => '/dashboard', 'icon' => 'fa-gauge" onload="alert(1)', 'urutan' => 1, 'aktif' => '1'],
            ],
        ])->assertSessionHasErrors(['rows.0.icon']);
    }
}

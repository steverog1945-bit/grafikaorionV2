<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regresi setoran konsumen (18 Nov 2026):
 *  1. "$ is not defined" — script inline di dalam section berjalan SEBELUM
 *     jQuery termuat; blok harus berada di push scripts.
 *  2. Error 500 "Undefined property: Factory::$yieldPushContent" — komentar
 *     JS yang menyebut direktif Blade dengan tanda @ (literal) terkompilasi
 *     sebagai direktif sungguhan. Jangan menaruh '@'+nama-direktif di komentar.
 *
 * Test ini memastikan halaman render 200 DAN memuat inisialisasi AJAX.
 */
class KonsumenDepositHalamanTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_setoran_konsumen_render_tanpa_error(): void
    {
        $roleId = (int) DB::table('roles')->insertGetId(['nama_role' => 'Administrator']);
        DB::table('menus')->insertOrIgnore(['id_menu' => 32, 'nama_menu' => 'Konsumen Deposit', 'url_link' => 'konsumen-deposit', 'parent_id' => 0]);
        DB::table('role_permissions')->insert(['id_role' => $roleId, 'id_menu' => 32]);
        $u = User::factory()->create(['id_role' => $roleId]);

        $r = $this->actingAs($u)->get('/konsumen-deposit');
        $body = (string) $r->getContent();

        $this->assertSame(200, $r->status(), 'Halaman konsumen-deposit tidak boleh error. Potongan: ' . substr(strip_tags($body), 0, 300));
        $this->assertStringContainsString('search-ajax', $body);
    }
}

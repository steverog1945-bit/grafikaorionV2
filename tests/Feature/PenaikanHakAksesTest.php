<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Audit Fase 4: pengelola akun/hak akses yang BUKAN Administrator tidak boleh
 * menaikkan hak siapa pun ke atas dirinya (buat akun admin, naikkan diri
 * sendiri, ubah role Administrator). Semuanya dicek di server.
 */
class PenaikanHakAksesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $pengelola;
    private Role $roleAdmin;
    private Role $rolePengelola;
    private Role $roleBiasa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->roleAdmin = Role::factory()->create(['nama_role' => 'Administrator']);
        $this->rolePengelola = Role::factory()->create(['nama_role' => 'Staf HRD']);
        $this->roleBiasa = Role::factory()->create(['nama_role' => 'Kasir Uji']);
        $this->admin = User::factory()->create(['id_role' => $this->roleAdmin->id_role]);
        $this->pengelola = User::factory()->create(['id_role' => $this->rolePengelola->id_role]);

        DB::table('menus')->insertOrIgnore([
            ['id_menu' => 4, 'nama_menu' => 'Setting Akses', 'url_link' => 'setting-akses', 'parent_id' => 0],
            ['id_menu' => 6, 'nama_menu' => 'Data User', 'url_link' => 'users', 'parent_id' => 0],
        ]);
        foreach ([4, 6] as $m) {
            DB::table('role_permissions')->insert(['id_role' => $this->rolePengelola->id_role, 'id_menu' => $m, 'can_view' => 1, 'can_add' => 1, 'can_edit' => 1, 'can_delete' => 1]);
        }
    }

    private function karyawan(string $nama): int
    {
        return DB::table('karyawan')->insertGetId(['nama_karyawan' => $nama, 'bagian' => 'Produksi', 'telepon' => '1', 'status_aktif' => 1]);
    }

    public function test_pengelola_tidak_bisa_membuat_akun_administrator(): void
    {
        $this->actingAs($this->pengelola)->post('/users/store', [
            'id_karyawan' => $this->karyawan('Calon Admin'), 'username' => 'calonadmin', 'password' => 'rahasia123', 'id_role' => $this->roleAdmin->id_role,
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['username' => 'calonadmin']);
    }

    public function test_pengelola_tetap_bisa_membuat_akun_role_biasa_dan_tercatat_di_audit(): void
    {
        $this->actingAs($this->pengelola)->post('/users/store', [
            'id_karyawan' => $this->karyawan('Kasir Baru'), 'username' => 'kasirbaru', 'password' => 'rahasia123', 'id_role' => $this->roleBiasa->id_role,
        ])->assertSessionHas('success');

        $id = DB::table('users')->where('username', 'kasirbaru')->value('id_user');
        $this->assertNotNull($id);
        $log = DB::table('audit_logs')->where('auditable_type', 'users')->where('auditable_id', $id)->where('event', 'created')->first();
        $this->assertNotNull($log);
        $this->assertStringNotContainsString('rahasia123', json_encode($log), 'Password tidak boleh masuk audit log');
    }

    public function test_role_yang_tidak_ada_ditolak_validasi(): void
    {
        $this->actingAs($this->admin)->post('/users/store', [
            'id_karyawan' => $this->karyawan('X'), 'username' => 'tanparole', 'password' => 'rahasia123', 'id_role' => 987654,
        ])->assertSessionHasErrors('id_role');
    }

    public function test_pengelola_tidak_bisa_menaikkan_diri_sendiri_atau_mengubah_akun_administrator(): void
    {
        $this->actingAs($this->pengelola)->put("/users/update/{$this->pengelola->id_user}", [
            'username' => $this->pengelola->username, 'id_role' => $this->roleAdmin->id_role,
        ])->assertForbidden();
        $this->assertEquals($this->rolePengelola->id_role, $this->pengelola->fresh()->id_role);

        // Bahkan ke role biasa: akun sendiri bukan wewenang pengelola non-admin.
        $this->actingAs($this->pengelola)->put("/users/update/{$this->pengelola->id_user}", [
            'username' => $this->pengelola->username, 'id_role' => $this->roleBiasa->id_role,
        ])->assertForbidden();

        $this->actingAs($this->pengelola)->put("/users/update/{$this->admin->id_user}", [
            'username' => 'diambilalih', 'password' => 'passwordbaru1', 'id_role' => $this->roleBiasa->id_role,
        ])->assertForbidden();
        $this->assertNotSame('diambilalih', $this->admin->fresh()->username);

        $this->actingAs($this->pengelola)->delete("/users/delete/{$this->admin->id_user}")->assertForbidden();
        $this->assertNotNull(User::find($this->admin->id_user));
    }

    public function test_pengelola_boleh_mengubah_akun_biasa_dan_perubahan_role_tercatat(): void
    {
        $biasa = User::factory()->create(['id_role' => $this->roleBiasa->id_role]);

        $this->actingAs($this->pengelola)->put("/users/update/{$biasa->id_user}", [
            'username' => $biasa->username, 'id_role' => $this->rolePengelola->id_role,
        ])->assertSessionHas('success');

        $this->assertEquals($this->rolePengelola->id_role, $biasa->fresh()->id_role);
        $log = DB::table('audit_logs')->where('auditable_type', 'users')->where('auditable_id', $biasa->id_user)->where('event', 'updated')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('role berubah', $log->keterangan ?? json_encode($log));
    }

    public function test_pengelola_tidak_bisa_mengubah_izin_role_administrator_atau_rolenya_sendiri(): void
    {
        $sebelum = DB::table('role_permissions')->where('id_role', $this->rolePengelola->id_role)->count();

        $this->actingAs($this->pengelola)->post('/setting-akses/update', ['role_id' => $this->roleAdmin->id_role, 'izin' => [7 => ['view' => 1]]])->assertForbidden();
        $this->actingAs($this->pengelola)->post('/setting-akses/update', ['role_id' => $this->rolePengelola->id_role, 'izin' => [16 => ['view' => 1, 'add' => 1]]])->assertForbidden();

        $this->assertSame($sebelum, DB::table('role_permissions')->where('id_role', $this->rolePengelola->id_role)->count());
        $this->assertSame(0, DB::table('role_permissions')->where('id_role', $this->roleAdmin->id_role)->count());
    }

    public function test_pengelola_boleh_mengatur_izin_role_lain(): void
    {
        DB::table('menus')->insertOrIgnore(['id_menu' => 7, 'nama_menu' => 'Data Konsumen', 'url_link' => 'konsumen', 'parent_id' => 0]);

        $this->actingAs($this->pengelola)->post('/setting-akses/update', ['role_id' => $this->roleBiasa->id_role, 'izin' => [7 => ['view' => 1]]])->assertRedirect();

        $this->assertSame(1, DB::table('role_permissions')->where('id_role', $this->roleBiasa->id_role)->where('id_menu', 7)->count());
    }

    public function test_jalur_lama_matriks_tidak_bisa_dipakai_pengelola_untuk_menaikkan_diri(): void
    {
        DB::table('menus')->insertOrIgnore(['id_menu' => 16, 'nama_menu' => 'Kasir', 'url_link' => 'kasir', 'parent_id' => 0]);
        $sebelum = DB::table('role_permissions')->where('id_role', $this->rolePengelola->id_role)->orderBy('id_menu')->pluck('id_menu')->all();

        $this->actingAs($this->pengelola)->post('/setting-akses/update', [
            'permissions' => [
                $this->rolePengelola->id_role => [4, 6, 16],   // menambah menu Kasir untuk dirinya sendiri
                $this->roleAdmin->id_role => [4, 6, 16],
                $this->roleBiasa->id_role => [16],
            ],
        ])->assertSessionHas('success');

        $this->assertSame($sebelum, DB::table('role_permissions')->where('id_role', $this->rolePengelola->id_role)->orderBy('id_menu')->pluck('id_menu')->all());
        $this->assertSame(0, DB::table('role_permissions')->where('id_role', $this->roleAdmin->id_role)->count());
        $this->assertSame(1, DB::table('role_permissions')->where('id_role', $this->roleBiasa->id_role)->where('id_menu', 16)->count());
    }

    public function test_pengelola_tidak_bisa_memberi_pengecualian_izin_ke_dirinya_atau_administrator(): void
    {
        $this->actingAs($this->pengelola)->post("/users/{$this->pengelola->id_user}/akses", ['izin' => [16 => ['view' => 1]]])->assertForbidden();
        $this->actingAs($this->pengelola)->post("/users/{$this->admin->id_user}/akses", ['izin' => [16 => ['view' => 1]]])->assertForbidden();
        $this->actingAs($this->pengelola)->post("/users/{$this->pengelola->id_user}/akses/reset")->assertForbidden();
    }

    public function test_administrator_tetap_bebas_mengelola_semuanya(): void
    {
        $this->actingAs($this->admin)->post('/users/store', [
            'id_karyawan' => $this->karyawan('Admin Kedua'), 'username' => 'admin2', 'password' => 'rahasia123', 'id_role' => $this->roleAdmin->id_role,
        ])->assertSessionHas('success');
        $this->actingAs($this->admin)->post('/setting-akses/update', ['role_id' => $this->rolePengelola->id_role, 'izin' => [4 => ['view' => 1]]])->assertRedirect();

        $this->assertDatabaseHas('users', ['username' => 'admin2']);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_dengan_kredensial_benar_berhasil(): void
    {
        $user = User::factory()->create([
            'username' => 'budi_test',
            'password' => Hash::make('rahasia123'),
        ]);

        $response = $this->post('/login', [
            'username' => 'budi_test',
            'password' => 'rahasia123',
        ]);

        $response->assertRedirect('dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_dengan_password_salah_ditolak(): void
    {
        User::factory()->create([
            'username' => 'budi_test',
            'password' => Hash::make('rahasia123'),
        ]);

        $response = $this->post('/login', [
            'username' => 'budi_test',
            'password' => 'password_salah',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_login_dengan_username_tidak_terdaftar_ditolak(): void
    {
        $response = $this->post('/login', [
            'username' => 'tidak_ada_user_seperti_ini',
            'password' => 'apapun',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_halaman_terproteksi_redirect_ke_login_kalau_belum_masuk(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/');
    }

    public function test_user_yang_sudah_login_bisa_akses_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
    }

    public function test_logout_menghapus_sesi(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->assertAuthenticated();

        $response = $this->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_role_administrator_selalu_lolos_menu_access(): void
    {
        $adminRole = Role::factory()->create(['nama_role' => 'Administrator']);
        $user = User::factory()->create(['id_role' => $adminRole->id_role]);

        // Administrator selalu lolos CheckMenuAccess tanpa perlu row
        // role_permissions apapun (lihat app/Http/Middleware/CheckMenuAccess.php)
        $response = $this->actingAs($user)->get('/kartu-stok');

        $response->assertOk();
    }
}

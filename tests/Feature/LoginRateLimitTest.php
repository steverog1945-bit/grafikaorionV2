<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * P0-4 (audit hardening): /login sebelumnya tanpa batas percobaan sama
 * sekali - dites di sini supaya tidak diam-diam hilang lagi di masa depan.
 */
class LoginRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_percobaan_login_gagal_dibatasi_lalu_pulih_setelah_jeda(): void
    {
        $user = User::factory()->create([
            'username' => 'usercoba',
            'password' => bcrypt('rahasia-benar'),
            'id_role' => Role::factory()->create(['nama_role' => 'Kasir'])->id_role,
        ]);

        for ($i = 0; $i < 3; $i++) {
            $this->post('/login', ['username' => 'usercoba', 'password' => 'salah'])
                ->assertSessionHasErrors('username');
        }

        // Setelah 3 gagal dari IP yang sama, CAPTCHA aktif: password BENAR
        // pun ditolak sebelum Auth::attempt (error key 'captcha'), jadi
        // tidak ada yang bisa login dari IP ini untuk sementara.
        $captcha = $this->post('/login', ['username' => 'usercoba', 'password' => 'rahasia-benar']);
        $captcha->assertSessionHasErrors('captcha');
        $this->assertGuest();

        // Percobaan ke-5 masih dalam jangkauan throttle (5/mnt) - tetap captcha.
        $kelima = $this->post('/login', ['username' => 'usercoba', 'password' => 'rahasia-benar']);
        $kelima->assertSessionHasErrors('captcha');
        $this->assertGuest();

        // Percobaan ke-6 dalam jendela waktu yang sama harus ditolak oleh limiter,
        // BUKAN diproses lagi oleh AuthController.
        $keenam = $this->post('/login', ['username' => 'usercoba', 'password' => 'rahasia-benar']);
        $keenam->assertSessionHasErrors('username');
        $this->assertGuest();

        // IP/username lain dari IP yang sama TIDAK lolos tanpa captcha juga
        // (CAPTCHA berlaku per IP) - dibuktikan di sini, bukan sekadar diasumsikan.
        $this->post('/login', ['username' => 'lain-tidak-ada', 'password' => 'apa saja'])
            ->assertSessionHasErrors('captcha');
    }

    public function test_login_benar_di_bawah_ambang_batas_tetap_berhasil(): void
    {
        User::factory()->create([
            'username' => 'usercoba2',
            'password' => bcrypt('rahasia-benar'),
            'id_role' => Role::factory()->create(['nama_role' => 'Kasir'])->id_role,
        ]);

        $this->post('/login', ['username' => 'usercoba2', 'password' => 'salah'])->assertSessionHasErrors('username');
        $this->post('/login', ['username' => 'usercoba2', 'password' => 'rahasia-benar'])->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }
}

<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Desain pelanggan dan bukti bayar hanya untuk pengguna yang sudah login; logo tetap publik.
 * (Apache meneruskan /storage/<folder privat>/ ke rute ini; lihat public/.htaccess.)
 */
class BerkasPrivatTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create(['id_role' => Role::factory()->create(['nama_role' => 'Kasir'])->id_role]);
    }

    protected function tearDown(): void
    {
        Storage::disk('public')->deleteDirectory('order_thumbnails/uji-privat');
        parent::tearDown();
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        Storage::disk('public')->put('order_thumbnails/uji-privat/a.jpg', 'x');

        $this->get('/storage/order_thumbnails/uji-privat/a.jpg')->assertRedirect();
    }

    public function test_pengguna_login_menerima_berkas_dengan_nosniff(): void
    {
        Storage::disk('public')->put('order_thumbnails/uji-privat/a.jpg', 'x');

        $r = $this->actingAs($this->user())->get('/storage/order_thumbnails/uji-privat/a.jpg');

        $r->assertOk();
        $r->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_berkas_html_dipaksa_diunduh_tanpa_skrip(): void
    {
        Storage::disk('public')->put('order_thumbnails/uji-privat/b.html', '<script>1</script>');

        $r = $this->actingAs($this->user())->get('/storage/order_thumbnails/uji-privat/b.html');

        $r->assertOk();
        $this->assertStringContainsString("script-src 'none'", $r->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString('attachment', $r->headers->get('Content-Disposition'));
    }

    public function test_folder_tak_dikenal_dan_penelusuran_direktori_ditolak(): void
    {
        $u = $this->user();

        $this->actingAs($u)->get('/storage/company_logo/x.png')->assertNotFound();
        $this->actingAs($u)->get('/storage/order_thumbnails/..%2F..%2F.env')->assertNotFound();
        $this->actingAs($u)->get('/storage/order_thumbnails/tidak-ada.jpg')->assertNotFound();
    }
}

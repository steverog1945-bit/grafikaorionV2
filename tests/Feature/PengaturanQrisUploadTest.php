<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Regresi 18 Nov 2026: simpan Pengaturan Aplikasi dengan upload gambar QRIS
 * langsung 500 - SubmitSekaliJalan mencoba serialize UploadedFile ('Serialization
 * of Illuminate\Http\UploadedFile is not allowed') SEBELUM controller jalan.
 */
class PengaturanQrisUploadTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $roleId = (int) DB::table('roles')->insertGetId(['nama_role' => 'Administrator']);
        DB::table('menus')->insertOrIgnore(['id_menu' => 37, 'nama_menu' => 'Pengaturan Aplikasi', 'url_link' => 'pengaturan-aplikasi', 'parent_id' => 0]);
        DB::table('role_permissions')->insertOrIgnore(['id_role' => $roleId, 'id_menu' => 37]);

        return User::factory()->create(['id_role' => $roleId]);
    }

    public function test_upload_qris_tidak_500_dan_tersimpan(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        // SubmitSekaliJalan meng-hash isi form (tanpa submit_id) - file harus lolos.
        $file = UploadedFile::fake()->create('qris-test.png', 123);

        $r = $this->actingAs($admin)->post('/pengaturan-aplikasi', [
            'nama_aplikasi' => 'Orion Grafika',
            'qris' => $file,
            'submit_id' => 'uji-upload-qris-1',
        ]);

        $r->assertSessionHasNoErrors()->assertRedirect();

        // Berkas tersimpan di disk 'public' pada folder qris/.
        $this->assertTrue(Storage::disk('public')->exists('qris'), 'Folder qris/ harus terbentuk setelah upload.');
        $setting = \App\Models\AppSetting::current();
        $this->assertNotNull($setting->qris_path, 'Kolom qris_path harus terisi setelah upload berhasil.');
        $this->assertTrue(Storage::disk('public')->exists($setting->qris_path));
    }
}

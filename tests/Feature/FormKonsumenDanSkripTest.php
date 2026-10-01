<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * - Modal Tambah dan Ubah Konsumen berisi isian yang sama (dulu Tipe Konsumen hanya ada di Ubah).
 * - Skrip di view tidak boleh memanggil classList dengan nama kelas kosong: browser melempar error dan
 *   sisa fungsi berhenti (penyebab produk Input Order tetap terkunci setelah kategori dipilih).
 */
class FormKonsumenDanSkripTest extends TestCase
{
    use RefreshDatabase;

    public function test_modal_tambah_dan_ubah_konsumen_punya_isian_yang_sama(): void
    {
        $this->actingAs(User::factory()->create(['id_role' => Role::factory()->create(['nama_role' => 'Administrator'])->id_role]));
        $html = $this->get('/konsumen')->assertOk()->getContent();

        $isian = function (string $idModal) use ($html) {
            $this->assertMatchesRegularExpression('/id="' . $idModal . '"/', $html);
            $mulai = strpos($html, 'id="' . $idModal . '"');
            $blok = substr($html, $mulai, strpos($html, '</form>', $mulai) - $mulai);
            preg_match_all('/<(?:input|select|textarea)\b[^>]*\bname="([^"_][^"]*)"/', $blok, $m);

            return collect($m[1])->reject(fn ($n) => $n === '_method')->sort()->values()->all();
        };

        $tambah = $isian('modal-tambah-konsumen');
        $this->assertContains('tipe_konsumen', $tambah);
        $this->assertSame($tambah, $isian('modal-edit-konsumen'));
    }

    public function test_tambah_konsumen_menyimpan_tipe(): void
    {
        $this->actingAs(User::factory()->create(['id_role' => Role::factory()->create(['nama_role' => 'Administrator'])->id_role]));
        $gol = DB::table('golongan_konsumen')->value('id_golongan') ?? DB::table('golongan_konsumen')->insertGetId(['nama_golongan' => 'Umum']);

        $this->post('/konsumen', ['nama_konsumen' => 'Toko Maju', 'telepon' => '0812345', 'id_golongan' => $gol, 'tipe_konsumen' => 'permanent'])
            ->assertSessionHas('success');
        $this->assertSame('permanent', DB::table('konsumen')->where('nama_konsumen', 'Toko Maju')->value('tipe_konsumen'));

        $this->post('/konsumen', ['nama_konsumen' => 'Salah', 'telepon' => '0812', 'id_golongan' => $gol, 'tipe_konsumen' => 'vip'])
            ->assertSessionHasErrors('tipe_konsumen');
    }

    public function test_tidak_ada_classlist_dengan_nama_kelas_kosong(): void
    {
        $temuan = [];
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views'))) as $f) {
            if (! str_ends_with($f->getFilename(), '.blade.php')) {
                continue;
            }
            foreach (file($f->getPathname()) as $i => $baris) {
                if (preg_match('/classList\.\w+\([^)]*(\'\'|"")/', $baris) && ! preg_match('/classList\.toggle\([^,]+,[^)]*(\'\'|"")/', $baris)) {
                    $temuan[] = str_replace(resource_path('views'), '', $f->getPathname()) . ':' . ($i + 1);
                }
            }
        }
        $this->assertSame([], $temuan, 'classList dengan nama kelas kosong melempar error di browser');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Audit Order Jersey (HTTP, dari form sampai database): baris pemain ke-1, ke-10,
 * ke-20, ke-50 tidak boleh menghasilkan galat larik/indeks; semua ukuran master
 * (XS sampai 3XL) dan ukuran kustom yang dibuat dari Master otomatis diterima;
 * ukuran yang tidak terdaftar ditolak dengan indeks baris yang benar.
 */
class JerseyOrderHttpTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private int $idKons;
    private int $tipe;
    private int $kategori;
    private int $bahan;
    private int $lengan;
    private int $kerah;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-09-28 10:00:00'));

        $this->admin = User::factory()->create(['id_role' => Role::factory()->create(['nama_role' => 'Administrator'])->id_role]);
        $gol = DB::table('golongan_konsumen')->insertGetId(['nama_golongan' => 'G', 'keterangan' => '-']);
        $this->idKons = DB::table('konsumen')->insertGetId(['nama_konsumen' => 'Klub Uji', 'telepon' => '1', 'alamat' => '-', 'id_golongan' => $gol]);

        // Master bawaan migrasi (tipe, kategori, bahan, lengan, kerah, ukuran) dipakai apa adanya; hanya yang belum ada dibuat.
        $now = now();
        $ambil = function (string $tabel, string $nama, array $tambahan = []) use ($now): int {
            $id = DB::table($tabel)->where('name', $nama)->value('id');

            return (int) ($id ?: DB::table($tabel)->insertGetId(array_merge(['name' => $nama, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now], $tambahan)));
        };
        $this->tipe = $ambil('jersey_product_types', 'Jersey Uji', ['has_sleeve_collar' => 1]);
        $this->kategori = $ambil('jersey_categories', 'Futsal Uji');
        $this->bahan = $ambil('jersey_materials', 'Brazil Uji');
        $this->lengan = $ambil('jersey_sleeve_types', 'Pendek', ['price_surcharge' => 0, 'sort_order' => 1]);
        $this->kerah = $ambil('jersey_collar_types', 'O-Neck', ['price_surcharge' => 0, 'sort_order' => 1]);

        foreach (['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL'] as $i => $u) {
            $ada = DB::table('jersey_sizes')->where('name', $u)->exists();
            if (!$ada) {
                DB::table('jersey_sizes')->insert(['name' => $u, 'sort_order' => $i + 1, 'is_active' => 1, 'price_surcharge' => 0, 'kelompok' => 'dewasa', 'price_percent' => 0, 'created_at' => $now, 'updated_at' => $now]);
            }
            DB::table('jersey_sizes')->where('name', $u)->update(['is_active' => 1]);
        }
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function pemain(int $n, string $ukuran): array
    {
        return [
            'player_name' => "Pemain {$n}", 'player_number' => (string) $n, 'size' => $ukuran, 'gender' => $n % 2 ? 'L' : 'P',
            'jersey_sleeve_type_id' => $this->lengan, 'jersey_collar_type_id' => $this->kerah,
        ];
    }

    private function payload(array $items): array
    {
        return [
            'order_date' => now()->toDateString(), 'id_konsumen' => $this->idKons, 'team_name' => 'Klub Uji FC',
            'lines' => [[
                'nama_barang' => 'Jersey Home', 'jersey_product_type_id' => $this->tipe, 'jersey_category_id' => $this->kategori,
                'jersey_material_id' => $this->bahan, 'jersey_sleeve_type_id' => $this->lengan, 'jersey_collar_type_id' => $this->kerah,
                'thumbnail' => 'jersey_thumbnails/uji.jpg', 'order_type' => 'player_list', 'items' => $items,
            ]],
        ];
    }

    public function test_enam_puluh_pemain_semua_ukuran_master_dan_ukuran_kustom_tersimpan_tanpa_galat_indeks(): void
    {
        // Ukuran kustom dibuat dari Master SETELAH aplikasi berjalan: tidak ada whitelist hard-code yang boleh menolaknya.
        DB::table('jersey_sizes')->insert(['name' => 'XXL Kustom Uji', 'sort_order' => 99, 'is_active' => 1, 'price_surcharge' => 0, 'kelompok' => 'dewasa', 'price_percent' => 0, 'created_at' => now(), 'updated_at' => now()]);
        $ukuran = ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', 'XXL Kustom Uji'];

        $items = [];
        for ($n = 1; $n <= 60; $n++) {
            $items[] = $this->pemain($n, $ukuran[($n - 1) % count($ukuran)]);
        }

        $r = $this->actingAs($this->admin)->post('/jersey/store', $this->payload($items));

        $r->assertSessionHasNoErrors();
        $this->assertSame(1, DB::table('jersey_orders')->count());
        $this->assertSame(60, DB::table('jersey_order_items')->count());
        foreach ([1, 10, 20, 50, 60] as $n) {
            $baris = DB::table('jersey_order_items')->where('player_name', "Pemain {$n}")->first();
            $this->assertNotNull($baris, "Pemain ke-{$n} harus tersimpan");
            $this->assertNotNull($baris->jersey_size_id, "Pemain ke-{$n}: ukuran harus tertaut ke master");
        }
        $this->assertGreaterThan(0, DB::table('jersey_order_items')->where('size', 'XXL Kustom Uji')->count());
        $this->assertSame(60, (int) DB::table('jersey_order_lines')->value('qty'));
    }

    public function test_ukuran_tidak_terdaftar_ditolak_dengan_indeks_baris_yang_benar_dan_tidak_menyimpan_sebagian(): void
    {
        foreach ([0, 9, 19, 49] as $indeks) {
            $items = [];
            for ($n = 1; $n <= 50; $n++) {
                $items[] = $this->pemain($n, $n - 1 === $indeks ? 'ZZ' : 'M');
            }

            $this->actingAs($this->admin)->post('/jersey/store', $this->payload($items))->assertSessionHasErrors("lines.0.items.{$indeks}.size");
            $this->assertSame(0, DB::table('jersey_orders')->count(), "Baris pemain ke-" . ($indeks + 1) . " salah: tidak boleh ada order tersimpan sebagian");
        }
    }

    public function test_ukuran_master_yang_dinonaktifkan_ditolak(): void
    {
        DB::table('jersey_sizes')->where('name', '3XL')->update(['is_active' => 0]);

        $this->actingAs($this->admin)->post('/jersey/store', $this->payload([$this->pemain(1, '3XL')]))->assertSessionHasErrors('lines.0.items.0.size');
    }

    public function test_dua_order_di_hari_yang_sama_mendapat_nomor_berbeda_dan_berurutan(): void
    {
        $this->actingAs($this->admin)->post('/jersey/store', $this->payload([$this->pemain(1, 'M')]))->assertSessionHasNoErrors();
        $this->actingAs($this->admin)->post('/jersey/store', $this->payload([$this->pemain(1, 'L')]))->assertSessionHasNoErrors();

        $nomor = DB::table('jersey_orders')->orderBy('id')->pluck('order_number')->all();
        $this->assertSame(['JO-20260928-001', 'JO-20260928-002'], $nomor);
    }

    public function test_pemain_tanpa_jenis_kelamin_tidak_menyebabkan_galat_basis_data(): void
    {
        $p = $this->pemain(1, 'M');
        unset($p['gender']);

        $r = $this->actingAs($this->admin)->post('/jersey/store', $this->payload([$p]));

        $r->assertSessionHasNoErrors();
        $this->assertSame(1, DB::table('jersey_order_items')->count());
    }
}

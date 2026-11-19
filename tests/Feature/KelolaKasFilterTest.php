<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Filter Kelola Kas Harian: rentang tanggal + pencarian nama konsumen.
 *
 * Permintaan owner (19 Nov 2026): satu tanggal saja tidak cukup untuk
 * rekonsiliasi (biasanya "minggu ini"/"bulan lalu"), dan nota pelanggan
 * sering harus dicari tanpa tahu tanggal bayarnya.
 *
 * Yang dijaga di sini:
 *  - rentang tanggal benar-benar membatasi baris (bukan hanya tampilan);
 *  - ?tgl= lama tetap berarti satu hari (tautan/bookmark lama tidak rusak);
 *  - rentang terbalik dirapikan, tanggal tidak valid diabaikan tanpa 500;
 *  - pencarian konsumen parsial dan tidak membocorkan wildcard LIKE;
 *  - form benar-benar memuat field baru.
 */
class KelolaKasFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $kasir;
    private int $idBudi;
    private int $idSiti;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-11-19 10:00:00'));
        AppSetting::clearCache();

        $roleKasir = Role::factory()->create(['nama_role' => 'Kasir']);
        $this->kasir = User::factory()->create(['id_role' => $roleKasir->id_role]);
        DB::table('menus')->insertOrIgnore(['id_menu' => 16, 'nama_menu' => 'Kasir', 'url_link' => 'kasir', 'parent_id' => 0]);
        DB::table('role_permissions')->insert(['id_role' => $roleKasir->id_role, 'id_menu' => 16]);

        $idGol = DB::table('golongan_konsumen')->insertGetId(['nama_golongan' => 'Retail']);
        $this->idBudi = DB::table('konsumen')->insertGetId([
            'id_unique' => (string) Str::uuid(), 'nama_konsumen' => 'Budi Santoso',
            'telepon' => '0811', 'alamat' => '-', 'id_golongan' => $idGol,
        ]);
        $this->idSiti = DB::table('konsumen')->insertGetId([
            'id_unique' => (string) Str::uuid(), 'nama_konsumen' => 'Siti Aminah',
            'telepon' => '0812', 'alamat' => '-', 'id_golongan' => $idGol,
        ]);

        $this->bayar('NOTA-10', $this->idBudi, 150000, '2026-11-10 09:00:00', 'Tunai');
        $this->bayar('NOTA-12', $this->idSiti, 250000, '2026-11-12 13:00:00', 'Transfer');
        $this->bayar('NOTA-18', $this->idBudi, 175000, '2026-11-18 10:00:00', 'Debit');
        $this->bayar('NOTA-19', $this->idBudi, 90000, '2026-11-19 08:30:00', 'Tunai');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function bayar(string $nomor, int $idKonsumen, float $nominal, string $waktu, string $metode): void
    {
        $idT = DB::table('transaksi')->insertGetId([
            'nomor_nota' => $nomor, 'tipe_order' => 'Reguler', 'id_konsumen' => $idKonsumen,
            'id_user_kasir' => $this->kasir->id_user, 'tgl_order' => $waktu,
            'total_bayar' => $nominal, 'status_pembayaran' => 'Lunas',
            'status_nota' => 'Aktif', 'status_collecting' => 'Belum Diambil',
        ]);
        DB::table('pembayaran_riwayat')->insert([
            'id_transaksi' => $idT, 'nominal' => $nominal, 'metode' => $metode, 'tgl_bayar' => $waktu,
        ]);
    }

    private function kelola(array $query = [])
    {
        return $this->actingAs($this->kasir)->get(route('kasir.kelola', $query));
    }

    public function test_form_memuat_rentang_tanggal_dan_pencarian_konsumen(): void
    {
        $this->kelola()
            ->assertOk()
            ->assertSee('name="tgl_dari"', false)
            ->assertSee('name="tgl_sampai"', false)
            ->assertSee('name="konsumen"', false)
            ->assertSee('Dari tanggal')
            ->assertSee('Sampai tanggal')
            ->assertSee('Cari nama konsumen')
            // Sesi dan pintasan laporan berbagi satu blok.
            ->assertSee('Sesi kas')
            ->assertSee('Laporan Riwayat Closing');
    }

    public function test_rentang_tanggal_membatasi_baris(): void
    {
        $this->kelola(['tgl_dari' => '2026-11-10', 'tgl_sampai' => '2026-11-12'])
            ->assertOk()
            ->assertSee('NOTA-10')
            ->assertSee('NOTA-12')
            ->assertDontSee('NOTA-18')
            ->assertDontSee('NOTA-19');
    }

    public function test_parameter_tgl_lama_tetap_satu_hari(): void
    {
        $this->kelola(['tgl' => '2026-11-18'])
            ->assertOk()
            ->assertSee('NOTA-18')
            ->assertDontSee('NOTA-10')
            ->assertDontSee('NOTA-19');
    }

    public function test_rentang_terbalik_dirapikan(): void
    {
        $this->kelola(['tgl_dari' => '2026-11-12', 'tgl_sampai' => '2026-11-10'])
            ->assertOk()
            ->assertSee('NOTA-10')
            ->assertSee('NOTA-12')
            ->assertDontSee('NOTA-18');
    }

    public function test_pencarian_konsumen_parsial(): void
    {
        // Tanpa filter tanggal, defaultnya tetap "sejak sesi dibuka/hari ini",
        // jadi beri rentang supaya baris beberapa hari ikut diuji.
        $this->kelola([
            'tgl_dari' => '2026-11-01',
            'tgl_sampai' => '2026-11-30',
            'konsumen' => 'Budi',
        ])
            ->assertOk()
            ->assertSee('NOTA-10')
            ->assertSee('NOTA-18')
            ->assertSee('NOTA-19')
            ->assertDontSee('NOTA-12');

        $this->kelola([
            'tgl_dari' => '2026-11-01',
            'tgl_sampai' => '2026-11-30',
            'konsumen' => 'Santoso',
        ])
            ->assertOk()
            ->assertSee('NOTA-10')
            ->assertDontSee('NOTA-12');
    }

    public function test_wildcard_like_tidak_berubah_jadi_tampilkan_semua(): void
    {
        $this->kelola(['konsumen' => '%'])
            ->assertOk()
            ->assertDontSee('NOTA-10')
            ->assertDontSee('NOTA-12')
            ->assertSee('Tidak ada pembayaran yang cocok');
    }

    public function test_tanggal_tidak_valid_diabaikan_bukan_500(): void
    {
        $this->kelola(['tgl_dari' => '2026-13-99', 'tgl_sampai' => "2026-11-12' OR '1'='1"])
            ->assertOk()
            // Tanpa rentang valid, perilaku lama: uang masuk sesi ini/hari ini.
            ->assertSee('NOTA-19')
            ->assertDontSee('NOTA-10');
    }

    public function test_konsumen_dan_rentang_bisa_digabung(): void
    {
        $this->kelola([
            'tgl_dari' => '2026-11-01',
            'tgl_sampai' => '2026-11-30',
            'konsumen' => 'Siti',
        ])
            ->assertOk()
            ->assertSee('NOTA-12')
            ->assertDontSee('NOTA-10')
            ->assertDontSee('NOTA-19');
    }
}

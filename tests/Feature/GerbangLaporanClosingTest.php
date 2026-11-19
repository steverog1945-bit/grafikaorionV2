<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Gembok password Administrator untuk laporan closing.
 *
 * Permintaan owner (19 Nov 2026):
 *
 * 1. Riwayat closing TIDAK ditampilkan di Kelola Kas Harian - pintasannya
 *    tetap ada, tetapi menuju Laporan Kas yang terkunci.
 * 2. Untuk membuka laporan closing, password akun Administrator harus
 *    dimasukkan. Password akun kasir sendiri TIDAK berlaku.
 *
 * Penegakan ada di server (LaporanController::kas dan cetak rekap closing),
 * bukan hanya tautan yang disembunyikan: alamat laporan diketik langsung pun
 * tetap dialihkan ke halaman gerbang.
 */
class GerbangLaporanClosingTest extends TestCase
{
    use RefreshDatabase;

    private User $kasir;
    private User $admin;
    private int $idSesiTutup;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-10-15 19:00:00'));
        AppSetting::clearCache();

        $roleKasir = Role::factory()->create(['nama_role' => 'Kasir']);
        $this->kasir = User::factory()->create(['id_role' => $roleKasir->id_role]);

        $roleAdmin = Role::factory()->create(['nama_role' => 'Administrator']);
        $this->admin = User::factory()->create([
            'id_role'  => $roleAdmin->id_role,
            'password' => Hash::make('admin-rahasia'),
        ]);

        DB::table('menus')->insertOrIgnore(['id_menu' => 16, 'nama_menu' => 'Kasir', 'url_link' => 'kasir', 'parent_id' => 0]);
        DB::table('menus')->insertOrIgnore(['id_menu' => 29, 'nama_menu' => 'Laporan Kas', 'url_link' => 'laporan/kas', 'parent_id' => 0]);
        DB::table('role_permissions')->insert([
            ['id_role' => $roleKasir->id_role, 'id_menu' => 16],
            ['id_role' => $roleKasir->id_role, 'id_menu' => 29],
        ]);

        $this->idSesiTutup = DB::table('kas_harian')->insertGetId([
            'tgl_kas' => '2026-10-15',
            'id_user' => $this->kasir->id_user,
            'saldo_awal' => 100000,
            'saldo_akhir' => 436193,
            'saldo_fisik' => 436193,
            'selisih' => 0,
            'status_lock' => 'closed',
            'status_rolling' => 'Tidak Rolling',
            'waktu_tutup' => '2026-10-15 18:00:00',
            'created_at' => '2026-10-15 08:00:00',
            'updated_at' => '2026-10-15 18:00:00',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_laporan_kas_terkunci_untuk_kasir(): void
    {
        $this->actingAs($this->kasir)
            ->get(route('laporan.kas'))
            ->assertRedirect(route('laporan.kas.gerbang'));
    }

    public function test_halaman_gerbang_meminta_password_administrator(): void
    {
        $this->actingAs($this->kasir)
            ->get(route('laporan.kas.gerbang'))
            ->assertOk()
            ->assertSee('Laporan Closing Terkunci')
            ->assertSee('Password Administrator')
            ->assertSee('name="password"', false)
            // Halaman gerbang tidak boleh membocorkan angka laci/selisih.
            ->assertDontSee('436.193');
    }

    public function test_password_akun_kasir_sendiri_tidak_membuka_laporan(): void
    {
        $this->actingAs($this->kasir)
            ->post(route('laporan.kas.gerbang.buka'), ['password' => 'password'])
            ->assertSessionHasErrors('password');

        $this->actingAs($this->kasir)
            ->get(route('laporan.kas'))
            ->assertRedirect(route('laporan.kas.gerbang'));
    }

    public function test_password_administrator_membuka_laporan(): void
    {
        $filter = [
            'status_lock' => 'closed',
            'tgl_dari'    => '2026-10-15',
            'tgl_sampai'  => '2026-10-15',
        ];

        $this->actingAs($this->kasir)
            ->post(route('laporan.kas.gerbang.buka', $filter), ['password' => 'admin-rahasia'])
            ->assertSessionHasNoErrors()
            // Filter yang dibawa dari Kelola Kas ikut diteruskan ke laporan.
            // Urutan query mengikuti GerbangLaporanClosing::parameter().
            ->assertRedirect(route('laporan.kas', [
                'tgl_dari'    => '2026-10-15',
                'tgl_sampai'  => '2026-10-15',
                'status_lock' => 'closed',
            ]));

        $this->actingAs($this->kasir)
            ->get(route('laporan.kas', $filter))
            ->assertOk()
            ->assertSee('436.193');
    }

    public function test_administrator_yang_login_tidak_perlu_password(): void
    {
        $this->actingAs($this->admin)
            ->get(route('laporan.kas'))
            ->assertOk()
            ->assertSee('436.193');
    }

    public function test_cetak_rekap_closing_juga_terkunci(): void
    {
        $this->actingAs($this->kasir)
            ->get(route('kasir.print-closing', ['id' => $this->idSesiTutup, 'format' => 'a4']))
            ->assertRedirect(route('laporan.kas.gerbang', ['cetak' => $this->idSesiTutup, 'format' => 'a4']));

        $this->actingAs($this->kasir)
            ->get(route('laporan.kas.gerbang', ['cetak' => $this->idSesiTutup, 'format' => 'a4']))
            ->assertOk()
            ->assertSee('Mencetak rekap closing sesi #' . $this->idSesiTutup);
    }

    public function test_setelah_password_benar_cetakan_langsung_diarahkan_ke_sesi_yang_diminta(): void
    {
        $tujuan = route('kasir.print-closing', ['id' => $this->idSesiTutup, 'format' => 'thermal']);

        $this->actingAs($this->kasir)
            ->post(
                route('laporan.kas.gerbang.buka', ['cetak' => $this->idSesiTutup, 'format' => 'thermal']),
                ['password' => 'admin-rahasia']
            )
            ->assertSessionHasNoErrors()
            ->assertRedirect($tujuan);

        $this->actingAs($this->kasir)
            ->get($tujuan)
            ->assertOk();
    }

    public function test_kelola_kas_tidak_menampilkan_riwayat_closing(): void
    {
        $this->actingAs($this->kasir)->get(route('kasir.kelola'))
            ->assertOk()
            // Pintasan ke laporan (terkunci), bukan daftar sesi yang sudah tutup.
            ->assertSee('Laporan Riwayat Closing')
            ->assertSee('status_lock=closed', false)
            // Baris sesi yang sudah ditutup tidak ikut terdaftar di halaman ini.
            ->assertDontSee('Sesi #' . $this->idSesiTutup);
    }
}

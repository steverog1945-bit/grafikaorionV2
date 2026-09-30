<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Perawatan data: log audit lama dipindah ke arsip (tidak hilang), cache dan sesi kedaluwarsa dibuang. */
class RapikanDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_log_audit_lama_dipindah_ke_arsip_dan_tetap_bisa_dibuka(): void
    {
        $admin = User::factory()->create(['id_role' => Role::factory()->create(['nama_role' => 'Administrator'])->id_role]);
        DB::table('audit_logs')->insert([
            ['event' => 'updated', 'auditable_type' => 'transaksi', 'auditable_id' => 1, 'keterangan' => 'Log lama sekali', 'created_at' => now()->subMonths(30)],
            ['event' => 'updated', 'auditable_type' => 'transaksi', 'auditable_id' => 2, 'keterangan' => 'Log bulan ini', 'created_at' => now()],
        ]);
        DB::table('cache')->insert([['key' => 'basi', 'value' => 'x', 'expiration' => time() - 10], ['key' => 'segar', 'value' => 'x', 'expiration' => time() + 600]]);

        Artisan::call('data:rapikan');

        $utama = DB::table('audit_logs')->pluck('keterangan')->all();
        $this->assertContains('Log bulan ini', $utama);
        $this->assertNotContains('Log lama sekali', $utama);
        $this->assertSame(['Log lama sekali'], DB::table('audit_logs_arsip')->pluck('keterangan')->all());
        $this->assertSame(['segar'], DB::table('cache')->pluck('key')->all());

        $this->actingAs($admin)->get('/log-aktivitas?arsip=1')->assertOk()->assertSee('Log lama sekali')->assertDontSee('Log bulan ini');
        $this->actingAs($admin)->get('/log-aktivitas')->assertOk()->assertDontSee('Log lama sekali');
    }

    public function test_pratinjau_tidak_mengubah_apa_pun(): void
    {
        DB::table('audit_logs')->insert(['event' => 'updated', 'auditable_type' => 'transaksi', 'auditable_id' => 1, 'keterangan' => 'Lama', 'created_at' => now()->subMonths(30)]);

        Artisan::call('data:rapikan', ['--pratinjau' => true]);

        $this->assertSame(1, DB::table('audit_logs')->where('keterangan', 'Lama')->count());
        $this->assertSame(0, DB::table('audit_logs_arsip')->count());
    }
}

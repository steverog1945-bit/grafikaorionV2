<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardBaruTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_administrator_menampilkan_kpi_tren_perlu_tindakan_dan_panduan_finishing(): void
    {
        $admin = User::factory()->create(['id_role' => Role::factory()->create(['nama_role' => 'Administrator'])->id_role]);

        $res = $this->actingAs($admin)->get('/dashboard')->assertOk();
        $res->assertSee('Order Hari Ini')->assertSee('Kas Masuk Hari Ini')->assertSee('Alur Pekerjaan')->assertSee('Perlu Tindakan')
            ->assertSee('Order 7 Hari Terakhir')->assertSee('Kas Masuk 7 Hari Terakhir');

        foreach (['Mata Ayam', 'Kisscut', 'Laminasi Panas', 'Laminasi Dingin', 'Lipat Press Banner', 'Slobokan Banner', 'Lebihan Banner'] as $f) {
            $res->assertSee($f);
        }
        $this->assertSame(7, substr_count($res->getContent(), 'class="kartu-finishing'));
    }

    public function test_panduan_finishing_juga_tampil_untuk_operator_desain_dan_produksi(): void
    {
        foreach (['Operator Desain', 'Operator Produksi'] as $nama) {
            $u = User::factory()->create(['id_role' => Role::factory()->create(['nama_role' => $nama])->id_role]);
            $this->actingAs($u)->get('/dashboard')->assertOk()->assertSee('Perbandingan Finishing Item Cetak')->assertSee('Slobokan Banner');
        }
    }
}

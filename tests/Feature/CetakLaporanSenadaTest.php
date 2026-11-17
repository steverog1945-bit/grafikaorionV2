<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Semua cetakan laporan harus MEMAKAI kop & footer yang sama
 * (partials/print-report-header + partials/print-report-footer),
 * bukan kop duplikat manual per halaman.
 */
class CetakLaporanSenadaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['id_role' => Role::factory()->create(['nama_role' => 'Administrator'])->id_role]);
        $this->actingAs($this->admin);
    }

    /** @dataProvider halamanLaporan */
    public function test_halaman_laporan_memuat_kop_dan_footer_bersama(string $path): void
    {
        $html = $this->get($path)->assertOk()->getContent();
        $this->assertStringContainsString('kop-laporan-judul', $html, $path . ': kop laporan bersama tidak dipakai');
        $this->assertStringContainsString('Dicetak pada', $html, $path . ': footer "Dicetak pada … oleh …" tidak dipakai');
    }

    public function test_setting_harga_cetak_memakai_partial_kop_bersama(): void
    {
        $html = $this->get('/setting-harga/cetak')->assertOk()->getContent();
        // Kop bersama menampilkan teks help/id "kop-laporan-judul"; dh-kop
        // duplikat manual sudah dihapus.
        $this->assertStringContainsString('kop-laporan-judul', $html);
        $this->assertStringNotContainsString('dh-kop', $html);
    }

    public function test_footer_mencetak_pembuat_dokumen(): void
    {
        $html = $this->get('/laporan/restock')->assertOk()->getContent();
        $this->assertStringContainsString('Dicetak pada', $html);
        $this->assertStringContainsString('oleh ' . (string) $this->admin->username, $html);
    }

    public static function halamanLaporan(): array
    {
        return [
            'rekap order' => ['/laporan/order'],
            'stok bahan' => ['/laporan/stok-bahan'],
            'produksi' => ['/laporan/produksi'],
            'kas' => ['/laporan/kas'],
            'piutang' => ['/laporan/piutang'],
            'hutang' => ['/laporan/hutang'],
            'hpp' => ['/laporan/hpp'],
            'belanja bahan' => ['/laporan/belanja-bahan'],
            'order jersey' => ['/laporan/order-jersey'],
            'evaluasi master' => ['/laporan/evaluasi-master'],
            'kebocoran tagihan' => ['/laporan/kebocoran-tagihan'],
            'prediksi restock' => ['/laporan/restock'],
            'jurnal' => ['/akuntansi/jurnal'],
            'buku besar' => ['/akuntansi/buku-besar'],
            'laba rugi' => ['/akuntansi/laba-rugi'],
            'neraca' => ['/akuntansi/neraca'],
            'neraca saldo' => ['/akuntansi/neraca-saldo'],
            'perbandingan' => ['/akuntansi/perbandingan'],
        ];
    }
}

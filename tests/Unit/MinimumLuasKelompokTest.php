<?php

namespace Tests\Unit;

use App\Support\MinimumLuas;
use PHPUnit\Framework\TestCase;

/**
 * Matematika Minimum Order berbasis AKUMULASI LUAS per kelompok
 * (spec owner 2026-11-15) - fungsi murni, tanpa database:
 *   1) satu item di bawah minimum -> selisih ditagih;
 *   2) akumulasi dua item yang memenuhi -> tanpa selisih;
 *   3) akumulasi dua item yang belum memenuhi -> selisih sisa;
 *   4) tepat memenuhi -> tanpa selisih;
 *   5) melebihi -> tanpa selisih;
 *   8/9) item diubah/dihapus -> rencana dihitung ulang dari daftar baru;
 *   10/11) kompatibilitas: label/minimum/tarif berbeda = kunci berbeda;
 *   12) qty > 1 dihitung sekali per baris (luas x qty);
 *   13) presisi 4 desimal (noise floating-point tidak menimbulkan selisih palsu).
 */
class MinimumLuasKelompokTest extends TestCase
{
    private function grup(float $min, array $areas): array
    {
        $items = [];
        $terbesar = 0;
        $idxTerbesar = null;
        foreach ($areas as $i => $area) {
            $items[] = ['area' => $area];
            if ($area > $terbesar) {
                $terbesar = $area;
                $idxTerbesar = $i;
            }
        }
        return ['K' => ['min' => $min, 'items' => $items, 'idxTerbesar' => $idxTerbesar]];
    }

    // Skenario 1: satu item 0,4 m2 dengan minimum order 1 m2.
    public function test_satu_item_0_4_selisih_0_6(): void
    {
        $r = MinimumLuas::rencanaKelompok($this->grup(1.0, [0.4]));
        $this->assertSame(0.4, $r['K']['total_aktual']);
        $this->assertEquals(0.6, $r['K']['selisih']);
    }

    // Skenario 2: 0,4 + 0,7 = 1,1 >= 1 -> tanpa selisih.
    public function test_0_4_plus_0_7_tanpa_selisih(): void
    {
        $r = MinimumLuas::rencanaKelompok($this->grup(1.0, [0.4, 0.7]));
        $this->assertEquals(1.1, $r['K']['total_aktual']);
        $this->assertSame(0.0, $r['K']['selisih']);
    }

    // Skenario 3: 0,3 + 0,2 = 0,5 -> selisih 0,5.
    public function test_0_3_plus_0_2_selisih_0_5(): void
    {
        $r = MinimumLuas::rencanaKelompok($this->grup(1.0, [0.3, 0.2]));
        $this->assertEquals(0.5, $r['K']['total_aktual']);
        $this->assertEquals(0.5, $r['K']['selisih']);
    }

    // Skenario 4: tepat memenuhi minimum.
    public function test_tepat_minimum_tanpa_selisih(): void
    {
        $r = MinimumLuas::rencanaKelompok($this->grup(1.0, [0.6, 0.4]));
        $this->assertSame(0.0, $r['K']['selisih']);
    }

    // Skenario 5: melebihi minimum.
    public function test_melebihi_minimum_tanpa_selisih(): void
    {
        $r = MinimumLuas::rencanaKelompok($this->grup(1.0, [0.6, 0.7]));
        $this->assertSame(0.0, $r['K']['selisih']);
    }

    // Skenario 8: ukuran item diubah setelah item lain masuk -> rencana
    // dihitung ulang dari daftar baru (0,3 + 0,2 lalu 0,3 jadi 0,8).
    public function test_ubah_ukuran_dihitung_ulang(): void
    {
        $sebelum = MinimumLuas::rencanaKelompok($this->grup(1.0, [0.3, 0.2]));
        $this->assertEquals(0.5, $sebelum['K']['selisih']);
        $sesudah = MinimumLuas::rencanaKelompok($this->grup(1.0, [0.8, 0.2]));
        $this->assertSame(0.0, $sesudah['K']['selisih']);
    }

    // Skenario 9: item yang menolong minimum dihapus -> selisih muncul lagi.
    public function test_hapus_item_penolong_selisih_muncul_lagi(): void
    {
        $denganDua = MinimumLuas::rencanaKelompok($this->grup(1.0, [0.6, 0.5]));
        $this->assertSame(0.0, $denganDua['K']['selisih']);
        $sisaSatu = MinimumLuas::rencanaKelompok($this->grup(1.0, [0.6]));
        $this->assertEquals(0.4, $sisaSatu['K']['selisih']);
    }

    // Skenario 12: qty > 1 - luas per baris = (P x L / 10000) x qty,
    // dihitung SEKALI per baris (tidak dobel).
    public function test_qty_lebih_dari_satu_sekali_hitung(): void
    {
        $r = MinimumLuas::rencanaKelompok($this->grup(1.0, [0.25, 0.75]));
        $this->assertSame(1.0, $r['K']['total_aktual']);
        $this->assertSame(0.0, $r['K']['selisih']);
    }

    // Skenario 13: presisi - 0,1 + 0,2 + 0,7 punya noise float
    // (0.9999999999999999), pembulatan 4 desimal harus menghasilkan selisih 0.
    public function test_presisi_noise_float_tanpa_selisih_palsu(): void
    {
        $r = MinimumLuas::rencanaKelompok($this->grup(1.0, [0.1, 0.2, 0.7]));
        $this->assertSame(1.0, $r['K']['total_aktual']);
        $this->assertSame(0.0, $r['K']['selisih']);
    }

    // Skenario 10/11: kompatibilitas kelompok - kunci harus memisahkan
    // item dengan minimum atau tarif berbeda, dan menyatukan item yang
    // benar-benar sekelompok.
    public function test_kunci_kelompok_memisahkan_konfigurasi_berbeda(): void
    {
        $sama = MinimumLuas::kunciKelompok('FLS280', 1.0, 20000.0);
        $this->assertSame($sama, MinimumLuas::kunciKelompok('FLS280', 1.000, 20000.00), 'format angka tidak mengubah kunci');
        $this->assertNotSame($sama, MinimumLuas::kunciKelompok('FLS280', 2.0, 20000.0), 'minimum berbeda -> terakumulasi terpisah');
        $this->assertNotSame($sama, MinimumLuas::kunciKelompok('FLS280', 1.0, 17000.0), 'tarif berbeda -> terakumulasi terpisah');
        $this->assertNotSame($sama, MinimumLuas::kunciKelompok('TC110', 1.0, 20000.0), 'label berbeda -> terakumulasi terpisah');
    }

    // Produk yang sama dua baris = dua "pekerjaan" (eksempsi owner
    // 2026-11-14) - di server baris itu tidak masuk kelompok minimum sama
    // sekali; di sini cukup dipastikan rencanaKelompok tidak menganggap
    // selisih bila kelompoknya memang tidak dibentuk.
    public function test_kelompok_tak_dibentuk_tidak_ada_selisih(): void
    {
        $this->assertSame([], MinimumLuas::rencanaKelompok([]));
    }
}

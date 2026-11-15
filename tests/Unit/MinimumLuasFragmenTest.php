<?php
namespace Tests\Unit;

use App\Support\MinimumLuas;
use PHPUnit\Framework\TestCase;

/**
 * Helper catatan minimum (komponen surcharge eksplisit di baris): fragmen
 * harus bulat - ditulis oleh service, dibaca kembali rekonsiliasi, dan
 * dibuang bersih dari catatan manusia saat baris dinormalkan.
 */
class MinimumLuasFragmenTest extends TestCase
{
    public function test_fragmen_format_sama_dengan_yang_ditulis_service(): void
    {
        $this->assertSame('Termasuk Minimum Order Group X: selisih 0.3 m²', MinimumLuas::fragmen('Group X', 0.3));
        // 0,70 -> '0.7' (nol akhir dipangkas, format lama dipertahankan).
        $this->assertSame('Termasuk Minimum Order 12: selisih 1.25 m²', MinimumLuas::fragmen('12', 1.25));
    }

    public function test_selisih_dari_catatan_membaca_fragmen_terakhir(): void
    {
        $catatan = 'Tolong packing rapi. ' . MinimumLuas::fragmen('Group X', 0.7);
        $this->assertSame(0.7, MinimumLuas::selisihDariCatatan($catatan));

        $dua = MinimumLuas::fragmen('A', 0.3) . ' ' . MinimumLuas::fragmen('B', 1.2);
        $this->assertSame(1.2, MinimumLuas::selisihDariCatatan($dua), 'fragmen terakhir yang berlaku');
    }

    public function test_selisih_dari_catatan_tanpa_fragmen_null(): void
    {
        $this->assertNull(MinimumLuas::selisihDariCatatan('catatan biasa'));
        $this->assertNull(MinimumLuas::selisihDariCatatan(''));
    }

    public function test_hapus_fragmen_menyisakan_catatan_manusia(): void
    {
        $catatan = 'Tolong packing rapi. ' . MinimumLuas::fragmen('Group X', 0.3);
        $this->assertSame('Tolong packing rapi.', MinimumLuas::hapusFragmen($catatan));

        $dua = MinimumLuas::fragmen('A', 0.3) . ' ' . MinimumLuas::fragmen('B', 1.2) . ' Catatan manual';
        $this->assertSame('Catatan manual', MinimumLuas::hapusFragmen($dua));

        // Label kelompok memuat ':' tidak boleh membuat fragmen tersangkut.
        $this->assertSame('', MinimumLuas::hapusFragmen(MinimumLuas::fragmen('Kelompok: A', 0.5)));
    }

    public function test_hapus_fragmen_pada_catatan_tanpa_fragmen_tidak_mengubah_isi(): void
    {
        $this->assertSame('catatan biasa', MinimumLuas::hapusFragmen('catatan biasa'));
    }
}

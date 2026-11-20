<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Keterangan desain versi cetak: penanda sistem yang panjang dipendekkan
 * menjadi "FILE SIAP CETAK", penanda ganda ditulis sekali, dan catatan
 * produksi yang diketik orang tidak dipotong.
 */
class KetProduksiRingkasTest extends TestCase
{
    public function test_penanda_siap_cetak_dipendekkan(): void
    {
        $this->assertSame(
            'FILE SIAP CETAK',
            ket_ringkas('[FILE SIAP CETAK - dari pelanggan, tanpa desain tim] ')
        );
    }

    public function test_penanda_ganda_ditulis_sekali(): void
    {
        $this->assertSame(
            'FILE SIAP CETAK',
            ket_ringkas('[FILE SIAP CETAK - dari pelanggan, tanpa desain tim] [FILE SIAP CETAK - dari pelanggan, tanpa desain tim]')
        );
    }

    public function test_catatan_orang_tetap_utuh(): void
    {
        $this->assertSame('lipat jadi 3, jilid lem panas', ket_ringkas('lipat jadi 3, jilid lem panas'));
        $this->assertSame(
            'FILE SIAP CETAK · lipat jadi 3',
            ket_ringkas('[FILE SIAP CETAK - dari pelanggan, tanpa desain tim] lipat jadi 3')
        );
    }

    public function test_kosong_tetap_kosong(): void
    {
        $this->assertSame('', ket_ringkas(null));
        $this->assertSame('', ket_ringkas('   '));
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Menjaga hasil `migrate` dari nol tetap bisa dipakai controller (bukan skema
 * "generasi lama" dari create_complex_system_tables).
 */
class SchemaDriftTest extends TestCase
{
    use RefreshDatabase;

    public function test_supplier_pakai_kontak(): void
    {
        $this->assertTrue(Schema::hasColumn('supplier', 'kontak'));
        $this->assertFalse(Schema::hasColumn('supplier', 'telepon'));
    }

    public function test_pembelian_bahan_tanpa_kolom_lama_not_null(): void
    {
        foreach (['no_faktur_supplier', 'id_supplier', 'tgl_pembelian', 'subtotal'] as $col) {
            $this->assertFalse(Schema::hasColumn('pembelian_bahan', $col), $col);
        }
        foreach (['no_faktur', 'tgl_transaksi', 'grand_total', 'status_pembayaran'] as $col) {
            $this->assertTrue(Schema::hasColumn('pembelian_bahan', $col), $col);
        }
    }

    public function test_setting_harga_punya_kolom_satuan_dan_tanpa_satuan_berlaku(): void
    {
        foreach (['id_satuan', 'qty_berlaku', 'id_satuan_berlaku', 'created_at', 'updated_at'] as $col) {
            $this->assertTrue(Schema::hasColumn('setting_harga', $col), $col);
        }
        $this->assertFalse(Schema::hasColumn('setting_harga', 'satuan_berlaku'));
    }

    public function test_produk_bisa_disimpan_seperti_produk_controller(): void
    {
        $id = DB::table('produk')->insertGetId([
            'nama_produk' => 'Spanduk', 'barcode' => 'X1', 'metode_hitung' => 'ukuran',
            'min_order' => 1, 'min_panjang' => 1, 'min_lebar' => 1, 'status' => 'aktif',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->assertGreaterThan(0, $id);
    }
}

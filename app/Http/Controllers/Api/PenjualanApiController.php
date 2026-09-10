<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HargaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PenjualanApiController extends Controller
{
    public function getDetailProduk($id)
    {
        try {
            $produk = DB::table('produk')->where('id_produk', $id)->first();
            
            if (!$produk) {
                return response()->json(['message' => 'Produk tidak ditemukan'], 404);
            }

            return response()->json($produk);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getHarga($id_produk, $id_golongan, $qty)
    {
        try {
            $hasil = HargaService::getHargaEfektif((int) $id_produk, (int) $id_golongan, (float) $qty);

            return response()->json($hasil);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Indikator ketersediaan stok bahan (BOM) SEBELUM checkout - murni
     * informasi untuk kasir, TIDAK memblokir apa pun. Order tetap boleh
     * dibuat walau bahan kurang (workflow percetakan biasa: order dulu,
     * bahan direstock belakangan sebelum naik cetak) - lihat catatan
     * keputusan di laporan audit sesi ini. Rumus qty_pakai & konversi satuan
     * HARUS sama persis dengan PenjualanController::store(), supaya
     * indikator ini tidak pernah bilang "cukup" padahal nanti sebenarnya
     * kurang (atau sebaliknya).
     */
    public function checkStok(Request $request)
    {
        $idProduk = (int) $request->query('id_produk');
        $p = (float) $request->query('p', 0);
        $l = (float) $request->query('l', 0);
        $qtyInput = (float) $request->query('qty_input', 0);

        $produk = DB::table('produk')->where('id_produk', $idProduk)->first();
        if (!$produk || $qtyInput <= 0) {
            return response()->json(['cukup' => true, 'detail' => []]);
        }

        $qtyHitung = $produk->metode_hitung === 'ukuran'
            ? ($p * $l / 10000) * $qtyInput
            : $qtyInput;

        $boms = DB::table('bom_detail')->where('id_produk', $idProduk)->get();
        $detail = [];
        $cukup = true;

        foreach ($boms as $bom) {
            $qtyPakai = $bom->cara_hitung_pemakaian === '(P*L)/10000' ? $qtyHitung : $qtyInput;
            if ($qtyPakai <= 0) {
                continue;
            }

            $bahan = DB::table('bahan_baku')->where('id_bahan', $bom->id_bahan)->first();
            if (!$bahan) {
                continue;
            }

            $nilaiKonversi = (float) ($bahan->nilai_konversi ?: 1);
            $dibutuhkan = $qtyPakai / $nilaiKonversi;
            $tersedia = (float) $bahan->stok;

            if ($dibutuhkan > $tersedia) {
                $cukup = false;
            }

            $detail[] = [
                'nama_bahan' => $bahan->nama_bahan,
                'dibutuhkan' => round($dibutuhkan, 4),
                'tersedia'   => round($tersedia, 4),
                'satuan'     => $bahan->satuan_stok,
                'cukup'      => $dibutuhkan <= $tersedia,
            ];
        }

        return response()->json(['cukup' => $cukup, 'detail' => $detail]);
    }
}
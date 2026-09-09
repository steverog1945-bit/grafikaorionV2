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
}
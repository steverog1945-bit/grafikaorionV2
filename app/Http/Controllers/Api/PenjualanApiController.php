<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
            $qty = floatval($qty);
            $harga = DB::table('setting_harga')
                ->where('id_produk', $id_produk)
                ->where('id_golongan', $id_golongan)
                ->where('range_min', '<=', $qty)
                ->orderBy('range_min', 'desc')
                ->first();

            return response()->json([
                'harga_jual' => $harga ? $harga->harga_jual : 0
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenjualanApiController extends Controller
{
    /**
     * Ambil detail produk untuk menentukan metode hitung (ukuran/jumlah)
     */
    public function getDetailProduk($id)
    {
        $produk = DB::table('produk')->where('id_produk', $id)->first();
        return response()->json($produk);
    }

    /**
     * Ambil harga jual berdasarkan range Qty dan Golongan Konsumen
     */
    public function getHarga($id_produk, $id_golongan, $qty)
    {
        $harga = DB::table('setting_harga')
            ->where('id_produk', $id_produk)
            ->where('id_golongan', $id_golongan)
            ->where('range_min', '<=', $qty)
            ->where('range_max', '>=', $qty)
            ->first();

        // Fallback jika qty melebihi range atau tidak ditemukan
        if (!$harga) {
            $harga = DB::table('setting_harga')
                ->where('id_produk', $id_produk)
                ->where('id_golongan', $id_golongan)
                ->orderBy('range_max', 'desc')
                ->first();
        }

        return response()->json([
            'harga_jual' => $harga ? $harga->harga_jual : 0
        ]);
    }
}
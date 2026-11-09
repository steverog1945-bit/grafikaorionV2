<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

/**
 * Master Konsumen - API Publik (read-only).
 *
 * GET /api/konsumen
 * GET /api/konsumen/{id}
 */
class KonsumenController extends Controller
{
    public function index()
    {
        // Batasi jendela pembacaan: roster tanpa batas tidak boleh ditumpahkan
        // sekaligus ke token API polos (audit run-1, SA::U7.api-konsumen-pii-overexposure).
        $limit = min(50, max(1, (int) request()->query('limit', 20)));
        $offset = max(0, (int) request()->query('offset', 0));

        $rows = DB::table('konsumen')
            ->select('id_konsumen', 'nama_konsumen', 'telepon', 'alamat', 'id_golongan', 'tipe_konsumen', 'tgl_registrasi')
            ->orderBy('nama_konsumen')
            ->offset($offset)
            ->limit($limit)
            ->get();

        return response()->json([
            'status' => 'success',
            'limit'  => $limit,
            'offset' => $offset,
            'data'   => $rows,
        ]);
    }

    public function show($id)
    {
        $row = DB::table('konsumen')
            ->select('id_konsumen', 'nama_konsumen', 'telepon', 'alamat', 'id_golongan', 'tipe_konsumen', 'tgl_registrasi')
            ->where('id_konsumen', (int) $id)
            ->first();

        if (!$row) {
            return response()->json(['error' => 'Konsumen tidak ditemukan.'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $row,
        ]);
    }
}

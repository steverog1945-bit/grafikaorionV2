<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * CRUD per-baris untuk tier harga Finishing (finishing_harga) - sengaja
 * SATU BARIS SATU AKSI (bukan bulk delete-lalu-insert-ulang seperti pola
 * lama Price List produk) supaya tidak ada kelas bug "baris lain ikut
 * hilang karena key array bentrok" - setiap baris py id_finishing_harga
 * asli di database, add/edit/hapus tidak pernah menyentuh baris lain.
 *
 * SELALU JSON (bukan redirect): dipanggil murni lewat fetch() dari modal
 * "Harga Bertingkat" supaya menambah/edit/hapus satu tier tidak perlu
 * reload seluruh halaman - sebelumnya submit form biasa (full page
 * reload) terasa lambat kalau mau memasukkan beberapa tier berturut-turut.
 */
class FinishingHargaController extends Controller
{
    public function store(Request $request, $idFinishing)
    {
        // numeric (bukan integer) - satuan rentang ini luas m² (finishing
        // metode 'luas') yang pecahan, sama seperti Price List produk. Lihat
        // migrasi 2026_09_11_240000 & catatan di SettingHargaController.
        $request->validate([
            'range_min' => 'required|numeric|min:0.01',
            'range_max' => 'nullable|numeric|gte:range_min',
            'harga'     => 'required|numeric|min:0',
        ]);

        $id = DB::table('finishing_harga')->insertGetId([
            'id_finishing' => $idFinishing,
            'range_min'    => $request->range_min,
            'range_max'    => $request->range_max ?: null,
            'harga'        => $request->harga,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $row = DB::table('finishing_harga')->where('id_finishing_harga', $id)->first();

        // Hardening P0-3: lihat catatan yang sama di ProdukController/SettingHargaController.
        AuditLogger::log('created', 'finishing_harga', (int) $id, null, (array) $row, 'Tier harga finishing baru untuk finishing #' . $idFinishing);

        return response()->json(['success' => true, 'message' => 'Tier harga ditambahkan.', 'row' => $row]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'range_min' => 'required|numeric|min:0.01',
            'range_max' => 'nullable|numeric|gte:range_min',
            'harga'     => 'required|numeric|min:0',
        ]);

        $lama = DB::table('finishing_harga')->where('id_finishing_harga', $id)->first();

        DB::table('finishing_harga')->where('id_finishing_harga', $id)->update([
            'range_min'  => $request->range_min,
            'range_max'  => $request->range_max ?: null,
            'harga'      => $request->harga,
            'updated_at' => now(),
        ]);

        if ($lama) {
            AuditLogger::log('updated', 'finishing_harga', (int) $id,
                ['range_min' => $lama->range_min, 'range_max' => $lama->range_max, 'harga' => $lama->harga],
                ['range_min' => $request->range_min, 'range_max' => $request->range_max ?: null, 'harga' => $request->harga],
                'Tier harga finishing diperbarui');
        }

        return response()->json(['success' => true, 'message' => 'Tier harga diperbarui.']);
    }

    public function destroy($id)
    {
        $row = DB::table('finishing_harga')->where('id_finishing_harga', $id)->first();
        DB::table('finishing_harga')->where('id_finishing_harga', $id)->delete();

        if ($row) {
            AuditLogger::log('deleted', 'finishing_harga', (int) $id, (array) $row, null, 'Tier harga finishing dihapus');
        }

        return response()->json(['success' => true, 'message' => 'Tier harga dihapus.']);
    }
}

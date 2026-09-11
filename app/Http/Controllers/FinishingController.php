<?php

namespace App\Http\Controllers;

use App\Models\Finishing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinishingController extends Controller
{

    public function index()
    {
        $data = Finishing::with('mesin')->orderBy('nama_finishing')->get();
        $mesin = DB::table('mesin')->orderBy('nama_mesin')->get();

        $hargaTiers = DB::table('finishing_harga')
            ->whereIn('id_finishing', $data->pluck('id_finishing'))
            ->orderBy('range_min')
            ->get()
            ->groupBy('id_finishing');

        return view('finishing.index', compact('data', 'mesin', 'hargaTiers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_finishing' => 'required|string|max:100',
            'harga_default'  => 'required|numeric|min:0',
            'id_mesin'       => 'nullable|exists:mesin,id_mesin',
            'metode_hitung'  => 'required|in:luas,quantity',
            'keterangan'     => 'nullable|string',
        ]);

        Finishing::create($validated);

        return redirect()->back()->with('success', 'Finishing berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_finishing' => 'required|string|max:100',
            'harga_default'  => 'required|numeric|min:0',
            'id_mesin'       => 'nullable|exists:mesin,id_mesin',
            'metode_hitung'  => 'required|in:luas,quantity',
            'keterangan'     => 'nullable|string',
        ]);

        Finishing::findOrFail($id)->update($validated);

        return redirect()->back()->with('success', 'Finishing berhasil diperbarui');
    }

    public function destroy($id)
    {
        // Cek pemakaian di BOM Finishing supaya tidak menghapus jenis
        // finishing yang masih dipakai oleh produk manapun.
        $terpakai = DB::table('bom_finishing')->where('id_finishing', $id)->exists();
        if ($terpakai) {
            return redirect()->back()->with('error', 'Gagal hapus! Finishing ini masih digunakan pada BOM Produk.');
        }

        Finishing::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Finishing dihapus');
    }

}

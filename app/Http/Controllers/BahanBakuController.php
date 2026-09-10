<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\BahanBaku;
use App\Models\Kategori; 
use App\Models\Mesin;   
use App\Models\Satuan;

class BahanBakuController extends Controller
{
    public function index(Request $request)
    {
        // Gunakan Eager Loading untuk semua relasi termasuk satuan ganda
        $query = BahanBaku::with(['kategori', 'mesin', 'satuanStok', 'satuanHitung']); 

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nama_bahan', 'like', '%' . $request->search . '%')
                  ->orWhere('kode_bahan', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('kategori')) {
            $query->where('id_kategori', $request->kategori);
        }

        $sortBy = $request->get('sort', 'created_at'); 
        if ($sortBy == 'stok') {
            $query->orderBy('stok', 'asc');
        } elseif ($sortBy == 'nama_bahan') {
            $query->orderBy('nama_bahan', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $bahan = $query->paginate(10);
        $kategori = Kategori::all();
        $mesin = Mesin::all();
        $satuan = Satuan::all();

        return view('master/bahan.index', compact('bahan', 'kategori', 'mesin', 'satuan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_bahan'        => 'required',
            'kode_bahan'        => 'required|unique:bahan_baku,kode_bahan',
            'id_mesin'          => 'required',
            'id_kategori'       => 'required',
            'id_satuan_stok'    => 'required',
            'id_satuan_hitung'  => 'required',
            'nilai_konversi'    => 'required|numeric',
            'stok'              => 'required|numeric',
            'stok_minimal'      => 'required|numeric',
        ]);

        try {
            BahanBaku::create([
                'nama_bahan'       => $request->nama_bahan,
                'kode_bahan'       => $request->kode_bahan,
                'id_mesin'         => $request->id_mesin,
                'id_kategori'      => $request->id_kategori,
                'id_satuan_stok'   => $request->id_satuan_stok,
                'id_satuan_hitung' => $request->id_satuan_hitung,
                'nilai_konversi'   => $request->nilai_konversi,
                'stok'             => $request->stok,
                'stok_minimal'     => $request->stok_minimal,
                'satuan_stok'  => $request->satuan_stok ?? '-',
                'status'           => 'aktif'
            ]);

            return back()->with('success', 'Bahan baku dengan logika konversi berhasil disimpan!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id_bahan)
    {
        $request->validate([
            'nama_bahan'        => 'required',
            'kode_bahan'        => 'required|unique:bahan_baku,kode_bahan,'.$id_bahan.',id_bahan',
            'id_satuan_stok'    => 'required',
            'id_satuan_hitung'  => 'required',
            'nilai_konversi'    => 'required|numeric',
            'stok'              => 'required|numeric',
            'stok_minimal'      => 'required|numeric',
        ]);

        $bahan = BahanBaku::findOrFail($id_bahan);
        $bahan->update($request->only([
            'nama_bahan', 'kode_bahan', 'id_mesin', 'id_kategori', 'stok',
            'stok_minimal', 'id_satuan_stok', 'id_satuan_hitung', 'nilai_konversi',
        ]));

        return redirect()->route('bahan.index')->with('success', 'Data bahan & logika konversi diperbarui!');
    }

    public function destroy($id)
    {
        try {
            BahanBaku::destroy($id);
            return back()->with('success', 'Bahan berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal hapus! Data masih terikat dengan transaksi.');
        }
    }
}
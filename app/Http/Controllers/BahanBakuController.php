<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\BahanBaku;
use App\Models\Kategori;
use App\Models\Mesin;
use App\Models\Satuan;
use App\Models\Merk;
use App\Models\JenisBahan;

class BahanBakuController extends Controller
{
    public function index(Request $request)
    {
        // Gunakan Eager Loading untuk semua relasi termasuk satuan ganda
        $query = BahanBaku::with(['kategori', 'mesin', 'satuanStok', 'satuanHitung', 'merk', 'jenisBahan']);

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
        $merk = Merk::orderBy('nama_merk')->get();
        $jenisBahan = JenisBahan::orderBy('nama_jenis')->get();

        return view('master/bahan.index', compact('bahan', 'kategori', 'mesin', 'satuan', 'merk', 'jenisBahan'));
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
            'nilai_konversi'    => 'required|numeric|min:0.0001',
            'stok'              => 'required|numeric',
            'stok_minimal'      => 'required|numeric',
            'id_merk'           => 'nullable|exists:merk,id_merk',
            'id_jenis_bahan'    => 'nullable|exists:jenis_bahan,id_jenis_bahan',
            'lebar_roll'        => 'nullable|numeric|min:0',
            'panjang_roll'      => 'nullable|numeric|min:0',
        ]);

        try {
            // satuan_stok (kolom string legacy) diturunkan otomatis dari nama
            // satuan yang dipilih di id_satuan_stok - form ini tidak pernah
            // mengirim field teks terpisah untuk itu, jadi sebelumnya selalu
            // tersimpan '-' untuk SEMUA bahan baru (lihat Paper Sublime,
            // id_bahan 13, yang persis menunjukkan gejala ini).
            $namaSatuanStok = Satuan::find($request->id_satuan_stok)->nama_satuan ?? '-';

            BahanBaku::create([
                'nama_bahan'       => $request->nama_bahan,
                'kode_bahan'       => $request->kode_bahan,
                'id_mesin'         => $request->id_mesin,
                'id_kategori'      => $request->id_kategori,
                'id_satuan_stok'   => $request->id_satuan_stok,
                'id_satuan_hitung' => $request->id_satuan_hitung,
                'nilai_konversi'   => $request->nilai_konversi,
                'lebar_roll'       => $request->lebar_roll ?: null,
                'panjang_roll'     => $request->panjang_roll ?: null,
                'stok'             => $request->stok,
                'stok_minimal'     => $request->stok_minimal,
                'satuan_stok'      => $namaSatuanStok,
                'id_merk'          => $request->id_merk ?: null,
                'id_jenis_bahan'   => $request->id_jenis_bahan ?: null,
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
            'nilai_konversi'    => 'required|numeric|min:0.0001',
            'stok'              => 'required|numeric',
            'stok_minimal'      => 'required|numeric',
            'id_merk'           => 'nullable|exists:merk,id_merk',
            'id_jenis_bahan'    => 'nullable|exists:jenis_bahan,id_jenis_bahan',
            'lebar_roll'        => 'nullable|numeric|min:0',
            'panjang_roll'      => 'nullable|numeric|min:0',
        ]);

        $bahan = BahanBaku::findOrFail($id_bahan);

        $data = $request->only([
            'nama_bahan', 'kode_bahan', 'id_mesin', 'id_kategori', 'stok',
            'stok_minimal', 'id_satuan_stok', 'id_satuan_hitung', 'nilai_konversi',
        ]);
        $data['id_merk'] = $request->id_merk ?: null;
        $data['id_jenis_bahan'] = $request->id_jenis_bahan ?: null;
        $data['lebar_roll'] = $request->lebar_roll ?: null;
        $data['panjang_roll'] = $request->panjang_roll ?: null;
        $data['satuan_stok'] = Satuan::find($request->id_satuan_stok)->nama_satuan ?? $bahan->satuan_stok;

        $bahan->update($data);

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
<?php

namespace App\Http\Controllers;

use App\Models\KategoriKasKecil;
use Illuminate\Http\Request;

/**
 * Kelola daftar kategori Kas Kecil (dropdown tetap) - sebelumnya kolom
 * kas_kecil.kategori isian bebas + datalist autocomplete, berisiko variasi
 * penulisan (ATK vs Atk) memecah laporan per kategori. Mengikuti pola CRUD
 * KategoriController (kategori bahan baku), tanpa kolom kode karena tidak
 * dibutuhkan untuk kas kecil.
 */
class KategoriKasKecilController extends Controller
{
    public function index(Request $request)
    {
        $query = KategoriKasKecil::query();

        if ($request->filled('search')) {
            $query->where('nama_kategori', 'like', '%' . $request->search . '%');
        }

        $kategori = $query->orderBy('nama_kategori')->paginate(20)->withQueryString();

        return view('master.kategori-kas-kecil.index', compact('kategori'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:100|unique:kategori_kas_kecil,nama_kategori',
        ], [
            'nama_kategori.unique' => 'Nama kategori sudah ada!',
        ]);

        KategoriKasKecil::create(['nama_kategori' => $request->nama_kategori]);

        return redirect()->route('kategori-kas-kecil.index')->with('success', 'Kategori kas kecil baru berhasil disimpan.');
    }

    public function update(Request $request, KategoriKasKecil $kategoriKasKecil)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:100|unique:kategori_kas_kecil,nama_kategori,' . $kategoriKasKecil->id_kategori_kas_kecil . ',id_kategori_kas_kecil',
        ], [
            'nama_kategori.unique' => 'Nama kategori sudah ada!',
        ]);

        $kategoriKasKecil->update(['nama_kategori' => $request->nama_kategori]);

        return redirect()->route('kategori-kas-kecil.index')->with('success', 'Kategori kas kecil berhasil diperbarui.');
    }

    public function destroy(KategoriKasKecil $kategoriKasKecil)
    {
        if ($kategoriKasKecil->kasKecil()->exists()) {
            return redirect()->route('kategori-kas-kecil.index')->with('error', 'Gagal hapus! Kategori ini masih dipakai oleh catatan Kas Kecil.');
        }

        $kategoriKasKecil->delete();

        return redirect()->route('kategori-kas-kecil.index')->with('success', 'Kategori kas kecil telah dihapus.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\KategoriKasKecil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Kelola daftar kategori Kas Kecil (dropdown tetap) - sebelumnya kolom
 * kas_kecil.kategori isian bebas + datalist autocomplete, berisiko variasi
 * penulisan (ATK vs Atk) memecah laporan per kategori. Mengikuti pola CRUD
 * KategoriController (kategori bahan baku).
 *
 * kode_akun (migrasi add_kode_akun_to_kategori_kas_kecil) menghubungkan
 * tiap kategori ke satu akun Beban di Chart of Accounts - inilah yang
 * membuat pengeluaran Kas Kecil bisa ikut masuk jurnal & Laporan Laba
 * Rugi (lihat KasKecilController::store()), bukan cuma sekadar dropdown
 * dekoratif seperti sebelumnya.
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

        // Cuma akun tipe Beban SELAIN 5000 (HPP) yang relevan sebagai
        // pilihan - HPP punya jalur pencatatan otomatisnya sendiri dari
        // BOM x harga_rata_rata bahan (AccountingService::postPenjualan),
        // tidak pernah dipetakan manual dari kategori kas kecil.
        // Pemasukan dipetakan ke akun Pendapatan/Modal (kecuali Penjualan/Jersey yang punya jalur otomatis sendiri).
        $akunBeban = DB::table('bag_akun')
            ->where('is_active', 1)
            ->where(fn ($q) => $q->where(fn ($b) => $b->where('tipe_akun', 'Beban')->where('kode_akun', '!=', '5000'))
                ->orWhere(fn ($m) => $m->whereIn('tipe_akun', ['Pendapatan', 'Modal'])->whereNotIn('kode_akun', ['4000', '4300']))
                ->orWhere('kode_akun', '1000'))
            ->orderBy('kode_akun')
            ->get();

        return view('master.kategori-kas-kecil.index', compact('kategori', 'akunBeban'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:100|unique:kategori_kas_kecil,nama_kategori',
            'jenis' => 'required|in:Keluar,Masuk',
            'kode_akun' => 'nullable|string|exists:bag_akun,kode_akun',
        ], [
            'nama_kategori.unique' => 'Nama kategori sudah ada!',
        ]);

        KategoriKasKecil::create([
            'nama_kategori' => $request->nama_kategori,
            'jenis' => $request->jenis,
            'kode_akun' => $request->kode_akun ?: null,
        ]);

        return redirect()->route('kategori-kas-kecil.index')->with('success', 'Kategori kas kecil baru berhasil disimpan.');
    }

    public function update(Request $request, KategoriKasKecil $kategoriKasKecil)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:100|unique:kategori_kas_kecil,nama_kategori,' . $kategoriKasKecil->id_kategori_kas_kecil . ',id_kategori_kas_kecil',
            'jenis' => 'required|in:Keluar,Masuk',
            'kode_akun' => 'nullable|string|exists:bag_akun,kode_akun',
        ], [
            'nama_kategori.unique' => 'Nama kategori sudah ada!',
        ]);

        $kategoriKasKecil->update([
            'nama_kategori' => $request->nama_kategori,
            'jenis' => $request->jenis,
            'kode_akun' => $request->kode_akun ?: null,
        ]);

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

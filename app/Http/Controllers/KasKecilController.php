<?php

namespace App\Http\Controllers;

use App\Models\KasKecil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Kas Kecil - pengeluaran non-bahan-utama bernilai kecil (ATK, konsumsi,
 * ongkos kirim, dst) yang sebelumnya tidak punya tempat pencatatan sama
 * sekali. Sengaja terpisah dari kas_harian/kas_manual (kas fisik laci
 * kasir) dan dari pembelian_bahan (belanja bahan baku utama) - lihat
 * catatan desain di migrasi create_kas_kecil_table.
 */
class KasKecilController extends Controller
{
    public function index(Request $request)
    {
        $query = KasKecil::with('user')->orderByDesc('tanggal')->orderByDesc('id');

        $tglDari = $request->filled('tgl_dari') ? $request->tgl_dari : now()->startOfMonth()->format('Y-m-d');
        $tglSampai = $request->filled('tgl_sampai') ? $request->tgl_sampai : now()->format('Y-m-d');
        $query->whereDate('tanggal', '>=', $tglDari)->whereDate('tanggal', '<=', $tglSampai);

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $totalPengeluaran = (clone $query)->sum('nominal');
        $data = $query->paginate(20)->withQueryString();

        $kategoriList = KasKecil::select('kategori')->distinct()->orderBy('kategori')->pluck('kategori');

        return view('kas-kecil.index', compact('data', 'tglDari', 'tglSampai', 'totalPengeluaran', 'kategoriList'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tanggal' => 'required|date',
            'kategori' => 'required|string|max:100',
            'keterangan' => 'required|string|max:500',
            'nominal' => 'required|numeric|min:1',
            'bukti_path' => 'nullable|string|max:255',
        ]);
        $data['id_user'] = Auth::id();

        KasKecil::create($data);

        return back()->with('success', 'Pengeluaran kas kecil berhasil dicatat.');
    }

    public function destroy(KasKecil $kasKecil)
    {
        $kasKecil->delete();

        return back()->with('success', 'Catatan kas kecil dihapus.');
    }

    public function uploadBukti(Request $request)
    {
        $request->validate([
            'bukti' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $path = $request->file('bukti')->store('kas_kecil_bukti', 'public');

        return response()->json([
            'status' => 'success',
            'path' => $path,
            'url' => asset('storage/' . $path),
        ]);
    }
}

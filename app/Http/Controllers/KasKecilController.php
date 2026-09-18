<?php

namespace App\Http\Controllers;

use App\Models\KasKecil;
use App\Models\KategoriKasKecil;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $query = KasKecil::with(['user', 'kategori'])->orderByDesc('tanggal')->orderByDesc('id');

        $tglDari = $request->filled('tgl_dari') ? $request->tgl_dari : now()->format('Y-m-d');
        $tglSampai = $request->filled('tgl_sampai') ? $request->tgl_sampai : now()->format('Y-m-d');
        $query->whereDate('tanggal', '>=', $tglDari)->whereDate('tanggal', '<=', $tglSampai);

        if ($request->filled('id_kategori')) {
            $query->where('id_kategori', $request->id_kategori);
        }

        $totalPengeluaran = (clone $query)->sum('nominal');
        $data = $query->paginate(20)->withQueryString();

        // Kategori sekarang tabel referensi tetap (dropdown), bukan lagi
        // isian bebas - lihat migrasi create_kategori_kas_kecil_table.
        $kategoriList = KategoriKasKecil::orderBy('nama_kategori')->get();

        return view('kas-kecil.index', compact('data', 'tglDari', 'tglSampai', 'totalPengeluaran', 'kategoriList'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tanggal' => 'required|date',
            'id_kategori' => 'required|integer|exists:kategori_kas_kecil,id_kategori_kas_kecil',
            'keterangan' => 'required|string|max:500',
            'nominal' => 'required|numeric|min:1',
            'bukti_path' => 'nullable|string|max:255',
        ]);
        $data['id_user'] = Auth::id();

        // Setiap pengeluaran kas kecil SEKARANG ikut masuk jurnal akuntansi
        // (sebelumnya berdiri sendiri, tidak pernah muncul di Laba Rugi) -
        // akun Beban yang di-debit ditentukan oleh kategori yang dipilih
        // (kategori_kas_kecil.kode_akun). Kalau kategori itu kebetulan
        // belum dipetakan ke akun apa pun (kode_akun kosong), pencatatan
        // kas kecil TETAP disimpan (tidak memblokir operasional harian),
        // tapi jurnalnya dilewati dengan peringatan ke user supaya ketahuan
        // dan bisa dilengkapi lewat menu Kategori Kas Kecil.
        $kategori = KategoriKasKecil::findOrFail($data['id_kategori']);

        $kasKecil = DB::transaction(function () use ($data, $kategori) {
            $kk = KasKecil::create($data);

            if ($kategori->kode_akun) {
                AccountingService::postKasKecil('KK-' . $kk->id, $kategori->kode_akun, (float) $kk->nominal);
            }

            return $kk;
        });

        if (!$kategori->kode_akun) {
            return back()->with('error', "Pengeluaran tersimpan, TAPI kategori \"{$kategori->nama_kategori}\" belum dihubungkan ke akun Beban manapun - nominal ini belum ikut terhitung di Laba Rugi. Lengkapi pemetaan akunnya lewat menu Kategori Kas Kecil.");
        }

        return back()->with('success', 'Pengeluaran kas kecil berhasil dicatat & masuk ke jurnal akuntansi.');
    }

    public function destroy(KasKecil $kasKecil)
    {
        DB::transaction(function () use ($kasKecil) {
            AccountingService::reverseByReferensi('KK-' . $kasKecil->id);
            $kasKecil->delete();
        });

        return back()->with('success', 'Catatan kas kecil dihapus & jurnal akuntansi terkait sudah dibalik.');
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

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
        // Kolom `tanggal` bertipe DATE: bandingkan langsung (sargable), bukan whereDate()/DATE(kolom) yang mematikan indeks.
        $query->where('tanggal', '>=', $tglDari)->where('tanggal', '<=', $tglSampai);

        if ($request->filled('id_kategori')) {
            $query->where('id_kategori', $request->id_kategori);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        // Ringkasan buku kas: pemasukan tunai (kas fisik), pemasukan non-tunai (mis. transfer jersey) & pengeluaran.
        $totalPengeluaran = (float) (clone $query)->where('jenis', 'Keluar')->sum('nominal');
        $masukTunai = (float) (clone $query)->where('jenis', 'Masuk')->where('metode', 'Tunai')->sum('nominal');
        $masukNonTunai = (float) (clone $query)->where('jenis', 'Masuk')->where('metode', '!=', 'Tunai')->sum('nominal');
        $data = $query->paginate(20)->withQueryString();

        // Kategori sekarang tabel referensi tetap (dropdown), bukan lagi
        // isian bebas - lihat migrasi create_kategori_kas_kecil_table.
        $kategoriList = KategoriKasKecil::orderBy('nama_kategori')->get();

        return view('kas-kecil.index', compact('data', 'tglDari', 'tglSampai', 'totalPengeluaran', 'masukTunai', 'masukNonTunai', 'kategoriList'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tanggal' => 'required|date',
            'jenis' => 'required|in:Keluar,Masuk',
            'id_kategori' => 'required|integer|exists:kategori_kas_kecil,id_kategori_kas_kecil',
            'keterangan' => 'required|string|max:500',
            'nominal' => 'required|numeric|min:1',
            'bukti_path' => 'nullable|string|max:255',
        ]);
        $data['id_user'] = Auth::id();
        $data['sumber'] = 'manual';
        $data['metode'] = 'Tunai';

        // Setiap pengeluaran kas kecil SEKARANG ikut masuk jurnal akuntansi
        // (sebelumnya berdiri sendiri, tidak pernah muncul di Laba Rugi) -
        // akun Beban yang di-debit ditentukan oleh kategori yang dipilih
        // (kategori_kas_kecil.kode_akun). Kalau kategori itu kebetulan
        // belum dipetakan ke akun apa pun (kode_akun kosong), pencatatan
        // kas kecil TETAP disimpan (tidak memblokir operasional harian),
        // tapi jurnalnya dilewati dengan peringatan ke user supaya ketahuan
        // dan bisa dilengkapi lewat menu Kategori Kas Kecil.
        $kategori = KategoriKasKecil::findOrFail($data['id_kategori']);

        if ($kategori->jenis !== $data['jenis']) {
            return back()->withInput()->withErrors(['id_kategori' => "Kategori \"{$kategori->nama_kategori}\" adalah kategori " . ($kategori->jenis === 'Masuk' ? 'pemasukan' : 'pengeluaran') . ', tidak cocok dengan jenis yang dipilih.']);
        }

        // Kategori pemasukan berakun 1000 = uang diambil dari LACI kasir untuk mengisi Kas Kecil: butuh sesi kas aktif,
        // dan laci berkurang (baris cermin di Kas Tak Terduga, tanpa jurnal kedua - jurnalnya satu: Dr 1005 / Cr 1000).
        $dariLaci = $data['jenis'] === 'Masuk' && $kategori->kode_akun === '1000';
        $sesi = $dariLaci ? \App\Services\KasSesiService::aktif() : null;
        if ($dariLaci && !$sesi) {
            return back()->withInput()->with('error', 'Kas kasir belum dibuka - buka kas dulu, karena uang pengisi Kas Kecil diambil dari laci.');
        }

        $kasKecil = DB::transaction(function () use ($data, $kategori, $dariLaci, $sesi) {
            $kk = KasKecil::create($data);

            if ($dariLaci) {
                DB::table('kas_manual')->insert([
                    'id_kas' => $sesi->id_kas, 'waktu' => now(), 'keterangan' => "Setor ke Kas Kecil [KK-{$kk->id}] - {$data['keterangan']}",
                    'status' => 'Keluar', 'nominal' => $data['nominal'], 'id_user' => Auth::id(), 'created_at' => now(), 'updated_at' => now(),
                ]);
            }

            if ($kategori->kode_akun) {
                $data['jenis'] === 'Masuk'
                    ? AccountingService::postKasKecilMasuk('KK-' . $kk->id, $kategori->kode_akun, (float) $kk->nominal)
                    : AccountingService::postKasKecil('KK-' . $kk->id, $kategori->kode_akun, (float) $kk->nominal);
            }

            return $kk;
        });

        if (!$kategori->kode_akun) {
            return back()->with('error', "Catatan tersimpan, TAPI kategori \"{$kategori->nama_kategori}\" belum dihubungkan ke akun manapun - nominal ini belum ikut terhitung di jurnal/Laba Rugi. Lengkapi pemetaan akunnya lewat menu Kategori Kas Kecil.");
        }

        return back()->with('success', ($data['jenis'] === 'Masuk' ? 'Pemasukan' : 'Pengeluaran') . ' kas kecil berhasil dicatat & masuk ke jurnal akuntansi.');
    }

    /**
     * Pembatalan catatan kas kecil = MENGAJUKAN ke Approval Workflow
     * (tipe_aksi 'batalkan_kas_kecil'); jurnal dibalik & catatan dihapus
     * saat disetujui (lihat KasKecilService::batalkan).
     */
    public function destroy(Request $request, KasKecil $kasKecil)
    {
        if ($kasKecil->sumber === 'jersey') {
            return back()->with('error', 'Pemasukan ini berasal dari pembayaran order jersey - batalkan lewat Keuangan Jersey (void pembayaran), bukan dari sini.');
        }
        if ($kasKecil->sumber === 'hutang') {
            return back()->with('error', 'Pengeluaran ini berasal dari pembayaran hutang supplier - batalkan lewat void pembayaran di Pembelian Bahan, bukan dari sini.');
        }

        $sudahAda = \App\Models\ApprovalRequest::where('tipe_aksi', 'batalkan_kas_kecil')
            ->where('referensi_id', $kasKecil->id)->where('status', 'Menunggu')->exists();

        if ($sudahAda) {
            return back()->with('error', 'Catatan ini sudah punya pengajuan pembatalan yang menunggu.');
        }

        $alasan = $request->input('alasan') ?: 'Pembatalan catatan kas kecil';

        \App\Services\ApprovalService::ajukan(
            'batalkan_kas_kecil',
            'kas_kecil',
            $kasKecil->id,
            ['alasan' => $alasan],
            "Batalkan kas kecil KK-{$kasKecil->id} (Rp " . number_format($kasKecil->nominal ?? 0, 0, ',', '.') . ')',
            $alasan
        );

        return back()->with('success', 'Pengajuan pembatalan kas kecil dikirim untuk persetujuan Administrator.');
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

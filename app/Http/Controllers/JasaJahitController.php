<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * JASA JAHIT BORONGAN - PENGELUARAN
 *
 * Dua hal yang dealt ini, dipisah karena sifatnya berbeda:
 *
 * 1. KATALOG jenis jahit + harga borongannya. Harga ini disepakati
 *    dengan penjahit, jadi disimpan apa adanya - bukan dihitung dari
 *    bahan atau margin produk.
 *
 * 2. CATATAN pengeluaran: satu jenis jahit yang dijahit untuk satu
 *    baris order, sebanyak berapa, dibayar berapa, tanggalnya kapan.
 *
 * "PENGAMBILAN DATA BERDASAR ORDERAN YANG SEDANG BUTUH JAHIT"
 * --------------------------------------------------------------
 * Yang ditampilkan di form Catat bukan daftar bebas, tapi baris order
 * yang finishing-nya menunjuk salah satu jenis jahit di katalog
 * (jasa_jahit.id_finishing). Jadi kasir tidak perlu mencari nomor nota
 * di antara ratusan order - dia cuma pilih dari yang memang perlu
 * dijahit.
 *
 * Kenapa lewat id_finishing, bukan nama finishing yang mengandung
 * "jahit": nama finishing bebas diisi user. Bisa "Jahit Keliling",
 * "Jahit Tepi Banner", atau "OB Jahitan". Pencocokan teks akan lupa
 * baris yang namanya sedikit berbeda - dan sebaliknya salah menandai
 * yang bukan jahit. Relasi eksplisit tidak punya dua masalah itu.
 *
 * STATUS SPK YANG DITAWARKAN
 * --------------------------
 * Hanya baris yang belum selesai (Antre, Desain, Cetak) yang
 * ditawarkan. Baris 'Selesai' dianggap sudah dikerjakan dan 'Batal'
 * dibatalkan - keduanya tidak masuk daftar karena jahitannya tidak
 * perlu dibayar.
 */
class JasaJahitController extends Controller
{
    /**
     * Status SPK yang jahitannya masih mungkin perlu dibayar.
     * 'Selesai' sengaja tidak ada: kalau sudah selesai dikerjakan,
     *Catatan dibuat untuk documentação ATAU untuk pembayarannya
     * terlambat. Keduanya tetap bisa: field baris order di form Catat
     * menerima pilihan "Lainnya" yang diisi bebas, jadi tidak ada jahit
     * yang mustahil dicatat hanya karena statusnya sudah bergeser.
     */
    private const STATUS_MASIH_DIBUTUHKAN = ['Antre', 'Desain', 'Cetak', 'Pending'];
    public function index(Request $request)
    {
        $jenis = DB::table('jasa_jahit')
            ->leftJoin('satuan', 'jasa_jahit.id_satuan', '=', 'satuan.id_satuan')
            ->leftJoin('finishing', 'jasa_jahit.id_finishing', '=', 'finishing.id_finishing')
            // Jumlah catatan per jenis, supaya tabel katalog bisa
            // menampilkan "sudah terpakai berapa kali". Tanpa ini,
            // mengubah harga borongan tidak terlihat akibatnya: tidak
            // ada yang tahu jenis itu sudah pernah dipakai atau belum.
            ->leftJoinSub(
                DB::table('jasa_jahit_catat')
                    ->select('id_jasa_jahit', DB::raw('COUNT(*) as jumlah_catatan'))
                    ->groupBy('id_jasa_jahit'),
                'c',
                'c.id_jasa_jahit',
                '=',
                'jasa_jahit.id_jasa_jahit'
            )
            ->select(
                'jasa_jahit.*',
                'satuan.nama_satuan',
                'finishing.nama_finishing',
                DB::raw('COALESCE(c.jumlah_catatan, 0) as jumlah_catatan')
            )
            ->orderBy('jasa_jahit.nama_jahit')
            ->get();

        // Baris order yang finishing-nya terpasang di katalog jahit.
        // Dipakai ulang sebagai sumber pilihan di form Catat.
        $orderan = $this->orderanPerluJahit($jenis->pluck('id_finishing')->filter());

        // Riwayat catatan, join ke katalog supaya nama jenisnya ikut.
        // Batas 100 baris: ini daftar kerja, bukan laporan tahunan, dan
        // untuk laporan ada menu Laporan terpisah.
        $riwayat = DB::table('jasa_jahit_catat as c')
            ->join('jasa_jahit as j', 'j.id_jasa_jahit', '=', 'c.id_jasa_jahit')
            ->leftJoin('transaksi_detail as d', 'd.id_detail', '=', 'c.id_transaksi_detail')
            ->leftJoin('transaksi as t', 't.id_transaksi', '=', 'd.id_transaksi')
            ->leftJoin('users as u', 'u.id_user', '=', 'c.id_user')
            ->select(
                'c.*',
                'j.nama_jahit',
                't.nomor_nota',
                'd.nama_file',
                'u.username'
            )
            ->orderBy('c.tgl_catat', 'desc')
            ->orderBy('c.id_jasa_jahit_catat', 'desc')
            ->limit(100)
            ->get();

        $totalBulanIni = (float) DB::table('jasa_jahit_catat')
            ->whereYear('tgl_catat', now()->year)
            ->whereMonth('tgl_catat', now()->month)
            ->sum('total');

        $satuan = DB::table('satuan')->orderBy('nama_satuan')->get();
        $finishing = DB::table('finishing')->orderBy('nama_finishing')->get();

        return view('jasa-jahit.index', compact(
            'jenis', 'orderan', 'riwayat', 'totalBulanIni', 'satuan', 'finishing'
        ));
    }

    /**
     * Baris order yang masih perlu dijahit.
     *
     * @param  \Illuminate\Support\Collection<int>  $idsFinishing
     * @return \Illuminate\Support\Collection
     */
    private function orderanPerluJahit($idsFinishing)
    {
        if ($idsFinishing->isEmpty()) {
            return collect();
        }

        return DB::table('transaksi_detail as d')
            ->join('transaksi as t', 't.id_transaksi', '=', 'd.id_transaksi')
            ->leftJoin('finishing as f', 'f.id_finishing', '=', 'd.id_finishing')
            ->leftJoin('produk as p', 'p.id_produk', '=', 'd.id_produk')
            ->whereIn('d.id_finishing', $idsFinishing)
            ->whereIn('d.status_spk', self::STATUS_MASIH_DIBUTUHKAN)
            // Nota batal tidak perlu dijahit - tidak ada yang dikerjakan.
            ->where('t.status_nota', '!=', 'Dibatalkan')
            ->orderBy('t.tgl_order', 'desc')
            ->select(
                'd.id_detail',
                'd.id_finishing',
                'd.nama_file',
                'd.qty',
                'd.p_ukuran',
                'd.l_ukuran',
                'd.status_spk',
                't.nomor_nota',
                't.tgl_order',
                'f.nama_finishing',
                DB::raw('COALESCE(p.nama_produk, "-") as nama_produk')
            )
            ->get();
    }

    // ---------------------------------------------------------------- katalog

    public function store(Request $request)
    {
        $request->validate([
            'nama_jahit'     => 'required|max:100',
            'id_satuan'      => 'nullable|exists:satuan,id_satuan',
            'id_finishing'   => 'nullable|exists:finishing,id_finishing',
            'harga_borongan' => 'required|numeric|min:0',
            'keterangan'     => 'nullable|max:1000',
        ]);

        // nama_jahit UNIQUE di database. Pelanggaran dibiarkan naik ke
        // handler bawaan Laravel supaya pesannya jujur ("nama sudah
        // dipakai") - bukan ditighthouse diam-diam dan menimpa katalog
        // lama yang mungkin sudah dipakai di catatan.
        DB::table('jasa_jahit')->insert([
            'nama_jahit'     => $request->nama_jahit,
            'id_satuan'      => $request->id_satuan ?: null,
            'id_finishing'   => $request->id_finishing ?: null,
            'harga_borongan' => $request->harga_borongan,
            'keterangan'     => $request->keterangan,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return redirect()->back()->with('success', 'Jenis jahit berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_jahit'     => 'required|max:100',
            'id_satuan'      => 'nullable|exists:satuan,id_satuan',
            'id_finishing'   => 'nullable|exists:finishing,id_finishing',
            'harga_borongan' => 'required|numeric|min:0',
            'keterangan'     => 'nullable|max:1000',
        ]);

        if (! DB::table('jasa_jahit')->where('id_jasa_jahit', $id)->exists()) {
            return redirect()->back()->with('error', 'Jenis jahit tidak ditemukan.');
        }

        // PENTING: harga borongan yang diubah di sini TIDAK mengubah
        // harga_snapshot catatan lama - sudah dibekukan saat pencatatan.
        // Itu disengaja: harga tahun lalu tidak boleh ikut berubah kalau
        // harga borongan naik tahun ini.
        DB::table('jasa_jahit')->where('id_jasa_jahit', $id)->update([
            'nama_jahit'     => $request->nama_jahit,
            'id_satuan'      => $request->id_satuan ?: null,
            'id_finishing'   => $request->id_finishing ?: null,
            'harga_borongan' => $request->harga_borongan,
            'keterangan'     => $request->keterangan,
            'updated_at'     => now(),
        ]);

        return redirect()->back()->with('success', 'Jenis jahit berhasil diperbarui. Catatan lama memakai harga yang sudah dibekukan.');
    }

    public function destroy($id)
    {
        // id_jasa_jahit_catat punya cascadeOnDelete, jadi catatan ikut
        // terhapus. Itu disengaja: jenis jahit dihapus berarti katalognya
        // memang dibubarkan. Tapi karena catatan pengeluaran adalah angka
        // yang sudah keluar, pemanggil harus diberi tahu - makanya
        // jumlahnya dikembalikan di pesan.
        $jumlahCatatan = DB::table('jasa_jahit_catat')->where('id_jasa_jahit', $id)->count();
        $nama = DB::table('jasa_jahit')->where('id_jasa_jahit', $id)->value('nama_jahit');

        if (! $nama) {
            return redirect()->back()->with('error', 'Jenis jahit tidak ditemukan.');
        }

        DB::table('jasa_jahit')->where('id_jasa_jahit', $id)->delete();

        return redirect()->back()->with(
            $jumlahCatatan > 0 ? 'warning' : 'success',
            $jumlahCatatan > 0
                ? "Jenis jahit \"{$nama}\" dihapus bersama {$jumlahCatatan} catatan pengeluarannya."
                : "Jenis jahit \"{$nama}\" dihapus."
        );
    }

    // ---------------------------------------------------------------- catatan

    public function storeCatat(Request $request)
    {
        $request->validate([
            'id_jasa_jahit'       => 'required|exists:jasa_jahit,id_jasa_jahit',
            'id_transaksi_detail' => 'nullable|exists:transaksi_detail,id_detail',
            'qty'                 => 'required|numeric|min:0.0001',
            'tgl_catat'           => 'required|date',
            'catatan'             => 'nullable|max:1000',
        ]);

        $jenis = DB::table('jasa_jahit')->where('id_jasa_jahit', $request->id_jasa_jahit)->first();
        if (! $jenis) {
            return redirect()->back()->with('error', 'Jenis jahit tidak ditemukan.');
        }

        $qty = (float) $request->qty;

        // Harga diambil dari katalog lalu DIBEKUKAN. Client boleh mengirim
        // harga sendiri, dan nilai itu dipakai kalau ada - supaya kasir
        // bisa mencatat harga yang benar-benar disepakati tahun ini, dan
        // catatan lamanya tetap utuh.
        $hargaKatalog = (float) $jenis->harga_borongan;
        $harga = $request->filled('harga')
            ? (float) $request->harga
            : $hargaKatalog;

        if ($harga < 0) {
            return redirect()->back()->with('error', 'Harga tidak boleh negatif.');
        }

        DB::table('jasa_jahit_catat')->insert([
            'id_jasa_jahit'       => $jenis->id_jasa_jahit,
            'id_transaksi_detail' => $request->id_transaksi_detail ?: null,
            'qty'                 => $qty,
            'harga_snapshot'      => $harga,
            'total'               => round($qty * $harga, 2),
            'tgl_catat'           => $request->tgl_catat,
            'catatan'             => $request->catatan,
            'id_user'             => Auth::id(),
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        return redirect()->back()->with('success', 'Pengeluaran jahit berhasil dicatat.');
    }

    public function destroyCatat($id)
    {
        // Catatan pengeluaran TIDAK punya edit. Sifatnya seperti koreksi
        // buku: kalau salah, dihapus lalu dicatat ulang yang benar.
        // Alasannya: kalau boleh diedit, tidak ada jejak bahwa angka
        // yang pernah dibayar ke penjahit berbeda dari yang tercatat -
        // dan untuk pengeluaran, jejak itu yang penting.
        $nama = DB::table('jasa_jahit_catat as c')
            ->join('jasa_jahit as j', 'j.id_jasa_jahit', '=', 'c.id_jasa_jahit')
            ->where('c.id_jasa_jahit_catat', $id)
            ->value('j.nama_jahit');

        if (! $nama) {
            return redirect()->back()->with('error', 'Catatan tidak ditemukan.');
        }

        DB::table('jasa_jahit_catat')->where('id_jasa_jahit_catat', $id)->delete();

        return redirect()->back()->with('success', "Catatan jahit \"{$nama}\" dihapus.");
    }
}

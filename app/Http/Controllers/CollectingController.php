<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CollectingController extends Controller
{
    /**
     * Collecting = status kelengkapan barang per NOTA, dihitung langsung dari
     * transaksi_detail.status_spk (tidak ada tabel/kolom baru). Sebuah nota
     * dianggap LENGKAP kalau semua item di dalamnya sudah Selesai atau Batal
     * (tidak ada lagi yang masih Antre/Desain/Cetak).
     *
     * status_collecting (kolom baru, migrasi 160000) adalah konsep TERPISAH:
     * status PENGAMBILAN FISIK oleh customer, bukan kelengkapan pengerjaan.
     * Dua hal beda yang sebelumnya kebetulan dinamai sama ("Status
     * Collecting" dipakai untuk kelengkapan pengerjaan) - lihat rename di
     * view jadi "Status Pengerjaan" vs "Pengambilan".
     */
    public function index(Request $request)
    {
        $query = DB::table('transaksi')
            ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
            ->leftJoin('transaksi_detail', 'transaksi_detail.id_transaksi', '=', 'transaksi.id_transaksi')
            ->leftJoin('users', 'transaksi.id_user_collecting', '=', 'users.id_user')
            ->select(
                'transaksi.id_transaksi',
                'transaksi.nomor_nota',
                'transaksi.tgl_order',
                'transaksi.status_pembayaran',
                'transaksi.status_collecting',
                'transaksi.tgl_collecting',
                'konsumen.nama_konsumen',
                'users.username as diambil_oleh',
                DB::raw('COUNT(transaksi_detail.id_detail) as total_item'),
                DB::raw("SUM(CASE WHEN transaksi_detail.status_spk IN ('Selesai','Batal') THEN 1 ELSE 0 END) as item_kelar"),
                DB::raw("SUM(CASE WHEN transaksi_detail.status_spk = 'Batal' THEN 1 ELSE 0 END) as item_batal")
            )
            ->groupBy(
                'transaksi.id_transaksi',
                'transaksi.nomor_nota',
                'transaksi.tgl_order',
                'transaksi.status_pembayaran',
                'transaksi.status_collecting',
                'transaksi.tgl_collecting',
                'konsumen.nama_konsumen',
                'users.username'
            );

        if ($request->filled('nama_konsumen')) {
            $query->where('konsumen.nama_konsumen', 'like', '%' . $request->nama_konsumen . '%');
        }

        if ($request->filled('nomor_nota')) {
            $query->where('transaksi.nomor_nota', 'like', '%' . $request->nomor_nota . '%');
        }

        if ($request->filled('tgl_dari')) {
            $query->whereDate('transaksi.tgl_order', '>=', $request->tgl_dari);
        }

        if ($request->filled('tgl_sampai')) {
            $query->whereDate('transaksi.tgl_order', '<=', $request->tgl_sampai);
        }

        // Kelengkapan pengerjaan dihitung dari HAVING karena melibatkan hasil
        // agregasi (kolom turunan) - param request sengaja dinamai beda dari
        // kolom transaksi.status_collecting (konsep pengambilan fisik) supaya
        // tidak ada dua hal berbeda yang bernama sama.
        $statusPengerjaan = $request->input('status_pengerjaan');
        if ($statusPengerjaan === 'lengkap') {
            $query->havingRaw('total_item > 0 AND item_kelar = total_item');
        } elseif ($statusPengerjaan === 'belum') {
            $query->havingRaw('item_kelar < total_item');
        }

        if ($request->filled('status_pengambilan')) {
            $query->where('transaksi.status_collecting', $request->status_pengambilan);
        }

        $collecting = $query->orderByDesc('transaksi.id_transaksi')->paginate(15)->withQueryString();

        return view('collecting.index', compact('collecting'));
    }

    /**
     * Tandai nota sudah diambil customer. Kelengkapan pengerjaan DIHITUNG
     * ULANG di sini (bukan percaya nilai yang sempat tampil di browser) -
     * satu-satunya penjaga sesungguhnya supaya nota dengan item yang masih
     * Antre/Desain/Cetak tidak bisa ditandai selesai diambil.
     */
    public function proses($id)
    {
        return DB::transaction(function () use ($id) {
            $transaksi = DB::table('transaksi')->where('id_transaksi', $id)->lockForUpdate()->first();

            if (!$transaksi) {
                return back()->with('error', 'Transaksi tidak ditemukan.');
            }

            if ($transaksi->status_collecting === 'Sudah Diambil') {
                return back()->with('error', 'Nota ini sudah ditandai diambil sebelumnya.');
            }

            $progres = DB::table('transaksi_detail')
                ->where('id_transaksi', $id)
                ->selectRaw('COUNT(*) as total_item')
                ->selectRaw("SUM(CASE WHEN status_spk IN ('Selesai','Batal') THEN 1 ELSE 0 END) as item_kelar")
                ->first();

            if (!$progres->total_item || $progres->item_kelar < $progres->total_item) {
                $belum = $progres->total_item - $progres->item_kelar;
                return back()->with('error', "Belum bisa diproses - masih ada {$belum} item yang belum selesai dikerjakan.");
            }

            DB::table('transaksi')->where('id_transaksi', $id)->update([
                'status_collecting'   => 'Sudah Diambil',
                'tgl_collecting'      => now(),
                'id_user_collecting'  => Auth::id(),
                'updated_at'          => now(),
            ]);

            return back()->with('success', "Nota {$transaksi->nomor_nota} ditandai sudah diambil.");
        });
    }
}

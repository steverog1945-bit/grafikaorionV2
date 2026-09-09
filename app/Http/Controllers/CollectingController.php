<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CollectingController extends Controller
{
    /**
     * Collecting = status kelengkapan barang per NOTA, dihitung langsung dari
     * transaksi_detail.status_spk (tidak ada tabel/kolom baru). Sebuah nota
     * dianggap LENGKAP kalau semua item di dalamnya sudah Selesai atau Batal
     * (tidak ada lagi yang masih Antre/Desain/Cetak).
     */
    public function index(Request $request)
    {
        $query = DB::table('transaksi')
            ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
            ->leftJoin('transaksi_detail', 'transaksi_detail.id_transaksi', '=', 'transaksi.id_transaksi')
            ->select(
                'transaksi.id_transaksi',
                'transaksi.nomor_nota',
                'transaksi.tgl_order',
                'transaksi.status_pembayaran',
                'konsumen.nama_konsumen',
                DB::raw('COUNT(transaksi_detail.id_detail) as total_item'),
                DB::raw("SUM(CASE WHEN transaksi_detail.status_spk IN ('Selesai','Batal') THEN 1 ELSE 0 END) as item_kelar"),
                DB::raw("SUM(CASE WHEN transaksi_detail.status_spk = 'Batal' THEN 1 ELSE 0 END) as item_batal")
            )
            ->groupBy(
                'transaksi.id_transaksi',
                'transaksi.nomor_nota',
                'transaksi.tgl_order',
                'transaksi.status_pembayaran',
                'konsumen.nama_konsumen'
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

        // status_collecting dihitung dari HAVING karena melibatkan hasil agregasi (kolom turunan).
        $status = $request->input('status_collecting');
        if ($status === 'lengkap') {
            $query->havingRaw('total_item > 0 AND item_kelar = total_item');
        } elseif ($status === 'belum') {
            $query->havingRaw('item_kelar < total_item');
        }

        $collecting = $query->orderByDesc('transaksi.id_transaksi')->paginate(15)->withQueryString();

        return view('collecting.index', compact('collecting'));
    }
}

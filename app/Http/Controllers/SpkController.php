<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpkController extends Controller
{
    /**
     * SPK dibaca langsung dari transaksi_detail yang sudah dibuat oleh Kasir/Penjualan.
     * Tidak ada tabel/duplikasi data baru - hanya menambahkan tampilan & tracking
     * di atas data yang sudah ada.
     */
    public function index(Request $request)
    {
        $query = DB::table('transaksi_detail')
            ->join('transaksi', 'transaksi_detail.id_transaksi', '=', 'transaksi.id_transaksi')
            ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
            ->join('produk', 'transaksi_detail.id_produk', '=', 'produk.id_produk')
            ->leftJoin('mesin', 'produk.id_mesin', '=', 'mesin.id_mesin')
            ->join('users', 'transaksi.id_user_kasir', '=', 'users.id_user')
            ->leftJoin('karyawan', 'users.id_karyawan', '=', 'karyawan.id_karyawan')
            ->select(
                'transaksi_detail.id_detail',
                'transaksi_detail.nama_file',
                'transaksi_detail.qty',
                'transaksi_detail.status_urgent',
                'transaksi_detail.status_spk',
                'transaksi.nomor_nota',
                'transaksi.tgl_order',
                'transaksi.status_pembayaran',
                'konsumen.nama_konsumen',
                'produk.id_mesin',
                'mesin.nama_mesin as divisi',
                DB::raw('COALESCE(karyawan.nama_karyawan, users.username) as pembuat_nota')
            );

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaksi_detail.nama_file', 'like', "%{$search}%")
                  ->orWhere('konsumen.nama_konsumen', 'like', "%{$search}%")
                  ->orWhere('transaksi.nomor_nota', 'like', "%{$search}%");
            });
        }

        if ($request->filled('divisi')) {
            $query->where('produk.id_mesin', $request->divisi);
        }

        if ($request->filled('status_spk')) {
            $query->where('transaksi_detail.status_spk', $request->status_spk);
        }

        if ($request->boolean('urgent_only')) {
            $query->where('transaksi_detail.status_urgent', 1);
        }

        $spk = $query->orderByDesc('transaksi_detail.id_detail')->paginate(15)->withQueryString();

        // Divisi diambil dari data master mesin yang benar-benar dipakai produk,
        // bukan hard-code. Submenu akan otomatis bertambah kalau ada mesin/divisi baru.
        $divisis = DB::table('mesin')
            ->join('produk', 'produk.id_mesin', '=', 'mesin.id_mesin')
            ->join('transaksi_detail', 'transaksi_detail.id_produk', '=', 'produk.id_produk')
            ->select('mesin.id_mesin', 'mesin.nama_mesin')
            ->distinct()
            ->orderBy('mesin.nama_mesin')
            ->get();

        return view('spk.index', compact('spk', 'divisis'));
    }

    public function toggleUrgent($id)
    {
        $detail = DB::table('transaksi_detail')->where('id_detail', $id)->first();

        if (!$detail) {
            return back()->with('error', 'Item SPK tidak ditemukan.');
        }

        DB::table('transaksi_detail')->where('id_detail', $id)->update([
            'status_urgent' => !$detail->status_urgent,
        ]);

        return back()->with('success', 'Status urgent diperbarui.');
    }

    public function bulkUrgent(Request $request)
    {
        $ids = $request->ids;

        if (!$ids) {
            return back()->with('error', 'Tidak ada data dipilih.');
        }

        DB::table('transaksi_detail')->whereIn('id_detail', $ids)->update([
            'status_urgent' => true,
        ]);

        return back()->with('success', count($ids) . ' item ditandai urgent.');
    }
}

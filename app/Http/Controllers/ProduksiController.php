<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProduksiController extends Controller
{
    /**
     * Produksi membaca SPK yang sama (transaksi_detail). "ON PROGRESS" / "SELESAI"
     * diturunkan dari status_spk yang sudah ada (Antre/Desain/Cetak dianggap ON
     * PROGRESS, Selesai/Batal punya statusnya sendiri) - tidak menambah kolom
     * status baru yang duplikat dengan status_spk.
     */
    public function index(Request $request)
    {
        $query = DB::table('transaksi_detail')
            ->join('transaksi', 'transaksi_detail.id_transaksi', '=', 'transaksi.id_transaksi')
            ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
            ->join('produk', 'transaksi_detail.id_produk', '=', 'produk.id_produk')
            ->leftJoin('mesin', 'produk.id_mesin', '=', 'mesin.id_mesin')
            ->select(
                'transaksi_detail.id_detail',
                'transaksi_detail.nama_file',
                'transaksi_detail.qty',
                'transaksi_detail.qty_cetak',
                'transaksi_detail.status_urgent',
                'transaksi_detail.status_spk',
                'transaksi_detail.catatan_produksi',
                'transaksi.nomor_nota',
                'transaksi.tgl_order',
                'konsumen.nama_konsumen',
                'produk.nama_produk',
                'mesin.nama_mesin as divisi'
            );

        if ($request->filled('konsumen')) {
            $query->where('konsumen.nama_konsumen', 'like', '%' . $request->konsumen . '%');
        }

        if ($request->filled('tgl_dari')) {
            $query->whereDate('transaksi.tgl_order', '>=', $request->tgl_dari);
        }

        if ($request->filled('tgl_sampai')) {
            $query->whereDate('transaksi.tgl_order', '<=', $request->tgl_sampai);
        }

        if ($request->filled('id_spk')) {
            $query->where('transaksi_detail.id_detail', $request->id_spk);
        }

        // Default 'progress' kalau parameter tidak dikirim sama sekali (mis. setelah
        // redirect back() dari aksi simpan/selesai/batal) - supaya perilakunya selalu
        // konsisten dengan pilihan default di dropdown "Status Produksi".
        $statusProduksi = $request->input('status_produksi', 'progress');

        if ($statusProduksi === 'selesai') {
            $query->where('transaksi_detail.status_spk', 'Selesai');
        } elseif ($statusProduksi === 'batal') {
            $query->where('transaksi_detail.status_spk', 'Batal');
        } else {
            $query->whereIn('transaksi_detail.status_spk', ['Antre', 'Desain', 'Cetak']);
        }

        $produksi = $query->orderByDesc('transaksi_detail.id_detail')->paginate(15)->withQueryString();

        return view('produksi.index', compact('produksi'));
    }

    public function simpanQty(Request $request, $id)
    {
        $request->validate([
            'qty_cetak' => 'required|integer|min:0',
            'catatan_produksi' => 'nullable|string|max:1000',
        ]);

        $detail = DB::table('transaksi_detail')->where('id_detail', $id)->first();

        if (!$detail) {
            return back()->with('error', 'Item SPK tidak ditemukan.');
        }

        if (in_array($detail->status_spk, ['Selesai', 'Batal'])) {
            return back()->with('error', 'Item ini sudah ' . $detail->status_spk . ', tidak bisa diubah lagi.');
        }

        DB::transaction(function () use ($request, $id, $detail) {
            DB::table('transaksi_detail')->where('id_detail', $id)->update([
                'qty_cetak' => $request->qty_cetak,
                'catatan_produksi' => $request->catatan_produksi,
                // Begitu jumlah cetak diinput, status bergerak maju ke "Cetak"
                // (kecuali sudah lebih maju dari itu).
                'status_spk' => $detail->status_spk === 'Antre' ? 'Cetak' : $detail->status_spk,
            ]);
        });

        return back()->with('success', 'Jumlah produksi disimpan.');
    }

    public function selesai(Request $request, $id)
    {
        $detail = DB::table('transaksi_detail')->where('id_detail', $id)->first();

        if (!$detail) {
            return back()->with('error', 'Item SPK tidak ditemukan.');
        }

        if (in_array($detail->status_spk, ['Selesai', 'Batal'])) {
            return back()->with('error', 'Item ini sudah ' . $detail->status_spk . '.');
        }

        DB::transaction(function () use ($request, $id, $detail) {
            DB::table('transaksi_detail')->where('id_detail', $id)->update([
                // Kalau qty_cetak belum pernah diisi, dianggap sesuai qty order.
                'qty_cetak' => $detail->qty_cetak ?? $detail->qty,
                'status_spk' => 'Selesai',
            ]);
        });

        return back()->with('success', 'Item produksi ditandai selesai.');
    }

    public function cancel(Request $request, $id)
    {
        $detail = DB::table('transaksi_detail')->where('id_detail', $id)->first();

        if (!$detail) {
            return back()->with('error', 'Item SPK tidak ditemukan.');
        }

        if (in_array($detail->status_spk, ['Selesai', 'Batal'])) {
            return back()->with('error', 'Item ini sudah ' . $detail->status_spk . ', tidak bisa dibatalkan.');
        }

        // Cancel murni mengubah status - TIDAK menyentuh stok bahan baku maupun
        // total transaksi, karena mekanisme potong-stok-otomatis di aplikasi ini
        // belum konsisten (lihat catatan audit). Mengembalikan stok secara otomatis
        // di sini berisiko malah salah hitung. Koreksi stok manual tetap lewat
        // Kartu Stok jika memang barang belum jadi dipakai.
        DB::transaction(function () use ($request, $id) {
            DB::table('transaksi_detail')->where('id_detail', $id)->update([
                'status_spk' => 'Batal',
                'catatan_produksi' => $request->input('alasan_batal'),
            ]);
        });

        return back()->with('success', 'Item SPK dibatalkan.');
    }
}

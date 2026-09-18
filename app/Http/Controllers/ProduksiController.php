<?php

namespace App\Http\Controllers;

use App\Services\StokReversalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        } elseif ($statusProduksi === 'pending') {
            // Item yang dibatalkan dengan tindakan "Pending" (lihat
            // ProduksiController::cancel) - diparkir di sini untuk ditinjau,
            // keputusan akhirnya (Antre lagi / Batal) diselesaikan dari
            // halaman SPK (lihat SpkController::resolvePending).
            $query->where('transaksi_detail.status_spk', 'Pending');
        } elseif ($statusProduksi === 'semua') {
            // Dipakai oleh quick-link "Lihat di Produksi" (SPK) dan "Lihat progres
            // di Produksi" (Collecting) - keduanya menuju item/konsumen spesifik
            // yang statusnya bisa Selesai/Batal, jadi TIDAK boleh disaring oleh
            // bucket default (Antre/Desain/Cetak) atau linknya mendarat di
            // halaman kosong walau datanya ada.
        } else {
            // 'Antre' SENGAJA tidak diikutkan di sini: SPK yang masih Antre
            // berarti belum ada yang mengambil pekerjaannya (lihat
            // SpkController::ambilPekerjaan) - Produksi hanya menampilkan
            // item yang sudah eksplisit diambil (Desain) atau sudah dicatat
            // qty cetaknya (Cetak). Antre tetap terlihat di halaman SPK.
            $query->whereIn('transaksi_detail.status_spk', ['Desain', 'Cetak']);
        }

        $produksi = $query->orderByDesc('transaksi_detail.id_detail')->paginate(20)->withQueryString();

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
                // (kecuali sudah lebih maju dari itu). 'Antre' dipertahankan di
                // sini untuk jaga-jaga walau sekarang seharusnya sudah 'Desain'
                // dulu (lihat SpkController::ambilPekerjaan) sebelum item ini
                // bisa muncul di Produksi sama sekali.
                'status_spk' => in_array($detail->status_spk, ['Antre', 'Desain']) ? 'Cetak' : $detail->status_spk,
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

        // Sebelumnya "Batalkan SPK" MURNI berarti status_spk = 'Batal' - status
        // akhir mati tanpa jalan balik selain koreksi manual ke database.
        // Sekarang operator memilih TINDAKAN SELANJUTNYA secara eksplisit di
        // dialog konfirmasi: kembalikan ke antrian SPK biasa (item bisa
        // diambil ulang siapa saja), diparkir Pending (belum diputuskan, tidak
        // ikut antrian aktif), atau batal permanen (seperti sebelumnya).
        $tindakan = $request->input('tindakan', 'batal');
        $statusBaru = match ($tindakan) {
            'antre'   => 'Antre',
            'pending' => 'Pending',
            default   => 'Batal',
        };

        // Stok bahan baku yang sudah dipotong otomatis saat order dibuat
        // (lihat PenjualanController::store) TIDAK otomatis dikembalikan -
        // material kadang sudah terlanjur dipakai/rusak sebelum pembatalan
        // dicatat, jadi mengembalikan stok begitu saja bisa salah. Checkbox
        // ini independen dari tindakan (mau balik Antre/Pending/Batal, stok
        // yang sudah terlanjur terpotong tetap bisa dikembalikan kalau
        // memang belum dipakai). Rumusnya lihat StokReversalService - PERSIS
        // sama dengan rumus potong stok saat order dibuat.
        $kembalikanStok = $request->boolean('kembalikan_stok');
        $ringkasanStok = [];

        DB::transaction(function () use ($request, $id, $detail, $statusBaru, $kembalikanStok, &$ringkasanStok) {
            DB::table('transaksi_detail')->where('id_detail', $id)->update([
                'status_spk' => $statusBaru,
                'catatan_produksi' => $request->input('alasan_batal'),
            ]);

            if (!$kembalikanStok) {
                return;
            }

            $ringkasanStok = StokReversalService::kembalikan(
                $detail,
                'BATAL-SPK-' . $id,
                'Pembatalan produksi (SPK #' . $id . ') - stok dikembalikan'
            );
        });

        $labelTindakan = [
            'Antre'   => 'dikembalikan ke antrian SPK',
            'Pending' => 'ditandai Pending, menunggu keputusan lebih lanjut',
            'Batal'   => 'dibatalkan permanen',
        ][$statusBaru];

        $pesan = "Item SPK {$labelTindakan}.";
        if ($kembalikanStok) {
            $pesan .= !empty($ringkasanStok)
                ? ' Stok dikembalikan: ' . implode(', ', $ringkasanStok) . '.'
                : ' Tidak ada bahan BOM untuk dikembalikan pada item ini.';
        }

        return back()->with('success', $pesan);
    }
}

<?php

namespace App\Http\Controllers;

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

        // Sebelumnya cancel MURNI mengubah status - stok bahan baku yang sudah
        // dipotong otomatis saat order dibuat (lihat PenjualanController::store,
        // sudah diverifikasi benar & konsisten di audit Satuan & Konversi
        // sesi ini - alasan lama "mekanisme potong stok belum konsisten" sudah
        // tidak berlaku lagi) TIDAK PERNAH dikembalikan, jadi satu-satunya
        // jalan keluar adalah koreksi manual lewat Kartu Stok - gampang lupa
        // dan tidak ada jejak yang menjelaskan KENAPA stok itu dikoreksi.
        //
        // Sekarang operator diberi PILIHAN eksplisit saat membatalkan (checkbox
        // di dialog konfirmasi) - bukan otomatis tanpa syarat - karena material
        // kadang sudah terlanjur dipakai/rusak sebelum pembatalan dicatat, jadi
        // mengembalikan stok begitu saja bisa salah. Kalau dipilih, qty yang
        // dikembalikan DIHITUNG ULANG dari data yang sudah tersimpan di baris
        // ini sendiri (p_ukuran/l_ukuran/qty + BOM produk) - rumus yang PERSIS
        // sama dengan yang dipakai saat stok dipotong pertama kali - bukan
        // menelusuri baris kartu_stok lama (referensi di sana level NOTA, bukan
        // per-item, jadi tidak bisa dipisah kalau satu nota punya banyak item).
        $kembalikanStok = $request->boolean('kembalikan_stok');
        $ringkasanStok = [];

        DB::transaction(function () use ($request, $id, $detail, $kembalikanStok, &$ringkasanStok) {
            DB::table('transaksi_detail')->where('id_detail', $id)->update([
                'status_spk' => 'Batal',
                'catatan_produksi' => $request->input('alasan_batal'),
            ]);

            if (!$kembalikanStok || !$detail->id_produk) {
                return;
            }

            $produk = DB::table('produk')->where('id_produk', $detail->id_produk)->first();
            if (!$produk) {
                return;
            }

            $qtyHitung = $produk->metode_hitung === 'ukuran'
                ? ((float) $detail->p_ukuran * (float) $detail->l_ukuran / 10000) * (float) $detail->qty
                : (float) $detail->qty;

            $boms = DB::table('bom_detail')->where('id_produk', $detail->id_produk)->get();

            foreach ($boms as $bom) {
                $qtyPakai = $bom->cara_hitung_pemakaian === '(P*L)/10000' ? $qtyHitung : (float) $detail->qty;
                if ($qtyPakai <= 0) {
                    continue;
                }

                $bahan = DB::table('bahan_baku')->where('id_bahan', $bom->id_bahan)->lockForUpdate()->first();
                if (!$bahan) {
                    continue;
                }

                $nilaiKonversi = (float) ($bahan->nilai_konversi ?: 1);
                if ($nilaiKonversi <= 0) {
                    $nilaiKonversi = 1;
                }
                $qtyKembali = $qtyPakai / $nilaiKonversi;

                $stokSebelum = (float) $bahan->stok;
                $stokSesudah = $stokSebelum + $qtyKembali;

                DB::table('bahan_baku')->where('id_bahan', $bom->id_bahan)->update(['stok' => $stokSesudah]);

                DB::table('kartu_stok')->insert([
                    'id_bahan'     => $bom->id_bahan,
                    'tanggal'      => now(),
                    'tipe'         => 'IN',
                    'qty'          => $qtyKembali,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,
                    'referensi'    => 'BATAL-SPK-' . $id,
                    'keterangan'   => 'Pembatalan produksi (SPK #' . $id . ') - stok dikembalikan',
                    'created_by'   => Auth::id(),
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);

                $ringkasanStok[] = $bahan->nama_bahan . ' +' . number_format($qtyKembali, 2);
            }
        });

        $pesan = 'Item SPK dibatalkan.';
        if ($kembalikanStok) {
            $pesan .= !empty($ringkasanStok)
                ? ' Stok dikembalikan: ' . implode(', ', $ringkasanStok) . '.'
                : ' Tidak ada bahan BOM untuk dikembalikan pada item ini.';
        }

        return back()->with('success', $pesan);
    }
}

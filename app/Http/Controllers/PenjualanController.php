<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PenjualanController extends Controller
{
    public function create()
{
    $kategori = DB::table('kategori')->get();
    
    // Ambil data produk lengkap dengan id_kategori dari bahan bakunya
    $produk = DB::table('produk')
    ->leftJoin('bom_detail', 'produk.id_produk', '=', 'bom_detail.id_produk')
    ->leftJoin('bahan_baku', 'bom_detail.id_bahan', '=', 'bahan_baku.id_bahan')
    ->select('produk.*', DB::raw('COALESCE(bahan_baku.id_kategori, 0) as id_kategori'))
    ->get()
    ->unique('id_produk');

    $golongan = DB::table('golongan_konsumen')->get();
    $konsumen = DB::table('konsumen')->get();
    
    // Generate No. Nota
    $date = date('Ymd');
    $lastNota = DB::table('transaksi')->whereDate('tgl_order', date('Y-m-d'))->count();
    $no_nota = "INV-" . $date . "-" . str_pad($lastNota + 1, 3, '0', STR_PAD_LEFT);

    return view('penjualan.create', compact('produk', 'golongan', 'konsumen', 'no_nota', 'kategori'));
}

public function getHarga($id_produk, $id_golongan, $qty) {
    $qty = floatval($qty);

    // Query ke tabel setting_harga sesuai dump SQL Anda
    $harga = DB::table('setting_harga')
        ->where('id_produk', $id_produk)
        ->where('id_golongan', $id_golongan)
        ->where('range_min', '<=', $qty) // Di database Anda namanya 'range_min'
        ->orderBy('range_min', 'desc')
        ->first();

    return response()->json([
        'harga_jual' => $harga ? $harga->harga_jual : 0
    ]);
}
    public function store(Request $request)
{
    if (!$request->items || count($request->items) == 0) {
        return response()->json(['status' => 'error', 'msg' => 'Keranjang kosong!'], 400);
    }

    if (!$request->id_konsumen) {
        return response()->json([
            'status' => 'error',
            'msg' => 'Konsumen wajib dipilih'
        ], 400);
    }

    if (!$request->id_golongan) {
        return response()->json([
            'status' => 'error',
            'msg' => 'Golongan konsumen wajib diisi'
        ], 400);
    }

    // Harga & subtotal TIDAK PERNAH dipercaya dari client (lihat catatan audit:
    // harga sebelumnya dikirim mentah-mentah dari browser tanpa verifikasi ulang,
    // sehingga siapa pun yang mengubah request bisa menetapkan harga sembarang).
    // Di sini setiap item dihitung ULANG dari data master (produk + setting_harga)
    // menggunakan hanya input mentah yang tidak bisa "dipalsukan" jadi lebih murah
    // (p, l, qty) - harga per unit dan subtotal akhir murni hasil hitungan server.
    $itemsTervalidasi = [];

    foreach ($request->items as $index => $item) {
        $produk = DB::table('produk')->where('id_produk', $item['id_produk'] ?? null)->first();

        if (!$produk) {
            return response()->json(['status' => 'error', 'msg' => 'Produk pada baris ' . ($index + 1) . ' tidak ditemukan.'], 422);
        }

        $p = (float) ($item['p'] ?? 0);
        $l = (float) ($item['l'] ?? 0);
        $qtyInput = (float) ($item['qty_input'] ?? 0);

        if ($qtyInput <= 0) {
            return response()->json(['status' => 'error', 'msg' => 'Qty pada baris ' . ($index + 1) . ' tidak valid.'], 422);
        }

        // Rumus qty-untuk-harga HARUS sama persis dengan hitungSubtotal() di
        // penjualan/create.blade.php, supaya harga yang ditagih konsisten
        // dengan yang ditampilkan ke operator sebelum disimpan.
        $qtyHitung = $produk->metode_hitung === 'ukuran'
            ? ($p * $l / 10000) * $qtyInput
            : $qtyInput;

        if ($qtyHitung <= 0) {
            return response()->json(['status' => 'error', 'msg' => 'Ukuran/qty pada baris ' . ($index + 1) . ' tidak valid.'], 422);
        }

        $hargaRow = DB::table('setting_harga')
            ->where('id_produk', $produk->id_produk)
            ->where('id_golongan', $request->id_golongan)
            ->where('range_min', '<=', $qtyHitung)
            ->orderBy('range_min', 'desc')
            ->first();

        $hargaSatuan = $hargaRow ? (float) $hargaRow->harga_jual : 0;

        if ($hargaSatuan <= 0) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Harga untuk "' . $produk->nama_produk . '" belum diatur di Price List untuk golongan konsumen ini.',
            ], 422);
        }

        // biaya_desain memang input manual operator (tidak ada tabel acuannya),
        // hanya dipastikan tidak negatif supaya tidak bisa dipakai mengurangi total.
        $biayaDesain = max(0, (float) ($item['biaya_desain'] ?? 0));

        $itemsTervalidasi[] = [
            'id_produk' => $produk->id_produk,
            'nama_file' => $item['nama_file'] ?? '-',
            'p' => $p,
            'l' => $l,
            'qty_input' => $qtyInput,
            'qty_hitung' => $qtyHitung,
            'subtotal' => ($hargaSatuan * $qtyHitung) + $biayaDesain,
        ];
    }

    DB::beginTransaction();

    try {

        $grand_total = collect($itemsTervalidasi)->sum('subtotal');

        $id_transaksi = DB::table('transaksi')->insertGetId([
            'nomor_nota'        => $request->no_nota,
            'id_konsumen'       => $request->id_konsumen,
            'id_user_kasir'     => Auth::id() ?? 1,
            'tgl_order'         => now(),
            'total_bayar'       => $grand_total,
            'status_pembayaran' => 'Belum Bayar',
        ]);

        foreach ($itemsTervalidasi as $item) {

            $jumlah_order = $item['qty_input'];

            DB::table('transaksi_detail')->insert([
                'id_transaksi' => $id_transaksi,
                'id_produk'    => $item['id_produk'],
                'nama_file'    => $item['nama_file'],
                'p_ukuran'     => $item['p'],
                'l_ukuran'     => $item['l'],
                'qty'          => $jumlah_order,
                'subtotal'     => $item['subtotal'],
                'status_spk'   => 'Antre',
            ]);

            $boms = DB::table('bom_detail')
                ->where('id_produk', $item['id_produk'])
                ->get();

            foreach ($boms as $bom) {

                $qty_pakai = 0;

                if ($bom->cara_hitung_pemakaian == '(P*L)/10000') {
                    $qty_pakai = $item['qty_hitung'] ?? 0;
                } else {
                    $qty_pakai = $jumlah_order;
                }

                if ($qty_pakai > 0) {
                    DB::table('bahan_baku')
                        ->where('id_bahan', $bom->id_bahan)
                        ->decrement('stok', $qty_pakai);
                }
            }
        }

        DB::commit();

        return response()->json([
            'status' => 'success',
            'msg' => 'Transaksi Berhasil',
            'id_transaksi' => $id_transaksi
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'status' => 'error',
            'msg' => $e->getMessage()
        ], 500);
    }
}

/**
 * Riwayat Nota - daftar transaksi penjualan dengan filter range tanggal.
 * Default: hanya menampilkan transaksi HARI INI jika tidak ada filter
 * tanggal yang dikirim. Murni query baca, tidak mengubah data apa pun.
 */
public function riwayat(Request $request)
{
    $from = $request->filled('from') ? $request->from : date('Y-m-d');
    $to   = $request->filled('to') ? $request->to : date('Y-m-d');

    $transaksis = DB::table('transaksi')
        ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
        ->leftJoin('users', 'transaksi.id_user_kasir', '=', 'users.id_user')
        ->leftJoin('karyawan', 'users.id_karyawan', '=', 'karyawan.id_karyawan')
        ->whereDate('tgl_order', '>=', $from)
        ->whereDate('tgl_order', '<=', $to)
        ->select(
            'transaksi.*',
            'konsumen.nama_konsumen',
            'karyawan.nama_karyawan as nama_kasir'
        )
        ->orderByDesc('tgl_order')
        ->paginate(15);

    return view('penjualan.riwayat', compact('transaksis', 'from', 'to'));
}
}
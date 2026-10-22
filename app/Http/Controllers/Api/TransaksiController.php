<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * Transaksi Penjualan - API Publik.
 *
 * GET /api/transaksi - Daftar nota (dibatasi milik user yang login)
 * GET /api/transaksi/{id} - Detail nota
 * POST /api/transaksi - Buat nota baru (JSON payload)
 */
class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = DB::table('transaksi')
            ->select('id_transaksi', 'nomor_nota', 'tgl_order', 'total_bayar', 'status_pembayaran', 'status_nota', 'status_order', 'id_konsumen')
            ->where('id_user_kasir', $user->id_user)
            ->orderByDesc('id_transaksi');

        if ($request->filled('status_nota')) {
            $query->where('status_nota', $request->status_nota);
        }

        $rows = $query->paginate(20);

        return response()->json([
            'status' => 'success',
            'data'   => $rows->items(),
            'meta'   => [
                'current_page' => $rows->currentPage(),
                'last_page'    => $rows->lastPage(),
                'per_page'     => $rows->perPage(),
                'total'        => $rows->total(),
            ],
        ]);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();

        $row = DB::table('transaksi')
            ->where('id_transaksi', $id)
            ->where('id_user_kasir', $user->id_user)
            ->first();

        if (!$row) {
            return response()->json(['error' => 'Transaksi tidak ditemukan.'], 404);
        }

        // Detail item
        $items = DB::table('transaksi_detail')
            ->where('id_transaksi', $id)
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => array_merge((array) $row, ['items' => $items]),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'id_konsumen' => 'required|exists:konsumen,id_konsumen',
            'items'       => 'required|array|min:1',
            'items.*.id_produk'   => 'required|exists:produk,id_produk',
            'items.*.qty_input'   => 'required|numeric|min:0.0001',
            'items.*.p'           => 'nullable|numeric|min:0',
            'items.*.l'           => 'nullable|numeric|min:0',
            'items.*.nama_file'   => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $kasHariIni = \App\Services\KasSesiService::aktifUntukTransaksi();
            if (!$kasHariIni) {
                return response()->json(['error' => 'Kasir belum dibuka.'], 400);
            }

            $prefixNota = \App\Models\AppSetting::current()->nomor_nota_prefix ?: 'INV';
            $noNota = \App\Services\DocumentNumberService::generate($prefixNota);

            $grandTotal = 0;
            $detailRows = [];

            foreach ($request->items as $item) {
                $produk = DB::table('produk')->where('id_produk', $item['id_produk'])->first();
                if (!$produk) {
                    throw new \Exception('Produk tidak ditemukan: ' . $item['id_produk']);
                }

                $p = (float) ($item['p'] ?? 0);
                $l = (float) ($item['l'] ?? 0);
                $qtyInput = (float) $item['qty_input'];
                $qtyHitung = $produk->metode_hitung === 'ukuran' ? ($p * $l / 10000) * $qtyInput : $qtyInput;

                $hargaResult = \App\Services\HargaService::getHargaEfektif(
                    (int) $produk->id_produk,
                    (int) DB::table('konsumen')->where('id_konsumen', $request->id_konsumen)->value('id_golongan'),
                    (float) $qtyHitung
                );
                $biayaCetak = (float) ($hargaResult['harga_jual'] ?? 0) * $qtyHitung;
                $subtotal = $biayaCetak;

                $grandTotal += $subtotal;

                $detailRows[] = [
                    'id_transaksi'     => 0, // placeholder, diupdate setelah insert transaksi
                    'id_produk'        => $produk->id_produk,
                    'nama_file'        => $item['nama_file'] ?? $produk->nama_produk,
                    'p_ukuran'         => $p,
                    'l_ukuran'         => $l,
                    'qty'              => (int) $qtyInput,
                    'biaya_cetak'      => $biayaCetak,
                    'biaya_desain'     => 0,
                    'biaya_finishing'  => 0,
                    'subtotal'         => $subtotal,
                    'status_spk'       => 'Antre',
                    'status_urgensi'   => 'Normal',
                    'status_finishing' => 'Standar',
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }

            $idTransaksi = DB::table('transaksi')->insertGetId([
                'nomor_nota'        => $noNota,
                'tipe_order'        => 'Reguler',
                'id_konsumen'       => $request->id_konsumen,
                'id_user_kasir'     => $user->id_user,
                'tgl_order'         => now(),
                'total_bayar'       => $grandTotal,
                'status_pembayaran' => 'Belum Bayar',
                'status_nota'       => 'Aktif',
                'status_order'      => 'Menunggu Kasir',
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            foreach ($detailRows as &$row) {
                $row['id_transaksi'] = $idTransaksi;
            }
            unset($row);

            if (!empty($detailRows)) {
                DB::table('transaksi_detail')->insert($detailRows);
            }

            DB::commit();

            return response()->json([
                'status'      => 'success',
                'message'     => 'Nota berhasil dibuat via API.',
                'nomor_nota'  => $noNota,
                'id_transaksi'=> $idTransaksi,
                'total_bayar' => $grandTotal,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => \App\Support\PesanError::untuk($e, 'Gagal membuat transaksi via API')], 422);
        }
    }
}

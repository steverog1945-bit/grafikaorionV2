<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembelianController extends Controller
{
    public function create()
    {
        $bahan = DB::table('bahan_baku')->get();
        $supplier = DB::table('supplier')->get();

        return view('pembelian.create', compact('bahan', 'supplier'));
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {

            // 1️⃣ Insert Header
            $idPembelian = DB::table('pembelian_bahan')->insertGetId([
                'no_faktur_supplier' => $request->no_faktur_supplier,
                'id_supplier'        => $request->id_supplier,
                'tgl_datang'         => $request->tgl_datang,
                'total_akhir'        => 0,
                'status_bayar'       => $request->status_bayar,
            ]);

            $grandTotal = 0;

            foreach ($request->id_bahan as $i => $idBahan) {

                $qty = $request->qty[$i];
                $harga = $request->harga[$i];
                $subtotal = $qty * $harga;
                $grandTotal += $subtotal;

                // Insert Detail
                DB::table('pembelian_bahan_detail')->insert([
                    'id_pembelian' => $idPembelian,
                    'id_bahan'     => $idBahan,
                    'qty'          => $qty,
                    'harga'        => $harga,
                    'subtotal'     => $subtotal,
                ]);

                // 🔒 Lock bahan
                $bahan = DB::table('bahan_baku')
                    ->where('id_bahan', $idBahan)
                    ->lockForUpdate()
                    ->first();

                $stokSebelum = $bahan->stok;
                $stokSesudah = $stokSebelum + $qty;

                // Insert kartu stok
                DB::table('kartu_stok')->insert([
                    'id_bahan'      => $idBahan,
                    'tanggal'       => now(),
                    'tipe'          => 'IN',
                    'qty'           => $qty,
                    'stok_sebelum'  => $stokSebelum,
                    'stok_sesudah'  => $stokSesudah,
                    'referensi'     => $request->no_faktur_supplier,
                    'keterangan'    => 'Pembelian bahan',
                    'created_by'    => auth()->user()->id_user ?? null,
                ]);

                // Update stok utama
                DB::table('bahan_baku')
                    ->where('id_bahan', $idBahan)
                    ->update(['stok' => $stokSesudah]);
            }

            // Update total header
            DB::table('pembelian_bahan')
                ->where('id_pembelian', $idPembelian)
                ->update(['total_akhir' => $grandTotal]);
        });

        return redirect()->back()->with('success', 'Pembelian berhasil disimpan');
    }
}   
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KartuStokController extends Controller
{
    public function index()
    {
        $bahan = DB::table('bahan_baku')
            ->orderBy('nama_bahan')
            ->get();

        return view('kartu_stok.index', compact('bahan'));
    }

    public function detail($id)
    {
        $bahan = DB::table('bahan_baku')
            ->where('id_bahan', $id)
            ->first();

        $kartu = DB::table('kartu_stok')
            ->where('id_bahan', $id)
            ->orderBy('tanggal', 'asc')
            ->get();

        return view('kartu_stok.detail', compact('bahan', 'kartu'));
    }

    /**
     * Penyesuaian stok manual - sebelum ini SATU-SATUNYA cara mengurangi atau
     * mengoreksi stok bahan baku (di luar konsumsi otomatis dari BOM order)
     * adalah lewat query SQL langsung, tidak ada jalur resmi untuk kasus
     * seperti bahan rusak/hilang atau hasil stock opname fisik. Memakai
     * tabel kartu_stok yang sudah ada (tipe OUT/ADJUSTMENT sudah didukung
     * skemanya) - tidak ada tabel baru.
     */
    public function storeAdjustment(Request $request)
    {
        $validated = $request->validate([
            'id_bahan'    => 'required|exists:bahan_baku,id_bahan',
            'tipe'        => 'required|in:OUT,ADJUSTMENT',
            'qty'         => 'required_if:tipe,OUT|nullable|numeric|min:0.01',
            'stok_baru'   => 'required_if:tipe,ADJUSTMENT|nullable|numeric|min:0',
            'keterangan'  => 'required|string|max:255',
        ]);

        return DB::transaction(function () use ($validated) {
            $bahan = DB::table('bahan_baku')
                ->where('id_bahan', $validated['id_bahan'])
                ->lockForUpdate()
                ->first();

            if (!$bahan) {
                return back()->with('error', 'Bahan tidak ditemukan.');
            }

            $stokSebelum = (float) $bahan->stok;

            if ($validated['tipe'] === 'OUT') {
                $qty = (float) $validated['qty'];

                if ($qty > $stokSebelum) {
                    return back()->withInput()->with('error',
                        "Gagal: stok {$bahan->nama_bahan} tersisa " . number_format($stokSebelum, 2) .
                        ", tidak bisa dikeluarkan " . number_format($qty, 2) . '.');
                }

                $stokSesudah = $stokSebelum - $qty;
                $tipe = 'OUT';
            } else {
                // ADJUSTMENT: operator memasukkan hasil hitung fisik yang
                // sebenarnya (stok opname), bukan delta - lebih sesuai alur
                // kerja stock opname nyata daripada minta operator menghitung
                // selisihnya sendiri.
                $stokBaru = (float) $validated['stok_baru'];
                $qty = abs($stokBaru - $stokSebelum);
                $stokSesudah = $stokBaru;
                $tipe = 'ADJUSTMENT';
            }

            DB::table('bahan_baku')
                ->where('id_bahan', $validated['id_bahan'])
                ->update(['stok' => $stokSesudah]);

            DB::table('kartu_stok')->insert([
                'id_bahan'     => $validated['id_bahan'],
                'tanggal'      => now(),
                'tipe'         => $tipe,
                'qty'          => $qty,
                'stok_sebelum' => $stokSebelum,
                'stok_sesudah' => $stokSesudah,
                'referensi'    => $tipe === 'OUT' ? 'MANUAL-KELUAR' : 'STOCK-OPNAME',
                'keterangan'   => $validated['keterangan'],
                'created_by'   => Auth::id(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            return back()->with('success', 'Penyesuaian stok berhasil dicatat.');
        });
    }
}

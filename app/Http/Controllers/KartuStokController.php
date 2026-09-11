<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KartuStokController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $bahan = DB::table('bahan_baku')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('nama_bahan', 'like', "%{$search}%")
                       ->orWhere('kode_bahan', 'like', "%{$search}%");
                });
            })
            ->orderBy('nama_bahan')
            ->get();

        return view('kartu_stok.index', compact('bahan', 'search'));
    }

    public function detail(Request $request, $id)
    {
        $bahan = DB::table('bahan_baku')
            ->leftJoin('satuan as satuan_stok_ref', 'satuan_stok_ref.id_satuan', '=', 'bahan_baku.id_satuan_stok')
            ->leftJoin('satuan as satuan_hitung_ref', 'satuan_hitung_ref.id_satuan', '=', 'bahan_baku.id_satuan_hitung')
            ->where('bahan_baku.id_bahan', $id)
            ->select('bahan_baku.*', 'satuan_stok_ref.nama_satuan as nama_satuan_stok', 'satuan_hitung_ref.nama_satuan as nama_satuan_hitung')
            ->firstOrFail();

        $awal = $request->get('awal', now()->startOfMonth()->toDateString());
        $akhir = $request->get('akhir', now()->toDateString());
        $tipe = $request->get('tipe');

        // Saldo sebelum periode - dipakai sebagai baris "Saldo Awal" supaya
        // baris pertama yang tampil tidak muncul begitu saja tanpa konteks
        // saat histori difilter ke sub-rentang tanggal (pola sama seperti
        // Buku Besar akuntansi: AkuntansiController::bukuBesar()).
        $saldoAwal = DB::table('kartu_stok')
            ->where('id_bahan', $id)
            ->where('tanggal', '<', $awal)
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->value('stok_sesudah');
        $saldoAwal = $saldoAwal !== null ? (float) $saldoAwal : 0.0;

        $kartu = DB::table('kartu_stok')
            ->where('id_bahan', $id)
            ->whereDate('tanggal', '>=', $awal)
            ->whereDate('tanggal', '<=', $akhir)
            ->when($tipe, fn ($q) => $q->where('tipe', $tipe))
            ->orderBy('tanggal', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return view('kartu_stok.detail', compact('bahan', 'kartu', 'awal', 'akhir', 'tipe', 'saldoAwal'));
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

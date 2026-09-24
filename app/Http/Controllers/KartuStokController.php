<?php

namespace App\Http\Controllers;

use App\Services\ApprovalService;
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
     * Penyesuaian stok manual satu-bahan (jalur cepat). Stok TIDAK diubah di
     * sini - sama seperti Stock Opname, cuma DIAJUKAN lewat ApprovalService
     * (tipe_aksi 'penyesuaian_stok_manual'); stok baru berubah saat approver
     * menyetujui (lihat PenyesuaianStokService::eksekusi).
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

        $bahan = DB::table('bahan_baku')->where('id_bahan', $validated['id_bahan'])->first();
        $stok = (float) $bahan->stok;

        // Cek awal supaya pengaju langsung tahu kalau mustahil; dicek ulang
        // saat eksekusi karena stok bisa berubah selama menunggu approval.
        if ($validated['tipe'] === 'OUT' && (float) $validated['qty'] > $stok) {
            return back()->withInput()->with('error',
                "Gagal: stok {$bahan->nama_bahan} tersisa " . number_format($stok, 2) .
                ", tidak bisa dikeluarkan " . number_format((float) $validated['qty'], 2) . '.');
        }

        $ringkasan = $validated['tipe'] === 'OUT'
            ? "Keluar {$bahan->nama_bahan}: " . number_format((float) $validated['qty'], 2)
            : "Revisi stok {$bahan->nama_bahan}: " . number_format($stok, 2) . ' -> ' . number_format((float) $validated['stok_baru'], 2);

        ApprovalService::ajukan(
            'penyesuaian_stok_manual',
            'bahan_baku',
            (int) $bahan->id_bahan,
            [
                'id_bahan'   => (int) $bahan->id_bahan,
                'tipe'       => $validated['tipe'],
                'qty'        => $validated['qty'] ?? null,
                'stok_baru'  => $validated['stok_baru'] ?? null,
                'keterangan' => $validated['keterangan'],
            ],
            $ringkasan,
            $validated['keterangan']
        );

        return back()->with('success', 'Penyesuaian stok diajukan untuk persetujuan - stok berubah setelah disetujui.');
    }
}

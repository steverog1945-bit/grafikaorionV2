<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\AccountingService;

class KonsumenDepositController extends Controller
{
    public function index()
    {
        // Saldo per konsumen = SUM(Setoran) - SUM(Pemakaian). Bukan kolom
        // running-balance terpisah, supaya tidak ada dua sumber kebenaran.
        $saldoPerKonsumen = DB::table('konsumen_deposit')
            ->join('konsumen', 'konsumen_deposit.id_konsumen', '=', 'konsumen.id_konsumen')
            ->leftJoin('golongan_konsumen', 'konsumen.id_golongan', '=', 'golongan_konsumen.id_golongan')
            ->select(
                'konsumen.id_konsumen',
                'konsumen.nama_konsumen',
                'golongan_konsumen.nama_golongan',
                DB::raw("SUM(CASE WHEN konsumen_deposit.tipe = 'Setoran' THEN konsumen_deposit.nominal ELSE -konsumen_deposit.nominal END) as saldo_deposit")
            )
            ->groupBy('konsumen.id_konsumen', 'konsumen.nama_konsumen', 'golongan_konsumen.nama_golongan')
            ->havingRaw('saldo_deposit != 0')
            ->orderByDesc('saldo_deposit')
            ->get();

        $riwayat = DB::table('konsumen_deposit')
            ->join('konsumen', 'konsumen_deposit.id_konsumen', '=', 'konsumen.id_konsumen')
            ->leftJoin('users', 'konsumen_deposit.id_user', '=', 'users.id_user')
            ->leftJoin('transaksi', 'konsumen_deposit.id_transaksi', '=', 'transaksi.id_transaksi')
            ->select('konsumen_deposit.*', 'konsumen.nama_konsumen', 'users.username as dicatat_oleh', 'transaksi.nomor_nota')
            ->orderByDesc('konsumen_deposit.created_at')
            ->limit(50)
            ->get();

        $konsumen = DB::table('konsumen')->orderBy('nama_konsumen')->get();

        return view('konsumen-deposit.index', compact('saldoPerKonsumen', 'riwayat', 'konsumen'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_konsumen'  => 'required|exists:konsumen,id_konsumen',
            'nominal'      => 'required|numeric|min:1',
            'metode_bayar' => 'required|in:Tunai,Transfer',
            'tgl_deposit'  => 'required|date',
            'keterangan'   => 'nullable',
        ]);

        DB::beginTransaction();
        try {
            $noReferensi = 'DEP-' . now()->format('YmdHis') . '-' . $request->id_konsumen;

            DB::table('konsumen_deposit')->insert([
                'id_konsumen'  => $request->id_konsumen,
                'tipe'         => 'Setoran',
                'nominal'      => $request->nominal,
                'metode_bayar' => $request->metode_bayar,
                'keterangan'   => $request->keterangan,
                'tgl_deposit'  => $request->tgl_deposit,
                'id_user'      => Auth::id(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            AccountingService::postSetoranDeposit($noReferensi, (float) $request->nominal, $request->metode_bayar);

            DB::commit();
            return redirect()->back()->with('success', 'Deposit berhasil dicatat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mencatat deposit: ' . $e->getMessage());
        }
    }

    /**
     * Saldo deposit tersedia untuk satu konsumen (dipakai KasirController
     * saat validasi pembayaran metode Deposit).
     */
    public static function saldoUntuk(int $idKonsumen): float
    {
        $setoran = (float) DB::table('konsumen_deposit')
            ->where('id_konsumen', $idKonsumen)->where('tipe', 'Setoran')->sum('nominal');
        $pemakaian = (float) DB::table('konsumen_deposit')
            ->where('id_konsumen', $idKonsumen)->where('tipe', 'Pemakaian')->sum('nominal');

        return $setoran - $pemakaian;
    }
}

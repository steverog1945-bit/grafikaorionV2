<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class KonsumenDepositController extends Controller
{
    public function index()
    {
        // Saldo per konsumen = SUM seluruh setoran deposit-nya (belum ada
        // fitur pemakaian deposit sebagai pembayaran, jadi belum ada
        // pengurang - murni ledger setoran untuk saat ini).
        $saldoPerKonsumen = DB::table('konsumen_deposit')
            ->join('konsumen', 'konsumen_deposit.id_konsumen', '=', 'konsumen.id_konsumen')
            ->leftJoin('golongan_konsumen', 'konsumen.id_golongan', '=', 'golongan_konsumen.id_golongan')
            ->select(
                'konsumen.id_konsumen',
                'konsumen.nama_konsumen',
                'golongan_konsumen.nama_golongan',
                DB::raw('SUM(konsumen_deposit.nominal) as saldo_deposit')
            )
            ->groupBy('konsumen.id_konsumen', 'konsumen.nama_konsumen', 'golongan_konsumen.nama_golongan')
            ->orderByDesc('saldo_deposit')
            ->get();

        $riwayat = DB::table('konsumen_deposit')
            ->join('konsumen', 'konsumen_deposit.id_konsumen', '=', 'konsumen.id_konsumen')
            ->leftJoin('users', 'konsumen_deposit.id_user', '=', 'users.id_user')
            ->select('konsumen_deposit.*', 'konsumen.nama_konsumen', 'users.username as dicatat_oleh')
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

        DB::table('konsumen_deposit')->insert([
            'id_konsumen'  => $request->id_konsumen,
            'nominal'      => $request->nominal,
            'metode_bayar' => $request->metode_bayar,
            'keterangan'   => $request->keterangan,
            'tgl_deposit'  => $request->tgl_deposit,
            'id_user'      => Auth::id(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return redirect()->back()->with('success', 'Deposit berhasil dicatat.');
    }
}

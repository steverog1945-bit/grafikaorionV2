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
        //
        // Sebelumnya konsumen yang saldonya persis 0 (deposit pernah ada,
        // tapi sudah habis terpakai semua) DIBUANG total dari daftar ini
        // (havingRaw('saldo_deposit != 0')) - hilang begitu saja tanpa
        // keterangan, seolah-olah tidak pernah punya deposit sama sekali.
        // Sekarang tetap ditampilkan dengan badge "Habis Terpakai" di view
        // (konsumen-deposit/index.blade.php) supaya jelas depositnya PERNAH
        // ada tapi sudah tidak bisa dipakai lagi - bukan sekadar hilang.
        // Konsumen yang memang TIDAK PERNAH punya baris konsumen_deposit
        // sama sekali tetap tidak muncul (join di atas sudah otomatis
        // mengecualikan mereka).
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

        // Saldo saat ini per konsumen (map id_konsumen -> saldo_deposit) -
        // dipakai di baris Riwayat bertipe Setoran untuk kasih tahu status
        // pemakaiannya SEKARANG (deposit dicatat sebagai saldo terpool per
        // konsumen, bukan dialokasikan per-setoran/FIFO ke transaksi
        // tertentu, jadi ini status "saldo konsumen saat ini" - bukan klaim
        // persis setoran mana yang dipakai untuk transaksi mana).
        $saldoMap = $saldoPerKonsumen->keyBy('id_konsumen');

        return view('konsumen-deposit.index', compact('saldoPerKonsumen', 'riwayat', 'konsumen', 'saldoMap'));
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

            $dataDeposit = [
                'id_konsumen'  => $request->id_konsumen,
                'tipe'         => 'Setoran',
                'nominal'      => $request->nominal,
                'metode_bayar' => $request->metode_bayar,
                'keterangan'   => $request->keterangan,
                'tgl_deposit'  => $request->tgl_deposit,
                'id_user'      => Auth::id(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
            $idDeposit = DB::table('konsumen_deposit')->insertGetId($dataDeposit);

            // Contoh pola audit trail untuk jalur DB::table() murni (tabel
            // ini tidak dikelola lewat Eloquent sama sekali di controller
            // ini - lihat catatan di App\Services\AuditLogger).
            \App\Services\AuditLogger::log('created', 'konsumen_deposit', $idDeposit, null, $dataDeposit, "Setoran deposit {$noReferensi}");

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

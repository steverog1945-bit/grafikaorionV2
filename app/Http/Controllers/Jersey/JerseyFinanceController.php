<?php

namespace App\Http\Controllers\Jersey;

use App\Http\Controllers\Controller;
use App\Models\JerseyOrder;
use App\Models\JerseyOrderPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Modul Keuangan Jersey - piutang, DP, & pelunasan order jersey, TERPISAH
 * dari kas_harian & transaksi Kasir utama (permintaan eksplisit user: dua
 * arus uang ini sengaja tidak dicampur). Satu order bisa punya banyak baris
 * pembayaran (ledger) lewat jersey_order_payments - lihat
 * JerseyOrder::recalculatePembayaran() untuk bagaimana status_pembayaran &
 * kolom deposit (turunan, bukan lagi diisi manual) dihitung ulang tiap
 * ledger berubah.
 */
class JerseyFinanceController extends Controller
{
    public function index(Request $request)
    {
        $query = JerseyOrder::with(['konsumen', 'payments'])
            ->whereIn('status_pembayaran', ['Belum Bayar', 'DP'])
            ->orderByRaw("deadline IS NULL, deadline ASC");

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('order_number', 'like', "%$s%")
                    ->orWhere('customer_name', 'like', "%$s%");
            });
        }

        $piutang = $query->paginate(20)->withQueryString();

        // Ringkasan dashboard - dihitung dari SELURUH order (bukan cuma
        // halaman yang sedang tampil) supaya angkanya benar-benar total.
        $semuaBelumLunas = JerseyOrder::whereIn('status_pembayaran', ['Belum Bayar', 'DP'])->get();
        $totalPiutang = $semuaBelumLunas->sum(fn ($o) => max(0, (float) $o->total - (float) $o->deposit));
        $jumlahJatuhTempo = $semuaBelumLunas->filter(fn ($o) => $o->jatuh_tempo)->count();

        $awalBulan = now()->startOfMonth();
        $totalDiterimaBulanIni = JerseyOrderPayment::where('tanggal_bayar', '>=', $awalBulan)->sum('jumlah');

        return view('jersey.keuangan.index', compact('piutang', 'totalPiutang', 'jumlahJatuhTempo', 'totalDiterimaBulanIni'));
    }

    public function storePayment(Request $request, JerseyOrder $order)
    {
        $data = $request->validate([
            'tanggal_bayar' => 'required|date',
            'jumlah' => 'required|numeric|min:1',
            'metode_bayar' => 'required|in:Tunai,Transfer,QRIS,Lainnya',
            'bukti_path' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($data, $order) {
            $order->payments()->create([
                'tanggal_bayar' => $data['tanggal_bayar'],
                'jumlah' => $data['jumlah'],
                'metode_bayar' => $data['metode_bayar'],
                'bukti_path' => $data['bukti_path'] ?? null,
                'keterangan' => $data['keterangan'] ?? null,
                'created_by' => Auth::id(),
            ]);
            $order->recalculatePembayaran();
        });

        return back()->with('success', 'Pembayaran berhasil dicatat.');
    }

    public function destroyPayment(JerseyOrderPayment $payment)
    {
        $order = $payment->order;

        DB::transaction(function () use ($payment, $order) {
            $payment->delete();
            $order->recalculatePembayaran();
        });

        return back()->with('success', 'Baris pembayaran dihapus, status order dihitung ulang.');
    }

    public function uploadBukti(Request $request)
    {
        $request->validate([
            'bukti' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $path = $request->file('bukti')->store('jersey_bukti_bayar', 'public');

        return response()->json([
            'status' => 'success',
            'path' => $path,
            'url' => asset('storage/' . $path),
        ]);
    }
}

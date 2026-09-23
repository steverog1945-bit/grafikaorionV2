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

        try {
        DB::transaction(function () use ($data, $order) {
            $payment = $order->payments()->create([
                'tanggal_bayar' => $data['tanggal_bayar'],
                'jumlah' => $data['jumlah'],
                'metode_bayar' => $data['metode_bayar'],
                'bukti_path' => $data['bukti_path'] ?? null,
                'keterangan' => $data['keterangan'] ?? null,
                'created_by' => Auth::id(),
            ]);
            \App\Services\JerseyAccountingService::catatPembayaran($order, $payment);
            $order->recalculatePembayaran();
        });
        } catch (\RuntimeException $e) {
            // Mis. periode akuntansi sudah ditutup - seluruh transaksi
            // (termasuk baris pembayaran) sudah di-rollback.
            return back()->withInput()->with('error', 'Pembayaran gagal dicatat: ' . $e->getMessage());
        }

        return back()->with('success', 'Pembayaran berhasil dicatat.');
    }

    /**
     * Menghapus pembayaran = MENGAJUKAN void lewat Approval Workflow
     * (tipe_aksi 'void_pembayaran_jersey'); jurnalnya dibalik & status order
     * dihitung ulang baru saat disetujui.
     */
    public function destroyPayment(Request $request, JerseyOrderPayment $payment)
    {
        $request->validate(['alasan' => 'nullable|string|max:255']);

        $sudahAda = \App\Models\ApprovalRequest::where('tipe_aksi', 'void_pembayaran_jersey')
            ->where('referensi_id', $payment->id)->where('status', 'Menunggu')->exists();

        if ($sudahAda) {
            return back()->with('error', 'Pembayaran ini sudah punya pengajuan void yang menunggu.');
        }

        $alasan = $request->input('alasan') ?: 'Void pembayaran jersey';

        \App\Services\ApprovalService::ajukan(
            'void_pembayaran_jersey',
            'jersey_order_payments',
            $payment->id,
            ['alasan' => $alasan],
            "Void pembayaran jersey {$payment->order->order_number} (Rp " . number_format($payment->jumlah, 0, ',', '.') . ')',
            $alasan
        );

        return back()->with('success', 'Pengajuan void pembayaran dikirim untuk persetujuan Administrator.');
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

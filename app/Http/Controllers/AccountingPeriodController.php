<?php

namespace App\Http\Controllers;

use App\Models\AccountingPeriod;
use App\Services\AccountingPeriodService;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Administrator-only: tutup/buka periode akuntansi (roadmap LEVEL 1).
 * Penegakan sebenarnya ada di AccountingService::postJournal() lewat
 * AccountingPeriodService::isClosed() - controller ini cuma UI untuk
 * mengubah status baris accounting_periods.
 */
class AccountingPeriodController extends Controller
{
    public function index()
    {
        $sekarang = Carbon::now();
        $daftarPeriode = [];

        // 12 bulan terakhir (termasuk bulan berjalan) - cukup untuk operasi
        // rutin tutup buku bulanan. Periode tanpa baris di DB dianggap Open
        // (lihat AccountingPeriodService::isClosed()).
        for ($i = 0; $i < 12; $i++) {
            $bulanKe = $sekarang->copy()->subMonths($i);
            $existing = AccountingPeriod::where('tahun', $bulanKe->year)->where('bulan', $bulanKe->month)->first();

            $daftarPeriode[] = [
                'tahun' => $bulanKe->year,
                'bulan' => $bulanKe->month,
                'nama' => $bulanKe->translatedFormat('F Y'),
                'status' => $existing->status ?? 'Open',
                'closed_at' => $existing->closed_at ?? null,
                'catatan' => $existing->catatan ?? null,
            ];
        }

        return view('accounting-period.index', compact('daftarPeriode'));
    }

    public function tutup(Request $request)
    {
        $data = $request->validate([
            'tahun' => 'required|integer|min:2000|max:2100',
            'bulan' => 'required|integer|min:1|max:12',
            'catatan' => 'nullable|string|max:255',
        ]);

        AccountingPeriodService::tutup($data['tahun'], $data['bulan'], auth()->id(), $data['catatan'] ?? null);

        return back()->with('success', "Periode {$data['bulan']}/{$data['tahun']} berhasil ditutup. Jurnal baru bertanggal periode ini akan ditolak.");
    }

    public function buka(Request $request)
    {
        $data = $request->validate([
            'tahun' => 'required|integer|min:2000|max:2100',
            'bulan' => 'required|integer|min:1|max:12',
        ]);

        AccountingPeriodService::buka($data['tahun'], $data['bulan']);

        return back()->with('success', "Periode {$data['bulan']}/{$data['tahun']} dibuka kembali.");
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\WasteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Waste Management (LEVEL 2). Aturan bisnis ada di App\Services\WasteService.
 */
class WasteController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('waste_log as w')
            ->join('bahan_baku as b', 'b.id_bahan', '=', 'w.id_bahan')
            ->leftJoin('users as u', 'u.id_user', '=', 'w.id_user')
            ->select('w.*', 'b.nama_bahan', 'b.satuan_stok', 'u.username')
            ->orderByDesc('w.id');

        foreach (['jenis', 'status'] as $f) {
            if ($request->filled($f)) {
                $query->where("w.$f", $request->$f);
            }
        }

        $daftar = $query->paginate(20)->withQueryString();

        $awalBulan = now()->startOfMonth()->toDateString();
        $ringkasan = DB::table('waste_log')
            ->where('status', 'Diterapkan')->where('tanggal', '>=', $awalBulan)
            ->selectRaw("jenis, COUNT(*) as jumlah, COALESCE(SUM(biaya), 0) as biaya")
            ->groupBy('jenis')->get()->keyBy('jenis');

        return view('waste.index', compact('daftar', 'ringkasan'));
    }

    public function create()
    {
        return view('waste.create', [
            'bahanList' => DB::table('bahan_baku')->where('status', 'aktif')->orderBy('nama_bahan')->get(['id_bahan', 'nama_bahan', 'satuan_stok', 'stok']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_bahan' => 'required|exists:bahan_baku,id_bahan',
            'qty' => 'required|numeric|min:0.0001',
            'jenis' => 'required|in:Normal,Abnormal',
            'alasan' => 'required|string|max:255',
        ]);

        try {
            $hasil = WasteService::catat((int) $data['id_bahan'], (float) $data['qty'], $data['jenis'], $data['alasan']);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('waste.index')->with('success', $hasil['status'] === 'Diterapkan'
            ? "Waste {$hasil['nomor']} dicatat: stok dikurangi & biaya dijurnal."
            : "Waste abnormal {$hasil['nomor']} diajukan - stok berkurang setelah disetujui Administrator.");
    }
}

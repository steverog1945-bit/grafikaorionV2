<?php

namespace App\Http\Controllers;

use App\Services\DashboardEksekutifService as Analisis;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Dashboard Eksekutif, Profitabilitas, dan Analisis Pemakaian Bahan
 * (LEVEL 4). Perhitungan ada di App\Services\DashboardEksekutifService.
 */
class AnalisisController extends Controller
{
    private function periode(Request $request): array
    {
        $dari = $request->filled('tgl_dari') ? Carbon::parse($request->tgl_dari) : now()->startOfMonth();
        $sampai = $request->filled('tgl_sampai') ? Carbon::parse($request->tgl_sampai) : now();

        if ($sampai->lt($dari)) {
            [$dari, $sampai] = [$sampai, $dari];
        }

        return [$dari, $sampai];
    }

    public function dashboard(Request $request)
    {
        [$dari, $sampai] = $this->periode($request);

        return view('analisis.dashboard', [
            'dari' => $dari, 'sampai' => $sampai,
            'r' => Analisis::ringkasan($dari, $sampai),
            'tren' => Analisis::trenOmzet(6),
            'topPelanggan' => Analisis::topPelanggan($dari, $sampai),
            'topProduk' => Analisis::topProduk($dari, $sampai),
            'peringatan' => Analisis::peringatan(),
            'estimasi' => Analisis::estimasiBulanDepan(),
        ]);
    }

    public function profitabilitas(Request $request)
    {
        [$dari, $sampai] = $this->periode($request);
        $dimensi = in_array($request->dimensi, ['produk', 'pelanggan', 'mesin'], true) ? $request->dimensi : 'produk';

        return view('analisis.profitabilitas', [
            'dari' => $dari, 'sampai' => $sampai, 'dimensi' => $dimensi,
            'data' => Analisis::profitabilitas($dimensi, $dari, $sampai),
        ]);
    }

    public function pemakaianBahan(Request $request)
    {
        [$dari, $sampai] = $this->periode($request);

        return view('analisis.pemakaian-bahan', ['dari' => $dari, 'sampai' => $sampai, 'data' => Analisis::pemakaianBahan($dari, $sampai)]);
    }
}

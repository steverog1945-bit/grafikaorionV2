<?php

namespace App\Http\Controllers;

use App\Services\DashboardEksekutifService as Analisis;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    /**
     * Analisis 30 Hari + Margin — diport dari erpV3 (2026-11-13): omzet
     * harian, jam sibuk, produk/konsumen terlaris, margin realisasi,
     * perbandingan periode sebelumnya, plus ekspor CSV. Dihitung
     * langsung dari data nyata, tanpa API.
     */
    public function analisis(Request $request)
    {
        abort_unless(
            \App\Support\AksesHarga::bolehLihat(),
            403,
            'Halaman analisis memuat omzet & margin - hanya untuk role yang berhak melihat harga.'
        );

        $periode = in_array((int) $request->query('periode'), [7, 30, 90], true) ? (int) $request->query('periode') : 30;

        $data = $this->muatAnalisis($periode);

        // Perbandingan dengan periode SEBELUMNYA (window yang sama, digeser):
        // naik/turun % pada omzet, jumlah nota, dan rata-rata nota - KPI
        // pertanyaan pertama owner setiap hari ("lebih baik dari kemarin?").
        $sebelum = $this->muatAnalisis($periode, now()->subDays($periode * 2), now()->subDays($periode));

        return view('analisis.analisis', $this->denganBandingan($data, $sebelum, $periode));
    }

    /** Export CSV omzet harian + KPI - data sama dengan halaman analisis. */
    public function analisisCsv(Request $request)
    {
        abort_unless(
            \App\Support\AksesHarga::bolehLihat(),
            403,
            'Halaman analisis memuat omzet & margin - hanya untuk role yang berhak melihat harga.'
        );

        $periode = in_array((int) $request->query('periode'), [7, 30, 90], true) ? (int) $request->query('periode') : 30;
        $data = $this->muatAnalisis($periode);

        $nama = 'analisis-omzet-' . $periode . 'hari-' . now()->format('Ymd-Hi') . '.csv';
        return response()->streamDownload(function () use ($data, $periode) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya Excel membaca UTF-8 dengan benar
            fputcsv($out, ['Omzet Harian - ' . $periode . ' hari terakhir']);
            fputcsv($out, ['Tanggal', 'Omzet', 'Nota']);
            foreach ($data['hari'] as $h) {
                fputcsv($out, [$h['tanggal'], $h['omzet'], $h['nota']]);
            }
            fputcsv($out, []);
            fputcsv($out, ['KPI', 'Nilai']);
            fputcsv($out, ['Omzet periode', $data['kpi']->omzet ?? 0]);
            fputcsv($out, ['Jumlah nota', $data['kpi']->nota ?? 0]);
            fputcsv($out, ['Rata-rata per nota', round((float) ($data['kpi']->rata_nota ?? 0), 2)]);
            fputcsv($out, ['Margin realisasi (%)', $data['marginPersen'] ?? '-']);
            fputcsv($out, []);
            fputcsv($out, ['Produk terlaris', 'Qty', 'Omzet']);
            foreach ($data['topProduk'] as $p) {
                fputcsv($out, [$p->nama_produk, $p->qty, $p->omzet]);
            }
            fputcsv($out, []);
            fputcsv($out, ['Konsumen teratas', 'Nota', 'Omzet']);
            foreach ($data['topKonsumen'] as $k) {
                fputcsv($out, [$k->nama, $k->nota, $k->omzet]);
            }
            fclose($out);
        }, $nama, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Muat data analisis untuk window $jumlahHari terakhir (atau window
     * antara $mulai dan $akhir, dipakai untuk perbandingan periode sebelumnya).
     */
    private function muatAnalisis(int $jumlahHari, ?\Carbon\CarbonInterface $mulai = null, ?\Carbon\CarbonInterface $akhir = null): array
    {
        $windowBerjalan = $mulai === null && $akhir === null; // window terakhir = sampai hari ini
        $mulai ??= now()->subDays($jumlahHari - 1)->startOfDay();
        $akhir ??= now()->endOfDay();

        $harian = DB::table('transaksi')
            ->where('tgl_order', '>=', $mulai)
            ->where('tgl_order', '<', $akhir)
            ->where('status_nota', 'Aktif')
            ->selectRaw('DATE(tgl_order) as d, SUM(total_bayar) as omzet, COUNT(*) as nota')
            ->groupByRaw('DATE(tgl_order)')
            ->get()
            ->keyBy('d');

        // Isi window penuh (hari tanpa transaksi tetap tampil sebagai 0).
        // Window berjalan: hari terakhir = hari ini. Window perbandingan:
        // hari terakhir = sehari sebelum $akhir (bukan hari ini).
        $hari = [];
        for ($i = $jumlahHari - 1; $i >= 0; $i--) {
            $hariTerakhir = $windowBerjalan ? now() : $akhir->copy()->subDay();
            $tglReal = $hariTerakhir->copy()->subDays($i)->format('Y-m-d');
            $row = $harian->get($tglReal);
            $hari[] = [
                'label' => $hariTerakhir->copy()->subDays($i)->translatedFormat('d M'),
                'tanggal' => $tglReal,
                'omzet' => (float) ($row->omzet ?? 0),
                'nota' => (int) ($row->nota ?? 0),
            ];
        }
        $maksOmzet = max(1, max(array_column($hari, 'omzet')));

        $jam = DB::table('transaksi')
            ->where('tgl_order', '>=', $mulai)
            ->where('tgl_order', '<', $akhir)
            ->where('status_nota', 'Aktif')
            ->selectRaw('HOUR(tgl_order) as h, COUNT(*) as n')
            ->groupByRaw('HOUR(tgl_order)')
            ->pluck('n', 'h');

        $kpi = DB::table('transaksi')
            ->where('tgl_order', '>=', $mulai)
            ->where('tgl_order', '<', $akhir)
            ->where('status_nota', 'Aktif')
            ->selectRaw('SUM(total_bayar) as omzet, AVG(total_bayar) as rata_nota, COUNT(*) as nota,
                         SUM(CASE WHEN status_pembayaran != \'Lunas\' THEN 1 ELSE 0 END) as belum_lunas')
            ->first();

        $margin = DB::table('transaksi_detail as d')
            ->join('transaksi as t', 't.id_transaksi', '=', 'd.id_transaksi')
            ->where('t.tgl_order', '>=', $mulai)
            ->where('t.tgl_order', '<', $akhir)
            ->where('t.status_nota', 'Aktif')
            ->selectRaw('SUM(d.subtotal) as jual, SUM(d.subtotal - COALESCE(d.hpp_snapshot, 0)) as margin')
            ->first();

        $marginPersen = ($margin->jual ?? 0) > 0 ? round(($margin->margin ?? 0) / $margin->jual * 100, 1) : null;

        $topProduk = DB::table('transaksi_detail as d')
            ->join('transaksi as t', 't.id_transaksi', '=', 'd.id_transaksi')
            ->join('produk as p', 'p.id_produk', '=', 'd.id_produk')
            ->where('t.tgl_order', '>=', $mulai)
            ->where('t.tgl_order', '<', $akhir)
            ->where('t.status_nota', 'Aktif')
            ->selectRaw('p.nama_produk, SUM(d.qty) as qty, SUM(d.subtotal) as omzet')
            ->groupBy('p.nama_produk')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        // Konsumen teratas - pelanggan paling berkontribusi dalam periode
        $topKonsumen = DB::table('transaksi as t')
            ->leftJoin('konsumen as k', 'k.id_konsumen', '=', 't.id_konsumen')
            ->where('t.tgl_order', '>=', $mulai)
            ->where('t.tgl_order', '<', $akhir)
            ->where('t.status_nota', 'Aktif')
            ->selectRaw("COALESCE(NULLIF(k.nama_konsumen, ''), t.nama_konsumen_manual, 'Pelanggan') as nama,
                         COUNT(*) as nota, SUM(t.total_bayar) as omzet")
            ->groupBy('nama')
            ->orderByDesc('omzet')
            ->limit(5)
            ->get();

        $backup = DB::table('backup_logs')
            ->orderByDesc('finished_at')
            ->limit(8)
            ->get();

        return compact('hari', 'maksOmzet', 'jam', 'kpi', 'marginPersen', 'topProduk', 'topKonsumen', 'backup');
    }

    /** Bandingkan dua window: kembalikan array view + badge naik/turun %. */
    private function denganBandingan(array $data, array $sebelum, int $periode): array
    {
        $persen = fn ($kini, $dahulu): ?float => $dahulu > 0 ? round(($kini - $dahulu) / $dahulu * 100, 1) : null;

        $omzetKini = (float) ($data['kpi']->omzet ?? 0);
        $omzetDahulu = (float) ($sebelum['kpi']->omzet ?? 0);
        $notaKini = (int) ($data['kpi']->nota ?? 0);
        $notaDahulu = (int) ($sebelum['kpi']->nota ?? 0);
        $rataKini = $notaKini > 0 ? $omzetKini / $notaKini : 0.0;
        $rataDahulu = $notaDahulu > 0 ? $omzetDahulu / $notaDahulu : 0.0;

        $data['periode'] = $periode;
        $data['delta'] = [
            'omzet' => $persen($omzetKini, $omzetDahulu),
            'nota' => $persen($notaKini, $notaDahulu),
            'rata' => $persen($rataKini, $rataDahulu),
        ];

        return $data;
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
    public function biayaTinta(Request $request)
    {
        [$dari, $sampai] = $this->periode($request);

        return view('analisis.biaya-tinta', [
            'dari' => $dari, 'sampai' => $sampai,
            'h' => \App\Services\BiayaTintaService::hitung($dari, $sampai),
            'pengaturan' => \Illuminate\Support\Facades\DB::table('pemakaian_tinta as pt')
                ->join('mesin as m', 'm.id_mesin', '=', 'pt.id_mesin')
                ->join('bahan_baku as b', 'b.id_bahan', '=', 'pt.id_bahan')
                ->orderBy('m.nama_mesin')->orderBy('b.nama_bahan')
                ->get(['pt.*', 'm.nama_mesin', 'b.nama_bahan']),
            'mesin' => \Illuminate\Support\Facades\DB::table('mesin')->orderBy('nama_mesin')->get(),
            'bahanTinta' => \Illuminate\Support\Facades\DB::table('bahan_baku')->where('status', 'aktif')->orderBy('nama_bahan')->get(['id_bahan', 'nama_bahan', 'kode_bahan']),
        ]);
    }

    public function simpanPemakaianTinta(Request $request)
    {
        $data = $request->validate([
            'id_mesin' => 'required|exists:mesin,id_mesin',
            'id_bahan' => 'required|exists:bahan_baku,id_bahan',
            'dasar' => 'required|in:m2,lembar',
            'pemakaian' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:255',
        ]);

        \Illuminate\Support\Facades\DB::table('pemakaian_tinta')->updateOrInsert(
            ['id_mesin' => $data['id_mesin'], 'id_bahan' => $data['id_bahan'], 'dasar' => $data['dasar']],
            ['pemakaian' => $data['pemakaian'], 'keterangan' => $data['keterangan'] ?? null, 'is_active' => true, 'updated_at' => now(), 'created_at' => now()]
        );
        \App\Services\AuditLogger::log('updated', 'pemakaian_tinta', null, null, $data, 'Pengaturan pemakaian tinta');

        return back()->with('success', 'Pengaturan pemakaian tinta disimpan.');
    }

    public function hapusPemakaianTinta(int $id)
    {
        $baris = \Illuminate\Support\Facades\DB::table('pemakaian_tinta')->where('id', $id)->first();
        abort_if(!$baris, 404);
        \Illuminate\Support\Facades\DB::table('pemakaian_tinta')->where('id', $id)->delete();
        \App\Services\AuditLogger::log('deleted', 'pemakaian_tinta', $id, (array) $baris, null, 'Hapus pengaturan pemakaian tinta');

        return back()->with('success', 'Pengaturan dihapus.');
    }
}

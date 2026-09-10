<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Lapisan laporan/read-only di atas jurnal_umum yang ditulis AccountingService.
 * Tidak ada satupun method di sini yang menulis ke jurnal_umum - satu-satunya
 * jalur tulis tetap AccountingService::postJournal() (lihat catatan di sana).
 * Semua total di sini murni derivasi dari data yang sudah ada, bukan sumber
 * kebenaran baru.
 */
class AkuntansiController extends Controller
{
    public function jurnal(Request $request)
    {
        $awal = $request->get('awal', now()->startOfMonth()->toDateString());
        $akhir = $request->get('akhir', now()->toDateString());

        $rows = DB::table('jurnal_umum')
            ->join('bag_akun', 'bag_akun.kode_akun', '=', 'jurnal_umum.kode_akun')
            ->whereBetween('tgl_jurnal', [$awal, $akhir])
            ->orderBy('tgl_jurnal')
            ->orderBy('id_jurnal')
            ->select('jurnal_umum.*', 'bag_akun.nama_akun')
            ->get();

        $totalDebet = $rows->sum('debet');
        $totalKredit = $rows->sum('kredit');

        return view('akuntansi.jurnal', compact('rows', 'awal', 'akhir', 'totalDebet', 'totalKredit'));
    }

    public function bukuBesar(Request $request)
    {
        $daftarAkun = DB::table('bag_akun')->orderBy('kode_akun')->get();

        $kodeAkun = $request->get('kode_akun', optional($daftarAkun->first())->kode_akun);
        $awal = $request->get('awal', now()->startOfMonth()->toDateString());
        $akhir = $request->get('akhir', now()->toDateString());

        $akun = $daftarAkun->firstWhere('kode_akun', $kodeAkun);
        $rows = collect();
        $saldoAwal = 0.0;
        $saldoAkhir = 0.0;

        if ($akun) {
            $saldoSebelum = DB::table('jurnal_umum')
                ->where('kode_akun', $kodeAkun)
                ->where('tgl_jurnal', '<', $awal)
                ->selectRaw('COALESCE(SUM(debet),0) as total_debet, COALESCE(SUM(kredit),0) as total_kredit')
                ->first();

            $arahNormal = $akun->saldo_normal === 'Debet' ? 1 : -1;
            $saldoAwal = $arahNormal * (($saldoSebelum->total_debet ?? 0) - ($saldoSebelum->total_kredit ?? 0));

            $transaksi = DB::table('jurnal_umum')
                ->where('kode_akun', $kodeAkun)
                ->whereBetween('tgl_jurnal', [$awal, $akhir])
                ->orderBy('tgl_jurnal')
                ->orderBy('id_jurnal')
                ->get();

            $berjalan = $saldoAwal;
            $rows = $transaksi->map(function ($t) use (&$berjalan, $arahNormal) {
                $berjalan += $arahNormal * ($t->debet - $t->kredit);
                $t->saldo_berjalan = $berjalan;
                return $t;
            });

            $saldoAkhir = $berjalan;
        }

        return view('akuntansi.buku-besar', compact('daftarAkun', 'akun', 'kodeAkun', 'awal', 'akhir', 'rows', 'saldoAwal', 'saldoAkhir'));
    }

    public function neracaSaldo(Request $request)
    {
        $akhir = $request->get('akhir', now()->toDateString());

        $akunList = DB::table('bag_akun')->orderBy('kode_akun')->get();

        $saldoPerAkun = DB::table('jurnal_umum')
            ->where('tgl_jurnal', '<=', $akhir)
            ->selectRaw('kode_akun, SUM(debet) as total_debet, SUM(kredit) as total_kredit')
            ->groupBy('kode_akun')
            ->get()
            ->keyBy('kode_akun');

        $rows = $akunList->map(function ($akun) use ($saldoPerAkun) {
            $s = $saldoPerAkun->get($akun->kode_akun);
            $totalDebet = $s->total_debet ?? 0;
            $totalKredit = $s->total_kredit ?? 0;
            $net = $totalDebet - $totalKredit;

            $akun->kolom_debet = $net > 0 ? $net : 0;
            $akun->kolom_kredit = $net < 0 ? abs($net) : 0;

            // Bendera diagnostik: saldo akun ini terbalik dari saldo_normal
            // yang didefinisikan (mis. akun Aset malah bersaldo Kredit) -
            // bukan error, tapi layak diperiksa (piutang lunas lebih besar
            // dari nilainya, dsb).
            $akun->saldo_tidak_normal = $net != 0 && (
                ($akun->saldo_normal === 'Debet' && $net < 0) ||
                ($akun->saldo_normal === 'Kredit' && $net > 0)
            );

            return $akun;
        })->filter(fn($a) => $a->kolom_debet != 0 || $a->kolom_kredit != 0 || $a->is_active)->values();

        $totalDebet = $rows->sum('kolom_debet');
        $totalKredit = $rows->sum('kolom_kredit');

        return view('akuntansi.neraca-saldo', compact('rows', 'akhir', 'totalDebet', 'totalKredit'));
    }

    public function labaRugi(Request $request)
    {
        $awal = $request->get('awal', now()->startOfMonth()->toDateString());
        $akhir = $request->get('akhir', now()->toDateString());

        $totals = DB::table('jurnal_umum')
            ->join('bag_akun', 'bag_akun.kode_akun', '=', 'jurnal_umum.kode_akun')
            ->whereBetween('tgl_jurnal', [$awal, $akhir])
            ->whereIn('tipe_akun', ['Pendapatan', 'Beban'])
            ->selectRaw('bag_akun.kode_akun, bag_akun.nama_akun, bag_akun.tipe_akun, SUM(debet) as total_debet, SUM(kredit) as total_kredit')
            ->groupBy('bag_akun.kode_akun', 'bag_akun.nama_akun', 'bag_akun.tipe_akun')
            ->orderBy('bag_akun.kode_akun')
            ->get();

        $pendapatan = $totals->where('tipe_akun', 'Pendapatan')->map(function ($a) {
            $a->jumlah = $a->total_kredit - $a->total_debet;
            return $a;
        })->values();

        $beban = $totals->where('tipe_akun', 'Beban')->map(function ($a) {
            $a->jumlah = $a->total_debet - $a->total_kredit;
            return $a;
        })->values();

        $totalPendapatan = $pendapatan->sum('jumlah');
        $totalBeban = $beban->sum('jumlah');
        $labaBersih = $totalPendapatan - $totalBeban;

        return view('akuntansi.laba-rugi', compact('awal', 'akhir', 'pendapatan', 'beban', 'totalPendapatan', 'totalBeban', 'labaBersih'));
    }

    public function neraca(Request $request)
    {
        $tanggal = $request->get('tanggal', now()->toDateString());

        $totals = DB::table('jurnal_umum')
            ->join('bag_akun', 'bag_akun.kode_akun', '=', 'jurnal_umum.kode_akun')
            ->where('tgl_jurnal', '<=', $tanggal)
            ->selectRaw('bag_akun.kode_akun, bag_akun.nama_akun, bag_akun.tipe_akun, SUM(debet) as total_debet, SUM(kredit) as total_kredit')
            ->groupBy('bag_akun.kode_akun', 'bag_akun.nama_akun', 'bag_akun.tipe_akun')
            ->orderBy('bag_akun.kode_akun')
            ->get();

        $aset = $totals->where('tipe_akun', 'Aset')->map(function ($a) {
            $a->jumlah = $a->total_debet - $a->total_kredit;
            return $a;
        })->values();

        $kewajiban = $totals->where('tipe_akun', 'Kewajiban')->map(function ($a) {
            $a->jumlah = $a->total_kredit - $a->total_debet;
            return $a;
        })->values();

        $modal = $totals->where('tipe_akun', 'Modal')->map(function ($a) {
            $a->jumlah = $a->total_kredit - $a->total_debet;
            return $a;
        })->values();

        $totalPendapatan = $totals->where('tipe_akun', 'Pendapatan')->sum(fn($a) => $a->total_kredit - $a->total_debet);
        $totalBeban = $totals->where('tipe_akun', 'Beban')->sum(fn($a) => $a->total_debet - $a->total_kredit);

        // Laba berjalan: sistem ini belum punya proses tutup buku (closing
        // entry) yang memindahkan laba/rugi ke akun Modal di akhir periode,
        // jadi supaya Neraca tetap balance (Aset = Kewajiban + Modal), laba
        // kumulatif sampai tanggal ini ditampilkan sebagai baris ekuitas
        // tersendiri - bukan dihitung ulang secara berbeda dari Laba Rugi,
        // cuma window periodenya "sejak awal s/d tanggal ini" bukan bulan
        // berjalan saja.
        $labaBerjalan = $totalPendapatan - $totalBeban;

        $totalAset = $aset->sum('jumlah');
        $totalKewajiban = $kewajiban->sum('jumlah');
        $totalModal = $modal->sum('jumlah');
        $totalEkuitas = $totalModal + $labaBerjalan;
        $totalPasiva = $totalKewajiban + $totalEkuitas;
        $balance = abs($totalAset - $totalPasiva) < 0.01;

        return view('akuntansi.neraca', compact(
            'tanggal', 'aset', 'kewajiban', 'modal', 'labaBerjalan',
            'totalAset', 'totalKewajiban', 'totalModal', 'totalEkuitas', 'totalPasiva', 'balance'
        ));
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Portal lacak pelanggan (tanpa login, read-only): pelanggan memasukkan
 * NOMOR NOTA + NOMOR HP yang cocok dengan data nota untuk melihat progres
 * pesanannya sendiri (status order, status SPK per item, pembayaran &
 * sisa, status pengambilan). Gerbang ganda ini mencegah orang iseng
 * melihat nota orang lain hanya dengan menebak nomor nota.
 *
 * Tidak menulis apa pun ke database - hanya SELECT + render.
 */
class LacakNotaController extends Controller
{
    /** Nomor HP dinormalisasi ke digit; pencocokan memakai 8 digit TERAKHIR (suffix). */
    private const SUFFIX_DIGIT = 8;

    public function form()
    {
        return view('publik.lacak-nota');
    }

    public function cari(Request $request)
    {
        $data = $request->validate([
            'nomor_nota' => 'required|string|max:40',
            'telepon' => 'required|string|max:24',
        ], [], ['nomor_nota' => 'nomor nota', 'telepon' => 'nomor HP']);

        $digits = preg_replace('/\D+/', '', (string) $data['telepon']);
        if (strlen($digits) < self::SUFFIX_DIGIT) {
            return back()
                ->with('lacakError', 'Nomor HP minimal ' . self::SUFFIX_DIGIT . ' digit.')
                ->withInput($request->only('nomor_nota', 'telepon'));
        }
        $kunci = substr($digits, -self::SUFFIX_DIGIT);

        // Nota dicari case-insensitive; ambil beberapa kandidat (nomor nota
        // bisa muncul lagi bila nota lama dibatalkan lalu dibuat ulang).
        $kandidat = DB::table('transaksi')
            ->join('konsumen', 'konsumen.id_konsumen', '=', 'transaksi.id_konsumen')
            ->whereRaw('LOWER(TRIM(transaksi.nomor_nota)) = ?', [strtolower(trim((string) $data['nomor_nota']))])
            ->orderByDesc('transaksi.id_transaksi')
            ->limit(5)
            ->get([
                'transaksi.id_transaksi', 'transaksi.nomor_nota', 'transaksi.tipe_order',
                'transaksi.tgl_order', 'transaksi.total_bayar', 'transaksi.status_pembayaran',
                'transaksi.status_order', 'transaksi.status_nota', 'transaksi.status_collecting',
                'transaksi.tgl_collecting', 'transaksi.metode_pembayaran',
                'konsumen.nama_konsumen', 'konsumen.telepon', 'konsumen.telp_norm',
            ]);

        $nota = $kandidat->first(function ($k) use ($kunci) {
            foreach ([$k->telp_norm ?? '', $k->telepon ?? ''] as $tel) {
                $d = preg_replace('/\D+/', '', (string) $tel);
                if (strlen($d) >= self::SUFFIX_DIGIT && substr($d, -self::SUFFIX_DIGIT) === $kunci) {
                    return true;
                }
            }
            return false;
        });

        if (!$nota) {
            return back()
                ->with('lacakError', 'Kombinasi nomor nota dan nomor HP tidak cocok. Periksa kembali atau hubungi kasir.')
                ->withInput($request->only('nomor_nota', 'telepon'));
        }

        $id = (int) $nota->id_transaksi;
        $item = DB::table('transaksi_detail')
            ->where('id_transaksi', $id)
            ->orderBy('id_detail')
            ->get(['nama_file', 'p_ukuran', 'l_ukuran', 'qty', 'status_spk', 'status_urgent']);

        $dibayar = (float) DB::table('pembayaran_riwayat')
            ->where('id_transaksi', $id)
            ->sum('nominal');
        $total = (float) $nota->total_bayar;
        $sisa = max(0, $total - $dibayar);

        return view('publik.lacak-nota', [
            'nota' => $nota, 'item' => $item,
            'dibayar' => $dibayar, 'total' => $total, 'sisa' => $sisa,
        ]);
    }
}

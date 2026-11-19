<?php

namespace App\Http\Controllers;

use App\Services\GerbangLaporanClosing;
use Illuminate\Http\Request;

/**
 * Halaman gerbang password Administrator untuk laporan closing.
 *
 * Dipakai oleh Laporan Kas (whole page, karena isinya rekap sesi + tombol
 * cetak closing) dan oleh cetak rekap closing per sesi. Kasir yang membuka
 * laporan akan melihat form password; setelah password Administrator cocok,
 * gembok terbuka untuk sesi login itu dan permintaan diteruskan ke tujuan
 * semula (laporan atau cetakan tertentu).
 */
class GerbangLaporanClosingController extends Controller
{
    public function create(Request $request, GerbangLaporanClosing $gerbang)
    {
        // Sudah terbuka (Administrator / baru memasukkan password) - tidak
        // perlu menampilkan form lagi.
        if ($gerbang->terbuka()) {
            return redirect($gerbang->tujuan($request));
        }

        $parameter = $gerbang->parameter($request);

        return view('laporan.kas_gerbang', [
            'parameter' => $parameter,
            'cetak' => $parameter['cetak'] ?? null,
            'menit' => GerbangLaporanClosing::MENIT,
        ]);
    }

    public function unlock(Request $request, GerbangLaporanClosing $gerbang)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ], [
            'password.required' => 'Password Administrator wajib diisi.',
        ]);

        if (! $gerbang->passwordAdministratorCocok((string) $request->input('password'))) {
            // Pesan sengaja tidak membedakan "password salah" dan "bukan
            // akun Administrator" - keduanya sama-sama tidak berhak.
            return back()
                ->withInput($request->except('password'))
                ->withErrors(['password' => 'Password Administrator tidak cocok. Laporan closing tetap terkunci.']);
        }

        $gerbang->buka();

        return redirect($gerbang->tujuan($request))
            ->with('success', 'Laporan closing dibuka untuk ' . GerbangLaporanClosing::MENIT . ' menit.');
    }
}

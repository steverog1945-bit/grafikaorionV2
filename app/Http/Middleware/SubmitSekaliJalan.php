<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menjamin satu pengiriman (satu `submit_id`) hanya diproses SEKALI oleh server. Terpasang di grup web untuk semua
 * permintaan tulis; layout mengisi `submit_id` otomatis pada form yang dikirim langsung oleh browser.
 *
 * Kenapa perlu, kalau tombol sudah dikunci di browser: kunci itu hanya hidup di satu tab. Bila jaringan putus
 * SETELAH server menyimpan, layar menampilkan "gagal", operator menekan simpan lagi, dan lahir transaksi kedua.
 * Dua tab atau dua perangkat yang mengirim isi yang sama juga lolos dari kunci browser. Aturannya:
 *
 * - `submit_id` kosong / metode GET: dibiarkan lewat (kompatibel dengan klien dan form lama).
 * - Pengiriman pertama mengunci id itu (Cache::add atomik) lalu berjalan.
 * - Bila selesai sukses, hasilnya disimpan: data JSON, atau tujuan redirect + pesan sukses untuk form biasa.
 *   Pengiriman ulang dengan id yang sama TIDAK menjalankan apa pun lagi; ia menerima hasil yang sama.
 * - Bila gagal (validasi, stok, galat), kunci dilepas supaya operator bisa memperbaiki data lalu mengirim lagi.
 * - Bila kunci masih terpegang tanpa hasil (pengiriman pertama masih jalan), pengiriman kedua ditolak, bukan
 *   diproses paralel.
 */
class SubmitSekaliJalan
{
    private const TTL_MENIT = 15;

    public function handle(Request $request, Closure $next): Response
    {
        $id = trim((string) $request->input('submit_id'));
        if ($request->isMethodSafe() || $id === '' || strlen($id) > 100) {
            return $next($request);
        }

        $kunci = 'submit:' . optional($request->route())->getName() . ':' . $request->path() . ':' . Auth::id() . ':' . $id;

        if (!Cache::add($kunci, 'diproses', now()->addMinutes(self::TTL_MENIT))) {
            return $this->ulangan($request, Cache::get($kunci));
        }

        try {
            $respons = $next($request);
        } catch (\Throwable $e) {
            Cache::forget($kunci);
            throw $e;
        }

        $hasil = $this->hasilSukses($request, $respons);
        if ($hasil !== null) {
            Cache::put($kunci, $hasil, now()->addMinutes(self::TTL_MENIT));
        } else {
            Cache::forget($kunci);
        }

        return $respons;
    }

    /** Hasil yang disimpan untuk dikembalikan ke pengiriman ulang, atau null bila pengiriman ini tidak sukses. */
    private function hasilSukses(Request $request, Response $respons): ?array
    {
        if ($respons instanceof JsonResponse) {
            $data = $respons->getData(true);

            return $respons->isSuccessful() && is_array($data) && ($data['status'] ?? null) === 'success' ? ['json' => $data] : null;
        }

        // Form biasa: sukses = redirect dengan pesan sukses dan tanpa pesan galat/validasi.
        $sesi = $request->hasSession() ? $request->session() : null;
        if ($respons->isRedirection() && $sesi && $sesi->has('success') && !$sesi->has('error') && !$sesi->has('errors')) {
            return ['redirect' => $respons->headers->get('Location'), 'success' => (string) $sesi->get('success')];
        }

        return null;
    }

    private function ulangan(Request $request, mixed $hasil): Response
    {
        if (is_array($hasil) && isset($hasil['json'])) {
            return response()->json($hasil['json'] + ['ulang' => true]);
        }
        if (is_array($hasil) && isset($hasil['redirect'])) {
            return redirect()->to($hasil['redirect'])->with('success', $hasil['success'] . ' (Kiriman ulang tidak dicatat dua kali.)');
        }

        $pesan = 'Pengiriman ini sedang diproses. Tunggu sebentar, lalu cek daftar sebelum mengirim ulang.';
        if ($request->expectsJson()) {
            return response()->json(['status' => 'error', 'msg' => $pesan], 409);
        }

        return redirect()->back()->with('error', $pesan);
    }
}

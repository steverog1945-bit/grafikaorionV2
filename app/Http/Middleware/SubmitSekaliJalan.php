<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menjamin satu pengiriman formulir (satu `submit_id`) hanya diproses SEKALI
 * oleh server, untuk endpoint JSON yang membuat dokumen (mis. nota).
 *
 * Kenapa perlu, kalau tombol sudah dikunci di browser: kunci itu hanya hidup di
 * satu tab. Bila jaringan putus SETELAH server menyimpan nota, layar operator
 * menampilkan "gagal", operator menekan simpan lagi, dan lahir nota kedua.
 * Dua tab atau dua perangkat yang mengirim isi yang sama juga lolos dari kunci
 * browser. Aturannya di sini, di server:
 *
 * - `submit_id` kosong: dibiarkan lewat (kompatibel dengan klien lama).
 * - Pengiriman pertama mengunci id itu (Cache::add atomik) lalu berjalan.
 * - Bila selesai sukses, hasilnya disimpan. Pengiriman ulang dengan id yang
 *   sama TIDAK menjalankan apa pun lagi; ia menerima hasil yang sama dan
 *   penanda `ulang: true`.
 * - Bila gagal (validasi, stok, galat), kunci dilepas supaya operator bisa
 *   memperbaiki data lalu mengirim lagi dengan id yang sama.
 * - Bila kunci masih terpegang tanpa hasil (pengiriman pertama masih jalan),
 *   pengiriman kedua ditolak 409, bukan diproses paralel.
 */
class SubmitSekaliJalan
{
    private const TTL_MENIT = 15;

    public function handle(Request $request, Closure $next): Response
    {
        $id = trim((string) $request->input('submit_id'));
        if ($id === '' || strlen($id) > 100) {
            return $next($request);
        }

        $kunci = 'submit:' . optional($request->route())->getName() . ':' . Auth::id() . ':' . $id;

        if (!Cache::add($kunci, 'diproses', now()->addMinutes(self::TTL_MENIT))) {
            $hasil = Cache::get($kunci);
            if (is_array($hasil)) {
                return response()->json($hasil + ['ulang' => true]);
            }

            return response()->json([
                'status' => 'error',
                'msg' => 'Pengiriman ini sedang diproses. Tunggu sebentar, lalu cek daftar sebelum mengirim ulang.',
            ], 409);
        }

        try {
            $respons = $next($request);
        } catch (\Throwable $e) {
            Cache::forget($kunci);
            throw $e;
        }

        $data = $respons instanceof JsonResponse ? $respons->getData(true) : null;
        if ($respons->isSuccessful() && is_array($data) && ($data['status'] ?? null) === 'success') {
            Cache::put($kunci, $data, now()->addMinutes(self::TTL_MENIT));
        } else {
            Cache::forget($kunci);
        }

        return $respons;
    }
}

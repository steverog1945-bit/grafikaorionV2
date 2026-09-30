<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Melayani berkas unggahan yang berisi data usaha (desain pelanggan, bukti bayar, bukti serah terima)
 * hanya untuk pengguna yang sudah login. Alamat tetap /storage/<folder>/<berkas>, jadi tampilan dan data
 * lama tidak berubah; yang berubah hanya Apache tidak lagi melayaninya langsung (lihat public/.htaccess).
 * Logo, gambar postingan, dan halaman login tetap publik karena memang harus tampil sebelum login.
 */
class BerkasPrivatController extends Controller
{
    public const FOLDER = ['order_thumbnails', 'jersey_thumbnails', 'jersey_bukti_bayar', 'kas_kecil_bukti', 'bukti_serah_terima', 'berkas_order'];

    public function tampil(string $folder, string $path): BinaryFileResponse
    {
        abort_unless(in_array($folder, self::FOLDER, true), 404);
        abort_if(str_contains($path, '..') || str_contains($path, "\0") || str_contains($path, '\\'), 404);

        $relatif = $folder . '/' . $path;
        $disk = Storage::disk('public');
        abort_unless($disk->exists($relatif), 404);

        $respons = response()->file($disk->path($relatif), [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=3600',
        ]);

        // Berkas yang bisa membawa skrip tidak boleh menjalankannya (unggahan lama mungkin tidak melalui BerkasAman).
        $ext = strtolower(pathinfo($relatif, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf'], true)) {
            $respons->headers->set('Content-Security-Policy', "default-src 'none'; script-src 'none'; object-src 'none'; img-src 'self' data:; style-src 'unsafe-inline'");
            $respons->headers->set('Content-Disposition', 'attachment; filename="' . basename($relatif) . '"');
        }

        return $respons;
    }
}

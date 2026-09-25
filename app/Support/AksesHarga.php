<?php

namespace App\Support;

use App\Models\User;

/**
 * Hak "boleh melihat & mengubah harga/nilai uang" (kolom roles.boleh_lihat_harga,
 * diatur di Pengaturan Hak Akses). Administrator SELALU boleh. Role tanpa hak ini
 * (Operator Desain, Operator Produksi) hanya menginput nota dan berproduksi:
 * halaman berharga ditutup (lihat menu_access.khusus_harga) dan kolom/angka uang
 * disembunyikan lewat @bolehHarga di view.
 */
class AksesHarga
{
    public static function bolehLihat(?User $user = null): bool
    {
        $user ??= auth()->user();
        if (!$user) {
            return false;
        }

        $role = $user->role;
        if (!$role) {
            return false;
        }

        return $role->nama_role === 'Administrator' || (bool) $role->boleh_lihat_harga;
    }

    /** Apakah path (mis. "finishing/3", "konsumen/5/timeline") termasuk halaman khusus harga. */
    public static function pathKhususHarga(string $path): bool
    {
        foreach (config('menu_access.khusus_harga', []) as $pola) {
            if ($path === $pola || str_starts_with($path, $pola . '/') || fnmatch($pola, $path)) {
                return true;
            }
        }

        return false;
    }
}

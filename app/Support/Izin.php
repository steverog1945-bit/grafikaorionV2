<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Hak akses per modul (menu) dengan 4 aksi: lihat, tambah, kelola (ubah), hapus.
 * Urutan: Administrator = semua boleh; override per user (user_permissions) menang atas role;
 * selain itu ikut role (role_permissions). Baris tidak ada = tidak ada akses ke modul itu.
 */
class Izin
{
    public const AKSI = ['view', 'add', 'edit', 'delete'];

    /** Cache per REQUEST (bukan per proses) supaya perubahan hak langsung berlaku di request berikutnya. */
    public static function lupakan(): void
    {
        request()->attributes->remove('izin_peta_cache');
    }

    public static function aksiDariMetode(string $metode): string
    {
        return match (strtoupper($metode)) {
            'GET', 'HEAD', 'OPTIONS' => 'view',
            'POST' => 'add',
            'DELETE' => 'delete',
            default => 'edit', // PUT, PATCH
        };
    }

    /** @return array<int, array<string,bool>> id_menu => [view, add, edit, delete] (untuk non-admin) */
    public static function peta(User $user): array
    {
        $simpan = request()->attributes->get('izin_peta_cache', []);
        if (isset($simpan[$user->id_user])) {
            return $simpan[$user->id_user];
        }

        $peta = [];
        foreach (DB::table('role_permissions')->where('id_role', $user->id_role)->get() as $r) {
            $peta[(int) $r->id_menu] = ['view' => (bool) $r->can_view, 'add' => (bool) $r->can_add, 'edit' => (bool) $r->can_edit, 'delete' => (bool) $r->can_delete];
        }
        foreach (DB::table('user_permissions')->where('id_user', $user->id_user)->get() as $r) {
            $peta[(int) $r->id_menu] = ['view' => (bool) $r->can_view, 'add' => (bool) $r->can_add, 'edit' => (bool) $r->can_edit, 'delete' => (bool) $r->can_delete];
        }

        $simpan[$user->id_user] = $peta;
        request()->attributes->set('izin_peta_cache', $simpan);

        return $peta;
    }

    public static function adalahAdmin(?User $user): bool
    {
        return optional(optional($user)->role)->nama_role === 'Administrator';
    }

    /** Boleh melakukan $aksi pada salah satu menu di $idMenus? */
    public static function boleh(?User $user, array $idMenus, string $aksi = 'view'): bool
    {
        if (!$user) {
            return false;
        }
        if (self::adalahAdmin($user)) {
            return true;
        }
        $peta = self::peta($user);
        foreach ($idMenus as $id) {
            $h = $peta[(int) $id] ?? null;
            // Tambah/kelola/hapus selalu mensyaratkan bisa melihat modulnya.
            if ($h && $h['view'] && ($aksi === 'view' || $h[$aksi])) {
                return true;
            }
        }

        return false;
    }

    /** Id menu yang boleh DILIHAT (untuk sidebar). */
    public static function menuTerlihat(User $user): array
    {
        return array_keys(array_filter(self::peta($user), fn ($h) => $h['view']));
    }
}

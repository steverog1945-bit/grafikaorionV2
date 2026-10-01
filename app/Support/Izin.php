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

    /**
     * Pengelola akun/hak akses yang BUKAN Administrator (mis. staf yang diberi
     * menu Data User atau Setting Akses) tidak boleh menaikkan hak siapa pun
     * ke atas dirinya: tidak boleh menyentuh role Administrator, role miliknya
     * sendiri, maupun akun Administrator dan akunnya sendiri. Tanpa batas ini
     * satu izin menu "Data User" sama dengan izin penuh (buat akun admin atau
     * naikkan diri sendiri). Dicek di SERVER, bukan disembunyikan di tampilan.
     */
    public static function bolehKelolaRole(?User $aktor, int $idRole): bool
    {
        if (self::adalahAdmin($aktor)) {
            return true;
        }
        if (!$aktor || (int) $aktor->id_role === $idRole) {
            return false;
        }
        $nama = DB::table('roles')->where('id_role', $idRole)->value('nama_role');

        return $nama !== null && $nama !== 'Administrator';
    }

    /** Memberikan role KE AKUN: non-admin boleh memberi role apa pun kecuali Administrator (role sendiri setara, bukan kenaikan). */
    public static function bolehBerikanRole(?User $aktor, int $idRole): bool
    {
        if (self::adalahAdmin($aktor)) {
            return true;
        }
        $nama = DB::table('roles')->where('id_role', $idRole)->value('nama_role');

        return $aktor !== null && $nama !== null && $nama !== 'Administrator';
    }

    public static function bolehKelolaUser(?User $aktor, User $target): bool
    {
        if (self::adalahAdmin($aktor)) {
            return true;
        }

        return $aktor && (int) $aktor->id_user !== (int) $target->id_user && !self::adalahAdmin($target);
    }

    /** Boleh melakukan $aksi pada salah satu menu di $idMenus? */
    /** Menu Kas Kecil (config/menu_access.php: 'kas-kecil' => [39]). */
    public const MENU_KAS_KECIL = 39;

    /**
     * Mengeluarkan uang Kas Kecil dari modul lain (bayar upah jahit, bayar komisi desainer) mensyaratkan hak TAMBAH
     * di menu Kas Kecil juga - bukan hanya hak di modul asalnya. Tanpa ini, siapa pun yang diberi hak mencatat di
     * Jasa Jahit otomatis bisa mengeluarkan uang Kas Kecil.
     */
    public static function bolehKeluarkanKasKecil(?User $user): bool
    {
        return self::boleh($user, [self::MENU_KAS_KECIL], 'add');
    }

    public static function boleh(?User $user, array $idMenus, string $aksi = 'view'): bool
    {
        if (!$user) {
            return false;
        }
        if (self::adalahAdmin($user)) {
            return true;
        }
        // Role "hanya lihat" pada modul tertentu (mis. Operator Desain di modul produksi): aksi selain Lihat ditolak
        // APA PUN isi matriks hak aksesnya - lapisan pengaman tambahan.
        if ($aksi !== 'view') {
            $terkunci = (array) config('menu_access.hanya_lihat.' . optional($user->role)->nama_role, []);
            $idMenus = array_values(array_filter($idMenus, fn ($id) => !in_array((int) $id, $terkunci, true)));
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

    /**
     * Boleh $user membuka PATH ini (akses lihat)? Dipakai saat login untuk
     * memvalidasi URL "intended" milik sesi lama sebelum mengarahkan user
     * ke sana - role baru tidak boleh ditabrakkan ke halaman yang tidak
     * berhak dia lihat (403/500 saat login dengan akun lain). Aturannya
     * ditulis SEKALI di sini dan dipakai CheckMenuAccess juga, supaya
     * keputusan "boleh lihat path ini?" tidak pernah beda antara dua tempat.
     */
    public static function bolehPath(User $user, string $path): bool
    {
        $path = trim($path, '/');
        if ($path === '') {
            return false;
        }

        // Administrator selalu lolos - sama seperti di awal CheckMenuAccess.
        if (self::adalahAdmin($user)) {
            return true;
        }

        // Prefix yang bebas untuk siapa pun yang sudah login.
        foreach ((array) config('menu_access.always_allow', []) as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return true;
            }
        }

        // Halaman harga ditutup untuk role tanpa hak lihat harga (GET saja
        // yang relevan di sini - validasi intended adalah akses "lihat").
        if (!\App\Support\AksesHarga::bolehLihat($user) && \App\Support\AksesHarga::pathKhususHarga($path)) {
            return false;
        }

        $rules = (array) config('menu_access.rules', []);
        $matchedPrefix = null;
        foreach ($rules as $prefix => $menuIds) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                if ($matchedPrefix === null || strlen($prefix) > strlen($matchedPrefix)) {
                    $matchedPrefix = $prefix;
                }
            }
        }
        if ($matchedPrefix === null) {
            return false; // belum dipetakan = fail closed, sama seperti middleware
        }

        // Path form (create/edit) menuntut hak lebih dari sekadar lihat -
        // disamakan dengan logika CheckMenuAccess.
        $aksi = 'view';
        if (preg_match('#(^|/)(create|edit)(/|$)#', $path, $mm)) {
            $aksi = $mm[2] === 'create' ? 'add' : 'edit';
        }

        return self::boleh($user, $rules[$matchedPrefix], $aksi);
    }
}

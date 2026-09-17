<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Beberapa role (mis. Operator Desain) sengaja dibatasi HANYA untuk input
 * order baru (Input Order, Order Jersey), bukan mengubah/menghapus data
 * lama. config/menu_access.php TIDAK BISA memisahkan ini sendirian karena
 * route seperti /penjualan/{id}/batalkan-nota atau /jersey/{order}
 * (update/destroy/edit-lines) berbagi prefix yang sama persis dengan route
 * "show" yang justru WAJIB tetap bisa diakses (halaman sukses setelah
 * input order baru redirect ke situ) - prefix-matching di CheckMenuAccess
 * tidak bisa membedakan keduanya karena {id}/{order} ada SEBELUM segmen
 * pembedanya. Middleware ini dipasang LANGSUNG di route mutasi yang
 * dimaksud (bukan lewat config), berlaku untuk role manapun yang
 * namanya didaftarkan di $rolesDibatasi.
 */
class RestrictInputOnlyRoles
{
    private array $rolesDibatasi = ['Operator Desain'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $namaRole = optional(optional($user)->role)->nama_role;

        if ($namaRole && in_array($namaRole, $this->rolesDibatasi, true)) {
            abort(403, 'Role Anda hanya diizinkan untuk input order baru, bukan mengubah/menghapus data yang sudah ada.');
        }

        return $next($request);
    }
}

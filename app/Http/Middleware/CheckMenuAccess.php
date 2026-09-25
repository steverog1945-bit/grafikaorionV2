<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menegakkan hak akses per-role di level ROUTE, bukan cuma menyembunyikan
 * link sidebar seperti sebelumnya (lihat catatan di config/menu_access.php).
 * Role Administrator selalu lolos; role lain dicek terhadap role_permissions
 * berdasarkan menu yang dipetakan untuk URL yang sedang diakses.
 */
class CheckMenuAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if (optional($user->role)->nama_role === 'Administrator') {
            return $next($request);
        }

        $path = trim($request->path(), '/');

        // Role baca-saja (Owner): tidak boleh mengubah data apa pun, walau tombolnya terlihat.
        if (!$request->isMethodSafe() && in_array(optional($user->role)->nama_role, config('menu_access.roles_baca_saja', []), true)) {
            abort(403, 'Role Anda bersifat baca-saja (audit). Perubahan data dilakukan oleh Administrator/staf terkait.');
        }

        foreach (config('menu_access.always_allow', []) as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return $next($request);
            }
        }

        // Halaman yang memuat harga/nilai uang ditutup untuk role tanpa hak lihat harga
        // (Operator Desain/Produksi), APA PUN menu yang diberikan padanya.
        if (!\App\Support\AksesHarga::bolehLihat($user) && \App\Support\AksesHarga::pathKhususHarga($path)) {
            abort(403, 'Role Anda tidak diizinkan melihat atau mengubah harga. Halaman ini khusus role yang berhak.');
        }

        $rules = config('menu_access.rules', []);
        $matchedPrefix = null;

        foreach ($rules as $prefix => $menuIds) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                if ($matchedPrefix === null || strlen($prefix) > strlen($matchedPrefix)) {
                    $matchedPrefix = $prefix;
                }
            }
        }

        if ($matchedPrefix === null) {
            // URL ini belum dipetakan ke menu manapun. Tolak demi keamanan
            // (fail closed) - untuk route yang sudah ada di aplikasi ini
            // seharusnya tidak pernah kejadian karena semua sudah dipetakan;
            // ini jaring pengaman untuk route baru yang lupa didaftarkan.
            abort(403, 'Rute ini belum terdaftar di peta akses. Hubungi Administrator.');
        }

        $hasAccess = DB::table('role_permissions')
            ->where('id_role', $user->id_role)
            ->whereIn('id_menu', $rules[$matchedPrefix])
            ->exists();

        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini. Hubungi Administrator untuk meminta akses.');
        }

        return $next($request);
    }
}

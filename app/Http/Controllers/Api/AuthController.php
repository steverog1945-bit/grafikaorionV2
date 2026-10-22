<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Autentikasi API publik.
 *
 * - POST /api/auth/token - Generate/refresh api_token untuk user.
 *   Body: { username, password }
 * - POST /api/auth/logout - Hapus api_token (logout).
 *   Header: Authorization: Bearer <token>
 *
 * Tabel users di ERP ini tidak memiliki kolom email/is_active - autentikasi
 * lewat username + password. Token disimpan di kolom users.api_token (via
 * migrasi 2026_11_04_110000).
 */
class AuthController extends Controller
{
    public function generateToken(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Username atau password salah.'], 401);
        }

        // Generate token acak 80 karakter
        $token = bin2hex(random_bytes(40));

        $user->update(['api_token' => $token]);

        return response()->json([
            'status'  => 'success',
            'token'   => $token,
            'user'    => [
                'id'       => $user->id_user,
                'username' => $user->username,
                'role'     => optional($user->role)->nama_role,
            ],
            'expires_at' => null,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->update(['api_token' => null]);

        return response()->json(['status' => 'success', 'message' => 'Token API dihapus.']);
    }
}

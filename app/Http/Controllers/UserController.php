<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Karyawan;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Pemetaan bagian karyawan -> nama role yang sesuai (dibakukan saat
    // melengkapi role Operator Produksi & Gudang - lihat migrasi
    // 140000/150000). Dipakai di form Buat Akun User untuk auto-pilih
    // role begitu karyawan dipilih, supaya kejadian seperti "Operator
    // Desain" dulu ke-assign ke siapa saja tanpa mikir tidak terulang -
    // admin tetap bisa override manual kalau memang ada kasus khusus.
    private const BAGIAN_ROLE_MAP = [
        'Admin'    => 'Administrator',
        'Desain'   => 'Operator Desain',
        'Produksi' => 'Operator Produksi',
        'Kasir'    => 'Kasir',
        'Gudang'   => 'Gudang',
    ];

    public function index()
    {
        $users = User::with(['karyawan', 'role'])->get();
        $karyawan = Karyawan::whereDoesntHave('user')->get(); // Hanya tampilkan karyawan yang belum punya user
        $roles = Role::all();
        $bagianRoleMap = self::BAGIAN_ROLE_MAP;
        return view('master.user.index', compact('users', 'karyawan', 'roles', 'bagianRoleMap'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_karyawan' => 'required|unique:users,id_karyawan',
            'username' => 'required|unique:users',
            'password' => 'required|min:5',
            'id_role' => 'required'
        ]);

        // password & id_role sengaja diisi lewat assignment eksplisit (bukan
        // mass-assignment) - keduanya bukan lagi $fillable di model User
        // supaya tidak bisa ke-mass-assign dari endpoint lain yang ceroboh.
        $user = new User([
            'id_karyawan' => $request->id_karyawan,
            'username' => $request->username,
        ]);
        $user->password = Hash::make($request->password);
        $user->id_role = $request->id_role;
        $user->save();

        return back()->with('success', 'User berhasil dibuat!');
    }
    public function update(Request $request, $id)
{
    $user = User::findOrFail($id);
    $user->username = $request->username;
    $user->id_role = $request->id_role;

    // Hanya update password jika diisi
    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }

    $user->save();
    return back()->with('success', 'Akun user berhasil diperbarui!');
}

public function destroy($id)
{
    $user = User::findOrFail($id);
    if ($user->id_user == Auth::id()) {
        return back()->with('error', 'Anda tidak bisa menghapus akun sendiri!');
    }
    $user->delete();
    return back()->with('success', 'Akun user berhasil dihapus!');
}
}
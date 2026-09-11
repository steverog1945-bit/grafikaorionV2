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

        User::create([
            'id_karyawan' => $request->id_karyawan,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'id_role' => $request->id_role,
        ]);

        return back()->with('success', 'User berhasil dibuat!');
    }
    public function update(Request $request, $id)
{
    $user = User::findOrFail($id);
    $data = ['id_role' => $request->id_role, 'username' => $request->username];
    
    // Hanya update password jika diisi
    if ($request->filled('password')) {
        $data['password'] = Hash::make($request->password);
    }
    
    $user->update($data);
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
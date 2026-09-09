<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AksesController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        $menus = Menu::all();
        return view('akses.index', compact('roles', 'menus'));
    }

    public function update(Request $request)
{
    // Kita gunakan Transaction agar jika satu gagal, semua batal (Data Integrity)
    DB::transaction(function () use ($request) {
        
        // 1. Bersihkan semua izin lama agar tidak duplikat
        DB::table('role_permissions')->delete();

        // 2. Ambil data dari checkbox (Array permissions[id_role][])
        $allPermissions = $request->input('permissions');

        if ($allPermissions) {
            $dataToInsert = [];
            foreach ($allPermissions as $roleId => $menuIds) {
                foreach ($menuIds as $menuId) {
                    $dataToInsert[] = [
                        'id_role' => $roleId,
                        'id_menu' => $menuId
                    ];
                }
            }
            
            // 3. Masukkan data baru secara massal
            DB::table('role_permissions')->insert($dataToInsert);
        }
    });

    return back()->with('success', 'Hak akses seluruh role berhasil disinkronkan!');
}
}
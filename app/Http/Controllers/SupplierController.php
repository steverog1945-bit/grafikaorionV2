<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function index() {
        $supplier = DB::table('supplier')->get();
        return view('master.supplier.index', compact('supplier'));
    }

    public function store(Request $request) {
        $request->validate([
            'nama_supplier' => 'required|max:255',
            'kontak' => 'required'
        ]);

        DB::table('supplier')->insert([
            'nama_supplier' => $request->nama_supplier,
            'alamat' => $request->alamat,
            'kontak' => $request->kontak
        ]);

        return redirect()->back()->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function destroy($id) {
        DB::table('supplier')->where('id_supplier', $id)->delete();
        return redirect()->back()->with('success', 'Supplier dihapus.');
    }
}
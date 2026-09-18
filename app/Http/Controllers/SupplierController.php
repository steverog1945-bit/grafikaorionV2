<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function index(Request $request) {
        $query = DB::table('supplier');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_supplier', 'like', '%' . $request->search . '%')
                  ->orWhere('kontak', 'like', '%' . $request->search . '%');
            });
        }

        $supplier = $query->orderBy('nama_supplier')->paginate(20)->withQueryString();
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

    public function update(Request $request, $id) {
        $request->validate([
            'nama_supplier' => 'required|max:255',
            'kontak' => 'required'
        ]);

        DB::table('supplier')->where('id_supplier', $id)->update([
            'nama_supplier' => $request->nama_supplier,
            'alamat' => $request->alamat,
            'kontak' => $request->kontak
        ]);

        return redirect()->back()->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy($id) {
        try {
            DB::table('supplier')->where('id_supplier', $id)->delete();
            return redirect()->back()->with('success', 'Supplier dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal hapus! Supplier ini masih punya riwayat kedatangan bahan.');
        }
    }
}
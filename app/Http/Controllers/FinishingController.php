<?php

namespace App\Http\Controllers;

use App\Models\Finishing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinishingController extends Controller
{

    public function index(Request $request)
    {
        // Pencarian dan penyaring sama dengan halaman master lain (Jasa, Konsumen).
        $data = Finishing::with(['mesin', 'kategori'])
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($w) => $w
                ->where('nama_finishing', 'like', '%' . $request->search . '%')
                ->orWhere('keterangan', 'like', '%' . $request->search . '%')))
            ->when($request->filled('id_mesin'), fn ($q) => $q->where('id_mesin', $request->id_mesin))
            ->when($request->filled('id_kategori'), fn ($q) => $request->id_kategori === 'semua'
                ? $q->whereNull('id_kategori')
                : $q->where('id_kategori', $request->id_kategori))
            ->when($request->filled('metode_hitung'), fn ($q) => $q->where('metode_hitung', $request->metode_hitung))
            ->orderBy('nama_finishing')
            ->get();
        $mesin = DB::table('mesin')->orderBy('nama_mesin')->get();
        $kategori = DB::table('kategori')->orderBy('nama_kategori')->get();

        $hargaTiers = DB::table('finishing_harga')
            ->whereIn('id_finishing', $data->pluck('id_finishing'))
            ->orderBy('range_min')
            ->get()
            ->groupBy('id_finishing');

        return view('finishing.index', compact('data', 'mesin', 'kategori', 'hargaTiers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_finishing' => 'required|string|max:100',
            'harga_default'  => 'required|numeric|min:0',
            'id_mesin'       => 'nullable|exists:mesin,id_mesin',
            'id_kategori'    => 'nullable|exists:kategori,id_kategori',
            'metode_hitung'  => 'required|in:luas,keliling,quantity',
            'keterangan'     => 'nullable|string',
        ]);

        Finishing::create($validated);

        return redirect()->back()->with('success', 'Finishing berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_finishing' => 'required|string|max:100',
            'harga_default'  => 'required|numeric|min:0',
            'id_mesin'       => 'nullable|exists:mesin,id_mesin',
            'id_kategori'    => 'nullable|exists:kategori,id_kategori',
            'metode_hitung'  => 'required|in:luas,keliling,quantity',
            'keterangan'     => 'nullable|string',
        ]);

        Finishing::findOrFail($id)->update($validated);

        return redirect()->back()->with('success', 'Finishing berhasil diperbarui');
    }

    public function destroy($id)
    {
        // Cek pemakaian di BOM Finishing supaya tidak menghapus jenis
        // finishing yang masih dipakai oleh produk manapun.
        $terpakai = DB::table('bom_finishing')->where('id_finishing', $id)->exists();
        if ($terpakai) {
            return redirect()->back()->with('error', 'Gagal hapus! Finishing ini masih digunakan pada BOM Produk.');
        }

        // Ditambahkan: tier harga dan pemakaian di transaksi_detail juga
        // menunjuk finishing. Keduanya tidak punya foreign key, jadi
        // menghapusnya akan meninggalkan baris yang menunjuk finishing
        // yang sudah tidak ada - dan harga jual per finishing ikut hilang
        // tanpa jejak.
        $terpakaiLain = [
            'tier harga'    => DB::table('finishing_harga')->where('id_finishing', $id)->exists(),
            'transaksi'     => DB::table('transaksi_detail')->where('id_finishing', $id)->exists(),
        ];
        $dipakai = array_keys(array_filter($terpakaiLain));
        if ($dipakai) {
            return redirect()->back()->with('error',
                'Gagal hapus! Finishing ini masih dipakai pada: ' . implode(', ', $dipakai) . '.'
            );
        }

        Finishing::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Finishing dihapus');
    }

}

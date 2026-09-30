<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JasaController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('jasa')
            ->leftJoin('satuan', 'jasa.id_satuan', '=', 'satuan.id_satuan')
            ->select('jasa.*', 'satuan.nama_satuan');

        if ($request->filled('search')) {
            $query->where('jasa.nama_jasa', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('id_satuan')) {
            $query->where('jasa.id_satuan', $request->id_satuan);
        }

        $jasa = $query->orderBy('jasa.nama_jasa')->paginate(20)->withQueryString();

        $satuan = DB::table('satuan')->orderBy('nama_satuan')->get();

        $hargaTiers = DB::table('jasa_harga')
            ->whereIn('id_jasa', $jasa->pluck('id_jasa'))
            ->orderBy('range_min')
            ->get()
            ->groupBy('id_jasa');

        return view('master.jasa.index', compact('jasa', 'satuan', 'hargaTiers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_jasa'      => 'required|max:100',
            'id_satuan'      => 'nullable|exists:satuan,id_satuan',
            'harga_default'  => 'nullable|numeric|min:0',
            'keterangan'     => 'nullable',
        ]);

        $id = DB::table('jasa')->insertGetId([
            'nama_jasa'      => $request->nama_jasa,
            'id_satuan'      => $request->id_satuan ?: null,
            'harga_default'  => $request->harga_default ?: null,
            'keterangan'     => $request->keterangan,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        AuditLogger::log('created', 'jasa', (int) $id, null, (array) DB::table('jasa')->where('id_jasa', $id)->first(), 'Jasa baru: ' . $request->nama_jasa);

        return redirect()->back()->with('success', 'Jasa berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_jasa'     => 'required|max:100',
            'id_satuan'     => 'nullable|exists:satuan,id_satuan',
            'harga_default' => 'nullable|numeric|min:0',
        ]);

        $lama = DB::table('jasa')->where('id_jasa', $id)->first();
        if (!$lama) {
            return redirect()->back()->with('error', 'Jasa tidak ditemukan - mungkin sudah dihapus pengguna lain.');
        }

        DB::table('jasa')->where('id_jasa', $id)->update([
            'nama_jasa'      => $request->nama_jasa,
            'id_satuan'      => $request->id_satuan ?: null,
            'harga_default'  => $request->harga_default ?: null,
            'keterangan'     => $request->keterangan,
            'updated_at'     => now(),
        ]);

        AuditLogger::log('updated', 'jasa', (int) $id, (array) $lama, (array) DB::table('jasa')->where('id_jasa', $id)->first(), 'Jasa diperbarui: ' . $request->nama_jasa);

        return redirect()->back()->with('success', 'Jasa berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $lama = DB::table('jasa')->where('id_jasa', $id)->first();
        DB::table('jasa')->where('id_jasa', $id)->delete();
        if ($lama) {
            AuditLogger::log('deleted', 'jasa', (int) $id, (array) $lama, null, 'Jasa dihapus: ' . $lama->nama_jasa);
        }
        return redirect()->back()->with('success', 'Jasa berhasil dihapus.');
    }
}

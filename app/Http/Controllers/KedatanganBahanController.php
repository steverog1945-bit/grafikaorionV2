<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\BahanBaku;
use App\Models\PembelianBahan;
use App\Models\KartuStok;
use App\Models\KedatanganBahan;
use App\Models\KedatanganBahanDetail;
use App\Models\Gudang;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KedatanganBahanController extends Controller
{
    /**
     * Ambil daftar gudang dari tabel `gudang` yang sesungguhnya.
     * Sebelumnya data ini di-hardcode langsung di controller (2 baris
     * dummy "Gudang Utama" / "Gudang Samping"). Sekarang dibaca dari
     * database, dengan fallback ke data lama HANYA jika tabel `gudang`
     * belum ada / masih kosong, supaya form kedatangan bahan tetap bisa
     * dipakai walau migration belum sempat dijalankan di environment ini.
     */
    private function getGudangList()
    {
        if (Schema::hasTable('gudang')) {
            $gudangs = Gudang::orderBy('id_gudang')->get();
            if ($gudangs->isNotEmpty()) {
                return $gudangs;
            }
        }

        return collect([
            (object)['id_gudang' => 1, 'nama_gudang' => 'Gudang Utama'],
            (object)['id_gudang' => 2, 'nama_gudang' => 'Gudang Samping'],
        ]);
    }

    public function index(Request $request)
{
    $query = KedatanganBahan::with(['supplier', 'gudang']);

    // 1. Filter Pencarian (No SJ atau Nama Supplier)
    $query->when($request->search, function ($q) use ($request) {
        $q->where('no_surat_jalan', 'like', '%' . $request->search . '%')
          ->orWhereHas('supplier', function ($sq) use ($request) {
              $sq->where('nama_supplier', 'like', '%' . $request->search . '%');
          });
    });

    // 2. Filter Tanggal (Range)
    $query->when($request->from, function ($q) use ($request) {
        $q->whereDate('tgl_datang', '>=', $request->from);
    });

    $query->when($request->to, function ($q) use ($request) {
        $q->whereDate('tgl_datang', '<=', $request->to);
    });

    // Urutkan berdasarkan yang terbaru dan paginasi
    $kedatangans = $query->latest('tgl_datang')->paginate(15);

    return view('kedatangan-bahan.index', compact('kedatangans'));
}
    public function create()
    {
        $suppliers = Supplier::orderBy('nama_supplier')->get();
        $bahans = BahanBaku::where('status', 'aktif')->orderBy('nama_bahan')->get();

        $gudangs = $this->getGudangList();
        return view('kedatangan-bahan.create', compact('suppliers', 'bahans', 'gudangs'));
    }

  public function store(Request $request)
{
    // 1. Validasi Backend (Pastikan field sesuai dengan name di HTML)
    $request->validate([
        'no_surat_jalan' => 'required|unique:kedatangan_bahan,no_surat_jalan',
        'id_supplier'    => 'required|exists:supplier,id_supplier',
        'tgl_datang'     => 'required|date',
        'id_bahan'       => 'required|array|min:1',
        'qty'            => 'required|array|min:1',
        'qty.*'          => 'required|numeric|min:0.01',
    ]);

    try {
        \DB::beginTransaction();

        // 2. Simpan Header
        $kedatangan = KedatanganBahan::create([
    'no_surat_jalan' => $request->no_surat_jalan,
    'id_supplier'    => $request->id_supplier,
    'tgl_datang'     => $request->tgl_datang,
    'id_gudang'      => $request->id_gudang, // Tambahkan ini
    'keterangan'     => $request->keterangan,
]);

        // 3. Simpan Detail (Looping Array)
        foreach ($request->id_bahan as $index => $id_bahan) {
            $kedatangan->detail()->create([
                'id_bahan' => $id_bahan,
                'qty'      => $request->qty[$index],
            ]);

            // OPSIONAL: Update stok di tabel bahan jika diperlukan
            // $bahan = Bahan::find($id_bahan);
            // $bahan->increment('stok', $request->qty[$index]);
        }

        \DB::commit();
        return redirect()->route('kedatangan-bahan.index')
                         ->with('success', 'Data berhasil disimpan!');

    } catch (\Exception $e) {
        \DB::rollBack();
        return back()->withInput()->with('error', 'Gagal simpan: ' . $e->getMessage());
    }
}
public function validateStatus($id)
{
    return DB::transaction(function () use ($id) {
        // Ambil data kedatangan beserta itemnya
        $kedatangan = KedatanganBahan::with('detail')->findOrFail($id);

        if ($kedatangan->status == 'Selesai') {
            return back()->with('error', 'Peringatan: Data ini sudah divalidasi sebelumnya.');
        }

        foreach ($kedatangan->detail as $item) {
            // Ambil stok terakhir bahan baku
            $bahan = BahanBaku::where('id_bahan', $item->id_bahan)->first();
            
            $stokAwal = $bahan->stok;
            $stokAkhir = $stokAwal + $item->qty;

            // 1. Update Stok di Master Bahan
            $bahan->update(['stok' => $stokAkhir]);

            // 2. Insert ke Kartu Stok (Sesuai Struktur SQL Anda)
            KartuStok::create([
                'id_bahan'      => $item->id_bahan,
                'tanggal'       => now(),
                'tipe'          => 'IN',
                'qty'           => $item->qty,
                'stok_sebelum'  => $stokAwal,
                'stok_sesudah'  => $stokAkhir,
                'referensi'     => $kedatangan->no_surat_jalan,
                'keterangan'    => 'Penerimaan Gudang: ' . ($kedatangan->keterangan ?? 'Tanpa Catatan'),
                'created_by'    => auth()->id(), // Mencatat siapa yang melakukan validasi
            ]);
        }

        // 3. Update Status Kedatangan
        $kedatangan->update(['status' => 'Selesai']);

        return back()->with('success', 'Berhasil! Stok telah diperbarui dan tercatat di Kartu Stok.');
    });
}
    public function edit($id)
    {
        $kedatangan = KedatanganBahan::with('detail.bahan')->findOrFail($id);
        $suppliers = Supplier::orderBy('nama_supplier')->get();
        $bahans = BahanBaku::where('status', 'aktif')->orderBy('nama_bahan')->get();

        $gudangs = $this->getGudangList();

        return view('kedatangan-bahan.create', compact('kedatangan', 'suppliers', 'bahans', 'gudangs'));
    }
public function bulkDelete(Request $request)
{
    $ids = $request->ids;

    if (!$ids) {
        return back()->with('error', 'Tidak ada data dipilih');
    }

    DB::transaction(function () use ($ids) {

        foreach ($ids as $id) {

            $kedatangan = KedatanganBahan::with('detail')
                ->where('id_kedatangan', $id)
                ->first();

            if (!$kedatangan) continue;

            // 🔴 Kurangi stok jika selesai - dan CATAT ke kartu_stok. Sebelumnya
            // baris ini cuma decrement() langsung tanpa insert kartu_stok sama
            // sekali, jadi begitu kedatangan yang sudah "Selesai" dihapus,
            // bahan_baku.stok turun tapi kartu_stok tetap menyimpan baris IN
            // lama seolah stok itu masih berlaku - ledger dan angka live jadi
            // tidak nyambung, tidak bisa dipertanggungjawabkan lagi.
            if ($kedatangan->status == 'Selesai') {
                foreach ($kedatangan->detail as $d) {
                    $bahan = BahanBaku::where('id_bahan', $d->id_bahan)->lockForUpdate()->first();
                    if (!$bahan) continue;

                    $stokSebelum = $bahan->stok;
                    $stokSesudah = $stokSebelum - $d->qty;
                    $bahan->update(['stok' => $stokSesudah]);

                    DB::table('kartu_stok')->insert([
                        'id_bahan'     => $d->id_bahan,
                        'tanggal'      => now(),
                        'tipe'         => 'OUT',
                        'qty'          => $d->qty,
                        'stok_sebelum' => $stokSebelum,
                        'stok_sesudah' => $stokSesudah,
                        'referensi'    => $kedatangan->no_surat_jalan,
                        'keterangan'   => 'Pembatalan kedatangan bahan (hapus massal) - stok dikembalikan',
                        'created_by'   => auth()->id(),
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }
            }

            // Hapus pembelian terkait
            PembelianBahan::where('id_kedatangan', $id)->delete();

            // Hapus detail
            $kedatangan->detail()->delete();

            // Hapus header
            $kedatangan->delete();
        }
    });

    return redirect()->route('kedatangan-bahan.index')
        ->with('success', 'Data berhasil dihapus');
}
    public function update(Request $request, $id)
{
    $request->validate([
        'no_surat_jalan' => 'required',
        'tgl_datang'     => 'required|date',
        'id_supplier'    => 'required',
        'id_bahan'       => 'required|array|min:1',
        'qty'            => 'required|array|min:1',
    ]);

    $kedatangan = KedatanganBahan::with('detail')->findOrFail($id);

    DB::transaction(function () use ($request, $kedatangan) {

        // 🔴 1. Jika sebelumnya Selesai → kembalikan stok lama, DAN catat ke
        // kartu_stok (sebelumnya cuma decrement() langsung - stok berubah
        // tapi tidak ada baris ledger yang menjelaskan kenapa).
        if ($kedatangan->status == 'Selesai') {
            foreach ($kedatangan->detail as $old) {
                $bahan = BahanBaku::where('id_bahan', $old->id_bahan)->lockForUpdate()->first();
                if (!$bahan) continue;

                $stokSebelum = $bahan->stok;
                $stokSesudah = $stokSebelum - $old->qty;
                $bahan->update(['stok' => $stokSesudah]);

                DB::table('kartu_stok')->insert([
                    'id_bahan'     => $old->id_bahan,
                    'tanggal'      => now(),
                    'tipe'         => 'OUT',
                    'qty'          => $old->qty,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,
                    'referensi'    => $kedatangan->no_surat_jalan,
                    'keterangan'   => 'Edit kedatangan bahan - stok lama dikembalikan sebelum diganti data baru',
                    'created_by'   => auth()->id(),
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }

        // 🔵 2. Update header
        $kedatangan->update([
            'no_surat_jalan' => $request->no_surat_jalan,
            'tgl_datang'     => $request->tgl_datang,
            'id_supplier'    => $request->id_supplier,
            'id_gudang'      => $request->id_gudang ?? 1,
            'status'         => $request->status ?? 'Draft',
                'keterangan'     => $request->keterangan,

        ]);

        // 🔵 3. Hapus detail lama
        $kedatangan->detail()->delete();

        // 🔵 4. Insert detail baru
        foreach ($request->id_bahan as $key => $id_bahan) {

            KedatanganBahanDetail::create([
                'id_kedatangan' => $kedatangan->id_kedatangan,
                'id_bahan'      => $id_bahan,
                'qty'           => $request->qty[$key],
                'satuan'        => $request->satuan[$key] ?? '',
                'status'        => 'OK',
            ]);

            // 🟢 5. Tambah stok jika status selesai - juga dicatat ke
            // kartu_stok (sebelumnya cuma increment() langsung).
            if (($request->status ?? 'Draft') == 'Selesai') {
                $bahan = BahanBaku::where('id_bahan', $id_bahan)->lockForUpdate()->first();
                if ($bahan) {
                    $stokSebelum = $bahan->stok;
                    $stokSesudah = $stokSebelum + $request->qty[$key];
                    $bahan->update(['stok' => $stokSesudah]);

                    DB::table('kartu_stok')->insert([
                        'id_bahan'     => $id_bahan,
                        'tanggal'      => now(),
                        'tipe'         => 'IN',
                        'qty'          => $request->qty[$key],
                        'stok_sebelum' => $stokSebelum,
                        'stok_sesudah' => $stokSesudah,
                        'referensi'    => $request->no_surat_jalan,
                        'keterangan'   => 'Edit kedatangan bahan - stok baru ditambahkan',
                        'created_by'   => auth()->id(),
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }
            }
        }
    });

    return redirect()->route('kedatangan-bahan.index')
        ->with('success', 'Data berhasil diperbarui.');
}
public function ajaxBahan(Request $request)
{
    $search = $request->search;

    // status='aktif' konsisten dengan 2 tempat lain di controller ini
    // (create/edit) yang sudah memfilternya - endpoint search ini yang
    // sebelumnya kelewatan, jadi bahan nonaktif masih bisa dipilih lewat
    // pencarian meski tidak muncul di daftar awal.
    $bahans = BahanBaku::query()
        ->where('status', 'aktif')
        ->when($search, function ($q) use ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('nama_bahan', 'like', "%{$search}%")
                   ->orWhere('kode_bahan', 'like', "%{$search}%");
            });
        })
        ->orderBy('nama_bahan')
        ->paginate(10);

    return response()->json($bahans);
}
    public function destroy($id)
{
    $kedatangan = KedatanganBahan::with('detail')->findOrFail($id);

    DB::transaction(function() use($kedatangan) {

        // 🔴 Kurangi stok jika sudah selesai - dan catat ke kartu_stok
        // (sebelumnya cuma decrement() langsung, sama seperti bulkDelete()).
        if ($kedatangan->status == 'Selesai') {
            foreach ($kedatangan->detail as $d) {
                $bahan = BahanBaku::where('id_bahan', $d->id_bahan)->lockForUpdate()->first();
                if (!$bahan) continue;

                $stokSebelum = $bahan->stok;
                $stokSesudah = $stokSebelum - $d->qty;
                $bahan->update(['stok' => $stokSesudah]);

                DB::table('kartu_stok')->insert([
                    'id_bahan'     => $d->id_bahan,
                    'tanggal'      => now(),
                    'tipe'         => 'OUT',
                    'qty'          => $d->qty,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,
                    'referensi'    => $kedatangan->no_surat_jalan,
                    'keterangan'   => 'Kedatangan bahan dihapus - stok dikembalikan',
                    'created_by'   => auth()->id(),
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }

        $kedatangan->detail()->delete();
        $kedatangan->delete();
    });

    return redirect()->route('kedatangan-bahan.index')
        ->with('success','Data kedatangan berhasil dihapus.');
}
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PembelianBahan;
use App\Models\PembelianBahanDetail;
use App\Models\KedatanganBahan;
use App\Models\BahanBaku;
use Illuminate\Support\Facades\DB;

class PembelianBahanController extends Controller
{

    public function index(Request $request)
    {
        // Filter search/from/to sebelumnya ditampilkan di form tapi diabaikan
        // total oleh controller (index() tidak menerima Request sama sekali) -
        // apapun yang diisi user, hasil yang tampil selalu daftar penuh tanpa
        // filter.
        $query = PembelianBahan::with('kedatangan.supplier');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('no_faktur', 'like', "%{$search}%")
                  ->orWhereHas('kedatangan.supplier', function ($qs) use ($search) {
                      $qs->where('nama_supplier', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('from')) {
            $query->whereDate('tgl_transaksi', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('tgl_transaksi', '<=', $request->to);
        }

        $data = $query->orderBy('id_pembelian', 'desc')->paginate(10)->withQueryString();

        return view('pembelian-bahan.index', compact('data'));

    }

public function create()
{
    // Mengambil kedatangan yang statusnya Selesai dan belum diproses menjadi pembelian
    $kedatangans = \App\Models\KedatanganBahan::with('supplier')
        ->where('status', 'Selesai')
        ->whereDoesntHave('pembelian')
        ->get();

    return view('pembelian-bahan.create', compact('kedatangans'));
}
public function kedatanganDetail(Request $request)
{
    $id = $request->query('id_kedatangan');
    $kedatangan = \App\Models\KedatanganBahan::with(['supplier', 'detail.bahan'])->find($id);

    if (!$kedatangan) return response()->json(['detail' => []]);

    return response()->json([
        'supplier' => $kedatangan->supplier->nama_supplier ?? 'Supplier Umum',
        'detail' => $kedatangan->detail->map(function($item) {
            return [
                'id_bahan' => $item->id_bahan,
                'nama_bahan' => $item->bahan->nama_bahan,
                'kode_bahan' => $item->bahan->kode_bahan,
                'qty' => $item->qty,
                'satuan' => $item->bahan->satuan_stok,
            ];
        })
    ]);
}
    public function store(Request $request)
    {

        $request->validate([
            'id_kedatangan'=>'required',
            'no_faktur'=>'required',
            'tgl_transaksi'=>'required',
            'tipe_pembelian'=>'required|in:Cash,Tempo',
            'id_bahan'=>'required|array|min:1',
            'qty'=>'required|array|min:1',
            'harga'=>'required|array|min:1',
            'qty.*'=>'required|numeric|min:0.01',
            'harga.*'=>'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try{

            $includePajak = $request->boolean('include_pajak');

            $pembelian = PembelianBahan::create([
                'id_kedatangan'=>$request->id_kedatangan,
                'no_faktur'=>$request->no_faktur,
                'tgl_transaksi'=>$request->tgl_transaksi,
                'tipe_pembelian'=>$request->tipe_pembelian,
                'include_pajak'=>$includePajak,
                'jatuh_tempo_hari'=>$request->tempo_hari,
                'grand_total'=>0
            ]);

            // Subtotal, diskon, potongan, dan PPN 11% dihitung ULANG di sini
            // memakai rumus yang SAMA dengan calculateGrandTotal() di form
            // (qty*harga - diskon - potongan, lalu *1.11 kalau PPN dicentang).
            // Sebelumnya field diskon/potongan/include_pajak yang diisi di form
            // tidak pernah dibaca sama sekali oleh server - grand_total yang
            // tersimpan bisa berbeda dari yang dilihat operator saat entry.
            $grandTotal = 0;

            foreach($request->id_bahan as $i=>$idBahan){

                $qty      = (float) $request->qty[$i];
                $harga    = (float) $request->harga[$i];
                $diskon   = (float) ($request->diskon[$i] ?? 0);
                $potongan = (float) ($request->potongan[$i] ?? 0);

                $subtotal = ($qty * $harga) - $diskon - $potongan;

                PembelianBahanDetail::create([
                    'id_pembelian'=>$pembelian->id_pembelian,
                    'id_bahan'=>$idBahan,
                    'qty'=>$qty,
                    'harga'=>$harga,
                    'diskon'=>$diskon,
                    'potongan'=>$potongan,
                    'subtotal'=>$subtotal
                ]);

                $grandTotal += $subtotal;

                // CATATAN: stok TIDAK ditambah di sini. Barang secara fisik sudah
                // masuk dan tercatat di kartu_stok saat Kedatangan Bahan divalidasi
                // (KedatanganBahanController::validateStatus). Pembelian Bahan hanya
                // dibuat dari kedatangan yang sudah berstatus 'Selesai' (lihat create()
                // di atas), jadi ini murni faktur/tagihan atas barang yang sama —
                // menambah stok lagi di sini akan menghitung barang dua kali.
            }

            if ($includePajak) {
                $grandTotal *= 1.11;
            }

            if ($request->tipe_pembelian == 'Cash') {
                $pembelian->status_pembayaran = 'Lunas';
                $pembelian->tgl_pelunasan = $request->tgl_transaksi;
            } else {
                $pembelian->status_pembayaran = 'Hutang';
                $pembelian->jatuh_tempo_tgl = $request->jatuh_tempo_tgl;
            }

            $pembelian->save();
            $pembelian->update([
                'grand_total'=>$grandTotal
            ]);

            DB::commit();

            return redirect()->route('pembelian-bahan.index')
                ->with('success','Pembelian berhasil disimpan');

        }catch(\Exception $e){

            DB::rollBack();
            return back()->with('error',$e->getMessage());

        }


    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;

        if (!$ids) {
            return back()->with('error', 'Tidak ada data dipilih');
        }

        DB::beginTransaction();

        try {
            // CATATAN: sama seperti destroy() tunggal - stok TIDAK disentuh di
            // sini, karena store() tidak lagi menambah stok (lihat catatan di
            // atas). Menghapus faktur pembelian secara massal tidak mengubah
            // barang yang secara fisik sudah ada di gudang.
            PembelianBahanDetail::whereIn('id_pembelian', $ids)->delete();
            PembelianBahan::whereIn('id_pembelian', $ids)->delete();

            DB::commit();

            return back()->with('success', count($ids) . ' data pembelian berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
    public function bahan()
{
    return $this->belongsTo(BahanBaku::class, 'id_bahan');
}
public function show($id)
{
    // Mengambil data pembelian beserta relasi kedatangan, supplier, dan detail bahan
    $pembelian = PembelianBahan::with([
        'kedatangan.supplier', 
        'detail.bahan'
    ])->findOrFail($id);

    return view('pembelian-bahan.show', compact('pembelian'));
}

    public function destroy($id)
    {

        DB::beginTransaction();

        try{

            $data = PembelianBahan::findOrFail($id);

            // CATATAN: stok TIDAK dikurangi di sini, karena store() di atas tidak
            // lagi menambah stok (stok sudah ditambahkan saat Kedatangan Bahan
            // divalidasi). Menghapus faktur pembelian tidak mengubah barang yang
            // secara fisik sudah ada di gudang.
            $data->delete();

            DB::commit();

            return back()->with('success','Data berhasil dihapus');

        }catch(\Exception $e){

            DB::rollBack();
            return back()->with('error',$e->getMessage());

        }

    }

// Menampilkan form edit (Terbatas pada data non-stok seperti No Faktur/Tanggal)
public function edit($id)
{
    $pembelian = PembelianBahan::with('kedatangan.supplier')->findOrFail($id);
    return view('pembelian-bahan.edit', compact('pembelian'));
}

// Update data dasar
public function update(Request $request, $id)
{
    $request->validate([
        'no_faktur' => 'required',
        'tgl_transaksi' => 'required'
    ]);

    $pembelian = PembelianBahan::findOrFail($id);
    $pembelian->update($request->only('no_faktur', 'tgl_transaksi'));

    return redirect()->route('pembelian-bahan.index')->with('success', 'Faktur berhasil diperbarui');
}

// Fitur Aksi Pelunasan (ERP Style)
public function pelunasan(Request $request, $id)
{
    // Pastikan mencari berdasarkan kolom id_pembelian
    $pembelian = DB::table('pembelian_bahan')->where('id_pembelian', $id)->first();

    if (!$pembelian) {
        return redirect()->back()->with('error', 'Data pembelian dengan ID ' . $id . ' tidak ditemukan.');
    }

    DB::table('pembelian_bahan')->where('id_pembelian', $id)->update([
        'status_pembayaran' => 'Lunas',
        'tgl_pelunasan'     => now(),
        'updated_at'        => now()
    ]);

    return redirect()->back()->with('success', 'Pembayaran berhasil dilunaskan!');
}
public function getKedatanganDetail(Request $request)
{
    $id = $request->id_kedatangan;
    $data = KedatanganBahan::with(['supplier', 'detail.bahan'])->findOrFail($id);

    return response()->json([
        'supplier' => $data->supplier,
        'detail' => $data->detail
    ]);
}

}
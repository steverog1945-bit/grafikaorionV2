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

    public function index()
    {

        $data = PembelianBahan::with('kedatangan.supplier')
            ->orderBy('id_pembelian','desc')
            ->paginate(10);

        return view('pembelian-bahan.index',compact('data'));

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
            'tgl_transaksi'=>'required'
        ]);

        DB::beginTransaction();

        try{

            $pembelian = PembelianBahan::create([
                'id_kedatangan'=>$request->id_kedatangan,
                'no_faktur'=>$request->no_faktur,
                'tgl_transaksi'=>$request->tgl_transaksi,
                'tipe_pembelian'=>'Biasa',
                'grand_total'=>0
            ]);

            $grandTotal = 0;

            foreach($request->id_bahan as $i=>$idBahan){

                $qty   = $request->qty[$i];
                $harga = $request->harga[$i];

                $subtotal = $qty * $harga;

                PembelianBahanDetail::create([
                    'id_pembelian'=>$pembelian->id_pembelian,
                    'id_bahan'=>$idBahan,
                    'qty'=>$qty,
                    'harga'=>$harga,
                    'subtotal'=>$subtotal
                ]);

                $grandTotal += $subtotal;

                $bahan = BahanBaku::find($idBahan);

                $stokAwal = $bahan->stok;
                $stokBaru = $stokAwal + $qty;

                $bahan->update([
                    'stok'=>$stokBaru
                ]);

                DB::table('kartu_stok')->insert([

                    'id_bahan'=>$idBahan,
                    'tanggal'=>now(),
                    'tipe'=>'IN',
                    'qty'=>$qty,
                    'stok_sebelum'=>$stokAwal,
                    'stok_sesudah'=>$stokBaru,
                    'referensi'=>$request->no_faktur,
                    'keterangan'=>'Pembelian bahan',
                    'created_by'=>auth()->user()->id_user ?? null

                ]);

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

            foreach($data->detail as $d){

                $bahan = BahanBaku::find($d->id_bahan);

                $stokBaru = $bahan->stok - $d->qty;

                $bahan->update([
                    'stok'=>$stokBaru
                ]);

            }

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
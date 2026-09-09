<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProdukController extends Controller
{
public function index(Request $request)
{
    // 1. Buat subquery untuk mengambil daftar bahan baku per produk
    $bomSubquery = DB::table('bom_detail')
        ->leftJoin('bahan_baku', 'bom_detail.id_bahan', '=', 'bahan_baku.id_bahan')
        ->select(
            'bom_detail.id_produk',
            DB::raw("GROUP_CONCAT(DISTINCT bahan_baku.nama_bahan SEPARATOR ', ') as daftar_bahan"),
            DB::raw("GROUP_CONCAT(bom_detail.cara_hitung_pemakaian SEPARATOR ' | ') as cara_hitung_pemakaian")
        )
        ->groupBy('bom_detail.id_produk');

    // 2. Query utama: Join produk dengan mesin dan hasil subquery BOM tadi
    $query = DB::table('produk')
        ->leftJoin('mesin', 'produk.id_mesin', '=', 'mesin.id_mesin')
        ->leftJoinSub($bomSubquery, 'bom_agg', function ($join) {
            $join->on('produk.id_produk', '=', 'bom_agg.id_produk');
        })
        ->select('produk.*', 'mesin.nama_mesin', 'bom_agg.daftar_bahan', 'bom_agg.cara_hitung_pemakaian');

    // 3. Filter Quick Search
    if ($request->filled('search')) {
        $searchTerm = '%' . $request->search . '%';
        $query->where(function($q) use ($searchTerm) {
            $q->where('produk.nama_produk', 'like', $searchTerm)
              ->orWhere('produk.barcode', 'like', $searchTerm);
        });
    }

    // 4. Filter Mesin
    if ($request->filled('category')) {
        $query->where('produk.id_mesin', $request->category);
    }

    // 5. Eksekusi Pagination
    $produk = $query->latest('produk.created_at')
        ->paginate(25)
        ->withQueryString();

    // Data pendukung untuk modal
    $mesin = DB::table('mesin')->get();
    $bahan_baku = DB::table('bahan_baku')
        ->leftJoin('kategori', 'bahan_baku.id_kategori', '=', 'kategori.id_kategori')
        ->select('bahan_baku.*', 'kategori.nama_kategori') 
        ->get();

    return view('master.produk.index', compact('produk', 'bahan_baku', 'mesin'));
}
    public function store(Request $request)
    {
        // Validasi dasar
        $request->validate([
            'nama_produk' => 'required',
            'metode_hitung' => 'required',
            'id_bahan' => 'required|array'
        ]);

        return DB::transaction(function () use ($request) {
            // Hitung Min Order
            $minOrderValue = ($request->metode_hitung == 'ukuran') 
                ? (($request->min_p ?? 0) * ($request->min_l ?? 0)) / 10000 
                : ($request->min_order_qty ?? 0);

            // 1. Insert Produk
            $id_produk = DB::table('produk')->insertGetId([
                'nama_produk'   => $request->nama_produk,
                'id_mesin'      => $request->id_mesin,
                'metode_hitung' => $request->metode_hitung,
                'barcode'       => $request->barcode, // Pastikan barcode masuk
                'min_order'     => $minOrderValue,
                'keterangan'    => $request->keterangan ?? '-',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            // 2. Insert BOM
            $bomData = [];
            foreach($request->id_bahan as $key => $val_id_bahan) {
                if(!empty($val_id_bahan)) {
                    $bomData[] = [
                        'id_produk'             => $id_produk,
                        'id_bahan'              => $val_id_bahan,
                        'cara_hitung_pemakaian' => $request->cara_hitung_pemakaian[$key] ?? 'Q',
                        'created_at'            => now(),
                        'updated_at'            => now(),
                    ];
                }
            }
            
            if(!empty($bomData)) {
                DB::table('bom_detail')->insert($bomData);
            }

            return redirect()->back()->with('success', 'Master Produk & Engineering BOM berhasil disimpan!');
        });
    }
public function show($id)
{
    $produk = DB::table('produk')->where('id_produk', $id)->first();
    $bom = DB::table('bom_detail')
        ->join('bahan_baku', 'bom_detail.id_bahan', '=', 'bahan_baku.id_bahan')
        ->where('bom_detail.id_produk', $id)
        ->select('bahan_baku.id_bahan', 'bahan_baku.nama_bahan', 'bahan_baku.satuan_stok', 'bom_detail.cara_hitung_pemakaian')
        ->get();

    return response()->json([
        'produk' => $produk,
        'bom'    => $bom
    ]);
}

    public function update(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $minOrderValue = ($request->metode_hitung == 'ukuran') 
                ? (($request->min_p ?? 0) * ($request->min_l ?? 0)) / 10000 
                : ($request->min_order_qty ?? $request->min_order ?? 0);

            // 1. Update Produk
            DB::table('produk')->where('id_produk', $id)->update([
                'nama_produk'   => $request->nama_produk,
                'id_mesin'      => $request->id_mesin,
                'metode_hitung' => $request->metode_hitung,
                'barcode'       => $request->barcode,
                'min_order'     => $minOrderValue,
                'keterangan'    => $request->keterangan ?? '-',
                'updated_at'    => now(),
            ]);

            // 2. Sync BOM (Delete & Re-insert adalah cara termudah untuk array input)
            DB::table('bom_detail')->where('id_produk', $id)->delete();
            
            if($request->has('id_bahan')) {
                $bomData = [];
                foreach($request->id_bahan as $key => $val_id_bahan) {
                    if(!empty($val_id_bahan)) {
                        $bomData[] = [
                            'id_produk'             => $id,
                            'id_bahan'              => $val_id_bahan,
                            'cara_hitung_pemakaian' => $request->cara_hitung_pemakaian[$key] ?? 'Q',
                            'created_at'            => now(),
                            'updated_at'            => now(),
                        ];
                    }
                }
                if(!empty($bomData)) {
                    DB::table('bom_detail')->insert($bomData);
                }
            }

            return redirect()->back()->with('success', 'Update Produk & Engineering Berhasil.');
        });
    }
public function potongStokOtomatis($id_order_detail)
{
    $order = DB::table('order_details')->where('id', $id_order_detail)->first();
    $bom_items = DB::table('bom_detail')->where('id_produk', $order->id_produk)->get();

    foreach ($bom_items as $item) {
        $P = $order->panjang;
        $L = $order->lebar;
        $Q = $order->qty;
        
        // Gunakan str_replace yang aman
        $ekspresi = str_ireplace(['P', 'L', 'Q'], [$P, $L, $Q], $item->cara_hitung_pemakaian);
        
        // Perhitungan aman tanpa eval() jika memungkinkan, 
        // tapi jika rumus dinamis, pastikan input sudah divalidasi.
        $hasil_per_unit = eval("return $ekspresi;"); 
        $total_potong = $hasil_per_unit * $Q;

        DB::table('bahan_baku')->where('id_bahan', $item->id_bahan)->decrement('stok', $total_potong);
    }
}
    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            // Hapus ketergantungan terlebih dahulu
            DB::table('setting_harga')->where('id_produk', $id)->delete();
            DB::table('bom_detail')->where('id_produk', $id)->delete();
            DB::table('produk')->where('id_produk', $id)->delete();

            return redirect()->back()->with('success', 'Produk berhasil dihapus selamanya.');
        });
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\HargaService;
use App\Services\AccountingService;
use App\Services\StokReversalService;
use Carbon\Carbon;

class PenjualanController extends Controller
{
    public function create()
{
    $kategori = DB::table('kategori')->get();
    
    // Ambil data produk lengkap dengan id_kategori dari bahan bakunya
    $produk = DB::table('produk')
    ->leftJoin('bom_detail', 'produk.id_produk', '=', 'bom_detail.id_produk')
    ->leftJoin('bahan_baku', 'bom_detail.id_bahan', '=', 'bahan_baku.id_bahan')
    ->select('produk.*', DB::raw('COALESCE(bahan_baku.id_kategori, 0) as id_kategori'))
    ->get()
    ->unique('id_produk');

    $golongan = DB::table('golongan_konsumen')->get();
    $konsumen = DB::table('konsumen')->get();
    $finishing = DB::table('finishing')->orderBy('nama_finishing')->get();
    $jasa = DB::table('jasa')
        ->leftJoin('satuan', 'jasa.id_satuan', '=', 'satuan.id_satuan')
        ->select('jasa.*', 'satuan.nama_satuan')
        ->orderBy('jasa.nama_jasa')
        ->get();

    // Generate No. Nota
    $date = date('Ymd');
    $lastNota = DB::table('transaksi')->whereDate('tgl_order', date('Y-m-d'))->count();
    $no_nota = "INV-" . $date . "-" . str_pad($lastNota + 1, 3, '0', STR_PAD_LEFT);

    return view('penjualan.create', compact('produk', 'golongan', 'konsumen', 'no_nota', 'kategori', 'finishing', 'jasa'));
}

public function getHarga($id_produk, $id_golongan, $qty) {
    $qty = floatval($qty);

    // Query ke tabel setting_harga sesuai dump SQL Anda
    $harga = DB::table('setting_harga')
        ->where('id_produk', $id_produk)
        ->where('id_golongan', $id_golongan)
        ->where('range_min', '<=', $qty) // Di database Anda namanya 'range_min'
        ->orderBy('range_min', 'desc')
        ->first();

    return response()->json([
        'harga_jual' => $harga ? $harga->harga_jual : 0
    ]);
}
    public function store(Request $request)
{
    if (!$request->items || count($request->items) == 0) {
        return response()->json(['status' => 'error', 'msg' => 'Keranjang kosong!'], 400);
    }

    if (!$request->id_konsumen) {
        return response()->json([
            'status' => 'error',
            'msg' => 'Konsumen wajib dipilih'
        ], 400);
    }

    if (!$request->id_golongan) {
        return response()->json([
            'status' => 'error',
            'msg' => 'Golongan konsumen wajib diisi'
        ], 400);
    }

    $tipeOrder = in_array($request->tipe_order, ['Reguler', 'PO']) ? $request->tipe_order : 'Reguler';
    $noPo = $tipeOrder === 'PO' ? trim((string) $request->no_po) : null;
    if ($tipeOrder === 'PO' && !$noPo) {
        return response()->json(['status' => 'error', 'msg' => 'No. PO wajib diisi untuk tipe order PO.'], 400);
    }

    // Tanggal order boleh diubah kasir (mis. mencatat PO yang sebenarnya
    // masuk kemarin) tapi tidak boleh di masa depan - itu bukan tanggal
    // order, itu penjadwalan, yang bukan konsep yang didukung di sini.
    $tglOrder = now();
    if ($request->filled('tgl_order')) {
        try {
            $tglInput = Carbon::parse($request->tgl_order);
            if (!$tglInput->isFuture()) {
                $tglOrder = $tglInput->setTimeFrom(now());
            }
        } catch (\Exception $e) {
            // format tanggal tidak valid - abaikan, pakai now() seperti biasa
        }
    }

    // Harga & subtotal TIDAK PERNAH dipercaya dari client (lihat catatan audit:
    // harga sebelumnya dikirim mentah-mentah dari browser tanpa verifikasi ulang,
    // sehingga siapa pun yang mengubah request bisa menetapkan harga sembarang).
    // Di sini setiap item dihitung ULANG dari data master (produk + setting_harga)
    // menggunakan hanya input mentah yang tidak bisa "dipalsukan" jadi lebih murah
    // (p, l, qty) - harga per unit dan subtotal akhir murni hasil hitungan server.
    $itemsTervalidasi = [];

    foreach ($request->items as $index => $item) {

        // Baris Jasa (bukan produk fisik) - tidak punya BOM/stok, harga
        // ditentukan manual oleh kasir (sama seperti biaya_desain), bukan
        // dari price list. Divalidasi terpisah lalu lanjut ke baris berikutnya.
        if (!empty($item['id_jasa'])) {
            $jasa = DB::table('jasa')->where('id_jasa', $item['id_jasa'])->first();
            if (!$jasa) {
                return response()->json(['status' => 'error', 'msg' => 'Jasa pada baris ' . ($index + 1) . ' tidak ditemukan.'], 422);
            }

            $qtyJasa = (float) ($item['qty_input'] ?? 0);
            $hargaJasa = max(0, (float) ($item['harga_satuan'] ?? 0));

            if ($qtyJasa <= 0) {
                return response()->json(['status' => 'error', 'msg' => 'Qty jasa pada baris ' . ($index + 1) . ' tidak valid.'], 422);
            }
            if ($hargaJasa <= 0) {
                return response()->json(['status' => 'error', 'msg' => 'Harga jasa pada baris ' . ($index + 1) . ' harus diisi.'], 422);
            }

            $itemsTervalidasi[] = [
                'is_jasa'    => true,
                'id_jasa'    => $jasa->id_jasa,
                'nama_file'  => $item['nama_file'] ?? $jasa->nama_jasa,
                'qty_input'  => $qtyJasa,
                'subtotal'   => $hargaJasa * $qtyJasa,
            ];
            continue;
        }

        $produk = DB::table('produk')->where('id_produk', $item['id_produk'] ?? null)->first();

        if (!$produk) {
            return response()->json(['status' => 'error', 'msg' => 'Produk pada baris ' . ($index + 1) . ' tidak ditemukan.'], 422);
        }

        $p = (float) ($item['p'] ?? 0);
        $l = (float) ($item['l'] ?? 0);
        $qtyInput = (float) ($item['qty_input'] ?? 0);

        if ($qtyInput <= 0) {
            return response()->json(['status' => 'error', 'msg' => 'Qty pada baris ' . ($index + 1) . ' tidak valid.'], 422);
        }

        // Rumus qty-untuk-harga HARUS sama persis dengan hitungSubtotal() di
        // penjualan/create.blade.php, supaya harga yang ditagih konsisten
        // dengan yang ditampilkan ke operator sebelum disimpan.
        $qtyHitung = $produk->metode_hitung === 'ukuran'
            ? ($p * $l / 10000) * $qtyInput
            : $qtyInput;

        if ($qtyHitung <= 0) {
            return response()->json(['status' => 'error', 'msg' => 'Ukuran/qty pada baris ' . ($index + 1) . ' tidak valid.'], 422);
        }

        // Harga promo (kalau sedang aktif) atau price list biasa - HargaService
        // adalah sumber yang sama persis dipakai layar POS untuk menampilkan
        // harga saat operator mengetik qty, supaya harga yang tersimpan tidak
        // pernah berbeda dari yang ditampilkan sebelum disimpan.
        $hargaSatuan = HargaService::getHargaEfektif($produk->id_produk, (int) $request->id_golongan, $qtyHitung)['harga_jual'];

        if ($hargaSatuan <= 0) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Harga untuk "' . $produk->nama_produk . '" belum diatur di Price List untuk golongan konsumen ini.',
            ], 422);
        }

        // biaya_desain & biaya_cetak memang input manual operator (tidak ada
        // tabel acuannya - biaya cetak riil tergantung mesin/ongkos produksi
        // saat itu), hanya dipastikan tidak negatif supaya tidak bisa dipakai
        // mengurangi total.
        $biayaDesain = max(0, (float) ($item['biaya_desain'] ?? 0));
        $biayaCetak = max(0, (float) ($item['biaya_cetak'] ?? 0));

        // id_finishing opsional (produk boleh tanpa finishing). Divalidasi ke
        // tabel finishing supaya id yang dipalsukan dari client tidak lolos
        // ke foreign key transaksi_detail.id_finishing. biaya_finishing input
        // manual operator sama seperti biaya_desain (harga_default di master
        // Finishing cuma starting point, bukan harga final).
        $idFinishing = !empty($item['id_finishing']) ? (int) $item['id_finishing'] : null;
        if ($idFinishing && !DB::table('finishing')->where('id_finishing', $idFinishing)->exists()) {
            return response()->json(['status' => 'error', 'msg' => 'Finishing pada baris ' . ($index + 1) . ' tidak ditemukan.'], 422);
        }
        $biayaFinishing = max(0, (float) ($item['biaya_finishing'] ?? 0));

        $statusUrgensi = in_array($item['status_urgensi'] ?? null, ['Normal', 'Urgent', 'Very Urgent'])
            ? $item['status_urgensi'] : 'Normal';
        $statusFinishing = in_array($item['status_finishing'] ?? null, ['Standar', 'Custom'])
            ? $item['status_finishing'] : 'Standar';

        // thumbnail sudah diunggah sebelumnya lewat endpoint upload-thumbnail
        // terpisah (lihat uploadThumbnail()) - baris ini cuma membawa path
        // string yang dikembalikan endpoint itu, bukan file binary (payload
        // /penjualan/store tetap JSON seperti sebelumnya, tidak diubah jadi
        // multipart). Path divalidasi berada di dalam folder upload yang
        // benar supaya client tidak bisa menyelipkan path sembarang.
        $thumbnail = null;
        if (!empty($item['thumbnail']) && str_starts_with($item['thumbnail'], 'order_thumbnails/')) {
            $thumbnail = $item['thumbnail'];
        }

        $catatanProduksi = trim((string) ($item['catatan_produksi'] ?? ''));

        $itemsTervalidasi[] = [
            'is_jasa' => false,
            'id_produk' => $produk->id_produk,
            'nama_file' => $item['nama_file'] ?? '-',
            'thumbnail' => $thumbnail,
            'catatan_produksi' => $catatanProduksi !== '' ? $catatanProduksi : null,
            'p' => $p,
            'l' => $l,
            'qty_input' => $qtyInput,
            'qty_hitung' => $qtyHitung,
            'id_finishing' => $idFinishing,
            'biaya_finishing' => $biayaFinishing,
            'biaya_cetak' => $biayaCetak,
            'biaya_desain' => $biayaDesain,
            'status_urgensi' => $statusUrgensi,
            'status_finishing' => $statusFinishing,
            'subtotal' => ($hargaSatuan * $qtyHitung) + $biayaDesain + $biayaFinishing + $biayaCetak,
        ];
    }

    DB::beginTransaction();

    try {

        $grand_total = collect($itemsTervalidasi)->sum('subtotal');

        $id_transaksi = DB::table('transaksi')->insertGetId([
            'nomor_nota'        => $request->no_nota,
            'tipe_order'        => $tipeOrder,
            'no_po'             => $noPo,
            'id_konsumen'       => $request->id_konsumen,
            'id_user_kasir'     => Auth::id() ?? 1,
            'tgl_order'         => $tglOrder,
            'total_bayar'       => $grand_total,
            'status_pembayaran' => 'Belum Bayar',
        ]);

        $totalHppMaterialSemua = 0;
        $totalJasaSemua = 0;
        $totalProdukSemua = 0;

        foreach ($itemsTervalidasi as $item) {

            // Baris Jasa: tidak ada BOM/stok/SPK produksi untuk dilacak -
            // langsung selesai (bukan 'Antre', supaya tidak nyasar ke
            // antrian SPK/Produksi yang isinya seharusnya cuma pekerjaan
            // cetak fisik).
            if (!empty($item['is_jasa'])) {
                DB::table('transaksi_detail')->insert([
                    'id_transaksi'        => $id_transaksi,
                    'id_produk'           => null,
                    'id_jasa'             => $item['id_jasa'],
                    'nama_file'           => $item['nama_file'],
                    'p_ukuran'            => 0,
                    'l_ukuran'            => 0,
                    'qty'                 => $item['qty_input'],
                    'subtotal'            => $item['subtotal'],
                    'status_spk'          => 'Selesai',
                    'harga_jual_snapshot' => $item['subtotal'],
                    'hpp_snapshot'        => 0,
                ]);

                $totalJasaSemua += $item['subtotal'];
                continue;
            }

            $totalProdukSemua += $item['subtotal'];

            $jumlah_order = $item['qty_input'];

            $id_detail = DB::table('transaksi_detail')->insertGetId([
                'id_transaksi'     => $id_transaksi,
                'id_produk'        => $item['id_produk'],
                'nama_file'        => $item['nama_file'],
                'thumbnail'        => $item['thumbnail'],
                'catatan_produksi' => $item['catatan_produksi'],
                'p_ukuran'         => $item['p'],
                'l_ukuran'         => $item['l'],
                'qty'              => $jumlah_order,
                'id_finishing'     => $item['id_finishing'],
                'biaya_finishing'  => $item['biaya_finishing'],
                'biaya_cetak'      => $item['biaya_cetak'],
                'biaya_desain'     => $item['biaya_desain'],
                'status_urgensi'   => $item['status_urgensi'],
                // status_urgent (kolom lama, tinyint) tetap diisi supaya
                // layar SPK yang sudah ada (toggleUrgent/bulkUrgent, sorting
                // urgent di spk/index) tetap jalan tanpa perubahan - Urgent
                // & Very Urgent dari kasir keduanya dianggap "urgent" di
                // sana, bedanya cuma tampil di status_urgensi.
                'status_urgent'    => $item['status_urgensi'] !== 'Normal' ? 1 : 0,
                'status_finishing' => $item['status_finishing'],
                'subtotal'         => $item['subtotal'],
                'status_spk'       => 'Antre',
                // harga_jual_snapshot dulu diisi = subtotal (harga jual final
                // baris ini). hpp_snapshot diisi setelah loop BOM di bawah,
                // karena baru diketahui setelah menjumlah biaya tiap bahan.
                'harga_jual_snapshot' => $item['subtotal'],
            ]);

            $boms = DB::table('bom_detail')
                ->where('id_produk', $item['id_produk'])
                ->get();

            $hppItemIni = 0;

            foreach ($boms as $bom) {

                $qty_pakai = 0;

                if ($bom->cara_hitung_pemakaian == '(P*L)/10000') {
                    $qty_pakai = $item['qty_hitung'] ?? 0;
                } else {
                    $qty_pakai = $jumlah_order;
                }

                if ($qty_pakai > 0) {
                    // Baca stok_sebelum dengan lock supaya aman dari order
                    // bersamaan, lalu catat ke kartu_stok (tipe OUT) - tanpa
                    // ini, konsumsi bahan baku per order tidak pernah
                    // tercatat di kartu_stok sama sekali (hanya kedatangan
                    // barang yang tercatat sebagai IN), sehingga riwayat
                    // stok tidak pernah bisa dipakai untuk menjelaskan
                    // kenapa stok berkurang atau sampai bisa negatif.
                    $bahan = DB::table('bahan_baku')
                        ->where('id_bahan', $bom->id_bahan)
                        ->lockForUpdate()
                        ->first();

                    // $qty_pakai dihitung dalam satuan HITUNG/pakai (mis. m2
                    // dari (P*L)/10000, atau lembar untuk metode 'Q'), sedangkan
                    // bahan_baku.stok & harga_rata_rata dilacak dalam satuan
                    // STOK/beli (mis. roll, rim) - dua satuan yang berbeda.
                    // nilai_konversi ("1 satuan stok = berapa satuan hitung",
                    // mis. 1 rim = 500 lembar) adalah faktor konversi di antara
                    // keduanya yang SUDAH ADA di skema tapi sebelumnya tidak
                    // pernah dipakai di sini - qty_pakai (lembar/m2) langsung
                    // dikurangkan dari stok (rim/roll) tanpa konversi, membuat
                    // stok terkuras ratusan kali lebih cepat dari seharusnya
                    // untuk bahan dengan nilai_konversi besar (mis. AC 210gr:
                    // order 1 lembar salah-salah mengurangi stok 1 RIM penuh),
                    // dan HPP snapshot ikut salah kali nilai_konversi juga.
                    $nilaiKonversi = (float) ($bahan->nilai_konversi ?? 1);
                    if ($nilaiKonversi <= 0) {
                        $nilaiKonversi = 1;
                    }
                    $qtyPakaiSatuanStok = $qty_pakai / $nilaiKonversi;

                    $stokSebelum = $bahan->stok ?? 0;
                    $stokSesudah = $stokSebelum - $qtyPakaiSatuanStok;

                    DB::table('bahan_baku')
                        ->where('id_bahan', $bom->id_bahan)
                        ->update(['stok' => $stokSesudah]);

                    DB::table('kartu_stok')->insert([
                        'id_bahan'      => $bom->id_bahan,
                        'tanggal'       => now(),
                        'tipe'          => 'OUT',
                        'qty'           => $qtyPakaiSatuanStok,
                        'stok_sebelum'  => $stokSebelum,
                        'stok_sesudah'  => $stokSesudah,
                        'referensi'     => $request->no_nota,
                        'keterangan'    => 'Pemakaian produksi order ' . $request->no_nota,
                        'created_by'    => Auth::id(),
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);

                    $hppItemIni += $qtyPakaiSatuanStok * (float) ($bahan->harga_rata_rata ?? 0);
                }
            }

            if ($hppItemIni > 0) {
                DB::table('transaksi_detail')->where('id_detail', $id_detail)->update([
                    'hpp_snapshot' => $hppItemIni,
                ]);
            }

            $totalHppMaterialSemua += $hppItemIni;
        }

        // Dipisah jadi 2 posting independen (bukan 1 baris gabungan) supaya
        // postPenjualan() yang sudah teruji untuk order produk-only tidak
        // perlu diubah sama sekali - order campuran produk+jasa cukup
        // menjalankan keduanya dalam transaction yang sama.
        if ($totalProdukSemua > 0) {
            AccountingService::postPenjualan($request->no_nota, $totalProdukSemua, $totalHppMaterialSemua);
        }
        if ($totalJasaSemua > 0) {
            AccountingService::postPenjualanJasa($request->no_nota, $totalJasaSemua);
        }

        // Pakai saldo deposit konsumen (kalau ada) LANGSUNG saat nota ini
        // dibuat - sebelumnya deposit cuma bisa dipakai lewat pilihan manual
        // di halaman Bayar Kasir (kasir harus pilih "Deposit" di dropdown +
        // ketik nominal sendiri), jadi konsumen dengan saldo tetap muncul
        // "Belum Bayar" sampai ada yang memprosesnya secara terpisah.
        // Rumus & ledger PERSIS sama dengan KasirController::prosesBayar()
        // untuk metode Deposit (insert konsumen_deposit tipe Pemakaian +
        // pembayaran_riwayat + posting akuntansi yang sama) supaya tidak ada
        // dua cara berbeda mencatat pemakaian deposit.
        //
        // SENGAJA hanya berlaku untuk nota BARU ini - tidak menyapu mundur
        // ke nota lain milik konsumen yang sama yang mungkin masih Belum
        // Bayar/DP (itu tetap harus diproses manual seperti biasa di Kasir).
        $depositTerpakai = null;
        if ($grand_total > 0) {
            $saldoDeposit = KonsumenDepositController::saldoUntuk((int) $request->id_konsumen);

            if ($saldoDeposit > 0) {
                $jumlahPakai = min($saldoDeposit, $grand_total);

                DB::table('konsumen_deposit')->insert([
                    'id_konsumen'  => $request->id_konsumen,
                    'tipe'         => 'Pemakaian',
                    'nominal'      => $jumlahPakai,
                    'metode_bayar' => 'Deposit',
                    'keterangan'   => "Pembayaran otomatis nota {$request->no_nota} saat order dibuat",
                    'tgl_deposit'  => now()->toDateString(),
                    'id_transaksi' => $id_transaksi,
                    'id_user'      => Auth::id(),
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);

                DB::table('pembayaran_riwayat')->insert([
                    'id_transaksi' => $id_transaksi,
                    'nominal'      => $jumlahPakai,
                    'metode'       => 'Deposit',
                    'id_bank'      => null,
                    'tgl_bayar'    => now(),
                    'keterangan'   => 'Otomatis dipotong dari saldo deposit saat order dibuat',
                ]);

                AccountingService::postPembayaranDeposit($request->no_nota, $jumlahPakai);

                $statusBaru = $jumlahPakai >= $grand_total ? 'Lunas' : 'DP';

                DB::table('transaksi')->where('id_transaksi', $id_transaksi)->update([
                    'status_pembayaran' => $statusBaru,
                    'metode_pembayaran' => 'Deposit',
                ]);

                $depositTerpakai = [
                    'dipakai'    => $jumlahPakai,
                    'sisa_saldo' => $saldoDeposit - $jumlahPakai,
                    'status'     => $statusBaru,
                ];
            }
        }

        DB::commit();

        return response()->json([
            'status' => 'success',
            'msg' => 'Transaksi Berhasil',
            'id_transaksi' => $id_transaksi,
            'deposit_terpakai' => $depositTerpakai,
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'status' => 'error',
            'msg' => $e->getMessage()
        ], 500);
    }
}

/**
 * Upload thumbnail gambar order (dipanggil terpisah dari /penjualan/store
 * begitu operator memilih file - payload store tetap JSON seperti semula,
 * bukan multipart, jadi cuma path hasil upload ini yang ikut terbawa di
 * item keranjang). Disimpan di storage/app/public/order_thumbnails, publik
 * lewat symlink public/storage.
 */
public function uploadThumbnail(Request $request)
{
    $request->validate([
        'thumbnail' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
    ]);

    $path = $request->file('thumbnail')->store('order_thumbnails', 'public');

    return response()->json([
        'status' => 'success',
        'path' => $path,
        'url' => asset('storage/' . $path),
    ]);
}

/**
 * Riwayat Nota - daftar transaksi penjualan dengan filter range tanggal.
 * Default: hanya menampilkan transaksi HARI INI jika tidak ada filter
 * tanggal yang dikirim. Murni query baca, tidak mengubah data apa pun.
 */
public function riwayat(Request $request)
{
    // Default HARI INI hanya masuk akal untuk lihat transaksi umum tanpa
    // filter - begitu ada id_konsumen (dipakai link "Riwayat Transaksi"
    // dari Master Konsumen), default HARI INI justru menyembunyikan
    // hampir seluruh histori pelanggan itu. Default melebar ke semua
    // tanggal kalau id_konsumen diisi TANPA rentang tanggal eksplisit.
    $filterKonsumen = $request->filled('id_konsumen');
    $from = $request->filled('from') ? $request->from : ($filterKonsumen ? null : date('Y-m-d'));
    $to   = $request->filled('to') ? $request->to : ($filterKonsumen ? null : date('Y-m-d'));

    $konsumenTerpilih = null;
    if ($filterKonsumen) {
        $konsumenTerpilih = DB::table('konsumen')->where('id_konsumen', $request->id_konsumen)->first();
    }

    $transaksis = DB::table('transaksi')
        ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
        ->leftJoin('golongan_konsumen', 'konsumen.id_golongan', '=', 'golongan_konsumen.id_golongan')
        ->leftJoin('users', 'transaksi.id_user_kasir', '=', 'users.id_user')
        ->leftJoin('karyawan', 'users.id_karyawan', '=', 'karyawan.id_karyawan')
        ->when($from, fn ($q) => $q->whereDate('tgl_order', '>=', $from))
        ->when($to, fn ($q) => $q->whereDate('tgl_order', '<=', $to))
        ->when($filterKonsumen, fn ($q) => $q->where('transaksi.id_konsumen', $request->id_konsumen))
        ->select(
            'transaksi.*',
            'konsumen.nama_konsumen',
            'golongan_konsumen.nama_golongan',
            'karyawan.nama_karyawan as nama_kasir'
        )
        ->orderByDesc('tgl_order')
        ->paginate(15)
        ->withQueryString();

    return view('penjualan.riwayat', compact('transaksis', 'from', 'to', 'konsumenTerpilih'));
}

/**
 * Lihat Data - detail satu transaksi (read-only). Transaksi yang sudah
 * tersimpan SENGAJA tidak punya alur edit: stok bahan baku sudah dikurangi
 * dan jurnal akuntansi sudah diposting saat store(), jadi "edit" yang benar
 * butuh alur pembalikan stok + jurnal dulu (retur/void) - bukan sekadar
 * update baris seperti form biasa. Belum ada alur retur di aplikasi ini,
 * jadi daripada membangun edit yang setengah jadi dan berisiko merusak
 * integritas stok/jurnal, halaman ini murni tampilan.
 */
public function show($id)
{
    $transaksi = DB::table('transaksi')
        ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
        ->leftJoin('golongan_konsumen', 'konsumen.id_golongan', '=', 'golongan_konsumen.id_golongan')
        ->leftJoin('users', 'transaksi.id_user_kasir', '=', 'users.id_user')
        ->leftJoin('karyawan', 'users.id_karyawan', '=', 'karyawan.id_karyawan')
        ->leftJoin('users as pembatal', 'transaksi.id_user_pembatal_nota', '=', 'pembatal.id_user')
        ->leftJoin('karyawan as karyawan_pembatal', 'pembatal.id_karyawan', '=', 'karyawan_pembatal.id_karyawan')
        ->where('transaksi.id_transaksi', $id)
        ->select(
            'transaksi.*',
            'konsumen.nama_konsumen',
            'konsumen.telepon',
            'golongan_konsumen.nama_golongan',
            'karyawan.nama_karyawan as nama_kasir',
            DB::raw('COALESCE(karyawan_pembatal.nama_karyawan, pembatal.username) as nama_pembatal_nota')
        )
        ->first();

    if (!$transaksi) {
        abort(404);
    }

    $items = DB::table('transaksi_detail')
        ->leftJoin('produk', 'transaksi_detail.id_produk', '=', 'produk.id_produk')
        ->leftJoin('jasa', 'transaksi_detail.id_jasa', '=', 'jasa.id_jasa')
        ->leftJoin('finishing', 'transaksi_detail.id_finishing', '=', 'finishing.id_finishing')
        ->where('id_transaksi', $id)
        ->select(
            'transaksi_detail.*',
            'produk.nama_produk',
            'jasa.nama_jasa',
            'finishing.nama_finishing'
        )
        ->get();

    return view('penjualan.show', compact('transaksi', 'items'));
}

/**
 * Membatalkan SELURUH nota (bukan per-item SPK seperti
 * ProduksiController::cancel) - untuk kasus nota salah input (salah
 * konsumen, duplikat, dsb). Ini SOFT-CANCEL: nota TIDAK dihapus dari
 * database (nomor_nota, riwayat pembayaran, kartu stok yang sudah
 * tertaut tetap utuh untuk audit) - hanya ditandai status_nota =
 * 'Dibatalkan' dan seluruh item SPK di dalamnya ikut dibatalkan supaya
 * tidak ada yang lanjut mengerjakan nota yang sudah tidak valid.
 *
 * Diblokir kalau ada item yang sudah 'Selesai' atau nota sudah
 * 'Sudah Diambil' (collecting) - barang fisik kemungkinan sudah di
 * tangan konsumen, pembatalan di titik itu harus lewat proses retur
 * manual, bukan tombol ini.
 */
public function batalkanNota(Request $request, $id)
{
    $request->validate([
        'alasan' => 'required|string|max:1000',
    ]);

    $transaksi = DB::table('transaksi')->where('id_transaksi', $id)->first();

    if (!$transaksi) {
        return back()->with('error', 'Nota tidak ditemukan.');
    }

    if ($transaksi->status_nota === 'Dibatalkan') {
        return back()->with('error', 'Nota ini sudah dibatalkan sebelumnya.');
    }

    if ($transaksi->status_collecting === 'Sudah Diambil') {
        return back()->with('error', 'Nota ini tidak bisa dibatalkan - barang sudah diambil konsumen (Collecting). Gunakan proses retur manual kalau memang diperlukan.');
    }

    $items = DB::table('transaksi_detail')->where('id_transaksi', $id)->get();

    $sudahSelesai = $items->firstWhere('status_spk', 'Selesai');
    if ($sudahSelesai) {
        return back()->with('error', 'Nota ini tidak bisa dibatalkan - ada item ("' . $sudahSelesai->nama_file . '") yang sudah Selesai diproduksi. Gunakan proses retur manual kalau memang diperlukan.');
    }

    $kembalikanStok = $request->boolean('kembalikan_stok');
    $ringkasanStok = [];

    DB::transaction(function () use ($request, $id, $items, $kembalikanStok, &$ringkasanStok) {
        foreach ($items as $item) {
            if ($item->status_spk === 'Batal') {
                continue;
            }

            DB::table('transaksi_detail')->where('id_detail', $item->id_detail)->update([
                'status_spk' => 'Batal',
            ]);

            if ($kembalikanStok) {
                $ringkasanStok = array_merge($ringkasanStok, StokReversalService::kembalikan(
                    $item,
                    'BATAL-NOTA-' . $id,
                    'Pembatalan nota #' . $id . ' - stok dikembalikan'
                ));
            }
        }

        DB::table('transaksi')->where('id_transaksi', $id)->update([
            'status_nota' => 'Dibatalkan',
            'alasan_batal_nota' => $request->input('alasan'),
            'tgl_dibatalkan_nota' => now(),
            'id_user_pembatal_nota' => Auth::id(),
        ]);
    });

    $pesan = 'Nota dibatalkan. Seluruh item SPK di dalamnya ikut ditandai Batal.';
    if ($kembalikanStok) {
        $pesan .= !empty($ringkasanStok)
            ? ' Stok dikembalikan: ' . implode(', ', $ringkasanStok) . '.'
            : ' Tidak ada bahan BOM untuk dikembalikan pada nota ini.';
    }

    return redirect()->route('penjualan.show', $id)->with('success', $pesan);
}
}
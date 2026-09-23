<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\HargaService;
use App\Services\AccountingService;
use App\Services\StokReversalService;
use App\Services\DocumentNumberService;
use Carbon\Carbon;

class PenjualanController extends Controller
{
    public function create()
{
    $kategori = DB::table('kategori')->get();
    
    // Ambil data produk lengkap dengan id_kategori dari bahan bakunya - hanya
    // yang berstatus 'aktif' (produk dinonaktifkan sengaja disembunyikan dari
    // pilihan order baru, sama seperti bahan_baku nonaktif tidak muncul di
    // BOM/Kedatangan Bahan, lihat ProdukController::toggleStatus).
    $produk = DB::table('produk')
    ->leftJoin('bom_detail', 'produk.id_produk', '=', 'bom_detail.id_produk')
    ->leftJoin('bahan_baku', 'bom_detail.id_bahan', '=', 'bahan_baku.id_bahan')
    ->where('produk.status', 'aktif')
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

    // Generate No. Nota - prefix bisa diatur di Pengaturan Aplikasi (dulu
    // hardcode "INV").
    $date = date('Ymd');
    $lastNota = DB::table('transaksi')->whereDate('tgl_order', date('Y-m-d'))->count();
    $prefixNota = \App\Models\AppSetting::current()->nomor_nota_prefix ?: 'INV';
    $no_nota = $prefixNota . "-" . $date . "-" . str_pad($lastNota + 1, 3, '0', STR_PAD_LEFT);

    // Order dari penawaran: tampilkan rincian penawaran sebagai acuan di
    // layar Input Order (item tetap diinput operator seperti biasa).
    $quotationRef = null;
    if (request()->filled('quotation')) {
        $q = \App\Models\Quotation::with('items')->find(request('quotation'));
        if ($q && in_array($q->status, ['Terkirim', 'Disetujui'], true)) {
            $quotationRef = $q;
        }
    }

    return view('penjualan.create', compact('produk', 'golongan', 'konsumen', 'no_nota', 'kategori', 'finishing', 'jasa', 'quotationRef'));
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

    // Order baru (dan otomatis SPK yang ikut terbentuk dari transaksi_detail-
    // nya - lihat SpkController::index, SPK dibaca langsung dari sini tanpa
    // tabel terpisah) HANYA boleh dibuat saat kasir sudah resmi "buka toko"
    // hari ini (kas_harian berstatus open). Sebelum ini tidak ada guard sama
    // sekali di sini - order/SPK bisa terbentuk kapan pun tanpa sesi kas yang
    // menaunginya. Guard yang sama persis dengan KasirController::prosesBayar().
    $kasHariIni = DB::table('kas_harian')
        ->where('tgl_kas', date('Y-m-d'))
        ->where('status_lock', 'open')
        ->first();

    if (!$kasHariIni) {
        return response()->json(['status' => 'error', 'msg' => 'Kasir belum dibuka! Buka kas terlebih dahulu sebelum membuat order baru.'], 400);
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
        // harga saat operator mengetik qty. Price List di sini TETAP wajib
        // ada (guard rail supaya tidak ada produk terjual tanpa harga pernah
        // diatur sama sekali) - tapi angkanya sendiri TIDAK LAGI otomatis
        // ditambahkan ke subtotal (lihat catatan biaya_cetak di bawah),
        // murni jadi SARAN nilai default untuk Biaya Cetak yang dikirim
        // client dari hitungSubtotal() di penjualan/create.blade.php.
        $hargaSatuan = HargaService::getHargaEfektif($produk->id_produk, (int) $request->id_golongan, $qtyHitung)['harga_jual'];

        if ($hargaSatuan <= 0) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Harga untuk "' . $produk->nama_produk . '" belum diatur di Price List untuk golongan konsumen ini.',
            ], 422);
        }

        // Biaya Cetak SEKARANG jadi komponen UTAMA subtotal (menggantikan
        // hargaSatuan*qtyHitung, bukan lagi ditambahkan di atasnya) - selaras
        // dengan biaya_desain/biaya_finishing yang memang sudah lebih dulu
        // murni input manual operator (tidak ada tabel acuan yang mengikat,
        // biaya cetak riil tergantung mesin/ongkos produksi saat itu).
        // Defaultnya dikirim client = hargaSatuan*qtyHitung (Price List),
        // tapi operator bebas menimpa - sama persis tingkat kepercayaan yang
        // sudah berlaku untuk desain/finishing, cuma dipastikan tidak
        // negatif supaya tidak bisa dipakai mengurangi total.
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
            'subtotal' => $biayaCetak + $biayaDesain + $biayaFinishing,
        ];
    }

    DB::beginTransaction();

    try {

        // Nomor nota di-generate ULANG di sini (atomik lewat
        // DocumentNumberService), bukan dipercaya dari $request->no_nota.
        // Sebelumnya nomor cuma dihitung sekali saat form GET dimuat
        // (create(), pola COUNT(*)+1) lalu dikirim balik client saat
        // submit - dua order dibuat nyaris bersamaan bisa melihat "nomor
        // berikutnya" yang sama sebelum salah satu commit (race
        // condition nyata, bukan teoretis - beda sesi kasir/tab yang
        // buka form di waktu berdekatan). $no_nota di create() sekarang
        // MURNI preview UX, angka final selalu ditentukan di sini.
        $prefixNota = \App\Models\AppSetting::current()->nomor_nota_prefix ?: 'INV';
        $noNota = DocumentNumberService::generate($prefixNota);

        $grand_total = collect($itemsTervalidasi)->sum('subtotal');

        $id_transaksi = DB::table('transaksi')->insertGetId([
            'nomor_nota'        => $noNota,
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

                $qty_pakai = \App\Services\BomCalculationService::hitungQtyPakai(
                    $bom->cara_hitung_pemakaian,
                    (float) $item['p'],
                    (float) $item['l'],
                    $jumlah_order
                );

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
                        'referensi'     => $noNota,
                        'keterangan'    => 'Pemakaian produksi order ' . $noNota,
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
            AccountingService::postPenjualan($noNota, $totalProdukSemua, $totalHppMaterialSemua);
        }
        if ($totalJasaSemua > 0) {
            AccountingService::postPenjualanJasa($noNota, $totalJasaSemua);
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
        // Deposit yang otomatis dipakai di sini TETAP "pembayaran" (insert
        // pembayaran_riwayat + posting akuntansi) - sesi kas aktif bersifat
        // eksklusif milik SATU orang (lihat catatan di
        // KasirController::storeKas()/prosesBayar()), jadi user yang BUKAN
        // pemilik sesi kas yang sedang aktif tidak boleh diam-diam mencatat
        // pembayaran lewat jalur ini juga - order tetap boleh dibuat
        // (Belum Bayar), cuma auto-pakai depositnya yang dilewati.
        $bolehBayarDenganKasIni = $kasHariIni && (int) $kasHariIni->id_user === (int) Auth::id();

        $depositTerpakai = null;
        if ($grand_total > 0 && $bolehBayarDenganKasIni) {
            // Lock baris konsumen sebagai mutex sebelum hitung saldo - sama
            // seperti catatan di KasirController::prosesBayar() metode Deposit,
            // supaya order lain untuk konsumen yang sama yang dibuat nyaris
            // bersamaan tidak bisa sama-sama membaca saldo lama yang sama dan
            // menyebabkan deposit terpakai melebihi yang disetor.
            DB::table('konsumen')->where('id_konsumen', $request->id_konsumen)->lockForUpdate()->first();

            $saldoDeposit = KonsumenDepositController::saldoUntuk((int) $request->id_konsumen);

            if ($saldoDeposit > 0) {
                $jumlahPakai = min($saldoDeposit, $grand_total);

                DB::table('konsumen_deposit')->insert([
                    'id_konsumen'  => $request->id_konsumen,
                    'tipe'         => 'Pemakaian',
                    'nominal'      => $jumlahPakai,
                    'metode_bayar' => 'Deposit',
                    'keterangan'   => "Pembayaran otomatis nota {$noNota} saat order dibuat",
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

                AccountingService::postPembayaranDeposit($noNota, $jumlahPakai);

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

        // Order dibuat dari penawaran (Quotation): tandai penawaran jadi
        // "Menjadi Order". Setelah commit & dibungkus try/catch - order sudah
        // sah dan tidak boleh gagal hanya karena penandaan penawaran.
        if ($request->filled('quotation_id')) {
            try {
                \App\Services\QuotationService::tandaiMenjadiOrder((int) $request->quotation_id, (int) $id_transaksi);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response()->json([
            'status' => 'success',
            'msg' => 'Transaksi Berhasil',
            'id_transaksi' => $id_transaksi,
            'nomor_nota' => $noNota,
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

    // Permintaan edit TERBARU per nota (kalau ada) - dipakai riwayat.blade.php
    // untuk menampilkan tombol yang sesuai (Ajukan Edit / Menunggu / Disetujui-
    // proses sekarang / Ditolak). MAX(id_request) per id_transaksi lalu di-join
    // balik ke tabelnya - pola "ambil baris terbaru per grup" standar untuk
    // Query Builder biasa (tanpa window function).
    $latestEditReq = DB::table('nota_edit_request')
        ->select('id_transaksi', DB::raw('MAX(id_request) as max_id'))
        ->groupBy('id_transaksi');

    $transaksis = DB::table('transaksi')
        ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
        ->leftJoin('golongan_konsumen', 'konsumen.id_golongan', '=', 'golongan_konsumen.id_golongan')
        ->leftJoin('users', 'transaksi.id_user_kasir', '=', 'users.id_user')
        ->leftJoin('karyawan', 'users.id_karyawan', '=', 'karyawan.id_karyawan')
        ->leftJoinSub($latestEditReq, 'latest_req', fn ($join) => $join->on('transaksi.id_transaksi', '=', 'latest_req.id_transaksi'))
        ->leftJoin('nota_edit_request', 'nota_edit_request.id_request', '=', 'latest_req.max_id')
        ->when($from, fn ($q) => $q->whereDate('tgl_order', '>=', $from))
        ->when($to, fn ($q) => $q->whereDate('tgl_order', '<=', $to))
        ->when($filterKonsumen, fn ($q) => $q->where('transaksi.id_konsumen', $request->id_konsumen))
        ->select(
            'transaksi.*',
            'konsumen.nama_konsumen',
            'golongan_konsumen.nama_golongan',
            'karyawan.nama_karyawan as nama_kasir',
            'nota_edit_request.id_request as edit_request_id',
            'nota_edit_request.status as edit_request_status',
            'nota_edit_request.catatan_approver as edit_request_catatan'
        )
        ->orderByDesc('tgl_order')
        ->paginate(20)
        ->withQueryString();

    $isAdmin = optional(optional($request->user())->role)->nama_role === 'Administrator';
    $pendingEditCount = $isAdmin ? DB::table('nota_edit_request')->where('status', 'Menunggu')->count() : 0;

    return view('penjualan.riwayat', compact('transaksis', 'from', 'to', 'konsumenTerpilih', 'isAdmin', 'pendingEditCount'));
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
/**
 * Sesuai roadmap Approval Workflow ("Cancel Invoice" - jangan pakai
 * delete/perubahan langsung untuk transaksi keuangan): method ini
 * SEKARANG cuma MENGAJUKAN pembatalan, tidak lagi mengeksekusinya
 * langsung. Validasi cepat (nota valid, belum dibatalkan, dst) tetap di
 * sini supaya pengajuan yang jelas tidak valid ditolak seketika - tidak
 * perlu membebani approver dengan pengajuan yang pasti gagal. Eksekusi
 * SUNGGUHAN (lihat eksekusiPembatalanNota() di bawah) baru terjadi
 * setelah Administrator menyetujui lewat ApprovalController.
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

    // Cegah pengajuan dobel - kalau sudah ada pengajuan Menunggu untuk
    // nota yang sama, tidak perlu diajukan ulang.
    $sudahAda = \App\Models\ApprovalRequest::where('tipe_aksi', 'batalkan_nota')
        ->where('referensi_type', 'transaksi')
        ->where('referensi_id', $id)
        ->where('status', 'Menunggu')
        ->exists();
    if ($sudahAda) {
        return back()->with('error', 'Sudah ada pengajuan pembatalan untuk nota ini yang masih menunggu persetujuan.');
    }

    \App\Services\ApprovalService::ajukan(
        tipeAksi: 'batalkan_nota',
        referensiType: 'transaksi',
        referensiId: (int) $id,
        payload: [
            'alasan' => $request->input('alasan'),
            'kembalikan_stok' => $request->boolean('kembalikan_stok'),
        ],
        ringkasan: "Batalkan nota {$transaksi->nomor_nota}",
        alasanPengajuan: $request->input('alasan'),
    );

    return redirect()->route('penjualan.show', $id)
        ->with('success', 'Pengajuan pembatalan nota terkirim, menunggu persetujuan Administrator.');
}

/**
 * Eksekusi SUNGGUHAN pembatalan nota - dipanggil ApprovalController
 * setelah Administrator menyetujui pengajuan dari batalkanNota() di
 * atas. Isinya PERSIS logic yang dulu langsung jalan di batalkanNota().
 *
 * @return array<int, string> Ringkasan stok yang dikembalikan (kalau ada)
 */
public function eksekusiPembatalanNota(int $id, array $payload): array
{
    $items = DB::table('transaksi_detail')->where('id_transaksi', $id)->get();
    $kembalikanStok = (bool) ($payload['kembalikan_stok'] ?? false);
    $ringkasanStok = [];

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
        'alasan_batal_nota' => $payload['alasan'] ?? null,
        'tgl_dibatalkan_nota' => now(),
        'id_user_pembatal_nota' => Auth::id(),
    ]);

    return $ringkasanStok;
}

/**
 * ALUR EDIT NOTA (lihat catatan di show() kenapa tidak ada edit langsung).
 * Kasir mengajukan permintaan lebih dulu - TIDAK langsung membuka akses
 * edit. Satu nota hanya boleh punya satu permintaan yang masih berjalan
 * (Menunggu/Disetujui-belum-dipakai) supaya tidak ada spam permintaan
 * ganda untuk nota yang sama.
 */
public function requestEditNota(Request $request, $id)
{
    $request->validate(['alasan' => 'required|string|max:1000']);

    $transaksi = DB::table('transaksi')->where('id_transaksi', $id)->first();
    if (!$transaksi) {
        return back()->with('error', 'Nota tidak ditemukan.');
    }
    if ($transaksi->status_nota === 'Dibatalkan') {
        return back()->with('error', 'Nota ini sudah dibatalkan, tidak perlu diedit.');
    }

    $adaPermintaanBerjalan = DB::table('nota_edit_request')
        ->where('id_transaksi', $id)
        ->whereIn('status', ['Menunggu', 'Disetujui'])
        ->exists();
    if ($adaPermintaanBerjalan) {
        return back()->with('error', 'Nota ini sudah punya permintaan edit yang masih berjalan (menunggu/sudah disetujui).');
    }

    DB::table('nota_edit_request')->insert([
        'id_transaksi' => $id,
        'id_user_requester' => Auth::id(),
        'alasan' => $request->input('alasan'),
        'status' => 'Menunggu',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return back()->with('success', 'Permintaan edit nota dikirim, menunggu persetujuan Administrator.');
}

/**
 * Semua route /penjualan/* (termasuk tiga method approval di bawah ini)
 * berbagi satu aturan menu_access ('penjualan' => [3]) yang sama dengan
 * create/store/riwayat - jadi kasir biasa pun lolos middleware CheckMenuAccess
 * untuk URL ini. Approval edit nota HARUS Administrator-only (itu intinya
 * fitur ini - anti "orang sembarangan tangan" mengedit nota), jadi dicek
 * eksplisit di sini, sama seperti SpkController::index() mendeteksi admin.
 */
private function pastikanAdministrator(Request $request): ?\Illuminate\Http\RedirectResponse
{
    $isAdmin = optional(optional($request->user())->role)->nama_role === 'Administrator';
    if (!$isAdmin) {
        return redirect()->route('penjualan.riwayat')->with('error', 'Halaman ini khusus Administrator.');
    }
    return null;
}

/**
 * Daftar permintaan edit nota - HANYA untuk Administrator.
 */
public function editApprovalIndex(Request $request)
{
    if ($redirect = $this->pastikanAdministrator($request)) {
        return $redirect;
    }

    $query = DB::table('nota_edit_request')
        ->join('transaksi', 'nota_edit_request.id_transaksi', '=', 'transaksi.id_transaksi')
        ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
        ->join('users as requester', 'nota_edit_request.id_user_requester', '=', 'requester.id_user')
        ->leftJoin('karyawan as karyawan_requester', 'requester.id_karyawan', '=', 'karyawan_requester.id_karyawan')
        ->leftJoin('users as approver', 'nota_edit_request.id_user_approver', '=', 'approver.id_user')
        ->leftJoin('karyawan as karyawan_approver', 'approver.id_karyawan', '=', 'karyawan_approver.id_karyawan')
        ->select(
            'nota_edit_request.*',
            'transaksi.nomor_nota',
            'transaksi.total_bayar',
            'konsumen.nama_konsumen',
            DB::raw('COALESCE(karyawan_requester.nama_karyawan, requester.username) as nama_requester'),
            DB::raw('COALESCE(karyawan_approver.nama_karyawan, approver.username) as nama_approver')
        );

    if ($request->filled('status')) {
        $query->where('nota_edit_request.status', $request->status);
    } else {
        $query->where('nota_edit_request.status', 'Menunggu');
    }

    $requests = $query->orderByDesc('nota_edit_request.created_at')->paginate(20)->withQueryString();

    return view('penjualan.edit-approval', compact('requests'));
}

public function approveEditNota(Request $request, $id)
{
    if ($redirect = $this->pastikanAdministrator($request)) {
        return $redirect;
    }

    $req = DB::table('nota_edit_request')->where('id_request', $id)->first();
    if (!$req) {
        return back()->with('error', 'Permintaan tidak ditemukan.');
    }
    if ($req->status !== 'Menunggu') {
        return back()->with('error', 'Permintaan ini sudah diproses sebelumnya (status: ' . $req->status . ').');
    }

    DB::table('nota_edit_request')->where('id_request', $id)->update([
        'status' => 'Disetujui',
        'id_user_approver' => Auth::id(),
        'catatan_approver' => $request->input('catatan'),
        'tgl_keputusan' => now(),
        'updated_at' => now(),
    ]);

    return back()->with('success', 'Permintaan edit disetujui. Kasir sekarang bisa memproses koreksi nota ini.');
}

public function rejectEditNota(Request $request, $id)
{
    if ($redirect = $this->pastikanAdministrator($request)) {
        return $redirect;
    }

    $request->validate(['catatan' => 'required|string|max:1000']);

    $req = DB::table('nota_edit_request')->where('id_request', $id)->first();
    if (!$req) {
        return back()->with('error', 'Permintaan tidak ditemukan.');
    }
    if ($req->status !== 'Menunggu') {
        return back()->with('error', 'Permintaan ini sudah diproses sebelumnya (status: ' . $req->status . ').');
    }

    DB::table('nota_edit_request')->where('id_request', $id)->update([
        'status' => 'Ditolak',
        'id_user_approver' => Auth::id(),
        'catatan_approver' => $request->input('catatan'),
        'tgl_keputusan' => now(),
        'updated_at' => now(),
    ]);

    return back()->with('success', 'Permintaan edit ditolak.');
}

/**
 * Eksekusi koreksi setelah permintaan Disetujui - dipanggil kasir dari
 * Riwayat Nota. TIDAK menulis ulang logic pembalikan stok/jurnal dari nol:
 * memakai ulang persis StokReversalService::kembalikan() (sama seperti
 * batalkanNota()) + AccountingService::reversePenjualan() (entri kontra,
 * tidak pernah menghapus/mengubah baris jurnal asli). Nota lama ditandai
 * Dibatalkan (riwayat & jejak audit tetap utuh, bisa dilihat lewat
 * alasan_batal_nota yang mengarah ke permintaan edit ini) - nota
 * PENGGANTI dibuat manual oleh kasir lewat Input Order seperti biasa
 * (bukan auto-generate) supaya harga/stok nota baru selalu dihitung ulang
 * dari data TERKINI (Price List, stok bahan saat ini), bukan menyalin
 * mentah angka lama yang mungkin sudah usang atau memang salah.
 */
public function applyApprovedEdit($id)
{
    $req = DB::table('nota_edit_request')->where('id_request', $id)->first();
    if (!$req) {
        return back()->with('error', 'Permintaan edit tidak ditemukan.');
    }
    if ($req->status !== 'Disetujui') {
        return back()->with('error', 'Permintaan ini belum disetujui Administrator (status: ' . $req->status . ').');
    }

    $transaksi = DB::table('transaksi')->where('id_transaksi', $req->id_transaksi)->first();
    if (!$transaksi) {
        return back()->with('error', 'Nota tidak ditemukan.');
    }
    if ($transaksi->status_nota === 'Dibatalkan') {
        DB::table('nota_edit_request')->where('id_request', $id)->update(['status' => 'Selesai', 'tgl_selesai' => now()]);
        return redirect()->route('penjualan.riwayat')->with('error', 'Nota ini sudah dibatalkan/dikoreksi sebelumnya.');
    }

    $items = DB::table('transaksi_detail')->where('id_transaksi', $transaksi->id_transaksi)->get();
    $sudahSelesai = $items->firstWhere('status_spk', 'Selesai');
    if ($sudahSelesai) {
        return back()->with('error', 'Nota ini tidak bisa dikoreksi - ada item ("' . $sudahSelesai->nama_file . '") yang sudah Selesai diproduksi.');
    }
    if ($transaksi->status_collecting === 'Sudah Diambil') {
        return back()->with('error', 'Nota ini tidak bisa dikoreksi - barang sudah diambil konsumen.');
    }

    DB::transaction(function () use ($items, $transaksi, $req) {
        foreach ($items as $item) {
            if ($item->status_spk === 'Batal') {
                continue;
            }
            DB::table('transaksi_detail')->where('id_detail', $item->id_detail)->update(['status_spk' => 'Batal']);
            StokReversalService::kembalikan(
                $item,
                'EDIT-NOTA-' . $transaksi->id_transaksi,
                'Koreksi nota #' . $transaksi->nomor_nota . ' (permintaan edit #' . $req->id_request . ') - stok dikembalikan'
            );
        }

        AccountingService::reversePenjualan($transaksi->nomor_nota);

        DB::table('transaksi')->where('id_transaksi', $transaksi->id_transaksi)->update([
            'status_nota' => 'Dibatalkan',
            'alasan_batal_nota' => 'Dikoreksi via alur Edit Nota (disetujui Administrator) - alasan kasir: ' . $req->alasan,
            'tgl_dibatalkan_nota' => now(),
            'id_user_pembatal_nota' => Auth::id(),
        ]);

        DB::table('nota_edit_request')->where('id_request', $req->id_request)->update([
            'status' => 'Selesai',
            'tgl_selesai' => now(),
            'updated_at' => now(),
        ]);
    });

    return redirect()->route('penjualan.create', ['id_konsumen' => $transaksi->id_konsumen])
        ->with('success', 'Nota lama sudah dibatalkan, stok & jurnal sudah dikembalikan. Silakan buat nota pengganti dengan data yang benar (nota lama tetap bisa dilihat di Riwayat untuk referensi).');
}
}
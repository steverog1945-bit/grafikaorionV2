<?php

namespace App\Http\Controllers;

use App\Services\KasSesiService;

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
    // Perf dataset besar: dropdown pelanggan POS sekarang AJAX (select2 AJAX
    // ke KonsumenController::searchAjax) - server TIDAK lagi menyeret seluruh
    // tabel konsumen (bisa puluhan ribu baris) ke HTML tiap buka POS. Server
    // render cuma untuk SATU konsumen pre-selected dari redirect ?id_konsumen=
    // (mis. alur nota pengganti), supaya select2 langsung menampilkan namanya
    // tanpa perlu fetch tambahan.
    $konsumenAwal = collect();
    if (request()->filled('id_konsumen')) {
        $konsumenAwal = DB::table('konsumen')
            ->where('id_konsumen', (int) request('id_konsumen'))
            ->get(['id_konsumen', 'nama_konsumen', 'telepon', 'id_golongan']);
    }
    $finishing = DB::table('finishing')->orderBy('nama_finishing')->get();
    $jasa = DB::table('jasa')
        ->leftJoin('satuan', 'jasa.id_satuan', '=', 'satuan.id_satuan')
        ->select('jasa.*', 'satuan.nama_satuan')
        ->orderBy('jasa.nama_jasa')
        ->get();

    // Generate No. Nota - prefix bisa diatur di Pengaturan Aplikasi (dulu
    // hardcode "INV").
    $date = date('Ymd');
    // Perf dataset besar: rentang sargable (DATE() mematikan index tgl_order).
    $lastNota = DB::table('transaksi')
        ->where('tgl_order', '>=', $date . ' 00:00:00')
        ->where('tgl_order', '<', date('Y-m-d', strtotime('+1 day')) . ' 00:00:00')
        ->count();
    $prefixNota = \App\Models\AppSetting::current()->nomor_nota_prefix ?: 'INV';
    $no_nota = $prefixNota . "-" . $date . "-" . str_pad($lastNota + 1, 3, '0', STR_PAD_LEFT);

    // Order dari penawaran: tampilkan rincian penawaran sebagai acuan di
    // layar Input Order (item tetap diinput operator seperti biasa).
    $quotationRef = null;
    if (request()->filled('quotation') && \App\Support\AksesHarga::bolehLihat(auth()->user())) { // rincian penawaran memuat harga
        $q = \App\Models\Quotation::with('items')->find(request('quotation'));
        if ($q && in_array($q->status, ['Terkirim', 'Disetujui'], true)) {
            $quotationRef = $q;
        }
    }

    $desainerList = \App\Services\DesainKomisiService::daftarDesainer();
    $desainerDefault = \App\Services\DesainKomisiService::tentukanDesainer(null);
    // Desainer terkunci: Operator Desain yang sedang membuat nota TIDAK boleh
    // memilih nama desainer lain - ia adalah desainer yang mengerjakan item
    // itu. Role lain (Kasir/Administrator/Owner) tetap bebas memilih desainer
    // yang benar-benar bekerja pada item tersebut.
    $desainerTerkunci = \App\Services\DesainKomisiService::desainerTerkunci();
    $desainerTerkunciNama = $desainerTerkunci
        ? optional($desainerList->firstWhere('id_user', $desainerTerkunci))->nama
        : null;

    $produkSiapCetak = \App\Services\HargaService::produkDenganHargaSiapCetak();

    // MODE EDIT (koreksi selisih): layar Input Pesanan yang sama, terisi item
    // nota yang mau dikoreksi. Item yang tidak berubah tidak menyentuh stok
    // sama sekali - hanya selisihnya yang efek.
    $notaEdit = null;
    $cartAwal = [];
    if (request()->filled('edit')) {
        $notaEdit = DB::table('transaksi')
            ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
            ->where('transaksi.id_transaksi', (int) request('edit'))
            ->select(
                'transaksi.id_transaksi',
                'transaksi.nomor_nota',
                'transaksi.id_konsumen',
                'transaksi.id_user_kasir',
                'transaksi.tgl_order',
                'transaksi.tipe_order',
                'transaksi.no_po',
                'transaksi.total_bayar',
                'transaksi.status_nota',
                'transaksi.status_order',
                'konsumen.nama_konsumen',
                'konsumen.telepon',
                'konsumen.id_golongan'
            )
            ->first();

        if (!$notaEdit) {
            abort(404);
        }
        $bolehEdit = \App\Services\NotaEditService::bolehEdit($notaEdit);
        if (!$bolehEdit['boleh']) {
            return back()->with('error', $bolehEdit['alasan']);
        }

        $cartAwal = $this->keranjangAwalEdit($notaEdit);
    }

    // MODE SUSULAN: layar Input Pesanan yang sama dipakai untuk menambah item
    // ke nota yang sudah jalan (?susulan={id_transaksi}). Data nota asal
    // dikunci di sini - operator menambah item, bukan membuat nota baru.
    $notaSusulan = null;
    // Item lama di nota asal + finishing-nya. Kosong di luar mode susulan.
    $notaSusulanItems = collect();
    $notaSusulanFinishing = collect();
    if (request()->filled('susulan')) {
        $notaSusulan = DB::table('transaksi')
            ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
            ->where('transaksi.id_transaksi', (int) request('susulan'))
            ->select(
                'transaksi.id_transaksi',
                'transaksi.nomor_nota',
                'transaksi.id_konsumen',
                'transaksi.id_user_kasir',
                'transaksi.tgl_order',
                'transaksi.tipe_order',
                'transaksi.no_po',
                'transaksi.total_bayar',
                'transaksi.status_nota',
                'transaksi.status_order',
                'konsumen.nama_konsumen',
                'konsumen.telepon',
                'konsumen.id_golongan'
            )
            ->first();

        // Syarat addition sama persis dengan endpoint tambah-item, tapi di
        // sini dicegat lebih awal supaya operator tidak sampai isi form
        // panjang untuk nota yang memang tidak boleh ditambah.
        if (!$notaSusulan) {
            abort(404);
        }
        if ((int) $notaSusulan->id_user_kasir !== (int) Auth::id()) {
            abort(403, 'Hanya operator yang membuat nota ini yang boleh menambah item.');
        }
        if ($notaSusulan->status_nota !== 'Aktif' || !in_array($notaSusulan->status_order, ['Menunggu Kasir', 'Diterima Kasir'], true)) {
            abort(422, 'Nota ini belum bisa ditambah item (harus Aktif dan belum dibatalkan).');
        }
        if (Carbon::parse($notaSusulan->tgl_order)->toDateString() !== now()->toDateString()) {
            abort(422, 'Penambahan hanya boleh dilakukan di hari yang sama dengan nota.');
        }

        // Konsumen, golongan, tanggal, dan nomor nota dikunci ke nota asal.
        $konsumenAwal = collect([(object) [
            'id_konsumen' => $notaSusulan->id_konsumen,
            'nama_konsumen' => $notaSusulan->nama_konsumen,
            'telepon' => $notaSusulan->telepon,
            'id_golongan' => $notaSusulan->id_golongan,
        ]]);
        $no_nota = $notaSusulan->nomor_nota;
        $quotationRef = null;

        // Item yang SUDAH ada di nota asal. Ditampilkan di keranjang layar
        // susulan supaya operator tahu persis apa yang sudah tercatat -
        // tidak ada item yang terlewat atau sengaja diduplikasi.
        //
        // Sifatnya BACA SAJA. Mengubah isi nota yang sudah jalan punya jalur
        // sendiri ("Edit Nota" = batal + buat ulang) yang membalik stok,
        // jurnal, dan pembayaran secara berurutan, dan ikut tunduk pada
        // persetujuan edit di Pengaturan Aplikasi. Memuat item lama ke
        // keranjang susulan sebagai item yang bisa diedit akan
        // menghasilkan baris dobel - bukan koreksi.
        $notaSusulanItems = DB::table('transaksi_detail as d')
            ->leftJoin('produk as pr', 'd.id_produk', '=', 'pr.id_produk')
            ->leftJoin('jasa as js', 'd.id_jasa', '=', 'js.id_jasa')
            ->where('d.id_transaksi', $notaSusulan->id_transaksi)
            ->orderBy('d.id_detail')
            ->get([
                'd.id_detail', 'd.id_produk', 'd.id_jasa', 'd.nama_file', 'd.p_ukuran', 'd.l_ukuran',
                'd.qty', 'd.qty_cetak', 'd.subtotal', 'd.status_spk', 'd.is_susulan',
                'pr.nama_produk', 'js.nama_jasa',
            ]);

        // Nama finishing per baris item - supaya daftar item lama di layar
        // susulan menampilkan finishing yang sama seperti di detail nota.
        $notaSusulanFinishing = collect();
        if ($notaSusulanItems->isNotEmpty()) {
            $notaSusulanFinishing = DB::table('transaksi_detail_finishing as f')
                ->join('finishing as fi', 'f.id_finishing', '=', 'fi.id_finishing')
                ->whereIn('f.id_detail', $notaSusulanItems->pluck('id_detail'))
                ->orderBy('f.id')
                ->get(['f.id_detail', 'fi.nama_finishing'])
                ->groupBy('id_detail');
        }
    }

    // Kepala nota (pelanggan, golongan harga, tipe order, tanggal) dikunci di
    // mode susulan maupun mode edit - di kedua mode operator bekerja pada nota
    // yang sudah ada, bukan membuat nota baru.
    $notaKunci = $notaSusulan ?: $notaEdit;

    return view('penjualan.create', compact('produk', 'golongan', 'konsumenAwal', 'no_nota', 'kategori', 'finishing', 'jasa', 'quotationRef', 'desainerList', 'desainerDefault', 'desainerTerkunci', 'desainerTerkunciNama', 'produkSiapCetak', 'notaSusulan', 'notaSusulanItems', 'notaSusulanFinishing', 'notaEdit', 'notaKunci', 'cartAwal'));
}

    public function store(Request $request)
{
    if (!$request->items || count($request->items) == 0) {
        return response()->json(['status' => 'error', 'msg' => 'Keranjang kosong!'], 400);
    }

    // =====================================================================
    // MODE SUSULAN - tambahan item ke nota yang SUDAH jalan.
    //
    // Layarnya sama persis dengan Input Pesanan (penjualan/create.blade.php
    // mode 'susulan'), jadi validasi harga, finishing, penulisan baris detail,
    // pemotongan stok, dan posting jurnal memakai KODE YANG SAMA dengan nota
    // baru - bukan implementasi terpisah. Yang berbeda hanya sumber data
    // nota (konsumen, golongan, tanggal, tipe order, nomor nota) dan
    // penandaan barisnya sebagai susulan supaya bisa dibedakan dari item
    // asli di nota, SPK, dan produksi.
    //
    // Syarat addition (dipastikan di sini, sebelum transaksi DB dimulai):
    //   - hanya operator yang membuat nota itu (transaksi.id_user_kasir)
    //   - nota masih Aktif, dan ordernya sudah ada (Menunggu Kasir atau
    //     Diterima Kasir) - syaratnya PERSIS sama dengan yang dicegat di
    //     create() supaya layar dan endpoint tidak berbeda
    //   - hanya di hari yang sama dengan tanggal order nota
    // =====================================================================
    $notaSusulan = null;
    if ($request->filled('susulan_id')) {
        $notaSusulan = DB::table('transaksi')->where('id_transaksi', (int) $request->susulan_id)->first();
        if (!$notaSusulan) {
            abort(404);
        }
        if ((int) $notaSusulan->id_user_kasir !== (int) Auth::id()) {
            return response()->json([
                'status'  => 'error',
                'msg'     => 'Hanya operator yang membuat nota ini yang boleh menambah item.',
                'message' => 'Hanya operator yang membuat nota ini yang boleh menambah item.',
            ], 403);
        }
        if ($notaSusulan->status_nota !== 'Aktif') {
            return response()->json([
                'status'  => 'error',
                'msg'     => 'Nota sudah tidak aktif.',
                'message' => 'Nota sudah tidak aktif.',
            ], 422);
        }
        // Status order HARUS sama persis dengan yang diperiksa create() (layar
        // Input Pesanan mode susulan). Dulu keduanya berbeda: layar membolehkan
        // nota 'Menunggu Kasir' (operator baru saja membuat nota, belum sempat
        // membuka kasir) tapi endpoint menolaknya dengan "Nota belum dibuka
        // kasir" - jadi operator boleh sampai_Isian full lalu baru ditolak
        // saat menekan "Tambahkan ke Nota".
        //
        // 'Menunggu Kasir' aman: itemnya tetap masuk SPK begitu kasir membuka
        // nota (SPK membaca transaksi dengan status_order = 'Diterima Kasir'),
        // sama seperti item nota biasa.
        if (!in_array($notaSusulan->status_order, ['Menunggu Kasir', 'Diterima Kasir'], true)) {
            return response()->json([
                'status'  => 'error',
                'msg'     => 'Nota belum dibuka kasir.',
                'message' => 'Nota belum dibuka kasir.',
            ], 422);
        }
        if (Carbon::parse($notaSusulan->tgl_order)->toDateString() !== now()->toDateString()) {
            return response()->json([
                'status'  => 'error',
                'msg'     => 'Penambahan hanya boleh dilakukan di hari yang sama dengan nota.',
                'message' => 'Penambahan hanya boleh dilakukan di hari yang sama dengan nota.',
            ], 422);
        }
    }

    // Konsumen & golongan pada mode susulan SELALU diambil dari nota, bukan
    // dari browser - item susulan tidak boleh pernah ditagihkan ke konsumen
    // lain atau dihitung dengan golongan harga yang lain.
    $idKonsumen = $notaSusulan
        ? (int) $notaSusulan->id_konsumen
        : $request->id_konsumen;
    $idGolongan = $notaSusulan
        ? (int) DB::table('konsumen')->where('id_konsumen', $notaSusulan->id_konsumen)->value('id_golongan')
        : $request->id_golongan;

    if (!$idKonsumen) {
        return response()->json([
            'status' => 'error',
            'msg' => 'Konsumen wajib dipilih'
        ], 400);
    }

    if (!$idGolongan) {
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
    //
    // Mode susulan TIDAK ikut guard ini: nota lamanya sudah lewat proses
    // terima kasir, jadi syarat "kasir buka toko" sudah terpenuhi saat nota
    // itu dibuat. Menambah item susulan tidak membuka order baru.
    $kasHariIni = $notaSusulan ? null : KasSesiService::aktifUntukTransaksi();

    if (!$notaSusulan && !$kasHariIni) {
        return response()->json(['status' => 'error', 'msg' => 'Kasir belum dibuka! Buka kas terlebih dahulu sebelum membuat order baru.'], 400);
    }

    // Tipe order & tanggal order pada mode susulan mengikuti nota asal -
    // tambahan item tidak boleh mengubah jenis order yang sudah disepakati.
    $tipeOrder = $notaSusulan
        ? ($notaSusulan->tipe_order ?: 'Reguler')
        : (in_array($request->tipe_order, ['Reguler', 'PO']) ? $request->tipe_order : 'Reguler');
    $noPo = $notaSusulan
        ? $notaSusulan->no_po
        : ($tipeOrder === 'PO' ? trim((string) $request->no_po) : null);
    if (!$notaSusulan && $tipeOrder === 'PO' && !$noPo) {
        return response()->json(['status' => 'error', 'msg' => 'No. PO wajib diisi untuk tipe order PO.'], 400);
    }

    // Tanggal order boleh diubah kasir (mis. mencatat PO yang sebenarnya
    // masuk kemarin) tapi tidak boleh di masa depan - itu bukan tanggal
    // order, itu penjadwalan, yang bukan konsep yang didukung di sini.
    $tglOrder = now();
    if ($notaSusulan) {
        $tglOrder = Carbon::parse($notaSusulan->tgl_order);
    } elseif ($request->filled('tgl_order')) {
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
    $hargaTerkunci = !\App\Support\AksesHarga::bolehLihat(Auth::user());
    // Hak menginput Biaya Desain dipisah dari hak melihat harga (lihat catatan
    // pemisahan di dalam validasiItems()). Dihitung SEKALI di sini, bukan
    // tiap baris item, dan TIDAK bergantung pada $hargaTerkunci: role boleh
    // salah satu atau keduanya.
    $bolehInputBiayaDesain = \App\Support\AksesHarga::bolehInputBiayaDesain(Auth::user());

    try {
        $itemsTervalidasi = $this->validasiItems($request, (int) $idGolongan, $hargaTerkunci, $bolehInputBiayaDesain);
    } catch (\App\Exceptions\ValidasiNotaException $e) {
        return response()->json(['status' => 'error', 'msg' => $e->getMessage()], 422);
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
        // Mode susulan memakai nomor nota yang sudah ada - bukan nomor baru.
        $noNota = $notaSusulan ? $notaSusulan->nomor_nota : DocumentNumberService::generate($prefixNota);

        $grand_total = collect($itemsTervalidasi)->sum('subtotal');

        $dataTransaksi = [
            'nomor_nota'        => $noNota,
            'tipe_order'        => $tipeOrder,
            'no_po'             => $noPo,
            'id_konsumen'       => $idKonsumen,
            // P0-6: fallback "?? 1" dihapus - sebelumnya request tanpa user
            // login (mis. sesi hangus) diam-diam dicatat atas nama user #1
            // (biasanya Administrator), merusak akuntabilitas. Sekarang
            // gagal jelas sebagai 401 kalau sesi tidak valid.
            'id_user_kasir'     => Auth::id() ?? abort(401, 'Sesi login tidak valid - login ulang sebelum membuat order.'),
            'tgl_order'         => $tglOrder,
            'total_bayar'       => $grand_total,
            'status_pembayaran' => 'Belum Bayar',
            'status_nota'       => 'Aktif',
            // Order lifecycle: nota baru = Menunggu Kasir. Baris
            // transaksi_detail status_spk='Antre' di bawah DORMAN sampai
            // Kasir menerima via POST terimaTransaksi (SPK/Produksi
            // memfilter status_order='Diterima Kasir').
            'status_order'      => 'Menunggu Kasir',
            'created_at'        => now(),
            'updated_at'        => now(),
        ];

        // Mode susulan: nota sudah ada, item nempel ke nota itu. Total nota
        // dan status pembayarannya ikut diperbarui setelah loop (lihat
        // blok "TOTAL NOTA" di bawah), bukan di sini.
        $id_transaksi = $notaSusulan
            ? (int) $notaSusulan->id_transaksi
            : DB::table('transaksi')->insertGetId($dataTransaksi);

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
                    // Penanda baris susulan supaya bisa dibedakan dari item
                    // asli nota (dipakai di detail nota & SPK).
                    'is_susulan'          => $notaSusulan ? 1 : 0,
                ]);

                $totalJasaSemua += $item['subtotal'];
                continue;
            }

            $totalProdukSemua += $item['subtotal'];

            // Penulisan item produk dipindah ke method tulisItemProduk()
            // supaya mode EDIT SELISIH memakai kode yang sama persis.
            [$id_detail, $hppItemIni] = $this->tulisItemProduk(
                $item,
                $id_transaksi,
                $notaSusulan ? $noNota . ' (susulan)' : $noNota,
                $notaSusulan
                    ? 'Pemakaian produksi order susulan ' . $noNota
                    : 'Pemakaian produksi order ' . $noNota,
                (bool) $notaSusulan
            );
            $totalHppMaterialSemua += $hppItemIni;
        }

        // Jejak penimpaan harga: bila biaya cetak yang dipakai berbeda dari
        // price list (potongan atau markup manual oleh kasir/admin), catat di
        // audit siapa, nota mana, baris mana, dari berapa ke berapa. Tanpa ini
        // potongan harga tidak bisa ditelusuri setelah nota tersimpan.
        $penyimpangan = [];
        foreach ($itemsTervalidasi as $urut => $item) {
            if (!empty($item['is_jasa']) || !isset($item['harga_standar'])) {
                continue;
            }
            $selisih = round((float) $item['biaya_cetak'] - (float) $item['harga_standar'], 2);
            if (abs($selisih) > 1) {
                $penyimpangan[] = ['baris' => $urut + 1, 'nama_file' => $item['nama_file'], 'price_list' => (float) $item['harga_standar'], 'dipakai' => (float) $item['biaya_cetak'], 'selisih' => $selisih];
            }
        }
        if ($penyimpangan !== []) {
            $totalSelisih = array_sum(array_column($penyimpangan, 'selisih'));
            \App\Services\AuditLogger::log(
                'harga_manual',
                'transaksi',
                (int) $id_transaksi,
                ['price_list' => array_sum(array_column($penyimpangan, 'price_list'))],
                ['dipakai' => array_sum(array_column($penyimpangan, 'dipakai')), 'baris' => $penyimpangan],
                'Nota ' . $noNota . ': biaya cetak diubah dari price list pada ' . count($penyimpangan) . ' baris (' . ($totalSelisih < 0 ? 'potongan Rp ' : 'tambahan Rp ') . number_format(abs($totalSelisih), 0, ',', '.') . ')'
            );
        }

        // Dipisah jadi 2 posting independen (bukan 1 baris gabungan) supaya
        // postPenjualan() yang sudah teruji untuk order produk-only tidak
        // perlu diubah sama sekali - order campuran produk+jasa cukup
        // menjalankan keduanya dalam transaction yang sama.
        // Nota bertotal Rp 0 tidak pernah bisa lunas (selalu di antrean Belum Bayar) dan jurnal HPP-nya terlewat padahal
        // stok bahan sudah terpotong. Ditolak di sini (di dalam transaksi, jadi potongan stok ikut batal).
        if ($totalProdukSemua + $totalJasaSemua <= 0) {
            throw new \RuntimeException('Total nota Rp 0: isi biaya cetak/harga barangnya dulu. Nota tanpa nilai tidak bisa dilunasi dan bahannya tidak tercatat sebagai biaya.');
        }
        // Nomor acuan jurnal. Mode susulan memakai sufiks "-susulan" supaya
        // tambahan ke nota yang sama bisa dibedakan dari posting nota awal
        // saat memuat jurnal umum.
        $noNotaJurnal = $notaSusulan ? $noNota . '-susulan' : $noNota;

        if ($totalProdukSemua > 0) {
            AccountingService::postPenjualan($noNotaJurnal, $totalProdukSemua, $totalHppMaterialSemua);
        }
        if ($totalJasaSemua > 0) {
            AccountingService::postPenjualanJasa($noNotaJurnal, $totalJasaSemua);
        }

        // TOTAL NOTA pada mode susulan: total nota lama naik sebesar tambahan,
        // dan status pembayaran dihitung ULANG dari total vs yang sudah
        // dibayar (kolom status_pembayaran cuma salinan denormalisasi - kalau
        // tidak diupdate di sini, badge dan kolom itu akan bertentangan).
        // Nota yang tadinya Lunas otomatis turun jadi DP/Belum Bayar karena
        // tambahan belum dibayar - kasir melihat sisa = hanya tambahan.
        if ($notaSusulan) {
            // $grand_total di sini = total BARU BARU ini (tambahan), bukan
            // total nota. Total nota adalah total lama + tambahan.
            $totalLama = (float) $notaSusulan->total_bayar;
            $totalNotaBaru = $totalLama + $grand_total;

            $totalTerbayar = (float) DB::table('pembayaran_riwayat')
                ->where('id_transaksi', $id_transaksi)
                ->sum('nominal');
            $statusBaru = \App\Services\StatusPembayaran::dari($totalNotaBaru, $totalTerbayar);

            DB::table('transaksi')
                ->where('id_transaksi', $id_transaksi)
                ->update([
                    'total_bayar'       => $totalNotaBaru,
                    'status_pembayaran' => $statusBaru,
                    'updated_at'        => now(),
                ]);
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

            $saldoDeposit = KonsumenDepositController::saldoUntuk((int) $request->id_konsumen, true);

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

                $idBayarDeposit = DB::table('pembayaran_riwayat')->insertGetId([
                    'id_transaksi' => $id_transaksi,
                    'nominal'      => $jumlahPakai,
                    'metode'       => 'Deposit',
                    'id_bank'      => null,
                    'tgl_bayar'    => now(),
                    'keterangan'   => 'Otomatis dipotong dari saldo deposit saat order dibuat',
                ]);

                \App\Services\AuditLogger::log(
                    'created',
                    'pembayaran_riwayat',
                    (int) $idBayarDeposit,
                    null,
                    ['nomor_nota' => $noNota, 'nominal' => (float) $jumlahPakai, 'metode' => 'Deposit'],
                    "Deposit dipakai otomatis untuk nota {$noNota}: Rp " . number_format((float) $jumlahPakai, 0, ',', '.')
                );

                AccountingService::postPembayaranDeposit($noNota, $jumlahPakai);

                $statusBaru = $jumlahPakai >= $grand_total ? 'Lunas' : 'DP';

                \App\Services\StatusTransitionGuard::validateStatusPembayaran(
                    (string) DB::table('transaksi')->where('id_transaksi', $id_transaksi)->value('status_pembayaran'),
                    $statusBaru
                );

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

        if ($notaSusulan) {
            // Mode susulan: nota asal tetap yang berlaku, jadi pesan sukses
            // menyuruh operator kembali ke detail nota - bukan ke Monitoring
            // Kasir (nota ini sudah di sana sejak awal).
            session()->flash('success', "Item susulan berhasil ditambahkan ke nota {$noNota}.");

            return response()->json([
                'status'       => 'success',
                'msg'          => 'Item susulan berhasil ditambahkan ke nota ' . $noNota . '.',
                'message'      => 'Item susulan berhasil ditambahkan ke nota ' . $noNota . '.',
                'id_transaksi' => $id_transaksi,
                'nomor_nota'   => $noNota,
                'total_baru'   => (float) $notaSusulan->total_bayar + $grand_total,
                'total_tambah' => $grand_total,
            ]);
        }

        session()->flash('success', "Nota {$noNota} berhasil dibuat.");
        \App\Support\JalurLanjut::set('Langkah berikut: pembayaran/validasi di Kasir', 'kasir.index', 'Buka Monitoring Kasir');

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
            'msg' => \App\Support\PesanError::untuk($e)
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
    /**
     * Tulis satu item produk ke nota: baris transaksi_detail, rincian
     * finishing, pemakaian bahan (potong stok + kartu_stok + snapshot
     * bahan_pakai), lalu HPP item ini.
     *
     * Dipakai oleh store() (nota baru & tambah item susulan) DAN oleh
     * updateItemNota() (edit selisih) - jadi aturan pemakaian bahan &
     * pencatatan stok hanya ada SATU implementasi, tidak mungkin ada dua
     * versi yang berbeda.
     *
     * @param  string  $referensiKartu  Referensi yang ditulis di kartu_stok.
     * @param  string  $keteranganKartu Keterangan yang ditulis di kartu_stok.
     * @param  bool    $isSusulan       Baris ini item susulan (bukan item asli).
     * @param  int     $idDetailLama    0 = baris baru. >0 = baris itu yang
     *                                    diperbarui (mode EDIT SELISIH) -
     *                                    id_detail sengaja tidak berubah
     *                                    supaya link SPK & Berkas Order
     *                                    yang sudah dicetak tetap berlaku.
     * @return array{0: int, 1: float} [id_detail, HPP item ini]
     */
    private function tulisItemProduk(array $item, int $idTransaksi, string $referensiKartu, string $keteranganKartu, bool $isSusulan, int $idDetailLama = 0): array
    {
        $jumlah_order = $item['qty_input'];
        $komisiDesain = \App\Services\DesainKomisiService::hitung($item['id_desainer'], (float) $item['biaya_desain']);

        $id_detail = $idDetailLama > 0
            ? $this->perbaruiBarisItemProduk($item, $idDetailLama, $komisiDesain, $jumlah_order)
            : DB::table('transaksi_detail')->insertGetId([
                'id_transaksi'     => $idTransaksi,
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
                'file_siap_cetak'  => $item['file_siap_cetak'] ? 1 : 0,
                'id_desainer'      => $item['id_desainer'],
                'komisi_desain_persen' => $komisiDesain['persen'],
                'komisi_desain'    => $komisiDesain['komisi'],
                'komisi_status'    => $komisiDesain['komisi'] > 0 ? 'Belum' : null,
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
                // Penanda baris susulan supaya bisa dibedakan dari item asli
                // nota (dipakai di detail nota & SPK).
                'is_susulan'       => $isSusulan ? 1 : 0,
                // harga_jual_snapshot dulu diisi = subtotal (harga jual final
                // baris ini). hpp_snapshot diisi setelah loop BOM di bawah,
                // karena baru diketahui setelah menjumlah biaya tiap bahan.
                'harga_jual_snapshot' => $item['subtotal'],
            ]);

        // Rincian finishing per item (boleh lebih dari satu).
        $this->tulisFinishingItem($id_detail, $item['finishing_list'] ?? []);

        // BOM + tinta/toner mesin produk (lihat BomCalculationService::komponen).
        $hppItemIni = $this->pakaiBahanItem($item, $id_detail, $referensiKartu, $keteranganKartu, $jumlah_order);

        return [$id_detail, $hppItemIni];
    }

    /**
     * Perbarui baris item produk yang sudah ada (mode EDIT SELISIH).
     *
     * Baris diperbarui DI TEMPAT - id_detail tidak berubah, jadi link SPK &
     * Berkas Order yang sudah dicetak untuk item ini tetap berlaku.
     *
     * status_spk & is_susulan SENGAJA tidak disentuh: status produksi item
     * tidak boleh berubah diam-diam karena operator mengoreksi nominalnya.
     */
    private function perbaruiBarisItemProduk(array $item, int $idDetail, array $komisiDesain, $jumlah_order): int
    {
        DB::table('transaksi_detail')->where('id_detail', $idDetail)->update([
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
            'file_siap_cetak'  => $item['file_siap_cetak'] ? 1 : 0,
            'id_desainer'      => $item['id_desainer'],
            'komisi_desain_persen' => $komisiDesain['persen'],
            'komisi_desain'    => $komisiDesain['komisi'],
            'komisi_status'    => $komisiDesain['komisi'] > 0 ? 'Belum' : null,
            'status_urgensi'   => $item['status_urgensi'],
            'status_urgent'    => $item['status_urgensi'] !== 'Normal' ? 1 : 0,
            'status_finishing' => $item['status_finishing'],
            'subtotal'         => $item['subtotal'],
            'harga_jual_snapshot' => $item['subtotal'],
            'updated_at'       => now(),
        ]);

        // Rincian finishing lama dibuang; yang baru ditulis ulang oleh
        // pemanggil dengan isi validasi terbaru.
        DB::table('transaksi_detail_finishing')->where('id_detail', $idDetail)->delete();

        return $idDetail;
    }

    /**
     * Tulis rincian finishing milik satu item nota.
     *
     * Rincian lama (kalau ada) dibuang lebih dulu - dipakai mode EDIT
     * SELISIH, di mana daftar finishing bisa berubah.
     */
    private function tulisFinishingItem(int $idDetail, array $daftarFinishing): void
    {
        foreach ($daftarFinishing as $fin) {
            DB::table('transaksi_detail_finishing')->insert([
                'id_detail' => $idDetail, 'id_finishing' => $fin['id_finishing'], 'status_finishing' => $fin['status_finishing'],
                'qty_pakai' => $fin['qty_pakai'], 'biaya' => $fin['biaya'], 'catatan' => $fin['catatan'],
                // Dimensi potong ikut ditulis. Sebelumnya kedua nilai ini
                // sudah dihitung dan dimasukkan ke $daftarFinishing (lihat
                // baris di atas blok komentar "Dimensi potong disimpan")
                // tapi TIDAK ikut ke INSERT, jadi tidak pernah sampai ke
                // database. Akibatnya biaya Potong Manual yang tersimpan
                // tidak bisa ditelusuri ulang: sisi media ada di
                // transaksi_detail.p_ukuran/l_ukuran/qty_cetak, sisi keping
                // yang dipotong hilang. Kolomnya dibuat migration
                // 2026_11_02_090000_simpan_dimensi_potong.php.
                // NULL (bukan 0) untuk finishing yang bukan LINEAR_GRID.
                'lebar_potong' => $fin['lebar_potong'] ?? null,
                'tinggi_potong' => $fin['tinggi_potong'] ?? null,
                // Ukuran media yang dipotong. Untuk produk 'ukuran' ini
                // sama dengan Panjang/Lebar item; untuk produk per lembar
                // (A3) inilah satu-satunya sumber ukuran media, jadi
                // tanpa kolom ini biaya potong A3 tidak bisa dihitung
                // ulang. Kolomnya dibuat migration
                // 2026_11_06_090000_potong_manual_a3.php.
                'lebar_media' => $fin['lebar_media'] ?? null,
                'tinggi_media' => $fin['tinggi_media'] ?? null,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    /**
     * Potong stok bahan untuk satu item: catat kartu_stok (tipe OUT), tulis
     * snapshot pemakaian bahan per baris item, lalu hitung HPP item ini.
     *
     * Dipisah dari tulisItemProduk() supaya mode EDIT SELISIH bisa memakai
     * bagian yang sama - hanya bedanya, mode edit memanggilnya SESUDAH
     * pemakaian bahan yang lama dikembalikan, sehingga efeknya hanya
     * sebesar selisihnya.
     */
    private function pakaiBahanItem(array $item, int $idDetail, string $referensiKartu, string $keteranganKartu, $jumlah_order): float
    {
        $boms = \App\Services\BomCalculationService::komponen(
            (int) $item['id_produk'], (float) $item['p'], (float) $item['l'], (float) $jumlah_order
        );

        $hppItemIni = 0;

        foreach ($boms as $bom) {

            $qty_pakai = $bom->qty_pakai;

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
                    'referensi'     => $referensiKartu,
                    'keterangan'    => $keteranganKartu,
                    'created_by'    => Auth::id(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                // Hardening P0-1: snapshot qty_pakai PER BARIS ITEM (bukan
                // dihitung ulang dari BOM live saat reversal - lihat
                // catatan lengkap di migrasi transaksi_detail_bahan_pakai
                // dan StokReversalService).
                DB::table('transaksi_detail_bahan_pakai')->insert([
                    'id_detail'  => $idDetail,
                    'id_bahan'   => $bom->id_bahan,
                    'qty_pakai'  => $qtyPakaiSatuanStok,
                    'is_tinta'   => $bom->is_tinta,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $hppItemIni += $qtyPakaiSatuanStok * (float) ($bahan->harga_rata_rata ?? 0);
            }
        }

        if ($hppItemIni > 0) {
            DB::table('transaksi_detail')->where('id_detail', $idDetail)->update([
                'hpp_snapshot' => $hppItemIni,
            ]);
        }

        return $hppItemIni;
    }

    /**
     * Apakah isi item nota BERUBAH dibanding isi yang tersimpan.
     *
     * Yang dibandingkan adalah hal yang benar-benar mengubah pemakaian
     * bahan & isi pekerjaan: produk/jasa, ukuran, qty, nama file, catatan
     * produksi, urgensi, dan daftar finishing.
     *
     * NOMINAL SENGAJA TIDAK dibandingkan. Harga selalu dihitung ulang dari
     * master, jadi item yang tidak disentuh operator pun boleh ikut harga
     * terbaru - dan itu tidak boleh dianggap "berubah" (kalau iya, stok
     * akan dikembalikan lalu dipotong lagi tanpa ada gunanya).
     */
    private function itemBerubah(object $lama, array $item): bool
    {
        $tandaFinishingLama = DB::table('transaksi_detail_finishing')
            ->where('id_detail', $lama->id_detail)
            ->orderBy('id_finishing')
            ->get(['id_finishing', 'status_finishing', 'qty_pakai'])
            ->map(fn ($f) => [
                (int) $f->id_finishing,
                (string) $f->status_finishing,
                (float) ($f->qty_pakai ?? 0),
            ])->all();

        $tandaFinishingBaru = collect($item['finishing_list'] ?? [])
            ->sortBy(fn ($f) => (int) $f['id_finishing'])
            ->map(fn ($f) => [
                (int) $f['id_finishing'],
                (string) $f['status_finishing'],
                (float) ($f['qty_pakai'] ?? 0),
            ])->all();

        if ($tandaFinishingLama != $tandaFinishingBaru) {
            return true;
        }

        return (int) ($lama->id_produk ?? 0) !== (int) ($item['id_produk'] ?? 0)
            || (int) ($lama->id_jasa ?? 0) !== (int) ($item['id_jasa'] ?? 0)
            || (float) $lama->p_ukuran !== (float) ($item['p'] ?? 0)
            || (float) $lama->l_ukuran !== (float) ($item['l'] ?? 0)
            || (int) $lama->qty !== (int) ($item['qty_input'] ?? 0)
            || (string) $lama->nama_file !== (string) ($item['nama_file'] ?? '')
            || (string) ($lama->catatan_produksi ?? '') !== (string) ($item['catatan_produksi'] ?? '')
            || (string) $lama->status_urgensi !== (string) ($item['status_urgensi'] ?? '')
            || (int) $lama->file_siap_cetak !== (int) ($item['file_siap_cetak'] ? 1 : 0);
    }

    /**
     * Perbarui item produk yang TIDAK berubah isinya: nominal disegarkan
     * dari master, tapi stok & pemakaian bahan tidak disentuh sama sekali.
     *
     * Dipakai mode EDIT SELISIH untuk item yang operatornya tidak sentuh -
     * item seperti ini harus benar-benar tidakLeaving jejak di kartu_stok.
     */
    private function perbaruiItemProdukTanpaStok(array $item, int $idDetail): void
    {
        $komisiDesain = \App\Services\DesainKomisiService::hitung($item['id_desainer'], (float) $item['biaya_desain']);
        $this->perbaruiBarisItemProduk($item, $idDetail, $komisiDesain, $item['qty_input']);
        $this->tulisFinishingItem($idDetail, $item['finishing_list'] ?? []);
    }

    /**
     * Simpan koreksi nota (EDIT SELISIH).
     *
     * Berbeda dengan alur lama (batal + buat ulang), nota di sini TIDAK
     * dibatalkan dan pembayarannya tidak diubah. Yang efeknya hanya selisih:
     *
     *   - item baru     : stok dipotong, baris baru dibuat
     *   - item diubah   : pemakaian bahan lama dikembalikan, yang baru dipotong
     *   - item dihapus  : pemakaian bahan dikembalikan, baris dihapus
     *   - item tetap    : hanya nominal yang disegarkan dari master, stok
     *                     tidak disentuh sama sekali
     *
     * Jurnal pendapatan direkonsiliasi sebesar total yang baru
     * (AccountingService::sesuaikanTotalNota) dan jurnal HPP disesuaikan
     * sebesar selisih HPP - bukan dibalik lalu ditulis ulang, supaya
     * riwayat buku besar tetap menjelaskan apa yang terjadi.
     */
    public function updateItemNota($id, Request $request)
    {
        if (!$request->items || count($request->items) == 0) {
            return response()->json(['status' => 'error', 'msg' => 'Keranjang kosong!'], 400);
        }

        $id = (int) $id;
        $transaksi = DB::table('transaksi')->where('id_transaksi', $id)->first();
        if (!$transaksi) {
            abort(404);
        }

        $boleh = \App\Services\NotaEditService::bolehEdit($transaksi);
        if (!$boleh['boleh']) {
            return response()->json(['status' => 'error', 'msg' => $boleh['alasan']], 422);
        }

        // Kepala nota terkunci: pelanggan & golongan harga diambil dari nota,
        // bukan dari kiriman browser.
        $idKonsumen = (int) $transaksi->id_konsumen;
        $idGolongan = (int) DB::table('konsumen')->where('id_konsumen', $idKonsumen)->value('id_golongan');

        $hargaTerkunci = !\App\Support\AksesHarga::bolehLihat(Auth::user());
        $bolehInputBiayaDesain = \App\Support\AksesHarga::bolehInputBiayaDesain(Auth::user());

        try {
            $itemsTervalidasi = $this->validasiItems($request, $idGolongan, $hargaTerkunci, $bolehInputBiayaDesain);
        } catch (\App\Exceptions\ValidasiNotaException $e) {
            return response()->json(['status' => 'error', 'msg' => $e->getMessage()], 422);
        }

        $noNota = $transaksi->nomor_nota;

        DB::beginTransaction();
        try {
            // Kunci nota: dua simpan bersamaan untuk nota yang sama tidak
            // boleh sama-sama menulis.
            $transaksi = DB::table('transaksi')->where('id_transaksi', $id)->lockForUpdate()->first();
            $boleh = \App\Services\NotaEditService::bolehEdit($transaksi);
            if (!$boleh['boleh']) {
                throw new \RuntimeException($boleh['alasan']);
            }

            $itemLama = DB::table('transaksi_detail')->where('id_transaksi', $id)->get()->keyBy('id_detail');
            $hppLama = (float) $itemLama->sum('hpp_snapshot');

            $totalBaru = 0.0;
            $hppBaru = 0.0;
            $idDetailDipakai = [];
            $jumlahPerubahan = ['baru' => 0, 'diubah' => 0, 'dihapus' => 0, 'tetap' => 0];

            foreach ($itemsTervalidasi as $index => $item) {
                $idDetailLama = isset($item['id_detail']) ? (int) $item['id_detail'] : 0;
                $lama = $idDetailLama ? $itemLama->get($idDetailLama) : null;

                if ($lama) {
                    // id_detail harus milik nota ini - kalau tidak, operator
                    // bisa menimpa baris nota orang lain.
                    if ((int) $lama->id_transaksi !== $id) {
                        throw new \RuntimeException('Item pada baris ' . ($index + 1) . ' bukan milik nota ini.');
                    }
                    $idDetailDipakai[] = $idDetailLama;
                }

                if ($lama && !$this->itemBerubah($lama, $item)) {
                    // TIDAK BERUBAH: hanya nominal yang disegarkan dari
                    // master. Stok & pemakaian bahan tidak disentuh.
                    $this->perbaruiItemProdukTanpaStok($item, $idDetailLama);
                    $totalBaru += (float) $item['subtotal'];
                    $hppBaru += (float) $lama->hpp_snapshot;
                    $jumlahPerubahan['tetap']++;

                    continue;
                }

                if ($lama) {
                    // BERUBAH: pemakaian bahan lama dikembalikan dulu, lalu
                    // yang baru dipotong - efek bersihnya cuma sebesar
                    // selisihnya.
                    StokReversalService::kembalikan(
                        $lama,
                        'EDIT-NOTA-' . $id,
                        'Koreksi nota #' . $noNota . ' - pemakaian bahan dikembalikan'
                    );
                    $jumlahPerubahan['diubah']++;
                } else {
                    $jumlahPerubahan['baru']++;
                }

                [$idDetailBaru, $hppItem] = $this->tulisItemProduk(
                    $item,
                    $id,
                    $noNota . ' (edit)',
                    'Pemakaian produksi order ' . $noNota . ' (koreksi)',
                    false,
                    $idDetailLama
                );
                $totalBaru += (float) $item['subtotal'];
                $hppBaru += $hppItem;
            }

            // Item yang ada di nota tapi TIDAK ada di kiriman -> dihapus:
            // pemakaian bahan dikembalikan, baris & rinciannya dihapus.
            foreach ($itemLama as $idDetail => $lama) {
                if (in_array((int) $idDetail, $idDetailDipakai, true)) {
                    continue;
                }
                StokReversalService::kembalikan(
                    $lama,
                    'EDIT-NOTA-' . $id,
                    'Koreksi nota #' . $noNota . ' - item dihapus, bahan dikembalikan'
                );
                DB::table('transaksi_detail_finishing')->where('id_detail', $idDetail)->delete();
                DB::table('transaksi_detail')->where('id_detail', $idDetail)->delete();
                $jumlahPerubahan['dihapus']++;
            }

            if ($totalBaru <= 0) {
                throw new \RuntimeException('Total nota tidak boleh Rp 0 - tidak ada data yang tersimpan; coba lagi atau hubungi Administrator.');
            }

            // Jurnal pendapatan direkonsiliasi sebesar total yang baru.
            AccountingService::sesuaikanTotalNota($noNota, $totalBaru);

            // Jurnal HPP disesuaikan sebesar selisih HPP.
            $selisihHpp = round($hppBaru - $hppLama, 2);
            if ($selisihHpp > 0) {
                AccountingService::postHppBahanTerpakai(
                    $noNota,
                    $selisihHpp,
                    'Penyesuaian HPP koreksi nota ' . $noNota . ' (pemakaian bahan bertambah)'
                );
            } elseif ($selisihHpp < 0) {
                AccountingService::postPembalikanHppItem($noNota, 'Penyesuaian HPP koreksi nota ' . $noNota . ' (pemakaian bahan berkurang)', abs($selisihHpp));
            }

            // Status pembayaran dihitung ulang dari total yang baru.
            $totalTerbayar = (float) DB::table('pembayaran_riwayat')->where('id_transaksi', $id)->sum('nominal');
            $statusBaru = \App\Services\StatusPembayaran::dari($totalBaru, $totalTerbayar);

            DB::table('transaksi')->where('id_transaksi', $id)->update([
                'total_bayar' => $totalBaru,
                'status_pembayaran' => $statusBaru,
                'updated_at' => now(),
            ]);

            \App\Services\AuditLogger::log(
                'updated',
                'transaksi',
                $id,
                ['total' => (float) $transaksi->total_bayar, 'item' => $itemLama->count()],
                ['total' => $totalBaru, 'item' => $jumlahPerubahan],
                'Koreksi nota ' . $noNota . ' (selisih): baru ' . $jumlahPerubahan['baru']
                    . ', diubah ' . $jumlahPerubahan['diubah']
                    . ', dihapus ' . $jumlahPerubahan['dihapus']
                    . ', tetap ' . $jumlahPerubahan['tetap']
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'msg' => \App\Support\PesanError::untuk($e),
            ], 500);
        }

        session()->flash('success', "Nota {$noNota} berhasil dikoreksi.");

        return response()->json([
            'status' => 'success',
            'msg' => 'Nota ' . $noNota . ' berhasil dikoreksi.',
            'id_transaksi' => $id,
            'nomor_nota' => $noNota,
            'total_baru' => $totalBaru,
            'perubahan' => $jumlahPerubahan,
        ]);
    }

    /**
     * Susun keranjang awal untuk mode EDIT: item-item nota yang dikoreksi,
     * dengan BENTUK YANG SAMA PERSIS dengan item yang dibuat di layar Input
     * Pesanan (lihat tambahKeKeranjang) supaya operator bisa langsung
     * mengubahnya - dan supaya server bisa mencocokkannya kembali ke baris
     * nota lewat id_detail.
     *
     * id_detail ikut dibawa supaya saat disimpan server tahu baris mana
     * yang diperbarui (diperbarui di tempat, bukan dibuat ulang) dan
     * barang mana yang memang baru.
     */
    private function keranjangAwalEdit(object $nota): array
    {
        $itemNota = DB::table('transaksi_detail')
            ->leftJoin('produk', 'transaksi_detail.id_produk', '=', 'produk.id_produk')
            ->leftJoin('jasa', 'transaksi_detail.id_jasa', '=', 'jasa.id_jasa')
            // Kategori produk diambil dari bahan bakunya (produk tidak punya
            // kolom kategori sendiri) - sama seperti query produk di create().
            ->leftJoin('bom_detail', 'transaksi_detail.id_produk', '=', 'bom_detail.id_produk')
            ->leftJoin('bahan_baku', 'bom_detail.id_bahan', '=', 'bahan_baku.id_bahan')
            ->where('transaksi_detail.id_transaksi', (int) $nota->id_transaksi)
            ->orderBy('transaksi_detail.id_detail')
            ->get([
                'transaksi_detail.id_detail',
                'transaksi_detail.id_produk',
                'transaksi_detail.id_jasa',
                'transaksi_detail.nama_file',
                'transaksi_detail.thumbnail',
                'transaksi_detail.p_ukuran',
                'transaksi_detail.l_ukuran',
                'transaksi_detail.qty',
                'transaksi_detail.qty_cetak',
                'transaksi_detail.biaya_cetak',
                'transaksi_detail.biaya_desain',
                'transaksi_detail.biaya_finishing',
                'transaksi_detail.id_finishing',
                'transaksi_detail.id_desainer',
                'transaksi_detail.file_siap_cetak',
                'transaksi_detail.status_urgensi',
                'transaksi_detail.status_finishing',
                'transaksi_detail.catatan_produksi',
                'transaksi_detail.subtotal',
                'produk.nama_produk',
                'produk.metode_hitung',
                'jasa.nama_jasa',
                DB::raw('COALESCE(bahan_baku.id_kategori, 0) as id_kategori'),
            ])->unique('id_detail')->values();

        $finishingNota = DB::table('transaksi_detail_finishing as f')
            ->join('finishing as fi', 'f.id_finishing', '=', 'fi.id_finishing')
            ->whereIn('f.id_detail', $itemNota->pluck('id_detail'))
            ->orderBy('f.id')
            ->get([
                'f.id_detail', 'f.id_finishing', 'f.status_finishing', 'f.qty_pakai', 'f.biaya', 'f.catatan',
                'f.lebar_potong', 'f.tinggi_potong', 'f.lebar_media', 'f.tinggi_media',
                'fi.nama_finishing', 'fi.metode_hitung',
            ])
            ->groupBy('id_detail');

        $keranjang = [];
        foreach ($itemNota as $baris) {
            $daftarFinishing = $finishingNota->get($baris->id_detail, collect())
                ->map(fn ($f) => [
                    'id_finishing' => (int) $f->id_finishing,
                    'nama' => $f->nama_finishing,
                    'metode' => $f->metode_hitung,
                    'status' => $f->status_finishing,
                    'qty_pakai' => $f->qty_pakai,
                    'biaya' => (float) $f->biaya,
                    'catatan' => $f->catatan,
                    'lebar_potong' => $f->lebar_potong,
                    'tinggi_potong' => $f->tinggi_potong,
                    'lebar_media' => $f->lebar_media,
                    'tinggi_media' => $f->tinggi_media,
                ])->values()->all();

            $qty = (float) $baris->qty;
            $keranjang[] = [
                // Penanda baris nota asal - server memakainya untuk tahu
                // item mana yang diperbarui & item mana yang baru.
                'id_detail' => (int) $baris->id_detail,
                'id_produk' => $baris->id_produk,
                'id_jasa' => $baris->id_jasa,
                'nama_produk' => $baris->nama_produk ?: ($baris->nama_jasa ?: $baris->nama_file),
                'nama_file' => $baris->nama_file,
                'thumbnail' => $baris->thumbnail,
                'p' => (float) $baris->p_ukuran,
                'l' => (float) $baris->l_ukuran,
                'qty_input' => $qty,
                'qty_hitung' => (float) ($baris->qty_cetak ?? $qty),
                'harga_satuan' => $qty > 0 ? round((float) $baris->subtotal / $qty, 2) : (float) $baris->subtotal,
                'biaya_desain' => (float) $baris->biaya_desain,
                'id_desainer' => $baris->id_desainer,
                'siap_cetak' => (bool) $baris->file_siap_cetak,
                'biaya_cetak' => (float) $baris->biaya_cetak,
                'cetak_manual' => false,
                'id_finishing' => $daftarFinishing[0]['id_finishing'] ?? null,
                'nama_finishing' => $daftarFinishing ? implode(' + ', array_column($daftarFinishing, 'nama')) : null,
                'finishing' => $daftarFinishing,
                'biaya_finishing' => (float) $baris->biaya_finishing,
                'status_urgensi' => $baris->status_urgensi,
                'status_finishing' => $baris->status_finishing,
                'catatan_produksi' => $baris->catatan_produksi,
                'subtotal' => (float) $baris->subtotal,
                'id_kategori' => $baris->id_kategori,
            ];
        }

        return $keranjang;
    }

    /**
     * Validasi item kiriman: harga, finishing, dan catatan produksi dihitung
     * ULANG dari
     * master (produk + setting_harga + finishing) memakai hanya input mentah
     * yang tidak bisa dipalsukan (p, l, qty) - nilai dari browser untuk
     * harga SELALU diabaikan.
     *
     * Dipakai oleh store() (nota baru & tambah item susulan) dan oleh
     * updateItemNota() (edit selisih) - jadi kedua alur memakai ATURAN HARGA
     * YANG SAMA PERSIS, tidak ada dua implementasi yang bisa berbeda.
     *
     * @return array<int, array> Baris item yang sudah tervalidasi.
     *
     * @throws \App\Exceptions\ValidasiNotaException Bila ada baris yang tidak valid.
     */
    private function validasiItems(Request $request, int $idGolongan, bool $hargaTerkunci, bool $bolehInputBiayaDesain): array
    {
        $itemsTervalidasi = [];

    // Perf (N+1): dulu tiap baris keranjang menjalankan query master-nya
    // sendiri (produk/jasa/finishing) di dalam loop - keranjang 20 item
    // berarti puluhan query identik per checkout. Sekarang semua master
    // yang dirujuk items di-prefetch SEKALI di atas, loop tinggal baca
    // dari collection yang sudah di-keyBy id. Semantik cek "tidak
    // ditemukan" tetap sama persis (get() mengembalikan null).
    $idJasaReq = collect($request->items)->filter(fn ($i) => !empty($i['id_jasa']))->map(fn ($i) => (int) $i['id_jasa'])->unique()->values()->all();
    $idProdukReq = collect($request->items)->filter(fn ($i) => empty($i['id_jasa']) && !empty($i['id_produk']))->map(fn ($i) => (int) $i['id_produk'])->unique()->values()->all();
    $idFinishingReq = collect($request->items)
        ->flatMap(function ($i) {
            $daftar = is_array($i['finishing'] ?? null) ? $i['finishing'] : [];
            if (!$daftar && !empty($i['id_finishing'])) $daftar = [['id_finishing' => $i['id_finishing']]];
            return collect($daftar)->pluck('id_finishing');
        })->filter()->map(fn ($v) => (int) $v)->unique()->values()->all();

    $jasaRows = $idJasaReq ? DB::table('jasa')->whereIn('id_jasa', $idJasaReq)->get()->keyBy('id_jasa') : collect();
    $produkRows = $idProdukReq ? DB::table('produk')->whereIn('id_produk', $idProdukReq)->get()->keyBy('id_produk') : collect();
    $finishingRows = $idFinishingReq ? DB::table('finishing')->whereIn('id_finishing', $idFinishingReq)->get()->keyBy('id_finishing') : collect();

    foreach ($request->items as $index => $item) {

        // Baris Jasa (bukan produk fisik) - tidak punya BOM/stok, harga
        // ditentukan manual oleh kasir (sama seperti biaya_desain), bukan
        // dari price list. Divalidasi terpisah lalu lanjut ke baris berikutnya.
        if (!empty($item['id_jasa'])) {
            $jasa = $jasaRows->get((int) $item['id_jasa']);
            if (!$jasa) {
                throw new \App\Exceptions\ValidasiNotaException('Jasa pada baris ' . ($index + 1) . ' tidak ditemukan.');
            }

            $qtyJasa = (float) ($item['qty_input'] ?? 0);
            // Role tanpa hak ubah harga (Operator Desain/Produksi): harga jasa SELALU dari master, kiriman client diabaikan.
            $hargaJasa = $hargaTerkunci
                ? (float) HargaService::getHargaJasaEfektif((int) $jasa->id_jasa, $qtyJasa)
                : max(0, (float) ($item['harga_satuan'] ?? 0));

            if ($qtyJasa <= 0) {
                throw new \App\Exceptions\ValidasiNotaException('Qty jasa pada baris ' . ($index + 1) . ' tidak valid.');
            }
            if ($hargaJasa <= 0) {
                throw new \App\Exceptions\ValidasiNotaException('Harga jasa pada baris ' . ($index + 1) . ' harus diisi.');
            }

            $itemsTervalidasi[] = [
                'is_jasa'    => true,
                'id_jasa'    => $jasa->id_jasa,
                // Penanda baris nota asal (mode EDIT SELISIH). Tanpa ini
                // server tidak tahu baris mana yang diperbarui dan barang
                // mana yang baru - setiap penyimpanan edit akan menambah
                // baris dobel.
                'id_detail' => isset($item['id_detail']) ? (int) $item['id_detail'] : null,
                'nama_file'  => $item['nama_file'] ?? $jasa->nama_jasa,
                'qty_input'  => $qtyJasa,
                'subtotal'   => $hargaJasa * $qtyJasa,
            ];
            continue;
        }

        $produk = $produkRows->get((int) ($item['id_produk'] ?? 0));

        if (!$produk) {
            throw new \App\Exceptions\ValidasiNotaException('Produk pada baris ' . ($index + 1) . ' tidak ditemukan.');
        }

        $p = (float) ($item['p'] ?? 0);
        $l = (float) ($item['l'] ?? 0);
        $qtyInput = (float) ($item['qty_input'] ?? 0);

        if ($qtyInput <= 0) {
            throw new \App\Exceptions\ValidasiNotaException('Qty pada baris ' . ($index + 1) . ' tidak valid.');
        }

        // Rumus qty-untuk-harga HARUS sama persis dengan hitungSubtotal() di
        // penjualan/create.blade.php, supaya harga yang ditagih konsisten
        // dengan yang ditampilkan ke operator sebelum disimpan.
        $qtyHitung = $produk->metode_hitung === 'ukuran'
            ? ($p * $l / 10000) * $qtyInput
            : $qtyInput;

        if ($qtyHitung <= 0) {
            throw new \App\Exceptions\ValidasiNotaException('Ukuran/qty pada baris ' . ($index + 1) . ' tidak valid.');
        }

        // Harga promo (kalau sedang aktif) atau price list biasa - HargaService
        // adalah sumber yang sama persis dipakai layar POS untuk menampilkan
        // harga saat operator mengetik qty. Price List di sini TETAP wajib
        // ada (guard rail supaya tidak ada produk terjual tanpa harga pernah
        // diatur sama sekali) - tapi angkanya sendiri TIDAK LAGI otomatis
        // ditambahkan ke subtotal (lihat catatan biaya_cetak di bawah),
        // murni jadi SARAN nilai default untuk Biaya Cetak yang dikirim
        // client dari hitungSubtotal() di penjualan/create.blade.php.
        $siapCetak = filter_var($item['siap_cetak'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $hargaSatuan = HargaService::getHargaEfektif($produk->id_produk, (int) $idGolongan, $qtyHitung, $siapCetak)['harga_jual'];

        if ($hargaSatuan <= 0) {
            throw new \App\Exceptions\ValidasiNotaException($siapCetak
                    ? 'Harga File Siap Cetak untuk "' . $produk->nama_produk . '" belum diatur (menu Harga File Siap Cetak) untuk golongan konsumen ini.'
                    : 'Harga untuk "' . $produk->nama_produk . '" belum diatur di Price List untuk golongan konsumen ini.');
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
        // File Siap Cetak = tanpa campur tangan tim desain: tidak ada biaya desain & tidak ada desainer/komisi.
        $biayaDesain = $siapCetak ? 0.0 : max(0, (float) ($item['biaya_desain'] ?? 0));
        $biayaCetak = max(0, (float) ($item['biaya_cetak'] ?? 0));

        // id_finishing opsional (produk boleh tanpa finishing). Divalidasi ke
        // tabel finishing supaya id yang dipalsukan dari client tidak lolos
        // ke foreign key transaksi_detail.id_finishing. biaya_finishing input
        // manual operator sama seperti biaya_desain (harga_default di master
        // Finishing cuma starting point, bukan harga final).
        // Finishing: DAFTAR (boleh lebih dari satu, mis. Laminasi + Kisscut). Bentuk lama (id_finishing tunggal)
        // tetap diterima dan diubah menjadi daftar satu elemen.
        $daftarFinishingMentah = is_array($item['finishing'] ?? null) ? $item['finishing'] : [];
        if (!$daftarFinishingMentah && !empty($item['id_finishing'])) {
            $daftarFinishingMentah = [[
                'id_finishing' => $item['id_finishing'], 'status_finishing' => $item['status_finishing'] ?? 'Standar',
                'biaya' => $item['biaya_finishing'] ?? 0, 'qty_pakai' => null, 'catatan' => null,
            ]];
        }
        if (count($daftarFinishingMentah) > 5) {
            throw new \App\Exceptions\ValidasiNotaException('Maksimal 5 finishing per item (baris ' . ($index + 1) . ').');
        }
        $daftarFinishing = [];
        $biayaFinishing = 0.0;
        foreach ($daftarFinishingMentah as $fin) {
            $idF = !empty($fin['id_finishing']) ? (int) $fin['id_finishing'] : null;
            $rowF = $idF ? $finishingRows->get($idF) : null;
            if (!$rowF) {
                throw new \App\Exceptions\ValidasiNotaException('Finishing pada baris ' . ($index + 1) . ' tidak ditemukan.');
            }
            $statusF = ($fin['status_finishing'] ?? $fin['status'] ?? 'Standar') === 'Custom' ? 'Custom' : 'Standar';
            $qtyPakai = isset($fin['qty_pakai']) && (float) $fin['qty_pakai'] > 0 ? (float) $fin['qty_pakai'] : null;

            // Qty acuan tarif finishing menurut metode_hitung finishing itu sendiri (sama dengan hitungan di form).
            $qtyF = match ($rowF->metode_hitung) {
                'luas' => $qtyHitung,
                'keliling' => $produk->metode_hitung === 'ukuran' ? (2 * ($p + $l) / 100) * $qtyInput : $qtyInput,
                // Potong Manual (LINEAR_GRID): tarif dihitung dari meter potong, BUKAN
                // dari qty pcs. lihat HargaService::hitungBiayaFinishingLinearGrid().
                'LINEAR_GRID' => $qtyHitung,
                default => $qtyPakai ?? $qtyInput,
            };

            // Ukuran media yang dipotong (dipakai mode LINEAR_GRID saja).
            $lebarMedia = 0.0;
            $tinggiMedia = 0.0;
            if ($rowF->metode_hitung === 'LINEAR_GRID') {
                // Biaya dihitung server dari dimensi potong yang dikirim form.
                // Kasir TIDAK boleh menentukan nominal sendiri untuk mode ini, karena
                // nominalnya turunan rumus (meter potong x tarif + minimum charge).
                $lebarPotong = (float) ($fin['lebar_potong'] ?? 0);
                $tinggiPotong = (float) ($fin['tinggi_potong'] ?? 0);

                // Ukuran MEDIA yang dipotong.
                //
                // Dua sumber, sesuai cara operator mengorder:
                //  - Produk ber-metode_hitung 'ukuran' (roll/banner): media =
                //    Panjang x Lebar yang diketik operator di form.
                //  - Produk ber-metode_hitung lain (mis. kertas/foto A3, dijual
                //    per lembar): form TIDAK punya field Panjang/Lebar, jadi
                //    operator mengisi ukuran lembar yang dipotong di panel
                //    Potong Manual dan mengirimnya lewat lebar_media/tinggi_media.
                // Tanpa sumber kedua, mode ini tidak mungkin dipakai untuk A3.
                $lebarMedia = (float) $p;
                $tinggiMedia = (float) $l;
                if ($lebarMedia <= 0 || $tinggiMedia <= 0) {
                    $lebarMedia = (float) ($fin['lebar_media'] ?? 0);
                    $tinggiMedia = (float) ($fin['tinggi_media'] ?? 0);
                }

                // Harga potong MANUAL: dipakai kalau finishing ini ber-spek
                // "Custom" DAN role pemakai punya hak mengubah harga. Rumus
                // LINEAR_GRID tetap jadi harga default, tapi ada kalanya
                // nominal potong tidak follow rumus (mis. potongan kecil sekali
                // dan operator tarifkan manual). Role tanpa hak ubah harga
                // tidak pernah bisa masuk jalur ini - angka dari browser
                // diabaikan, sama seperti finishing lain.
                $biayaManual = ($statusF === 'Custom' && !$hargaTerkunci)
                    ? max(0, (float) ($fin['biaya'] ?? 0))
                    : null;

                if ($biayaManual !== null && $biayaManual > 0) {
                    $biayaF = $biayaManual;
                } else {
                    $biayaF = HargaService::hitungBiayaFinishingLinearGrid(
                        $idF,
                        $lebarMedia,
                        $tinggiMedia,
                        $lebarPotong,
                        $tinggiPotong,
                        (float) $qtyHitung
                    );

                    if ($biayaF <= 0) {
                        throw new \App\Exceptions\ValidasiNotaException('Dimensi potong pada baris ' . ($index + 1) . ' tidak valid untuk finishing Potong Manual.');
                    }
                }
            } else {
                $standar = $qtyF > 0 ? round(HargaService::getHargaFinishingEfektif($idF, $qtyF) * $qtyF) : 0.0;

                // Standar (atau role tanpa hak ubah harga) = SELALU dihitung server dari master; hanya Custom oleh role berhak
                // yang boleh menentukan biaya sendiri.
                $biayaF = ($statusF === 'Standar' || $hargaTerkunci) ? $standar : max(0, (float) ($fin['biaya'] ?? 0));
            }

            $daftarFinishing[] = [
                'id_finishing' => $idF, 'status_finishing' => $statusF, 'qty_pakai' => $rowF->metode_hitung === 'quantity' ? ($qtyPakai ?? $qtyInput) : null,
                'biaya' => $biayaF, 'catatan' => isset($fin['catatan']) && trim((string) $fin['catatan']) !== '' ? mb_substr(trim((string) $fin['catatan']), 0, 255) : null,
                // Dimensi potong dibawa ke bawah supaya ikut tersimpan bersama
                // baris finishing-nya. Tanpa ini hanya `biaya` yang bertahan dan
                // angka Potong Manual tidak bisa ditelusuri ulang. Dipakai juga
                // oleh hitungUlangPotongManualDiDaftar() di layar kasir, jadi
                // jangan dibuang di sini.
                'lebar_potong' => $rowF->metode_hitung === 'LINEAR_GRID' ? (float) ($fin['lebar_potong'] ?? 0) : null,
                'tinggi_potong' => $rowF->metode_hitung === 'LINEAR_GRID' ? (float) ($fin['tinggi_potong'] ?? 0) : null,
                // Ukuran media yang dipotong. Untuk produk 'ukuran' sama
                // dengan Panjang/Lebar item; untuk produk per lembar (A3) ini
                // satu-satunya sumber ukuran media, jadi WAJIB ikut disimpan
                // - tanpa ini biaya potong tidak bisa ditelusuri ulang.
                'lebar_media' => $rowF->metode_hitung === 'LINEAR_GRID' ? ($lebarMedia > 0 ? $lebarMedia : null) : null,
                'tinggi_media' => $rowF->metode_hitung === 'LINEAR_GRID' ? ($tinggiMedia > 0 ? $tinggiMedia : null) : null,
            ];
            $biayaFinishing += $biayaF;
        }
        $idFinishing = $daftarFinishing[0]['id_finishing'] ?? null;

        // Role tanpa hak ubah harga: biaya cetak & desain juga dihitung server (finishing sudah dihitung di atas).
        // CATATAN PENTING - dua kunci yang TIDAK boleh digabung lagi:
        //
        // 1. $biayaCetak TETAP dikunci server untuk role tanpa akses harga. Harga produk
        //    adalah angka milik toko; mengizinkan client menimpanya sama dengan memberi
        //    harga gratis. Tidak ada exception apa pun di sini - nilai dari browser
        //    diabaikan total dan dihitung ulang dari master.
        //
        // 2. $biayaDesain TIDAK lagi ikut terkunci hanya karena role tidak bisa melihat
        //    harga. Biaya desain bukan harga barang: itu nilai jasa yang diinput orang
        //    yang mengerjakan desain (role Operator Desain), dan tidak pernah tampil di
        //    halaman manapun milik role itu. Pemisahan izinnya lewat
        //    roles.boleh_input_biaya_desain (lihat AksesHarga::bolehInputBiayaDesain),
        //    jadi Operator Desain boleh menginput biaya yang ia kerjakan tanpa ikut
        //    membuka seluruh harga produk/cetak/finishing.
        //
        // Kolomnya default 0 (gagal-terutup): role yang tidak dicentang di Pengaturan
        // Hak Akses tetap dipaksa 0 di sini, sama seperti sebelumnya.
        if ($hargaTerkunci) {
            $biayaCetak = round($hargaSatuan * $qtyHitung);
        }

        if (!$bolehInputBiayaDesain) {
            $biayaDesain = 0;
        }

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
        if ($siapCetak) {
            // Penanda ikut tercetak di SPK & tampil di daftar produksi tanpa mengubah layar-layar itu.
            $catatanProduksi = trim('[FILE SIAP CETAK - dari pelanggan, tanpa desain tim] ' . $catatanProduksi);
        }

        $itemsTervalidasi[] = [
            'is_jasa' => false,
            'id_produk' => $produk->id_produk,
            // Penanda baris nota asal (mode EDIT SELISIH) - lihat catatan di
            // cabang jasa di atas.
            'id_detail' => isset($item['id_detail']) ? (int) $item['id_detail'] : null,
            'nama_file' => $item['nama_file'] ?? '-',
            'thumbnail' => $thumbnail,
            'catatan_produksi' => $catatanProduksi !== '' ? $catatanProduksi : null,
            'p' => $p,
            'l' => $l,
            'qty_input' => $qtyInput,
            'qty_hitung' => $qtyHitung,
            'id_finishing' => $idFinishing,
            'finishing_list' => $daftarFinishing,
            'biaya_finishing' => $biayaFinishing,
            'biaya_cetak' => $biayaCetak,
            // Acuan price list (harga per unit x qty hitung) - dipakai HANYA untuk mencatat selisih bila kasir menimpa biaya cetak.
            'harga_standar' => round($hargaSatuan * $qtyHitung),
            'biaya_desain' => $biayaDesain,
            'file_siap_cetak' => $siapCetak,
            // Desainer yang menerapkan biaya desain + komisi menurut kelasnya (snapshot saat nota dibuat).
            // kunciDesainer(): kalau yang membuat nota adalah Operator Desain,
            // desainernya WAJIB dirinya sendiri - nilai id_desainer dari browser
            // diabaikan supaya komisi tidak bisa dialihkan ke nama lain.
            'id_desainer' => $biayaDesain > 0 ? \App\Services\DesainKomisiService::kunciDesainer(isset($item['id_desainer']) ? (int) $item['id_desainer'] : null) : null,
            'status_urgensi' => $statusUrgensi,
            'status_finishing' => collect($daftarFinishing)->contains('status_finishing', 'Custom') ? 'Custom' : $statusFinishing,
            'subtotal' => $biayaCetak + $biayaDesain + $biayaFinishing,
        ];
    }

        return $itemsTervalidasi;
    }

/**
 * Upload gambar acuan desain (thumbnail item order).
 *
 * Format yang diterima LEBAR: semua gambar umum (jpg, jpeg, png, webp, gif,
 * bmp, svg, tif, tiff, heic, avif) plus dokumen desain (pdf, cdr, ai, eps,
 * psd). Gambar dikompres di peramban sampai 200 KB supaya tetap jernih saat
 * dicetak di SPK; dokumen desain tidak bisa dikompres (browser tidak bisa
 * membukanya sebagai gambar) jadi dikirim apa adanya.
 *
 * Ekstensi dicek dari nama file asal, bukan dari tebakan MIME - format
 * desain seperti CDR punya MIME generik (application/octet-stream) yang
 * akan ditolak aturan mimes padahal berkasnya benar. Daftar formatnya satu
 * sumber kebenaran di App\Support\BerkasDesain.
 */
public function uploadThumbnail(Request $request)
{
    $file = $request->file('thumbnail');

    // Format & ukuran dicek sendiri (bukan aturan 'mimes'), karena aturan itu
    // menebak ekstensi dari MIME - dan format desain seperti CDR punya MIME
    // generik (application/octet-stream) yang akan ditolak padahal berkasnya
    // benar. Daftar formatnya satu sumber kebenaran di App\Support\BerkasDesain.
    //
    // Error-nya tetap dikembalikan sebagai error validasi Laravel (422 +
    // errors.thumbnail) supaya sama dengan jalur validasi lain di endpoint ini.
    $validator = \Illuminate\Support\Facades\Validator::make(
        ['thumbnail' => $file],
        [
            'thumbnail' => [
                'required',
                'file',
                function ($attribute, $value, $fail) {
                    if (!$value instanceof \Illuminate\Http\UploadedFile) {
                        return;
                    }

                    $ekstensi = \App\Support\BerkasDesain::ekstensi($value->getClientOriginalExtension());
                    if (!\App\Support\BerkasDesain::diizinkan($ekstensi)) {
                        $fail('Format file tidak didukung. Yang boleh: ' . \App\Support\BerkasDesain::daftarFormat() . '.');

                        return;
                    }

                    $maksKb = \App\Support\BerkasDesain::maksKilobyte($ekstensi);
                    if ($value->getSize() > $maksKb * 1024) {
                        $fail('Ukuran file terlalu besar (maksimal ' . number_format($maksKb / 1024, 0, ',', '.') . ' MB).');
                    }
                },
            ],
        ]
    );

    if ($validator->fails()) {
        throw new \Illuminate\Validation\ValidationException($validator);
    }

    $ekstensi = \App\Support\BerkasDesain::ekstensi($file->getClientOriginalExtension());

    // Nama berkas di disk diacak, tapi EKSTENSI ASLI dipertahankan. Kalau
    // ekstensinya hilang (mis. jadi .bin), peramban tidak bisa menampilkan
    // gambarnya dan file desainnya tidak bisa dibuka tim produksi.
    //
    // Catatan format: PDF sudah dirender jadi gambar di peramban (pdf.js),
    // jadi berkas yang tiba di sini untuk PDF hanya kalau peramban gagal
    // merendernya. Berkas desain lain (CDR, AI, EPS, PSD) tidak bisa
    // dirender di peramban maupun di server (tidak ada ImageMagick/
    // Ghostscript/LibreOffice di sini, dan thumbnail Windows hanya
    // menghasilkan ikon file generik - bukan isi desainnya). Berkas seperti
    // itu disimpan apa adanya sebagai sumber desain, dan SPK menampilkan
    // keterangan formatnya, bukan gambar rusak.
    $path = $file->storeAs(
        'order_thumbnails',
        \Illuminate\Support\Str::random(40) . '.' . $ekstensi,
        'public'
    );

    return response()->json([
        'status' => 'success',
        'path' => $path,
        'url' => asset('storage/' . $path),
        'bisa_ditampilkan' => \App\Support\BerkasDesain::bisaDitampilkan($ekstensi),
        'nama_asli' => $file->getClientOriginalName(),
    ]);
}

/**
 * Riwayat Nota - daftar transaksi penjualan dengan filter range tanggal.
 * Default: hanya menampilkan transaksi HARI INI jika tidak ada filter
 * tanggal yang dikirim. Murni query baca, tidak mengubah data apa pun.
 */
public function riwayat(Request $request)
{
    // P8I: tolak format tanggal yang rusak sebelum dipakai di
    // Carbon::parse() di bawah - tanpa ini input rusak melempar 500.
    $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date']);

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
        // Perf dataset besar: rentang sargable untuk memakai index
        // (tgl_order,status_nota) - whereDate()/DATE() mematikan index.
        ->when($from, fn ($q) => $q->where('tgl_order', '>=', $from . ' 00:00:00'))
        ->when($to, fn ($q) => $q->where('tgl_order', '<', \Carbon\Carbon::parse($to)->addDay()->format('Y-m-d') . ' 00:00:00'))
        ->when($filterKonsumen, fn ($q) => $q->where('transaksi.id_konsumen', $request->id_konsumen))
        ->select(
            'transaksi.*',
            'konsumen.nama_konsumen',
            'golongan_konsumen.nama_golongan',
            'karyawan.nama_karyawan as nama_kasir',
            'nota_edit_request.id_request as edit_request_id',
            'nota_edit_request.status as edit_request_status',
            'nota_edit_request.catatan_approver as edit_request_catatan',
            // Penanda untuk syarat edit selisih, diambil sebagai subquery
            // supaya daftar riwayat tidak jadi N+1 query (satu nota satu
            // query, bisa ratusan baris).
            DB::raw("EXISTS (SELECT 1 FROM transaksi_detail d WHERE d.id_transaksi = transaksi.id_transaksi AND d.status_spk IN ('Selesai', 'Cetak')) as ada_item_selesai"),
            DB::raw("EXISTS (SELECT 1 FROM transaksi_detail d WHERE d.id_transaksi = transaksi.id_transaksi AND d.id_produk IS NOT NULL) as ada_item_produksi")
        )
        ->orderByDesc('tgl_order')
        ->paginate(20)
        ->withQueryString();

    $isAdmin = optional(optional($request->user())->role)->nama_role === 'Administrator';
    $pendingEditCount = $isAdmin ? DB::table('nota_edit_request')->where('status', 'Menunggu')->count() : 0;

    // Penanda "boleh diedit" per baris - sumbernya sama dengan yang dipakai
    // server saat menyimpan (NotaEditService), jadi tombol Edit di daftar dan
    // penolakan saat disimpan tidak bisa berbeda pendapat.
    $transaksis->getCollection()->each(function ($t) {
        $t->boleh_edit = \App\Services\NotaEditService::penandaRiwayat($t)['boleh'];
        $t->alasan_edit = \App\Services\NotaEditService::penandaRiwayat($t)['alasan'];
    });

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
            'produk.spk_lengkap',
            'jasa.nama_jasa',
            'finishing.nama_finishing'
        )
        ->get();

    $finishingItems = \App\Services\FinishingItemService::untukDetail($items->pluck('id_detail'));

    return view('penjualan.show', compact('transaksi', 'items', 'finishingItems'));
}

/**
 * Tambah item susulan ke nota yang sudah berjalan.
 *
 * Aturan (sesuai permintaan bisnis):
 *   - hanya operator yang membuat nota itu (transaksi.id_user_kasir)
 *   - hanya di hari yang sama (DATE(tgl_order) = hari ini)
 *   - hanya nota yang transaksinya sudah dibuka kasir (Diterima Kasir)
 *     dan masih Aktif
 *   - item baru masuk ke SPK sebagai 'Antre' (status_spk) dan ikut
 *     tercatat di kartu_stok + jurnal seperti nota awal
 *
 * Jika nota lama sudah Lunas, total_bayar naik sementara pembayaran lama
 * tetap. Status pembayaran otomatis turun jadi 'DP'/'Belum Bayar' karena
 * StatusPembayaran::sqlCase() membandingkan total_bayar vs terbayar, dan
 * rincian "Terbayar/Sisa" di Monitoring Kasir sudah saya perbaiki agar
 * memakai status turunan, jadi kasir melihat sisa = hanya tambahan.
 *
 * Dipisah dari store() supaya alur Input Order yang ada tidak disentuh.
 */
public function tambahItemSusulan($id, Request $request)
{
    // Alur "Tambah Order Susulan" memakai LAYAR Input Pesanan yang sama
    // (penjualan/create.blade.php mode susulan), jadi kasir merakit item
    // dengan form yang sama persis dengan order baru.
    //
    // Endpoint ini lalu meneruskan request ke store() dengan penanda
    // susulan_id. store() memakai jalur validasi item, penulisan baris
    // detail, pemotongan stok, dan posting jurnal yang SAMA dengan nota
    // baru - jadi tidak ada dua implementasi aturan harga yang bisa
    // berbeda. Yang membedakan cuma sumber data nota (konsumen, golongan,
    // tanggal, tipe order, nomor nota) dan penandaan baris sebagai susulan.
    //
    // Syarat addition (dikunci di store() dan diuji TambahItemSusulanTest):
    //   - hanya operator yang membuat nota itu (transaksi.id_user_kasir)
    //   - hanya di hari yang sama (DATE(tgl_order) = hari ini)
    //   - hanya nota yang transaksinya sudah dibuka kasir (Diterima Kasir)
    //     dan masih Aktif
    //   - item baru masuk ke SPK sebagai 'Antre' (status_spk) dan ikut
    //     tercatat di kartu_stok + jurnal seperti nota awal
    //
    // Jika nota lama sudah Lunas, total_bayar naik sementara pembayaran lama
    // tetap. Status pembayaran otomatis turun jadi 'DP'/'Belum Bayar' karena
    // StatusPembayaran::sqlCase() membandingkan total_bayar vs terbayar, dan
    // rincian "Terbayar/Sisa" di Monitoring Kasir sudah diperbaiki agar
    // memakai status turunan, jadi kasir melihat sisa = hanya tambahan.
    $request->merge(['susulan_id' => (int) $id]);

    return $this->store($request);
}

/**
 * Item hasil produksi (punya id_produk). Bahan, jasa, dan item custom dari Penjualan dan Jasa langsung berstatus
 * Selesai sejak nota dibuat karena tidak lewat produksi, jadi status itu tidak boleh mengunci koreksi/pembatalan.
 */
/**
 * HPP item yang SPK-nya sudah dibatalkan TANPA pengembalian bahan (baris snapshot pemakaian masih ada, karena
 * snapshot dihapus saat bahan dikembalikan). Bahan itu sudah terpakai, jadi HPP-nya tetap biaya walau notanya batal.
 */
private static function hppItemBatalTanpaKembali($items): float
{
    $nilai = 0.0;
    foreach ($items as $i) {
        if ($i->status_spk === 'Batal' && !empty($i->id_produk)
            && DB::table('transaksi_detail_bahan_pakai')->where('id_detail', $i->id_detail)->exists()) {
            $nilai += (float) $i->hpp_snapshot;
        }
    }

    return round($nilai, 2);
}

private static function adaItemProduksi($items): bool
{
    return $items->contains(fn ($i) => !empty($i->id_produk));
}

private static function itemProduksiSelesai($items): ?object
{
    return $items->first(fn ($i) => !empty($i->id_produk) && $i->status_spk === 'Selesai');
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

    $items = DB::table('transaksi_detail')->where('id_transaksi', $id)->get();

    if (self::adaItemProduksi($items) && $transaksi->status_collecting === 'Sudah Diambil') {
        return back()->with('error', 'Nota ini tidak bisa dibatalkan - barang sudah diambil konsumen (Collecting). Gunakan proses retur manual kalau memang diperlukan.');
    }

    $sudahSelesai = self::itemProduksiSelesai($items);
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
    // P0-2: jalan persis SEKALI per nota - sebelumnya tidak ada guard, jadi
    // pembatalan notanya sendiri tidak idempoten (stok/jurnal bisa dobel
    // kalau eksekusi terpicu dua kali).
    $transaksi = DB::table('transaksi')->where('id_transaksi', $id)->lockForUpdate()->first();
    if (!$transaksi) {
        throw new \RuntimeException("Nota #{$id} tidak ditemukan - pembatalan tidak bisa dieksekusi.");
    }
    if ($transaksi->status_nota === 'Dibatalkan') {
        return []; // sudah dibatalkan sebelumnya - tidak ada yang perlu dilakukan
    }

    $items = DB::table('transaksi_detail')->where('id_transaksi', $id)->get();

    // Keadaan bisa berubah selama pengajuan menunggu: item produk selesai diproduksi atau barang sudah diambil.
    // Pemeriksaan saat pengajuan diulang di sini (setelah nota dikunci); bila tidak lolos, pengajuan tetap Menunggu
    // untuk ditolak, bukan membatalkan nota yang barangnya sudah di tangan pelanggan.
    if (self::adaItemProduksi($items) && $transaksi->status_collecting === 'Sudah Diambil') {
        throw new \RuntimeException("Nota {$transaksi->nomor_nota} tidak bisa dibatalkan: barangnya sudah diambil pelanggan sejak pengajuan dibuat. Tolak pengajuan ini; gunakan retur manual bila perlu.");
    }
    if ($selesai = self::itemProduksiSelesai($items)) {
        throw new \RuntimeException("Nota {$transaksi->nomor_nota} tidak bisa dibatalkan: item \"{$selesai->nama_file}\" sudah selesai diproduksi sejak pengajuan dibuat. Tolak pengajuan ini.");
    }

    $kembalikanStok = (bool) ($payload['kembalikan_stok'] ?? false);
    $hppTerpakai = self::hppItemBatalTanpaKembali($items);
    $ringkasanStok = [];

    foreach ($items as $item) {
        if ($item->status_spk === 'Batal') {
            continue;
        }

        // P9: tolak transisi SPK yang tidak sah ke Batal.
        \App\Services\StatusTransitionGuard::validateStatusSpk((string) $item->status_spk, 'Batal');

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

    // P0-2: jurnal pendapatan/HPP nota DIBALIK saat pembatalan dieksekusi
    // (sesuai docs/PANDUAN_PENGGUNAAN.md: "jurnal dibalik"). Baris jurnal
    // PEMBAYARAN (referensi <nota>-BYR-*) sengaja TIDAK disentuh oleh
    // reversePenjualan() - uang yang sudah benar-benar diterima tetap
    // sah di buku besar; nota batal berarti pendapatannya hangus, BUKAN
    // uangnya hilang. Konsekuensinya piutang nota batal yang sudah
    // dibayar penuh berubah jadi saldo kas/bank atas nota yang batal -
    // ditindaklanjuti (retur uang / deposit) lewat proses manual &
    // terlihat di Laporan Piutang yang sudah mengecualikan nota batal.
    // Bahan tidak dikembalikan = sudah terpakai: HPP-nya tetap biaya (lihat reversePenjualan).
    AccountingService::reversePenjualan($transaksi->nomor_nota, $kembalikanStok);
    if ($kembalikanStok) {
        AccountingService::postHppBahanTerpakai($transaksi->nomor_nota, $hppTerpakai);
    }
    $this->kreditkanPembayaranNotaBatal($transaksi);

    // P9: guard status nota - Dibatalkan terminal, tidak boleh kembali ke Aktif.
    \App\Services\StatusTransitionGuard::validateStatusNota(
        (string) DB::table('transaksi')->where('id_transaksi', $id)->value('status_nota'),
        'Dibatalkan'
    );

    DB::table('transaksi')->where('id_transaksi', $id)->update([
        'status_nota' => 'Dibatalkan',
        // status_order mengikuti agar nota batal keluar dari antrean kasir/SPK.
        'status_order' => 'Dibatalkan',
        'alasan_batal_nota' => $payload['alasan'] ?? null,
        'tgl_dibatalkan_nota' => now(),
        'id_user_pembatal_nota' => Auth::id(),
    ]);

    return $ringkasanStok;
}

/**
 * Uang yang sudah masuk untuk nota yang dibatalkan tidak boleh menggantung: menjadi saldo deposit
 * pelanggan (bisa dipakai di nota pengganti), dengan jurnal Piutang -> Hutang Deposit dan jejak audit.
 * Dipanggil di dalam transaksi pembatalan, tepat sekali per nota (pembatalan dijaga status_nota).
 */
private function kreditkanPembayaranNotaBatal(object $transaksi): void
{
    $terbayar = round((float) DB::table('pembayaran_riwayat')->where('id_transaksi', $transaksi->id_transaksi)->sum('nominal'), 2);
    if ($terbayar <= 0) {
        return;
    }

    $data = [
        'id_konsumen' => $transaksi->id_konsumen,
        'tipe' => 'Setoran',
        'nominal' => $terbayar,
        'metode_bayar' => 'Kredit Nota Batal',
        'keterangan' => "Uang nota {$transaksi->nomor_nota} yang dibatalkan, menjadi saldo pelanggan",
        'tgl_deposit' => now()->toDateString(),
        'id_transaksi' => $transaksi->id_transaksi,
        'id_user' => Auth::id(),
        'created_at' => now(),
        'updated_at' => now(),
    ];
    $idDeposit = DB::table('konsumen_deposit')->insertGetId($data);
    AccountingService::postKreditNotaBatal($transaksi->nomor_nota, $terbayar);
    \App\Services\AuditLogger::log('created', 'konsumen_deposit', $idDeposit, null, $data, "Kredit pelanggan Rp " . number_format($terbayar, 0, ',', '.') . " dari nota batal {$transaksi->nomor_nota}");
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
    // Mode langsung: bila fitur approval edit dinonaktifkan di
    // Pengaturan Aplikasi, operator yang membuat nota boleh langsung
    // mengoreksi tanpa persetujuan - eksekusi yang sama dengan
    // applyApprovedEdit, cuma tidak lewat antrian approval.
    if (!\App\Models\AppSetting::current()->approval_edit_nota) {
        $transaksi = DB::table('transaksi')->where('id_transaksi', $id)->first();
        if (!$transaksi) {
            abort(404);
        }
        if ((int) $transaksi->id_user_kasir !== (int) Auth::id()) {
            return back()->with('error', 'Hanya operator yang membuat nota ini yang boleh mengedit.');
        }
        if ($transaksi->status_nota !== 'Aktif' || $transaksi->status_order !== 'Diterima Kasir') {
            return back()->with('error', 'Nota ini tidak bisa diedit (status tidak memenuhi syarat).');
        }
        if (Carbon::parse($transaksi->tgl_order)->toDateString() !== now()->toDateString()) {
            return back()->with('error', 'Edit hanya boleh dilakukan di hari yang sama dengan nota.');
        }

        return $this->editLangsung($id);
    }

    $request->validate(['alasan' => 'required|string|max:1000']);

    // Hardening P1-2: cek "sudah ada permintaan berjalan?" lalu insert SEBELUMNYA
    // tidak dikunci sama sekali - dua klik/tab bersamaan bisa sama-sama lolos
    // cek (belum ada yang commit) dan menghasilkan DUA permintaan Menunggu
    // untuk nota yang sama. transaksi.id_transaksi PASTI ada untuk nota yang
    // valid, jadi mengunci baris itu (bukan baris nota_edit_request yang belum
    // tentu ada) cukup untuk membuat cek+insert ini serial per-nota.
    $pesanError = null;
    DB::transaction(function () use ($id, $request, &$pesanError) {
        $transaksi = DB::table('transaksi')->where('id_transaksi', $id)->lockForUpdate()->first();
        if (!$transaksi) {
            $pesanError = 'Nota tidak ditemukan.';

            return;
        }
        if ($transaksi->status_nota === 'Dibatalkan') {
            $pesanError = 'Nota ini sudah dibatalkan, tidak perlu diedit.';

            return;
        }

        $adaPermintaanBerjalan = DB::table('nota_edit_request')
            ->where('id_transaksi', $id)
            ->whereIn('status', ['Menunggu', 'Disetujui'])
            ->exists();
        if ($adaPermintaanBerjalan) {
            $pesanError = 'Nota ini sudah punya permintaan edit yang masih berjalan (menunggu/sudah disetujui).';

            return;
        }

        DB::table('nota_edit_request')->insert([
            'id_transaksi' => $id,
            'id_user_requester' => Auth::id(),
            'alasan' => $request->input('alasan'),
            'status' => 'Menunggu',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    });

    if ($pesanError) {
        return back()->with('error', $pesanError);
    }

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

    // Hardening P1-2: lock + cek ulang status di dalam transaction supaya
    // klik ganda tidak bisa menimpa keputusan (approve/reject) yang sudah
    // tercatat lebih dulu tanpa terdeteksi.
    $pesanError = null;
    DB::transaction(function () use ($id, $request, &$pesanError) {
        $req = DB::table('nota_edit_request')->where('id_request', $id)->lockForUpdate()->first();
        if (!$req) {
            $pesanError = 'Permintaan tidak ditemukan.';

            return;
        }
        if ($req->status !== 'Menunggu') {
            $pesanError = 'Permintaan ini sudah diproses sebelumnya (status: ' . $req->status . ').';

            return;
        }

        DB::table('nota_edit_request')->where('id_request', $id)->update([
            'status' => 'Disetujui',
            'id_user_approver' => Auth::id(),
            'catatan_approver' => $request->input('catatan'),
            'tgl_keputusan' => now(),
            'updated_at' => now(),
        ]);
    });

    if ($pesanError) {
        return back()->with('error', $pesanError);
    }

    return back()->with('success', 'Permintaan edit disetujui. Kasir sekarang bisa memproses koreksi nota ini.');
}

public function rejectEditNota(Request $request, $id)
{
    if ($redirect = $this->pastikanAdministrator($request)) {
        return $redirect;
    }

    $request->validate(['catatan' => 'required|string|max:1000']);

    // Hardening P1-2: lihat catatan sama di approveEditNota().
    $pesanError = null;
    DB::transaction(function () use ($id, $request, &$pesanError) {
        $req = DB::table('nota_edit_request')->where('id_request', $id)->lockForUpdate()->first();
        if (!$req) {
            $pesanError = 'Permintaan tidak ditemukan.';

            return;
        }
        if ($req->status !== 'Menunggu') {
            $pesanError = 'Permintaan ini sudah diproses sebelumnya (status: ' . $req->status . ').';

            return;
        }

        DB::table('nota_edit_request')->where('id_request', $id)->update([
            'status' => 'Ditolak',
            'id_user_approver' => Auth::id(),
            'catatan_approver' => $request->input('catatan'),
            'tgl_keputusan' => now(),
            'updated_at' => now(),
        ]);
    });

    if ($pesanError) {
        return back()->with('error', $pesanError);
    }

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
    public function editLangsung($id)
    {
        $transaksi = DB::table('transaksi')->where('id_transaksi', $id)->first();
        if (!$transaksi) {
            abort(404);
        }

        if ((int) $transaksi->id_user_kasir !== (int) Auth::id()) {
            return back()->with('error', 'Hanya operator yang membuat nota ini yang boleh mengedit.');
        }
        if ($transaksi->status_nota !== 'Aktif' || $transaksi->status_order !== 'Diterima Kasir') {
            return back()->with('error', 'Nota ini tidak bisa diedit (status tidak memenuhi syarat).');
        }
        if (Carbon::parse($transaksi->tgl_order)->toDateString() !== now()->toDateString()) {
            return back()->with('error', 'Edit hanya boleh dilakukan di hari yang sama dengan nota.');
        }

        $idRequest = DB::table('nota_edit_request')->insertGetId([
            'id_transaksi' => $id,
            'id_user_requester' => Auth::id(),
            'alasan' => 'Edit langsung (approval dinonaktifkan di Pengaturan Aplikasi)',
            'status' => 'Disetujui',
            'id_user_approver' => Auth::id(),
            'catatan_approver' => 'Auto-disetujui (fitur approval edit nonaktif)',
            'tgl_keputusan' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->applyApprovedEdit($idRequest);
    }

public function applyApprovedEdit($id)
{
    // Hardening P1-2: SEBELUMNYA seluruh pengecekan status (nota_edit_request,
    // transaksi, item sudah Selesai, sudah Diambil) dilakukan DI LUAR
    // transaction dan TANPA lockForUpdate() - klik ganda tombol "Terapkan
    // Koreksi" (atau retry jaringan) bisa membuat DUA request sama-sama lolos
    // pengecekan sebelum salah satunya sempat menulis, sehingga
    // StokReversalService::kembalikan() dan AccountingService::reversePenjualan()
    // terpanggil DUA KALI untuk nota yang sama (stok & jurnal ganda). Sekarang
    // baris nota_edit_request DAN transaksi dikunci lebih dulu, seluruh
    // pengecekan diulang SETELAH lock didapat - pola yang sama persis dengan
    // ProduksiController::cancel().
    $pesanError = null;
    $sudahDibatalkanSebelumnya = false;
    $transaksiHasil = null;

    DB::transaction(function () use ($id, &$pesanError, &$sudahDibatalkanSebelumnya, &$transaksiHasil) {
        $req = DB::table('nota_edit_request')->where('id_request', $id)->lockForUpdate()->first();
        if (!$req) {
            $pesanError = 'Permintaan edit tidak ditemukan.';

            return;
        }
        if ($req->status !== 'Disetujui') {
            $pesanError = 'Permintaan ini belum disetujui Administrator (status: ' . $req->status . ').';

            return;
        }

        $transaksi = DB::table('transaksi')->where('id_transaksi', $req->id_transaksi)->lockForUpdate()->first();
        if (!$transaksi) {
            $pesanError = 'Nota tidak ditemukan.';

            return;
        }
        if ($transaksi->status_nota === 'Dibatalkan') {
            DB::table('nota_edit_request')->where('id_request', $id)->update(['status' => 'Selesai', 'tgl_selesai' => now()]);
            $sudahDibatalkanSebelumnya = true;

            return;
        }

        $items = DB::table('transaksi_detail')->where('id_transaksi', $transaksi->id_transaksi)->get();
        $sudahSelesai = self::itemProduksiSelesai($items);
        if ($sudahSelesai) {
            $pesanError = 'Nota ini tidak bisa dikoreksi - ada item ("' . $sudahSelesai->nama_file . '") yang sudah Selesai diproduksi.';

            return;
        }
        if (self::adaItemProduksi($items) && $transaksi->status_collecting === 'Sudah Diambil') {
            $pesanError = 'Nota ini tidak bisa dikoreksi - barang sudah diambil konsumen.';

            return;
        }

        $hppTerpakai = self::hppItemBatalTanpaKembali($items);
        foreach ($items as $item) {
            if ($item->status_spk === 'Batal') {
                continue;
            }

            // P9: tolak transisi SPK yang tidak sah dari ke Batal.
            \App\Services\StatusTransitionGuard::validateStatusSpk((string) $item->status_spk, 'Batal');

            DB::table('transaksi_detail')->where('id_detail', $item->id_detail)->update(['status_spk' => 'Batal']);
            StokReversalService::kembalikan(
                $item,
                'EDIT-NOTA-' . $transaksi->id_transaksi,
                'Koreksi nota #' . $transaksi->nomor_nota . ' (permintaan edit #' . $req->id_request . ') - stok dikembalikan'
            );
        }

        AccountingService::reversePenjualan($transaksi->nomor_nota);
        AccountingService::postHppBahanTerpakai($transaksi->nomor_nota, $hppTerpakai);
        $this->kreditkanPembayaranNotaBatal($transaksi);

        // P9: guard status nota.
        \App\Services\StatusTransitionGuard::validateStatusNota(
            (string) $transaksi->status_nota,
            'Dibatalkan'
        );

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

        $transaksiHasil = $transaksi;
        $transaksiHasil->penjualanLangsung = !self::adaItemProduksi($items);
    });

    if ($sudahDibatalkanSebelumnya) {
        return redirect()->route('penjualan.riwayat')->with('error', 'Nota ini sudah dibatalkan/dikoreksi sebelumnya.');
    }
    if ($pesanError) {
        return back()->with('error', $pesanError);
    }

    // Nota Penjualan dan Jasa (tanpa item produk) diganti lewat Penjualan dan Jasa juga, bukan Input Order.
    return redirect()->route($transaksiHasil->penjualanLangsung ? 'penjualan-barang.create' : 'penjualan.create', ['id_konsumen' => $transaksiHasil->id_konsumen])
        ->with('success', 'Nota lama sudah dibatalkan, stok & jurnal sudah dikembalikan. Silakan buat nota pengganti dengan data yang benar (nota lama tetap bisa dilihat di Riwayat untuk referensi).');
}
}
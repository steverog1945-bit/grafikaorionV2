<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{
    AuthController,
    AksesController,
    KaryawanController,
    UserController,
    MesinController,
    BahanBakuController,
    KategoriController,
    KonsumenController,
    GolonganController,
    SatuanController,
    SupplierController,
    BankController,
    ProdukController,
    SettingHargaController,
    PembelianBahanController,
    KedatanganBahanController,
    KartuStokController,
    FinishingController
};

use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\Api\PenjualanApiController;
use App\Http\Controllers\Kasir\KasirController;
use App\Http\Controllers\SpkController;
use App\Http\Controllers\ProduksiController;
use App\Http\Controllers\CollectingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\HargaPromoController;



/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');



/*
|--------------------------------------------------------------------------
| AUTHENTICATED AREA
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'menu.access'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');



    /*
    |--------------------------------------------------------------------------
    | MASTER DATA
    |--------------------------------------------------------------------------
    */

    Route::resource('kategori', KategoriController::class);
    Route::resource('supplier', SupplierController::class);
    Route::resource('bank', BankController::class);
    Route::resource('konsumen', KonsumenController::class);
    Route::resource('golongan', GolonganController::class);
    Route::resource('satuan', SatuanController::class);
    Route::resource('mesin', MesinController::class);
    Route::resource('produk', ProdukController::class);
    Route::resource('finishing', FinishingController::class)->except(['create', 'edit', 'show']);

    Route::resource('merk', \App\Http\Controllers\MerkController::class)->except(['create', 'edit', 'show']);
    Route::resource('jenis-bahan', \App\Http\Controllers\JenisBahanController::class)->except(['create', 'edit', 'show']);
    Route::resource('jasa', \App\Http\Controllers\JasaController::class)->except(['create', 'edit', 'show']);
    Route::resource('gudang', \App\Http\Controllers\GudangController::class)->except(['create', 'edit', 'show']);
    Route::get('/konsumen-deposit', [\App\Http\Controllers\KonsumenDepositController::class, 'index'])->name('konsumen-deposit.index');
    Route::post('/konsumen-deposit', [\App\Http\Controllers\KonsumenDepositController::class, 'store'])->name('konsumen-deposit.store');



    /*
    |--------------------------------------------------------------------------
    | PENJUALAN
    |--------------------------------------------------------------------------
    */

    Route::get('/penjualan/kasir', [PenjualanController::class, 'create'])
        ->name('penjualan.create');

    Route::post('/penjualan/store', [PenjualanController::class, 'store'])
        ->name('penjualan.store');

    Route::get('/penjualan/riwayat', [PenjualanController::class, 'riwayat'])
        ->name('penjualan.riwayat');

    Route::get('/penjualan/{id}', [PenjualanController::class, 'show'])
        ->name('penjualan.show');

    Route::post('/penjualan/upload-thumbnail', [PenjualanController::class, 'uploadThumbnail'])
        ->name('penjualan.upload-thumbnail');

    Route::post('/konsumen/store-ajax', [KonsumenController::class, 'storeAjax']);



    /*
    |--------------------------------------------------------------------------
    | API PENJUALAN
    |--------------------------------------------------------------------------
    */

    Route::prefix('api/penjualan')->group(function () {

        Route::get('/produk/{id}',
            [PenjualanApiController::class, 'getDetailProduk']);

        Route::get('/get-harga/{id_produk}/{id_golongan}/{qty}',
            [PenjualanApiController::class, 'getHarga']);

        Route::get('/check-stok',
            [PenjualanApiController::class, 'checkStok']);
    });



    /*
    |--------------------------------------------------------------------------
    | KARTU STOK
    |--------------------------------------------------------------------------
    */

    Route::get('/kartu-stok',
        [KartuStokController::class, 'index'])->name('kartu-stok.index');

    Route::post('/kartu-stok/adjustment',
        [KartuStokController::class, 'storeAdjustment'])->name('kartu-stok.adjustment');

    Route::get('/kartu-stok/{id}',
        [KartuStokController::class, 'detail']);



    /*
    |--------------------------------------------------------------------------
    | KASIR
    |--------------------------------------------------------------------------
    */

    Route::prefix('kasir')->name('kasir.')->group(function () {

        Route::get('/monitoring', [KasirController::class, 'index'])->name('index');

        Route::get('/check-pembayaran',
            [KasirController::class, 'checkNewTransaction']);

        Route::get('/bayar-form/{id}',
            [KasirController::class, 'bayarForm'])->name('bayarForm');

        Route::post('/proses-bayar',
            [KasirController::class, 'prosesBayar'])->name('prosesBayar');

        Route::get('/waiting-list',
            [KasirController::class, 'waitingList'])->name('waiting');

        Route::get('/kelola-kas',
            [KasirController::class, 'kelolaKas'])->name('kelola');

        Route::get('/store-kas',
            [KasirController::class, 'storeKas']);

        Route::post('/proses-closing',
            [KasirController::class, 'prosesClosing'])->name('proses-closing');

        Route::get('/closing-preview',
            [KasirController::class, 'previewClosing'])->name('preview-closing');

        Route::post('/closing-simpan',
            [KasirController::class, 'simpanClosing'])->name('simpan-closing');

        Route::get('/print-opsi/{id}',
            [KasirController::class, 'printOpsi'])->name('print-opsi');

        Route::get('/print-closing/{id}/{format}',
            [KasirController::class, 'printReport'])->name('print-closing');

        Route::get('/history/{id}',
            [KasirController::class, 'getHistory'])->name('history');
    });

        /*
        |--------------------------------------------------------------------------
        | PEMBELIAN BAHAN
        |--------------------------------------------------------------------------
        */
        // Pastikan strukturnya seperti ini:
    Route::patch('/kedatangan-bahan/validate/{id}', [KedatanganBahanController::class, 'validateStatus'])
     ->name('kedatangan-bahan.validate');
     Route::post('pembelian-bahan/bulk-delete', [PembelianBahanController::class, 'bulkDelete'])
            ->name('pembelian-bahan.bulk-delete');
        // Contoh Route Pelunasan
        // Menggunakan match agar bisa POST maupun PATCH
Route::match(['get', 'post', 'patch'], '/pembelian-bahan/pelunasan/{id}', [PembelianBahanController::class, 'pelunasan'])
      ->name('pembelian.pelunasan');
        // HANYA GUNAKAN SATU ROUTE INI UNTUK AJAX
        Route::get('pembelian-bahan/kedatangan-detail', [PembelianBahanController::class, 'kedatanganDetail'])
            ->name('pembelian-bahan.kedatangan-detail');
            
        Route::resource('pembelian-bahan', PembelianBahanController::class);
        // PENTING: Hapus atau komentari bagian "AJAX PEMBELIAN" yang mengarah ke KedatanganBahanController 
        // agar tidak terjadi tabrakan URL.
    /*
    |--------------------------------------------------------------------------
    | KEDATANGAN BAHAN
    |--------------------------------------------------------------------------
    */

    Route::resource('kedatangan-bahan', KedatanganBahanController::class);

    Route::post('kedatangan-bahan/bulk/delete',
        [KedatanganBahanController::class, 'bulkDelete'])
        ->name('kedatangan-bahan.bulk-delete');

    Route::get('/ajax/bahan',
        [KedatanganBahanController::class, 'ajaxBahan'])
        ->name('ajax.bahan');



    /*
    |--------------------------------------------------------------------------
    | SETTING HARGA
    |--------------------------------------------------------------------------
    */

    Route::get('/setting-harga',
        [SettingHargaController::class, 'index'])
        ->name('setting-harga.index');

    Route::post('/setting-harga/update-bulk',
        [SettingHargaController::class, 'update_bulk'])
        ->name('setting-harga.update_bulk');

    Route::delete('/setting-harga/{id_produk}',
        [SettingHargaController::class, 'destroy'])
        ->name('setting-harga.destroy');



    /*
    |--------------------------------------------------------------------------
    | KARYAWAN
    |--------------------------------------------------------------------------
    */

    Route::get('/karyawan',
        [KaryawanController::class, 'index']);

    Route::post('/karyawan/store',
        [KaryawanController::class, 'store'])
        ->name('karyawan.store');

    Route::put('/karyawan/update/{id}',
        [KaryawanController::class, 'update'])
        ->name('karyawan.update');

    Route::delete('/karyawan/delete/{id}',
        [KaryawanController::class, 'destroy']);



    /*
    |--------------------------------------------------------------------------
    | USERS
    |--------------------------------------------------------------------------
    */

    Route::get('/users',
        [UserController::class, 'index']);

    Route::post('/users/store',
        [UserController::class, 'store'])
        ->name('users.store');

    Route::put('/users/update/{id}',
        [UserController::class, 'update']);

    Route::delete('/users/delete/{id}',
        [UserController::class, 'destroy']);



    /*
    |--------------------------------------------------------------------------
    | BAHAN BAKU
    |--------------------------------------------------------------------------
    */

    Route::get('/bahan-baku',
        [BahanBakuController::class, 'index'])
        ->name('bahan.index');

    Route::post('/bahan-baku',
        [BahanBakuController::class, 'store'])
        ->name('bahan.store');

    Route::put('/bahan-baku/{id_bahan}',
        [BahanBakuController::class, 'update'])
        ->name('bahan.update');

    Route::delete('/bahan-baku/{id_bahan}',
        [BahanBakuController::class, 'destroy'])
        ->name('bahan.destroy');

    Route::post('/bahan-baku/bulk-delete',
        [BahanBakuController::class, 'bulkDelete'])
        ->name('bahan.bulk-delete');

    Route::patch('/bahan-baku/{id_bahan}/toggle-status',
        [BahanBakuController::class, 'toggleStatus'])
        ->name('bahan.toggle-status');

    Route::get('/produk/{id}', [ProdukController::class, 'show']);

    /*
    |--------------------------------------------------------------------------
    | SPK & PRODUKSI
    |--------------------------------------------------------------------------
    */

    Route::get('/spk', [SpkController::class, 'index'])->name('spk.index');
    Route::patch('/spk/{id}/urgent', [SpkController::class, 'toggleUrgent'])->name('spk.toggle-urgent');
    Route::post('/spk/bulk-urgent', [SpkController::class, 'bulkUrgent'])->name('spk.bulk-urgent');
    Route::post('/spk/bulk-ambil', [SpkController::class, 'bulkAmbil'])->name('spk.bulk-ambil');
    Route::patch('/spk/{id}/ambil', [SpkController::class, 'ambilPekerjaan'])->name('spk.ambil');
    Route::get('/spk/{id}/print/{format?}', [SpkController::class, 'printSpk'])->name('spk.print');

    Route::get('/produksi', [ProduksiController::class, 'index'])->name('produksi.index');
    Route::patch('/produksi/{id}/simpan-qty', [ProduksiController::class, 'simpanQty'])->name('produksi.simpan-qty');
    Route::patch('/produksi/{id}/selesai', [ProduksiController::class, 'selesai'])->name('produksi.selesai');
    Route::patch('/produksi/{id}/cancel', [ProduksiController::class, 'cancel'])->name('produksi.cancel');

    Route::get('/collecting', [CollectingController::class, 'index'])->name('collecting.index');
    Route::patch('/collecting/{id}/proses', [CollectingController::class, 'proses'])->name('collecting.proses');

    Route::get('/laporan/order', [LaporanController::class, 'order'])->name('laporan.order');
    Route::get('/laporan/produksi', [LaporanController::class, 'produksi'])->name('laporan.produksi');
    Route::get('/laporan/kas', [LaporanController::class, 'kas'])->name('laporan.kas');

    Route::get('/harga-promo', [HargaPromoController::class, 'index'])->name('harga-promo.index');
    Route::post('/harga-promo', [HargaPromoController::class, 'store'])->name('harga-promo.store');
    Route::put('/harga-promo/{id}', [HargaPromoController::class, 'update'])->name('harga-promo.update');
    Route::patch('/harga-promo/{id}/toggle', [HargaPromoController::class, 'toggleStatus'])->name('harga-promo.toggle');
    Route::delete('/harga-promo/{id}', [HargaPromoController::class, 'destroy'])->name('harga-promo.destroy');



    /*
    |--------------------------------------------------------------------------
    | AKUNTANSI
    |--------------------------------------------------------------------------
    */

    Route::resource('akun', \App\Http\Controllers\AkunController::class)->except(['create', 'edit', 'show']);
    Route::patch('/akun/{id}/toggle-active', [\App\Http\Controllers\AkunController::class, 'toggleActive'])->name('akun.toggle-active');

    Route::prefix('akuntansi')->name('akuntansi.')->group(function () {
        Route::get('/jurnal', [\App\Http\Controllers\AkuntansiController::class, 'jurnal'])->name('jurnal');
        Route::get('/buku-besar', [\App\Http\Controllers\AkuntansiController::class, 'bukuBesar'])->name('buku-besar');
        Route::get('/neraca-saldo', [\App\Http\Controllers\AkuntansiController::class, 'neracaSaldo'])->name('neraca-saldo');
        Route::get('/laba-rugi', [\App\Http\Controllers\AkuntansiController::class, 'labaRugi'])->name('laba-rugi');
        Route::get('/neraca', [\App\Http\Controllers\AkuntansiController::class, 'neraca'])->name('neraca');
    });



    /*
    |--------------------------------------------------------------------------
    | AKSES
    |--------------------------------------------------------------------------
    */

    Route::get('/setting-akses',
        [AksesController::class, 'index']);

    Route::post('/setting-akses/update',
        [AksesController::class, 'update'])
        ->name('akses.update');

});
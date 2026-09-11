<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\AccountingService;
use App\Http\Controllers\KonsumenDepositController;

class KasirController extends Controller
{
    public function index(Request $request)
    {
        // 1. Hitung jumlah antrean baru (Hanya status 'Belum Bayar')
        $waitingCount = DB::table('transaksi')
            ->where('status_pembayaran', 'Belum Bayar')
            ->count();

        // 2. Query Monitoring Utama - SEMUA status (Belum Bayar/DP/Lunas)
        // sekarang ikut ditampilkan & bisa difilter di sini (sebelumnya
        // Belum Bayar dikecualikan paksa dari halaman ini sama sekali, cuma
        // bisa dilihat lewat "Antrean Baru" terpisah - operator yang ingin
        // rekap SEMUA nota, termasuk yang benar-benar belum disentuh kasir,
        // tidak punya satu tempat untuk itu).
        $query = DB::table('transaksi')
            ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
            ->join('users', 'transaksi.id_user_kasir', '=', 'users.id_user')
            ->select(
                'transaksi.*',
                'konsumen.nama_konsumen',
                'users.username',
                DB::raw("(SELECT SUM(nominal) FROM pembayaran_riwayat WHERE pembayaran_riwayat.id_transaksi = transaksi.id_transaksi) as total_masuk")
            )
            // Nota yang sudah dibatalkan (lihat PenjualanController::batalkanNota)
            // tidak perlu terus "dimonitor" pembayarannya di sini - sebelumnya
            // luput dikecualikan waktu fitur batal-nota ditambahkan, jadi nota
            // DP/Lunas yang belakangan dibatalkan tetap nyangkut di daftar ini.
            ->where('transaksi.status_nota', '!=', 'Dibatalkan');

        if ($request->filled('id_konsumen')) {
            $query->where('transaksi.id_konsumen', $request->id_konsumen);
        }

        if ($request->filled('nomor_nota')) {
            $query->where('transaksi.nomor_nota', 'like', "%{$request->nomor_nota}%");
        }

        if ($request->filled('tgl_mulai')) {
            $query->whereDate('transaksi.tgl_order', '>=', $request->tgl_mulai);
        }
        if ($request->filled('tgl_selesai')) {
            $query->whereDate('transaksi.tgl_order', '<=', $request->tgl_selesai);
        }

        // Status: kalau operator MEMILIH status tertentu, ikuti pilihannya
        // (termasuk pilihan eksplisit "-- Semua Status --" = tidak difilter
        // sama sekali, ditandai dari hidden input "status_filtered" yang ikut
        // terkirim setiap kali form filter ini disubmit). Kalau halaman baru
        // dibuka TANPA filter apa pun (nav pertama dari sidebar), default-nya
        // tampilkan yang masih perlu ditagih saja (Belum Bayar + DP) - nota
        // yang sudah Lunas tetap bisa dicari kapan saja lewat filter status.
        if ($request->filled('status_bayar')) {
            $query->where('transaksi.status_pembayaran', $request->status_bayar);
        } elseif (!$request->has('status_filtered')) {
            $query->whereIn('transaksi.status_pembayaran', ['Belum Bayar', 'DP']);
        }

        // COALESCE ke tgl_order - baris 'Belum Bayar' yang belum pernah
        // disentuh kasir sama sekali punya updated_at NULL (insertGetId()
        // di PenjualanController::store() tidak mengisinya), jadi kalau
        // urut murni by updated_at DESC baris-baris ini selalu terdorong ke
        // paling bawah biarpun order-nya baru masuk barusan.
        $transaksi = $query->orderByRaw('COALESCE(transaksi.updated_at, transaksi.tgl_order) DESC')->paginate(15)->withQueryString();

        // Dropdown filter Konsumen - dibatasi ke konsumen yang memang pernah
        // bertransaksi (bukan seluruh master konsumen), supaya daftar
        // pilihan relevan dan tidak membengkak tanpa guna.
        $konsumenList = DB::table('transaksi')
            ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
            ->where('transaksi.status_nota', '!=', 'Dibatalkan')
            ->select('konsumen.id_konsumen', 'konsumen.nama_konsumen')
            ->distinct()
            ->orderBy('konsumen.nama_konsumen')
            ->get();

        return view('kasir.monitoring_transaksi', compact('transaksi', 'waitingCount', 'konsumenList'));
    }

    /**
     * Closing cepat lewat modal di Kelola Kas (beda dari alur preview di
     * closing-preview -> simpanClosing, yang menampilkan rincian dulu sebelum
     * dikunci). Dipakai oleh tombol "Closing Kas Sekarang" di kelola_kas.blade.php.
     * Kedua alur ini sengaja dipertahankan berdampingan karena sama-sama nyata
     * dipakai di UI - bukan disatukan di sini tanpa sepengetahuan pemilik aplikasi.
     */
    public function prosesClosing(Request $request)
    {
        DB::beginTransaction();
        try {
            $id_kas = $request->id_kas;
            $saldo_fisik = (float) str_replace('.', '', $request->saldo_fisik ?? 0);

            if ($saldo_fisik < 0) {
                throw new \Exception('Saldo fisik tidak boleh negatif.');
            }

            $kas = DB::table('kas_harian')->where('id_kas', $id_kas)->lockForUpdate()->first();

            if (!$kas || $kas->status_lock === 'closed') {
                throw new \Exception('Sesi kas sudah ditutup atau tidak ditemukan.');
            }

            // Rincian tunai/transfer/debit DIHITUNG ULANG dari pembayaran_riwayat
            // (sama seperti simpanClosing()) dan disimpan ke kolom
            // total_tunai_masuk/total_transfer_masuk/total_debit_masuk/
            // total_tunai_keluar. Sebelumnya kolom-kolom ini tidak pernah diisi
            // oleh alur closing yang benar-benar terhubung ke tombol "CLOSING
            // KAS SEKARANG", sehingga nota cetak closing (print_closing.blade.php)
            // selalu menampilkan Rp 0 untuk semua rincian pemasukan/pengeluaran
            // walau uang sungguhan sudah masuk. Debit (kartu, non-tunai seperti
            // Transfer) TIDAK ikut menambah saldoAkhirSistem (laci fisik).
            $pemasukan = DB::table('pembayaran_riwayat')
                ->whereDate('tgl_bayar', $kas->tgl_kas)
                ->select(
                    DB::raw("SUM(CASE WHEN metode = 'Tunai' THEN nominal ELSE 0 END) as tunai_masuk"),
                    DB::raw("SUM(CASE WHEN metode = 'Transfer' THEN nominal ELSE 0 END) as transfer_masuk"),
                    DB::raw("SUM(CASE WHEN metode = 'Debit' THEN nominal ELSE 0 END) as debit_masuk")
                )->first();

            $tunaiMasuk = (float) ($pemasukan->tunai_masuk ?? 0);
            $transferMasuk = (float) ($pemasukan->transfer_masuk ?? 0);
            $debitMasuk = (float) ($pemasukan->debit_masuk ?? 0);

            // "Kas Tidak Terduga" (kas_manual) - satu-satunya sumber
            // pengeluaran tunai riil di app ini (nota selalu pemasukan,
            // tidak pernah pengeluaran). Kas masuk manual juga ikut
            // menambah laci fisik sama seperti Tunai dari nota.
            $manual = DB::table('kas_manual')
                ->where('id_kas', $kas->id_kas)
                ->select(
                    DB::raw("SUM(CASE WHEN status = 'Masuk' THEN nominal ELSE 0 END) as manual_masuk"),
                    DB::raw("SUM(CASE WHEN status = 'Keluar' THEN nominal ELSE 0 END) as manual_keluar")
                )->first();
            $manualMasuk = (float) ($manual->manual_masuk ?? 0);
            $tunaiKeluar = (float) ($manual->manual_keluar ?? 0);

            $saldoAkhirSistem = $kas->saldo_awal + $tunaiMasuk + $manualMasuk - $tunaiKeluar;
            $selisih = $saldo_fisik - $saldoAkhirSistem;

            DB::table('kas_harian')->where('id_kas', $id_kas)->update([
                'total_tunai_masuk' => $tunaiMasuk,
                'total_transfer_masuk' => $transferMasuk,
                'total_debit_masuk' => $debitMasuk,
                'total_tunai_keluar' => $tunaiKeluar,
                'saldo_akhir' => $saldoAkhirSistem,
                'saldo_fisik' => $saldo_fisik,
                'selisih' => $selisih,
                'status_lock' => 'closed',
                'waktu_tutup' => now(),
                'keterangan_closing' => $request->keterangan,
                'updated_at' => now()
            ]);

            DB::commit();
            return redirect()->route('kasir.kelola')->with('success', 'Kas Berhasil Ditutup. Selisih: Rp ' . number_format($selisih, 0, ',', '.'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal Closing: ' . $e->getMessage());
        }
    }

    public function waitingList()
    {
        // Pastikan hanya mengambil status 'Belum Bayar'. Urut berdasar
        // tgl_order (BUKAN created_at) - transaksi dibuat lewat
        // DB::table()->insertGetId() di PenjualanController::store(), yang
        // tidak otomatis mengisi created_at (selalu NULL), sementara
        // tgl_order SELALU diisi now() saat order dibuat.
        $waiting = DB::table('transaksi')
            ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
            ->where('transaksi.status_pembayaran', 'Belum Bayar')
            ->select('transaksi.*', 'konsumen.nama_konsumen')
            ->orderBy('transaksi.tgl_order', 'desc')
            ->get();

        return view('kasir.waiting_list', compact('waiting'));
    }

    public function bayarForm($id)
    {
        $transaksi = DB::table('transaksi')
            ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
            ->leftJoin('golongan_konsumen', 'konsumen.id_golongan', '=', 'golongan_konsumen.id_golongan')
            ->where('transaksi.id_transaksi', $id)
            ->select('transaksi.*', 'konsumen.nama_konsumen', 'konsumen.telepon', 'golongan_konsumen.nama_golongan')
            ->first();

        if (!$transaksi) {
            return redirect()->route('kasir.index')->with('error', 'Transaksi tidak ditemukan!');
        }

        // LEFT JOIN (bukan INNER) - baris Jasa punya id_produk NULL (lihat
        // migrasi add_jasa_support_to_transaksi_detail). INNER JOIN produk
        // sebelumnya diam-diam membuang baris Jasa dari halaman ini sama
        // sekali, termasuk dari TOTAL NOTA - nota jadi tertagih kurang dari
        // seharusnya tanpa ada tanda apa pun ke kasir.
        $items = DB::table('transaksi_detail')
            ->leftJoin('produk', 'transaksi_detail.id_produk', '=', 'produk.id_produk')
            ->leftJoin('jasa', 'transaksi_detail.id_jasa', '=', 'jasa.id_jasa')
            ->where('transaksi_detail.id_transaksi', $id)
            ->select(
                'transaksi_detail.*',
                'produk.nama_produk',
                'produk.metode_hitung',
                'jasa.nama_jasa'
            )
            ->get();

        // qty_hitung = satuan hitung SEBENARNYA yang dipakai untuk Harga per
        // item (m² untuk produk metode 'ukuran', pcs untuk 'quantity'/jasa) -
        // PERSIS rumus yang sama dengan HargaService/PenjualanController::
        // store()/StokReversalService & prosesBayar() di bawah. Dihitung di
        // sini (bukan dipercaya dari kolom qty mentah) supaya tampilan Harga
        // & preview Custom Harga di blade selalu cocok dengan apa yang nanti
        // benar-benar disimpan server saat submit.
        foreach ($items as $item) {
            $item->qty_hitung = ($item->metode_hitung === 'ukuran')
                ? round(((float) $item->p_ukuran * (float) $item->l_ukuran / 10000) * (float) $item->qty, 4)
                : (float) $item->qty;
        }

        $totalTerbayar = DB::table('pembayaran_riwayat')->where('id_transaksi', $id)->sum('nominal') ?? 0;
        $sisaTagihan = $transaksi->total_bayar - $totalTerbayar;
        $banks = DB::table('bank_perusahaan')->orderBy('nama_bank')->get();
        $saldoDeposit = KonsumenDepositController::saldoUntuk($transaksi->id_konsumen);

        // Konsumen ini PERNAH setor deposit tapi saldoDeposit sekarang 0 -
        // dibedakan dari "memang tidak pernah punya deposit sama sekali",
        // supaya kasir dapat keterangan jelas kenapa opsi Deposit tidak bisa
        // dipakai (lihat pesan di form_pembayaran.blade.php), bukan cuma
        // opsi yang diam-diam disabled tanpa alasan.
        $depositSudahHabis = $saldoDeposit <= 0 && DB::table('konsumen_deposit')
            ->where('id_konsumen', $transaksi->id_konsumen)
            ->where('tipe', 'Setoran')
            ->exists();

        return view('kasir.form_pembayaran', compact('transaksi', 'items', 'totalTerbayar', 'sisaTagihan', 'banks', 'saldoDeposit', 'depositSudahHabis'));
    }

    public function prosesBayar(Request $request)
    {
        DB::beginTransaction();
        try {
            $id_transaksi = $request->id_transaksi;
            $uangDiterimaSekarang = (float) (str_replace('.', '', $request->jumlah_bayar) ?: 0);
            $diskon = (float) str_replace('.', '', $request->diskon ?? 0);
            $metode = $request->metode_pembayaran;

            if ($uangDiterimaSekarang < 0) {
                throw new \Exception("Jumlah bayar tidak boleh negatif.");
            }
            if ($diskon < 0) {
                throw new \Exception("Diskon tidak boleh negatif.");
            }
            if (!in_array($metode, ['Tunai', 'Debit', 'Transfer', 'Deposit'], true)) {
                throw new \Exception("Metode pembayaran tidak dikenali.");
            }

            // id_bank hanya relevan untuk Transfer - divalidasi ke tabel
            // bank_perusahaan supaya id yang dipalsukan dari client tidak
            // lolos ke foreign key pembayaran_riwayat.id_bank.
            $idBank = null;
            if ($metode === 'Transfer' && $request->filled('id_bank')) {
                $idBank = (int) $request->id_bank;
                if (!DB::table('bank_perusahaan')->where('id_bank', $idBank)->exists()) {
                    throw new \Exception("Rekening bank tujuan tidak ditemukan.");
                }
            }

            // 1. Update Detail Barang ("Custom Harga" per item). Kasir memang
            // sengaja diberi wewenang untuk menyesuaikan harga & biaya di sini -
            // itu TIDAK dihapus, malah diperluas supaya biaya_cetak/biaya_desain/
            // biaya_finishing juga bisa dikoreksi (mis. operator lupa
            // menambahkannya saat nota dibuat), plus "potongan" (diskon per
            // item) untuk kasus itu juga.
            //
            // SEBELUMNYA baris ini HANYA mengganti subtotal = harga_baru * qty,
            // diam-diam MEMBUANG biaya_cetak/biaya_finishing yang sudah
            // tersimpan (kolom aslinya tidak disentuh sama sekali) - subtotal
            // jadi tidak sinkron lagi dengan biaya_cetak/finishing yang masih
            // tampil di SPK/Produksi/cetak nota. Sekarang SEMUA komponen
            // (harga, cetak, desain, finishing, potongan) dibaca & disimpan
            // bersamaan supaya subtotal selalu konsisten dengan kolom-kolom
            // penyusunnya.
            //
            // qty dipakai dari data transaksi_detail yang tersimpan (bukan
            // dari field tersembunyi di request) supaya tidak bisa dikalikan
            // dengan qty palsu untuk memperbesar/memperkecil subtotal di luar
            // penyesuaian harga yang sesungguhnya dimaksud.
            if ($request->items) {
                foreach ($request->items as $id_detail => $data) {
                    $detailRow = DB::table('transaksi_detail')
                        ->where('id_detail', $id_detail)
                        ->where('id_transaksi', $id_transaksi)
                        ->first();

                    if (!$detailRow) {
                        continue;
                    }

                    // Biaya Cetak SEKARANG komponen UTAMA subtotal (menggantikan
                    // "Harga per satuan hitung x qty", bukan lagi ditambahkan di
                    // atasnya - lihat catatan yang sama di PenjualanController::
                    // store()). "Harga" per-unit yang dulu dikalikan qty di sini
                    // TIDAK LAGI dipakai untuk hitungan - field itu murni acuan/
                    // referensi Price List di UI, tidak lagi disetorkan ke
                    // subtotal supaya tidak dobel-hitung dengan Biaya Cetak.
                    $bersihkan = fn ($v) => (float) str_replace('.', '', (string) ($v ?? 0));

                    $biayaCetakBaru = $bersihkan($data['biaya_cetak'] ?? $detailRow->biaya_cetak);
                    $biayaDesainBaru = $bersihkan($data['biaya_desain'] ?? $detailRow->biaya_desain);
                    $biayaFinishingBaru = $bersihkan($data['biaya_finishing'] ?? $detailRow->biaya_finishing);
                    $potonganBaru = $bersihkan($data['potongan'] ?? 0);

                    // JARING PENGAMAN: item LAMA (biaya_cetak masih 0 di
                    // database, dari sebelum Biaya Cetak jadi komponen
                    // utama) yang hidden input-nya di client belum sempat
                    // direkonsiliasi (lihat reconcileSemuaBiayaCetak() di
                    // form_pembayaran.blade.php) TIDAK BOLEH diam-diam
                    // menol-kan subtotal yang sudah ada cuma karena ketiga
                    // field biaya ini kosong di request - persis bug yang
                    // pernah membuat "harga di rincian barang jadi 0 setelah
                    // disimpan" (termasuk semua proses DP/Deposit). Kalau
                    // ketiganya 0 TAPI baris ini sudah punya subtotal
                    // tersimpan > 0, dipulihkan dulu dari situ - diperlakukan
                    // sebagai "belum sempat disentuh", bukan sengaja
                    // dikosongkan (potongan lama ikut dikembalikan supaya
                    // hasilnya persis subtotal yang sudah ada sebelum baris
                    // formula di bawah menghitung ulang dengan potongan BARU
                    // dari request ini).
                    if ($biayaCetakBaru <= 0 && $biayaDesainBaru <= 0 && $biayaFinishingBaru <= 0 && (float) $detailRow->subtotal > 0) {
                        $biayaCetakBaru = (float) $detailRow->subtotal + (float) $detailRow->potongan;
                    }

                    foreach (['Biaya cetak' => $biayaCetakBaru, 'Biaya desain' => $biayaDesainBaru, 'Biaya finishing' => $biayaFinishingBaru, 'Potongan' => $potonganBaru] as $label => $nilai) {
                        if ($nilai < 0) {
                            throw new \Exception("{$label} tidak boleh negatif.");
                        }
                    }

                    $subtotalBaru = $biayaCetakBaru + $biayaDesainBaru + $biayaFinishingBaru - $potonganBaru;

                    if ($subtotalBaru < 0) {
                        throw new \Exception("Potongan untuk item \"{$detailRow->nama_file}\" tidak boleh lebih besar dari total item itu sendiri.");
                    }

                    DB::table('transaksi_detail')->where('id_detail', $id_detail)->update([
                        'biaya_cetak'     => $biayaCetakBaru,
                        'biaya_desain'    => $biayaDesainBaru,
                        'biaya_finishing' => $biayaFinishingBaru,
                        'potongan'        => $potonganBaru,
                        'subtotal'        => $subtotalBaru,
                    ]);
                }
            }

            $totalNotaBaru = DB::table('transaksi_detail')->where('id_transaksi', $id_transaksi)->sum('subtotal');

            if ($diskon > $totalNotaBaru) {
                throw new \Exception("Diskon tidak boleh lebih besar dari total nota.");
            }

            $grandTotalFinal = $totalNotaBaru - $diskon;

            // 2. Catat Pembayaran (Hanya jika uang > 0)
            if ($uangDiterimaSekarang > 0) {
                $kas = DB::table('kas_harian')
                        ->where('tgl_kas', date('Y-m-d'))
                        ->where('status_lock', 'open')
                        ->first();

                if (!$kas) {
                    throw new \Exception("Kasir belum dibuka! Silakan buka kas terlebih dahulu.");
                }

                $transaksiRow = DB::table('transaksi')->where('id_transaksi', $id_transaksi)->first();
                $nomorNota = $transaksiRow->nomor_nota ?? null;

                if ($metode === 'Deposit') {
                    // Bukan uang tunai/transfer baru - ini menarik saldo deposit
                    // yang sudah disetor sebelumnya (lihat KonsumenDepositController).
                    // Validasi saldo di sini juga (bukan cuma client-side) supaya
                    // tidak bisa "bayar" dari saldo yang tidak ada.
                    $saldoDeposit = KonsumenDepositController::saldoUntuk((int) $transaksiRow->id_konsumen);
                    if ($uangDiterimaSekarang > $saldoDeposit) {
                        throw new \Exception("Saldo deposit tidak cukup. Tersedia: Rp " . number_format($saldoDeposit, 0, ',', '.'));
                    }

                    DB::table('konsumen_deposit')->insert([
                        'id_konsumen'  => $transaksiRow->id_konsumen,
                        'tipe'         => 'Pemakaian',
                        'nominal'      => $uangDiterimaSekarang,
                        'metode_bayar' => 'Deposit',
                        'keterangan'   => "Pembayaran nota {$nomorNota}",
                        'tgl_deposit'  => now()->toDateString(),
                        'id_transaksi' => $id_transaksi,
                        'id_user'      => Auth::id(),
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }

                DB::table('pembayaran_riwayat')->insert([
                    'id_transaksi' => $id_transaksi,
                    'nominal'      => $uangDiterimaSekarang,
                    'metode'       => $metode,
                    'id_bank'      => $idBank,
                    'tgl_bayar'    => now(),
                    'keterangan'   => "Pembayaran via Kasir"
                ]);

                // Update Saldo Kas - Deposit TIDAK menambah kas hari ini, karena
                // uangnya sudah masuk kas saat setoran deposit dulu, bukan hari ini.
                if ($metode == 'Tunai') {
                    DB::table('kas_harian')->where('id_kas', $kas->id_kas)->increment('saldo_akhir', $uangDiterimaSekarang);
                } elseif ($metode == 'Transfer') {
                    DB::table('kas_harian')->where('id_kas', $kas->id_kas)->increment('total_transfer', $uangDiterimaSekarang);
                }

                if ($metode === 'Deposit') {
                    AccountingService::postPembayaranDeposit($nomorNota, $uangDiterimaSekarang);
                } else {
                    AccountingService::postPembayaran($nomorNota, $uangDiterimaSekarang, $metode);
                }
            }

            // 3. Update Status - DIHITUNG JUJUR dari pembayaran_riwayat yang
            // BENAR-BENAR ada, bukan dipaksa jadi DP begitu form ini
            // disubmit apa pun isinya. Sebelumnya status SELALU dipaksa jadi
            // DP (bahkan untuk klik "Simpan" dengan uang 0 - lihat riwayat
            // git), jadi ada transaksi berstatus DP di database TANPA satu
            // pun baris pembayaran_riwayat menyertainya - kalau dibuka lagi
            // kelihatan seperti "nilai DP-nya hilang" padahal memang tidak
            // pernah ada uang yang tercatat sama sekali. Sekarang DP hanya
            // bisa dicapai lewat pembayaran yang sungguhan tercatat > 0;
            // kalau belum ada uang masuk sama sekali, tetap "Belum Bayar"
            // (sekarang aman ditinggal di status ini - Monitoring Kasir
            // sudah menampilkan & bisa memfilter Belum Bayar juga).
            $totalTerbayarSemua = DB::table('pembayaran_riwayat')->where('id_transaksi', $id_transaksi)->sum('nominal') ?? 0;

            if ($totalTerbayarSemua >= $grandTotalFinal && $grandTotalFinal > 0) {
                $status = 'Lunas';
            } elseif ($totalTerbayarSemua > 0) {
                $status = 'DP';
            } else {
                $status = 'Belum Bayar';
            }

            $updateData = [
                'total_bayar'       => $grandTotalFinal,
                'status_pembayaran' => $status,
                'updated_at'        => now()
            ];

            // metode_pembayaran cuma dicatat kalau nota INI memang baru
            // menerima uang sungguhan - jangan sampai klik "Simpan" (0
            // rupiah) diam-diam menuliskan metode dari radio button yang
            // sedang aktif di form, padahal tidak ada uang yang berpindah
            // lewat metode itu sama sekali.
            if ($uangDiterimaSekarang > 0) {
                $updateData['metode_pembayaran'] = $metode;
            }

            DB::table('transaksi')->where('id_transaksi', $id_transaksi)->update($updateData);

            DB::commit();
            return redirect()->route('kasir.index')->with('success', 'Transaksi berhasil diproses ke Monitoring.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Manajemen Kas Harian
     */
    public function kelolaKas()
    {
        $hari_ini = date('Y-m-d');
        $kasAktif = DB::table('kas_harian')->where('tgl_kas', $hari_ini)->where('status_lock', 'open')->first();

        $mutasi = DB::table('pembayaran_riwayat')
            ->join('transaksi', 'pembayaran_riwayat.id_transaksi', '=', 'transaksi.id_transaksi')
            ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
            ->whereDate('pembayaran_riwayat.tgl_bayar', $hari_ini)
            ->select('pembayaran_riwayat.*', 'transaksi.nomor_nota', 'transaksi.status_pembayaran', 'konsumen.nama_konsumen')
            ->orderBy('pembayaran_riwayat.tgl_bayar', 'desc')
            ->get();

        // "Kas Tidak Terduga" - mutasi tunai yang tidak terikat nota (lihat
        // migrasi create_kas_manual_table). Ditampilkan terpisah dari
        // $mutasi (yang murni pembayaran_riwayat/nota) supaya kolom-kolom
        // yang tidak relevan (Nota, Konsumen, Metode) tidak perlu dipaksakan
        // ada isinya untuk baris ini.
        $mutasiManual = DB::table('kas_manual')
            ->join('users', 'kas_manual.id_user', '=', 'users.id_user')
            ->whereDate('kas_manual.waktu', $hari_ini)
            ->select('kas_manual.*', 'users.username as dicatat_oleh')
            ->orderBy('kas_manual.waktu', 'desc')
            ->get();

        $data_kas = collect([(object) [
            'nominal_tunai' => $mutasi->where('metode', 'Tunai')->sum('nominal'),
            'nominal_transfer' => $mutasi->where('metode', 'Transfer')->sum('nominal'),
            'nominal_debit' => $mutasi->where('metode', 'Debit')->sum('nominal'),
            'nominal_manual_masuk' => $mutasiManual->where('status', 'Masuk')->sum('nominal'),
            'nominal_manual_keluar' => $mutasiManual->where('status', 'Keluar')->sum('nominal'),
        ]]);

        return view('kasir.kelola_kas', compact('kasAktif', 'mutasi', 'mutasiManual', 'data_kas'));
    }

    /**
     * "Kas Tidak Terduga" - catat mutasi tunai yang tidak terikat nota
     * (pengeluaran operasional dadakan, kas masuk di luar penjualan).
     * Mengikuti referensi form "Alur Kas Kasir": Keterangan, Status
     * (Debet/Kas Masuk atau Kredit/Kas Keluar), Nominal - selalu Tunai
     * (uang fisik di laci), tidak ada pilihan metode seperti pembayaran
     * nota.
     */
    public function storeKasManual(Request $request)
    {
        $request->validate([
            'keterangan' => 'required|string|max:500',
            'status'     => 'required|in:Masuk,Keluar',
            'nominal'    => 'required|numeric|min:1',
        ]);

        DB::beginTransaction();
        try {
            $kas = DB::table('kas_harian')
                ->where('tgl_kas', now()->format('Y-m-d'))
                ->where('status_lock', 'open')
                ->first();

            if (!$kas) {
                throw new \Exception('Kasir belum dibuka! Silakan buka kas terlebih dahulu.');
            }

            $idKasManual = DB::table('kas_manual')->insertGetId([
                'id_kas'      => $kas->id_kas,
                'waktu'       => now(),
                'keterangan'  => $request->keterangan,
                'status'      => $request->status,
                'nominal'     => $request->nominal,
                'id_user'     => Auth::id(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            AccountingService::postKasManual('KAS-MANUAL-' . $idKasManual, (float) $request->nominal, $request->status);

            DB::commit();
            return redirect()->route('kasir.kelola')->with('success', 'Mutasi kas tidak terduga berhasil dicatat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('kasir.kelola')->with('error', 'Gagal mencatat: ' . $e->getMessage());
        }
    }

   public function storeKas(Request $request)
{
    $request->validate(['jumlah' => 'required']);
    $nominal = str_replace('.', '', $request->jumlah);

    $sudahBuka = DB::table('kas_harian')
        ->where('tgl_kas', now()->format('Y-m-d'))
        ->where('id_user', auth()->id())
        ->where('status_lock', 'open')
        ->exists();

    if ($sudahBuka) {
        return redirect()->route('kasir.kelola')->with('error', 'Anda sudah membuka sesi kas hari ini.');
    }

    DB::table('kas_harian')->insert([
        'tgl_kas'     => now()->format('Y-m-d'),
        'id_user'     => auth()->id(),
        'saldo_awal'  => $nominal,
        'saldo_akhir' => $nominal, 
        'status_lock' => 'open',
        'created_at'  => now(),
        'updated_at'  => now()
    ]);

    // Setelah simpan, lempar ke halaman monitoring
    return redirect()->route('kasir.index')->with('success', 'Kas Berhasil Dibuka!');
}
/**
 * Rombak total mengikuti referensi "Closing Alur Kas Kasir" milik pemilik
 * aplikasi - rekap nota HARI INI per Mesin/Divisi (sebelumnya cuma rincian
 * pemasukan per metode bayar, tidak ada gambaran nota apa saja yang masuk
 * hari ini). "Nota Penjualan Barang"/"Piutang Penjualan Barang" di
 * referensi SENGAJA TIDAK direplikasi - app ini cuma py satu jenis
 * transaksi (transaksi+transaksi_detail, mencakup produk & jasa
 * sekaligus), tidak ada jalur "penjualan barang" terpisah dari "nota
 * order" seperti sistem lama pemilik aplikasi, jadi bagian itu tidak py
 * padanan data nyata untuk ditampilkan (bukan dihilangkan sengaja,
 * memang tidak ada). Begitu juga "Status Rolling Saldo Akhir" & "Bayar DP
 * Transaksi Ref" - tidak py alur kerja yang jelas padanannya di app ini,
 * jadi tidak dipaksakan dibangun tanpa tahu maksud pastinya.
 */
public function previewClosing()
{
    $hari_ini = date('Y-m-d');
    $kas = DB::table('kas_harian')
        ->where('tgl_kas', $hari_ini)
        ->where('status_lock', 'open')
        ->where('id_user', auth()->id())
        ->first();

    // Proteksi: Tidak bisa closing jika tidak ada kas aktif
    if (!$kas) {
        return redirect()->route('kasir.kelola')->with('error', 'Tidak ada alur kas aktif yang bisa ditutup!');
    }

    // REKAP TRANSAKSI - nota yang ORDER-nya dibuat hari ini (tgl_order),
    // dipecah per Mesin/Divisi produk (Jasa & produk tanpa mesin masuk
    // "Jasa / Lain-lain"). Ini "Nota Order Cetak" di referensi.
    $rekapKategori = DB::table('transaksi_detail')
        ->join('transaksi', 'transaksi_detail.id_transaksi', '=', 'transaksi.id_transaksi')
        ->leftJoin('produk', 'transaksi_detail.id_produk', '=', 'produk.id_produk')
        ->leftJoin('mesin', 'produk.id_mesin', '=', 'mesin.id_mesin')
        ->whereDate('transaksi.tgl_order', $hari_ini)
        ->where('transaksi.status_nota', '!=', 'Dibatalkan')
        ->select(
            DB::raw("COALESCE(mesin.nama_mesin, 'Jasa / Lain-lain') as kategori"),
            DB::raw('SUM(transaksi_detail.subtotal) as total')
        )
        ->groupBy('kategori')
        ->orderByDesc('total')
        ->get();

    $totalNotaOrder = (float) $rekapKategori->sum('total');

    // Piutang Nota Order - sisa tagihan (belum dibayar) KHUSUS dari nota
    // yang order-nya dibuat hari ini (bukan seluruh piutang lama).
    $notaHariIni = DB::table('transaksi')
        ->whereDate('tgl_order', $hari_ini)
        ->where('status_nota', '!=', 'Dibatalkan')
        ->select('id_transaksi', 'total_bayar')
        ->get();
    $totalTerbayarNotaHariIni = DB::table('pembayaran_riwayat')
        ->whereIn('id_transaksi', $notaHariIni->pluck('id_transaksi'))
        ->sum('nominal');
    $piutangNotaOrder = max(0, $totalNotaOrder - (float) $totalTerbayarNotaHariIni);
    $totalPendapatan = $totalNotaOrder - $piutangNotaOrder;

    // REKAP PEMBAYARAN - seluruh pembayaran yang diterima HARI INI (tgl_bayar),
    // dipecah: "Bayar Pelunasan" (melunasi/mencicil nota yang ORDER-nya dari
    // hari-hari SEBELUMNYA) vs "Bayar Transaksi Hari Ini" (nota yang order &
    // bayarnya sama-sama hari ini).
    $pembayaranHariIni = DB::table('pembayaran_riwayat')
        ->join('transaksi', 'pembayaran_riwayat.id_transaksi', '=', 'transaksi.id_transaksi')
        ->whereDate('pembayaran_riwayat.tgl_bayar', $hari_ini)
        ->select('pembayaran_riwayat.nominal', 'pembayaran_riwayat.metode', DB::raw('DATE(transaksi.tgl_order) as tgl_order_date'))
        ->get();
    $bayarPelunasan = (float) $pembayaranHariIni->where('tgl_order_date', '!=', $hari_ini)->sum('nominal');
    $bayarHariIni = (float) $pembayaranHariIni->where('tgl_order_date', $hari_ini)->sum('nominal');

    // Hitung Rincian Pemasukan dari Riwayat Pembayaran (per metode, seluruh
    // pembayaran hari ini apa pun tanggal order-nya - dipakai utk mutasi laci).
    $pemasukan = DB::table('pembayaran_riwayat')
        ->whereDate('tgl_bayar', $hari_ini)
        ->select(
            DB::raw("SUM(CASE WHEN metode = 'Tunai' THEN nominal ELSE 0 END) as tunai_masuk"),
            DB::raw("SUM(CASE WHEN metode = 'Transfer' THEN nominal ELSE 0 END) as transfer_masuk"),
            DB::raw("SUM(CASE WHEN metode = 'Debit' THEN nominal ELSE 0 END) as debit_masuk"),
            DB::raw("SUM(CASE WHEN metode = 'Deposit' THEN nominal ELSE 0 END) as deposit_masuk")
        )->first();

    // "Kas Tidak Terduga" - satu-satunya sumber pengeluaran tunai riil di
    // app ini (nota selalu pemasukan, tidak pernah pengeluaran).
    $manual = DB::table('kas_manual')
        ->where('id_kas', $kas->id_kas)
        ->select(
            DB::raw("SUM(CASE WHEN status = 'Masuk' THEN nominal ELSE 0 END) as manual_masuk"),
            DB::raw("SUM(CASE WHEN status = 'Keluar' THEN nominal ELSE 0 END) as manual_keluar")
        )->first();
    $manualMasuk = (float) ($manual->manual_masuk ?? 0);
    $pengeluaran_tunai = (float) ($manual->manual_keluar ?? 0);

    // Kalkulasi Saldo Akhir Laci (Hanya Tunai - Transfer/Debit/Deposit tidak
    // pernah menyentuh uang fisik di laci).
    $saldo_akhir_sistem = ($kas->saldo_awal + $pemasukan->tunai_masuk + $manualMasuk) - $pengeluaran_tunai;

    return view('kasir.preview_closing', compact(
        'kas', 'pemasukan', 'pengeluaran_tunai', 'saldo_akhir_sistem',
        'rekapKategori', 'totalNotaOrder', 'piutangNotaOrder', 'totalPendapatan',
        'bayarPelunasan', 'bayarHariIni', 'manualMasuk'
    ));
}

public function simpanClosing(Request $request)
{
    $id_kas = $request->id_kas;
    $saldo_fisik = (float) str_replace('.', '', $request->saldo_fisik ?? 0);

    if ($saldo_fisik < 0) {
        return back()->with('error', 'Saldo fisik tidak boleh negatif.');
    }

    DB::beginTransaction();
    try {
        // Lock row supaya dua klik "Verifikasi & Kunci Kasir" yang hampir
        // bersamaan tidak sama-sama lolos menutup kas yang sama.
        $kas = DB::table('kas_harian')->where('id_kas', $id_kas)->lockForUpdate()->first();

        if (!$kas) {
            throw new \Exception('Sesi kas tidak ditemukan.');
        }
        if ($kas->status_lock === 'closed') {
            throw new \Exception('Sesi kas ini sudah ditutup sebelumnya.');
        }

        // Total pemasukan DIHITUNG ULANG dari pembayaran_riwayat di server
        // (bukan dipercaya dari hidden field), sama seperti previewClosing() -
        // supaya laporan yang tersimpan permanen tidak bisa diselipi angka
        // yang tidak sesuai riwayat pembayaran sesungguhnya.
        $pemasukan = DB::table('pembayaran_riwayat')
            ->whereDate('tgl_bayar', $kas->tgl_kas)
            ->select(
                DB::raw("SUM(CASE WHEN metode = 'Tunai' THEN nominal ELSE 0 END) as tunai_masuk"),
                DB::raw("SUM(CASE WHEN metode = 'Transfer' THEN nominal ELSE 0 END) as transfer_masuk"),
                DB::raw("SUM(CASE WHEN metode = 'Debit' THEN nominal ELSE 0 END) as debit_masuk")
            )->first();

        $tunaiMasuk = (float) ($pemasukan->tunai_masuk ?? 0);
        $transferMasuk = (float) ($pemasukan->transfer_masuk ?? 0);
        $debitMasuk = (float) ($pemasukan->debit_masuk ?? 0);

        // "Kas Tidak Terduga" - lihat catatan yang sama di previewClosing().
        $manual = DB::table('kas_manual')
            ->where('id_kas', $kas->id_kas)
            ->select(
                DB::raw("SUM(CASE WHEN status = 'Masuk' THEN nominal ELSE 0 END) as manual_masuk"),
                DB::raw("SUM(CASE WHEN status = 'Keluar' THEN nominal ELSE 0 END) as manual_keluar")
            )->first();
        $manualMasuk = (float) ($manual->manual_masuk ?? 0);
        $tunaiKeluar = (float) ($manual->manual_keluar ?? 0);

        $saldoAkhirSistem = $kas->saldo_awal + $tunaiMasuk + $manualMasuk - $tunaiKeluar;
        $selisih = $saldo_fisik - $saldoAkhirSistem;

        DB::table('kas_harian')->where('id_kas', $id_kas)->update([
            'total_tunai_masuk' => $tunaiMasuk,
            'total_transfer_masuk' => $transferMasuk,
            'total_debit_masuk' => $debitMasuk,
            'total_tunai_keluar' => $tunaiKeluar,
            'saldo_akhir' => $saldoAkhirSistem,
            'saldo_fisik' => $saldo_fisik,
            'selisih' => $selisih,
            'status_lock' => 'closed',
            'waktu_tutup' => now(),
            'updated_at' => now()
        ]);

        DB::commit();
        return redirect()->route('kasir.kelola')->with('success', 'Kas Berhasil Ditutup dan Dikunci. Selisih: Rp ' . number_format($selisih, 0, ',', '.'));

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', $e->getMessage());
    }
}

/**
 * Cetak nota transaksi. Format ditentukan lewat query string (?format=thermal
 * untuk struk 58mm, default 'a4' untuk invoice biasa) - satu view yang sama,
 * cuma CSS lebar kertasnya beda, supaya tidak perlu 2 template terpisah.
 */
public function printOpsi(Request $request, $id)
{
    $transaksi = DB::table('transaksi')
        ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
        ->leftJoin('users', 'transaksi.id_user_kasir', '=', 'users.id_user')
        ->leftJoin('karyawan', 'users.id_karyawan', '=', 'karyawan.id_karyawan')
        ->where('transaksi.id_transaksi', $id)
        ->select(
            'transaksi.*',
            'konsumen.nama_konsumen',
            'konsumen.alamat',
            'konsumen.telepon',
            DB::raw('COALESCE(karyawan.nama_karyawan, users.username) as nama_kasir')
        )
        ->first();

    if (!$transaksi) {
        abort(404, 'Transaksi tidak ditemukan.');
    }

    $items = DB::table('transaksi_detail')
        ->where('id_transaksi', $id)
        ->select('nama_file', 'qty', 'subtotal')
        ->get();

    $totalTerbayar = DB::table('pembayaran_riwayat')->where('id_transaksi', $id)->sum('nominal') ?? 0;
    $format = $request->query('format', 'a4') === 'thermal' ? 'thermal' : 'a4';

    return view('kasir.print_nota', compact('transaksi', 'items', 'totalTerbayar', 'format'));
}

/**
 * Cetak laporan closing kasir yang SUDAH ditutup (butuh saldo_fisik/selisih,
 * jadi hanya bisa dicetak untuk sesi yang status_lock-nya 'closed').
 */
public function printReport($id, $format)
{
    $kas = DB::table('kas_harian')
        ->leftJoin('users', 'kas_harian.id_user', '=', 'users.id_user')
        ->leftJoin('karyawan', 'users.id_karyawan', '=', 'karyawan.id_karyawan')
        ->where('kas_harian.id_kas', $id)
        ->select('kas_harian.*', DB::raw('COALESCE(karyawan.nama_karyawan, users.username) as nama_kasir'))
        ->first();

    if (!$kas) {
        abort(404, 'Sesi kas tidak ditemukan.');
    }

    $format = $format === 'thermal' ? 'thermal' : 'a4';

    return view('kasir.print_closing', compact('kas', 'format'));
}

/**
 * Riwayat proses sebuah nota - dari data yang benar-benar ada (kapan order
 * dibuat, riwayat pembayaran, status SPK tiap item saat ini). transaksi_detail
 * tidak punya kolom timestamp sama sekali, jadi "kapan status SPK berubah"
 * tidak bisa direkonstruksi - yang ditampilkan adalah status TERKINI per item,
 * bukan linimasa perubahan status (jangan berpura-pura tahu yang tidak diketahui).
 */
public function getHistory($id)
{
    $transaksi = DB::table('transaksi')->where('id_transaksi', $id)->first();

    if (!$transaksi) {
        return response()->json(['status' => 'error', 'msg' => 'Transaksi tidak ditemukan.'], 404);
    }

    $items = DB::table('transaksi_detail')
        ->where('id_transaksi', $id)
        ->select('nama_file', 'status_spk', 'qty', 'qty_cetak')
        ->get();

    $pembayaran = DB::table('pembayaran_riwayat')
        ->where('id_transaksi', $id)
        ->orderBy('tgl_bayar')
        ->select('nominal', 'metode', 'tgl_bayar', 'keterangan')
        ->get();

    return response()->json([
        'status' => 'success',
        'nomor_nota' => $transaksi->nomor_nota,
        'tgl_order' => $transaksi->tgl_order,
        'status_pembayaran' => $transaksi->status_pembayaran,
        'items' => $items,
        'pembayaran' => $pembayaran,
    ]);
}
    public function checkNewTransaction()
    {
        $count = DB::table('transaksi')->where('tgl_order', date('Y-m-d'))->where('status_pembayaran', 'Belum Bayar')->count();
        return response()->json(['total' => $count]);
    }
}
<?php

namespace App\Http\Controllers\Kasir;

use App\Services\KasSesiService;

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
        $transaksi = $query->orderByRaw('COALESCE(transaksi.updated_at, transaksi.tgl_order) DESC')->paginate(20)->withQueryString();

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
                    // lockForUpdate() - sudah di dalam transaction luar
                    // (baris 171), cuma menambah lock supaya klik ganda
                    // "Simpan" pada form koreksi harga yang sama tidak
                    // saling menimpa hasil hitungan biaya_cetak/subtotal.
                    $detailRow = DB::table('transaksi_detail')
                        ->where('id_detail', $id_detail)
                        ->where('id_transaksi', $id_transaksi)
                        ->lockForUpdate()
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
                $kas = KasSesiService::aktifUntukTransaksi();

                if (!$kas) {
                    throw new \Exception("Kasir belum dibuka! Silakan buka kas terlebih dahulu.");
                }

                // Kas aktif itu MILIK SATU ORANG SAJA (lihat catatan di
                // storeKas()) - kalau yang sedang login BUKAN pemilik sesi
                // kas yang aktif (mis. Administrator sementara Kasir sedang
                // buka kas), pembayaran ditolak SAMA SEKALI, bukan diam-diam
                // tercatat ke sesi kas orang lain seperti sebelumnya.
                if ((int) $kas->id_user !== (int) auth()->id()) {
                    throw new \Exception("Anda tidak dapat melakukan transaksi. Cek kembali saldo kas kasir Anda. Kas kasir akan terus berjalan sebelum terjadi closing kas kasir.");
                }

                $transaksiRow = DB::table('transaksi')->where('id_transaksi', $id_transaksi)->first();
                $nomorNota = $transaksiRow->nomor_nota ?? null;

                if ($metode === 'Deposit') {
                    // Lock baris konsumen sebagai mutex SEBELUM hitung saldo -
                    // saldoUntuk() cuma SUM() atas baris konsumen_deposit (tidak
                    // ada satu baris "saldo" yang bisa di-lockForUpdate langsung),
                    // jadi tanpa ini dua pembayaran Deposit yang hampir bersamaan
                    // untuk konsumen yang sama bisa sama-sama membaca saldo lama
                    // yang sama sebelum salah satu insert 'Pemakaian'-nya commit -
                    // deposit bisa terpakai melebihi yang disetor (double-spend).
                    DB::table('konsumen')->where('id_konsumen', $transaksiRow->id_konsumen)->lockForUpdate()->first();

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

    /** Pemilik sesi atau Administrator boleh closing. */
    private static function bolehTutup(object $kas): bool
    {
        return (int) $kas->id_user === (int) auth()->id() || optional(auth()->user()->role)->nama_role === 'Administrator';
    }

    /**
     * Manajemen Kas Harian
     */
    public function kelolaKas()
    {
        $hari_ini = date('Y-m-d');
        $kasAktif = KasSesiService::aktif();
        $mulaiSesi = $kasAktif ? KasSesiService::mulai($kasAktif) : now()->startOfDay();

        // "Status Rolling Saldo Akhir" - kalau sesi kas yang PALING BARU
        // ditutup (tanggal berapa pun sebelum hari ini, bukan cuma kemarin
        // persis - jaga-jaga ada hari libur/lewat tanpa buka kas) ditutup
        // dengan status 'Rolling', saldo fisik hasil hitungannya dibawa
        // lanjut jadi SARAN saldo awal hari ini (tetap bisa ditimpa manual
        // di prompt "Buka Saldo Kasir" - bukan otomatis dipaksakan).
        $saldoRolling = null;
        if (!$kasAktif) {
            $kasSebelumnya = DB::table('kas_harian')
                ->where('status_lock', 'closed')
                ->where('tgl_kas', '<', $hari_ini)
                ->orderByDesc('tgl_kas')
                ->first();
            if ($kasSebelumnya && $kasSebelumnya->status_rolling === 'Rolling') {
                $saldoRolling = (float) $kasSebelumnya->saldo_fisik;
            }
        }

        $mutasi = DB::table('pembayaran_riwayat')
            ->join('transaksi', 'pembayaran_riwayat.id_transaksi', '=', 'transaksi.id_transaksi')
            ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
            ->where('pembayaran_riwayat.tgl_bayar', '>=', $mulaiSesi)
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
            ->when($kasAktif, fn ($q) => $q->where('kas_manual.id_kas', $kasAktif->id_kas), fn ($q) => $q->where('kas_manual.waktu', '>=', $mulaiSesi))
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

        return view('kasir.kelola_kas', compact('kasAktif', 'mutasi', 'mutasiManual', 'data_kas', 'saldoRolling'));
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
            $kas = KasSesiService::aktifUntukTransaksi();

            if (!$kas) {
                throw new \Exception(KasSesiService::alasanTerblokir() ?? 'Kasir belum dibuka! Silakan buka kas terlebih dahulu.');
            }

            // Sama seperti prosesBayar() - kas manual (mutasi tunai di luar
            // nota) cuma boleh dicatat oleh PEMILIK sesi kas yang aktif.
            if ((int) $kas->id_user !== (int) auth()->id()) {
                throw new \Exception('Anda tidak dapat melakukan transaksi. Cek kembali saldo kas kasir Anda. Kas kasir akan terus berjalan sebelum terjadi closing kas kasir.');
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

    // Hari Minggu = kas libur (bisa dinonaktifkan admin di Pengaturan
    // Aplikasi) - dicek PALING AWAL sebelum guard eksklusivitas di bawah,
    // supaya pesannya jelas menunjuk alasan hari libur, bukan "sudah dibuka
    // orang lain" yang membingungkan.
    if (\App\Models\AppSetting::current()->libur_kas_minggu && now()->isSunday()) {
        return redirect()->route('kasir.kelola')->with('error', 'Hari Minggu kas libur - Open Saldo tidak bisa dilakukan. Ubah pengaturan ini di Pengaturan Aplikasi kalau toko memang buka hari Minggu.');
    }

    // Open saldo bersifat EKSKLUSIF - hanya SATU sesi kas boleh aktif dalam
    // satu waktu, SIAPA PUN rolenya (termasuk Administrator). Sebelumnya
    // pengecekan ini hanya melihat kas milik user yang sedang login sendiri
    // (where id_user = auth()->id()), jadi Administrator tetap bisa buka
    // sesi kas KEDUA sementara punya Kasir masih terbuka - akibatnya dua
    // sesi kas aktif bersamaan, dan prosesBayar()/storeKasManual() (yang
    // mencari kas aktif tanpa peduli siapa pemiliknya) diam-diam mencatat
    // pembayaran ke SALAH SATU sesi secara tidak menentu, mengacaukan
    // pertanggungjawaban uang fisik per kasir. Sekarang dicek GLOBAL: kas
    // aktif milik siapa pun (termasuk diri sendiri) memblokir open saldo
    // baru sampai sesi itu di-closing.
    // Cek + insert di atas dibungkus transaksi, dan sebelum cek dilakukan
    // kita lockForUpdate() baris app_settings (selalu ada persis 1 baris)
    // sebagai "mutex" global. Tanpa ini, dua request storeKas() yang hampir
    // bersamaan bisa sama-sama lolos SELECT "belum ada kas aktif" sebelum
    // salah satu sempat INSERT - hasilnya dua sesi kas open sekaligus,
    // padahal baris di atas justru menegaskan itu harus eksklusif. Karena
    // belum ada baris kas_harian yang bisa di-lock (sesi barunya belum
    // ada), app_settings dipakai sebagai baris pengganti untuk lock.
    return DB::transaction(function () use ($nominal) {
        DB::table('app_settings')->lockForUpdate()->first();

        $kasAktifSiapaPun = KasSesiService::aktif();

        if ($kasAktifSiapaPun) {
            $tglBuka = \Carbon\Carbon::parse($kasAktifSiapaPun->tgl_kas)->format('d/m/Y');
            if ((int) $kasAktifSiapaPun->id_user === (int) auth()->id()) {
                return redirect()->route('kasir.kelola')->with('error', "Sesi kas Anda (dibuka {$tglBuka}) masih berjalan dan belum di-closing. Lakukan closing kas dulu sebelum membuka sesi baru.");
            }
            $namaKasirAktif = DB::table('users')
                ->leftJoin('karyawan', 'users.id_karyawan', '=', 'karyawan.id_karyawan')
                ->where('users.id_user', $kasAktifSiapaPun->id_user)
                ->select(DB::raw('COALESCE(karyawan.nama_karyawan, users.username) as nama'))
                ->value('nama') ?? 'pengguna lain';
            return redirect()->route('kasir.kelola')->with('error', "Sesi kas milik {$namaKasirAktif} (dibuka {$tglBuka}) belum di-closing. Hanya satu sesi kas yang boleh aktif dalam satu waktu - closing dulu sebelum membuka sesi baru (Administrator dapat menutup paksa).");
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
    });
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
    $kas = KasSesiService::aktif();

    // Proteksi: Tidak bisa closing jika tidak ada kas aktif
    if (!$kas) {
        return redirect()->route('kasir.kelola')->with('error', 'Tidak ada alur kas aktif yang bisa ditutup!');
    }

    // Closing: pemilik sesi, atau Administrator (tutup paksa, mis. kasir lupa closing).
    if (!self::bolehTutup($kas)) {
        return redirect()->route('kasir.kelola')->with('error', 'Sesi kas ini milik kasir lain. Hanya pemilik sesi atau Administrator yang dapat melakukan closing.');
    }
    $mulaiSesi = KasSesiService::mulai($kas);
    $akhirSesi = KasSesiService::akhir($kas);
    $tutupPaksa = (int) $kas->id_user !== (int) auth()->id();

    // REKAP TRANSAKSI - nota yang ORDER-nya dibuat hari ini (tgl_order),
    // dipecah per Mesin/Divisi produk (Jasa & produk tanpa mesin masuk
    // "Jasa / Lain-lain"). Ini "Nota Order Cetak" di referensi.
    $rekapKategori = DB::table('transaksi_detail')
        ->join('transaksi', 'transaksi_detail.id_transaksi', '=', 'transaksi.id_transaksi')
        ->leftJoin('produk', 'transaksi_detail.id_produk', '=', 'produk.id_produk')
        ->leftJoin('mesin', 'produk.id_mesin', '=', 'mesin.id_mesin')
        ->where('transaksi.tgl_order', '>=', $mulaiSesi)
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
        ->where('tgl_order', '>=', $mulaiSesi)
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
        ->where('pembayaran_riwayat.tgl_bayar', '>=', $mulaiSesi)
        ->where('pembayaran_riwayat.tgl_bayar', '<=', $akhirSesi)
        ->select('pembayaran_riwayat.nominal', 'pembayaran_riwayat.metode', 'transaksi.tgl_order')
        ->get();
    // Pelunasan = nota yang dibuat SEBELUM sesi ini dimulai; sisanya transaksi sesi ini.
    $bayarPelunasan = (float) $pembayaranHariIni->filter(fn ($p) => \Carbon\Carbon::parse($p->tgl_order)->lt($mulaiSesi))->sum('nominal');
    $bayarHariIni = (float) $pembayaranHariIni->filter(fn ($p) => !\Carbon\Carbon::parse($p->tgl_order)->lt($mulaiSesi))->sum('nominal');

    // Hitung Rincian Pemasukan dari Riwayat Pembayaran (per metode, seluruh
    // pembayaran hari ini apa pun tanggal order-nya - dipakai utk mutasi laci).
    $pemasukan = DB::table('pembayaran_riwayat')
        ->where('tgl_bayar', '>=', $mulaiSesi)
        ->where('tgl_bayar', '<=', $akhirSesi)
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
        'bayarPelunasan', 'bayarHariIni', 'manualMasuk', 'mulaiSesi', 'tutupPaksa'
    ));
}

public function simpanClosing(Request $request)
{
    $request->validate([
        'status_rolling' => 'required|in:Rolling,Tidak Rolling',
    ]);

    // Closing SELALU boleh (termasuk hari Minggu libur): sesi kas tetap open
    // sampai di-closing, jadi sesi yang dibuka hari Sabtu harus bisa ditutup.

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
        if (!self::bolehTutup($kas)) {
            throw new \Exception('Hanya pemilik sesi kas atau Administrator yang dapat melakukan closing.');
        }
        $tutupPaksa = (int) $kas->id_user !== (int) auth()->id();
        if ($tutupPaksa && trim((string) $request->keterangan) === '') {
            throw new \Exception('Tutup paksa sesi kas milik kasir lain wajib disertai alasan pada kolom keterangan.');
        }
        $mulaiSesi = KasSesiService::mulai($kas);

        // Total pemasukan DIHITUNG ULANG dari pembayaran_riwayat di server
        // (bukan dipercaya dari hidden field), sama seperti previewClosing() -
        // supaya laporan yang tersimpan permanen tidak bisa diselipi angka
        // yang tidak sesuai riwayat pembayaran sesungguhnya.
        $pemasukan = DB::table('pembayaran_riwayat')
            ->where('tgl_bayar', '>=', $mulaiSesi)
            ->where('tgl_bayar', '<=', now())
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
            'keterangan_closing' => $request->keterangan,
            'status_rolling' => $request->status_rolling,
            'updated_at' => now()
        ]);

        if ($tutupPaksa) {
            \App\Services\AuditLogger::log('updated', 'kas_harian', (int) $id_kas, ['status_lock' => 'open'], ['status_lock' => 'closed'], 'Tutup paksa sesi kas milik user #' . $kas->id_user . ': ' . $request->keterangan);
        }

        DB::commit();
        return redirect()->route('kasir.kelola')->with('success', 'Kas Berhasil Ditutup dan Dikunci. Selisih: Rp ' . number_format($selisih, 0, ',', '.'));

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', $e->getMessage());
    }
}

/**
 * Cetak nota transaksi. Format kertas (thermal/a4/continuous) diambil dari
 * Pengaturan Aplikasi GLOBAL (dulu dipilih manual per transaksi lewat
 * dropdown "Nota Thermal"/"Invoice A4" - kasir tidak perlu pilih format
 * tiap kali cetak lagi, satu view yang sama, cuma CSS lebar kertasnya
 * beda, supaya tidak perlu template terpisah per format).
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
        ->select('nama_file', 'qty', 'subtotal', 'biaya_cetak', 'biaya_desain', 'biaya_finishing', 'p_ukuran', 'l_ukuran', 'id_bahan', 'id_produk', 'id_jasa')
        ->get();

    $totalTerbayar = DB::table('pembayaran_riwayat')->where('id_transaksi', $id)->sum('nominal') ?? 0;
    $totalCetak = (float) $items->sum('biaya_cetak');
    $totalDesain = (float) $items->sum('biaya_desain');
    $totalFinishing = (float) $items->sum('biaya_finishing');

    // Pembulatan MURNI tampilan nota (lihat AppSetting::bulatkan() &
    // migrasi 2026_09_18_120000) - transaksi.total_bayar, pembayaran_riwayat,
    // dan jurnal_umum TIDAK disentuh/diubah sama sekali di sini, tetap
    // presisi asli untuk keperluan akuntansi. Krg. Bayar/Kembali di nota
    // sengaja dihitung terhadap nominal yang SUDAH dibulatkan (kalau beda)
    // - supaya nota tidak menampilkan "kembalian" yang sebenarnya cuma
    // selisih pembulatan (tidak ada uang fisik yang sungguhan dikembalikan
    // untuk selisih sekecil itu).
    $totalTagihanAsli = (float) $transaksi->total_bayar;
    $totalTagihanBulat = \App\Models\AppSetting::bulatkan($totalTagihanAsli);
    $pembulatan = $totalTagihanBulat - $totalTagihanAsli;

    // "Kembali" hanya bermakna kalau total yang sudah dibayar MELEBIHI
    // tagihan (dibayar tunai lebih lalu dikembalikan kembaliannya) - aplikasi
    // ini tidak menyimpan "uang diterima" terpisah dari "nominal diterapkan
    // ke nota" per pembayaran, jadi ini pendekatan terbaik yang bisa dihitung
    // dari data yang benar-benar ada.
    $kembali = max(0, $totalTerbayar - $totalTagihanBulat);
    $kurangBayar = max(0, $totalTagihanBulat - $totalTerbayar);

    // Info deposit - dipakai buat baris "Penggunaan deposit pada transaksi
    // ini" & "Saldo deposit anda saat ini" di nota (lihat referensi user).
    $depositTerpakaiTransaksiIni = (float) DB::table('konsumen_deposit')
        ->where('id_transaksi', $id)
        ->where('tipe', 'Pemakaian')
        ->sum('nominal');
    $saldoDepositSaatIni = KonsumenDepositController::saldoUntuk((int) $transaksi->id_konsumen);
    // Blok deposit di nota cuma relevan kalau konsumen ini memang PERNAH
    // pakai deposit (di transaksi ini atau transaksi lain) - konsumen yang
    // tidak pernah sama sekali menyetor/memakai deposit tidak perlu melihat
    // baris "Saldo deposit Rp 0" yang tidak bermakna apa pun buat mereka.
    $konsumenPernahPakaiDeposit = DB::table('konsumen_deposit')->where('id_konsumen', $transaksi->id_konsumen)->exists();

    // Nota Penjualan Barang (transaksi isinya bahan baku dan/atau barang
    // custom/lainnya - tidak ada produk/jasa dari Input Order sama sekali)
    // defaultnya A4/Continuous Form, TIDAK PERNAH Thermal - permintaan
    // eksplisit user, nota jual barang lebih pas sebagai invoice
    // (A4/Continuous) daripada struk kasir sempit. Nota biasa (ada
    // produk/jasa) tetap ikut format global apa adanya. Item custom (lihat
    // PenjualanBarangController::store()) juga tidak punya id_bahan, jadi
    // cek-nya sengaja TIDAK mensyaratkan id_bahan - cukup pastikan tidak ada
    // baris id_produk/id_jasa (ciri transaksi dari Input Order).
    $formatGlobal = \App\Models\AppSetting::current()->format_nota ?? 'a4';
    $dariPenjualanBarang = $items->isNotEmpty()
        && $items->every(fn ($i) => !$i->id_produk && !$i->id_jasa);
    $format = ($dariPenjualanBarang && $formatGlobal === 'thermal') ? 'a4' : $formatGlobal;

    return view('kasir.print_nota', compact(
        'transaksi', 'items', 'totalTerbayar', 'format',
        'totalCetak', 'totalDesain', 'totalFinishing', 'kembali', 'kurangBayar',
        'totalTagihanAsli', 'totalTagihanBulat', 'pembulatan',
        'depositTerpakaiTransaksiIni', 'saldoDepositSaatIni', 'konsumenPernahPakaiDeposit'
    ));
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
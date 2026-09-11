@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto pb-20">

    <x-page-header title="Input Pembayaran">
        <x-slot:actions>
            <x-button href="{{ route('kasir.index') }}" variant="outline">
                <i class="fas fa-arrow-left"></i> Kembali
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- LOCK KASIR - $kasAktif dibagikan global ke semua view lewat
         AppServiceProvider (view composer '*'), jadi tersedia di sini tanpa
         perlu dioper eksplisit dari KasirController::bayarForm(). Sebelumnya
         TIDAK ada peringatan sama sekali di halaman ini - kasir baru tahu
         kas belum dibuka SETELAH mengisi form lengkap & klik Bayar (pesan
         error muncul dari validasi server di prosesBayar()). Sekarang
         dikunci dari awal: tombol Bayar dinonaktifkan kalau kas belum
         dibuka, supaya waktu kasir tidak terbuang mengisi form yang pasti
         ditolak. Tombol "Simpan" (tanpa uang masuk, cuma menyimpan status
         DP/antrean) TETAP aktif - tidak menyentuh kas sama sekali. --}}
    @if(!$kasAktif)
    <div class="mb-5 flex items-center gap-3 bg-amber-50 border-2 border-amber-200 rounded-2xl px-5 py-4">
        <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 text-lg">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="flex-1">
            <p class="text-sm font-black text-amber-800 uppercase tracking-wide">Kas Kasir Belum Dibuka</p>
            <p class="text-xs text-amber-600 mt-0.5">Anda tidak bisa mencatat pembayaran sebelum membuka saldo kas hari ini. Nota tetap bisa disimpan sebagai antrean (tanpa uang masuk).</p>
        </div>
        <x-button href="{{ route('kasir.kelola') }}" variant="warning" class="shrink-0">
            <i class="fas fa-wallet"></i> Buka Kas
        </x-button>
    </div>
    @endif

    {{-- INFO NOTA & KONSUMEN - satu baris ringkas (nama konsumen, nomor
         nota, golongan). Thumbnail acuan order TIDAK lagi ditampilkan di
         sini - sebelumnya cuma menampilkan foto item PERTAMA seolah itu
         "foto konsumen", padahal satu nota bisa berisi banyak item dengan
         file acuan yang berbeda-beda. Sekarang tiap item di Rincian Barang
         menampilkan thumbnail-nya masing-masing (lihat kolom Deskripsi di
         bawah), supaya kasir tahu persis gambar acuan SETIAP barang. --}}
    <div class="flex items-center gap-4 bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-5">
        <div class="w-11 h-11 rounded-xl bg-indigo-50 border border-indigo-100 shrink-0 flex items-center justify-center text-indigo-400">
            <i class="fas fa-user text-lg"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-bold text-slate-800 truncate">{{ $transaksi->nama_konsumen }}</p>
            <p class="text-xs text-slate-400">Nota #{{ $transaksi->nomor_nota }} &middot; {{ \Carbon\Carbon::parse($transaksi->tgl_order)->format('d M Y H:i') }}</p>
        </div>
        <span class="shrink-0 px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-bold uppercase tracking-wide">{{ $transaksi->nama_golongan ?? '-' }}</span>
    </div>

    <form action="{{ url('/kasir/proses-bayar') }}" method="POST" id="form-pembayaran">
        @csrf
        <input type="hidden" name="id_transaksi" value="{{ $transaksi->id_transaksi }}">

        {{-- BAGIAN 1: RINCIAN BARANG --}}
        <x-card class="mb-5">
            <h4 class="font-black text-xs uppercase text-slate-400 tracking-widest mb-4 pb-3 border-b border-slate-50">Rincian Barang &amp; Penyesuaian Harga</h4>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-[10px] uppercase text-slate-400 border-b border-slate-100">
                            <th class="text-left py-2 pr-2 w-8">#</th>
                            <th class="text-left py-2 px-2">Deskripsi</th>
                            <th class="text-right py-2 px-2">Harga</th>
                            <th class="text-center py-2 px-2">Qty</th>
                            <th class="text-right py-2 pl-2">Subtotal</th>
                            <th class="w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($items as $i => $item)
                        {{-- Harga per satuan hitung = m² untuk produk metode 'ukuran',
                             pcs untuk 'quantity'/jasa. Pakai qty_hitung (dihitung di
                             KasirController::bayarForm), BUKAN $item->qty mentah - untuk
                             produk 'ukuran', qty mentah itu jumlah pcs bukan luas, jadi
                             Harga hasil bagi-mundur akan salah kali lipat & tidak cocok
                             dengan Price List kalau dipakai. --}}
                        @php
                            $hargaSatuan = ($item->qty_hitung > 0) ? ($item->subtotal + $item->potongan - $item->biaya_cetak - $item->biaya_desain - $item->biaya_finishing) / $item->qty_hitung : 0;
                            // Cetak/Desain/Finishing/Potongan TIDAK lagi masing-masing jadi
                            // kolom sendiri (dulu 10 kolom, tabel jadi sempit & ramai) -
                            // dirangkum jadi satu baris kecil di bawah Deskripsi, sama
                            // seperti pola "Cetak x • Desain y" di kartu keranjang Kasir
                            // Utama. Detail lengkapnya tetap bisa diedit satu-satu lewat
                            // modal Custom Harga (ikon pensil) - cuma tidak lagi makan
                            // tempat sebagai kolom terpisah di tabel utama.
                            $bagianBiaya = collect(['Cetak' => $item->biaya_cetak, 'Desain' => $item->biaya_desain, 'Finishing' => $item->biaya_finishing])
                                ->filter(fn ($v) => $v > 0)
                                ->map(fn ($v, $k) => $k . ' ' . number_format($v, 0, ',', '.'))
                                ->implode(' &bull; ');
                            if ($item->potongan > 0) {
                                $bagianBiaya .= ($bagianBiaya ? ' &bull; ' : '') . '-Rp ' . number_format($item->potongan, 0, ',', '.') . ' Potongan';
                            }
                        @endphp
                        <tr class="item-row" data-id="{{ $item->id_detail }}">
                            <td class="py-3 pr-2 text-slate-400">{{ $i + 1 }}</td>
                            <td class="py-3 px-2">
                                <div class="flex items-center gap-2.5">
                                    @if($item->thumbnail)
                                        <img src="{{ asset('storage/'.$item->thumbnail) }}" class="w-10 h-10 rounded-lg object-cover border border-slate-100 shrink-0">
                                    @else
                                        <div class="w-10 h-10 rounded-lg bg-slate-50 border border-slate-100 shrink-0 flex items-center justify-center text-slate-300">
                                            <i class="far fa-image text-sm"></i>
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-700 uppercase truncate">{{ $item->nama_produk ?? $item->nama_jasa }}</div>
                                        <div class="text-xs text-slate-400 italic truncate">{{ $item->nama_file }}</div>
                                        <div class="disp-biaya-tambahan text-[10px] text-slate-400 mt-0.5">{!! $bagianBiaya !!}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-2 text-right disp-harga font-bold text-slate-700">Rp {{ number_format($hargaSatuan, 0, ',', '.') }}</td>
                            <td class="py-3 px-2 text-center text-slate-500">
                                @if($item->metode_hitung == 'ukuran')
                                    {{ number_format($item->qty_hitung, 2, ',', '.') }} m²
                                    <div class="text-[10px] text-slate-300">{{ $item->qty }} pcs</div>
                                @else
                                    {{ $item->qty }} {{ $item->id_jasa ? 'Jasa' : 'Pcs' }}
                                @endif
                            </td>
                            <td class="py-3 pl-2 text-right disp-subtotal font-black text-slate-800">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            <td class="py-3 text-center">
                                <button type="button" onclick="openCustomHarga({{ $item->id_detail }})" class="w-7 h-7 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all" title="Custom Harga">
                                    <i class="far fa-edit text-xs"></i>
                                </button>
                            </td>

                            {{-- Nilai efektif dikirim lewat hidden input, diedit HANYA lewat
                                 modal Custom Harga (bukan inline) supaya kelima komponen
                                 (harga/cetak/desain/finishing/potongan) selalu disesuaikan
                                 bersamaan & konsisten, tidak ada yang kelewat. --}}
                            <input type="hidden" class="inp-harga" name="items[{{ $item->id_detail }}][harga]" value="{{ round($hargaSatuan) }}">
                            <input type="hidden" class="inp-cetak" name="items[{{ $item->id_detail }}][biaya_cetak]" value="{{ (int) $item->biaya_cetak }}">
                            <input type="hidden" class="inp-desain" name="items[{{ $item->id_detail }}][biaya_desain]" value="{{ (int) $item->biaya_desain }}">
                            <input type="hidden" class="inp-finishing" name="items[{{ $item->id_detail }}][biaya_finishing]" value="{{ (int) $item->biaya_finishing }}">
                            <input type="hidden" class="inp-potongan" name="items[{{ $item->id_detail }}][potongan]" value="{{ (int) $item->potongan }}">
                            {{-- inp-qty menyimpan qty_hitung (satuan hitung harga - m² atau
                                 pcs), BUKAN string berformat Rupiah, jadi dibaca dengan
                                 parseFloat() biasa di JS (lihat calculateInvoice), tidak lewat
                                 cleanRupiah() yang akan salah membuang titik desimalnya. --}}
                            <input type="hidden" class="inp-qty" value="{{ number_format($item->qty_hitung, 4, '.', '') }}" data-satuan="{{ $item->metode_hitung == 'ukuran' ? 'm²' : ($item->id_jasa ? 'jasa' : 'pcs') }}">
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- TOTAL & DISKON - dimampatkan (dulu p-8 dgn grand total text-4xl,
                 sekarang lebih ringkas) supaya "Rincian Barang" di atas jadi
                 proporsi utama halaman, bukan kotak total yang paling menonjol. --}}
            <div class="mt-5 bg-slate-900 rounded-2xl p-5 text-white relative overflow-hidden">
                @if($totalTerbayar > 0)
                <div class="absolute top-0 right-0 bg-amber-500 text-slate-900 px-4 py-1.5 rounded-bl-xl font-black text-[10px] uppercase tracking-widest">
                    Piutang: Rp {{ number_format($totalTerbayar, 0, ',', '.') }}
                </div>
                @endif

                <div class="flex justify-between items-center mb-2 opacity-70 text-xs">
                    <span class="font-bold uppercase tracking-widest">Total Nota</span>
                    <span id="total-kotor" class="text-white">Rp 0</span>
                </div>

                <div class="flex justify-between items-center mb-3">
                    <span class="text-xs font-bold uppercase tracking-widest text-indigo-300">Diskon (Nota)</span>
                    <div class="relative">
                        <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-white/50 font-bold text-[11px]">Rp</span>
                        <input type="text" id="input-diskon" name="diskon" value="0"
                            class="format-ribuan w-32 h-8 bg-white/10 border-none rounded-lg text-right text-sm text-white font-bold focus:ring-2 focus:ring-indigo-400 pl-8">
                    </div>
                </div>

                @if($totalTerbayar > 0)
                <div class="flex justify-between items-center mb-2 text-emerald-400 border-t border-white/10 pt-2 text-xs">
                    <span class="font-bold uppercase tracking-widest">Sudah Dibayar</span>
                    <span class="font-bold">- Rp {{ number_format($totalTerbayar, 0, ',', '.') }}</span>
                </div>
                <input type="hidden" id="total_terbayar_hidden" value="{{ $totalTerbayar }}">
                @else
                <input type="hidden" id="total_terbayar_hidden" value="0">
                @endif

                <div class="border-t border-white/20 pt-3 flex justify-between items-center">
                    <span class="text-sm font-black italic tracking-tighter uppercase text-indigo-400">
                        {{ $totalTerbayar > 0 ? 'Sisa Tagihan' : 'Grand Total' }}
                    </span>
                    <input type="hidden" id="grand_total_hidden" name="total_akhir" value="0">
                    <span id="display-grand-total" class="text-2xl font-black">Rp 0</span>
                </div>
            </div>
        </x-card>

        {{-- BAGIAN 2: VALIDASI PEMBAYARAN --}}
        <x-card>
            @if($saldoDeposit > 0)
            {{-- Notifikasi deposit - murni informasi + auto-fill lewat JS
                 (autoTerapkanDeposit), BUKAN kuncian. Kasir tetap bebas ganti
                 ke Tunai/Transfer atau ubah nominal kapan saja - selaras
                 dengan prinsip "warn don't block" yang dipakai di seluruh
                 halaman ini. --}}
            <div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-100 rounded-xl px-4 py-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-500 text-white flex items-center justify-center shrink-0 text-sm">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="flex-1">
                    <p class="text-xs font-black text-emerald-700 uppercase tracking-wide">Deposit tersedia Rp {{ number_format($saldoDeposit, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-emerald-600 mt-0.5">Otomatis diterapkan. Ganti Metode Pembayaran jika ingin pakai Tunai/Transfer.</p>
                </div>
            </div>
            @elseif($depositSudahHabis)
            {{-- Konsumen ini PERNAH setor deposit (beda dari yang memang
                 tidak pernah punya sama sekali) tapi saldonya sekarang 0 -
                 supaya kasir tidak bingung kenapa opsi Deposit di bawah
                 terkunci/tidak otomatis terisi padahal pelanggan ini
                 langganan pakai deposit. --}}
            <div class="mb-5 flex items-center gap-3 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3">
                <div class="w-8 h-8 rounded-lg bg-slate-300 text-white flex items-center justify-center shrink-0 text-sm">
                    <i class="fas fa-ban"></i>
                </div>
                <div class="flex-1">
                    <p class="text-xs font-black text-slate-500 uppercase tracking-wide">Deposit Sudah Habis Terpakai</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Konsumen ini pernah setor deposit, tapi saldonya sudah habis - tidak bisa dipakai lagi sampai ada setoran baru.</p>
                </div>
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    {{-- Jenis Pembayaran & Metode Pembayaran sekarang berdampingan
                         (dulu masing-masing selebar penuh & bertumpuk, makan tempat
                         vertikal dua kali lipat) - keduanya sama-sama grup pilihan
                         singkat, wajar disandingkan dalam satu baris. --}}
                    <div class="grid grid-cols-2 gap-3">
                        {{-- Sebelumnya status DP/Lunas cuma DISIMPULKAN diam-diam dari
                             angka yang diketik - toggle ini murni bantuan UX supaya
                             niatnya jelas dari awal. Status akhir yang TERSIMPAN tetap
                             dihitung server dari nominal vs sisa tagihan, bukan dari
                             pilihan toggle ini. --}}
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase mb-2">Jenis Pembayaran</label>
                            {{-- grid-cols-1 - 2 pilihan bertumpuk ke bawah, masing-masing
                                 selebar penuh kolomnya. --}}
                            <div class="grid grid-cols-1 gap-1.5">
                                <label class="tipe-bayar-option relative flex items-center justify-center gap-1.5 py-2.5 rounded-xl border-2 border-indigo-500 bg-indigo-50 text-indigo-700 font-black text-[10px] uppercase cursor-pointer transition-all">
                                    <input type="radio" name="tipe_bayar" value="lunas" class="sr-only" checked onchange="onTipeBayarChange()">
                                    <i class="fas fa-check-circle"></i> Lunas
                                </label>
                                <label class="tipe-bayar-option relative flex items-center justify-center gap-1.5 py-2.5 rounded-xl border-2 border-slate-100 text-slate-400 font-black text-[10px] uppercase cursor-pointer transition-all">
                                    <input type="radio" name="tipe_bayar" value="dp" class="sr-only" onchange="onTipeBayarChange()">
                                    <i class="fas fa-hand-holding-usd"></i> DP
                                </label>
                            </div>
                        </div>

                        {{-- Metode Pembayaran disederhanakan dari <select> polos jadi
                             pill-button minimalist - TANPA mengurangi fungsi:
                             Tunai/Debit/Transfer/Deposit keempatnya tetap ada &
                             tetap men-submit field metode_pembayaran seperti
                             sebelumnya (Debit ditambahkan - lihat migrasi
                             2026_09_11_250000: kartu debit sebelumnya tidak
                             punya opsi sendiri, kasir terpaksa mencatatnya
                             sebagai Transfer padahal beda sumber uang). --}}
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase mb-2">Metode Pembayaran</label>
                            {{-- grid-cols-2 SENGAJA dipertahankan (bukan grid-cols-4/
                                 sebaris) - CSS grid membagi tiap kolom SAMA RATA (1fr),
                                 jadi keempat pill (Tunai/Debit/Transfer/Deposit) otomatis
                                 punya lebar identik apa pun panjang teksnya, tidak ada yang
                                 lebih panjang dari yang lain. grid-cols-4 sebaris akan
                                 membuat teks "Transfer" mepet/terpotong di layar sempit. --}}
                            <div class="grid grid-cols-2 gap-1.5">
                                <label class="metode-bayar-option relative flex items-center justify-center gap-1.5 py-2.5 rounded-xl border-2 border-indigo-500 bg-indigo-50 text-indigo-700 font-black text-[10px] uppercase cursor-pointer transition-all">
                                    <input type="radio" name="metode_pembayaran" id="metode-tunai" value="Tunai" class="sr-only" checked onchange="onMetodePembayaranChange()">
                                    <i class="fas fa-money-bill-wave"></i> Tunai
                                </label>
                                <label class="metode-bayar-option relative flex items-center justify-center gap-1.5 py-2.5 rounded-xl border-2 border-slate-100 text-slate-400 font-black text-[10px] uppercase cursor-pointer transition-all">
                                    <input type="radio" name="metode_pembayaran" value="Debit" class="sr-only" onchange="onMetodePembayaranChange()">
                                    <i class="fas fa-credit-card"></i> Debit
                                </label>
                                <label class="metode-bayar-option relative flex items-center justify-center gap-1.5 py-2.5 rounded-xl border-2 border-slate-100 text-slate-400 font-black text-[10px] uppercase cursor-pointer transition-all">
                                    <input type="radio" name="metode_pembayaran" value="Transfer" class="sr-only" onchange="onMetodePembayaranChange()">
                                    <i class="fas fa-university"></i> Transfer
                                </label>
                                <label class="metode-bayar-option relative flex items-center justify-center gap-1.5 py-2.5 rounded-xl border-2 border-slate-100 text-slate-400 font-black text-[10px] uppercase cursor-pointer transition-all {{ $saldoDeposit <= 0 ? 'opacity-40 cursor-not-allowed' : '' }}"
                                    title="{{ $saldoDeposit > 0 ? '' : ($depositSudahHabis ? 'Deposit konsumen ini sudah habis terpakai, tidak bisa dipakai lagi.' : 'Konsumen ini belum pernah setor deposit.') }}">
                                    <input type="radio" name="metode_pembayaran" value="Deposit" class="sr-only" {{ $saldoDeposit <= 0 ? 'disabled' : '' }} onchange="onMetodePembayaranChange()">
                                    <i class="fas fa-wallet"></i> Deposit
                                </label>
                            </div>
                        </div>
                    </div>
                    <div id="wrapper-bank" class="hidden">
                        <label class="block text-[10px] font-black text-slate-500 uppercase mb-2">Rekening Tujuan</label>
                        <select name="id_bank" id="input-bank" class="w-full h-11 rounded-xl border-slate-200 text-sm font-bold focus:ring-4 focus:ring-indigo-100 transition-all">
                            <option value="">-- Pilih Rekening --</option>
                            @foreach($banks as $b)
                                <option value="{{ $b->id_bank }}">{{ $b->nama_bank }} - {{ $b->nomor_rekening }} ({{ $b->atas_nama }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="wrapper-deposit-info" class="hidden bg-indigo-50 border border-indigo-100 rounded-xl p-3">
                        <p class="text-[11px] font-bold text-indigo-600 uppercase tracking-wide">
                            Saldo deposit: Rp {{ number_format($saldoDeposit, 0, ',', '.') }}
                        </p>
                        <p class="text-[10px] text-indigo-400 mt-0.5">Jumlah bayar tidak boleh melebihi saldo ini.</p>
                    </div>
                    <div>
                        <label id="label-uang-diterima" class="block text-[10px] font-black text-slate-500 uppercase mb-2">Uang Diterima</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 font-black text-slate-400 text-lg">Rp</span>
                            <input type="text" id="input-bayar-mask"
                                   class="format-ribuan w-full h-12 pl-12 rounded-xl border-2 border-slate-100 bg-white text-lg font-black text-indigo-600 focus:border-indigo-500 transition-all"
                                   placeholder="0">
                            <input type="hidden" name="jumlah_bayar" id="input-bayar-asli">
                        </div>
                        <div class="mt-2 flex justify-end">
                            <button type="button" id="btn-bayar-pas" onclick="setUangPas()" class="flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-lg text-[11px] font-black uppercase transition-all border border-indigo-100 shadow-sm">
                                <i class="fas fa-bolt"></i> Bayar Sisa Tagihan
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Status, kalkulasi, & tombol aksi digabung dalam SATU kotak -
                     sebelumnya tombol "Simpan Ke Monitoring"/"Bayar Sekarang"
                     terpisah di bawah kartu ini (baris sendiri di paling bawah
                     halaman), sekarang jadi satu unit dengan status supaya tidak
                     perlu scroll jauh untuk menemukan aksi finalnya. --}}
                <div class="bg-indigo-600 rounded-2xl p-5 text-white shadow-xl flex flex-col relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10 text-5xl"><i class="fas fa-cash-register"></i></div>
                    <div class="flex justify-between items-center mb-1.5">
                        <span class="text-[10px] font-black uppercase tracking-widest opacity-70">Status</span>
                        <span id="status-label" class="bg-white/20 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-tighter">MENUNGGU...</span>
                    </div>
                    <div class="h-px bg-white/10 w-full mb-3"></div>
                    <span id="label-selisih" class="text-[10px] font-black italic uppercase opacity-70 block mb-1">Kalkulasi</span>
                    <div id="display-selisih" class="text-2xl font-black mb-4">Rp 0</div>

                    <div class="mt-auto grid grid-cols-2 gap-2 relative">
                        <button type="button" onclick="simpanSaja()" class="flex items-center justify-center gap-1.5 py-3 bg-white/10 hover:bg-white/20 rounded-xl font-bold text-[11px] uppercase tracking-wide transition-all">
                            <i class="fas fa-inbox"></i> Simpan
                        </button>
                        <button type="submit" {{ !$kasAktif ? 'disabled title="Kas belum dibuka - lihat peringatan di atas"' : '' }} class="flex items-center justify-center gap-1.5 py-3 bg-white text-indigo-600 hover:bg-indigo-50 rounded-xl font-bold text-[11px] uppercase tracking-wide transition-all disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-white">
                            <i class="fas fa-cash-register"></i> Bayar
                        </button>
                    </div>
                </div>
            </div>
        </x-card>
    </form>

    {{-- MODAL CUSTOM HARGA - satu modal dipakai bergantian untuk semua item
         (diisi ulang lewat JS tiap kali tombol edit satu baris diklik),
         bukan satu modal per item - lebih ringan & konsisten dengan pola
         openEditX(data) yang sudah dipakai di halaman master lain. --}}
    <x-modal id="custom-harga" title="Custom Harga" maxWidth="sm">
        <p id="ch-nama-item" class="text-xs text-slate-400 uppercase font-bold mb-1 -mt-2"></p>
        {{-- Acuan per-unit murni informasi (Biaya Cetak / Qty), dihitung LIVE
             lewat hitungPreviewCustomHarga() - Biaya Cetak sekarang komponen
             UTAMA Subtotal (bukan lagi Harga x Qty ditambah Biaya Cetak
             sebagai ekstra), jadi tidak ada lagi field "Harga" per-unit
             terpisah yang perlu diketik/disimpan sendiri. --}}
        <p id="ch-harga-acuan" class="text-[11px] text-slate-400 mb-5"></p>

        <div class="space-y-4">
            {{-- Biaya Cetak SELALU tampil, defaultnya = subtotal saat item ini
                 pertama dibuat (dipulihkan di openCustomHarga() dari
                 subtotal-potongan-desain-finishing, supaya nilainya
                 konsisten baik untuk item baru maupun item lama sebelum
                 Biaya Cetak jadi komponen utama). --}}
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Biaya Cetak</label>
                <input type="text" id="ch-cetak" class="format-ribuan-modal w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
            </div>

            {{-- Biaya Desain/Finishing disembunyikan default, cuma muncul kalau
                 item ini memang sudah punya nilainya (dari nota asli) atau
                 operator sengaja menambahkannya lewat tombol di bawah -
                 selaras dengan penjualan/create.blade.php. --}}
            <div id="ch-wrapper-desain" class="hidden">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Biaya Desain</label>
                <input type="text" id="ch-desain" class="format-ribuan-modal w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
            </div>
            <div id="ch-wrapper-finishing" class="hidden">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Biaya Finishing</label>
                <input type="text" id="ch-finishing" class="format-ribuan-modal w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
            </div>
            <div class="flex flex-wrap gap-x-4 gap-y-1">
                <button type="button" id="ch-btn-tambah-desain" onclick="tampilkanChBiaya('desain')" class="text-indigo-500 hover:text-indigo-700 text-[10px] font-bold uppercase tracking-widest underline underline-offset-4 decoration-dotted">
                    <i class="fas fa-plus-circle"></i> Biaya Desain
                </button>
                <button type="button" id="ch-btn-tambah-finishing" onclick="tampilkanChBiaya('finishing')" class="text-indigo-500 hover:text-indigo-700 text-[10px] font-bold uppercase tracking-widest underline underline-offset-4 decoration-dotted">
                    <i class="fas fa-plus-circle"></i> Biaya Finishing
                </button>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Potongan Tunai (Diskon Item Ini)</label>
                <input type="text" id="ch-potongan" class="format-ribuan-modal w-full bg-rose-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-rose-600 focus:ring-2 focus:ring-rose-500/20 shadow-inner">
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase">Total Potongan</span>
                <span id="ch-total-potongan" class="text-sm font-bold text-rose-500">Rp 0</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-black text-slate-500 uppercase">Subtotal Baru</span>
                <span id="ch-preview-subtotal" class="text-xl font-black text-indigo-600">Rp 0</span>
            </div>
        </div>

        {{-- Kembalikan nilai SEMUA field di modal ini ke kondisi saat modal
             dibuka (nota asli sebelum diedit) - berguna kalau operator sudah
             sempat mengubah-ubah Biaya Desain/Finishing/Potongan lalu
             berubah pikiran, tanpa perlu tutup modal & buka ulang. --}}
        <button type="button" onclick="kembalikanNilaiAsliCh()" class="mt-4 w-full flex items-center justify-center gap-2 py-2.5 rounded-xl border-2 border-dashed border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-300 font-bold text-[11px] uppercase tracking-widest transition-all">
            <i class="fas fa-undo-alt"></i> Kembalikan Nilai Asli
        </button>

        <div class="pt-3 flex gap-3">
            <x-button type="button" variant="secondary" onclick="closeModal('custom-harga')" class="flex-1">Batalkan</x-button>
            <x-button type="button" onclick="simpanCustomHarga()" class="flex-1">Simpan</x-button>
        </div>
    </x-modal>

</div>

<script>
    function formatRupiah(angka) {
        let string = angka.toString().replace(/[^0-9]/g, "");
        return string.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function cleanRupiah(string) {
        if (!string) return 0;
        return parseFloat(string.toString().replace(/\./g, '')) || 0;
    }

    // Rangkuman Cetak/Desain/Finishing/Potongan jadi satu baris kecil di
    // bawah Deskripsi (bukan kolom sendiri-sendiri) - dipakai ulang setelah
    // Custom Harga disimpan, sama persis pola PHP di server saat render awal
    // (lihat $bagianBiaya di tabel Rincian Barang) supaya konsisten.
    function buildBiayaTambahanText(cetak, desain, finishing, potongan) {
        const bagian = [];
        if (cetak > 0) bagian.push('Cetak ' + formatRupiah(cetak));
        if (desain > 0) bagian.push('Desain ' + formatRupiah(desain));
        if (finishing > 0) bagian.push('Finishing ' + formatRupiah(finishing));
        let teks = bagian.join(' &bull; ');
        if (potongan > 0) teks += (teks ? ' &bull; ' : '') + '-Rp ' + formatRupiah(potongan) + ' Potongan';
        return teks;
    }

    function calculateInvoice() {
        let totalKotor = 0;

        // Subtotal = Cetak + Desain + Finishing - Potongan (SAMA PERSIS
        // dengan rumus server di KasirController::prosesBayar()) - TIDAK
        // lagi ada komponen Harga x Qty terpisah. reconcileSemuaBiayaCetak()
        // di DOMContentLoaded sudah memastikan .inp-harga selalu 0 untuk
        // SEMUA baris sebelum fungsi ini pernah dipanggil, jadi rumus di
        // sini dan yang tersimpan di server tidak akan pernah berbeda lagi.
        document.querySelectorAll('.item-row').forEach(row => {
            const subtotal = cleanRupiah(row.querySelector('.inp-cetak').value)
                + cleanRupiah(row.querySelector('.inp-desain').value)
                + cleanRupiah(row.querySelector('.inp-finishing').value)
                - cleanRupiah(row.querySelector('.inp-potongan').value);
            row.querySelector('.disp-subtotal').innerText = 'Rp ' + formatRupiah(Math.max(0, subtotal));
            totalKotor += Math.max(0, subtotal);
        });

        const diskon = cleanRupiah(document.getElementById('input-diskon').value);
        const totalTerbayarSebelumnya = parseFloat(document.getElementById('total_terbayar_hidden').value) || 0;

        let grandTotalNota = totalKotor - diskon;
        let sisaTagihan = grandTotalNota - totalTerbayarSebelumnya;

        document.getElementById('total-kotor').innerText = 'Rp ' + formatRupiah(totalKotor);
        document.getElementById('display-grand-total').innerText = 'Rp ' + formatRupiah(sisaTagihan);
        document.getElementById('grand_total_hidden').value = sisaTagihan;

        // Kalau kasir sedang di mode "Lunas Penuh", Uang Diterima otomatis
        // mengikuti sisa tagihan terbaru (mis. setelah Custom Harga mengubah
        // total) - supaya tidak ketinggalan angka lama. Mode "DP/Sebagian"
        // TIDAK disentuh di sini, murni ketikan manual kasir.
        const tipeBayar = document.querySelector('input[name="tipe_bayar"]:checked')?.value;
        if (tipeBayar === 'lunas') {
            document.getElementById('input-bayar-mask').value = formatRupiah(Math.max(0, sisaTagihan));
        }

        const inputMask = document.getElementById('input-bayar-mask');
        let bayar = cleanRupiah(inputMask.value);
        document.getElementById('input-bayar-asli').value = bayar;

        let selisih = bayar - sisaTagihan;
        const displaySelisih = document.getElementById('display-selisih');
        const statusLabel = document.getElementById('status-label');
        const labelSelisih = document.getElementById('label-selisih');

        if (bayar <= 0) {
            statusLabel.innerText = "BELUM BAYAR";
            statusLabel.className = "bg-red-500 text-white px-3 py-1 rounded-lg text-[10px] font-black uppercase";
            labelSelisih.innerText = "SISA PIUTANG";
            displaySelisih.innerText = 'Rp ' + formatRupiah(Math.abs(sisaTagihan));
        } else if (bayar < sisaTagihan) {
            statusLabel.innerText = "PIUTANG / DP";
            statusLabel.className = "bg-amber-400 text-slate-900 px-3 py-1 rounded-lg text-[10px] font-black uppercase";
            labelSelisih.innerText = "KEKURANGAN";
            displaySelisih.innerText = 'Rp ' + formatRupiah(Math.abs(selisih));
        } else {
            statusLabel.innerText = "LUNAS";
            statusLabel.className = "bg-emerald-400 text-slate-900 px-3 py-1 rounded-lg text-[10px] font-black uppercase";
            labelSelisih.innerText = "KEMBALIAN";
            displaySelisih.innerText = 'Rp ' + formatRupiah(selisih);
        }
    }

    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('format-ribuan')) {
            e.target.value = formatRupiah(e.target.value);
            calculateInvoice();
        }
        if (e.target.classList.contains('format-ribuan-modal')) {
            e.target.value = formatRupiah(e.target.value);
            hitungPreviewCustomHarga();
        }
    });

    function setUangPas() {
        const sisa = document.getElementById('grand_total_hidden').value;
        document.getElementById('input-bayar-mask').value = formatRupiah(Math.max(0, sisa));
        calculateInvoice();
    }

    // Toggle "Jenis Pembayaran" murni bantuan UX - lihat catatan di atas
    // form. Pindah ke "Lunas Penuh" langsung mengisi Uang Diterima =
    // sisa tagihan (dan menyembunyikan tombol "Bayar Sisa Tagihan" karena
    // jadi mubazir); pindah ke "DP/Sebagian" mengosongkan field itu supaya
    // kasir mengetik sendiri berapa yang diterima sekarang.
    function onTipeBayarChange() {
        document.querySelectorAll('.tipe-bayar-option').forEach(label => {
            const isChecked = label.querySelector('input').checked;
            label.classList.toggle('border-indigo-500', isChecked);
            label.classList.toggle('bg-indigo-50', isChecked);
            label.classList.toggle('text-indigo-700', isChecked);
            label.classList.toggle('border-slate-100', !isChecked);
            label.classList.toggle('text-slate-400', !isChecked);
        });

        const tipe = document.querySelector('input[name="tipe_bayar"]:checked').value;
        const btnBayarPas = document.getElementById('btn-bayar-pas');
        const labelUangDiterima = document.getElementById('label-uang-diterima');

        if (tipe === 'lunas') {
            btnBayarPas.classList.add('hidden');
            labelUangDiterima.innerText = 'Uang Diterima (Otomatis - Lunas)';
            setUangPas();
        } else {
            btnBayarPas.classList.remove('hidden');
            labelUangDiterima.innerText = 'Uang Diterima (DP / Sebagian)';
            document.getElementById('input-bayar-mask').value = '';
            calculateInvoice();
        }
    }

    const saldoDepositTersedia = {{ (float) $saldoDeposit }};

    function getMetodePembayaran() {
        return document.querySelector('input[name="metode_pembayaran"]:checked')?.value || 'Tunai';
    }

    // Sinkron gaya pill button "Metode Pembayaran" dengan pill "Jenis
    // Pembayaran" di atasnya - sama-sama radio yang disamarkan (sr-only),
    // tampilannya digambar ulang lewat class toggle di label pembungkusnya.
    function onMetodePembayaranChange() {
        document.querySelectorAll('.metode-bayar-option').forEach(label => {
            const input = label.querySelector('input');
            const isChecked = input.checked;
            label.classList.toggle('border-indigo-500', isChecked);
            label.classList.toggle('bg-indigo-50', isChecked);
            label.classList.toggle('text-indigo-700', isChecked);
            label.classList.toggle('border-slate-100', !isChecked && !input.disabled);
            label.classList.toggle('text-slate-400', !isChecked);
        });
        toggleBankField();
    }

    function toggleBankField() {
        const metode = getMetodePembayaran();
        document.getElementById('wrapper-bank').classList.toggle('hidden', metode !== 'Transfer');
        document.getElementById('wrapper-deposit-info').classList.toggle('hidden', metode !== 'Deposit');
    }

    // Kalau konsumen ini punya saldo deposit, otomatis pilih Metode
    // Pembayaran = Deposit & isi Uang Diterima = saldo (dibatasi maksimal
    // sisa tagihan) saat halaman pertama dibuka, supaya kasir tidak perlu
    // ingat menerapkannya manual. HANYA jalan sekali di awal load (bukan
    // tiap kali angka berubah) supaya tidak menimpa input yang sudah
    // diketik sendiri - kasir tetap bebas ganti ke Tunai/Transfer kapan
    // saja (banner di atas cuma informasi, bukan kuncian).
    function autoTerapkanDeposit() {
        if (saldoDepositTersedia <= 0) return;
        const sisaTagihan = parseFloat(document.getElementById('grand_total_hidden').value) || 0;
        if (sisaTagihan <= 0) return;

        document.getElementById('metode-tunai').checked = false;
        const radioDeposit = document.querySelector('input[name="metode_pembayaran"][value="Deposit"]');
        radioDeposit.checked = true;
        onMetodePembayaranChange();

        const jumlahOtomatis = Math.min(saldoDepositTersedia, sisaTagihan);
        const tipeLunas = jumlahOtomatis >= sisaTagihan;
        document.querySelector(`input[name="tipe_bayar"][value="${tipeLunas ? 'lunas' : 'dp'}"]`).checked = true;
        onTipeBayarChange();

        document.getElementById('input-bayar-mask').value = formatRupiah(jumlahOtomatis);
        calculateInvoice();
    }

    // ---- CUSTOM HARGA MODAL ----
    // Biaya Cetak sekarang komponen UTAMA subtotal (Subtotal = Cetak +
    // Desain + Finishing - Potongan), bukan lagi "Harga per unit x Qty"
    // ditambah Biaya Cetak sebagai ekstra - lihat perubahan formula yang
    // sama di KasirController::prosesBayar() & PenjualanController::store().
    let currentEditingRow = null;
    let chNilaiAsli = null; // snapshot saat modal dibuka, untuk tombol "Kembalikan Nilai Asli"

    function openCustomHarga(idDetail) {
        const row = document.querySelector(`.item-row[data-id="${idDetail}"]`);
        currentEditingRow = row;

        const qty = parseFloat(row.querySelector('.inp-qty').value) || 0;
        const hargaLama = cleanRupiah(row.querySelector('.inp-harga').value);
        let cetak = cleanRupiah(row.querySelector('.inp-cetak').value);
        const desain = cleanRupiah(row.querySelector('.inp-desain').value);
        const finishing = cleanRupiah(row.querySelector('.inp-finishing').value);
        const potongan = cleanRupiah(row.querySelector('.inp-potongan').value);

        // Item LAMA (dibuat sebelum Biaya Cetak jadi komponen utama)
        // biaya_cetak-nya bisa 0 padahal subtotal sudah benar (dulu dihitung
        // dari Harga x Qty terpisah). Dipulihkan di sini dari komponen harga
        // per unit yang masih tersisa, supaya "default sama dengan subtotal"
        // konsisten untuk item lama MAUPUN baru, dan klik Simpan tanpa ubah
        // apa pun tidak diam-diam mengubah subtotal nota yang sudah ada.
        if (cetak <= 0 && hargaLama > 0 && qty > 0) {
            cetak = Math.round(hargaLama * qty);
        }

        chNilaiAsli = { cetak, desain, finishing, potongan };

        document.getElementById('ch-nama-item').innerText = row.querySelector('td:nth-child(2) .font-bold').innerText;
        document.getElementById('ch-cetak').value = formatRupiah(cetak);
        document.getElementById('ch-desain').value = formatRupiah(desain);
        document.getElementById('ch-finishing').value = formatRupiah(finishing);
        document.getElementById('ch-potongan').value = formatRupiah(potongan);

        // Desain/Finishing cuma tampil kalau item ini memang sudah punya
        // nilainya - "muncul ketika ada penambahan biaya".
        document.getElementById('ch-wrapper-desain').classList.toggle('hidden', desain <= 0);
        document.getElementById('ch-btn-tambah-desain').classList.toggle('hidden', desain > 0);
        document.getElementById('ch-wrapper-finishing').classList.toggle('hidden', finishing <= 0);
        document.getElementById('ch-btn-tambah-finishing').classList.toggle('hidden', finishing > 0);

        hitungPreviewCustomHarga();
        openModal('custom-harga');
    }

    function tampilkanChBiaya(jenis) {
        document.getElementById(`ch-wrapper-${jenis}`).classList.remove('hidden');
        document.getElementById(`ch-btn-tambah-${jenis}`).classList.add('hidden');
        document.getElementById(`ch-${jenis}`).focus();
    }

    // Mengembalikan keempat field (Cetak/Desain/Finishing/Potongan) ke
    // kondisi PERSIS saat modal ini dibuka (chNilaiAsli) - membatalkan
    // ketikan yang sudah sempat diubah tanpa perlu tutup+buka ulang modal.
    function kembalikanNilaiAsliCh() {
        if (!chNilaiAsli) return;
        document.getElementById('ch-cetak').value = formatRupiah(chNilaiAsli.cetak);
        document.getElementById('ch-desain').value = formatRupiah(chNilaiAsli.desain);
        document.getElementById('ch-finishing').value = formatRupiah(chNilaiAsli.finishing);
        document.getElementById('ch-potongan').value = formatRupiah(chNilaiAsli.potongan);

        document.getElementById('ch-wrapper-desain').classList.toggle('hidden', chNilaiAsli.desain <= 0);
        document.getElementById('ch-btn-tambah-desain').classList.toggle('hidden', chNilaiAsli.desain > 0);
        document.getElementById('ch-wrapper-finishing').classList.toggle('hidden', chNilaiAsli.finishing <= 0);
        document.getElementById('ch-btn-tambah-finishing').classList.toggle('hidden', chNilaiAsli.finishing > 0);

        hitungPreviewCustomHarga();

        Toast.fire({ icon: 'info', title: 'Dikembalikan ke nilai sebelum diedit.' });
    }

    function hitungPreviewCustomHarga() {
        if (!currentEditingRow) return;
        const cetak = cleanRupiah(document.getElementById('ch-cetak').value);
        const desain = cleanRupiah(document.getElementById('ch-desain').value);
        const finishing = cleanRupiah(document.getElementById('ch-finishing').value);
        const potongan = cleanRupiah(document.getElementById('ch-potongan').value);

        const subtotal = Math.max(0, cetak + desain + finishing - potongan);

        // inp-qty menyimpan qty_hitung (m²/pcs) sebagai angka desimal biasa
        // (mis. "2.4500") - dibaca dengan parseFloat(), BUKAN cleanRupiah()
        // yang akan salah membuang titik desimalnya (dianggap pemisah
        // ribuan). Cuma dipakai untuk acuan "≈ Rp X /unit" - informasi saja,
        // tidak lagi bagian dari rumus subtotal.
        const qty = parseFloat(currentEditingRow.querySelector('.inp-qty').value) || 0;
        const satuan = currentEditingRow.querySelector('.inp-qty').dataset.satuan || 'unit';
        const acuan = document.getElementById('ch-harga-acuan');
        acuan.innerText = qty > 0 ? `≈ Rp ${formatRupiah(Math.round(cetak / qty))} / ${satuan}` : '';

        document.getElementById('ch-total-potongan').innerText = 'Rp ' + formatRupiah(potongan);
        document.getElementById('ch-preview-subtotal').innerText = 'Rp ' + formatRupiah(subtotal);
    }

    function simpanCustomHarga() {
        if (!currentEditingRow) return;
        const row = currentEditingRow;

        const cetak = cleanRupiah(document.getElementById('ch-cetak').value);
        const desain = cleanRupiah(document.getElementById('ch-desain').value);
        const finishing = cleanRupiah(document.getElementById('ch-finishing').value);
        const potongan = cleanRupiah(document.getElementById('ch-potongan').value);

        // "Harga" per-unit terpisah tidak lagi dipakai (Biaya Cetak sudah
        // jadi komponen utama) - diset 0 di baris tabel supaya tidak
        // menampilkan angka lama yang sudah tidak relevan/menyesatkan.
        row.querySelector('.inp-harga').value = 0;
        row.querySelector('.inp-cetak').value = cetak;
        row.querySelector('.inp-desain').value = desain;
        row.querySelector('.inp-finishing').value = finishing;
        row.querySelector('.inp-potongan').value = potongan;

        row.querySelector('.disp-harga').innerText = 'Rp 0';
        // Cetak/Desain/Finishing/Potongan tidak lagi kolom terpisah - dirangkum
        // jadi satu baris kecil di bawah Deskripsi (lihat buildBiayaTambahanText),
        // sama seperti dulu dirender server-side saat halaman pertama dimuat.
        row.querySelector('.disp-biaya-tambahan').innerHTML = buildBiayaTambahanText(cetak, desain, finishing, potongan);

        closeModal('custom-harga');
        currentEditingRow = null;
        calculateInvoice();
    }

    function simpanSaja() {
        document.getElementById('input-bayar-asli').value = 0;
        document.getElementById('input-bayar-mask').value = 0;
        document.getElementById('form-pembayaran').submit();
    }

    // Validasi ringan di sisi klien supaya kasir langsung tahu kalau input
    // melebihi saldo deposit - validasi SEBENARNYA tetap di server
    // (KasirController::prosesBayar), ini cuma UX, bukan satu-satunya
    // penjaga.
    document.getElementById('form-pembayaran').addEventListener('submit', function (e) {
        const metode = getMetodePembayaran();
        const bayar = cleanRupiah(document.getElementById('input-bayar-mask').value);
        if (metode === 'Deposit' && bayar > saldoDepositTersedia) {
            e.preventDefault();
            alert('Jumlah bayar melebihi saldo deposit yang tersedia (Rp ' + formatRupiah(saldoDepositTersedia) + ').');
        }
    });

    // BUG PENTING (ditemukan dari laporan "harga di rincian barang jadi 0
    // setelah disimpan", termasuk transaksi DP/Deposit): server sekarang
    // menghitung subtotal murni dari Cetak+Desain+Finishing-Potongan (lihat
    // KasirController::prosesBayar()), TAPI hidden input .inp-cetak untuk
    // item LAMA (dibuat sebelum Biaya Cetak jadi komponen utama, biaya_cetak
    // masih 0 di database) baru direkonsiliasi ke nilai yang benar KETIKA
    // modal Custom Harga item itu dibuka (lihat openCustomHarga()). Form ini
    // SELALU mengirim items[][...] untuk SEMUA baris di setiap submit (Bayar
    // maupun Simpan) - kalau kasir tidak sempat membuka Custom Harga untuk
    // setiap item (kasus paling umum: langsung isi nominal bayar & klik
    // Bayar), hidden input yang masih 0 itu ikut terkirim apa adanya, server
    // menyimpannya sebagai subtotal 0 - persis yang dilaporkan.
    //
    // Diperbaiki di akarnya: rekonsiliasi SEMUA baris (bukan cuma yang
    // dibuka lewat modal) sekali di awal, SEBELUM form sempat disubmit sama
    // sekali - supaya hidden input yang terkirim selalu sudah benar apa pun
    // yang kasir lakukan setelahnya.
    function reconcileSemuaBiayaCetak() {
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.inp-qty').value) || 0;
            const hargaLama = cleanRupiah(row.querySelector('.inp-harga').value);
            const cetakLama = cleanRupiah(row.querySelector('.inp-cetak').value);

            if (cetakLama <= 0 && hargaLama > 0 && qty > 0) {
                row.querySelector('.inp-cetak').value = Math.round(hargaLama * qty);
                row.querySelector('.inp-harga').value = 0;
                row.querySelector('.disp-harga').innerText = 'Rp 0';
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        reconcileSemuaBiayaCetak();
        calculateInvoice();
        toggleBankField();
        autoTerapkanDeposit();
    });
</script>
@endsection

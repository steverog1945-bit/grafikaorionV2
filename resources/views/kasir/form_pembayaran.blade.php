@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-5xl mx-auto pb-20">

    <x-page-header title="Input Pembayaran" subtitle="Nota #{{ $transaksi->nomor_nota }} &middot; {{ $transaksi->nama_konsumen }}">
        <x-slot:actions>
            <x-button href="{{ route('kasir.index') }}" variant="outline">
                <i class="fas fa-arrow-left"></i> Kembali
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <form action="{{ url('/kasir/proses-bayar') }}" method="POST" id="form-pembayaran">
        @csrf
        <input type="hidden" name="id_transaksi" value="{{ $transaksi->id_transaksi }}">

        {{-- BAGIAN 1: RINCIAN BARANG --}}
        <x-card class="mb-6">
            <div class="flex items-center justify-between mb-6 border-b border-slate-50 pb-4">
                <h4 class="font-black text-xs uppercase text-slate-400 tracking-widest">Rincian Barang &amp; Penyesuaian Harga</h4>
                <span class="text-[10px] text-slate-400 italic">Tanggal Order: {{ \Carbon\Carbon::parse($transaksi->tgl_order)->format('d M Y H:i') }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="text-[10px] uppercase text-slate-400 border-b border-slate-100">
                            <th class="text-left py-2 pr-2 w-8">#</th>
                            <th class="text-left py-2 px-2">Deskripsi</th>
                            <th class="text-right py-2 px-2">Harga</th>
                            <th class="text-center py-2 px-2">Qty</th>
                            <th class="text-right py-2 px-2">Cetak</th>
                            <th class="text-right py-2 px-2">Desain</th>
                            <th class="text-right py-2 px-2">Finishing</th>
                            <th class="text-right py-2 px-2">Potongan</th>
                            <th class="text-right py-2 pl-2">Subtotal</th>
                            <th class="w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($items as $i => $item)
                        @php $hargaSatuan = ($item->qty > 0) ? ($item->subtotal + $item->potongan - $item->biaya_cetak - $item->biaya_desain - $item->biaya_finishing) / $item->qty : 0; @endphp
                        <tr class="item-row" data-id="{{ $item->id_detail }}">
                            <td class="py-3 pr-2 text-slate-400">{{ $i + 1 }}</td>
                            <td class="py-3 px-2">
                                <div class="font-bold text-slate-700 uppercase">{{ $item->nama_produk ?? $item->nama_jasa }}</div>
                                <div class="text-[10px] text-slate-400 italic">{{ $item->nama_file }}</div>
                            </td>
                            <td class="py-3 px-2 text-right disp-harga font-bold text-slate-700">Rp {{ number_format($hargaSatuan, 0, ',', '.') }}</td>
                            <td class="py-3 px-2 text-center text-slate-500">{{ $item->qty }} {{ $item->metode_hitung == 'ukuran' ? 'm²' : ($item->id_jasa ? 'Jasa' : 'Pcs') }}</td>
                            <td class="py-3 px-2 text-right disp-cetak text-slate-500">Rp {{ number_format($item->biaya_cetak, 0, ',', '.') }}</td>
                            <td class="py-3 px-2 text-right disp-desain text-slate-500">Rp {{ number_format($item->biaya_desain, 0, ',', '.') }}</td>
                            <td class="py-3 px-2 text-right disp-finishing text-slate-500">Rp {{ number_format($item->biaya_finishing, 0, ',', '.') }}</td>
                            <td class="py-3 px-2 text-right disp-potongan text-rose-500">{{ $item->potongan > 0 ? '- Rp ' . number_format($item->potongan, 0, ',', '.') : '-' }}</td>
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
                            <input type="hidden" class="inp-qty" value="{{ $item->qty }}" data-satuan="{{ $item->metode_hitung == 'ukuran' ? 'm²' : ($item->id_jasa ? 'jasa' : 'pcs') }}">
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- TOTAL & DISKON --}}
            <div class="mt-8 bg-slate-900 rounded-3xl p-8 text-white relative overflow-hidden">
                @if($totalTerbayar > 0)
                <div class="absolute top-0 right-0 bg-amber-500 text-slate-900 px-6 py-2 rounded-bl-2xl font-black text-[10px] uppercase tracking-widest">
                    Nota Piutang (Terbayar: Rp {{ number_format($totalTerbayar, 0, ',', '.') }})
                </div>
                @endif

                <div class="flex justify-between items-center mb-4 opacity-70">
                    <span class="text-sm font-bold uppercase tracking-widest">Total Nota</span>
                    <span id="total-kotor" class="text-lg text-white">Rp 0</span>
                </div>

                <div class="flex justify-between items-center mb-6">
                    <span class="text-sm font-bold uppercase tracking-widest text-indigo-300">Diskon Potongan (Nota)</span>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/50 font-bold text-xs">Rp</span>
                        <input type="text" id="input-diskon" name="diskon" value="0"
                            class="format-ribuan w-40 bg-white/10 border-none rounded-xl text-right text-white font-bold focus:ring-2 focus:ring-indigo-400 pl-10">
                    </div>
                </div>

                @if($totalTerbayar > 0)
                <div class="flex justify-between items-center mb-4 text-emerald-400 border-t border-white/10 pt-4">
                    <span class="text-sm font-bold uppercase tracking-widest">Sudah Dibayar Sebelumnya</span>
                    <span class="text-lg">- Rp {{ number_format($totalTerbayar, 0, ',', '.') }}</span>
                </div>
                <input type="hidden" id="total_terbayar_hidden" value="{{ $totalTerbayar }}">
                @else
                <input type="hidden" id="total_terbayar_hidden" value="0">
                @endif

                <div class="border-t border-white/20 pt-6 flex justify-between items-center">
                    <span class="text-xl font-black italic tracking-tighter uppercase text-indigo-400">
                        {{ $totalTerbayar > 0 ? 'Sisa Tagihan' : 'Grand Total' }}
                    </span>
                    <input type="hidden" id="grand_total_hidden" name="total_akhir" value="0">
                    <span id="display-grand-total" class="text-4xl font-black">Rp 0</span>
                </div>
            </div>
        </x-card>

        {{-- BAGIAN 2: VALIDASI PEMBAYARAN --}}
        <x-card>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-6">
                    {{-- Sebelumnya status DP/Lunas cuma DISIMPULKAN diam-diam dari
                         angka yang diketik (berapa pun kurang dari sisa tagihan
                         otomatis jadi DP) - kasir tidak pernah eksplisit memilih.
                         Toggle ini murni bantuan UX supaya niatnya jelas dari awal:
                         pilih "Lunas Penuh" langsung isi Uang Diterima = Sisa
                         Tagihan, atau "DP / Sebagian" untuk mengetik nominal
                         sendiri. Status akhir yang TERSIMPAN tetap dihitung server
                         dari nominal vs sisa tagihan seperti sebelumnya, bukan dari
                         pilihan toggle ini - jadi tidak ada aturan baru, cuma jalan
                         yang lebih jelas menuju hasil yang sama. --}}
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase mb-3">Jenis Pembayaran</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="tipe-bayar-option relative flex items-center justify-center gap-2 py-3 rounded-2xl border-2 border-indigo-500 bg-indigo-50 text-indigo-700 font-black text-xs uppercase cursor-pointer transition-all">
                                <input type="radio" name="tipe_bayar" value="lunas" class="sr-only" checked onchange="onTipeBayarChange()">
                                <i class="fas fa-check-circle"></i> Lunas Penuh
                            </label>
                            <label class="tipe-bayar-option relative flex items-center justify-center gap-2 py-3 rounded-2xl border-2 border-slate-100 text-slate-400 font-black text-xs uppercase cursor-pointer transition-all">
                                <input type="radio" name="tipe_bayar" value="dp" class="sr-only" onchange="onTipeBayarChange()">
                                <i class="fas fa-hand-holding-usd"></i> DP / Sebagian
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase mb-3">Metode Pembayaran</label>
                        <select name="metode_pembayaran" id="metode-pembayaran" onchange="toggleBankField()" class="w-full h-14 rounded-2xl border-slate-200 text-base font-bold focus:ring-4 focus:ring-indigo-100 transition-all">
                            <option value="Tunai">💵 TUNAI</option>
                            <option value="Transfer">🏦 TRANSFER</option>
                            <option value="Deposit" {{ $saldoDeposit <= 0 ? 'disabled' : '' }}>👛 DEPOSIT (Saldo: Rp {{ number_format($saldoDeposit, 0, ',', '.') }})</option>
                        </select>
                    </div>
                    <div id="wrapper-bank" class="hidden">
                        <label class="block text-xs font-black text-slate-500 uppercase mb-3">Rekening Tujuan</label>
                        <select name="id_bank" id="input-bank" class="w-full h-14 rounded-2xl border-slate-200 text-sm font-bold focus:ring-4 focus:ring-indigo-100 transition-all">
                            <option value="">-- Pilih Rekening --</option>
                            @foreach($banks as $b)
                                <option value="{{ $b->id_bank }}">{{ $b->nama_bank }} - {{ $b->nomor_rekening }} ({{ $b->atas_nama }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="wrapper-deposit-info" class="hidden bg-indigo-50 border border-indigo-100 rounded-2xl p-4">
                        <p class="text-[11px] font-bold text-indigo-600 uppercase tracking-wide">
                            Saldo deposit tersedia: Rp {{ number_format($saldoDeposit, 0, ',', '.') }}
                        </p>
                        <p class="text-[10px] text-indigo-400 mt-1">Jumlah bayar tidak boleh melebihi saldo ini.</p>
                    </div>
                    <div>
                        <label id="label-uang-diterima" class="block text-xs font-black text-slate-500 uppercase mb-3">Uang Diterima</label>
                        <div class="relative">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 font-black text-slate-400 text-xl">Rp</span>
                            <input type="text" id="input-bayar-mask"
                                   class="format-ribuan w-full h-16 pl-16 rounded-2xl border-2 border-slate-100 bg-white text-2xl font-black text-indigo-600 focus:border-indigo-500 transition-all"
                                   placeholder="0">
                            <input type="hidden" name="jumlah_bayar" id="input-bayar-asli">
                        </div>
                        <div class="mt-3 flex justify-end">
                            <button type="button" id="btn-bayar-pas" onclick="setUangPas()" class="flex items-center gap-2 px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-xl text-[12px] font-black uppercase transition-all border border-indigo-100 shadow-sm">
                                <i class="fas fa-bolt"></i> Bayar Sisa Tagihan
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-indigo-600 rounded-[2rem] p-8 text-white shadow-xl flex flex-col justify-between relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10 text-6xl"><i class="fas fa-cash-register"></i></div>
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-[10px] font-black uppercase tracking-widest opacity-70">Status</span>
                            <span id="status-label" class="bg-white/20 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-tighter">MENUNGGU...</span>
                        </div>
                        <div class="h-px bg-white/10 w-full mb-6"></div>
                        <span id="label-selisih" class="text-[10px] font-black italic uppercase opacity-70 block mb-1">Kalkulasi</span>
                        <div id="display-selisih" class="text-4xl font-black">Rp 0</div>
                    </div>
                </div>
            </div>
        </x-card>

        <div class="grid grid-cols-2 gap-4 mt-6">
            <x-button type="button" variant="secondary" onclick="simpanSaja()" class="py-4">
                <i class="fas fa-inbox"></i> Simpan Ke Monitoring
            </x-button>

            <x-button type="submit" class="py-4">
                <i class="fas fa-cash-register"></i> Bayar Sekarang
            </x-button>
        </div>
    </form>

    {{-- MODAL CUSTOM HARGA - satu modal dipakai bergantian untuk semua item
         (diisi ulang lewat JS tiap kali tombol edit satu baris diklik),
         bukan satu modal per item - lebih ringan & konsisten dengan pola
         openEditX(data) yang sudah dipakai di halaman master lain. --}}
    <x-modal id="custom-harga" title="Custom Harga" maxWidth="sm">
        <p id="ch-nama-item" class="text-xs text-slate-400 uppercase font-bold mb-5 -mt-2"></p>

        <div class="space-y-4">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Harga <span id="ch-satuan" class="normal-case"></span></label>
                <input type="text" id="ch-harga" class="format-ribuan-modal w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Potongan Tunai (Diskon Item Ini)</label>
                <input type="text" id="ch-potongan" class="format-ribuan-modal w-full bg-rose-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-rose-600 focus:ring-2 focus:ring-rose-500/20 shadow-inner">
            </div>
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Biaya Cetak</label>
                    <input type="text" id="ch-cetak" class="format-ribuan-modal w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Biaya Desain</label>
                    <input type="text" id="ch-desain" class="format-ribuan-modal w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Biaya Finishing</label>
                    <input type="text" id="ch-finishing" class="format-ribuan-modal w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                </div>
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

        <div class="pt-6 flex gap-3">
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

    function calculateInvoice() {
        let totalKotor = 0;

        document.querySelectorAll('.item-row').forEach(row => {
            const subtotal = cleanRupiah(row.querySelector('.inp-harga').value) * cleanRupiah(row.querySelector('.inp-qty').value)
                + cleanRupiah(row.querySelector('.inp-cetak').value)
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

    function toggleBankField() {
        const metode = document.getElementById('metode-pembayaran').value;
        document.getElementById('wrapper-bank').classList.toggle('hidden', metode !== 'Transfer');
        document.getElementById('wrapper-deposit-info').classList.toggle('hidden', metode !== 'Deposit');
    }

    // ---- CUSTOM HARGA MODAL ----
    let currentEditingRow = null;

    function openCustomHarga(idDetail) {
        const row = document.querySelector(`.item-row[data-id="${idDetail}"]`);
        currentEditingRow = row;

        document.getElementById('ch-nama-item').innerText = row.querySelector('td:nth-child(2) .font-bold').innerText;
        document.getElementById('ch-satuan').innerText = '/ ' + (row.querySelector('.inp-qty').dataset.satuan || 'unit');
        document.getElementById('ch-harga').value = formatRupiah(row.querySelector('.inp-harga').value);
        document.getElementById('ch-cetak').value = formatRupiah(row.querySelector('.inp-cetak').value);
        document.getElementById('ch-desain').value = formatRupiah(row.querySelector('.inp-desain').value);
        document.getElementById('ch-finishing').value = formatRupiah(row.querySelector('.inp-finishing').value);
        document.getElementById('ch-potongan').value = formatRupiah(row.querySelector('.inp-potongan').value);

        hitungPreviewCustomHarga();
        openModal('custom-harga');
    }

    function hitungPreviewCustomHarga() {
        if (!currentEditingRow) return;
        const qty = cleanRupiah(currentEditingRow.querySelector('.inp-qty').value);
        const harga = cleanRupiah(document.getElementById('ch-harga').value);
        const cetak = cleanRupiah(document.getElementById('ch-cetak').value);
        const desain = cleanRupiah(document.getElementById('ch-desain').value);
        const finishing = cleanRupiah(document.getElementById('ch-finishing').value);
        const potongan = cleanRupiah(document.getElementById('ch-potongan').value);

        const subtotal = Math.max(0, (harga * qty) + cetak + desain + finishing - potongan);

        document.getElementById('ch-total-potongan').innerText = 'Rp ' + formatRupiah(potongan);
        document.getElementById('ch-preview-subtotal').innerText = 'Rp ' + formatRupiah(subtotal);
    }

    function simpanCustomHarga() {
        if (!currentEditingRow) return;
        const row = currentEditingRow;
        const qty = cleanRupiah(row.querySelector('.inp-qty').value);

        const harga = cleanRupiah(document.getElementById('ch-harga').value);
        const cetak = cleanRupiah(document.getElementById('ch-cetak').value);
        const desain = cleanRupiah(document.getElementById('ch-desain').value);
        const finishing = cleanRupiah(document.getElementById('ch-finishing').value);
        const potongan = cleanRupiah(document.getElementById('ch-potongan').value);

        row.querySelector('.inp-harga').value = harga;
        row.querySelector('.inp-cetak').value = cetak;
        row.querySelector('.inp-desain').value = desain;
        row.querySelector('.inp-finishing').value = finishing;
        row.querySelector('.inp-potongan').value = potongan;

        row.querySelector('.disp-harga').innerText = 'Rp ' + formatRupiah(harga);
        row.querySelector('.disp-cetak').innerText = 'Rp ' + formatRupiah(cetak);
        row.querySelector('.disp-desain').innerText = 'Rp ' + formatRupiah(desain);
        row.querySelector('.disp-finishing').innerText = 'Rp ' + formatRupiah(finishing);
        row.querySelector('.disp-potongan').innerText = potongan > 0 ? ('- Rp ' + formatRupiah(potongan)) : '-';

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
        const metode = document.getElementById('metode-pembayaran').value;
        const bayar = cleanRupiah(document.getElementById('input-bayar-mask').value);
        if (metode === 'Deposit' && bayar > saldoDepositTersedia) {
            e.preventDefault();
            alert('Jumlah bayar melebihi saldo deposit yang tersedia (Rp ' + formatRupiah(saldoDepositTersedia) + ').');
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        calculateInvoice();
        toggleBankField();
    });
</script>
@endsection

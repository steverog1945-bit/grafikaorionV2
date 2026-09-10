@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto pb-20">
    <div class="mb-8 flex justify-between items-end">
        <div>
            <a href="{{ route('kasir.index') }}" class="text-indigo-600 font-bold text-sm flex items-center gap-2 mb-2">← Kembali</a>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight uppercase">Input Pembayaran</h2>
            <p class="text-slate-500 text-sm italic">Nota: #{{ $transaksi->nomor_nota }} | Konsumen: {{ $transaksi->nama_konsumen }}</p>
        </div>
        <div class="text-right">
            <span class="text-[10px] font-black text-slate-400 uppercase block">Tanggal Order</span>
            <span class="text-sm font-bold text-slate-700">{{ date('d M Y H:i', strtotime($transaksi->tgl_order)) }}</span>
        </div>
    </div>

    <form action="{{ url('/kasir/proses-bayar') }}" method="POST" id="form-pembayaran">
        @csrf
        <input type="hidden" name="id_transaksi" value="{{ $transaksi->id_transaksi }}">

        {{-- BAGIAN 1: RINCIAN BARANG --}}
        <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden p-8 mb-6">
            <h4 class="font-black text-xs uppercase text-slate-400 mb-6 tracking-widest italic border-b pb-4">Rincian Barang & Penyesuaian Harga</h4>
            
            @foreach($items as $item)
            <div class="grid grid-cols-12 gap-4 items-center mb-6 border-b border-slate-50 pb-4">
                <div class="col-span-5">
                    <div class="detail-item-text text-slate-700 uppercase">
                        {{ $item->nama_produk ?? ('🔧 ' . $item->nama_jasa) }}
                    </div>
                    <div class="detail-sub-text italic">
                        {{ $item->qty }} {{ $item->metode_hitung == 'ukuran' ? 'm²' : ($item->id_jasa ? 'Jasa' : 'Pcs') }}
                    </div>
                </div>
                <div class="col-span-3">
                    <label class="text-[9px] font-black uppercase text-slate-400 block mb-1">Harga Unit (Edit)</label>
                    @php $hargaSatuan = ($item->qty > 0) ? ($item->subtotal / $item->qty) : 0; @endphp
                    <input type="text" 
                        name="items[{{ $item->id_detail }}][harga]" 
                        value="{{ number_format($hargaSatuan, 0, ',', '.') }}" 
                        class="input-harga format-ribuan w-full bg-slate-50 border-none rounded-xl text-sm font-mono font-bold focus:ring-2 focus:ring-indigo-500"
                        data-qty="{{ $item->qty }}">
                    <input type="hidden" name="items[{{ $item->id_detail }}][qty_hidden]" value="{{ $item->qty }}">
                </div>
                <div class="col-span-4 text-right">
                    <label class="text-[9px] font-black uppercase text-slate-400 block mb-1">Subtotal</label>
                    <span class="subtotal font-mono font-black text-slate-800 text-sm">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
            </div>
            @endforeach

            {{-- TOTAL & DISKON (Hanya muncul jika belum pernah bayar DP/Baru) --}}
            <div class="mt-10 bg-slate-900 rounded-3xl p-8 text-white relative overflow-hidden">
                @if($totalTerbayar > 0)
                <div class="absolute top-0 right-0 bg-amber-500 text-slate-900 px-6 py-2 rounded-bl-2xl font-black text-[10px] uppercase tracking-widest">
                    Nota Piutang (Terbayar: Rp {{ number_format($totalTerbayar, 0, ',', '.') }})
                </div>
                @endif

                <div class="flex justify-between items-center mb-4 opacity-70">
                    <span class="text-sm font-bold uppercase tracking-widest">Total Nota</span>
                    <span id="total-kotor" class="font-mono text-lg text-white">Rp 0</span>
                </div>

                <div class="flex justify-between items-center mb-6">
                    <span class="text-sm font-bold uppercase tracking-widest text-indigo-300">Diskon Potongan</span>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/50 font-bold text-xs">Rp</span>
                        <input type="text" id="input-diskon" name="diskon" value="0" 
                            class="format-ribuan w-40 bg-white/10 border-none rounded-xl text-right text-white font-mono font-bold focus:ring-2 focus:ring-indigo-400 pl-10">
                    </div>
                </div>

                @if($totalTerbayar > 0)
                <div class="flex justify-between items-center mb-4 text-emerald-400 border-t border-white/10 pt-4">
                    <span class="text-sm font-bold uppercase tracking-widest">Sudah Dibayar Sebelumnya</span>
                    <span class="font-mono text-lg">- Rp {{ number_format($totalTerbayar, 0, ',', '.') }}</span>
                </div>
                {{-- Hidden input untuk membantu JS menghitung sisa --}}
                <input type="hidden" id="total_terbayar_hidden" value="{{ $totalTerbayar }}">
                @else
                <input type="hidden" id="total_terbayar_hidden" value="0">
                @endif

                <div class="border-t border-white/20 pt-6 flex justify-between items-center">
                    <div>
                        <span class="text-xl font-black italic tracking-tighter uppercase text-indigo-400">
                            {{ $totalTerbayar > 0 ? 'Sisa Tagihan' : 'Grand Total' }}
                        </span>
                    </div>
                    <input type="hidden" id="grand_total_hidden" name="total_akhir" value="0">
                    <span id="display-grand-total" class="text-4xl font-mono font-black">Rp 0</span>
                </div>
            </div>
        </div>

        {{-- BAGIAN 2: VALIDASI PEMBAYARAN --}}
        <div class="bg-white rounded-[2rem] border border-slate-200 p-8 shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-6">
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
                        <label class="block text-xs font-black text-slate-500 uppercase mb-3">Uang Diterima</label>
                        <div class="relative">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 font-black text-slate-400 text-xl">Rp</span>
                            <input type="text" id="input-bayar-mask" 
                                   class="format-ribuan w-full h-16 pl-16 rounded-2xl border-2 border-slate-100 bg-white text-2xl font-mono font-black text-indigo-600 focus:border-indigo-500 transition-all" 
                                   placeholder="0">
                            <input type="hidden" name="jumlah_bayar" id="input-bayar-asli">
                        </div>
                        <div class="mt-3 flex justify-end">
                            <button type="button" onclick="setUangPas()" class="flex items-center gap-2 px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-xl text-[12px] font-black uppercase transition-all border border-indigo-100 shadow-sm">
                                <span>⚡</span> Bayar Sisa Tagihan
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-indigo-600 rounded-[2rem] p-8 text-white shadow-xl flex flex-col justify-between relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10 text-6xl">💰</div>
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-[10px] font-black uppercase tracking-widest opacity-70">Status</span>
                            <span id="status-label" class="bg-white/20 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-tighter">MENUNGGU...</span>
                        </div>
                        <div class="h-px bg-white/10 w-full mb-6"></div>
                        <span id="label-selisih" class="text-[10px] font-black italic uppercase opacity-70 block mb-1">Kalkulasi</span>
                        <div id="display-selisih" class="text-4xl font-mono font-black">Rp 0</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mt-8">
    <button type="button" onclick="simpanSaja()" class="bg-slate-100 hover:bg-slate-200 text-slate-600 py-4 rounded-2xl font-black text-xs uppercase tracking-widest transition-all">
        📥 Simpan Ke Monitoring
    </button>

    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-lg shadow-indigo-200">
        💰 Bayar Sekarang
    </button>
</div>
    </form>
</div>

<script>
    function simpanSaja() {
    // 1. Arahkan ke ID yang benar: 'input-bayar-asli'
    const inputAsli = document.getElementById('input-bayar-asli');
    const inputMask = document.getElementById('input-bayar-mask');
    
    if (inputAsli) {
        inputAsli.value = 0; // Set nominal jadi 0
    }
    
    if (inputMask) {
        inputMask.value = 0; // Set tampilan jadi 0
    }

    // 2. Kirim form secara manual
    document.getElementById('form-pembayaran').submit();
}
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
        
        // 1. Hitung Subtotal Item
        document.querySelectorAll('.input-harga').forEach(input => {
            let harga = cleanRupiah(input.value);
            let qty = parseFloat(input.dataset.qty) || 0;
            let subtotal = harga * qty;
            const subtotalElement = input.closest('.grid').querySelector('.subtotal');
            if(subtotalElement) subtotalElement.innerText = 'Rp ' + formatRupiah(subtotal);
            totalKotor += subtotal;
        });

        // 2. Diskon & Sisa Tagihan
        const diskon = cleanRupiah(document.getElementById('input-diskon').value);
        const totalTerbayarSebelumnya = parseFloat(document.getElementById('total_terbayar_hidden').value) || 0;
        
        let grandTotalNota = totalKotor - diskon;
        let sisaTagihan = grandTotalNota - totalTerbayarSebelumnya;

        // Update Tampilan
        document.getElementById('total-kotor').innerText = 'Rp ' + formatRupiah(totalKotor);
        document.getElementById('display-grand-total').innerText = 'Rp ' + formatRupiah(sisaTagihan);
        document.getElementById('grand_total_hidden').value = sisaTagihan;

        // 3. Hitung Selisih Bayar
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
    });

    function setUangPas() {
        const sisa = document.getElementById('grand_total_hidden').value;
        document.getElementById('input-bayar-mask').value = formatRupiah(sisa);
        calculateInvoice();
    }

    const saldoDepositTersedia = {{ (float) $saldoDeposit }};

    function toggleBankField() {
        const metode = document.getElementById('metode-pembayaran').value;
        document.getElementById('wrapper-bank').classList.toggle('hidden', metode !== 'Transfer');
        document.getElementById('wrapper-deposit-info').classList.toggle('hidden', metode !== 'Deposit');
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
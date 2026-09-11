<div class="max-w-4xl mx-auto p-6 bg-white rounded-3xl shadow-xl">
    <h2 class="text-2xl font-black text-slate-800 mb-6 uppercase tracking-tighter">Laporan Closing Kasir</h2>
    
    <div class="grid grid-cols-2 gap-6 mb-8">
        <div class="p-6 bg-emerald-50 rounded-2xl border-2 border-emerald-100">
            <h3 class="font-bold text-emerald-800 mb-4">ALUR KAS TUNAI (LACI)</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span>Saldo Awal (Modal)</span> <b>Rp {{ number_format($kas->saldo_awal) }}</b></div>
                <div class="flex justify-between text-emerald-600"><span>Pemasukan Tunai (+)</span> <b>Rp {{ number_format($pemasukan->tunai_masuk) }}</b></div>
                <div class="flex justify-between text-red-500"><span>Pengeluaran Tunai (-)</span> <b>Rp {{ number_format($pengeluaran_tunai) }}</b></div>
                <hr class="border-emerald-200">
                <div class="flex justify-between text-lg font-black text-emerald-900">
                    <span>SALDO AKHIR LACI</span>
                    <span>Rp {{ number_format($saldo_akhir_sistem) }}</span>
                </div>
            </div>
        </div>

        <div class="p-6 bg-blue-50 rounded-2xl border-2 border-blue-100">
            <h3 class="font-bold text-blue-800 mb-4">LAPORAN NON-TUNAI (NON-LACI)</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span>Total Masuk Transfer</span> <b>Rp {{ number_format($pemasukan->transfer_masuk) }}</b></div>
                <div class="flex justify-between"><span>Total Masuk Debit</span> <b>Rp {{ number_format($pemasukan->debit_masuk ?? 0) }}</b></div>
                <p class="text-[10px] text-blue-500 mt-4">*Uang Transfer/Debit tidak dihitung dalam saldo fisik laci.</p>
            </div>
        </div>
    </div>

    <form action="{{ route('kasir.simpan-closing') }}" method="POST" class="bg-slate-900 p-8 rounded-3xl text-white">
        @csrf
        <input type="hidden" name="id_kas" value="{{ $kas->id_kas }}">
        <input type="hidden" name="saldo_sistem" value="{{ $saldo_akhir_sistem }}">
        <input type="hidden" name="tunai_masuk" value="{{ $pemasukan->tunai_masuk }}">
        <input type="hidden" name="transfer_masuk" value="{{ $pemasukan->transfer_masuk }}">
        <input type="hidden" name="debit_masuk" value="{{ $pemasukan->debit_masuk ?? 0 }}">
        <input type="hidden" name="tunai_keluar" value="{{ $pengeluaran_tunai }}">

        <label class="block text-center text-sm font-bold text-slate-400 mb-2 uppercase">Masukkan Jumlah Uang Fisik di Laci</label>
        <input type="number" name="saldo_fisik" class="w-full bg-transparent text-center text-4xl font-black text-white border-b-2 border-slate-700 focus:outline-none focus:border-emerald-500 mb-6" placeholder="0" required>

        <button type="submit" class="w-full py-4 bg-emerald-500 hover:bg-emerald-400 text-white rounded-2xl font-black uppercase tracking-widest transition-all">
            VERIFIKASI & KUNCI KASIR
        </button>
    </form>
</div>
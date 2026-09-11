<div class="max-w-4xl mx-auto p-6 bg-white rounded-3xl shadow-xl">
    <h2 class="text-2xl font-black text-slate-800 mb-6 uppercase tracking-tighter">Closing Alur Kas Kasir</h2>

    {{-- REKAP TRANSAKSI - nota Order yang tgl_order-nya HARI INI, dipecah per
         Mesin/Divisi (lihat KasirController::previewClosing()). "Nota
         Penjualan Barang"/"Piutang Penjualan Barang" di referensi TIDAK
         direplikasi di sini - app ini cuma py satu jenis transaksi
         (transaksi+transaksi_detail mencakup produk & jasa sekaligus),
         tidak ada jalur "penjualan barang" terpisah dari "nota order"
         seperti sistem lama, jadi tidak py padanan data nyata. --}}
    <div class="mb-3">
        <span class="inline-block px-3 py-1 bg-rose-500 text-white text-[10px] font-black uppercase tracking-widest rounded-lg">Rekap Transaksi</span>
    </div>
    <div class="rounded-2xl border border-slate-200 overflow-hidden mb-6">
        <div class="grid grid-cols-2 bg-emerald-50 border-b border-slate-200">
            <div class="px-5 py-3 text-xs font-black text-emerald-800 uppercase">Nota Order Cetak</div>
            <div class="px-5 py-3 text-sm font-bold text-slate-700 text-right">Rp {{ number_format($totalNotaOrder, 0, ',', '.') }}</div>
        </div>
        @forelse($rekapKategori as $rk)
        <div class="grid grid-cols-2 border-b border-slate-100 bg-sky-50/40">
            <div class="px-5 py-2.5 text-xs font-bold text-slate-600 pl-8">{{ $rk->kategori }}</div>
            <div class="px-5 py-2.5 text-xs font-semibold text-slate-500 text-right">Rp {{ number_format($rk->total, 0, ',', '.') }}</div>
        </div>
        @empty
        <div class="px-5 py-4 text-xs text-slate-400 italic text-center">Belum ada nota order hari ini.</div>
        @endforelse

        <div class="grid grid-cols-2 bg-rose-50 border-b border-t border-slate-200">
            <div class="px-5 py-3 text-xs font-black text-rose-700 uppercase">Piutang Nota Order</div>
            <div class="px-5 py-3 text-sm font-bold text-rose-600 text-right">Rp {{ number_format($piutangNotaOrder, 0, ',', '.') }}</div>
        </div>

        <div class="grid grid-cols-2 bg-indigo-600">
            <div class="px-5 py-4 text-xs font-black text-white uppercase tracking-widest">Total Pendapatan</div>
            <div class="px-5 py-4 text-base font-black text-white text-right">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</div>
        </div>
    </div>

    {{-- REKAP PEMBAYARAN - seluruh uang yang DITERIMA hari ini (tgl_bayar),
         dipecah pelunasan nota lama vs nota yang order & bayarnya sama-sama
         hari ini. "Bayar DP Transaksi Ref" di referensi TIDAK direplikasi -
         tidak py alur "transaksi referensi" yang jelas padanannya di app
         ini, dari pada dipaksakan tanpa tahu maksud pastinya. --}}
    <div class="mb-3">
        <span class="inline-block px-3 py-1 bg-emerald-500 text-white text-[10px] font-black uppercase tracking-widest rounded-lg">Rekap Pembayaran</span>
    </div>
    <div class="rounded-2xl border border-slate-200 overflow-hidden mb-6">
        <div class="grid grid-cols-2 border-b border-slate-100 bg-sky-50/40">
            <div class="px-5 py-3 text-xs font-bold text-slate-600">Bayar Pelunasan (nota hari sebelumnya)</div>
            <div class="px-5 py-3 text-sm font-bold text-slate-700 text-right">Rp {{ number_format($bayarPelunasan, 0, ',', '.') }}</div>
        </div>
        <div class="grid grid-cols-2 bg-emerald-50/60">
            <div class="px-5 py-3 text-xs font-black text-emerald-700 uppercase">Bayar Transaksi Hari Ini</div>
            <div class="px-5 py-3 text-sm font-bold text-emerald-700 text-right">Rp {{ number_format($bayarHariIni, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-6 mb-8">
        <div class="p-6 bg-emerald-50 rounded-2xl border-2 border-emerald-100">
            <h3 class="font-bold text-emerald-800 mb-4">ALUR KAS TUNAI (LACI)</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span>Saldo Awal (Modal)</span> <b>Rp {{ number_format($kas->saldo_awal) }}</b></div>
                <div class="flex justify-between text-emerald-600"><span>Pemasukan Tunai Nota (+)</span> <b>Rp {{ number_format($pemasukan->tunai_masuk) }}</b></div>
                <div class="flex justify-between text-emerald-600"><span>Kas Tidak Terduga Masuk (+)</span> <b>Rp {{ number_format($manualMasuk) }}</b></div>
                <div class="flex justify-between text-red-500"><span>Kas Tidak Terduga Keluar (-)</span> <b>Rp {{ number_format($pengeluaran_tunai) }}</b></div>
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
                <div class="flex justify-between"><span>Total Masuk Deposit</span> <b>Rp {{ number_format($pemasukan->deposit_masuk ?? 0) }}</b></div>
                <p class="text-[10px] text-blue-500 mt-4">*Transfer/Debit/Deposit tidak dihitung dalam saldo fisik laci - Deposit sudah masuk kas saat konsumen setor deposit dulu, bukan hari ini.</p>
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

        {{-- "Saldo Revisi" di referensi = field ini (uang fisik hasil hitung
             manual di laci) - nama disamakan konsepnya, cuma label mengikuti
             yang sudah dipakai sebelumnya di app ini supaya konsisten. --}}
        <label class="block text-center text-sm font-bold text-slate-400 mb-2 uppercase">Saldo Revisi (Uang Fisik di Laci)</label>
        <input type="number" name="saldo_fisik" class="w-full bg-transparent text-center text-4xl font-black text-white border-b-2 border-slate-700 focus:outline-none focus:border-emerald-500 mb-6" placeholder="0" required>

        <button type="submit" class="w-full py-4 bg-emerald-500 hover:bg-emerald-400 text-white rounded-2xl font-black uppercase tracking-widest transition-all">
            VERIFIKASI & KUNCI KASIR
        </button>
    </form>
</div>

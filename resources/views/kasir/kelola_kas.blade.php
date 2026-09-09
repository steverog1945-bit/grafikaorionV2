@extends('layouts.app')

@section('content')
<div class="mb-8 flex justify-between items-end">
    <div>
        <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight uppercase">Kelola Kas Harian</h2>
        <p class="text-slate-500 text-sm italic font-medium">Manajemen saldo awal dan status operasional kasir.</p>
    </div>

    {{-- KONDISI TOMBOL BERDASARKAN STATUS KAS --}}
    @if(!$kasAktif)
        <button onclick="aksiKas('open')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-2xl font-bold text-xs shadow-lg shadow-indigo-200 transition-all flex items-center gap-2 uppercase tracking-widest">
            <span class="text-lg">💰</span> Buka Saldo Kasir
        </button>
    @else
        <div class="flex gap-3">
            <div class="bg-emerald-50 border border-emerald-100 px-4 py-2 rounded-2xl flex items-center gap-3">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
                <span class="text-[10px] font-black text-emerald-600 uppercase">Kasir Aktif</span>
            </div>
            @if($kasAktif)
            <button onclick="openModalClosing()" class="bg-red-600 text-white px-6 py-2 rounded-lg font-bold">
    🔒 CLOSING KAS SEKARANG
</button>  
            <div id="modalClosing" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white p-8 rounded-2xl max-w-md w-full shadow-2xl">
            <h2 class="text-xl font-bold mb-4">Closing Kasir</h2>
            
            <form action="{{ route('kasir.proses-closing') }}" method="POST">
                @csrf
                <input type="hidden" name="id_kas" value="{{ $kasAktif->id_kas }}">
                
                <div class="space-y-4">
                    <div class="bg-slate-100 p-4 rounded-xl">
                        <p class="text-sm text-slate-500">Saldo Akhir di Sistem:</p>
                        <p class="text-2xl font-black text-indigo-700">Rp {{ number_format($kasAktif->saldo_akhir, 0, ',', '.') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold mb-1">Jumlah Uang Fisik (Di Laci)</label>
                        <input type="number" name="saldo_fisik" class="w-full border p-3 rounded-lg text-xl font-bold" required placeholder="0">
                    </div>

                    <div>
                        <label class="block text-sm font-bold mb-1">Keterangan/Catatan</label>
                        <textarea name="keterangan" class="w-full border p-3 rounded-lg" placeholder="Contoh: Aman, atau Selisih kurang karena..."></textarea>
                    </div>

                    <div class="flex gap-2">
                        <button type="button" onclick="closeModalClosing()" class="flex-1 py-3 bg-slate-200 rounded-lg">Batal</button>
                        <button type="submit" class="flex-1 py-3 bg-red-600 text-white rounded-lg font-bold">KONFIRMASI CLOSING</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openModalClosing() { document.getElementById('modalClosing').classList.remove('hidden'); }
    function closeModalClosing() { document.getElementById('modalClosing').classList.add('hidden'); }
</script>
@endif
        </div>
    @endif
</div>

<div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
        <div>
            <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">Rincian Mutasi Kas Hari Ini</h3>
            <p class="text-xs text-slate-500 italic">Daftar masuknya uang berdasarkan nota dan metode pembayaran</p>
        </div>
        <div class="flex gap-4">
            {{-- Badge Ringkasan Cepat --}}
            <div class="px-4 py-2 bg-emerald-50 rounded-xl border border-emerald-100 text-right">
                <span class="block text-[9px] font-black text-emerald-600 uppercase">Total Tunai</span>
                <span class="font-mono font-bold text-emerald-700">Rp {{ number_format($data_kas->sum('nominal_tunai'), 0, ',', '.') }}</span>
            </div>
            <div class="px-4 py-2 bg-blue-50 rounded-xl border border-blue-100 text-right">
                <span class="block text-[9px] font-black text-blue-600 uppercase">Total Transfer</span>
                <span class="font-mono font-bold text-blue-700">Rp {{ number_format($data_kas->sum('nominal_transfer'), 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50">
                <th class="p-4 text-[10px] font-black uppercase text-slate-400">Waktu</th>
                <th class="p-4 text-[10px] font-black uppercase text-slate-400">Sumber / Konsumen</th>
                <th class="p-4 text-[10px] font-black uppercase text-slate-400">No. Nota</th>
                <th class="p-4 text-[10px] font-black uppercase text-slate-400 text-center">Metode</th>
                <th class="p-4 text-[10px] font-black uppercase text-slate-400 text-right">Nominal Masuk</th>
                <th class="p-4 text-[10px] font-black uppercase text-slate-400 text-center">Status Nota</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($mutasi as $m)
            <tr class="hover:bg-slate-50/50 transition-all">
                <td class="p-4 font-mono text-xs text-slate-500">{{ date('H:i', strtotime($m->tgl_bayar)) }}</td>
                <td class="p-4">
                    <span class="block font-bold text-slate-700 uppercase text-sm">{{ $m->nama_konsumen }}</span>
                    <span class="text-[10px] text-slate-400 italic">{{ $m->keterangan }}</span>
                </td>
                <td class="p-4">
                    <span class="px-3 py-1 bg-slate-100 rounded-lg text-xs font-bold text-slate-600">#{{ $m->nomor_nota }}</span>
                </td>
                <td class="p-4 text-center">
                    @if($m->metode == 'Tunai')
                        <span class="px-2 py-1 bg-amber-100 text-amber-700 rounded text-[9px] font-black">💵 TUNAI</span>
                    @else
                        <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-[9px] font-black">🏦 TRANSFER</span>
                    @endif
                </td>
                <td class="p-4 text-right">
                    <span class="font-mono font-black {{ $m->metode == 'Tunai' ? 'text-emerald-600' : 'text-blue-600' }}">
                        Rp {{ number_format($m->nominal, 0, ',', '.') }}
                    </span>
                </td>
                <td class="p-4 text-center">
                    <span class="text-[10px] font-black uppercase {{ $m->status_pembayaran == 'Lunas' ? 'text-emerald-500' : 'text-amber-500' }}">
                        {{ $m->status_pembayaran }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
function aksiKas(tipe) {
    if(tipe === 'open') {
        Swal.fire({
            title: 'Buka Saldo Awal',
            text: 'Masukkan jumlah modal awal yang ada di laci kasir sekarang:',
            input: 'number',
            inputAttributes: { min: 0 },
            showCancelButton: true,
            confirmButtonText: 'Buka Kas',
            confirmButtonColor: '#4f46e5',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-[2rem]' }
        }).then((result) => {
            if (result.isConfirmed) {
                if(result.value >= 0) {
                    window.location.href = `{{ url('/kasir/store-kas') }}?jumlah=${result.value}`;
                } else {
                    Swal.fire('Error', 'Jumlah tidak valid', 'error');
                }
            }
        });
    }
}
</script>
@endsection
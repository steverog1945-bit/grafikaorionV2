@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Deposit Konsumen" subtitle="Pencatatan setoran deposit pelanggan.">
        <x-slot:actions>
            <x-button onclick="toggleModal('modalDeposit')">
                <i class="fas fa-piggy-bank"></i>
                Setoran Baru
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- SALDO PER KONSUMEN --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden mb-8">
        <div class="px-8 py-5 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-sm font-black text-slate-700 uppercase tracking-tight">Saldo Deposit Aktif</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em] bg-slate-50/50 border-b border-slate-100">
                        <th class="px-8 py-4 text-left">Konsumen</th>
                        <th class="px-6 py-4 text-center">Golongan</th>
                        <th class="px-8 py-4 text-right">Saldo Deposit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($saldoPerKonsumen as $s)
                    <tr class="hover:bg-slate-50/30 transition-all">
                        <td class="px-8 py-4 text-sm font-bold text-slate-700 uppercase">{{ $s->nama_konsumen }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-[10px] font-black uppercase bg-indigo-50 text-indigo-700 border border-indigo-100">
                                {{ $s->nama_golongan ?? '-' }}
                            </span>
                        </td>
                        <td class="px-8 py-4 text-right text-sm font-black text-emerald-600">Rp {{ number_format($s->saldo_deposit, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="py-16 text-center text-slate-400 font-medium italic">Belum ada deposit tercatat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- RIWAYAT SETORAN --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-8 py-5 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-sm font-black text-slate-700 uppercase tracking-tight">Riwayat Deposit (50 Terbaru)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em] bg-slate-50/50 border-b border-slate-100">
                        <th class="px-8 py-4 text-left">Tanggal</th>
                        <th class="px-6 py-4 text-left">Konsumen</th>
                        <th class="px-6 py-4 text-center">Tipe</th>
                        <th class="px-6 py-4 text-right">Nominal</th>
                        <th class="px-6 py-4 text-center">Metode / Nota</th>
                        <th class="px-6 py-4 text-left">Keterangan</th>
                        <th class="px-8 py-4 text-center">Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($riwayat as $r)
                    <tr class="hover:bg-slate-50/30 transition-all">
                        <td class="px-8 py-4 text-sm text-slate-600 font-semibold">{{ \Carbon\Carbon::parse($r->tgl_deposit)->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-sm font-bold text-slate-700 uppercase">{{ $r->nama_konsumen }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-[10px] font-black uppercase {{ $r->tipe === 'Setoran' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                                {{ $r->tipe }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-black {{ $r->tipe === 'Setoran' ? 'text-emerald-600' : 'text-rose-500' }}">
                            {{ $r->tipe === 'Setoran' ? '+' : '-' }} Rp {{ number_format($r->nominal, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($r->tipe === 'Setoran')
                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-[10px] font-black uppercase {{ $r->metode_bayar === 'Tunai' ? 'bg-amber-50 text-amber-600' : 'bg-blue-50 text-blue-600' }}">
                                    {{ $r->metode_bayar }}
                                </span>
                            @else
                                <span class="text-[11px] text-indigo-500">{{ $r->nomor_nota ?? '-' }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-500 italic truncate max-w-xs">{{ $r->keterangan ?? '-' }}</td>
                        <td class="px-8 py-4 text-center text-[11px] text-slate-400">{{ $r->dicatat_oleh ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-16 text-center text-slate-400 font-medium italic">Belum ada riwayat deposit.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL SETORAN BARU --}}
<div id="modalDeposit" class="hidden fixed inset-0 z-[70] overflow-y-auto bg-slate-900/40 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen p-6">
        <div class="bg-white rounded-[3rem] shadow-[0_20px_50px_rgba(0,0,0,0.1)] w-full max-w-[500px] overflow-hidden transform transition-all">

            <div class="px-10 py-8 bg-indigo-600 flex justify-between items-center text-white">
                <div class="flex items-center gap-3">
                    <i class="fas fa-piggy-bank"></i>
                    <h3 class="text-xl font-bold tracking-tight">Setoran Deposit Baru</h3>
                </div>
                <button onclick="toggleModal('modalDeposit')" class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition-colors">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>

            <form action="{{ route('konsumen-deposit.store') }}" method="POST" class="py-10 space-y-5">
                @csrf
                <div class="space-y-5 max-w-[85%] mx-auto">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1 text-left">Konsumen</label>
                        <select name="id_konsumen" required
                               class="w-full bg-slate-50 border-none rounded-2xl py-3.5 px-5 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner appearance-none">
                            <option value="">-- Pilih Konsumen --</option>
                            @foreach($konsumen as $k)
                                <option value="{{ $k->id_konsumen }}">{{ $k->nama_konsumen }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1 text-left">Nominal Deposit</label>
                        <input type="number" step="0.01" min="1" name="nominal" placeholder="0" required
                               class="w-full bg-slate-50 border-none rounded-2xl py-3.5 px-5 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1 text-left">Tanggal</label>
                            <input type="date" name="tgl_deposit" value="{{ date('Y-m-d') }}" required
                                   class="w-full bg-slate-50 border-none rounded-2xl py-3.5 px-5 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1 text-left">Metode</label>
                            <select name="metode_bayar" required
                                   class="w-full bg-slate-50 border-none rounded-2xl py-3.5 px-5 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner appearance-none">
                                <option value="Tunai">Tunai</option>
                                <option value="Transfer">Transfer</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1 text-left">Keterangan</label>
                        <textarea name="keterangan" placeholder="Keterangan setoran (opsional)..."
                                  class="w-full bg-slate-50 border-none rounded-2xl py-3.5 px-5 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner"></textarea>
                    </div>
                </div>

                <div class="flex flex-col items-center gap-3 pt-4 max-w-[70%] mx-auto">
                    <button type="submit" class="w-full py-4 bg-indigo-600 text-white rounded-2xl text-[11px] font-bold uppercase tracking-widest hover:bg-indigo-700 shadow-lg shadow-indigo-100">Simpan Setoran</button>
                    <button type="button" onclick="toggleModal('modalDeposit')" class="w-full py-4 bg-transparent text-slate-400 rounded-2xl text-[11px] font-bold uppercase tracking-widest">Batalkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.toggle('hidden');
            document.body.style.overflow = modal.classList.contains('hidden') ? 'auto' : 'hidden';
        }
    }
</script>
@endsection

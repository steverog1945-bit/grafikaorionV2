@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">
    <x-page-header title="Neraca" subtitle="Posisi keuangan (Aset = Kewajiban + Ekuitas) per tanggal tertentu." />

    @include('akuntansi._subnav')

    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 p-6 mb-6">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Per Tanggal</label>
                <input type="date" name="tanggal" value="{{ $tanggal }}" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner">
            </div>
            <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-xl text-[11px] font-bold uppercase tracking-widest hover:bg-indigo-700">
                <i class="fas fa-filter mr-1"></i> Tampilkan
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- AKTIVA --}}
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-8 py-5 border-b border-slate-100">
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold bg-sky-50 text-sky-700 border border-sky-100 uppercase">Aktiva (Aset)</span>
            </div>
            <div class="p-8 space-y-2">
                @forelse($aset as $a)
                <div class="flex justify-between text-sm">
                    <span class="text-slate-600">{{ $a->nama_akun }}</span>
                    <span class="font-mono font-medium text-slate-700">{{ number_format($a->jumlah, 0, ',', '.') }}</span>
                </div>
                @empty
                <p class="text-xs text-slate-400 italic">Belum ada akun aset dengan saldo.</p>
                @endforelse
            </div>
            <div class="px-8 py-5 bg-sky-50/50 border-t border-sky-100 flex justify-between items-center">
                <span class="text-xs font-bold text-sky-700 uppercase tracking-widest">Total Aktiva</span>
                <span class="text-lg font-black font-mono text-sky-700">{{ number_format($totalAset, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- PASIVA --}}
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-8 py-5 border-b border-slate-100">
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold bg-rose-50 text-rose-700 border border-rose-100 uppercase">Kewajiban</span>
            </div>
            <div class="p-8 space-y-2">
                @forelse($kewajiban as $k)
                <div class="flex justify-between text-sm">
                    <span class="text-slate-600">{{ $k->nama_akun }}</span>
                    <span class="font-mono font-medium text-slate-700">{{ number_format($k->jumlah, 0, ',', '.') }}</span>
                </div>
                @empty
                <p class="text-xs text-slate-400 italic">Belum ada akun kewajiban dengan saldo.</p>
                @endforelse
            </div>
            <div class="px-8 py-4 bg-rose-50/50 border-t border-rose-100 flex justify-between items-center">
                <span class="text-xs font-bold text-rose-700 uppercase tracking-widest">Total Kewajiban</span>
                <span class="text-sm font-black font-mono text-rose-700">{{ number_format($totalKewajiban, 0, ',', '.') }}</span>
            </div>

            <div class="px-8 py-5 border-b border-t border-slate-100">
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold bg-violet-50 text-violet-700 border border-violet-100 uppercase">Ekuitas</span>
            </div>
            <div class="p-8 space-y-2">
                @forelse($modal as $m)
                <div class="flex justify-between text-sm">
                    <span class="text-slate-600">{{ $m->nama_akun }}</span>
                    <span class="font-mono font-medium text-slate-700">{{ number_format($m->jumlah, 0, ',', '.') }}</span>
                </div>
                @empty
                <p class="text-xs text-slate-400 italic">Belum ada setoran modal tercatat.</p>
                @endforelse
                <div class="flex justify-between text-sm">
                    <span class="text-slate-600">Laba Ditahan Berjalan</span>
                    <span class="font-mono font-medium {{ $labaBerjalan >= 0 ? 'text-slate-700' : 'text-rose-600' }}">{{ number_format($labaBerjalan, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="px-8 py-5 bg-violet-50/50 border-t border-violet-100 flex justify-between items-center">
                <span class="text-xs font-bold text-violet-700 uppercase tracking-widest">Total Ekuitas</span>
                <span class="text-lg font-black font-mono text-violet-700">{{ number_format($totalEkuitas, 0, ',', '.') }}</span>
            </div>

            <div class="px-8 py-5 bg-slate-50 border-t-2 border-slate-100 flex justify-between items-center">
                <span class="text-xs font-bold text-slate-600 uppercase tracking-widest">Total Kewajiban + Ekuitas</span>
                <span class="text-lg font-black font-mono text-slate-800">{{ number_format($totalPasiva, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <div class="mt-6 rounded-2xl p-5 text-center {{ $balance ? 'bg-emerald-50 border border-emerald-100' : 'bg-rose-50 border border-rose-100' }}">
        @if($balance)
            <span class="text-xs font-bold text-emerald-700 uppercase tracking-widest"><i class="fas fa-check-circle"></i> Balance - Total Aktiva = Total Kewajiban + Ekuitas</span>
        @else
            <span class="text-xs font-bold text-rose-700 uppercase tracking-widest"><i class="fas fa-exclamation-circle"></i> TIDAK BALANCE - Selisih {{ number_format(abs($totalAset - $totalPasiva), 0, ',', '.') }}</span>
        @endif
    </div>
</div>
@endsection

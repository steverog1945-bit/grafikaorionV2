@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">
    <x-page-header title="Laporan Laba Rugi" subtitle="Pendapatan dikurangi beban untuk periode yang dipilih." />

    @include('akuntansi._subnav')

    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 p-6 mb-6">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Dari Tanggal</label>
                <input type="date" name="awal" value="{{ $awal }}" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Sampai Tanggal</label>
                <input type="date" name="akhir" value="{{ $akhir }}" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner">
            </div>
            <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-xl text-[11px] font-bold uppercase tracking-widest hover:bg-indigo-700">
                <i class="fas fa-filter mr-1"></i> Tampilkan
            </button>
        </form>
    </div>

    <div class="max-w-3xl mx-auto bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-10 py-6 border-b border-slate-100 text-center">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Periode</p>
            <p class="text-sm font-bold text-slate-700 mt-1">{{ \Carbon\Carbon::parse($awal)->format('d M Y') }} — {{ \Carbon\Carbon::parse($akhir)->format('d M Y') }}</p>
        </div>

        <div class="p-10 space-y-8">
            <div>
                <p class="text-xs font-bold text-emerald-600 uppercase tracking-widest mb-4">Pendapatan</p>
                <div class="space-y-2">
                    @forelse($pendapatan as $p)
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">{{ $p->nama_akun }}</span>
                        <span class="font-medium text-slate-700">{{ number_format($p->jumlah, 0, ',', '.') }}</span>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 italic">Belum ada pendapatan pada periode ini.</p>
                    @endforelse
                </div>
                <div class="flex justify-between text-sm font-bold border-t border-slate-100 mt-4 pt-3">
                    <span class="text-slate-700">Total Pendapatan</span>
                    <span class="text-emerald-700">{{ number_format($totalPendapatan, 0, ',', '.') }}</span>
                </div>
            </div>

            <div>
                <p class="text-xs font-bold text-amber-600 uppercase tracking-widest mb-4">Beban</p>
                <div class="space-y-2">
                    @forelse($beban as $b)
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">{{ $b->nama_akun }}</span>
                        <span class="font-medium text-slate-700">{{ number_format($b->jumlah, 0, ',', '.') }}</span>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 italic">Belum ada beban tercatat pada periode ini.</p>
                    @endforelse
                </div>
                <div class="flex justify-between text-sm font-bold border-t border-slate-100 mt-4 pt-3">
                    <span class="text-slate-700">Total Beban</span>
                    <span class="text-amber-700">{{ number_format($totalBeban, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="rounded-2xl p-6 {{ $labaBersih >= 0 ? 'bg-emerald-50 border border-emerald-100' : 'bg-rose-50 border border-rose-100' }}">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-bold uppercase tracking-widest {{ $labaBersih >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                        {{ $labaBersih >= 0 ? 'Laba Bersih' : 'Rugi Bersih' }}
                    </span>
                    <span class="text-2xl font-black {{ $labaBersih >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                        {{ number_format(abs($labaBersih), 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

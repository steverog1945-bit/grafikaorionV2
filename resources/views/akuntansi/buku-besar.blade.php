@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">
    <x-page-header title="Buku Besar" subtitle="Mutasi & saldo berjalan per akun." />

    @include('akuntansi._subnav')

    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 p-6 mb-6">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div class="min-w-[260px]">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Akun</label>
                <select name="kode_akun" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-inner">
                    @foreach($daftarAkun as $a)
                        <option value="{{ $a->kode_akun }}" {{ $kodeAkun == $a->kode_akun ? 'selected' : '' }}>{{ $a->kode_akun }} - {{ $a->nama_akun }}</option>
                    @endforeach
                </select>
            </div>
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

    @if($akun)
    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-8 py-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">{{ $akun->kode_akun }}</span>
                <span class="ml-2 text-sm font-bold text-slate-700">{{ $akun->nama_akun }}</span>
            </div>
            <span class="text-[10px] font-bold text-slate-400 uppercase">Saldo Normal: {{ $akun->saldo_normal }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em] bg-slate-50/50 border-b border-slate-100">
                        <th class="px-8 py-4 text-left w-28">Tanggal</th>
                        <th class="px-6 py-4 text-left">No. Referensi / Keterangan</th>
                        <th class="px-6 py-4 text-right w-32">Debet</th>
                        <th class="px-6 py-4 text-right w-32">Kredit</th>
                        <th class="px-8 py-4 text-right w-36">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <tr class="bg-slate-50/40">
                        <td colspan="4" class="px-8 py-3 text-xs font-bold text-slate-500 uppercase tracking-widest">Saldo Awal</td>
                        <td class="px-8 py-3 text-right text-sm font-bold text-slate-700">{{ number_format($saldoAwal, 0, ',', '.') }}</td>
                    </tr>
                    @forelse($rows as $r)
                    <tr class="hover:bg-slate-50/30 transition-all">
                        <td class="px-8 py-3 text-xs text-slate-500">{{ \Carbon\Carbon::parse($r->tgl_jurnal)->format('d/m/Y') }}</td>
                        <td class="px-6 py-3 text-sm text-slate-700">
                            <span class="text-xs text-slate-400">{{ $r->no_referensi }}</span> — {{ $r->keterangan }}
                        </td>
                        <td class="px-6 py-3 text-right text-sm {{ $r->debet > 0 ? 'text-slate-700 font-bold' : 'text-slate-300' }}">
                            {{ $r->debet > 0 ? number_format($r->debet, 0, ',', '.') : '-' }}
                        </td>
                        <td class="px-6 py-3 text-right text-sm {{ $r->kredit > 0 ? 'text-slate-700 font-bold' : 'text-slate-300' }}">
                            {{ $r->kredit > 0 ? number_format($r->kredit, 0, ',', '.') : '-' }}
                        </td>
                        <td class="px-8 py-3 text-right text-sm font-bold text-slate-800">{{ number_format($r->saldo_berjalan, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-16 text-center text-slate-400 font-medium italic">Tidak ada mutasi pada rentang tanggal ini.</td>
                    </tr>
                    @endforelse
                    <tr class="bg-indigo-50/40 border-t-2 border-indigo-100">
                        <td colspan="4" class="px-8 py-4 text-right text-[11px] font-bold text-indigo-700 uppercase tracking-widest">Saldo Akhir</td>
                        <td class="px-8 py-4 text-right text-sm font-black text-indigo-700">{{ number_format($saldoAkhir, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 p-16 text-center text-slate-400 italic">
        Belum ada akun terdaftar. Tambah akun dulu di Chart of Accounts.
    </div>
    @endif
</div>
@endsection

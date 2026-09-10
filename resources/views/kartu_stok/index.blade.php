@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Kartu Stok">
        <x-slot:subtitle>
            Bahan Baku &amp; Logistik <span class="mx-1 text-slate-300">/</span> Riwayat Pergerakan Stok per Bahan
        </x-slot:subtitle>
    </x-page-header>

    <x-table>
        <thead>
            <tr>
                <th class="px-6 py-5 text-left">Kode</th>
                <th class="px-4 py-5 text-left">Nama Bahan</th>
                <th class="px-4 py-5 text-right">Stok Saat Ini</th>
                <th class="px-8 py-5 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($bahan as $b)
            <tr class="group hover:bg-slate-50/50 transition-all">
                <td class="px-6 py-5">
                    <span class="text-[12px] font-bold text-slate-700 font-mono">{{ $b->kode_bahan }}</span>
                </td>
                <td class="px-4 py-5">
                    <span class="text-[13px] font-bold text-slate-700 uppercase group-hover:text-indigo-600 transition-colors">{{ $b->nama_bahan }}</span>
                </td>
                <td class="px-4 py-5 text-right">
                    <span class="text-[13px] font-bold text-slate-800">{{ number_format($b->stok, 2) }}</span>
                </td>
                <td class="px-8 py-5 text-center">
                    <x-button href="{{ url('/kartu-stok/'.$b->id_bahan) }}" size="icon" variant="ghost" class="border border-slate-100 hover:text-indigo-600" title="Lihat Kartu Stok">
                        <i class="fas fa-book text-xs"></i>
                    </x-button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4">
                    <x-empty-state title="Belum ada data bahan baku" />
                </td>
            </tr>
            @endforelse
        </tbody>
    </x-table>

</div>
@endsection

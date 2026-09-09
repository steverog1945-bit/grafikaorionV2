@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Riwayat Nota" subtitle="Daftar transaksi penjualan &bull; default hari ini">
        <x-slot:actions>
            <x-button href="{{ route('penjualan.create') }}" variant="outline">
                <i class="fas fa-arrow-left"></i> Kembali ke Kasir
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- FILTER RANGE TANGGAL --}}
    <x-card class="mb-8">
        <form method="GET" action="{{ route('penjualan.riwayat') }}" class="flex flex-wrap items-end gap-4">
            <div class="w-full md:w-56">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Dari Tanggal</label>
                <x-form.input type="date" name="from" value="{{ $from }}" />
            </div>

            <div class="w-full md:w-56">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Sampai Tanggal</label>
                <x-form.input type="date" name="to" value="{{ $to }}" />
            </div>

            <x-button type="submit">
                <i class="fas fa-filter"></i> Tampilkan
            </x-button>

            <x-button href="{{ route('penjualan.riwayat') }}" variant="outline">
                Hari Ini
            </x-button>
        </form>
    </x-card>

    <x-table>
        <thead>
            <tr>
                <th class="px-8 py-5 text-left">No. Nota</th>
                <th class="px-6 py-5 text-left">Tanggal & Jam</th>
                <th class="px-6 py-5 text-left">Pelanggan</th>
                <th class="px-6 py-5 text-left">Kasir</th>
                <th class="px-6 py-5 text-center">Status</th>
                <th class="px-8 py-5 text-right">Total</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($transaksis as $t)
            <tr class="group hover:bg-slate-50/30 transition-all">
                <td class="px-8 py-5">
                    <span class="text-[13px] font-bold text-slate-700 font-mono">{{ $t->nomor_nota }}</span>
                </td>
                <td class="px-6 py-5">
                    <span class="text-sm font-semibold text-slate-600">{{ \Carbon\Carbon::parse($t->tgl_order)->format('d M Y') }}</span>
                    <div class="text-[11px] text-slate-400">{{ \Carbon\Carbon::parse($t->tgl_order)->format('H:i') }}</div>
                </td>
                <td class="px-6 py-5">
                    <span class="text-sm font-bold text-slate-700">{{ $t->nama_konsumen }}</span>
                </td>
                <td class="px-6 py-5">
                    <span class="text-sm text-slate-500">{{ $t->nama_kasir ?? '-' }}</span>
                </td>
                <td class="px-6 py-5 text-center">
                    <x-badge :status="$t->status_pembayaran" />
                </td>
                <td class="px-8 py-5 text-right">
                    <span class="text-[14px] font-bold text-indigo-600">Rp {{ number_format($t->total_bayar, 0, ',', '.') }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6">
                    <x-empty-state title="Belum ada nota pada rentang tanggal ini" />
                </td>
            </tr>
            @endforelse
        </tbody>
    </x-table>

    <x-table.pagination :paginator="$transaksis" />

</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Riwayat Nota" :subtitle="$konsumenTerpilih ? 'Daftar transaksi penjualan per konsumen' : 'Daftar transaksi penjualan • default hari ini'">
        <x-slot:actions>
            <x-button href="{{ route('penjualan.create') }}" variant="outline">
                <i class="fas fa-arrow-left"></i> Kembali ke Kasir
            </x-button>
        </x-slot:actions>
    </x-page-header>

    @if($konsumenTerpilih)
        <div class="mb-6 flex items-center justify-between bg-indigo-50 border border-indigo-100 rounded-2xl px-6 py-4">
            <p class="text-[13px] text-indigo-700">
                <i class="fas fa-user mr-1.5"></i>
                Menampilkan seluruh riwayat untuk <b>{{ $konsumenTerpilih->nama_konsumen }}</b>
                @if($from || $to)
                    (dipersempit ke rentang tanggal di bawah)
                @endif
            </p>
            <a href="{{ route('penjualan.riwayat') }}" class="text-[11px] font-bold text-indigo-500 uppercase tracking-widest hover:underline">
                <i class="fas fa-times-circle mr-1"></i> Hapus Filter Konsumen
            </a>
        </div>
    @endif

    {{-- FILTER RANGE TANGGAL --}}
    <x-card class="mb-8">
        <form method="GET" action="{{ route('penjualan.riwayat') }}" class="flex flex-wrap items-end gap-4">
            @if($konsumenTerpilih)
                <input type="hidden" name="id_konsumen" value="{{ $konsumenTerpilih->id_konsumen }}">
            @endif
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
                <th class="px-6 py-5 text-left">Golongan</th>
                <th class="px-6 py-5 text-left">Tipe Order</th>
                <th class="px-6 py-5 text-left">Kasir</th>
                <th class="px-6 py-5 text-center">Status</th>
                <th class="px-6 py-5 text-right">Total</th>
                <th class="px-8 py-5 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($transaksis as $t)
            <tr class="group hover:bg-slate-50/30 transition-all {{ $t->status_nota === 'Dibatalkan' ? 'opacity-50' : '' }}">
                <td class="px-8 py-5">
                    <span class="text-[13px] font-bold text-slate-700">{{ $t->nomor_nota }}</span>
                </td>
                <td class="px-6 py-5">
                    <span class="text-sm font-semibold text-slate-600">{{ \Carbon\Carbon::parse($t->tgl_order)->format('d M Y') }}</span>
                    <div class="text-[11px] text-slate-400">{{ \Carbon\Carbon::parse($t->tgl_order)->format('H:i') }}</div>
                </td>
                <td class="px-6 py-5">
                    <span class="text-sm font-bold text-slate-700">{{ $t->nama_konsumen }}</span>
                </td>
                <td class="px-6 py-5">
                    <span class="text-xs text-slate-500">{{ $t->nama_golongan ?? '-' }}</span>
                </td>
                <td class="px-6 py-5">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold {{ ($t->tipe_order ?? 'Reguler') === 'PO' ? 'bg-amber-50 text-amber-700 border border-amber-100' : 'bg-slate-50 text-slate-500 border border-slate-100' }}">
                        {{ $t->tipe_order ?? 'Reguler' }}
                    </span>
                </td>
                <td class="px-6 py-5">
                    <span class="text-sm text-slate-500">{{ $t->nama_kasir ?? '-' }}</span>
                </td>
                <td class="px-6 py-5 text-center">
                    @if($t->status_nota === 'Dibatalkan')
                        <x-badge status="Dibatalkan" variant="danger" />
                    @else
                        <x-badge :status="$t->status_pembayaran" />
                    @endif
                </td>
                <td class="px-6 py-5 text-right">
                    <span class="text-[14px] font-bold text-indigo-600">Rp {{ number_format($t->total_bayar, 0, ',', '.') }}</span>
                </td>
                <td class="px-8 py-5 text-right">
                    <a href="{{ route('penjualan.show', $t->id_transaksi) }}" class="inline-flex items-center gap-1 px-3 py-2 rounded-lg text-[11px] font-bold uppercase text-indigo-600 hover:bg-indigo-50 transition-all">
                        <i class="far fa-eye"></i> Lihat
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9">
                    <x-empty-state title="Belum ada nota pada rentang tanggal ini" />
                </td>
            </tr>
            @endforelse
        </tbody>
    </x-table>

    <x-table.pagination :paginator="$transaksis" />

</div>
@endsection

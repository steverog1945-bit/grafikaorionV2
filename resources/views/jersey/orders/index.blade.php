@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Order Jersey" subtitle="Transaksi order jersey - terpisah dari alur Kasir percetakan.">
        <x-slot:actions>
            <x-button href="{{ route('jersey.master.index') }}" variant="secondary">
                <i class="fas fa-sliders-h"></i> Master Data
            </x-button>
            <x-button href="{{ route('jersey.orders.create') }}">
                <i class="fas fa-plus"></i> Order Baru
            </x-button>
        </x-slot:actions>
    </x-page-header>

    @if (session('success'))
        <div class="bg-emerald-50 text-emerald-600 p-4 rounded-2xl mb-6 font-semibold">{{ session('success') }}</div>
    @endif

    <x-card class="mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <x-form.input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no. order / pelanggan / tim..." />
            </div>
            <x-form.select name="status">
                <option value="">-- Semua Status --</option>
                @foreach(['baru' => 'Baru', 'proses' => 'Proses', 'selesai' => 'Selesai', 'diambil' => 'Diambil'] as $val => $label)
                    <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                @endforeach
            </x-form.select>
            <x-button type="submit">Filter</x-button>
        </form>
    </x-card>

    <x-table>
        <thead>
            <tr>
                <th class="px-6 py-5 text-left">No. Order</th>
                <th class="px-6 py-5 text-left">Pelanggan / Tim</th>
                <th class="px-6 py-5 text-left">Kategori</th>
                <th class="px-6 py-5 text-center">Deadline</th>
                <th class="px-6 py-5 text-center">Status</th>
                <th class="px-6 py-5 text-center">Bayar</th>
                <th class="px-6 py-5 text-right">Total</th>
                <th class="px-6 py-5 text-center w-24">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($orders as $o)
            <tr class="hover:bg-slate-50/50">
                <td class="px-6 py-4 font-bold text-indigo-600">{{ $o->order_number }}</td>
                <td class="px-6 py-4">
                    <p class="font-semibold text-slate-700">{{ $o->customer_name }}</p>
                    @if($o->team_name)<p class="text-[11px] text-slate-400">{{ $o->team_name }}</p>@endif
                </td>
                <td class="px-6 py-4 text-xs text-slate-500">{{ $o->category->name ?? '-' }}</td>
                <td class="px-6 py-4 text-center text-xs">{{ $o->deadline ? \Carbon\Carbon::parse($o->deadline)->format('d/m/Y') : '-' }}</td>
                <td class="px-6 py-4 text-center">
                    @php $statusColor = ['baru' => 'bg-slate-100 text-slate-500', 'proses' => 'bg-amber-50 text-amber-600', 'selesai' => 'bg-indigo-50 text-indigo-600', 'diambil' => 'bg-emerald-50 text-emerald-600'][$o->status] ?? 'bg-slate-100 text-slate-500'; @endphp
                    <span class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase {{ $statusColor }}">{{ $o->status }}</span>
                </td>
                <td class="px-6 py-4 text-center">
                    @php $bayarColor = ['Belum Bayar' => 'bg-rose-50 text-rose-600', 'DP' => 'bg-amber-50 text-amber-600', 'Lunas' => 'bg-emerald-50 text-emerald-600'][$o->status_pembayaran] ?? 'bg-slate-100 text-slate-500'; @endphp
                    <span class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase {{ $bayarColor }}">{{ $o->status_pembayaran }}</span>
                </td>
                <td class="px-6 py-4 text-right font-bold">Rp {{ number_format($o->total, 0, ',', '.') }}</td>
                <td class="px-6 py-4 text-center">
                    <x-button href="{{ route('jersey.orders.show', $o->id) }}" size="icon" variant="ghost" class="hover:text-indigo-600" title="Detail">
                        <i class="fas fa-eye text-xs"></i>
                    </x-button>
                </td>
            </tr>
            @empty
            <tr><td colspan="8"><x-empty-state title="Belum ada order jersey" description="Klik 'Order Baru' untuk membuat order pertama." /></td></tr>
            @endforelse
        </tbody>
    </x-table>

    <x-table.pagination :paginator="$orders" />
</div>
@endsection

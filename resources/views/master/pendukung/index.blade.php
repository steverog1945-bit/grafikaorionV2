@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">
    <x-page-header title="Mesin &amp; Kategori Bahan" subtitle="Master pendukung: mesin untuk produksi (muncul otomatis sebagai divisi di SPK) dan kategori bahan baku." />

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <x-card class="p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-6">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-500"><i class="fas fa-industry"></i></span>
                <h2 class="text-xl font-medium text-slate-800">Daftar Mesin</h2>
            </div>

            <form action="{{ route('mesin.store') }}" method="POST" class="space-y-4 mb-8">
                @csrf
                <div>
                    <label class="text-xs font-medium text-slate-500 uppercase ml-1">Nama Mesin</label>
                    <input type="text" name="nama_mesin" placeholder="Contoh: Roland Eco-Solvent" class="w-full mt-1 rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 transition" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-slate-500 uppercase ml-1">Tipe</label>
                        <input type="text" name="tipe_mesin" placeholder="Indoor/Outdoor" class="w-full mt-1 rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 transition">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-500 uppercase ml-1">Biaya/m</label>
                        <input type="text" inputmode="numeric" autocomplete="off" name="biaya_per_meter" placeholder="5000" class="w-full mt-1 rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 transition format-ribuan">
                    </div>
                </div>
                <x-button type="submit" class="w-full"><i class="fas fa-plus"></i> Simpan Mesin</x-button>
            </form>

            <div class="border-t border-slate-100 pt-4">
                <x-table bare>
                    <tbody>
                        @foreach($mesin as $m)
                        <tr>
                            <td class="font-medium text-slate-800">{{ $m->nama_mesin }}</td>
                            <td class="text-right text-xs text-slate-500">{{ $m->tipe_mesin }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </x-table>
            </div>
        </x-card>

        <x-card class="p-6 sm:p-8 h-fit">
            <div class="flex items-center gap-3 mb-6">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-500"><i class="fas fa-tags"></i></span>
                <h2 class="text-xl font-medium text-slate-800">Kategori Bahan</h2>
            </div>

            <form action="{{ route('kategori.store') }}" method="POST" class="flex gap-2 mb-8">
                @csrf
                <input type="text" name="nama_kategori" placeholder="Contoh: Sticker" class="flex-1 rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 transition" required>
                <x-button type="submit"><i class="fas fa-plus"></i> Tambah</x-button>
            </form>

            <div class="grid grid-cols-2 gap-2">
                @foreach($kategori as $kat)
                <div class="bg-slate-50 border border-slate-100 px-4 py-2 rounded-lg text-sm font-medium text-slate-700 flex justify-between">
                    {{ $kat->nama_kategori }}
                </div>
                @endforeach
            </div>
        </x-card>

    </div>
</div>
@endsection

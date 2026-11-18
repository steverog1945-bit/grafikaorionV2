@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-5xl mx-auto">

    <x-page-header title="Topbar Premium" subtitle="Baris kedua pada tema Premium — tab modul cepat. Hanya tampil di tema Premium." />

    @if(session('success'))
        <div class="bg-emerald-50 text-emerald-700 border border-emerald-100 p-4 rounded-2xl mb-6 font-medium">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-rose-50 text-rose-700 border border-rose-100 p-4 rounded-2xl mb-6 font-medium">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="bg-rose-50 text-rose-700 border border-rose-100 p-4 rounded-2xl mb-6 font-medium">
            <ul class="list-disc list-inside text-sm space-y-0.5">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('pengaturan-aplikasi.topbar.update') }}" id="form-topbar" class="space-y-4">
        @csrf

        <x-card>
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-sm font-medium text-slate-800">Tab modul</h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">Muncul di baris kedua tema Premium. Tab yang tidak punya izin untuk role tertentu otomatis tidak terlihat baginya — permisian tetap dari Hak Akses, bukan di sini.</p>
                </div>
                <x-button type="button" variant="secondary" onclick="tambahBarisTopbar()">
                    <i class="fas fa-plus text-xs"></i> Tambah tab
                </x-button>
            </div>

            <div class="overflow-x-auto -mx-2">
                <table class="w-full text-sm" id="tabel-topbar">
                    <thead>
                        <tr class="text-left text-[10px] font-medium text-slate-400 uppercase tracking-widest border-b border-slate-100">
                            <th class="px-2 py-2 w-36">Label</th>
                            <th class="px-2 py-2">URL (relatif)</th>
                            <th class="px-2 py-2 w-40">Ikon (fa-*)</th>
                            <th class="px-2 py-2 w-20">Urutan</th>
                            <th class="px-2 py-2 w-16 text-center">Aktif</th>
                            <th class="px-2 py-2 w-12"></th>
                        </tr>
                    </thead>
                    <tbody id="baris-topbar">
                        @foreach($tabs as $i => $t)
                        <tr class="border-b border-slate-50 align-middle">
                            <td class="px-2 py-2"><input type="text" name="rows[{{ $i }}][label]" value="{{ $t->label }}" required maxlength="40" class="w-36 rounded-lg border-slate-200 bg-slate-50 px-2 py-2 text-xs"></td>
                            <td class="px-2 py-2"><input type="text" name="rows[{{ $i }}][url]" value="{{ $t->url }}" required maxlength="191" class="w-full rounded-lg border-slate-200 bg-slate-50 px-2 py-2 font-mono text-xs" placeholder="/produksi"></td>
                            <td class="px-2 py-2"><input type="text" name="rows[{{ $i }}][icon]" value="{{ $t->icon }}" required maxlength="64" class="w-40 rounded-lg border-slate-200 bg-slate-50 px-2 py-2 font-mono text-xs" placeholder="fa-industry"></td>
                            <td class="px-2 py-2"><input type="number" name="rows[{{ $i }}][urutan]" value="{{ $t->urutan }}" required min="0" max="9999" class="w-20 rounded-lg border-slate-200 bg-slate-50 px-2 py-2 text-xs text-right"></td>
                            <td class="px-2 py-2 text-center">
                                <input type="hidden" name="rows[{{ $i }}][aktif]" value="0">
                                <input type="checkbox" name="rows[{{ $i }}][aktif]" value="1" @checked($t->aktif) class="h-4 w-4 accent-[var(--brand-accent-light)]" aria-label="Aktif">
                            </td>
                            <td class="px-2 py-2 text-right">
                                <input type="hidden" name="rows[{{ $i }}][id]" value="{{ $t->id }}">
                                <button type="button" class="text-slate-300 hover:text-rose-500 transition-colors" onclick="hapusBarisTopbar(this)" title="Hapus tab" aria-label="Hapus tab"><i class="fas fa-trash-can text-xs"></i></button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($tabs->isEmpty())
                <p class="text-xs text-slate-400 italic px-2 py-4">Belum ada baris — tambah tab pertama Anda.</p>
            @endif

            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                <p class="text-[11px] text-slate-400 max-w-md">Isi bawaan bila tabel masih kosong: Dashboard · Penjualan · Produksi · Master · Keuangan · Laporan · Jersey · Pengaturan.</p>
                <x-button type="submit"><i class="fas fa-floppy-disk text-xs"></i> Simpan Topbar</x-button>
            </div>
        </x-card>
    </form>
</div>

{{-- Template baris baru (pakai waktu klien, id mengikuti index urutan tambah) --}}
<template id="template-baris-topbar">
    <tr class="border-b border-slate-50 align-middle">
        <td class="px-2 py-2"><input type="text" name="rows[__I__][label]" required maxlength="40" class="w-36 rounded-lg border-slate-200 bg-slate-50 px-2 py-2 text-xs" placeholder="Label"></td>
        <td class="px-2 py-2"><input type="text" name="rows[__I__][url]" required maxlength="191" class="w-full rounded-lg border-slate-200 bg-slate-50 px-2 py-2 font-mono text-xs" placeholder="/produksi"></td>
        <td class="px-2 py-2"><input type="text" name="rows[__I__][icon]" required maxlength="64" class="w-40 rounded-lg border-slate-200 bg-slate-50 px-2 py-2 font-mono text-xs" placeholder="fa-industry"></td>
        <td class="px-2 py-2"><input type="number" name="rows[__I__][urutan]" value="100" required min="0" max="9999" class="w-20 rounded-lg border-slate-200 bg-slate-50 px-2 py-2 text-xs text-right"></td>
        <td class="px-2 py-2 text-center">
            <input type="hidden" name="rows[__I__][aktif]" value="0">
            <input type="checkbox" name="rows[__I__][aktif]" value="1" checked class="h-4 w-4 accent-[var(--brand-accent-light)]">
        </td>
        <td class="px-2 py-2 text-right">
            <button type="button" class="text-slate-300 hover:text-rose-500 transition-colors" onclick="hapusBarisTopbar(this)" title="Hapus tab"><i class="fas fa-trash-can text-xs"></i></button>
        </td>
    </tr>
</template>

@push('scripts')
<script>
let indeksBaris = {{ $tabs->count() }};

function tambahBarisTopbar() {
    const tpl = document.getElementById('template-baris-topbar').innerHTML.replaceAll('__I__', String(indeksBaris++));
    document.getElementById('baris-topbar').insertAdjacentHTML('beforeend', tpl);
}

function hapusBarisTopbar(btn) {
    const row = btn.closest('tr');
    if (row) row.remove();
}
</script>
@endpush
@endsection

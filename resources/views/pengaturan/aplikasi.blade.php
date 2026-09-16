@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-3xl mx-auto">

    <x-page-header title="Pengaturan Aplikasi" subtitle="Nama, logo, dan alamat perusahaan yang tampil di header & cetak nota/laporan." />

    @if (session('success'))
        <div class="bg-emerald-50 text-emerald-600 p-4 rounded-2xl mb-6 font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <x-card>
        <form action="{{ route('pengaturan-aplikasi.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="flex items-center gap-5">
                <div class="w-20 h-20 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center overflow-hidden shrink-0">
                    @if($setting->logo_path)
                        <img id="preview-logo" src="{{ asset('storage/' . $setting->logo_path) }}" alt="Logo" class="w-full h-full object-contain">
                    @else
                        <i id="preview-logo-placeholder" class="fas fa-image text-slate-300 text-2xl"></i>
                        <img id="preview-logo" src="" alt="Logo" class="w-full h-full object-contain hidden">
                    @endif
                </div>
                <x-form.group label="Logo Perusahaan" name="logo" help="PNG/JPG/WEBP, maks 2MB. Kosongkan kalau tidak ingin mengubah.">
                    <input type="file" name="logo" accept="image/png,image/jpeg,image/webp"
                        onchange="previewLogoAplikasi(this)"
                        class="w-full bg-slate-50 border-none rounded-xl py-2.5 px-4 text-xs font-semibold text-slate-600 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white">
                </x-form.group>
            </div>

            <x-form.group label="Nama Aplikasi" name="nama_aplikasi" required>
                <x-form.input type="text" name="nama_aplikasi" value="{{ old('nama_aplikasi', $setting->nama_aplikasi) }}" required />
            </x-form.group>

            <x-form.group label="Alamat" name="alamat">
                <textarea name="alamat" rows="3" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">{{ old('alamat', $setting->alamat) }}</textarea>
            </x-form.group>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form.group label="Telepon" name="telepon">
                    <x-form.input type="text" name="telepon" value="{{ old('telepon', $setting->telepon) }}" placeholder="08xx / 021xx" />
                </x-form.group>

                <x-form.group label="Fax" name="fax">
                    <x-form.input type="text" name="fax" value="{{ old('fax', $setting->fax) }}" placeholder="- (kosongkan kalau tidak ada)" />
                </x-form.group>

                <x-form.group label="Email" name="email">
                    <x-form.input type="email" name="email" value="{{ old('email', $setting->email) }}" placeholder="info@perusahaan.com" />
                </x-form.group>

                <x-form.group label="Website" name="website">
                    <x-form.input type="text" name="website" value="{{ old('website', $setting->website) }}" placeholder="http://perusahaan.com" />
                </x-form.group>
            </div>

            <div class="pt-4 flex justify-end">
                <x-button type="submit" class="!px-10">Simpan Pengaturan</x-button>
            </div>
        </form>
    </x-card>
</div>

@push('scripts')
<script>
    function previewLogoAplikasi(input) {
        if (!input.files || !input.files[0]) return;
        const img = document.getElementById('preview-logo');
        const placeholder = document.getElementById('preview-logo-placeholder');
        img.src = URL.createObjectURL(input.files[0]);
        img.classList.remove('hidden');
        if (placeholder) placeholder.classList.add('hidden');
    }
</script>
@endpush
@endsection

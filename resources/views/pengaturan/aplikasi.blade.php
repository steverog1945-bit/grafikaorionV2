@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-3xl mx-auto">

    <x-page-header title="Pengaturan Aplikasi" subtitle="Identitas perusahaan, format nota, dan aturan kas - semua pengaturan aplikasi dalam satu tempat." />

    @if (session('success'))
        <div class="bg-emerald-50 text-emerald-600 p-4 rounded-2xl mb-6 font-semibold">
            {{ session('success') }}
        </div>
    @endif

    {{-- TAB - satu form, satu tombol Simpan untuk semua tab (bukan submit
         terpisah per tab) supaya perubahan di beberapa tab sekaligus tidak
         perlu disimpan satu-satu. --}}
    <div class="flex gap-1 mb-5 border-b border-slate-200 overflow-x-auto">
        <button type="button" onclick="pindahTabPengaturan('umum')" id="tab-btn-umum" class="tab-btn-pengaturan px-5 py-3 text-xs font-bold uppercase tracking-widest border-b-2 border-indigo-600 text-indigo-600 whitespace-nowrap">
            <i class="fas fa-building mr-1.5"></i> Umum
        </button>
        <button type="button" onclick="pindahTabPengaturan('nota')" id="tab-btn-nota" class="tab-btn-pengaturan px-5 py-3 text-xs font-bold uppercase tracking-widest border-b-2 border-transparent text-slate-400 hover:text-slate-600 whitespace-nowrap">
            <i class="fas fa-receipt mr-1.5"></i> Nota
        </button>
        <button type="button" onclick="pindahTabPengaturan('kas')" id="tab-btn-kas" class="tab-btn-pengaturan px-5 py-3 text-xs font-bold uppercase tracking-widest border-b-2 border-transparent text-slate-400 hover:text-slate-600 whitespace-nowrap">
            <i class="fas fa-cash-register mr-1.5"></i> Kas
        </button>
    </div>

    <x-card>
        <form action="{{ route('pengaturan-aplikasi.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- TAB UMUM --}}
            <div id="tab-panel-umum" class="tab-panel-pengaturan space-y-6">
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
            </div>

            {{-- TAB NOTA --}}
            <div id="tab-panel-nota" class="tab-panel-pengaturan space-y-6 hidden">
                <x-form.group label="Format Cetak Nota" name="format_nota" help="Berlaku GLOBAL untuk semua nota - kasir tidak perlu pilih format tiap kali cetak.">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        @foreach(['thermal' => ['Thermal (58mm)', 'Struk kasir, kertas gulung'], 'a4' => ['A4 / A5', 'Invoice kertas biasa'], 'continuous' => ['Continuous Form', 'Kertas sambung/dot-matrix']] as $val => $label)
                        <label class="relative flex flex-col gap-1 p-4 rounded-xl border-2 cursor-pointer {{ old('format_nota', $setting->format_nota ?? 'a4') === $val ? 'border-indigo-500 bg-indigo-50' : 'border-slate-100' }}">
                            <input type="radio" name="format_nota" value="{{ $val }}" @checked(old('format_nota', $setting->format_nota ?? 'a4') === $val) onchange="tandaiFormatNota(this)" class="absolute top-3 right-3 accent-indigo-600">
                            <span class="text-sm font-bold text-slate-700">{{ $label[0] }}</span>
                            <span class="text-[11px] text-slate-400">{{ $label[1] }}</span>
                        </label>
                        @endforeach
                    </div>
                </x-form.group>

                <x-form.group label="Prefix Nomor Nota" name="nomor_nota_prefix" help="Contoh hasil: {{ old('nomor_nota_prefix', $setting->nomor_nota_prefix ?? 'INV') }}-20260918-001">
                    <x-form.input type="text" name="nomor_nota_prefix" value="{{ old('nomor_nota_prefix', $setting->nomor_nota_prefix ?? 'INV') }}" maxlength="20" placeholder="INV" />
                </x-form.group>

                <x-form.group label="Footer Nota" name="nota_footer_text" help="Ditampilkan di bagian bawah nota - rekening pembayaran, syarat & ketentuan, dst. Satu baris = satu keterangan. Kosongkan untuk memakai pesan default.">
                    <textarea name="nota_footer_text" rows="4" placeholder="Contoh:&#10;No. Rek: BCA 1234567890 a/n Nama Perusahaan&#10;1. Periksa barang sebelum meninggalkan toko.&#10;2. Barang yang tidak diambil lebih dari 30 hari bukan tanggung jawab kami." class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">{{ old('nota_footer_text', $setting->nota_footer_text) }}</textarea>
                </x-form.group>
            </div>

            {{-- TAB KAS --}}
            <div id="tab-panel-kas" class="tab-panel-pengaturan space-y-6 hidden">
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="libur_kas_minggu" value="1" @checked(old('libur_kas_minggu', $setting->libur_kas_minggu)) class="mt-1 w-4 h-4 rounded accent-indigo-600">
                        <span>
                            <span class="block text-sm font-bold text-slate-700">Kas Libur di Hari Minggu</span>
                            <span class="block text-xs text-slate-400 mt-0.5">Kalau diaktifkan, Open Saldo & Closing Kas tidak bisa dilakukan setiap hari Minggu (toko dianggap tutup).</span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="pt-4 flex justify-end border-t border-slate-100">
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

    function pindahTabPengaturan(nama) {
        document.querySelectorAll('.tab-panel-pengaturan').forEach(el => {
            el.classList.toggle('hidden', el.id !== `tab-panel-${nama}`);
        });
        document.querySelectorAll('.tab-btn-pengaturan').forEach(btn => {
            const aktif = btn.id === `tab-btn-${nama}`;
            btn.classList.toggle('border-indigo-600', aktif);
            btn.classList.toggle('text-indigo-600', aktif);
            btn.classList.toggle('border-transparent', !aktif);
            btn.classList.toggle('text-slate-400', !aktif);
        });
    }

    function tandaiFormatNota(radio) {
        radio.closest('.grid').querySelectorAll('label').forEach(l => {
            l.classList.remove('border-indigo-500', 'bg-indigo-50');
            l.classList.add('border-slate-100');
        });
        const label = radio.closest('label');
        label.classList.remove('border-slate-100');
        label.classList.add('border-indigo-500', 'bg-indigo-50');
    }
</script>
@endpush
@endsection

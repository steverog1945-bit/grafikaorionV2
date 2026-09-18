@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-5xl mx-auto">

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

    <form action="{{ route('pengaturan-aplikasi.update') }}" method="POST" enctype="multipart/form-data" id="form-pengaturan-aplikasi">
        @csrf

        {{-- TAB UMUM --}}
        <div id="tab-panel-umum" class="tab-panel-pengaturan">
            <x-card class="space-y-6">
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

                <div class="pt-4 flex justify-end border-t border-slate-100">
                    <x-button type="submit" class="!px-10">Simpan Pengaturan</x-button>
                </div>
            </x-card>
        </div>

        {{-- TAB NOTA - 2 kolom: form di kiri, preview LIVE di kanan (sticky)
             supaya admin langsung lihat dampak visual dari format/footer yang
             diubah, tidak perlu cetak nota sungguhan cuma untuk mengecek. --}}
        <div id="tab-panel-nota" class="tab-panel-pengaturan hidden">
            <div class="flex flex-col lg:flex-row gap-5 items-start">
                <div class="w-full lg:w-[58%] space-y-5">
                    <x-card class="space-y-6">
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

                        <x-form.group label="Footer Nota" help="Ditampilkan di bagian bawah nota - rekening pembayaran, syarat & ketentuan, dst. Kosongkan untuk memakai pesan default.">
                            {{-- Editor ringan (contenteditable + execCommand) - bukan
                                 library WYSIWYG pihak ketiga, cukup untuk kebutuhan
                                 tebal/miring/garis bawah/daftar. HTML yang tersimpan
                                 disaring ketat di server (whitelist tag, SEMUA atribut
                                 dibuang - lihat SettingAplikasiController::sanitizeFooterHtml())
                                 sebelum dipakai lagi sebagai HTML mentah di nota. --}}
                            <div class="rounded-xl border border-slate-200 overflow-hidden bg-white">
                                <div class="flex items-center gap-0.5 border-b border-slate-100 bg-slate-50 px-2 py-1.5">
                                    <button type="button" onmousedown="event.preventDefault()" onclick="formatFooterNota('bold')" class="w-7 h-7 rounded hover:bg-slate-200 text-slate-600 text-xs" title="Tebal"><i class="fas fa-bold"></i></button>
                                    <button type="button" onmousedown="event.preventDefault()" onclick="formatFooterNota('italic')" class="w-7 h-7 rounded hover:bg-slate-200 text-slate-600 text-xs" title="Miring"><i class="fas fa-italic"></i></button>
                                    <button type="button" onmousedown="event.preventDefault()" onclick="formatFooterNota('underline')" class="w-7 h-7 rounded hover:bg-slate-200 text-slate-600 text-xs" title="Garis Bawah"><i class="fas fa-underline"></i></button>
                                    <span class="w-px h-5 bg-slate-200 mx-1"></span>
                                    <button type="button" onmousedown="event.preventDefault()" onclick="formatFooterNota('insertUnorderedList')" class="w-7 h-7 rounded hover:bg-slate-200 text-slate-600 text-xs" title="Daftar Berpoin"><i class="fas fa-list-ul"></i></button>
                                    <button type="button" onmousedown="event.preventDefault()" onclick="formatFooterNota('insertOrderedList')" class="w-7 h-7 rounded hover:bg-slate-200 text-slate-600 text-xs" title="Daftar Bernomor"><i class="fas fa-list-ol"></i></button>
                                    <span class="w-px h-5 bg-slate-200 mx-1"></span>
                                    <button type="button" onmousedown="event.preventDefault()" onclick="formatFooterNota('removeFormat')" class="w-7 h-7 rounded hover:bg-slate-200 text-slate-600 text-xs" title="Hapus Format"><i class="fas fa-eraser"></i></button>
                                </div>
                                <div id="editor-footer-nota" contenteditable="true" oninput="syncFooterNotaHidden()" class="min-h-[120px] p-3 text-sm text-slate-700 focus:outline-none" style="white-space: pre-wrap;">{!! old('nota_footer_text', $setting->nota_footer_text) !!}</div>
                            </div>
                            <input type="hidden" name="nota_footer_text" id="input-footer-nota-hidden" value="{{ old('nota_footer_text', $setting->nota_footer_text) }}">
                            @error('nota_footer_text')
                                <p class="mt-1.5 ml-2 text-xs font-semibold text-rose-500">{{ $message }}</p>
                            @enderror
                        </x-form.group>

                        <div class="pt-4 flex justify-end border-t border-slate-100">
                            <x-button type="submit" class="!px-10">Simpan Pengaturan</x-button>
                        </div>
                    </x-card>
                </div>

                {{-- PREVIEW LIVE --}}
                <div class="w-full lg:w-[42%] lg:sticky lg:top-6">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 text-center">Preview Nota</p>
                    <div class="bg-slate-100 rounded-2xl p-6 flex justify-center">
                        <div id="preview-nota-sheet" class="bg-white shadow-md p-4 text-[11.5px]" style="max-width: 320px; width: 100%;">
                            <div class="text-center mb-2.5">
                                <img id="preview-nota-logo" class="hidden mx-auto mb-1 object-contain" style="max-height: 36px;" alt="Logo">
                                <h1 id="preview-nota-nama" class="font-extrabold text-slate-900" style="font-size: 1.15em;"></h1>
                                <p id="preview-nota-alamat" class="text-slate-500 mt-0.5" style="font-size: 0.85em;"></p>
                                <p class="text-indigo-600 font-bold uppercase mt-1" style="font-size: 0.8em; letter-spacing: 0.03em;">Nota Penjualan</p>
                            </div>
                            <table class="w-full">
                                <tr><td class="py-0.5">No. Nota</td><td class="py-0.5 text-right" id="preview-nota-nomor"></td></tr>
                                <tr><td class="py-0.5">Tanggal</td><td class="py-0.5 text-right">{{ now()->format('d/m/Y H:i') }}</td></tr>
                                <tr><td class="py-0.5">Konsumen</td><td class="py-0.5 text-right">Budi Santoso</td></tr>
                            </table>
                            <div class="border-t border-dashed border-slate-300 my-2"></div>
                            <table class="w-full">
                                <thead>
                                    <tr class="text-slate-400 uppercase" style="font-size: 0.8em;">
                                        <th class="text-left font-bold pb-1">Item</th>
                                        <th class="text-right font-bold pb-1">Qty</th>
                                        <th class="text-right font-bold pb-1">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td class="py-0.5">Banner 3x1m</td><td class="py-0.5 text-right">2</td><td class="py-0.5 text-right">150.000</td></tr>
                                    <tr><td class="py-0.5">Stiker A3</td><td class="py-0.5 text-right">5</td><td class="py-0.5 text-right">50.000</td></tr>
                                </tbody>
                            </table>
                            <div class="border-t border-dashed border-slate-300 my-2"></div>
                            <table class="w-full">
                                <tr class="font-extrabold text-slate-900"><td class="py-0.5">TOTAL</td><td class="py-0.5 text-right" colspan="2">Rp 200.000</td></tr>
                                <tr><td class="py-0.5">Status</td><td class="py-0.5 text-right" colspan="2">Belum Bayar</td></tr>
                            </table>
                            <div class="border-t border-dashed border-slate-300 my-2"></div>
                            <div id="preview-nota-footer" class="text-slate-400 text-center" style="font-size: 0.83em;"></div>
                        </div>
                    </div>
                    <p class="text-[10px] text-slate-400 text-center mt-2">Ilustrasi - proporsi lebar kertas sungguhan mengikuti format yang dipilih.</p>
                </div>
            </div>
        </div>

        {{-- TAB KAS --}}
        <div id="tab-panel-kas" class="tab-panel-pengaturan hidden">
            <x-card class="space-y-6">
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="libur_kas_minggu" value="1" @checked(old('libur_kas_minggu', $setting->libur_kas_minggu)) class="mt-1 w-4 h-4 rounded accent-indigo-600">
                        <span>
                            <span class="block text-sm font-bold text-slate-700">Kas Libur di Hari Minggu</span>
                            <span class="block text-xs text-slate-400 mt-0.5">Kalau diaktifkan, Open Saldo & Closing Kas tidak bisa dilakukan setiap hari Minggu (toko dianggap tutup).</span>
                        </span>
                    </label>
                </div>

                <div class="pt-4 flex justify-end border-t border-slate-100">
                    <x-button type="submit" class="!px-10">Simpan Pengaturan</x-button>
                </div>
            </x-card>
        </div>
    </form>
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
        perbaruiPreviewNota();
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
        if (nama === 'nota') perbaruiPreviewNota();
    }

    function tandaiFormatNota(radio) {
        radio.closest('.grid').querySelectorAll('label').forEach(l => {
            l.classList.remove('border-indigo-500', 'bg-indigo-50');
            l.classList.add('border-slate-100');
        });
        const label = radio.closest('label');
        label.classList.remove('border-slate-100');
        label.classList.add('border-indigo-500', 'bg-indigo-50');
        perbaruiPreviewNota();
    }

    // Toolbar formatting pakai document.execCommand - API lama/deprecated tapi
    // masih berfungsi luas di browser berbasis Chromium untuk perintah dasar
    // (bold/italic/underline/list) - cukup untuk kebutuhan footer nota tanpa
    // perlu menambah library WYSIWYG pihak ketiga. HTML hasilnya DISARING
    // KETAT di server sebelum disimpan (lihat SettingAplikasiController).
    function formatFooterNota(command) {
        document.getElementById('editor-footer-nota').focus();
        document.execCommand(command, false, null);
        syncFooterNotaHidden();
    }

    function syncFooterNotaHidden() {
        const editor = document.getElementById('editor-footer-nota');
        document.getElementById('input-footer-nota-hidden').value = editor.innerHTML;
        perbaruiPreviewNota();
    }

    function perbaruiPreviewNota() {
        const namaApl = document.querySelector('input[name="nama_aplikasi"]')?.value || 'Nama Perusahaan';
        const alamat = document.querySelector('textarea[name="alamat"]')?.value || '';
        const telepon = document.querySelector('input[name="telepon"]')?.value || '';
        const prefix = document.querySelector('input[name="nomor_nota_prefix"]')?.value || 'INV';
        const format = document.querySelector('input[name="format_nota"]:checked')?.value || 'a4';
        const footerHtml = document.getElementById('editor-footer-nota')?.innerHTML.trim();

        document.getElementById('preview-nota-nama').textContent = namaApl;
        document.getElementById('preview-nota-alamat').textContent = [alamat, telepon].filter(Boolean).join(' • ');

        const hariIni = new Date().toISOString().slice(0, 10).replace(/-/g, '');
        document.getElementById('preview-nota-nomor').textContent = `${prefix}-${hariIni}-001`;

        const footerEl = document.getElementById('preview-nota-footer');
        footerEl.innerHTML = footerHtml || 'Terima kasih atas kepercayaan Anda';

        const sheet = document.getElementById('preview-nota-sheet');
        const lebar = format === 'thermal' ? '200px' : (format === 'continuous' ? '400px' : '320px');
        sheet.style.maxWidth = lebar;

        const logoAsli = document.getElementById('preview-logo');
        const logoPreview = document.getElementById('preview-nota-logo');
        if (logoAsli && !logoAsli.classList.contains('hidden') && logoAsli.getAttribute('src')) {
            logoPreview.src = logoAsli.src;
            logoPreview.classList.remove('hidden');
        } else {
            logoPreview.classList.add('hidden');
        }
    }

    document.getElementById('form-pengaturan-aplikasi').addEventListener('input', perbaruiPreviewNota);
    document.addEventListener('DOMContentLoaded', () => {
        syncFooterNotaHidden();
        perbaruiPreviewNota();
    });
</script>
@endpush
@endsection

@extends('layouts.app')

@section('content')
@include('partials.rupiah-format-script')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

<div class="mb-8 flex justify-between items-end">
    <div>
        <h2 class="text-2xl font-medium text-slate-800 tracking-tight uppercase">Kelola Kas Harian</h2>
        <p class="text-slate-500 text-sm italic font-medium">Manajemen saldo awal dan status operasional kasir.</p>
    </div>

    {{-- KONDISI TOMBOL BERDASARKAN STATUS KAS --}}
    @if(!$kasAktif)
        <x-button onclick="aksiKas('open')">
            <i class="fas fa-wallet"></i>
            Buka Saldo Kasir
        </x-button>
    @else
        <div class="flex gap-3">
            <div class="bg-emerald-50 border border-emerald-100 px-4 py-2 rounded-2xl flex items-center gap-3">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
                <span class="text-[10px] font-medium text-emerald-600 uppercase">Kasir Aktif</span>
            </div>
            {{-- Kas Tidak Terduga - mutasi tunai yang tidak terikat nota
                 (pengeluaran operasional dadakan, kas masuk di luar
                 penjualan). Mengikuti referensi "Alur Kas Kasir": Waktu
                 (otomatis = sekarang, bukan diketik manual), Keterangan,
                 Status Debet/Kredit, Nominal. --}}
            <x-button variant="warning" onclick="openModal('kas-manual')">
                <i class="fas fa-exclamation-circle"></i>
                Kas Tidak Terduga
            </x-button>

            <x-modal id="kas-manual" title="Alur Kas Kasir" maxWidth="sm">
                <form action="{{ route('kasir.kas-manual.store') }}" method="POST" class="space-y-5">
                    @csrf
                    <x-form.group label="Waktu">
                        <x-form.input type="text" value="{{ now()->format('d-m-Y H:i:s') }}" disabled class="!bg-slate-100 !text-slate-400" />
                    </x-form.group>

                    <x-form.group label="Keterangan" required>
                        <textarea name="keterangan" rows="3" required placeholder="Contoh: Beli perlengkapan kebersihan, sumbangan kas dari pemilik, dll"
                            class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500/20 shadow-inner"></textarea>
                    </x-form.group>

                    <x-form.group label="Status" required>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="relative flex items-center gap-2 py-3 px-4 rounded-xl border-2 border-emerald-500 bg-emerald-50 text-emerald-700 font-medium text-xs cursor-pointer transition-all status-manual-option">
                                <input type="radio" name="status" value="Masuk" class="accent-emerald-600" checked onchange="toggleStatusManual(this)">
                                Debet / Kas Masuk
                            </label>
                            <label class="relative flex items-center gap-2 py-3 px-4 rounded-xl border-2 border-slate-100 text-slate-400 font-medium text-xs cursor-pointer transition-all status-manual-option">
                                <input type="radio" name="status" value="Keluar" class="accent-rose-600" onchange="toggleStatusManual(this)">
                                Kredit / Kas Keluar
                            </label>
                        </div>
                    </x-form.group>

                    <x-form.group label="Nominal / Jumlah" required>
                        <x-form.input type="text" inputmode="numeric" name="nominal" required placeholder="0" class="text-xl font-medium format-ribuan" />
                    </x-form.group>

                    <div class="pt-2 flex gap-3">
                        <x-button type="button" variant="secondary" onclick="closeModal('kas-manual')" class="flex-1">Batal</x-button>
                        <x-button type="submit" class="flex-1">Simpan</x-button>
                    </div>
                </form>
            </x-modal>

            {{-- Closing SELALU lewat laporan penuh (closing-preview), bukan
                 lagi modal singkat yang langsung mengunci begitu disubmit -
                 operator perlu melihat rekap satu hari penuh (rekap
                 transaksi per mesin, rekap pembayaran, selisih laci) dulu
                 sebelum menutup kas. --}}
            <x-button variant="danger" href="{{ route('kasir.preview-closing') }}">
                <i class="fas fa-lock"></i>
                Closing Kas Sekarang
            </x-button>
        </div>
    @endif
</div>

@if($kasAktif)
    @php
        $mulaiTampil = \App\Services\KasSesiService::mulai($kasAktif);
        $umurJam = \App\Services\KasSesiService::umurJam($kasAktif);
        $pemilikSesi = DB::table('users')->leftJoin('karyawan', 'users.id_karyawan', '=', 'karyawan.id_karyawan')->where('users.id_user', $kasAktif->id_user)->value(DB::raw('COALESCE(karyawan.nama_karyawan, users.username)'));
        $saya = (int) $kasAktif->id_user === (int) auth()->id();
    @endphp
    <div class="mb-6 flex flex-wrap items-center gap-3 rounded-2xl px-5 py-4 border-2 {{ $kasBasi ? 'bg-amber-50 border-amber-200' : 'bg-slate-50 border-slate-100' }}">
        <i class="fas {{ $kasBasi ? 'fa-triangle-exclamation text-amber-500' : 'fa-clock text-slate-400' }} text-lg"></i>
        <div class="text-sm font-medium {{ $kasBasi ? 'text-amber-800' : 'text-slate-600' }}">
            Sesi kas dibuka <b>{{ $mulaiTampil->translatedFormat('d F Y, H:i') }}</b> oleh <b>{{ $pemilikSesi }}</b> ({{ $umurJam }} jam).
            @if($kasBasi)
                Sesi ini sudah lebih dari 24 jam dan <b>belum di-closing</b> - lakukan closing supaya uang laci direkonsiliasi
                @if(!$saya) (Administrator dapat menutup paksa dengan alasan)@endif.
            @else
                Kas tetap berjalan sampai closing dilakukan, walau melewati pergantian tanggal.
            @endif
        </div>
    </div>
@endif

{{-- SALDO ROLLING - kas kemarin (atau sesi terakhir sebelum hari ini)
     ditutup dengan status "Rolling", saldonya jadi saran saldo awal hari
     ini. Ditampilkan HANYA saat kasir belum dibuka - begitu dibuka, saran
     ini tidak relevan lagi. --}}
@if(!$kasAktif && $saldoRolling !== null)
<div class="mb-6 flex items-center gap-3 bg-indigo-50 border-2 border-indigo-200 rounded-2xl px-5 py-4">
    <div class="w-10 h-10 rounded-xl bg-indigo-500 text-white flex items-center justify-center shrink-0 text-lg">
        <i class="fas fa-arrows-rotate"></i>
    </div>
    <div class="flex-1">
        <p class="text-sm font-medium text-indigo-800 uppercase tracking-wide">Saldo Di-rolling dari Sesi Sebelumnya</p>
        <p class="text-xs text-indigo-600 mt-0.5">Sesi kas terakhir ditutup dengan saldo Rp {{ number_format($saldoRolling, 0, ',', '.') }} dan status "Rolling" - uang ini tetap di laci, dipakai sebagai saran saldo awal hari ini (tetap bisa diubah manual saat membuka kasir).</p>
    </div>
</div>
@endif

<div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
        <div>
            <h3 class="text-lg font-medium text-slate-800 uppercase tracking-tight">Rincian Mutasi Kas Hari Ini</h3>
            <p class="text-xs text-slate-500 italic">Daftar masuknya uang berdasarkan nota dan metode pembayaran</p>
        </div>
        <div class="flex gap-4">
            {{-- Badge Ringkasan Cepat --}}
            <div class="px-4 py-2 bg-emerald-50 rounded-xl border border-emerald-100 text-right">
                <span class="block text-[9px] font-medium text-emerald-600 uppercase">Total Tunai</span>
                <span class="font-medium text-emerald-700">Rp {{ number_format($data_kas->sum('nominal_tunai'), 0, ',', '.') }}</span>
            </div>
            <div class="px-4 py-2 bg-sky-50 rounded-xl border border-sky-100 text-right">
                <span class="block text-[9px] font-medium text-sky-600 uppercase">Total Debit</span>
                <span class="font-medium text-sky-700">Rp {{ number_format($data_kas->sum('nominal_debit'), 0, ',', '.') }}</span>
            </div>
            <div class="px-4 py-2 bg-blue-50 rounded-xl border border-blue-100 text-right">
                <span class="block text-[9px] font-medium text-blue-600 uppercase">Total Transfer</span>
                <span class="font-medium text-blue-700">Rp {{ number_format($data_kas->sum('nominal_transfer'), 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <x-table bare>
        <thead>
            <tr>
                <th class="p-4 text-center w-10">#</th>
                <th class="p-4">Waktu</th>
                <th class="p-4">Sumber / Konsumen</th>
                <th class="p-4">No. Nota</th>
                <th class="p-4 text-center">Metode</th>
                <th class="p-4 text-right">Nominal Masuk</th>
                <th class="p-4 text-center">Status Nota</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($mutasi as $i => $m)
            <tr class="hover:bg-slate-50/50 transition-all">
                <td class="p-4 text-center text-[11px] text-slate-400">{{ $i + 1 }}</td>
                <td class="p-4 text-xs text-slate-500">{{ date('H:i', strtotime($m->tgl_bayar)) }}</td>
                <td class="p-4">
                    <span class="block font-medium text-slate-700 uppercase text-sm">{{ $m->nama_konsumen }}</span>
                    <span class="text-[10px] text-slate-400 italic">{{ $m->keterangan }}</span>
                </td>
                <td class="p-4">
                    <span class="px-3 py-1 bg-slate-100 rounded-lg text-xs font-medium text-slate-600">#{{ $m->nomor_nota }}</span>
                </td>
                {{-- Sebelumnya cuma dibedakan Tunai vs "selain Tunai" (dianggap
                     selalu Transfer) - Debit & Deposit ikut kepencet label
                     TRANSFER yang salah. Sekarang eksplisit 4 metode. --}}
                <td class="p-4 text-center">
                    @if($m->metode == 'Tunai')
                        <span class="px-2 py-1 bg-amber-100 text-amber-700 rounded-lg text-[9px] font-medium"><i class="fas fa-money-bill-wave mr-1"></i>TUNAI</span>
                    @elseif($m->metode == 'Debit')
                        <span class="px-2 py-1 bg-sky-100 text-sky-700 rounded-lg text-[9px] font-medium"><i class="fas fa-credit-card mr-1"></i>DEBIT</span>
                    @elseif($m->metode == 'Deposit')
                        <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-lg text-[9px] font-medium"><i class="fas fa-wallet mr-1"></i>DEPOSIT</span>
                    @else
                        <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-lg text-[9px] font-medium"><i class="fas fa-university mr-1"></i>TRANSFER</span>
                    @endif
                </td>
                <td class="p-4 text-right">
                    @php
                        $warnaNominal = match($m->metode) {
                            'Tunai' => 'text-emerald-600',
                            'Debit' => 'text-sky-600',
                            'Deposit' => 'text-purple-600',
                            default => 'text-blue-600',
                        };
                    @endphp
                    <span class="font-medium {{ $warnaNominal }}">
                        Rp {{ number_format($m->nominal, 0, ',', '.') }}
                    </span>
                </td>
                <td class="p-4 text-center">
                    <span class="text-[10px] font-medium uppercase {{ $m->status_pembayaran == 'Lunas' ? 'text-emerald-500' : 'text-amber-500' }}">
                        {{ $m->status_pembayaran }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </x-table>
</div>

{{-- KAS TIDAK TERDUGA - mutasi tunai yang tidak terikat nota, terpisah
     dari tabel mutasi nota di atas supaya kolom Nota/Konsumen/Metode yang
     tidak relevan tidak perlu dipaksakan ada isinya. --}}
@if($mutasiManual->isNotEmpty())
<div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden mt-6">
    <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
        <div>
            <h3 class="text-lg font-medium text-slate-800 uppercase tracking-tight">Kas Tidak Terduga Hari Ini</h3>
            <p class="text-xs text-slate-500 italic">Mutasi tunai di luar nota (pengeluaran dadakan, kas masuk lain-lain)</p>
        </div>
        <div class="flex gap-4">
            <div class="px-4 py-2 bg-emerald-50 rounded-xl border border-emerald-100 text-right">
                <span class="block text-[9px] font-medium text-emerald-600 uppercase">Masuk</span>
                <span class="font-medium text-emerald-700">Rp {{ number_format($data_kas->sum('nominal_manual_masuk'), 0, ',', '.') }}</span>
            </div>
            <div class="px-4 py-2 bg-rose-50 rounded-xl border border-rose-100 text-right">
                <span class="block text-[9px] font-medium text-rose-600 uppercase">Keluar</span>
                <span class="font-medium text-rose-700">Rp {{ number_format($data_kas->sum('nominal_manual_keluar'), 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <x-table bare>
        <thead>
            <tr>
                <th class="p-4 text-center w-10">#</th>
                <th class="p-4">Waktu</th>
                <th class="p-4">Keterangan</th>
                <th class="p-4 text-center">Status</th>
                <th class="p-4 text-right">Nominal</th>
                <th class="p-4 text-center">Dicatat Oleh</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($mutasiManual as $i => $m)
            <tr class="hover:bg-slate-50/50 transition-all">
                <td class="p-4 text-center text-[11px] text-slate-400">{{ $i + 1 }}</td>
                <td class="p-4 text-xs text-slate-500">{{ \Carbon\Carbon::parse($m->waktu)->format('H:i') }}</td>
                <td class="p-4 text-sm text-slate-600">{{ $m->keterangan }}</td>
                <td class="p-4 text-center">
                    @if($m->status === 'Masuk')
                        <span class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded-lg text-[9px] font-medium">DEBET / MASUK</span>
                    @else
                        <span class="px-2 py-1 bg-rose-100 text-rose-700 rounded-lg text-[9px] font-medium">KREDIT / KELUAR</span>
                    @endif
                </td>
                <td class="p-4 text-right font-medium {{ $m->status === 'Masuk' ? 'text-emerald-600' : 'text-rose-600' }}">
                    {{ $m->status === 'Masuk' ? '+' : '-' }} Rp {{ number_format($m->nominal, 0, ',', '.') }}
                </td>
                <td class="p-4 text-center text-[11px] text-slate-400">{{ $m->dicatat_oleh ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </x-table>
</div>
@endif

</div>

<script>
function toggleStatusManual(input) {
    document.querySelectorAll('.status-manual-option').forEach(label => {
        const isMasuk = label.querySelector('input').value === 'Masuk';
        const isChecked = label.querySelector('input').checked;
        label.classList.toggle('border-emerald-500', isChecked && isMasuk);
        label.classList.toggle('bg-emerald-50', isChecked && isMasuk);
        label.classList.toggle('text-emerald-700', isChecked && isMasuk);
        label.classList.toggle('border-rose-500', isChecked && !isMasuk);
        label.classList.toggle('bg-rose-50', isChecked && !isMasuk);
        label.classList.toggle('text-rose-700', isChecked && !isMasuk);
        label.classList.toggle('border-slate-100', !isChecked);
        label.classList.toggle('text-slate-400', !isChecked);
    });
}

function aksiKas(tipe) {
    if(tipe === 'open') {
        Swal.fire({
            title: 'Buka Saldo Awal',
            text: {!! $saldoRolling !== null ? "'Saldo di-rolling dari sesi sebelumnya sudah terisi otomatis - ubah kalau berbeda dari laci fisik sekarang:'" : "'Masukkan jumlah modal awal yang ada di laci kasir sekarang:'" !!},
            input: 'number',
            inputValue: {{ $saldoRolling !== null ? (int) $saldoRolling : 0 }},
            inputAttributes: { min: 0 },
            showCancelButton: true,
            confirmButtonText: 'Buka Kas',
            confirmButtonColor: '#4f46e5',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-[2rem]' }
        }).then((result) => {
            if (result.isConfirmed) {
                if(result.value >= 0) {
                    // P0-1: kirim via POST + CSRF (sebelumnya GET query string
                    // tanpa verifikasi CSRF - celah CSRF nyata).
                    const f = document.getElementById('form-open-kas');
                    f.querySelector('input[name=jumlah]').value = result.value;
                    f.submit();
                } else {
                    Swal.fire('Error', 'Jumlah tidak valid', 'error');
                }
            }
        });
    }
}
</script>

{{-- P0-1: form POST + CSRF untuk Buka Kas (dipakai JS aksiKas('open') di atas) --}}
<form id="form-open-kas" action="{{ route('kasir.store-kas') }}" method="POST" class="hidden">@csrf<input type="hidden" name="jumlah" value="0"></form>
@endsection
@extends('layouts.app')

@section('content')
@include('partials.rupiah-format-script')
<style>
    @media print {
        aside, .no-print { display: none !important; }
        main { margin-left: 0 !important; padding: 28px 32px !important; }
        body { background: #fff !important; }
    }
</style>
@include('partials.print-report-header', ['docTitle' => 'Kas Kecil'])

<div class="w-full px-4 sm:px-6 lg:px-8 py-10 print:pt-0 max-w-[1600px] mx-auto">

    <div class="print:hidden">
        <x-page-header title="Kas Kecil">
            <x-slot:subtitle>
                Pencatatan belanja kebutuhan non-bahan-utama bernilai kecil (ATK, konsumsi, ongkos kirim, dst) - terpisah dari Kas Utama, Belanja Bahan, &amp; Keuangan Jersey
            </x-slot:subtitle>
            <x-slot:actions>
                <x-button type="button" onclick="window.print()" variant="secondary" class="no-print">
                    <i class="fas fa-print"></i>
                    Cetak
                </x-button>
            </x-slot:actions>
        </x-page-header>
    </div>

    @if (session('success'))
        <div class="bg-emerald-50 text-emerald-700 p-4 rounded-2xl mb-6 font-semibold no-print">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="bg-rose-50 text-rose-600 p-4 rounded-2xl mb-6 font-semibold no-print">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- FORM TAMBAH --}}
    <x-card class="mb-6 no-print">
        <form method="POST" action="{{ route('kas-kecil.store') }}" id="form-kas-kecil">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <x-form.group label="Tanggal" required>
                    <x-form.input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required />
                </x-form.group>
                <x-form.group label="Kategori" required>
                    <input type="text" name="kategori" list="kategori-kas-kecil" value="{{ old('kategori') }}" required placeholder="mis. ATK, Konsumsi, Transport" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner">
                    <datalist id="kategori-kas-kecil">
                        @foreach($kategoriList as $kat)
                            <option value="{{ $kat }}">
                        @endforeach
                    </datalist>
                </x-form.group>
                <x-form.group label="Nominal (Rp)" required>
                    <x-form.input type="text" inputmode="numeric" name="nominal" value="{{ old('nominal') }}" required placeholder="0" class="format-ribuan" />
                </x-form.group>
                <div class="flex items-end">
                    <x-button type="submit" class="w-full">
                        <i class="fas fa-plus"></i> Catat Pengeluaran
                    </x-button>
                </div>
            </div>
            <x-form.group label="Keterangan" class="mt-4" required>
                <x-form.input type="text" name="keterangan" value="{{ old('keterangan') }}" required placeholder="mis. Beli tinta printer & kertas HVS untuk admin" />
            </x-form.group>
        </form>
    </x-card>

    {{-- FILTER --}}
    <x-card class="mb-6 no-print">
        <form method="GET" action="{{ route('kas-kecil.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="w-full md:w-44">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Dari Tanggal</label>
                <x-form.input type="date" name="tgl_dari" value="{{ $tglDari }}" />
            </div>
            <div class="w-full md:w-44">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Sampai Tanggal</label>
                <x-form.input type="date" name="tgl_sampai" value="{{ $tglSampai }}" />
            </div>
            <div class="w-full md:w-48">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Kategori</label>
                <x-form.select name="kategori">
                    <option value="">-- Semua --</option>
                    @foreach($kategoriList as $kat)
                        <option value="{{ $kat }}" {{ request('kategori') === $kat ? 'selected' : '' }}>{{ $kat }}</option>
                    @endforeach
                </x-form.select>
            </div>

            <x-button type="submit">
                <i class="fas fa-filter"></i>
                Filter
            </x-button>

            @if(request()->anyFilled(['tgl_dari', 'tgl_sampai', 'kategori']))
                <a href="{{ route('kas-kecil.index') }}" class="text-[11px] font-bold text-rose-500 uppercase tracking-widest hover:underline ml-2">
                    Reset
                </a>
            @endif
        </form>
    </x-card>

    {{-- RESUME - kartu berwarna di layar (tidak diubah), tabel standar
         polos khusus cetak. --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 print:hidden">
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Jumlah Catatan</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($data->total(), 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Pengeluaran (sesuai filter)</p>
            <h3 class="text-2xl font-bold text-rose-500 mt-1">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</h3>
        </div>
    </div>
    <div class="hidden print:block">
        <x-laporan.resume-table title="Resume" :rows="[
            'Jumlah Catatan' => number_format($data->total(), 0, ',', '.'),
            'Total Pengeluaran (sesuai filter)' => 'Rp ' . number_format($totalPengeluaran, 0, ',', '.'),
        ]" />
    </div>

    <div class="print:hidden">
    <x-table>
        <thead>
            <tr>
                <th class="px-6 py-5 text-left">Tanggal</th>
                <th class="px-4 py-5 text-left">Kategori</th>
                <th class="px-4 py-5 text-left">Keterangan</th>
                <th class="px-4 py-5 text-left">Dicatat Oleh</th>
                <th class="px-4 py-5 text-right">Nominal</th>
                <th class="px-4 py-5 text-center w-20 no-print">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($data as $k)
            <tr class="hover:bg-slate-50/50 transition-all">
                <td class="px-6 py-5">
                    <span class="text-[13px] font-bold text-slate-700">{{ \Carbon\Carbon::parse($k->tanggal)->format('d M Y') }}</span>
                </td>
                <td class="px-4 py-5">
                    <span class="text-[11px] font-bold uppercase text-indigo-600 bg-indigo-50 rounded px-2 py-1">{{ $k->kategori }}</span>
                </td>
                <td class="px-4 py-5">
                    <span class="text-[12px] text-slate-600">{{ $k->keterangan }}</span>
                </td>
                <td class="px-4 py-5">
                    <span class="text-[12px] text-slate-400">{{ $k->user->username ?? '-' }}</span>
                </td>
                <td class="px-4 py-5 text-right">
                    <span class="text-[12px] text-rose-500 font-bold">Rp {{ number_format($k->nominal, 0, ',', '.') }}</span>
                </td>
                <td class="px-4 py-5 text-center no-print">
                    <form method="POST" action="{{ route('kas-kecil.destroy', $k->id) }}" onsubmit="return konfirmasiHapusKasKecil(event, this)">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-8 h-8 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-rose-50" title="Hapus"><i class="far fa-trash-alt text-xs"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6">
                    <x-empty-state title="Belum ada catatan kas kecil" description="Sesuaikan filter tanggal, atau catat pengeluaran baru lewat form di atas." />
                </td>
            </tr>
            @endforelse
        </tbody>
    </x-table>
    </div>

    {{-- Versi cetak: tabel polos sederhana, gaya visual sama dengan
         laporan lain (font 10/11px konsisten, tanpa padding lega UI). --}}
    <div class="hidden print:block">
        <x-laporan.print-table title="Detail Kas Kecil" :headers="['Tanggal', 'Kategori', 'Keterangan', 'Dicatat Oleh', 'Nominal']" :align="['left', 'left', 'left', 'left', 'right']">
            @forelse($data as $k)
            <tr>
                <td class="px-3 py-1.5 font-semibold text-slate-700">{{ \Carbon\Carbon::parse($k->tanggal)->format('d/m/Y') }}</td>
                <td class="px-3 py-1.5 text-slate-600">{{ $k->kategori }}</td>
                <td class="px-3 py-1.5 text-slate-600">{{ $k->keterangan }}</td>
                <td class="px-3 py-1.5 text-slate-600">{{ $k->user->username ?? '-' }}</td>
                <td class="px-3 py-1.5 text-right font-semibold text-slate-800">{{ number_format($k->nominal, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td class="px-3 py-1.5 text-slate-400 italic" colspan="5">Belum ada catatan kas kecil.</td>
            </tr>
            @endforelse
            @if($data->isNotEmpty())
            <tr class="bg-slate-50 border-t border-slate-200">
                <td class="px-3 py-1.5 font-bold text-slate-700 uppercase" colspan="4">Total Pengeluaran (halaman ini)</td>
                <td class="px-3 py-1.5 text-right font-bold text-slate-800">{{ number_format($data->sum('nominal'), 0, ',', '.') }}</td>
            </tr>
            @endif
        </x-laporan.print-table>
    </div>

    <div class="no-print">
        <x-table.pagination :paginator="$data" />
    </div>

</div>

@push('scripts')
<script>
    function konfirmasiHapusKasKecil(e, form) {
        e.preventDefault();
        Swal.fire({
            title: 'Hapus catatan kas kecil ini?',
            text: 'Data yang sudah dihapus tidak bisa dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            customClass: { confirmButton: 'rounded-lg px-6 py-2.5', cancelButton: 'rounded-lg px-6 py-2.5 text-slate-600' },
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
        return false;
    }
</script>
@endpush
@endsection

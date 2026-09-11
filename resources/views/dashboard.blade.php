@extends('layouts.app')

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Dashboard</h1>
        <p class="text-slate-500 text-sm">Pergerakan bisnis hari ini, {{ now()->translatedFormat('d F Y') }} &middot; {{ Auth::user()->username }} ({{ Auth::user()->role->nama_role }})</p>
    </div>
    <div class="flex items-center gap-2 px-4 py-2 rounded-xl {{ $kasAktif ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-rose-50 text-rose-600 border border-rose-100' }}">
        <span class="relative flex h-2 w-2">
            @if($kasAktif)
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            @endif
            <span class="relative inline-flex rounded-full h-2 w-2 {{ $kasAktif ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
        </span>
        <span class="text-[11px] font-black uppercase tracking-widest">Kasir {{ $kasAktif ? 'Aktif' : 'Standby' }}</span>
    </div>
</div>

{{-- ALUR ORDER -> SPK -> PRODUKSI -> SELESAI -> COLLECTING --}}
<div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 mb-6">
    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-5">Alur Pergerakan Order</p>
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <a href="{{ route('penjualan.riwayat') }}" class="text-center group">
            <div class="text-3xl font-extrabold text-slate-800 group-hover:text-indigo-600 transition-colors">{{ $orderHariIni }}</div>
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">Order Hari Ini</div>
        </a>
        <a href="{{ route('spk.index', ['status_spk' => 'Antre']) }}" class="text-center group">
            <div class="text-3xl font-extrabold text-amber-500 group-hover:text-amber-600 transition-colors">{{ $spkAntre }}</div>
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">SPK Antre</div>
        </a>
        <a href="{{ route('produksi.index') }}" class="text-center group">
            <div class="text-3xl font-extrabold text-indigo-500 group-hover:text-indigo-600 transition-colors">{{ $spkProses }}</div>
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">Produksi Berjalan</div>
        </a>
        <a href="{{ route('produksi.index', ['status_produksi' => 'selesai']) }}" class="text-center group">
            <div class="text-3xl font-extrabold text-emerald-500 group-hover:text-emerald-600 transition-colors">{{ $spkSelesai }}</div>
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">Selesai Produksi</div>
        </a>
        <a href="{{ route('collecting.index', ['status_pengerjaan' => 'lengkap']) }}" class="text-center group">
            <div class="text-3xl font-extrabold text-teal-500 group-hover:text-teal-600 transition-colors">{{ $notaLengkap }}</div>
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">Siap Diambil</div>
        </a>
    </div>
</div>

{{-- KARTU RINGKASAN --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition">
        <div class="flex items-center gap-4">
            <div class="bg-indigo-100 p-3 rounded-2xl text-2xl">💰</div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Nilai Order Hari Ini</p>
                <h3 class="text-lg font-bold text-slate-900">Rp {{ number_format($nilaiOrderHariIni, 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition">
        <div class="flex items-center gap-4">
            <div class="bg-emerald-100 p-3 rounded-2xl text-2xl">🏦</div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Kas Masuk Hari Ini</p>
                <h3 class="text-lg font-bold text-slate-900">Rp {{ number_format($kasMasukHariIni, 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition">
        <div class="flex items-center gap-4">
            <div class="bg-rose-100 p-3 rounded-2xl text-2xl">📋</div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Piutang ({{ $piutangCount }} nota)</p>
                <h3 class="text-lg font-bold text-slate-900">Rp {{ number_format($piutang, 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition">
        <div class="flex items-center gap-4">
            <div class="bg-orange-100 p-3 rounded-2xl text-2xl">🔥</div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Item Urgent Aktif</p>
                <h3 class="text-lg font-bold text-slate-900">{{ $urgentAktif }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ORDER TERBARU --}}
    <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Order Terbaru</h3>
            {{-- riwayat() default ke tanggal HARI INI saja kalau from/to tidak
                 dikirim - "Order Terbaru" di sini menampilkan 5 order TERBARU
                 tanpa filter tanggal, jadi kalau dikirim tanpa parameter,
                 "Lihat Semua" bisa mendarat di halaman kosong (mis. dibuka pagi
                 hari sebelum ada order masuk hari itu). Kirim rentang 30 hari
                 terakhir supaya selalu mencakup order yang baru terlihat di sini. --}}
            <a href="{{ route('penjualan.riwayat', ['from' => now()->subDays(30)->format('Y-m-d'), 'to' => now()->format('Y-m-d')]) }}" class="text-[10px] font-black text-indigo-500 uppercase tracking-widest hover:underline">Lihat Semua</a>
        </div>
        <div class="divide-y divide-slate-50">
            @forelse($orderTerbaru as $o)
            <div class="px-6 py-4 flex items-center justify-between hover:bg-slate-50/50 transition-all">
                <div>
                    <div class="text-[13px] font-bold text-slate-700 uppercase">{{ $o->nomor_nota }}</div>
                    <div class="text-[11px] text-slate-400">{{ $o->nama_konsumen }} &middot; {{ \Carbon\Carbon::parse($o->tgl_order)->format('d M Y H:i') }}</div>
                </div>
                <div class="text-right">
                    <div class="text-sm font-bold text-slate-800">Rp {{ number_format($o->total_bayar, 0, ',', '.') }}</div>
                    @if($o->status_nota === 'Dibatalkan')
                        <x-badge status="Dibatalkan" variant="danger" />
                    @else
                        <x-badge :status="$o->status_pembayaran" />
                    @endif
                </div>
            </div>
            @empty
            <div class="px-6 py-10">
                <x-empty-state title="Belum ada order" />
            </div>
            @endforelse
        </div>
    </div>

    {{-- PERLU PERHATIAN (URGENT) --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">🔥 Perlu Perhatian</h3>
            <a href="{{ route('spk.index', ['urgent_only' => 1]) }}" class="text-[10px] font-black text-indigo-500 uppercase tracking-widest hover:underline">Semua</a>
        </div>
        <div class="divide-y divide-slate-50">
            @forelse($urgentList as $u)
            <div class="px-6 py-4 hover:bg-slate-50/50 transition-all">
                <div class="text-[12px] font-bold text-slate-700 uppercase">{{ $u->nama_file }}</div>
                <div class="text-[11px] text-slate-400">{{ $u->nama_konsumen }}</div>
                <x-badge :status="$u->status_spk" class="mt-1" />
            </div>
            @empty
            <div class="px-6 py-10">
                <x-empty-state title="Tidak ada item urgent" description="Semua pekerjaan berjalan normal." />
            </div>
            @endforelse
        </div>
    </div>
</div>

@if($notaBelumLengkap > 0)
<div class="mt-6 bg-amber-50 border border-amber-100 text-amber-700 rounded-3xl p-5 flex items-center gap-4">
    <span class="text-2xl">⏳</span>
    <div class="flex-1">
        <p class="text-sm font-bold">{{ $notaBelumLengkap }} nota belum lengkap produksinya</p>
        <p class="text-xs text-amber-600">Barang belum bisa diserahkan ke konsumen sampai semua item selesai.</p>
    </div>
    <a href="{{ route('collecting.index', ['status_pengerjaan' => 'belum']) }}" class="px-4 py-2 bg-amber-500 text-white rounded-xl text-[11px] font-black uppercase tracking-widest hover:bg-amber-600 transition-all">
        Lihat Detail
    </a>
</div>
@endif

@endsection

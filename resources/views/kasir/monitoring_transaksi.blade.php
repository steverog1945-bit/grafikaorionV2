@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Monitoring Kasir" subtitle="Pantau pembayaran, cetak nota, dan riwayat proses pesanan.">
        <x-slot:actions>
            <x-button href="{{ url('/kasir/waiting-list') }}" variant="warning" class="relative">
                <i class="fas fa-inbox"></i>
                Antrean Baru
                @if($waitingCount > 0)
                    <span class="absolute -top-2 -right-2 bg-rose-600 text-white w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-black border-2 border-white">{{ $waitingCount }}</span>
                @endif
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div id="new-data-alert" class="hidden fixed top-24 left-1/2 -translate-x-1/2 z-[100]">
        <button onclick="location.reload()" class="bg-indigo-600 text-white px-6 py-3 rounded-full shadow-2xl font-bold text-sm flex items-center gap-3 border-4 border-white">
            <i class="fas fa-bell"></i> Ada transaksi baru masuk - klik untuk tampilkan
        </button>
    </div>

    {{-- LOCK KASIR - $kasAktif dibagikan global lewat AppServiceProvider.
         Peringatan yang sama juga ada di Antrean Baru & Form Pembayaran
         (di sanalah aksinya benar-benar dikunci) - di sini murni supaya
         siapa pun yang membuka Monitoring langsung tahu kondisinya tanpa
         perlu masuk ke halaman lain dulu. --}}
    @if(!$kasAktif)
    <div class="mb-6 flex items-center gap-3 bg-amber-50 border-2 border-amber-200 rounded-2xl px-5 py-4">
        <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 text-lg">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="flex-1">
            <p class="text-sm font-black text-amber-800 uppercase tracking-wide">Kas Kasir Belum Dibuka</p>
            <p class="text-xs text-amber-600 mt-0.5">Pencatatan pembayaran baru terkunci sampai saldo kas hari ini dibuka.</p>
        </div>
        <x-button href="{{ route('kasir.kelola') }}" variant="warning" class="shrink-0">
            <i class="fas fa-wallet"></i> Buka Kas Sekarang
        </x-button>
    </div>
    @endif

    {{-- FILTER --}}
    <x-card class="mb-6">
        <form method="GET" action="{{ route('kasir.index') }}" class="flex flex-wrap items-end gap-4">
            {{-- Penanda "form filter ini memang disubmit" - membedakan nav
                 pertama ke halaman ini (tanpa query string sama sekali,
                 default tampilkan yang belum lunas saja) dari submit
                 eksplisit dengan Status Transaksi dikosongkan/"-- Semua
                 Status --" (yang berarti benar-benar tampilkan semua,
                 termasuk Lunas). Lihat KasirController::index(). --}}
            <input type="hidden" name="status_filtered" value="1">

            <div class="w-full md:w-40">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Order Dari</label>
                <x-form.input type="date" name="tgl_mulai" value="{{ request('tgl_mulai') }}" />
            </div>

            <div class="w-full md:w-40">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Order Sampai</label>
                <x-form.input type="date" name="tgl_selesai" value="{{ request('tgl_selesai') }}" />
            </div>

            <div class="w-full md:w-56">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Konsumen</label>
                <select name="id_konsumen" class="select2-filter w-full">
                    <option value="">-- Semua Konsumen --</option>
                    @foreach($konsumenList as $k)
                        <option value="{{ $k->id_konsumen }}" {{ (string) request('id_konsumen') === (string) $k->id_konsumen ? 'selected' : '' }}>{{ $k->nama_konsumen }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-44">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">No. Nota</label>
                <x-form.input type="text" name="nomor_nota" value="{{ request('nomor_nota') }}" placeholder="INV-..." />
            </div>

            <div class="w-full md:w-44">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Status Transaksi</label>
                <x-form.select name="status_bayar">
                    <option value="">-- Semua Status --</option>
                    <option value="Belum Bayar" {{ request('status_bayar') === 'Belum Bayar' ? 'selected' : '' }}>Belum Bayar</option>
                    <option value="DP" {{ request('status_bayar') === 'DP' ? 'selected' : '' }}>DP / Panjar</option>
                    <option value="Lunas" {{ request('status_bayar') === 'Lunas' ? 'selected' : '' }}>Lunas</option>
                </x-form.select>
            </div>

            <x-button type="submit">
                <i class="fas fa-filter"></i>
                Filter
            </x-button>

            @if(request()->anyFilled(['tgl_mulai', 'tgl_selesai', 'id_konsumen', 'nomor_nota', 'status_bayar']))
                <a href="{{ route('kasir.index') }}" class="text-[11px] font-bold text-rose-500 uppercase tracking-widest hover:underline ml-2">
                    Reset
                </a>
            @endif
        </form>
    </x-card>

    <x-table>
        <thead>
            <tr>
                <th class="px-4 py-5 text-center w-12">#</th>
                <th class="px-4 py-5 text-left">Waktu</th>
                <th class="px-4 py-5 text-center">Tipe</th>
                <th class="px-4 py-5 text-left">No. Nota</th>
                <th class="px-4 py-5 text-left">Konsumen</th>
                <th class="px-6 py-5 text-right">Total</th>
                <th class="px-4 py-5 text-center">Status</th>
                <th class="px-8 py-5 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($transaksi as $i => $t)
            <tr class="group hover:bg-slate-50/50 transition-all">
                <td class="px-4 py-5 text-center text-[11px] text-slate-400">{{ $transaksi->firstItem() + $i }}</td>
                <td class="px-4 py-5">
                    <span class="text-[12px] font-semibold text-slate-600">{{ \Carbon\Carbon::parse($t->tgl_order)->format('d M Y') }}</span>
                    <div class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($t->tgl_order)->format('H:i') }}</div>
                </td>
                <td class="px-4 py-5 text-center">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold {{ $t->tipe_order === 'PO' ? 'bg-amber-50 text-amber-700 border border-amber-100' : 'bg-slate-50 text-slate-500 border border-slate-100' }}">
                        {{ $t->tipe_order }}
                    </span>
                </td>
                <td class="px-4 py-5">
                    <span class="text-[13px] font-bold text-indigo-600">#{{ $t->nomor_nota }}</span>
                    <div class="text-[10px] text-slate-400">Kasir: {{ $t->username }}</div>
                </td>
                <td class="px-4 py-5">
                    <span class="text-[12px] font-bold text-slate-700 uppercase">{{ $t->nama_konsumen }}</span>
                </td>
                <td class="px-6 py-5 text-right">
                    <span class="text-[13px] font-bold text-slate-800">Rp {{ number_format($t->total_bayar, 0, ',', '.') }}</span>
                    {{-- Rincian "sudah terbayar berapa" untuk status DP - data
                         ini sebelumnya SUDAH diambil lewat subquery total_masuk
                         (lihat KasirController::index()) tapi tidak pernah
                         ditampilkan di sini, jadi begitu status berubah dari DP
                         ke Lunas, jejak cicilannya seolah "menghilang" karena
                         cuma bisa dilihat lewat modal Riwayat Proses terpisah. --}}
                    @if($t->status_pembayaran === 'DP')
                        <div class="text-[10px] text-emerald-500 font-bold mt-0.5">Terbayar Rp {{ number_format($t->total_masuk ?? 0, 0, ',', '.') }}</div>
                        <div class="text-[10px] text-amber-500 font-bold">Sisa Rp {{ number_format($t->total_bayar - ($t->total_masuk ?? 0), 0, ',', '.') }}</div>
                    @endif
                </td>
                <td class="px-4 py-5 text-center">
                    <x-badge :status="$t->status_pembayaran" />
                </td>
                <td class="px-8 py-5 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <x-button href="{{ url('/kasir/bayar-form/'.$t->id_transaksi) }}" size="icon" variant="ghost" class="border border-slate-100 hover:text-emerald-600" title="Input Pembayaran">
                            <i class="fas fa-money-bill-wave text-xs"></i>
                        </x-button>

                        <div class="relative">
                            <x-button type="button" size="icon" variant="ghost" onclick="togglePrintMenu(event, {{ $t->id_transaksi }})" class="border border-slate-100 hover:text-indigo-600" title="Cetak Nota">
                                <i class="fas fa-print text-xs"></i>
                            </x-button>
                            <div id="print-menu-{{ $t->id_transaksi }}" class="hidden absolute right-0 mt-2 w-48 bg-white border border-slate-100 shadow-xl rounded-xl z-50 overflow-hidden">
                                <a href="{{ url('/kasir/print-opsi/'.$t->id_transaksi) }}?format=thermal" target="_blank" class="flex items-center gap-2 px-4 py-2.5 text-[10px] font-bold text-slate-600 hover:bg-slate-50 border-b border-slate-50">
                                    <i class="fas fa-receipt text-indigo-400"></i> NOTA THERMAL (58mm)
                                </a>
                                <a href="{{ url('/kasir/print-opsi/'.$t->id_transaksi) }}?format=a4" target="_blank" class="flex items-center gap-2 px-4 py-2.5 text-[10px] font-bold text-slate-600 hover:bg-slate-50">
                                    <i class="fas fa-file-invoice text-indigo-400"></i> INVOICE (A4/A5)
                                </a>
                            </div>
                        </div>

                        <x-button type="button" size="icon" variant="ghost" onclick="showHistory('{{ $t->id_transaksi }}')" class="border border-slate-100 hover:text-slate-800" title="Riwayat Proses">
                            <i class="fas fa-clock-rotate-left text-xs"></i>
                        </x-button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8">
                    <x-empty-state title="Tidak ada transaksi" description="Sesuaikan filter, atau tunggu order baru diproses dari antrean SPK/Kasir." />
                </td>
            </tr>
            @endforelse
        </tbody>
    </x-table>

    <x-table.pagination :paginator="$transaksi" />

</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2-filter').select2({ width: '100%', placeholder: '-- Semua Konsumen --', allowClear: true });
    });

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // Dropdown "Cetak Nota" dipicu lewat klik/tap, bukan :hover CSS - supaya
    // tetap berfungsi di layar sentuh (kasir sering pakai perangkat touchscreen,
    // dan dropdown hover-only tidak pernah terbuka lewat tap).
    function togglePrintMenu(event, id) {
        event.stopPropagation();
        const menu = document.getElementById('print-menu-' + id);
        const isOpen = !menu.classList.contains('hidden');

        document.querySelectorAll('[id^="print-menu-"]').forEach(m => m.classList.add('hidden'));

        if (!isOpen) {
            menu.classList.remove('hidden');
        }
    }

    document.addEventListener('click', function () {
        document.querySelectorAll('[id^="print-menu-"]').forEach(m => m.classList.add('hidden'));
    });

    async function showHistory(id) {
        Swal.fire({ title: 'Memuat...', didOpen: () => Swal.showLoading() });

        try {
            const res = await fetch(`{{ url('/kasir/history') }}/${id}`);
            const data = await res.json();

            if (data.status !== 'success') {
                return Swal.fire('Gagal', data.msg || 'Data tidak ditemukan.', 'error');
            }

            const itemRows = data.items.map(it =>
                `<tr>
                    <td class="py-1 pr-2">${escapeHtml(it.nama_file)}</td>
                    <td class="py-1 pr-2 text-center">${it.qty}${it.qty_cetak !== null ? ' / ' + it.qty_cetak : ''}</td>
                    <td class="py-1 text-right font-bold">${it.status_spk}</td>
                </tr>`
            ).join('');

            const bayarRows = data.pembayaran.length
                ? data.pembayaran.map(p =>
                    `<tr>
                        <td class="py-1 pr-2">${new Date(p.tgl_bayar).toLocaleString('id-ID')}</td>
                        <td class="py-1 pr-2">${p.metode}</td>
                        <td class="py-1 text-right font-bold">Rp ${new Intl.NumberFormat('id-ID').format(p.nominal)}</td>
                    </tr>`
                  ).join('')
                : '<tr><td colspan="3" class="py-2 text-slate-400 italic">Belum ada pembayaran tercatat.</td></tr>';

            Swal.fire({
                title: 'Nota ' + data.nomor_nota,
                width: 480,
                html: `
                    <div class="text-left text-xs">
                        <p class="text-slate-500 mb-3">Order: ${new Date(data.tgl_order).toLocaleString('id-ID')} &middot; Status: <b>${data.status_pembayaran}</b></p>
                        <p class="font-bold uppercase text-[10px] text-slate-400 mb-1">Item &amp; Status SPK (qty order / qty cetak)</p>
                        <table class="w-full mb-4">${itemRows}</table>
                        <p class="font-bold uppercase text-[10px] text-slate-400 mb-1">Riwayat Pembayaran</p>
                        <table class="w-full">${bayarRows}</table>
                    </div>
                `,
                icon: undefined,
                customClass: { popup: 'rounded-[2rem]' }
            });
        } catch (e) {
            Swal.fire('Error', 'Gagal memuat history.', 'error');
        }
    }

    // Polling notifikasi transaksi baru masuk ke antrean (Belum Bayar) -
    // dipertahankan dari versi sebelumnya, cuma tampilan banner-nya diubah.
    let initialWaitingCount = {{ $waitingCount ?? 0 }};

    function checkNewTransaction() {
        fetch("{{ url('/kasir/check-pembayaran') }}")
            .then(res => res.json())
            .then(data => {
                if (data.total > initialWaitingCount) {
                    document.getElementById('new-data-alert').classList.remove('hidden');
                }
            });
    }
    setInterval(checkNewTransaction, 10000);
</script>
@endpush
@endsection

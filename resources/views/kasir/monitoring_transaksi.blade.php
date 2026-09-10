@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">MONITORING KASIR</h2>
        <p class="text-slate-500 text-sm italic">Kelola pembayaran, cetak nota, dan pantau histori pesanan.</p>
    </div>
{{-- Letakkan di sebelah judul Monitoring --}}
<a href="{{ url('/kasir/waiting-list') }}" class="relative inline-flex items-center gap-3 bg-amber-400 hover:bg-amber-500 text-slate-900 px-6 py-3 rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-lg shadow-amber-100">
    <span>📢</span> Antrean Baru
    @if($waitingCount > 0)
        <span class="absolute -top-2 -right-2 bg-rose-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-[10px] border-4 border-white animate-bounce">
            {{ $waitingCount }}
        </span>
    @endif
</a>
<div id="new-data-alert" class="hidden fixed top-24 left-1/2 -translate-x-1/2 z-[100]">
    <button onclick="location.reload()" class="bg-indigo-600 text-white px-8 py-3 rounded-full shadow-2xl font-black text-sm flex items-center gap-3 animate-bounce border-4 border-white">
        <span>🔔</span> ADA TRANSAKSI BARU MASUK! KLIK UNTUK TAMPILKAN
    </button>
</div>

<script>
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
    setInterval(checkNewTransaction, 10000); // Cek setiap 10 detik
</script>
</div>

<div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm mb-6">
    <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nota / Konsumen..." class="bg-slate-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-indigo-500">
        
        <select name="status_bayar" class="bg-slate-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-indigo-500">
            <option value="">-- Semua Status Bayar --</option>
            <option value="DP" {{ request('status_bayar') == 'DP' ? 'selected' : '' }}>DP / Panjar</option>
            <option value="Lunas" {{ request('status_bayar') == 'Lunas' ? 'selected' : '' }}>Lunas</option>
        </select>

        <div class="flex gap-2">
            <input type="date" name="tgl_mulai" value="{{ request('tgl_mulai') }}" class="bg-slate-50 border-none rounded-xl text-sm w-full">
            <input type="date" name="tgl_selesai" value="{{ request('tgl_selesai') }}" class="bg-slate-50 border-none rounded-xl text-sm w-full">
        </div>

        <button type="submit" class="bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all">Filter Data</button>
    </form>
</div>

<div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-left border-collapse min-w-[640px]">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="p-6 text-[10px] font-black uppercase text-slate-400">Nota & Konsumen</th>
                <th class="p-6 text-[10px] font-black uppercase text-slate-400">Total Tagihan</th>
                <th class="p-6 text-[10px] font-black uppercase text-slate-400">Status</th>
                <th class="p-6 text-[10px] font-black uppercase text-slate-400 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaksi as $t)
            <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-all">
                <td class="p-6">
                    <div class="font-bold text-indigo-600">#{{ $t->nomor_nota }}</div>
                    <div class="font-bold text-slate-700 uppercase text-xs">{{ $t->nama_konsumen }}</div>
                    <div class="text-[10px] text-slate-400 uppercase tracking-tighter">{{ date('d M Y H:i', strtotime($t->tgl_order)) }}</div>
                </td>
                <td class="p-6">
                    <div class="font-mono font-bold text-slate-800">Rp {{ number_format($t->total_bayar) }}</div>
                </td>
                <td class="p-6">
                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase 
                        {{ $t->status_pembayaran == 'Lunas' ? 'bg-emerald-100 text-emerald-600' : ($t->status_pembayaran == 'DP' ? 'bg-amber-100 text-amber-600' : 'bg-rose-100 text-rose-600') }}">
                        {{ $t->status_pembayaran }}
                    </span>
                </td>
                <td class="p-6 text-center">
                    <div class="flex justify-center gap-2">
                        <a href="{{ url('/kasir/bayar-form/'.$t->id_transaksi) }}" class="bg-emerald-500 text-white p-2 rounded-lg hover:bg-emerald-600 transition-all" title="Input Pembayaran">
                            💰
                        </a>

                        <div class="relative">
                            <button type="button" onclick="togglePrintMenu(event, {{ $t->id_transaksi }})" class="bg-indigo-500 text-white p-2 rounded-lg hover:bg-indigo-600 transition-all" title="Cetak Nota">
                                🖨️
                            </button>
                            <div id="print-menu-{{ $t->id_transaksi }}" class="hidden absolute right-0 mt-2 w-40 bg-white border border-slate-100 shadow-xl rounded-xl z-50 overflow-hidden">
                                <a href="{{ url('/kasir/print-opsi/'.$t->id_transaksi) }}?format=thermal" target="_blank" class="block px-4 py-2 text-[10px] font-bold text-slate-600 hover:bg-slate-50 border-b border-slate-50">NOTA THERMAL (58mm)</a>
                                <a href="{{ url('/kasir/print-opsi/'.$t->id_transaksi) }}?format=a4" target="_blank" class="block px-4 py-2 text-[10px] font-bold text-slate-600 hover:bg-slate-50">INVOICE (A4/A5)</a>
                            </div>
                        </div>

                        <button onclick="showHistory('{{ $t->id_transaksi }}')" class="bg-slate-100 text-slate-600 p-2 rounded-lg hover:bg-slate-200 transition-all" title="History Proses">
                            🕒
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>

<script>
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
</script>

</div>
@endsection
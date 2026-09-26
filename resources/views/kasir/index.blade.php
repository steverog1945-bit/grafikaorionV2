@extends('layouts.app')

@section('content')
<div class="p-8 bg-slate-50 min-h-screen">
    <div id="notif-baru" class="hidden fixed top-10 left-1/2 -translate-x-1/2 z-50 animate-bounce">
        <button onclick="location.reload()" class="bg-orange-500 text-white px-8 py-4 rounded-full shadow-2xl font-black flex items-center gap-3">
            <span>🔔 ADA TRANSAKSI BARU MASUK!</span>
            <span class="bg-white text-orange-600 px-2 py-1 rounded-lg text-xs">REFRESH</span>
        </button>
    </div>

    <div class="flex justify-between items-center mb-10">
        <div>
            <h1 class="text-3xl font-black text-slate-800 uppercase italic">Monitoring Kasir</h1>
            <p class="text-slate-400 font-bold text-sm">Kelola pembayaran dan persetujuan SPK produksi</p>
        </div>
        
        <div class="bg-white p-2 rounded-2xl shadow-sm border border-slate-200 flex gap-2">
            <a href="?status=Piutang" class="px-6 py-3 rounded-xl font-black text-xs uppercase transition-all {{ $status == 'Piutang' ? 'bg-indigo-600 text-white' : 'text-slate-400' }}">Piutang (Hold)</a>
            <a href="?status=Lunas" class="px-6 py-3 rounded-xl font-black text-xs uppercase transition-all {{ $status == 'Lunas' ? 'bg-indigo-600 text-white' : 'text-slate-400' }}">Sudah Lunas</a>
        </div>
    </div>

    <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="p-6 text-left text-[10px] font-black uppercase text-slate-400">Nota & Pelanggan</th>
                    <th class="p-6 text-left text-[10px] font-black uppercase text-slate-400">Waktu Order</th>
                    <th class="p-6 text-right text-[10px] font-black uppercase text-slate-400">Total Tagihan</th>
                    <th class="p-6 text-center text-[10px] font-black uppercase text-slate-400">Status</th>
                    <th class="p-6 text-center text-[10px] font-black uppercase text-slate-400">Aksi</th>
                </tr>
            </thead>
            <tbody id="tabel-body">
                @foreach($transaksi as $t)
                <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-all">
                    <td class="p-6">
                        <div class="font-black text-slate-700">{{ $t->nomor_nota }}</div>
                        <div class="text-xs font-bold text-indigo-500">{{ $t->nama_konsumen }}</div>
                    </td>
                    <td class="p-6 text-xs font-bold text-slate-500">{{ date('H:i', strtotime($t->tgl_order)) }}</td>
                    <td class="p-6 text-right font-black text-slate-800">Rp {{ number_format($t->total_bayar, 0, ',', '.') }}</td>
                    <td class="p-6 text-center">
                        <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase {{ $t->status_pembayaran == 'Lunas' ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }}">
                            {{ $t->status_pembayaran }}
                        </span>
                    </td>
                    <td class="p-6 text-center">
                        @if($is_holder_kas && $t->status_pembayaran != 'Lunas')
                            <button onclick="bayar('{{ $t->id_transaksi }}', '{{ $t->nomor_nota }}')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-[10px] font-black uppercase shadow-lg shadow-indigo-100 transition-all">
                                Konfirmasi & SPK
                            </button>
                        @else
                            <span class="text-slate-300 text-[10px] font-black uppercase italic">Terkunci</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    // Logic Notifikasi Baru (Polling)
    let lastId = {{ $transaksi->first()->id_transaksi ?? 0 }};
    
    setInterval(async () => {
        try {
            const res = await fetch(`{{ url('/api/cek-order-baru') }}/${lastId}`);
            const data = await res.json();
            if(data.jumlah > 0) {
                document.getElementById('notif-baru').classList.remove('hidden');
            }
        } catch (e) {}
    }, 15000); // Cek setiap 15 detik

    // Logic Tombol Bayar
    async function bayar(id, nota) {
        const { isConfirmed } = await Swal.fire({
            title: 'Konfirmasi Pembayaran',
            text: `Yakin ingin melunasi nota ${nota} dan mengirim ke Produksi?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            confirmButtonText: 'YA, LUNASKAN!'
        });

        if (isConfirmed) {
            const res = await fetch('{{ url('/kasir/bayar') }}', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ id_transaksi: id })
            });
            const result = await res.json();
            if(result.status === 'success') {
                Swal.fire('Berhasil!', result.msg, 'success').then(() => location.reload());
            }
        }
    }
</script>
@endsection
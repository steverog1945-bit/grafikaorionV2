@extends('layouts.app')

@section('content')
<div class="mb-8 flex justify-between items-end">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 uppercase">Manajemen Harga Jual</h1>
        <p class="text-sm text-slate-500">Kelola harga produk berdasarkan level konsumen (Retail, Reseller, Platinum).</p>
    </div>
</div>

<div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead class="bg-slate-50 border-b border-slate-200 text-[10px] font-black text-slate-500 uppercase tracking-widest">
            <tr>
                <th class="p-5 w-1/4">Produk</th>
                @foreach($golongan as $g)
                <th class="p-5 text-center border-l border-slate-100">{{ $g->nama_golongan }}</th>
                @endforeach
                <th class="p-5 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($produk as $p)
            <tr class="hover:bg-indigo-50/30 transition">
                <td class="p-5">
                    <div class="font-bold text-slate-900">{{ $p->nama_produk }}</div>
                    <div class="text-[10px] text-slate-400 font-mono">{{ strtoupper($p->metode_hitung) }}</div>
                </td>
                
                @foreach($golongan as $g)
                <td class="p-5 text-center border-l border-slate-50">
                    @php 
                        $harga = $all_harga->where('id_produk', $p->id_produk)->where('id_golongan', $g->id_golongan)->first();
                    @endphp
                    <span class="font-mono font-bold text-slate-700">
                        {{ $harga ? number_format($harga->harga_jual, 0, ',', '.') : '-' }}
                    </span>
                </td>
                @endforeach

                <td class="p-5 text-right">
                    <button onclick="openEditHarga({{ json_encode($p) }}, {{ json_encode($all_harga->where('id_produk', $p->id_produk)->values()) }})" 
                            class="bg-white border border-slate-200 px-3 py-1.5 rounded-lg text-xs font-bold shadow-sm hover:bg-indigo-600 hover:text-white transition">
                        Update Harga
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div id="modalHarga" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal()"></div>
    
    <form action="{{ route('setting-harga.update_bulk') }}" method="POST" class="relative w-full max-w-5xl bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        @csrf
        <input type="hidden" name="id_produk" id="modal_id_produk">

        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-white">
            <div>
                <h3 id="nama_produk_modal" class="text-xl font-bold text-slate-900">Nama Produk</h3>
                <p class="text-xs text-indigo-600 font-bold uppercase tracking-widest">Konfigurasi Harga Berjenjang (Tiered Pricing)</p>
            </div>
            <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition text-3xl">&times;</button>
        </div>

        <div class="p-6 overflow-y-auto bg-slate-50/50 space-y-8">
            @foreach($golongan as $g)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="bg-slate-50 px-6 py-3 border-b border-slate-200 flex justify-between items-center">
                    <span class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        <span class="text-sm font-bold text-slate-700">{{ $g->nama_golongan }}</span>
                    </span>
                    <button type="button" onclick="addRangeRow({{ $g->id_golongan }})" 
                            class="text-xs bg-indigo-600 text-white px-4 py-1.5 rounded-lg font-bold hover:bg-indigo-700 transition shadow-md shadow-indigo-100">
                        + Tambah Range
                    </button>
                </div>

                <div class="grid grid-cols-12 gap-4 px-6 py-2 bg-slate-50/50 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase">
                    <div class="col-span-3">Min Qty</div>
                    <div class="col-span-3">Max Qty</div>
                    <div class="col-span-5 italic">Harga Satuan (Rp)</div>
                    <div class="col-span-1 text-center">Aksi</div>
                </div>

                <div id="range_container_{{ $g->id_golongan }}" class="p-4 space-y-3">
                    </div>
            </div>
            @endforeach
        </div>

        <div class="p-6 border-t border-slate-100 bg-white flex justify-end gap-3">
            <button type="button" onclick="closeModal()" class="px-6 py-3 text-sm font-bold text-slate-500 hover:text-slate-700 transition">Batal</button>
            <button type="submit" class="px-10 py-3 bg-indigo-600 text-white rounded-xl font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 active:scale-95 transition-all">
                SIMPAN SEMUA HARGA
            </button>
        </div>
    </form>
</div>
<script>
    function addRangeRow(idGolongan, data = null) {
        const container = document.getElementById(`range_container_${idGolongan}`);
        const index = Date.now() + Math.floor(Math.random() * 100); // Unique index
        
        const row = document.createElement('div');
        row.className = 'grid grid-cols-12 gap-4 items-center p-2 rounded-xl hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100';
        row.innerHTML = `
            <div class="col-span-3">
                <input type="number" name="ranges[${idGolongan}][${index}][min]" 
                    class="w-full rounded-xl border-slate-200 bg-white px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm" 
                    placeholder="Min" value="${data ? data.range_min : '1'}" required>
            </div>
            <div class="col-span-3">
                <input type="number" name="ranges[${idGolongan}][${index}][max]" 
                    class="w-full rounded-xl border-slate-200 bg-white px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm" 
                    placeholder="Max" value="${data ? data.range_max : '999999'}" required>
            </div>
            <div class="col-span-5">
                <div class="relative">
                    <span class="absolute left-3 top-2 text-slate-400 text-sm font-bold">Rp</span>
                    <input type="number" name="ranges[${idGolongan}][${index}][harga]" 
                        class="w-full pl-10 pr-4 py-2 rounded-xl border-slate-200 bg-white text-sm font-bold text-emerald-600 focus:ring-2 focus:ring-emerald-500 shadow-sm" 
                        placeholder="0" value="${data ? parseInt(data.harga_jual) : ''}" required>
                </div>
            </div>
            <div class="col-span-1 text-center">
                <button type="button" onclick="this.parentElement.parentElement.remove()" 
                        class="p-2 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        `;
        container.appendChild(row);
    }

    function openEditHarga(produk, hargaList) {
        document.getElementById('modalHarga').classList.remove('hidden');
        document.getElementById('nama_produk_modal').innerText = produk.nama_produk;
        document.getElementById('modal_id_produk').value = produk.id_produk;
        
        // Bersihkan kontainer
        @foreach($golongan as $g)
            document.getElementById('range_container_{{ $g->id_golongan }}').innerHTML = '';
        @endforeach

        // Isi data jika ada
        if (hargaList && hargaList.length > 0) {
            hargaList.forEach(h => {
                addRangeRow(h.id_golongan, h);
            });
        } else {
            // Default 1 baris kosong per golongan
            @foreach($golongan as $g)
                addRangeRow({{ $g->id_golongan }});
            @endforeach
        }
    }

    function closeModal() {
        document.getElementById('modalHarga').classList.add('hidden');
    }
</script>
@endsection
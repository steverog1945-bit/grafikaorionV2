@extends('layouts.app')

@section('content')
<div class="w-full px-6 py-8 bg-[#f8fafc] min-h-screen max-w-[1600px] mx-auto text-[14px] font-medium text-slate-600">

    {{-- HEADER & CONTROLS --}}
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-black text-slate-800 tracking-tighter uppercase flex items-center gap-2">
            <i class="fas fa-tags text-indigo-600"></i>
            Price Engineering
        </h1>
        <p class="text-slate-400 text-[10px] uppercase tracking-widest mt-0.5">Automated Pricing & Tiering Management</p>
    </div>

    <form action="{{ route('setting-harga.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
        {{-- Search --}}
        <div class="relative">
            <input type="text" name="search" value="{{ request('search') }}" 
                placeholder="Cari produk..." 
                class="pl-8 pr-4 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 text-[11px] w-44">
            <i class="fas fa-search absolute left-3 top-2.5 text-slate-300"></i>
        </div>

        {{-- Filter Kategori --}}
        <select name="category" onchange="this.form.submit()" class="bg-white border border-slate-200 rounded-lg py-2 px-3 text-[11px] focus:ring-indigo-500">
            <option value="">Semua Mesin</option>
            @foreach($categories as $cat)
                {{-- Gunakan id_mesin --}}
                <option value="{{ $cat->id_mesin }}" {{ request('category') == $cat->id_mesin ? 'selected' : '' }}>
                    {{ strtoupper($cat->nama_mesin) }}
                </option>
            @endforeach
        </select>

        {{-- Sortir --}}
        <select name="sort" onchange="this.form.submit()" class="bg-white border border-slate-200 rounded-lg py-2 px-3 text-[11px]">
            <option value="produk.nama_produk" {{ request('sort') == 'produk.nama_produk' ? 'selected' : '' }}>Urut Nama</option>
            <option value="produk.created_at" {{ request('sort') == 'produk.created_at' ? 'selected' : '' }}>Produk Baru</option>
        </select>

        <a href="{{ route('setting-harga.index') }}" class="p-2 text-slate-400 hover:text-rose-500 transition-colors" title="Reset Filter">
            <i class="fas fa-sync-alt text-[11px]"></i>
        </a>
    </form>
</div>

{{-- TABLE AREA --}}
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-[10px] uppercase text-slate-400">
                <th class="px-5 py-3 text-left font-bold w-64">Produk / Kategori</th>
                @foreach($golongan as $g)
                <th class="px-3 py-3 text-center font-bold border-l border-slate-100">{{ $g->nama_golongan }}</th>
                @endforeach
                <th class="px-5 py-3 text-right font-bold border-l border-slate-100">Opsi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($produk as $p)
            <tr class="hover:bg-indigo-50/30 transition-colors group">
                <td class="px-5 py-3">
                    <div class="text-slate-800 font-bold tracking-tight">{{ $p->nama_produk }}</div>
                    <div class="flex items-center gap-2 mt-0.5">
                        {{-- Menampilkan nama kategori hasil JOIN --}}
                        <span class="text-[9px] bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded font-bold uppercase border border-indigo-100">
                            <i class="fas fa-print mr-1"></i>{{ $p->nama_mesin ?? 'Tanpa Mesin' }}
                        </span>
                        <span class="text-[9px] text-slate-400 font-medium italic">By {{ $p->metode_hitung }}</span>
                    </div>
                </td>
                
                @foreach($golongan as $g)
                <td class="px-3 py-3 text-center border-l border-slate-50/50">
                    @php 
                        $harga = $all_harga->where('id_produk', $p->id_produk)->where('id_golongan', $g->id_golongan);
                    @endphp
                    @if($harga->count() > 1)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-600 border border-indigo-100">TIERED</span>
                    @elseif($harga->count() == 1)
                        <span class="text-slate-700 font-semibold">Rp{{ number_format($harga->first()->harga_jual, 0, ',', '.') }}</span>
                    @else
                        <span class="text-slate-300 italic text-[10px]">Belum Set</span>
                    @endif
                </td>
                @endforeach

                <td class="px-5 py-3 text-right border-l border-slate-50/50">
                    <button onclick='openEditHarga(@json($p), @json($all_harga->where("id_produk", $p->id_produk)->values()))' 
                            class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all">
                        <i class="fas fa-sliders-h text-sm"></i>
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ count($golongan) + 2 }}" class="px-5 py-12 text-center">
                    <div class="flex flex-col items-center opacity-30">
                        <i class="fas fa-search mb-2 text-2xl"></i>
                        <p class="text-[11px] font-bold uppercase tracking-widest">Produk tidak ditemukan</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- PAGINATION --}}
    <div class="px-5 py-3 bg-slate-50 border-t border-slate-100">
        {{ $produk->links() }}
    </div>
</div>


{{-- MODAL --}}
<div id="modalHarga" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-[2px]" onclick="closeModalHarga()"></div>
    
    <form action="{{ route('setting-harga.update_bulk') }}" method="POST" id="formHarga" 
          class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl flex flex-col max-h-[90vh] border border-slate-200">
        @csrf
        <input type="hidden" name="id_produk" id="modal_id_produk">
        
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-white rounded-t-2xl">
            <div>
                <h3 id="nama_produk_modal" class="text-base font-black text-slate-800 tracking-tight uppercase">Product Name</h3>
                <p class="text-indigo-500 text-[9px] font-bold uppercase tracking-widest">Configuration Engine</p>
            </div>
            <button type="button" onclick="closeModalHarga()" class="h-8 w-8 flex items-center justify-center rounded-full hover:bg-slate-100 text-slate-400 transition-colors">&times;</button>
        </div>

        <div class="p-6 overflow-y-auto space-y-6 custom-scrollbar bg-[#fcfdfe]">
            {{-- Alokasi Mesin: satu produk cuma punya SATU mesin, jadi ini di
                 LUAR loop golongan (sebelumnya duplikat sekali per golongan,
                 4 elemen dengan id sama - hanya elemen pertama yang keisi nilai
                 saat edit, dan browser mengirim elemen TERAKHIR saat submit,
                 jadi mesin yang tersimpan bisa beda dari yang terlihat dipilih). --}}
            <div class="mb-6 p-4 bg-indigo-50 border border-indigo-100 rounded-xl flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white">
                        <i class="fas fa-print text-xs"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-indigo-700 uppercase block leading-none">Alokasi Mesin Produksi</span>
                        <p class="text-[9px] text-indigo-400 mt-1">Ubah jika kategori mesin produk ini salah</p>
                    </div>
                </div>
                <select name="id_mesin" id="modal_id_mesin" class="text-[11px] border-indigo-200 rounded-lg bg-white p-2 font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id_mesin }}">{{ strtoupper($cat->nama_mesin) }}</option>
                    @endforeach
                </select>
            </div>

            @foreach($golongan as $g)
            <div class="p-4 bg-white border border-slate-200 rounded-xl shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-50 pb-3">
                    <h4 class="text-[11px] font-black text-slate-700 uppercase tracking-tight flex items-center gap-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-indigo-500"></div>
                        {{ $g->nama_golongan }}
                    </h4>
                    <div class="flex items-center gap-4">
                        <button type="button" onclick="copyToAll({{ $g->id_golongan }})" class="text-[9px] font-bold text-indigo-500 hover:text-indigo-700 bg-indigo-50 px-2 py-1 rounded">SYNC ALL</button>
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <span class="text-[10px] text-slate-400 font-bold group-hover:text-slate-600 transition-colors uppercase">Range Mode</span>
                            <div class="relative">
                                <input type="checkbox" onchange="toggleRangeUI({{ $g->id_golongan }}, this.checked)" id="toggle_{{ $g->id_golongan }}" class="sr-only peer">
                                <div class="w-8 h-4 bg-slate-200 rounded-full peer-checked:bg-indigo-500 transition-all after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:after:translate-x-4"></div>
                            </div>
                        </label>
                    </div>
                </div>

                <div id="flat_box_{{ $g->id_golongan }}" class="flex items-center gap-3">
                    <div class="flex-1 bg-slate-50 rounded-lg px-3 py-2 border border-slate-100 flex items-center">
                        <span class="text-slate-400 font-bold mr-2 text-[10px]">Rp</span>
                        <input type="number" name="harga_flat[{{ $g->id_golongan }}]" id="input_flat_{{ $g->id_golongan }}" 
                            class="bg-transparent border-none w-full p-0 text-sm font-bold text-slate-700 focus:ring-0" placeholder="0">
                    </div>
                </div>

                <div id="range_box_{{ $g->id_golongan }}" class="hidden space-y-2">
                    <div id="container_{{ $g->id_golongan }}" class="space-y-2"></div>
                    <button type="button" onclick="addRangeRow({{ $g->id_golongan }})" 
                            class="w-full py-2 border border-dashed border-slate-300 rounded-lg text-[10px] font-bold text-slate-400 hover:border-indigo-400 hover:text-indigo-500 transition-all">
                        + ADD PRICING TIER
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex justify-end gap-3 rounded-b-2xl">
            <button type="button" onclick="closeModalHarga()" class="px-4 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest hover:text-slate-700">Cancel</button>
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-indigo-700 transition-all uppercase text-[10px] shadow-lg shadow-indigo-100">
                Update Strategy
            </button>
        </div>
    </form>
</div>

<script>
    // Counter unik untuk key array `ranges[idGol][index]`. Sebelumnya index
    // dibuat dari Date.now()+random(0-99) - ketika addRangeRow dipanggil
    // berturut-turut sangat cepat (persis yang terjadi di openEditHarga(),
    // yang memuat semua baris tier lewat forEach dalam satu tick JS),
    // Date.now() bisa sama antar panggilan, dan cukup sering dua baris
    // berakhir dengan index yang identik. Baris dengan key array yang sama
    // saling menimpa field satu sama lain saat form di-submit ke PHP -
    // hasilnya satu baris harga hilang secara diam-diam setiap kali form
    // "Update Strategy" disimpan untuk produk dengan tiering harga.
    // Counter yang naik terus menjamin setiap baris selalu unik.
    let rangeRowCounter = 0;

    // FUNGSI UTAMA (Pertahankan & Perbaiki logic copy)
    function closeModalHarga() {
        document.getElementById('modalHarga').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function toggleRangeUI(idGol, isChecked) {
        const flatBox = document.getElementById(`flat_box_${idGol}`);
        const rangeBox = document.getElementById(`range_box_${idGol}`);
        const inputFlat = document.getElementById(`input_flat_${idGol}`);
        const container = document.getElementById(`container_${idGol}`);

        if (isChecked) {
            flatBox.classList.add('hidden');
            rangeBox.classList.remove('hidden');
            inputFlat.value = '';
            if (container.children.length === 0) addRangeRow(idGol);
        } else {
            flatBox.classList.remove('hidden');
            rangeBox.classList.add('hidden');
        }
    }
function addRangeRow(idGol, data = null) {
    const container = document.getElementById(`container_${idGol}`);
    const index = ++rangeRowCounter;
    
    // NORMALISASI DATA: Memastikan semua tipe data konsisten untuk pembandingan
    const d = {
        min: data ? (data.range_min ?? data.min) : '',
        max: data ? (data.range_max ?? data.max) : '',
        id_sat: data ? String(data.id_satuan ?? data.id_sat ?? '') : '',
        harga: data ? (data.harga_jual ?? data.harga) : '',
        qty: data ? (data.qty_berlaku ?? data.qty) : '1',
        id_sat_berlaku: data ? String(data.id_satuan_berlaku ?? data.id_sat_berlaku ?? '') : ''
    };

    // Generate options Satuan Utama dengan pengecekan String agar 'selected' bekerja
    let satuanOptions = '<option value="">Unit</option>';
    @foreach($satuan as $s)
        satuanOptions += `<option value="{{ $s->id_satuan }}" ${d.id_sat === String('{{ $s->id_satuan }}') ? 'selected' : ''}>{{ $s->nama_satuan }}</option>`;
    @endforeach

    // Generate options Satuan Berlaku
    let satuanBerlakuOptions = '<option value="">Unit</option>';
    @foreach($satuan as $s)
        satuanBerlakuOptions += `<option value="{{ $s->id_satuan }}" ${d.id_sat_berlaku === String('{{ $s->id_satuan }}') ? 'selected' : ''}>{{ $s->nama_satuan }}</option>`;
    @endforeach

    const div = document.createElement('div');
    div.className = "bg-slate-50 p-3 rounded-lg border border-slate-100 text-[11px] animate-in fade-in slide-in-from-top-1 duration-200";
    div.innerHTML = `
        <div class="grid grid-cols-12 gap-2 mb-2">
            <div class="col-span-3 flex items-center bg-white border border-slate-200 rounded p-1">
                <input type="number" name="ranges[${idGol}][${index}][min]" value="${d.min}" class="w-full border-none p-0 focus:ring-0 text-center" placeholder="Min">
            </div>
            <div class="col-span-1 flex items-center justify-center text-slate-300 font-bold">to</div>
            <div class="col-span-3 flex items-center bg-white border border-slate-200 rounded p-1">
                <input type="number" name="ranges[${idGol}][${index}][max]" value="${d.max}" class="w-full border-none p-0 focus:ring-0 text-center" placeholder="Max">
            </div>
            <div class="col-span-5">
                <select name="ranges[${idGol}][${index}][id_satuan]" class="w-full border-slate-200 rounded p-1 text-[10px] focus:ring-indigo-500">${satuanOptions}</select>
            </div>
        </div>
        <div class="flex items-center gap-2 pt-2 border-t border-slate-200/50">
            <div class="flex-1 flex items-center bg-white border border-slate-200 rounded px-2 py-1">
                <span class="text-slate-300 font-bold mr-1 text-[9px]">Rp</span>
                <input type="number" name="ranges[${idGol}][${index}][harga]" value="${d.harga ? parseInt(d.harga) : ''}" class="w-full border-none p-0 font-bold text-indigo-600 focus:ring-0" placeholder="0">
            </div>
            <div class="flex items-center gap-1">
                <span class="text-[9px] text-slate-400 uppercase font-bold">Per</span>
                <input type="number" name="ranges[${idGol}][${index}][qty_berlaku]" value="${d.qty}" class="w-10 border-slate-200 rounded p-1 text-center font-bold">
                <select name="ranges[${idGol}][${index}][id_satuan_berlaku]" class="w-20 border-slate-200 rounded p-1 text-[10px] focus:ring-indigo-500">${satuanBerlakuOptions}</select>
            </div>
            <button type="button" onclick="this.closest('.bg-slate-50').remove()" class="h-7 w-7 flex items-center justify-center text-rose-400 hover:bg-rose-50 rounded transition-colors"><i class="fas fa-trash-alt text-[10px]"></i></button>
        </div>
    `;
    container.appendChild(div);
}
function openEditHarga(produk, hargaList) {
    document.getElementById('modal_id_produk').value = produk.id_produk;
    document.getElementById('nama_produk_modal').innerText = produk.nama_produk;
    if (document.getElementById('modal_id_mesin')) {
            document.getElementById('modal_id_mesin').value = produk.id_mesin;
        }
    // Reset Semua Input sebelum mengisi
    @foreach($golongan as $g)
        document.getElementById('container_{{ $g->id_golongan }}').innerHTML = '';
        document.getElementById('input_flat_{{ $g->id_golongan }}').value = '';
        document.getElementById('toggle_{{ $g->id_golongan }}').checked = false;
        toggleRangeUI({{ $g->id_golongan }}, false);
    @endforeach

    if (hargaList && hargaList.length > 0) {
        // Kelompokkan harga berdasarkan id_golongan
        const grouped = {};
        hargaList.forEach(item => {
            if (!grouped[item.id_golongan]) grouped[item.id_golongan] = [];
            grouped[item.id_golongan].push(item);
        });

        Object.keys(grouped).forEach(idGol => {
            const hargas = grouped[idGol];
            // Jika ada lebih dari 1 record atau range_min tidak null, aktifkan Range Mode
            const isRange = hargas.length > 1 || (hargas[0].range_min !== null && hargas[0].range_min !== "");

            if (isRange) {
                const toggle = document.getElementById('toggle_' + idGol);
                if(toggle) {
                    toggle.checked = true;
                    // Isi baris ASLI dulu, baru toggle tampilan UI ke mode range.
                    // toggleRangeUI() punya efek samping "kalau container masih
                    // kosong, tambah 1 baris kosong otomatis" (untuk kasus toggle
                    // manual di produk baru) - kalau dipanggil SEBELUM baris asli
                    // dimuat, baris kosong ini ikut tersimpan sebagai baris ke-4
                    // yang tidak diinginkan. Mengisi dulu membuat container tidak
                    // pernah kosong di titik ini, jadi baris kosong itu tidak muncul.
                    hargas.forEach(data => addRangeRow(idGol, data));
                    toggleRangeUI(idGol, true);
                }
            } else {
                const inputFlat = document.getElementById('input_flat_' + idGol);
                if(inputFlat) inputFlat.value = parseInt(hargas[0].harga_jual || 0);
            }
        });
    }

    document.getElementById('modalHarga').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function copyToAll(sourceId) {
    Swal.fire({
        title: 'SINKRONKAN HARGA?',
        text: "Data dari golongan ini akan menyalin ke seluruh golongan lain.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        confirmButtonText: 'Ya, Sinkronkan'
    }).then((result) => {
        if (result.isConfirmed) {
            const isRangeMode = document.getElementById(`toggle_${sourceId}`).checked;
            const allGolonganIds = [@foreach($golongan as $g) {{ $g->id_golongan }}, @endforeach];
            const targetIds = allGolonganIds.filter(id => id != sourceId);

            targetIds.forEach(targetId => {
                document.getElementById(`toggle_${targetId}`).checked = isRangeMode;
                toggleRangeUI(targetId, isRangeMode);

                if (!isRangeMode) {
                    document.getElementById(`input_flat_${targetId}`).value = document.getElementById(`input_flat_${sourceId}`).value;
                } else {
                    const targetContainer = document.getElementById(`container_${targetId}`);
                    targetContainer.innerHTML = '';
                    
                    // Ambil setiap baris di sumber dan kirim ke addRangeRow target
                    Array.from(document.getElementById(`container_${sourceId}`).children).forEach(row => {
                        addRangeRow(targetId, {
                            min: row.querySelector('input[name*="[min]"]').value,
                            max: row.querySelector('input[name*="[max]"]').value,
                            id_sat: row.querySelector('select[name*="[id_satuan]"]').value, // Pastikan .value diambil
                            harga: row.querySelector('input[name*="[harga]"]').value,
                            qty: row.querySelector('input[name*="[qty_berlaku]"]').value,
                            id_sat_berlaku: row.querySelector('select[name*="[id_satuan_berlaku]"]').value // Pastikan .value diambil
                        });
                    });
                }
            });
        }
    });
}
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    input[type=number]::-webkit-inner-spin-button, input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
</style>
@endsection
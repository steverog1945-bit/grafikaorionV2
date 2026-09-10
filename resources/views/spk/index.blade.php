@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="SPK - Surat Perintah Kerja">
        <x-slot:subtitle>
            Produksi &amp; Logistik <span class="mx-1 text-slate-300">/</span> Daftar Pekerjaan dari Order
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button href="{{ route('produksi.index') }}" variant="secondary">
                <i class="fas fa-industry"></i>
                Lihat Produksi
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- DIVISI (dinamis dari master mesin yang benar-benar dipakai) --}}
    @if($divisis->isNotEmpty())
    <div class="flex flex-wrap gap-2 mb-6">
        <a href="{{ route('spk.index', request()->except(['divisi', 'page'])) }}"
           class="px-4 py-2 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all {{ !request('divisi') ? 'bg-slate-900 text-white' : 'bg-white text-slate-500 border border-slate-200 hover:border-slate-400' }}">
            Semua Divisi
        </a>
        @foreach($divisis as $d)
        <a href="{{ route('spk.index', array_merge(request()->except('page'), ['divisi' => $d->id_mesin])) }}"
           class="px-4 py-2 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all {{ (string) request('divisi') === (string) $d->id_mesin ? 'bg-slate-900 text-white' : 'bg-white text-slate-500 border border-slate-200 hover:border-slate-400' }}">
            {{ $d->nama_mesin }}
        </a>
        @endforeach
    </div>
    @endif

    {{-- FILTER & SEARCH --}}
    <x-card class="mb-6">
        <form method="GET" action="{{ route('spk.index') }}" class="flex flex-wrap items-end gap-4">
            <input type="hidden" name="divisi" value="{{ request('divisi') }}">

            <div class="flex-1 min-w-[260px]">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Cari</label>
                <x-form.input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Nama file, konsumen, atau nomor nota..." />
            </div>

            <div class="w-full md:w-52">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Status SPK</label>
                <x-form.select name="status_spk">
                    <option value="">-- Semua Status --</option>
                    @foreach(['Antre', 'Desain', 'Cetak', 'Selesai', 'Batal'] as $s)
                        <option value="{{ $s }}" {{ request('status_spk') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </x-form.select>
            </div>

            <label class="flex items-center gap-2 pb-3 cursor-pointer">
                <input type="checkbox" name="urgent_only" value="1" {{ request('urgent_only') ? 'checked' : '' }}
                    class="rounded-md border-slate-200 text-rose-600 focus:ring-rose-500">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Urgent saja</span>
            </label>

            <x-button type="submit">
                <i class="fas fa-filter"></i>
                Filter
            </x-button>

            @if(request()->anyFilled(['search', 'status_spk', 'urgent_only']))
                <a href="{{ route('spk.index', ['divisi' => request('divisi')]) }}" class="text-[11px] font-bold text-rose-500 uppercase tracking-widest hover:underline ml-2">
                    Reset
                </a>
            @endif
        </form>
    </x-card>

    <form id="bulk-action-form" method="POST" action="{{ route('spk.bulk-urgent') }}">
        @csrf
        <x-table>
            <thead>
                <tr>
                    <th class="px-6 py-5 w-12 text-center">
                        <input type="checkbox" id="checkAll" class="rounded-md border-slate-200 text-indigo-600 focus:ring-indigo-500">
                    </th>
                    <th class="px-4 py-5 text-left">Nama File</th>
                    <th class="px-4 py-5 text-left">Konsumen</th>
                    <th class="px-4 py-5 text-left">Tanggal Order</th>
                    <th class="px-4 py-5 text-center">Qty</th>
                    <th class="px-4 py-5 text-center">Urgent</th>
                    <th class="px-4 py-5 text-left">Pembuat Nota</th>
                    <th class="px-4 py-5 text-center">Status Bayar</th>
                    <th class="px-4 py-5 text-center">Status SPK</th>
                    <th class="px-8 py-5 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($spk as $item)
                <tr class="group hover:bg-slate-50/50 transition-all {{ $item->status_urgent ? 'bg-rose-50/40' : '' }}">
                    <td class="px-6 py-5 text-center">
                        <input type="checkbox" name="ids[]" value="{{ $item->id_detail }}" class="row-checkbox rounded-md border-slate-200 text-indigo-600 focus:ring-indigo-500">
                    </td>
                    <td class="px-4 py-5">
                        <div class="text-[13px] font-bold text-slate-700 uppercase group-hover:text-indigo-600 transition-colors">{{ $item->nama_file }}</div>
                        <div class="text-[10px] text-slate-400 mt-0.5">Nota: {{ $item->nomor_nota }} &middot; SPK #{{ $item->id_detail }}</div>
                    </td>
                    <td class="px-4 py-5">
                        <span class="text-[12px] font-bold text-slate-700 uppercase">{{ $item->nama_konsumen }}</span>
                        @if($item->divisi)
                            <div class="text-[10px] text-slate-400">{{ $item->divisi }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-5">
                        <span class="text-[12px] font-semibold text-slate-600">{{ \Carbon\Carbon::parse($item->tgl_order)->format('d M Y') }}</span>
                    </td>
                    <td class="px-4 py-5 text-center">
                        <span class="text-[13px] font-bold text-slate-700">{{ $item->qty }}</span>
                    </td>
                    <td class="px-4 py-5 text-center">
                        <button type="button" onclick="toggleUrgent({{ $item->id_detail }})"
                            class="text-lg {{ $item->status_urgent ? 'text-rose-500' : 'text-slate-200 hover:text-rose-300' }}" title="Tandai urgent">
                            <i class="fas fa-fire"></i>
                        </button>
                    </td>
                    <td class="px-4 py-5">
                        <span class="text-[12px] font-semibold text-slate-600">{{ $item->pembuat_nota }}</span>
                    </td>
                    <td class="px-4 py-5 text-center">
                        <x-badge :status="$item->status_pembayaran" />
                    </td>
                    <td class="px-4 py-5 text-center">
                        <x-badge :status="$item->status_spk" />
                    </td>
                    <td class="px-8 py-5 text-center">
                        <div class="flex items-center justify-center gap-2">
                            @if($item->status_spk === 'Antre')
                                <x-button type="button" size="icon" variant="ghost" onclick="ambilPekerjaan({{ $item->id_detail }})" class="border border-slate-100 hover:text-emerald-600" title="Ambil Pekerjaan (pindah ke Produksi)">
                                    <i class="fas fa-hand-paper text-xs"></i>
                                </x-button>
                            @endif
                            <x-button href="{{ route('spk.print', ['id' => $item->id_detail, 'format' => 'thermal']) }}" target="_blank" size="icon" variant="ghost" class="border border-slate-100 hover:text-slate-800" title="Cetak SPK (Thermal)">
                                <i class="fas fa-print text-xs"></i>
                            </x-button>
                            <x-button href="{{ route('produksi.index', ['id_spk' => $item->id_detail, 'status_produksi' => 'semua']) }}" size="icon" variant="ghost" class="border border-slate-100 hover:text-indigo-600" title="Lihat di Produksi">
                                <i class="fas fa-industry text-xs"></i>
                            </x-button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10">
                        <x-empty-state title="Belum ada SPK" description="SPK akan muncul otomatis begitu ada order baru dari Kasir." />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </x-table>

        <div class="mt-4 flex gap-3">
            <x-button type="button" id="btn-bulk-ambil" variant="success" class="opacity-0 pointer-events-none" disabled>
                <i class="fas fa-hand-paper"></i>
                Ambil Semua Terpilih (<span id="count-checked-ambil">0</span>)
            </x-button>
            <x-button type="button" id="btn-bulk-urgent" variant="danger" class="opacity-0 pointer-events-none" disabled>
                <i class="fas fa-fire"></i>
                Tandai Urgent (<span id="count-checked">0</span>)
            </x-button>
        </div>
    </form>

    <x-table.pagination :paginator="$spk" />

</div>
@endsection

@push('scripts')
<script>
    const checkAll = document.getElementById('checkAll');
    const checkboxes = document.querySelectorAll('.row-checkbox');
    const bulkBtn = document.getElementById('btn-bulk-urgent');
    const bulkAmbilBtn = document.getElementById('btn-bulk-ambil');
    const countSpan = document.getElementById('count-checked');
    const countSpanAmbil = document.getElementById('count-checked-ambil');

    checkAll?.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateBulkButton();
    });

    checkboxes.forEach(cb => cb.addEventListener('change', updateBulkButton));

    function updateBulkButton() {
        const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
        countSpan.textContent = checkedCount;
        countSpanAmbil.textContent = checkedCount;
        [bulkBtn, bulkAmbilBtn].forEach(btn => {
            btn.disabled = checkedCount === 0;
            btn.classList.toggle('opacity-0', checkedCount === 0);
            btn.classList.toggle('pointer-events-none', checkedCount === 0);
        });
    }

    bulkBtn?.addEventListener('click', function() {
        Swal.fire({
            title: 'Tandai Urgent Semua Terpilih?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Ya, Tandai',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-[2rem]' }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('bulk-action-form');
                form.action = "{{ route('spk.bulk-urgent') }}";
                form.submit();
            }
        });
    });

    bulkAmbilBtn?.addEventListener('click', function() {
        const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
        Swal.fire({
            title: 'Ambil Semua Pekerjaan Terpilih?',
            text: `${checkedCount} item akan pindah dari antrean SPK ke halaman Produksi. Item yang sudah diambil sebelumnya otomatis dilewati.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            confirmButtonText: 'Ya, Ambil Semua',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-[2rem]' }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('bulk-action-form');
                form.action = "{{ route('spk.bulk-ambil') }}";
                form.submit();
            }
        });
    });

    function toggleUrgent(id) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('spk') }}/${id}/urgent`;
        form.innerHTML = `@csrf @method('PATCH')`;
        document.body.appendChild(form);
        form.submit();
    }

    function ambilPekerjaan(id) {
        Swal.fire({
            title: 'Ambil Pekerjaan Ini?',
            text: 'Item akan pindah dari antrean SPK ke halaman Produksi.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            confirmButtonText: 'Ya, Ambil',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-[2rem]' }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ url('spk') }}/${id}/ambil`;
                form.innerHTML = `@csrf @method('PATCH')`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>
@endpush

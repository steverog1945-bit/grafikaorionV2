@extends('layouts.app')

@section('content')
@include('partials.rupiah-format-script')
<div class="max-w-6xl mx-auto px-4 py-10">
    <form action="{{ route('pembelian-bahan.store') }}" method="POST" id="main-form">
        @csrf

        <div class="flex flex-col md:flex-row md:items-center justify-between mb-10 gap-6">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-xl shadow-indigo-100">
                    <i class="fas fa-file-invoice-dollar text-white text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-slate-800 uppercase tracking-tight">Entry Faktur</h1>
                    <p class="text-slate-400 font-medium italic">Konversi Surat Jalan menjadi Tagihan Pembelian</p>
                </div>
            </div>
            <x-card :padding="false" class="px-8 py-4">
                <div class="text-right">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Tagihan</p>
                    <h2 class="text-3xl font-bold text-indigo-600" id="total-header">Rp 0</h2>
                </div>
            </x-card>
        </div>

        <x-card class="mb-8">
            @if ($errors->any())
                <div class="bg-rose-50 text-rose-600 p-4 rounded-2xl mb-6 font-semibold">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-rose-500 text-white p-4 rounded-2xl mb-6 font-semibold shadow-lg">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <x-form.group label="No. Surat Jalan">
                    <div class="relative group cursor-pointer" onclick="openModal('sj-picker')">
                        <input type="text" id="display_no_sj" readonly class="w-full bg-slate-50 border-2 border-transparent group-hover:border-indigo-300 rounded-xl p-3 text-sm font-bold transition-all cursor-pointer shadow-inner" placeholder="Pilih SJ...">
                        <input type="hidden" name="id_kedatangan" id="id_kedatangan">
                        <i class="fas fa-search absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 group-hover:text-indigo-500"></i>
                    </div>
                </x-form.group>

                <x-form.group label="Supplier">
                    <input type="text" id="display_supplier" readonly class="w-full bg-slate-100 border-none rounded-xl p-3 text-sm font-bold text-slate-500 shadow-inner" placeholder="-">
                </x-form.group>

                <x-form.group label="Tgl Transaksi">
                    <x-form.input type="date" name="tgl_transaksi" value="{{ date('Y-m-d') }}" required />
                </x-form.group>

                <x-form.group label="No. Faktur Vendor">
                    <x-form.input type="text" name="no_faktur" required placeholder="Contoh: INV/9910" />
                </x-form.group>

                <x-form.group label="Opsi Pajak">
                    <div class="bg-slate-50 rounded-xl p-3 flex items-center justify-between shadow-inner h-[46px]">
                        <span class="text-xs font-bold text-slate-500 uppercase">PPN 11%</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="tax_toggle" name="include_pajak" class="sr-only peer" onchange="calculateGrandTotal()">
                            <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-indigo-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                        </label>
                    </div>
                </x-form.group>

                <x-form.group label="Metode Bayar">
                    <x-form.select name="tipe_pembelian" id="tipe_pembelian" onchange="toggleTempo()" required>
                        <option value="Cash">CASH / TUNAI</option>
                        <option value="Tempo">TEMPO / KREDIT</option>
                    </x-form.select>
                </x-form.group>

                <div id="tempo_group" class="hidden">
                    <x-form.group label="Durasi Tempo (Hari)">
                        <input type="number" name="tempo_hari" id="tempo_hari" oninput="calculateDueDate()"
                            class="w-full bg-rose-50 border-none rounded-xl p-3 text-sm font-bold outline-none focus:ring-2 focus:ring-rose-500/30 shadow-inner" placeholder="30">
                    </x-form.group>
                </div>

                <div id="due_date_group" class="hidden">
                    <x-form.group label="Tgl Jatuh Tempo">
                        <input type="date" name="jatuh_tempo_tgl" id="jatuh_tempo_tgl" readonly
                            class="w-full bg-slate-100 border-none rounded-xl p-3 text-sm font-bold text-slate-500 shadow-inner cursor-not-allowed">
                    </x-form.group>
                </div>
            </div>
        </x-card>

        <x-table>
            <thead>
                <tr>
                    <th class="px-10 py-6 text-left">Nama Bahan</th>
                    <th class="px-4 py-6 text-center w-32">Qty (SJ)</th>
                    <th class="px-4 py-6 text-left w-48">Harga Satuan</th>
                    <th class="px-4 py-6 text-left w-40">Diskon/Pot</th>
                    <th class="px-10 py-6 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody id="table-body" class="divide-y divide-slate-50">
                <tr>
                    <td colspan="5" class="py-20 text-center opacity-30">
                        <i class="fas fa-inbox text-5xl mb-4 block"></i>
                        <span class="text-xs font-bold uppercase tracking-widest">Pilih Nomor Surat Jalan Dahulu</span>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="bg-slate-900 text-white">
                    <td colspan="4" class="px-10 py-8 text-right text-[10px] font-bold uppercase tracking-widest">Net Total Faktur</td>
                    <td class="px-10 py-8 text-right font-bold text-2xl" id="total-footer">Rp 0</td>
                </tr>
            </tfoot>
        </x-table>
        <input type="hidden" name="grand_total" id="input_grand_total">

        <div class="mt-10 flex justify-end">
            <x-button type="submit" size="default" class="!px-12 !py-5">
                Proses & Simpan Faktur
            </x-button>
        </div>
    </form>
</div>

{{-- MODAL PILIH SURAT JALAN (satu-satunya - sebelumnya ada 2 dgn id sama) --}}
<x-modal id="sj-picker" title="Pilih Surat Jalan" maxWidth="4xl">
    <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-widest -mt-4 mb-6">Hanya menampilkan kedatangan yang sudah berstatus 'Selesai'</p>

    <div class="overflow-y-auto max-h-[400px] pr-2">
        <table class="w-full text-left">
            <thead>
                <tr class="text-[10px] font-bold text-slate-300 uppercase tracking-widest border-b border-slate-100">
                    <th class="pb-4">No. Surat Jalan</th>
                    <th class="pb-4">Supplier</th>
                    <th class="pb-4 text-center">Tgl Datang</th>
                    <th class="pb-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($kedatangans as $sj)
                <tr class="hover:bg-slate-50/80 transition-all group">
                    <td class="py-4 font-bold text-slate-700 uppercase text-sm">{{ $sj->no_surat_jalan }}</td>
                    <td class="py-4 font-bold text-indigo-600 text-[11px] uppercase">{{ $sj->supplier->nama_supplier ?? 'UMUM' }}</td>
                    <td class="py-4 text-center text-slate-500 text-xs">{{ \Carbon\Carbon::parse($sj->tgl_datang)->format('d/m/Y') }}</td>
                    <td class="py-4 text-right">
                        <x-button type="button" size="sm" onclick="selectSJ('{{ $sj->id_kedatangan }}', '{{ $sj->no_surat_jalan }}')">
                            Pilih
                        </x-button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4">
                        <x-empty-state title="Belum ada Surat Jalan yang tersedia" />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-modal>

@endsection

@push('scripts')
<script>
    function selectSJ(id, noSj) {
        document.getElementById('id_kedatangan').value = id;
        document.getElementById('display_no_sj').value = noSj;
        loadKedatanganDetail(id);
        closeModal('sj-picker');
    }

    async function loadKedatanganDetail(id) {
        const tbody = document.getElementById('table-body');
        tbody.innerHTML = `<tr><td colspan="5" class="py-20 text-center"><i class="fas fa-circle-notch fa-spin text-indigo-500 text-2xl"></i></td></tr>`;

        try {
            const response = await fetch(`/pembelian-bahan/kedatangan-detail?id_kedatangan=${id}`);
            const data = await response.json();

            document.getElementById('display_supplier').value = data.supplier || 'UMUM';

            if (data.detail && data.detail.length > 0) {
                tbody.innerHTML = '';
                data.detail.forEach(item => {
                    tbody.innerHTML += `
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-10 py-6">
                            <p class="font-bold text-slate-700 text-xs uppercase">${item.nama_bahan}</p>
                            <p class="text-[10px] text-slate-400 font-bold tracking-widest uppercase">${item.kode_bahan}</p>
                            <input type="hidden" name="id_bahan[]" value="${item.id_bahan}">
                        </td>
                        <td class="px-4 py-6 text-center">
                            <input type="number" name="qty[]" step="0.01" value="${item.qty}" oninput="calculateGrandTotal()" class="w-20 bg-transparent text-center font-bold text-slate-700 border-b border-slate-200 focus:border-indigo-500 outline-none">
                            <p class="text-[9px] font-bold text-slate-300 mt-1 uppercase">${item.satuan || 'Pcs'}</p>
                        </td>
                        <td class="px-4 py-6">
                            <div class="flex items-center gap-2 bg-slate-50 rounded-xl px-4 py-2 shadow-inner">
                                <span class="text-[10px] font-bold text-slate-300 uppercase">Harga</span>
                                <input type="text" inputmode="numeric" name="harga[]" value="" oninput="calculateGrandTotal()" class="w-full bg-transparent font-bold text-sm text-slate-700 outline-none format-ribuan">
                            </div>
                        </td>
                        <td class="px-4 py-6 space-y-1">
                            <input type="text" inputmode="numeric" name="diskon[]" placeholder="Disc Rp" value="" oninput="calculateGrandTotal()" class="w-full text-[10px] font-bold bg-slate-50 rounded-lg p-2 outline-none border border-transparent focus:border-rose-200 format-ribuan">
                            <input type="text" inputmode="numeric" name="potongan[]" placeholder="Pot Rp" value="" oninput="calculateGrandTotal()" class="w-full text-[10px] font-bold bg-slate-50 rounded-lg p-2 outline-none border border-transparent focus:border-rose-200 format-ribuan">
                        </td>
                        <td class="px-10 py-6 text-right">
                            <p class="subtotal-text font-bold text-slate-800">Rp 0</p>
                            <input type="hidden" name="subtotal[]" class="subtotal-input">
                        </td>
                    </tr>`;
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="5" class="py-10 text-center text-slate-400">Tidak ada item di surat jalan ini.</td></tr>`;
            }

            calculateGrandTotal();

        } catch (error) {
            console.error("Error fetching detail:", error);
            Swal.fire('Error', 'Gagal mengambil data detail. Pastikan koneksi aman.', 'error');
        }
    }

    function toggleTempo() {
        const tipe = document.getElementById('tipe_pembelian').value;
        const tempoGroup = document.getElementById('tempo_group');
        const dueGroup = document.getElementById('due_date_group');

        if (tipe === 'Tempo') {
            tempoGroup.classList.remove('hidden');
            dueGroup.classList.remove('hidden');
        } else {
            tempoGroup.classList.add('hidden');
            dueGroup.classList.add('hidden');
            document.getElementById('tempo_hari').value = '';
            document.getElementById('jatuh_tempo_tgl').value = '';
        }
    }

    function calculateDueDate() {
        const tglTrans = document.querySelector('input[name="tgl_transaksi"]').value;
        const hari = document.getElementById('tempo_hari').value;

        if (tglTrans && hari) {
            let date = new Date(tglTrans);
            date.setDate(date.getDate() + parseInt(hari));
            document.getElementById('jatuh_tempo_tgl').value = date.toISOString().split('T')[0];
        }
    }

    function calculateGrandTotal() {
        let total = 0;
        document.querySelectorAll('#table-body tr').forEach(row => {
            const qtyInput = row.querySelector('[name="qty[]"]');
            if (!qtyInput) return;

            const qty = parseFloat(qtyInput.value) || 0;
            const harga = cleanRupiah(row.querySelector('[name="harga[]"]').value);
            const disc = cleanRupiah(row.querySelector('[name="diskon[]"]').value);
            const pot = cleanRupiah(row.querySelector('[name="potongan[]"]').value);

            const sub = (qty * harga) - disc - pot;
            row.querySelector('.subtotal-text').innerText = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(sub);
            row.querySelector('.subtotal-input').value = sub;
            total += sub;
        });

        const isTaxed = document.getElementById('tax_toggle').checked;
        if (isTaxed) total *= 1.11;

        const formatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(total);
        document.getElementById('total-header').innerText = formatted;
        document.getElementById('total-footer').innerText = formatted;
        document.getElementById('input_grand_total').value = total;
    }
</script>
@endpush

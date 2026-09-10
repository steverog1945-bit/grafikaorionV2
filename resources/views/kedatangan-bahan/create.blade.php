@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10">

    <x-page-header :title="isset($kedatangan) ? 'Edit Kedatangan' : 'Penerimaan Bahan'"
                   subtitle="Logistik & Inventori | Input fisik surat jalan" />

    <form id="form-kedatangan" action="{{ isset($kedatangan) ? route('kedatangan-bahan.update', $kedatangan->id_kedatangan) : route('kedatangan-bahan.store') }}" method="POST">
        @csrf
        @if(isset($kedatangan)) @method('PUT') @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- KIRI: Informasi Dokumen --}}
            <div class="lg:col-span-1 space-y-6">
                <x-card>
                    <div class="space-y-5">
                        <x-form.group label="No. Surat Jalan (Fisik)">
                            <x-form.input type="text" name="no_surat_jalan" id="no_surat_jalan"
                                value="{{ old('no_surat_jalan', $kedatangan->no_surat_jalan ?? '') }}"
                                placeholder="Contoh: SJ-2024-001" required class="uppercase" />
                        </x-form.group>

                        <x-form.group label="Tanggal Terima">
                            <x-form.input type="date" name="tgl_datang" id="tgl_datang"
                                value="{{ old('tgl_datang', isset($kedatangan) ? $kedatangan->tgl_datang->format('Y-m-d') : date('Y-m-d')) }}" />
                        </x-form.group>

                        <x-form.group label="Gudang Tujuan">
                            <x-form.select name="id_gudang">
                                @foreach($gudangs as $g)
                                    <option value="{{ $g->id_gudang }}" {{ (old('id_gudang') ?? ($kedatangan->id_gudang ?? '')) == $g->id_gudang ? 'selected' : '' }}>
                                        {{ $g->nama_gudang }}
                                    </option>
                                @endforeach
                            </x-form.select>
                        </x-form.group>

                        <x-form.group label="Supplier">
                            <select name="id_supplier" id="id_supplier" class="select2-elegant" required>
                                <option value="">-- Pilih Supplier --</option>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id_supplier }}" {{ (old('id_supplier') ?? ($kedatangan->id_supplier ?? '')) == $s->id_supplier ? 'selected' : '' }}>
                                        {{ $s->nama_supplier }}
                                    </option>
                                @endforeach
                            </select>
                        </x-form.group>
                    </div>
                </x-card>

                <button type="submit" id="btn-submit" disabled
                    class="w-full py-5 bg-slate-400 opacity-50 cursor-not-allowed text-white rounded-2xl font-bold text-xs uppercase tracking-widest transition-all flex items-center justify-center gap-3 shadow-lg">
                    <i class="fas fa-save"></i>
                    {{ isset($kedatangan) ? 'Update Data' : 'Simpan Kedatangan' }}
                </button>
            </div>

            {{-- KANAN: Detail Item --}}
            <div class="lg:col-span-2 space-y-6">
                <x-card :padding="false">
                    <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                        <div>
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-widest">Daftar Bahan Masuk</h3>
                            <p class="text-[10px] text-indigo-400 font-bold uppercase mt-1">Pastikan Qty & Item sesuai fisik</p>
                        </div>
                        <x-button type="button" id="btn-add-row" size="sm">
                            <i class="fas fa-plus-circle"></i> Tambah Item
                        </x-button>
                    </div>

                    <div class="overflow-x-auto p-4">
                        <table class="w-full border-separate border-spacing-y-3">
                            <thead>
                                <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                    <th class="px-6 py-4 text-left">Nama Bahan Baku</th>
                                    <th class="px-4 py-4 w-32 text-center">Quantity</th>
                                    <th class="px-4 py-4 w-32 text-center">Satuan</th>
                                    <th class="px-6 py-4 w-20 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="bahan-rows">
                                @forelse($kedatangan->detail ?? [] as $d)
                                    <tr class="group">
                                        <input type="hidden" name="detail_id[]" value="{{ $d->id_detail_kedatangan }}">
                                        <input type="hidden" name="id_bahan[]" value="{{ $d->id_bahan }}">

                                        <td class="px-4 py-2">
                                            <div class="flex gap-2">
                                                <input type="text" name="nama_bahan_display[]" value="{{ $d->bahan->nama_bahan }}"
                                                    class="w-full bg-slate-50 border-transparent rounded-xl p-4 font-bold text-slate-700 text-[11px] uppercase" readonly required>
                                                <button type="button" onclick="openBahanModal(this)"
                                                    class="px-4 bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white rounded-xl transition-all shadow-sm">
                                                    <i class="fas fa-search text-xs"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="number" name="qty[]" step="0.01" value="{{ $d->qty }}"
                                                class="w-full bg-slate-50 border-transparent rounded-xl p-4 font-bold text-slate-700 text-center text-[11px] focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all" required>
                                        </td>
                                        <td class="px-2 py-2 text-center">
                                            <div class="satuan-display flex items-center justify-center bg-indigo-50/50 text-indigo-600 text-[10px] font-bold uppercase py-4 rounded-xl border border-indigo-100/50">
                                                {{ $d->bahan->satuan_stok }}
                                            </div>
                                            <input type="hidden" name="satuan[]" value="{{ $d->bahan->satuan_stok }}">
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <button type="button" onclick="removeRow(this)"
                                                class="w-10 h-10 flex items-center justify-center rounded-xl text-slate-300 hover:text-white hover:bg-rose-500 transition-all mx-auto">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="p-8 border-t border-slate-50 bg-slate-50/30">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-3 ml-1 tracking-widest">Catatan Internal / Keterangan</label>
                        <textarea name="keterangan" id="keterangan_otomatis"
                            class="w-full rounded-2xl border-slate-100 bg-white p-5 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-[13px] font-semibold text-slate-600 transition-all min-h-[150px]"
                            rows="5" placeholder="Keterangan otomatis akan terisi di sini..."></textarea>
                    </div>
                    <input type="hidden" name="deleted_detail_ids" id="deleted_detail_ids">
                </x-card>
            </div>
        </div>
    </form>
</div>

{{-- Template Row --}}
<template id="row-template">
    <tr class="group">
        <td class="px-4 py-2">
            <div class="flex gap-2">
                <input type="hidden" name="id_bahan[]">
                <input type="text" name="nama_bahan_display[]"
                    class="w-full bg-slate-50 border-transparent rounded-xl p-4 font-bold text-slate-700 text-[11px] uppercase placeholder:text-slate-300"
                    placeholder="KLIK CARI BAHAN →" readonly required>
                <button type="button" onclick="openBahanModal(this)"
                    class="px-4 bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white rounded-xl transition-all shadow-sm">
                    <i class="fas fa-search text-xs"></i>
                </button>
            </div>
        </td>
        <td class="px-2 py-2">
            <input type="number" name="qty[]" step="0.01"
                class="w-full bg-slate-50 border-transparent rounded-xl p-4 font-bold text-slate-700 text-center text-[11px] focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all"
                placeholder="0.00" required>
        </td>
        <td class="px-2 py-2 text-center">
            <div class="satuan-display flex items-center justify-center bg-slate-50 text-slate-400 text-[10px] font-bold uppercase py-4 rounded-xl min-h-[50px] border border-dashed border-slate-200">
                -
            </div>
            <input type="hidden" name="satuan[]">
        </td>
        <td class="px-4 py-2 text-center">
            <button type="button" onclick="removeRow(this)"
                class="w-10 h-10 flex items-center justify-center rounded-xl text-slate-300 hover:text-white hover:bg-rose-500 transition-all mx-auto">
                <i class="fas fa-trash-alt text-sm"></i>
            </button>
        </td>
    </tr>
</template>

{{-- Modal Bahan --}}
<x-modal id="bahan-search" title="Cari Bahan Baku" maxWidth="4xl">
    <div class="relative group mb-6">
        <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors"></i>
        <input type="text" id="search-bahan"
            class="w-full bg-slate-50 border-slate-100 rounded-2xl p-5 pl-14 text-[13px] font-bold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
            placeholder="Masukkan kode bahan atau nama bahan baku...">
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-100">
        <table class="w-full text-[12px]">
            <thead>
                <tr class="bg-slate-50/50 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">
                    <th class="px-6 py-4 text-left">Kode</th>
                    <th class="px-6 py-4 text-left">Nama Bahan</th>
                    <th class="px-6 py-4 text-left">Stok Saat Ini</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="bahan-result" class="divide-y divide-slate-50">
                {{-- Data via AJAX --}}
            </tbody>
        </table>
    </div>
    <div class="mt-6 flex justify-between items-center bg-slate-50 p-4 rounded-2xl">
        <button id="prev-page" class="px-5 py-2 hover:bg-white hover:shadow-sm rounded-xl text-[10px] font-bold uppercase tracking-widest disabled:opacity-30 transition-all">← Prev</button>
        <span id="page-info" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest"></span>
        <button id="next-page" class="px-5 py-2 hover:bg-white hover:shadow-sm rounded-xl text-[10px] font-bold uppercase tracking-widest disabled:opacity-30 transition-all">Next →</button>
    </div>
</x-modal>

@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
// Global Variables untuk State Management
let activeRow = null;
let currentPage = 1;
let currentSearch = '';
let debounceTimer = null;
let isKeteranganDirty = false;

$(document).ready(function () {
    // 1. Inisialisasi Select2
    $('.select2-elegant').select2({
        width: '100%',
        placeholder: '-- Pilih Supplier --',
        dropdownParent: $('#form-kedatangan')
    });

    // 2. Jika Create (Data Kosong), Tambah Baris Pertama Otomatis
    if ($('#bahan-rows tr').length === 0) {
        addRow();
    }

    // 3. Event Listener Delegasi (Memantau input di baris baru maupun lama)
    $(document).on('input change keyup', '#form-kedatangan input, #form-kedatangan select', function() {
        validateForm();
    });

    // 4. Tombol Tambah Baris
    $('#btn-add-row').on('click', function(e) {
        e.preventDefault();
        addRow();
    });

    // 5. Visual Feedback saat Submit
    $('#form-kedatangan').on('submit', function() {
        $('#btn-submit').html('<i class="fas fa-spinner fa-spin"></i> Memproses...').prop('disabled', true);
    });

    // Jalankan validasi awal (untuk mode Edit)
    validateForm();
    $('#keterangan_otomatis').on('input', function() {
        if ($(this).val().trim() !== "") {
            isKeteranganDirty = true;
        } else {
            isKeteranganDirty = false;
        }
    });

    $(document).on('input change', 'input[name="qty[]"], input[name="id_bahan[]"]', function() {
        generateAutomaticRemarks();
    });
});

function generateAutomaticRemarks() {
    if (isKeteranganDirty) return;

    let items = [];

    $('#bahan-rows tr').each(function(index) {
        const namaBahan = $(this).find('input[name="nama_bahan_display[]"]').val();
        const qty = $(this).find('input[name="qty[]"]').val();
        const satuan = $(this).find('input[name="satuan[]"]').val();

        if (namaBahan && qty > 0) {
            items.push(`${index + 1}. ${namaBahan} (${qty} ${satuan})`);
        }
    });

    if (items.length > 0) {
        const header = "PENERIMAAN BARANG FISIK:\n";
        const body = items.join("\n");
        const footer = "\n\nNB : Barang Lengkap!";
        $('#keterangan_otomatis').val(header + body + footer);
    } else {
        $('#keterangan_otomatis').val('');
    }
}

function addRow() {
    const template = document.getElementById('row-template');
    const clone = template.content.cloneNode(true);
    document.getElementById('bahan-rows').appendChild(clone);
    validateForm();
}

function removeRow(btn) {
    const row = $(btn).closest('tr');
    const detailId = row.find('input[name="detail_id[]"]').val();

    Swal.fire({
        title: 'Hapus Item?',
        text: 'Item yang dihapus tidak akan disimpan ke database.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus'
    }).then((result) => {
        if (result.isConfirmed) {
            if (detailId) {
                let deletedList = $('#deleted_detail_ids').val();
                let arr = deletedList ? deletedList.split(',') : [];
                arr.push(detailId);
                $('#deleted_detail_ids').val(arr.join(','));
            }

            row.remove();
            generateAutomaticRemarks();
            if ($('#bahan-rows tr').length === 0) addRow();
            validateForm();
        }
    });
}

function validateForm() {
    let isValid = true;
    const noSJ = $('#no_surat_jalan').val()?.trim();
    const supplier = $('#id_supplier').val();
    const rows = $('#bahan-rows tr');

    if (!noSJ || !supplier) {
        isValid = false;
    }

    if (rows.length === 0) {
        isValid = false;
    } else {
        rows.each(function () {
            const idBahan = $(this).find('input[name="id_bahan[]"]').val();
            const qty = $(this).find('input[name="qty[]"]').val();
            if (!idBahan || idBahan === "" || !qty || parseFloat(qty) <= 0) {
                isValid = false;
            }
        });
    }

    const btn = $('#btn-submit');
    if (isValid) {
        btn.prop('disabled', false)
           .removeClass('bg-slate-400 opacity-50 cursor-not-allowed')
           .addClass('bg-indigo-600 hover:bg-indigo-700 cursor-pointer shadow-indigo-200');
    } else {
        btn.prop('disabled', true)
           .addClass('bg-slate-400 opacity-50 cursor-not-allowed')
           .removeClass('bg-indigo-600 hover:bg-indigo-700 cursor-pointer shadow-indigo-200');
    }
}

/* --- MODAL PENCARIAN BAHAN (AJAX) --- */

function openBahanModal(button) {
    activeRow = $(button).closest('tr');
    openModal('bahan-search');
    $('#search-bahan').val('').focus();
    currentPage = 1;
    loadBahan();
}

function loadBahan() {
    const tbody = $('#bahan-result');
    tbody.html('<tr><td colspan="5" class="text-center py-8"><i class="fas fa-circle-notch fa-spin text-indigo-500 text-2xl"></i></td></tr>');

    $.get("{{ route('ajax.bahan') }}", {
        search: currentSearch,
        page: currentPage
    }, function(response) {
        let rows = '';
        if (response.data.length === 0) {
            rows = '<tr><td colspan="5" class="text-center py-6 text-slate-400">Data bahan tidak ditemukan.</td></tr>';
        } else {
            response.data.forEach(function(item) {
                const safeNama = item.nama_bahan.replace(/'/g, "\\'");
                rows += `
                    <tr class="hover:bg-slate-50 transition border-b border-slate-50">
                        <td class="px-6 py-4 font-bold text-slate-700">${item.kode_bahan ?? '-'}</td>
                        <td class="px-6 py-4 text-slate-600 font-medium">${item.nama_bahan}</td>
                        <td class="px-6 py-4 text-slate-500 font-bold">${item.stok}</td>
                        <td class="px-6 py-4 text-center">
                            <button type="button"
                                onclick="selectBahan(${item.id_bahan}, '${safeNama}', '${item.satuan_stok}')"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest transition">
                                Pilih
                            </button>
                        </td>
                    </tr>`;
            });
        }
        tbody.html(rows);
        $('#page-info').text(`Hal ${response.current_page} dari ${response.last_page}`);
        $('#prev-page').prop('disabled', !response.prev_page_url);
        $('#next-page').prop('disabled', !response.next_page_url);
    }).fail(function() {
        tbody.html('<tr><td colspan="5" class="text-center py-6 text-rose-500 font-bold">Gagal memuat data. Periksa Route/Controller.</td></tr>');
    });
}

function selectBahan(id, nama, satuan) {
    activeRow.find('input[name="id_bahan[]"]').val(id);
    activeRow.find('input[name="nama_bahan_display[]"]').val(nama);

    const displaySatuan = activeRow.find('.satuan-display');
    displaySatuan.text(satuan)
        .removeClass('bg-slate-50 text-slate-400 border-dashed')
        .addClass('bg-indigo-50 text-indigo-600 border-solid border-indigo-100');

    activeRow.find('input[name="satuan[]"]').val(satuan);

    closeModal('bahan-search');
    generateAutomaticRemarks();
    validateForm();
}

$('#search-bahan').on('keyup', function() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        currentSearch = $(this).val();
        currentPage = 1;
        loadBahan();
    }, 400);
});

$('#prev-page').on('click', function() {
    if (currentPage > 1) {
        currentPage--;
        loadBahan();
    }
});

$('#next-page').on('click', function() {
    currentPage++;
    loadBahan();
});
</script>
@endpush

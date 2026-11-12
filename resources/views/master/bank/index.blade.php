@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Rekening Bank Perusahaan" subtitle="Daftar rekening bank untuk penerimaan pembayaran nota.">
        <x-slot:actions>
            <x-button onclick="toggleModal('bank-tambah')">
                <i class="fas fa-plus-circle"></i>
                Tambah Rekening
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- FILTER & SEARCH --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-8">
        <form method="GET" action="{{ route('bank.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[260px]">
                <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2 ml-2">Cari Rekening</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full bg-slate-50 border-none rounded-xl py-3 pl-12 pr-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-indigo-500/20 shadow-inner"
                           placeholder="Ketik nama bank, atas nama, atau no. rekening...">
                </div>
            </div>
            <button type="submit" class="h-[38px] px-6 bg-indigo-600 text-white rounded-xl text-[11px] font-medium uppercase tracking-widest hover:bg-indigo-700">Filter</button>
            <a href="{{ route('bank.index') }}" class="h-[38px] px-4 flex items-center justify-center bg-slate-100 text-slate-400 rounded-xl hover:bg-rose-50 hover:text-rose-500 transition-all" title="Reset Filter">
                <i class="fas fa-undo-alt text-sm"></i>
            </a>
        </form>
    </div>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 md:gap-5">
    @forelse($bank as $b)
    @php
        $nana = strtolower($b->nama_bank);
        $kelasBank = str_contains($nana, 'bca') ? 'bca'
            : (str_contains($nana, 'mandiri') ? 'mandiri'
            : (str_contains($nana, 'bri') ? 'bri'
            : (str_contains($nana, 'bni') ? 'bni'
            : (str_contains($nana, 'cimb') ? 'cimb'
            : (str_contains($nana, 'danamon') ? 'danamon'
            : 'default')))));
        $nomor = preg_replace('/\D+/', '', (string) $b->nomor_rekening);
        $tampilNomor = trim(chunk_split($nomor, 4, ' ')) ?: $b->nomor_rekening;
    @endphp
    {{-- Kartu ATM/debit - satu rekening = satu kartu; tombol ubah/hapus
         melayang di kanan bawah kartu. Warna menirukan paruh atas kartu
         tiap bank (bukan merek resmi). --}}
    <div class="bank-atm-card bank-atm-static bank-{{ $kelasBank }}" role="group" aria-label="{{ $b->nama_bank }} - {{ $b->nomor_rekening }} ({{ $b->atas_nama }})">
        <span class="bank-atm-sheen"></span>
        <span class="bank-atm-head">
            <span class="bank-atm-bank" title="{{ $b->nama_bank }}">{{ $b->nama_bank }}</span>
            <span class="bank-atm-debit">DEBIT</span>
        </span>
        <span class="bank-atm-chip" style="margin-top:14px"></span>
        <span class="bank-atm-number bank-atm-number--big">{{ $tampilNomor }}</span>
        <span class="bank-atm-foot">
            <span class="min-w-0">
                <span class="bank-atm-label">Pemegang Kartu</span>
                <span class="bank-atm-holder">a.n {{ $b->atas_nama }}</span>
            </span>
        </span>
        <span class="bank-atm-actions">
            <button onclick="openEditBank({{ json_encode($b) }})"
                    class="act-edit" aria-label="Ubah rekening {{ $b->nama_bank }}" title="Ubah rekening">
                <i class="far fa-pen-to-square text-[13px]"></i>
            </button>
            <form id="delete-form-bank-{{ $b->id_bank }}" action="{{ route('bank.destroy', $b->id_bank) }}" method="POST">
                @csrf @method('DELETE')
                <button type="button" class="act-delete" onclick="confirmDeleteBank('{{ $b->id_bank }}', {{ json_encode($b->nama_bank . ' - ' . $b->nomor_rekening) }})" aria-label="Hapus rekening {{ $b->nama_bank }}" title="Hapus rekening">
                    <i class="far fa-trash-can text-[13px]"></i>
                </button>
            </form>
        </span>
    </div>
    @empty
    <div class="col-span-full py-20 text-center text-slate-400 font-medium italic">Belum ada rekening bank yang terdaftar.</div>
    @endforelse
</div>

<x-table.pagination :paginator="$bank" />

</div>

<x-modal id="bank-tambah" title="Tambah Bank"
         subtitle="Rekening bank untuk sinkronisasi pembayaran"
         icon="fa-building-columns" maxWidth="md">
<form action="{{ route('bank.store') }}" method="POST" class="relative w-full max-w-md bg-white p-8 space-y-4 shadow-2xl">
        @csrf
        <h3 class="text-xl text-slate-900 mb-2">Input Rekening Baru</h3>
        <input type="text" name="nama_bank" placeholder="Nama Bank (BCA, Mandiri, dll)" class="w-full border-slate-200 px-4 outline-none focus:ring-indigo-500" required>
        <input type="text" name="nomor_rekening" placeholder="Nomor Rekening" class="w-full border-slate-200 px-4 outline-none focus:ring-indigo-500" required>
        <input type="text" name="atas_nama" placeholder="Atas Nama (Pemilik)" class="w-full border-slate-200 px-4 outline-none focus:ring-indigo-500" required>
        <x-button type="submit" class="w-full mt-2">Simpan Rekening</x-button>
    </form>
</x-modal>
<x-modal id="bank-edit" title="Edit Bank"
         subtitle="Perubahan berlaku untuk transaksi berikutnya"
         icon="fa-pen" maxWidth="md">
<form id="formEditBank" method="POST" class="relative w-full max-w-md bg-white p-8 space-y-4 shadow-2xl">
        @csrf
        @method('PUT')
        <h3 class="text-xl text-slate-900 mb-2">Perbarui Rekening</h3>
        
        <div>
            <label class="text-xs">Nama Bank</label>
            <input type="text" name="nama_bank" id="edit_nama_bank" class="w-full border-slate-200 px-4 outline-none focus:ring-indigo-500" required>
        </div>
        
        <div>
            <label class="text-xs">Nomor Rekening</label>
            <input type="text" name="nomor_rekening" id="edit_nomor_rekening" class="w-full border-slate-200 px-4 outline-none focus:ring-indigo-500" required>
        </div>
        
        <div>
            <label class="text-xs">Atas Nama</label>
            <input type="text" name="atas_nama" id="edit_atas_nama" class="w-full border-slate-200 px-4 outline-none focus:ring-indigo-500" required>
        </div>

        <x-button type="submit" class="w-full mt-2">Perbarui Rekening</x-button>
    </form>
</x-modal>
<script>

    function openEditBank(data) {
        // Set Action URL sesuai ID Bank
        const form = document.getElementById('formEditBank');
        form.action = `{{ url('/bank') }}/${data.id_bank}`;
        
        // Isi nilai input modal edit
        document.getElementById('edit_nama_bank').value = data.nama_bank;
        document.getElementById('edit_nomor_rekening').value = data.nomor_rekening;
        document.getElementById('edit_atas_nama').value = data.atas_nama;
        
        // Tampilkan modal
        toggleModal('bank-edit');
    }

    function confirmDeleteBank(id, label) {
        Swal.fire({
            title: 'Hapus Rekening?',
            html: `Rekening <b>${escHtml(label)}</b> akan dihapus permanen.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#93481A',
            cancelButtonColor: '#FAFBF9',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'rounded-lg px-6 py-2.5',
                cancelButton: 'rounded-lg px-6 py-2.5 text-slate-600'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(`delete-form-bank-${id}`).submit();
            }
        });
    }
</script>
@endsection
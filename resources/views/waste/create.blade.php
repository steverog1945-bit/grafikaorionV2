@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-10">

    <x-page-header title="Catat Waste" subtitle="Susut bahan baku yang tidak menjadi barang terjual" />

    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-rose-50 text-rose-600 text-sm font-semibold">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <x-card>
        <form method="POST" action="{{ route('waste.store') }}" class="space-y-5">
            @csrf

            <x-form.group label="Bahan">
                <x-form.select name="id_bahan" id="id_bahan" required>
                    <option value="">-- Pilih Bahan --</option>
                    @foreach($bahanList as $b)
                        <option value="{{ $b->id_bahan }}" {{ old('id_bahan') == $b->id_bahan ? 'selected' : '' }}>{{ $b->nama_bahan }} (stok {{ angka($b->stok, 2) }} {{ $b->satuan_stok }})</option>
                    @endforeach
                </x-form.select>
            </x-form.group>

            <div class="grid grid-cols-2 gap-5">
                <x-form.group label="Qty Waste (satuan stok)">
                    <x-form.input type="number" step="0.0001" min="0.0001" name="qty" value="{{ old('qty') }}" required />
                </x-form.group>
                <x-form.group label="Jenis">
                    <x-form.select name="jenis" required>
                        <option value="Normal" {{ old('jenis') === 'Normal' ? 'selected' : '' }}>Normal (susut wajar proses cetak)</option>
                        <option value="Abnormal" {{ old('jenis') === 'Abnormal' ? 'selected' : '' }}>Abnormal (rusak, salah potong, hilang)</option>
                    </x-form.select>
                </x-form.group>
            </div>

            <x-form.group label="Terkait Item Order (opsional - masuk biaya Job Costing order itu)">
                <x-form.select name="id_detail" id="id_detail">
                    <option value="">-- Tidak terkait order tertentu --</option>
                    @foreach($items as $i)
                        <option value="{{ $i->id_detail }}" {{ old('id_detail') == $i->id_detail ? 'selected' : '' }}>{{ $i->nomor_nota }} - {{ $i->nama_file }}</option>
                    @endforeach
                </x-form.select>
            </x-form.group>

            <x-form.group label="Alasan">
                <x-form.input type="text" name="alasan" maxlength="255" value="{{ old('alasan') }}" placeholder="Mis. sisa potongan roll, basah kena air" required />
            </x-form.group>

            <p class="text-xs text-slate-400">Normal langsung mengurangi stok dan dijurnal. Abnormal diajukan ke Approval Workflow; stok baru berkurang setelah disetujui.</p>

            <x-button type="submit" variant="primary" class="w-full"><i class="fas fa-save"></i> Simpan</x-button>
        </form>
    </x-card>
</div>
@endsection

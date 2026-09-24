@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1600px] mx-auto">

    <x-page-header title="Design Proof">
        <x-slot:subtitle>
            <i class="fas fa-pencil-ruler text-amber-400"></i>
            Proof desain per item order: unggah, catat persetujuan pelanggan, dan kunci desain sebelum produksi
        </x-slot:subtitle>
    </x-page-header>

    <x-card class="mb-8">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div class="w-full md:w-72">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Cari nota / nama file</label>
                <x-form.input type="text" name="q" value="{{ request('q') }}" placeholder="INV-... atau nama file" />
            </div>
            <div class="w-full md:w-56">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-2">Status Proof</label>
                <x-form.select name="status" onchange="this.form.submit()">
                    <option value="">Semua</option>
                    @foreach(['Belum Ada Proof', 'Menunggu Review', 'Revisi Diminta', 'Disetujui', 'Dibuka Kembali'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <x-button type="submit" variant="secondary"><i class="fas fa-search"></i> Cari</x-button>
        </form>
    </x-card>

    <x-table>
        <thead>
            <tr>
                <th class="px-8 py-4 text-left">Nota / Item</th>
                <th class="px-6 py-4 text-right">Qty</th>
                <th class="px-6 py-4 text-center">Status SPK</th>
                <th class="px-6 py-4 text-center">Status Proof</th>
                <th class="px-8 py-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @php $variantMap = ['Menunggu Review' => 'warning', 'Revisi Diminta' => 'danger', 'Disetujui' => 'success', 'Dibuka Kembali' => 'warning']; @endphp
            @forelse($daftar as $d)
            <tr class="hover:bg-slate-50/30">
                <td class="px-8 py-4"><span class="text-[13px] font-bold text-slate-700">{{ $d->nomor_nota }}</span><div class="text-[11px] text-slate-400">{{ $d->nama_file }}</div></td>
                <td class="px-6 py-4 text-right text-sm">{{ $d->qty }}</td>
                <td class="px-6 py-4 text-center text-xs text-slate-500">{{ $d->status_spk }}</td>
                <td class="px-6 py-4 text-center">
                    @if($d->status_proof)
                        <x-badge :status="$d->status_proof . ' (rev #' . $d->revisi_no . ')'" :variant="$variantMap[$d->status_proof] ?? 'info'" />
                    @else
                        <span class="text-xs text-slate-300">Belum ada proof</span>
                    @endif
                </td>
                <td class="px-8 py-4 text-right">
                    <x-button size="sm" variant="secondary" :href="route('design-proof.show', $d->id_detail)"><i class="fas fa-folder-open"></i> Buka</x-button>
                </td>
            </tr>
            @empty
            <tr><td colspan="5"><x-empty-state title="Tidak ada item order" /></td></tr>
            @endforelse
        </tbody>
    </x-table>
    <x-table.pagination :paginator="$daftar" />
</div>
@endsection

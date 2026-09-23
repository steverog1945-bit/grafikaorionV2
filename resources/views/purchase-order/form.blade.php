@extends('layouts.app')

@section('content')
@php
    $editable = !$po || $po->status === 'Draft';
    $idPr = old('id_purchase_request', $po->id_purchase_request ?? ($prefill['id_purchase_request'] ?? null));
    $rows = old('items', $po
        ? $po->details->map(fn ($d) => ['id_bahan' => $d->id_bahan, 'qty' => $d->qty, 'harga' => $d->harga, 'qty_diterima' => $d->qty_diterima])->all()
        : ($prefill['items'] ?? [['id_bahan' => '', 'qty' => '', 'harga' => '']]));
    $nomorPr = $po?->purchaseRequest?->nomor ?? ($prefill['nomor_pr'] ?? null);
    $labelPr = $nomorPr ? "Dari {$nomorPr} - " : '';
    $variantMap = ['Draft' => 'info', 'Dipesan' => 'warning', 'Diterima Sebagian' => 'warning', 'Selesai' => 'success', 'Dibatalkan' => 'danger'];
@endphp
<div class="w-full px-4 sm:px-6 lg:px-8 py-10 max-w-[1200px] mx-auto">

    <x-page-header title="{{ $po ? 'Purchase Order ' . $po->nomor : 'Purchase Order Baru' }}">
        <x-slot:subtitle>
            <i class="fas fa-file-invoice text-amber-400"></i>
            {{ $labelPr }}
            {{ $editable ? 'Draft - bisa diubah sampai diterbitkan ke supplier' : 'Hanya-baca - sudah diterbitkan' }}
        </x-slot:subtitle>
        @if($po)
        <x-slot:actions>
            <x-badge :status="$po->status" :variant="$variantMap[$po->status] ?? 'info'" />
        </x-slot:actions>
        @endif
    </x-page-header>

    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-rose-50 text-rose-600 text-sm font-semibold">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <form id="form-po" method="POST" action="{{ $po ? route('purchase-order.update', $po->id) : route('purchase-order.store') }}">
        @csrf
        @if($po) @method('PATCH') @endif
        @if($idPr && !$po)<input type="hidden" name="id_purchase_request" value="{{ $idPr }}">@endif

        <x-card class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <x-form.group label="Supplier">
                    <x-form.select name="id_supplier" id="id_supplier" required :disabled="! $editable">
                        <option value="">-- Pilih Supplier --</option>
                        @foreach($supplierList as $s)
                            <option value="{{ $s->id_supplier }}" {{ old('id_supplier', $po->id_supplier ?? '') == $s->id_supplier ? 'selected' : '' }}>{{ $s->nama_supplier }}</option>
                        @endforeach
                    </x-form.select>
                </x-form.group>
                <x-form.group label="Tanggal PO">
                    <x-form.input type="date" name="tanggal" value="{{ old('tanggal', $po ? $po->tanggal->toDateString() : now()->toDateString()) }}" required {{ $editable ? '' : 'disabled' }} />
                </x-form.group>
                <x-form.group label="Estimasi Kedatangan">
                    <x-form.input type="date" name="tgl_pengiriman_estimasi" value="{{ old('tgl_pengiriman_estimasi', $po?->tgl_pengiriman_estimasi?->toDateString()) }}" {{ $editable ? '' : 'disabled' }} />
                </x-form.group>
                <x-form.group label="Termin Pembayaran">
                    <x-form.input type="text" name="termin_pembayaran" value="{{ old('termin_pembayaran', $po->termin_pembayaran ?? '') }}" placeholder="Mis. Net 30 / Tunai" {{ $editable ? '' : 'disabled' }} />
                </x-form.group>
            </div>
        </x-card>

        <x-table>
            <thead>
                <tr>
                    <th class="px-6 py-4 text-left">Bahan</th>
                    <th class="px-4 py-4 text-right w-32">Qty</th>
                    <th class="px-4 py-4 text-right w-44">Harga Satuan</th>
                    <th class="px-4 py-4 text-right w-44">Subtotal</th>
                    @if($po && !$editable)<th class="px-4 py-4 text-right w-28">Diterima</th>@endif
                    @if($editable)<th class="px-4 py-4 w-12"></th>@endif
                </tr>
            </thead>
            <tbody id="item-rows" class="divide-y divide-slate-50">
                @foreach($rows as $i => $r)
                <tr data-row>
                    <td class="px-6 py-3">
                        <select name="items[{{ $i }}][id_bahan]" class="w-full rounded-xl border-slate-200 text-sm p-2" required {{ $editable ? '' : 'disabled' }}>
                            <option value="">-- Pilih Bahan --</option>
                            @foreach($bahanList as $b)
                                <option value="{{ $b->id_bahan }}" {{ ($r['id_bahan'] ?? '') == $b->id_bahan ? 'selected' : '' }}>{{ $b->nama_bahan }} ({{ $b->satuan_stok }})</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-4 py-3"><input type="number" step="0.01" min="0.01" name="items[{{ $i }}][qty]" value="{{ $r['qty'] ?? '' }}" data-qty class="w-full rounded-xl border-slate-200 text-sm p-2 text-right" required {{ $editable ? '' : 'disabled' }}></td>
                    <td class="px-4 py-3"><input type="number" step="0.01" min="0" name="items[{{ $i }}][harga]" value="{{ $r['harga'] ?? '' }}" data-harga class="w-full rounded-xl border-slate-200 text-sm p-2 text-right" required {{ $editable ? '' : 'disabled' }}></td>
                    <td class="px-4 py-3 text-right"><span class="text-sm font-bold text-slate-700" data-subtotal>0</span></td>
                    @if($po && !$editable)<td class="px-4 py-3 text-right text-sm text-slate-500">{{ number_format($r['qty_diterima'] ?? 0, 2) }}</td>@endif
                    @if($editable)<td class="px-4 py-3 text-center"><button type="button" onclick="this.closest('tr').remove(); hitungTotal()" class="text-slate-300 hover:text-rose-500"><i class="fas fa-trash-alt"></i></button></td>@endif
                </tr>
                @endforeach
            </tbody>
        </x-table>

        @if($editable)
        <div class="mt-4"><x-button type="button" variant="outline" size="sm" onclick="tambahBaris()"><i class="fas fa-plus"></i> Tambah Item</x-button></div>
        @endif

        <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-card>
                <x-form.group label="Catatan (opsional)">
                    <textarea name="catatan" rows="4" class="w-full rounded-xl border-slate-200 text-sm p-3" {{ $editable ? '' : 'disabled' }}>{{ old('catatan', $po->catatan ?? '') }}</textarea>
                </x-form.group>
            </x-card>
            <x-card>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span class="font-bold" id="t-subtotal">0</span></div>
                    <div class="flex justify-between items-center gap-4"><span class="text-slate-500">Diskon (Rp)</span>
                        <input type="number" step="0.01" min="0" name="diskon" id="in-diskon" value="{{ old('diskon', $po->diskon ?? 0) }}" class="w-40 rounded-xl border-slate-200 text-sm p-2 text-right" {{ $editable ? '' : 'disabled' }}></div>
                    <div class="flex justify-between items-center gap-4"><span class="text-slate-500">Pajak (%)</span>
                        <input type="number" step="0.01" min="0" max="100" name="pajak_persen" id="in-pajak" value="{{ old('pajak_persen', $po->pajak_persen ?? 0) }}" class="w-40 rounded-xl border-slate-200 text-sm p-2 text-right" {{ $editable ? '' : 'disabled' }}></div>
                    <div class="flex justify-between items-center gap-4"><span class="text-slate-500">Ongkir (Rp)</span>
                        <input type="number" step="0.01" min="0" name="ongkir" id="in-ongkir" value="{{ old('ongkir', $po->ongkir ?? 0) }}" class="w-40 rounded-xl border-slate-200 text-sm p-2 text-right" {{ $editable ? '' : 'disabled' }}></div>
                    <div class="flex justify-between pt-3 border-t border-slate-100 text-base"><span class="font-bold text-slate-700">Total</span><span class="font-extrabold text-indigo-600" id="t-total">0</span></div>
                </div>
            </x-card>
        </div>

        @if($editable)
        <div class="mt-8 flex flex-wrap gap-3 justify-end">
            @if($po)
                <x-button type="submit" form="form-hapus-po" variant="danger"><i class="fas fa-trash"></i> Hapus</x-button>
            @endif
            <x-button type="submit" variant="secondary"><i class="fas fa-save"></i> Simpan Draft</x-button>
            @if($po)
                <x-button type="submit" form="form-terbit-po" variant="primary"><i class="fas fa-paper-plane"></i> Terbitkan ke Supplier</x-button>
            @endif
        </div>
        @endif
    </form>

    @if($po)
        @if($editable)
            <form id="form-terbit-po" method="POST" action="{{ route('purchase-order.terbitkan', $po->id) }}" onsubmit="return confirm('Terbitkan PO ini ke supplier? Setelah diterbitkan tidak bisa diubah.');">@csrf @method('PATCH')</form>
            <form id="form-hapus-po" method="POST" action="{{ route('purchase-order.destroy', $po->id) }}" onsubmit="return confirm('Hapus Purchase Order ini?');">@csrf @method('DELETE')</form>
        @endif
        @if(in_array($po->status, ['Draft', 'Dipesan']))
            <form method="POST" action="{{ route('purchase-order.batalkan', $po->id) }}" onsubmit="return confirm('Batalkan Purchase Order ini?');" class="mt-4 flex justify-end">@csrf @method('PATCH')
                <x-button type="submit" variant="outline" size="sm"><i class="fas fa-ban"></i> Batalkan PO</x-button>
            </form>
        @endif
    @endif

    <template id="row-template">
        <tr data-row>
            <td class="px-6 py-3">
                <select name="items[__I__][id_bahan]" class="w-full rounded-xl border-slate-200 text-sm p-2" required>
                    <option value="">-- Pilih Bahan --</option>
                    @foreach($bahanList as $b)<option value="{{ $b->id_bahan }}">{{ $b->nama_bahan }} ({{ $b->satuan_stok }})</option>@endforeach
                </select>
            </td>
            <td class="px-4 py-3"><input type="number" step="0.01" min="0.01" name="items[__I__][qty]" data-qty class="w-full rounded-xl border-slate-200 text-sm p-2 text-right" required></td>
            <td class="px-4 py-3"><input type="number" step="0.01" min="0" name="items[__I__][harga]" data-harga class="w-full rounded-xl border-slate-200 text-sm p-2 text-right" required></td>
            <td class="px-4 py-3 text-right"><span class="text-sm font-bold text-slate-700" data-subtotal>0</span></td>
            <td class="px-4 py-3 text-center"><button type="button" onclick="this.closest('tr').remove(); hitungTotal()" class="text-slate-300 hover:text-rose-500"><i class="fas fa-trash-alt"></i></button></td>
        </tr>
    </template>
</div>

@push('scripts')
<script>
    let nextIndex = {{ count($rows) + 100 }};
    const rp = n => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(n);

    // Tampilan saja - total sebenarnya SELALU dihitung ulang di server.
    function hitungTotal() {
        let subtotal = 0;
        document.querySelectorAll('[data-row]').forEach(row => {
            const s = (parseFloat(row.querySelector('[data-qty]').value) || 0) * (parseFloat(row.querySelector('[data-harga]').value) || 0);
            row.querySelector('[data-subtotal]').textContent = rp(s);
            subtotal += s;
        });
        const diskon = parseFloat(document.getElementById('in-diskon').value) || 0;
        const pajak = (subtotal - diskon) * (parseFloat(document.getElementById('in-pajak').value) || 0) / 100;
        const ongkir = parseFloat(document.getElementById('in-ongkir').value) || 0;
        document.getElementById('t-subtotal').textContent = rp(subtotal);
        document.getElementById('t-total').textContent = 'Rp ' + rp(subtotal - diskon + pajak + ongkir);
    }

    function tambahBaris() {
        document.getElementById('item-rows').insertAdjacentHTML('beforeend', document.getElementById('row-template').innerHTML.replaceAll('__I__', nextIndex++));
        hitungTotal();
    }

    document.getElementById('form-po').addEventListener('input', hitungTotal);
    hitungTotal();
</script>
@endpush
@endsection

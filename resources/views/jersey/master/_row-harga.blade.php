{{-- Sinkron dengan rowHargaHtml() di JS master/index.blade.php. --}}
<tr data-id="{{ $row->id }}">
    <td class="p-2.5">{{ $row->productType->name ?? 'Semua' }}</td>
    <td class="p-2.5">{{ $row->category->name ?? 'Semua' }}</td>
    <td class="p-2.5 text-center">{{ $row->qty_min }} - {{ $row->qty_max ?? '∞' }}</td>
    <td class="p-2.5 text-right font-bold">Rp {{ number_format($row->price, 0, ',', '.') }}</td>
    <td class="p-2.5 text-center">
        <button type="button" onclick="hapusHarga({{ $row->id }})" class="w-8 h-8 rounded-lg text-rose-400 hover:bg-rose-50"><i class="far fa-trash-alt text-xs"></i></button>
    </td>
</tr>
